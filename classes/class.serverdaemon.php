<?php
// classes/class.serverdaemon.php

class ServerDaemon
{
    private string $pidFile;
    private string $serverScript;
    private Logger $logger;
    private bool $running = false;
    private $serverSocket = null; // stream_socket_server resource
    private $pidFp = null;           // file handle we keep locked
    private bool $foreground = false;

    public function __construct(string $serverScript, string $pidFile, Logger $logger)
    {
        $this->serverScript = $serverScript;
        $this->pidFile = $pidFile;
        $this->logger = $logger;
    }

    /* ------------------------- Lifecycle commands ------------------------- */

    public function start(): void
    {
        if ($this->isRunning()) {
            $this->logger->info("Already running (PID " . $this->readPid() . ").");
            return;
        }

        $this->acquirePidLock();  // <— take the lock early

        // daemonize (double fork)
        if (stripos(PHP_OS, 'WIN') === false) {
            $this->daemonize();
        }

        // Now we’re the daemonized child: write our real PID under the lock
        $this->writeLockedPid(getmypid());
        $this->logger->info("Daemon started. PID " . getmypid());

        // Ensure we always release lock on shutdown
        register_shutdown_function(function () {
            $this->releasePidLock();
        });

        $this->runLoop();
    }

    public function run(): void
    {
        if ($this->isRunning()) {
            $this->logger->info("Already running (PID " . $this->readPid() . ").");
            return;
        }

        $this->acquirePidLock();               // <— take the lock
        $this->writeLockedPid(getmypid());     // foreground PID
        $this->foreground = true;
        $this->logger->info("Running in foreground. PID " . getmypid());

        register_shutdown_function(function () {
            $this->releasePidLock();
        });

        $this->runLoop();
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

    private function acquirePidLock(): void
    {
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
        if ($this->pidFp) {
            @flock($this->pidFp, LOCK_UN);
            @fclose($this->pidFp);
            $this->pidFp = null;
        }
    }

    /* ----------------------------- Main loop ------------------------------ */

    private function runLoop(): void
    {
        $this->installSignals();
        $this->running = true;

        // Build your app SocketServer
        $appServer = $this->createSocketServer();

        // Try to get its listening socket; otherwise delegate to run()
        if (method_exists($appServer, 'getServerSocket')) {
            $appSock = $appServer->getServerSocket();
        } elseif (method_exists($appServer, 'getSocket')) {
            $appSock = $appServer->getSocket();
        } else {
            $this->logger->info('Delegating loop to SocketServer::run() (no socket getter exposed).');
            if (method_exists($appServer, 'run')) {
                $appServer->run(); // blocks until killed
                $this->cleanupAndExit(0);
                return;
            }
            $this->logger->error('SocketServer has no getServerSocket()/getSocket()/run().');
            $this->cleanupAndExit(1);
            return;
        }

        if (!is_resource($appSock)) {
            $this->logger->error("SocketServer did not return a listening socket resource.");
            $this->cleanupAndExit(1);
        }

        stream_set_blocking($appSock, false);
        $this->logger->info("App socket listening on " . SERVER_APP_HOST . ":" . SERVER_APP_PORT);

        // Enable interactive STDIN only in foreground mode
        $stdin = null;
        if ($this->foreground && defined('STDIN') && is_resource(STDIN)) {
            stream_set_blocking(STDIN, false);
            $stdin = STDIN;
            $this->logger->info("Interactive commands enabled (type: status | ping | shutdown).");
        }

        // Main loop (no admin port)
        while ($this->running) {
            $read = [$appSock];
            if ($stdin) {
                $read[] = $stdin;
            }

            if (method_exists($appServer, 'getClientSockets')) {
                $read = array_merge($read, $appServer->getClientSockets());
            }

            $write = $except = null;
            @stream_select($read, $write, $except, 0, 200_000); // 200ms

            foreach ($read as $sock) {
                // Handle STDIN commands in the same terminal
                if ($stdin && $sock === $stdin) {
                    $line = fgets($stdin);
                    if ($line !== false) {
                        $resp = $this->handleAdminCommand(trim($line));
                        echo $resp . PHP_EOL; // show response
                    }
                    continue;
                }

                // New app client
                if ($sock === $appSock) {
                    if (method_exists($appServer, 'accept')) {
                        $appServer->accept(); // non-blocking accept
                    }
                    continue;
                }

                // If you merged app client sockets into $read, you can handle IO here.
                // Otherwise, let the tick/poll pass below do it.
            }

            // Let the app server do a non-blocking pass
            if (method_exists($appServer, 'tick')) {
                $appServer->tick();
            } elseif (method_exists($appServer, 'poll')) {
                $appServer->poll();
            } else {
                // If your SocketServer has a blocking run(), prefer delegating earlier.
                usleep(50_000);
            }
        }

        if (method_exists($appServer, 'shutdown'))
            $appServer->shutdown();
        $this->cleanupAndExit(0);
    }

    private function createSocketServer()
    {
        // Your SocketServer signature expects PID file as 3rd arg.
        // Some versions also accept a Logger as 4th.
        try {
            if (class_exists('ReflectionClass')) {
                $ref = new \ReflectionClass('SocketServer');
                $ctor = $ref->getConstructor();
                $argc = $ctor ? $ctor->getNumberOfParameters() : 0;

                if ($argc >= 4) {
                    return new \SocketServer(SERVER_APP_HOST, SERVER_APP_PORT, PID_FILE, $this->logger);
                } else {
                    return new \SocketServer(SERVER_APP_HOST, SERVER_APP_PORT, PID_FILE);
                }
            }
        } catch (\Throwable $e) {
            // fall through to best-guess
            $this->logger->warn('Reflection failed for SocketServer::__construct(): ' . $e->getMessage());
        }

        // Best guess: 3-arg ctor
        return new \SocketServer(SERVER_APP_HOST, SERVER_APP_PORT, PID_FILE);
    }

    private function handleAdminCommand(string $line): string
    {
        $parts = preg_split('/\s+/', $line);
        $cmd = strtolower($parts[0] ?? '');
        switch ($cmd) {
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
                    'pid' => getmypid(),
                    'cwd' => getcwd(),
                    'memory' => [
                        'usage' => memory_get_usage(true),
                        'peak' => memory_get_peak_usage(true),
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
            case 'stop':
            case 'quit':
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
        if (function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
        }

        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, function () {
                $this->logger->info("SIGTERM received.");
                $this->running = false;
            });
            pcntl_signal(SIGINT, function () {
                $this->logger->info("SIGINT received.");
                $this->running = false;
            });
            pcntl_signal(SIGHUP, function () {
                $this->logger->info("SIGHUP (reload) not implemented.");
            });
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