<?php
class SocketServer
{
    private string $host;
    private int $port;
    private string $pidFile;
    private $socket;
    private bool $running = true;
    protected Logger $logger;


    public function __construct(string $host, int $port, string $pidFile, Logger $logger)
    {
        $this->host = $host;
        $this->port = $port;
        $this->pidFile = $pidFile;
        $this->logger = $logger;

        $this->ensureRequirements();
        $this->registerSignalHandlers();
        $this->setProcessTitle("socket-server:{$port}");
    }

    private function ensureRequirements(): void
    {
        if (PHP_SAPI !== 'cli') return;

        if (stripos(PHP_OS, 'LIN') === 0) {
            foreach (['pcntl', 'posix', 'sockets'] as $ext) {
                if (!extension_loaded($ext)) {
                    die("Extension `$ext` is required. Exiting.\n");
                }
            }
        }
    }

    private function setProcessTitle(string $title): void
    {
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        } elseif (is_writable("/proc/" . getmypid() . "/comm")) {
            file_put_contents("/proc/" . getmypid() . "/comm", $title);
        } else {
            $this->logger->debug("Could not set process title (insufficient permission?)");
        }
    }

    private function registerSignalHandlers(): void
    {
        if (PHP_SAPI !== 'cli' || stripos(PHP_OS, 'LIN') !== 0) return;

        pcntl_async_signals(true);
        pcntl_signal(SIGCHLD, [$this, 'signalHandler']);
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);
        pcntl_signal(SIGINT, [$this, 'signalHandler']);
        pcntl_signal(SIGHUP, [$this, 'signalHandler']);
    }

    public function signalHandler(int $signal): void
    {
        switch ($signal) {
            case SIGCHLD:
                while (pcntl_waitpid(-1, $status, WNOHANG) > 0) {
                    // Reap zombie processes
                }
                break;

            case SIGHUP:
                $this->logger->info("Received SIGHUP – restarting...");
                $this->shutdown();
                $this->start(); // optional restart
                break;

            case SIGTERM:
            case SIGINT:
                $this->logger->info("Received signal $signal – shutting down gracefully...");
                $this->shutdown();
                break;
        }
    }

    public function start(): void
    {
        $this->checkForRunningInstance();
        $this->bindSocket();
        $this->writePidFile();

        $this->logger->info("Starting server at {$this->host}:{$this->port}");

        while ($this->running && ($conn = @stream_socket_accept($this->socket))) {
            $message = trim(fgets($conn));
            $this->logger->debug("Received: $message");

            $response = $this->handleCommand($message);
            fwrite($conn, $response . PHP_EOL);
            fclose($conn);
        }

        $this->shutdown(); // fallback
    }

    private function handleCommand(string $cmd): string
    {
        return match ($cmd) {
            'ping' => 'pong',
            'time' => date('Y-m-d H:i:s'),
            'exit', 'quit' => $this->shutdownReturn("Server shutting down."),
            default => "Unknown command: $cmd",
        };
    }

    private function shutdownReturn(string $msg): string
    {
        $this->running = false;
        return $msg;
    }

    private function checkForRunningInstance(): void
    {
        if (file_exists($this->pidFile)) {
            $pid = (int) file_get_contents($this->pidFile);
            if ($pid && function_exists('posix_kill') && posix_kill($pid, 0)) {
                $this->logger->info("Server already running with PID $pid");
                exit(0);
            }
            unlink($this->pidFile); // stale
        }
    }

    private function bindSocket(): void
    {
        $this->socket = @stream_socket_server("tcp://{$this->host}:{$this->port}", $errno, $errstr);
        if (!$this->socket) {
            throw new RuntimeException("Socket Error [$errno]: $errstr");
        }
    }

    private function writePidFile(): void
    {
        file_put_contents($this->pidFile, getmypid());
    }

    public function shutdown(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        if (file_exists($this->pidFile)) {
            unlink($this->pidFile);
        }

        $this->logger->info("Server shut down cleanly.");
        
        $this->running = false;
    }
}