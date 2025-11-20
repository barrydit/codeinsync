<?php
//declare(ticks=1);
// src/Infrastructure/Daemon/ServerDaemon.php

namespace CodeInSync\Infrastructure\Daemon;

use CodeInSync\Infrastructure\Logging\Logger;
use CodeInSync\Infrastructure\Socket\SocketServer;

final class ServerDaemon
{
    private string $pidFile;
    private string $serverScript;
    private Logger $logger;
    private bool $running = false;
    private $lockHandle = null;         // external flock() handle if provided

    private ?SocketServer $socketServer = null;
    /** @var resource|null */
    private $serverSocket = null;
    /** @var resource|null */
    private $lockFp = null;            // file handle we created and locked
    private $pidFp = null;           // file handle we keep locked
    private bool $signalsInstalled = false;
    private bool $readyForSignals = false;
    private float $startedAt = 0.0;
    private bool $shutdownRequested = false;
    private bool $restartRequested = false;
    private bool $restartsInWindow = false;
    private bool $restartWindowStart = false;
    private bool $reloadRequested = false;

    private bool $foreground = false;

    public function __construct(string $serverScript, string $pidFile, Logger $logger)
    {
        $this->serverScript = $serverScript;
        $this->pidFile = $pidFile;
        $this->logger = $logger;
    }

    /* ------------------------- Lifecycle commands ------------------------- */

    public function run(): void
    {
        $this->launch(/*daemonize=*/ false);
    }
    public function start(): void
    {
        $this->launch(/*daemonize=*/ true);
    }

    private function launch(bool $daemonize): void
    {
        // 1) Take (or adopt) the lock
        $this->acquirePidLock();

        try {
            // 2) Only treat "running" as true if PID is alive
            $pid = (int) $this->readPid();
            if ($pid > 1) {
                if ($pid === getmypid()) {
                    // Our own stale pid from a previous attempt — clear and continue
                    $this->logger->info("Stale self PID in pidfile ({$pid}); clearing.");
                    @unlink($this->pidFile);
                } elseif (!$this->pidLooksAlive($pid)) {
                    // Stale pidfile — clear and continue
                    $this->logger->info("Stale pidfile ({$pid}); clearing.");
                    @unlink($this->pidFile);
                } else {
                    // A *different* live process is running — bail out
                    $this->logger->info("Already running (PID {$pid}).");
                    return;
                }
            }
            $this->logger->debug('ServerDaemon using socket: ' . (is_resource($this->serverSocket) ? 'injected' : 'self-bound'));
            // 3) Bind if not injected
            if ($this->serverSocket === null) {
                $ctx = stream_context_create(['socket' => ['so_reuseaddr' => true, 'backlog' => 128]]);
                $errno = 0;
                $errstr = '';
                $this->serverSocket = @stream_socket_server(
                    "tcp://127.0.0.1:9000",
                    $errno,
                    $errstr,
                    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                    $ctx
                );
                if ($this->serverSocket === false) {
                    throw new \RuntimeException("Port busy: {$errstr} (errno={$errno})");
                }
            }

            // 4) Daemonize only when requested (and only on Unix)
            if ($daemonize && PHP_OS_FAMILY !== 'Windows') {
                $this->daemonize();
            }

            // 5) Write PID under the lock (child PID if daemonized)
            $this->writeLockedPid(getmypid());
            $this->foreground = !$daemonize;
            $this->logger->info(($daemonize ? "Daemon started" : "Running in foreground")
                . ". PID " . getmypid());

            // $this->socketServer = new SocketServer('127.0.0.1', 9000, $this->pidFile, $this->logger, $this->serverSocket);

            // 6) Ensure cleanup
            register_shutdown_function(fn() => $this->releasePidLock());

            // 7) Main loop
            $this->runLoop();

        } catch (\Throwable $e) {
            $this->releasePidLock();
            throw $e;
        }
    }

    public function stop(): void
    {
        $pid = $this->readPid();
        if (!$pid) {
            $this->logger->info("Not running.");
            return;
        }
        if (!posix_kill($pid, 0)) {
            $this->logger->info("Stale PID $pid found. Removing PID file.");
            @unlink($this->pidFile);
            return;
        }
        $this->logger->info("Stopping PID $pid ...");
        posix_kill($pid, SIGTERM);
        // Wait up to ~3s for clean shutdown
        $deadline = microtime(true) + 3.0;
        while (microtime(true) < $deadline) {
            if (!posix_kill($pid, 0))
                break;
            usleep(100_000);
        }
        if (posix_kill($pid, 0)) {
            $this->logger->warn("Process did not exit in time. Sending SIGKILL.");
            posix_kill($pid, SIGKILL);
        }
        @unlink($this->pidFile);
        $this->logger->info("Stopped.");
    }

    public function restart(): void
    {
        $this->stop();
        $this->start();
    }

    public function status(): void
    {
        $pid = $this->readPid();
        if ($pid && posix_kill($pid, 0)) {
            $this->logger->info("Running (PID $pid).");
        } else {
            $this->logger->info("Not running.");
        }
    }

    private function pidLooksAlive(int $pid): bool
    {
        if ($pid <= 1)
            return false;

        // Prefer /proc on Linux — very reliable under WSL/real Linux
        if (PHP_OS_FAMILY === 'Linux') {
            return @is_dir("/proc/{$pid}");
        }

        // Fallback (BSD/macOS): posix_kill(0) if available
        if (function_exists('posix_kill')) {
            return @posix_kill($pid, 0);
        }

        // Windows: no cheap probe; rely on port bind instead
        return false;
    }

    // call this before run()
    public function adoptExternalLock($handle): void
    {
        $this->lockHandle = $handle;    // just store the resource; do NOT close it here
    }

    // helper
    private function hasExternalLock(): bool
    {
        return is_resource($this->lockHandle);
    }

    public function setSocket($sock): void
    {
        $this->serverSocket = $sock;
    }
    private function isRunningAlive(): bool
    {
        $pid = (int) $this->readPid();
        if ($pid <= 1)
            return false;
        if (PHP_OS_FAMILY === 'Windows') {
            // No cheap cross-proc check on Windows; rely on the port bind above.
            return false;
        }
        return function_exists('posix_kill') ? @posix_kill($pid, 0) : false;
    }

    private function clearStalePid(): void
    {
        $pid = (int) $this->readPid();
        if ($pid > 1 && PHP_OS_FAMILY !== 'Windows' && function_exists('posix_kill') && @posix_kill($pid, 0)) {
            // alive → do nothing
            return;
        }
        // not alive → unlink if present
        @unlink($this->pidFile);
    }

    // make acquirePidLock() idempotent
    private function acquirePidLock(): void
    {
        if ($this->hasExternalLock())
            return; // already locked by main
        $this->pidFp = @fopen($this->pidFile, 'c+'); // create if not exists
        if (!$this->pidFp) {
            $this->logger->error("Cannot open PID file: {$this->pidFile}");
            $this->cleanupAndExit(1);
        }

        // Try non-blocking exclusive lock
        if (!@flock($this->pidFp, LOCK_EX | LOCK_NB)) {
            // Someone else holds the lock – very likely the running daemon
            $existingPid = trim(@file_get_contents($this->pidFile) ?: '');
            if ($existingPid !== '' && ctype_digit($existingPid)) {
                $this->logger->info("Already running (PID {$existingPid}).");
            } else {
                $this->logger->info("Already running (PID unknown; lock held).");
            }
            $this->cleanupAndExit(0);
        }

        // We hold the lock now. Don’t close $pidFp until exit.
    }

    private function writeLockedPid(int $pid): void
    {
        if (!$this->pidFp)
            return;
        // Overwrite atomically while holding the lock
        ftruncate($this->pidFp, 0);
        rewind($this->pidFp);
        fwrite($this->pidFp, (string) $pid);
        fflush($this->pidFp);
        // Optionally make it readable
        @chmod($this->pidFile, 0644);
    }

    private function releasePidLock(): void
    {
        if (!$this->hasExternalLock()) {
            // only close/unlink if we created it
            if ($this->pidFp) {
                @flock($this->pidFp, LOCK_UN);
                @fclose($this->pidFp);
                $this->pidFp = null;
            }
        }

    }

    /* ----------------------------- Main loop ------------------------------ */
    private function runLoop(): void
    {
        $this->installSignals();
        $this->running = true;

        // Ensure we have exactly one SocketServer instance
        if ($this->socketServer === null) {
            $this->socketServer = $this->createSocketServer(); // must pass injected socket
        }
        $appServer = $this->socketServer;

        // Prefer a single, well-known getter for the listening socket
        if (method_exists($appServer, 'getServerSocket')) {
            $appSock = $appServer->getServerSocket();
        } elseif (method_exists($appServer, 'getSocket')) {
            $appSock = $appServer->getSocket();
        } else {
            // Fallback: delegate to a blocking run() if that’s the API
            if (method_exists($appServer, 'run')) {
                $this->logger->info('Delegating loop to SocketServer::run()');
                $appServer->run();           // blocks
                $this->cleanupAndExit(0);
                return;
            }
            $this->logger->error('SocketServer has no getServerSocket()/getSocket()/run().');
            $this->cleanupAndExit(1);
            return;
        }

        if (!is_resource($appSock)) {
            $this->logger->error('SocketServer did not return a listening socket resource.');
            $this->cleanupAndExit(1);
            return;
        }

        stream_set_blocking($appSock, false);
        $this->logger->info("App socket listening on " . SERVER_APP_HOST . ":" . SERVER_APP_PORT);

        // Foreground interactive commands
        $stdin = null;
        if ($this->foreground && defined('STDIN') && is_resource(STDIN)) {
            stream_set_blocking(STDIN, false);
            $stdin = STDIN;
            $this->logger->info('Interactive commands enabled (status | ping | shutdown).');
        }

        $this->armSignals();

        $needPrompt = $this->foreground;

        while ($this->running) {

            if ($needPrompt) {
                echo SHELL_PROMPT;
                fflush(STDOUT);
                $needPrompt = false;     // prevent reprinting every iteration
            }

            $read = [$appSock];
            if ($stdin)
                $read[] = $stdin;

            if (method_exists($appServer, 'getClientSockets')) {
                $clientSocks = $appServer->getClientSockets();
                if (is_array($clientSocks) && $clientSocks) {
                    $read = array_merge($read, $clientSocks);
                }
            }

            $write = $except = null;
            @stream_select($read, $write, $except, 0, 200_000); // 200ms

            foreach ($read as $sock) {
                if ($stdin && $sock === $stdin) {
                    $line = fgets($stdin);
                    if ($line !== false) {
                        $resp = $this->handleAdminCommand(trim($line));
                        echo $resp . PHP_EOL;
                        $needPrompt = true;
                    }
                    continue;
                }

                if ($sock === $appSock) {
                    if (method_exists($appServer, 'accept')) {
                        $appServer->accept();
                    }
                    continue;
                }

                // Optionally handle per-client IO here if you merged client sockets
            }

            if (method_exists($appServer, 'tick')) {
                $appServer->tick();     // non-blocking maintenance
            } elseif (method_exists($appServer, 'poll')) {
                $appServer->poll();
            } else {
                usleep(50_000);
            }

            /* ===== PLACE YOUR ADMIN FLAGS HERE (inside the loop, after IO/tick) ===== */

            // Hot reload (no restart)
            if (!empty($this->reloadRequested)) {
                $this->reloadRequested = false;
                $this->logger->info('Reloading configuration...');
                if (method_exists($this, 'loadConfig')) {
                    $this->loadConfig();
                }
                if ($this->socketServer && method_exists($this->socketServer, 'reload')) {
                    $this->socketServer->reload();
                }
                if (method_exists($this->logger, 'reopen')) {
                    $this->logger->reopen();
                }
                $this->logger->info('Reload complete.');
            }

            // Full restart (spawn new process, then exit this one)
            if ($this->restartRequested) {
                $this->logger->info('Restart requested...');
                $ok = $this->restartSelf();
                if (!$ok) {
                    $this->logger->error('Restart failed; continuing to run current instance.');
                    $this->restartRequested = false;
                } else {
                    // Tell current loop to exit
                    $this->running = false;
                }
            }
            /* ===== END ADMIN FLAGS ===== */
        }

        if (method_exists($appServer, 'shutdown')) {
            $appServer->shutdown();
        }
        $this->cleanupAndExit(0);
    }
    private function restartSelf(): bool
    {
        // Basic restart loop guard: max 3 restarts in 60s
        $now = time();
        if ($now - $this->restartWindowStart > 60) {
            $this->restartWindowStart = $now;
            $this->restartsInWindow = 0;
        }
        if (++$this->restartsInWindow > 3) {
            $this->logger->error('Restart suppressed: too many restarts in 60s.');
            return false;
        }

        // Build argv similar to how you launched
        $php = PHP_BINARY ?: 'php';
        $script = $this->serverScript;           // e.g., absolute path to server.php
        $args = ['run', '--verbose'];            // or keep from $GLOBALS['argv']

        // Cross-platform spawn
        $cmd = escapeshellarg($php) . ' ' . escapeshellarg($script) . ' ' . implode(' ', array_map('escapeshellarg', $args));

        if (PHP_OS_FAMILY === 'Windows') {
            // Start detached
            $spec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $proc = @proc_open($cmd, $spec, $pipes, dirname($script));
            if (!\is_resource($proc)) {
                $this->logger->error('Failed to spawn new process on Windows.');
                return false;
            }
            // Don't wait; just close handles to detach
            foreach ($pipes as $p) {
                @fclose($p);
            }
            @proc_close($proc);
            return true;
        } else {
            // POSIX: prefer exec()-style handoff
            if (function_exists('pcntl_exec')) {
                // Before exec, do minimal cleanup (close socket, release lock)
                $this->preExitCleanup();
                pcntl_exec($php, array_merge([$script], $args));
                $this->logger->error('pcntl_exec() failed; fallback to proc_open.');
            }
            $proc = @proc_open($cmd, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, dirname($script));
            if (!\is_resource($proc)) {
                $this->logger->error('Failed to spawn new process.');
                return false;
            }
            foreach ($pipes as $p) {
                @fclose($p);
            }
            @proc_close($proc);
            return true;
        }
    }

    private function preExitCleanup(): void
    {
        // Stop accepting ASAP
        if (is_resource($this->serverSocket)) {
            @stream_socket_shutdown($this->serverSocket, STREAM_SHUT_RDWR);
            @fclose($this->serverSocket);
        }
        // Your loop will see $this->running=false and exit,
        // and your shutdown hook will release PID/lock.
    }

    private function createSocketServer(): SocketServer
    {
        if ($this->socketServer instanceof SocketServer) {
            return $this->socketServer;                // reuse existing
        }

        $this->socketServer = new SocketServer(
            SERVER_APP_HOST,
            SERVER_APP_PORT,
            $this->pidFile,
            $this->logger,
            $this->serverSocket      // injected listener or null
        );

        return $this->socketServer;                     // return for callers
    }

    private function handleAdminCommand(string $line): string
    {
        $parts = preg_split('/\s+/', $line);
        $cmd = strtolower($parts[0] ?? '');

        switch ($cmd) {
            case 'restart':
                $this->restartRequested = true;
                $this->logger->info('Restart requested...');
                return 'ok restarting';
            case 'reload':
                $this->reloadRequested = true;
                $this->logger->info('Reload requested...');
                return 'ok reloading';
            case 'ping':
                return 'pong';
            case 'status':
                return 'ok running pid=' . getmypid();
            case 'trace':
            case 'debug':
            case 'info': {
                $prefix = APP_PATH;
                $len = strlen($prefix);

                $files = array_map(
                    static function (string $p) use ($prefix, $len): string {
                        // Optional: normalize for display (helps on Windows)
                        $disp = str_replace('\\', '/', $p);
                        $pre = str_replace('\\', '/', $prefix);

                        return strncmp($p, $prefix, $len) === 0
                            ? substr($p, $len)
                            : $disp; // fall back to normalized full path
                    },
                    get_included_files()
                );

                // Determine context
                $isCli = defined('APP_CLI') && APP_CLI;                       // daemon/foreground
                $hasCtx = defined('APP_CONTEXT');
                $context = $hasCtx ? APP_CONTEXT : ($isCli ? 'cli' : 'web');    // fallback guess
                $isSocket = $hasCtx && APP_CONTEXT === 'socket';

                $payload = [
                    'ok' => true,
                    'base' => $prefix,
                    'count' => count($files),
                    'context' => $hasCtx ? APP_CONTEXT : null,
                    'mode' => defined('APP_MODE') ? APP_MODE : null,
                    'pid' => getmypid() . ' ' . $this->pidFile,
                    'log' => ini_get('error_log'),
                    'cwd' => getcwd(),
                    'memory' => [
                        'usage' => formatSizeUnits(memory_get_usage(true)),
                        'peak' => formatSizeUnits(memory_get_peak_usage(true)),
                    ],
                    'files' => array_values($files),
                ];

                // Pretty-print only when NOT sending over the socket (line-based)
                $opts = JSON_UNESCAPED_SLASHES | ($isSocket ? 0 : ($isCli ? JSON_PRETTY_PRINT : 0));

                $out = json_encode($payload, $opts); // implode("\n", $files); array_values($payload);

                // Line-terminated for sockets; pretty JSON already contains newlines for CLI
                if ($isSocket)
                    $out .= "\n";

                return $out;
            }
            case 'shutdown':
                $this->shutdownRequested = true;
                $this->logger->info('Shutdown requested...');
                return "ok shutting down";

            case 'stop':
            case 'quit':
            case 'exit':
                $this->logger->info("Admin requested shutdown.");
                $this->running = false;
                return 'ok bye';
            default:
                return 'error unknown_command';
        }
    }

    private function closeClient(array &$list, $sock): void
    {
        $i = array_search($sock, $list, true);
        if ($i !== false) {
            @fclose($list[$i]);
            array_splice($list, $i, 1);
        }
    }

    private function tick(): void
    {
        // put periodic work here (sweeps, heartbeats, etc.)
    }

    /* ---------------------------- Signal handling ------------------------- */

    private function installSignals(): void
    {
        if ($this->signalsInstalled)
            return;
        $this->startedAt = microtime(true);

        if (PHP_OS_FAMILY === 'Windows' && function_exists('sapi_windows_set_ctrl_handler')) {
            // Arm a debounced CTRL handler
            $self = $this;
            sapi_windows_set_ctrl_handler(function (int $evt) use ($self): bool {
                $since = microtime(true) - $self->startedAt;

                // Ignore very-early spurious events, and ignore until we're "armed"
                if ($since < 0.25 || !$self->readyForSignals) {
                    if (!empty($self->logger)) {
                        $self->logger->debug("Ignoring early Windows console event {$evt} ({$since} s)");
                    }
                    return true; // handled (suppressed)
                }

                switch ($evt) {
                    case 0: // CTRL_C_EVENT
                    case 1: // CTRL_BREAK_EVENT
                    case 2: // CTRL_CLOSE_EVENT
                    case 5: // LOGOFF
                    case 6: // SHUTDOWN
                        if (!empty($self->logger)) {
                            $self->logger->info("Windows console event {$evt}; stopping...");
                        }
                        $self->running = false;
                        if (is_resource($self->serverSocket)) {
                            @stream_socket_shutdown($self->serverSocket, STREAM_SHUT_RDWR);
                            @fclose($self->serverSocket);
                        }
                        return true; // handled
                }
                return false; // unhandled -> default behavior
            });
        }

        if (PHP_OS_FAMILY !== 'Windows' && function_exists('pcntl_async_signals') && function_exists('pcntl_signal')) {
            pcntl_async_signals(true);

            pcntl_signal(SIGTERM, function () {
                $this->logger->info("SIGTERM received.");
                $this->running = false;
            });
            pcntl_signal(SIGINT, function () {
                $this->logger->info("SIGINT received.");
                $this->running = false;
            });
            pcntl_signal(SIGHUP, function () {
                $this->logger->info("SIGHUP (reload) requested.");
                $this->reloadRequested = true;
            });
            pcntl_signal(SIGQUIT, function () {
                $this->logger->info("SIGQUIT received.");
                $this->running = false;
            });
        }

        $this->signalsInstalled = true;
    }

    private function armSignals(): void
    {
        // Call this once your loop is fully set up (after sockets, stdin, etc.)
        $this->readyForSignals = true;
        $this->logger->info(str_replace('{{STATUS}}', 'Server is running... PID=' . getmypid() . str_pad('', 6, " "), APP_DASHBOARD) . PHP_EOL);
    }

    private function stopWithLog(string $sig): void
    {
        if (!empty($this->logger)) {
            $this->logger->info("Received {$sig}; stopping...");
        }
        $this->running = false;
        if (is_resource($this->serverSocket)) {
            @stream_socket_shutdown($this->serverSocket, STREAM_SHUT_RDWR);
            @fclose($this->serverSocket);
        }
    }
    /* ------------------------------- Utilities ---------------------------- */

    private function daemonize(): void
    {
        $pid = pcntl_fork();
        if ($pid === -1) {
            $this->logger->error("First fork failed.");
            $this->cleanupAndExit(1);
        } elseif ($pid > 0) {
            // parent exits
            exit(0);
        }

        if (posix_setsid() === -1) {
            $this->logger->error("setsid() failed.");
            $this->cleanupAndExit(1);
        }

        $pid = pcntl_fork();
        if ($pid === -1) {
            $this->logger->error("Second fork failed.");
            $this->cleanupAndExit(1);
        } elseif ($pid > 0) {
            // parent exits
            exit(0);
        }

        // Detach from terminal
        chdir('/');
        umask(0);

        // Close stdio
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
        $stdIn = fopen('/dev/null', 'r');
        $stdOut = fopen('/dev/null', 'ab');
        $stdErr = fopen('/dev/null', 'ab');
        // Avoid unused warnings
        $stdIn && $stdOut && $stdErr;
    }

    private function writePid(int $pid): void
    {
        if (@file_put_contents($this->pidFile, (string) $pid) === false) {
            $this->logger->warn("Could not write PID file: {$this->pidFile}");
        }
    }

    private function readPid(): ?int
    {
        if (!is_file($this->pidFile))
            return null;
        $v = trim((string) @file_get_contents($this->pidFile));
        return ctype_digit($v) ? (int) $v : null;
    }

    private function isRunning(): bool
    {
        $pid = $this->readPid();
        return $pid ? posix_kill($pid, 0) : false;
    }

    private function cleanupAndExit(int $code): void
    {
        // ... your existing socket closes ...
        $this->releasePidLock();
        // Optional: remove PID file if process is gone
        // If you prefer to leave it, comment the next lines.
        if (is_file($this->pidFile)) {
            $pid = (int) trim(@file_get_contents($this->pidFile));
            if (!$pid || (function_exists('posix_kill') ? !@posix_kill($pid, 0) : true)) {
                @unlink($this->pidFile);
            }
        }
        exit($code);
    }
}