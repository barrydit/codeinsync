<?php

class ServerDaemon
{
    private string $pidFile;
    private string $phpPath;
    private string $serverScript;
    private static Logger $logger;

    public function __construct(string $serverScript, string $pidFile, Logger $logger)
    {
        $this->serverScript = $serverScript;
        $this->pidFile = $pidFile;
        $this->logger = $logger;
        $this->phpPath = PHP_BINARY; // /usr/bin/php or wherever CLI binary is
    }

    public function start(): void
    {
        if ($this->isRunning()) {
            echo "Server is already running with PID {$this->getPid()}.\n";
            return;
        }

        $cmd = "nohup {$this->phpPath} {$this->serverScript} run > /dev/null 2>&1 & echo $!";
        $output = [];
        exec($cmd, $output);

        if (!empty($output[0])) {
            file_put_contents($this->pidFile, $output[0]);
            echo "Server started with PID {$output[0]}\n";
        } else {
            echo "Failed to start server.\n";
        }
    }

    public function stop(): void
    {
        if (!$this->isRunning()) {
            echo "Server is not running.\n";
            return;
        }

        $pid = $this->getPid();
        exec("kill $pid");
        unlink($this->pidFile);
        echo "Server stopped (PID $pid).\n";
    }

    public function restart(): void
    {
        $this->stop();
        sleep(1); // optional delay
        $this->start();
    }

    public function status(): void
    {
        if ($this->isRunning()) {
            echo "Server is running with PID {$this->getPid()}.\n";
        } else {
            echo "Server is not running.\n";
        }
    }

    public function isRunning(): bool
    {
        if (!file_exists($this->pidFile))
            return false;

        $pid = $this->getPid();
        return $pid && posix_kill($pid, 0);
    }

    private function getPid(): ?int
    {
        return file_exists($this->pidFile) ? (int) file_get_contents($this->pidFile) : null;
    }
    
    /**
     * Initializes signal handling for graceful shutdown.
     */
    public static function initializeSignalHandlers(): void
    {
        if (stripos(PHP_OS, 'LIN') === 0) {
            if (!extension_loaded('pcntl') || !extension_loaded('posix')) {
                $errorStream = defined('STDERR') ? STDERR : fopen('php://stderr', 'w');
                fwrite($errorStream, "Required extensions pcntl/posix are missing.\n");
                exit(1);
            }

            pcntl_async_signals(true);

            pcntl_signal(SIGINT, [self::class, 'handleSignal']);
            pcntl_signal(SIGTERM, [self::class, 'handleSignal']);
            pcntl_signal(SIGHUP, [self::class, 'handleSignal']);
        }
    }

    /**
     * Sets the process title on compatible systems (Linux).
     */
    public static function setProcessTitle(string $title): void
    {
        if (function_exists('cli_set_process_title')) {
            self::$logger = new Logger(true); // Assuming Logger is already defined
            if (!@cli_set_process_title($title)) {
                self::$logger->warn("Could not set process title.");
            } else {
                self::$logger->info("Process title set to: $title");
            }
        }

        // Fallback on Linux using /proc
        if (stripos(PHP_OS, 'LIN') === 0) {
            $pid = getmypid();
            if (is_writable("/proc/{$pid}/comm")) {
                file_put_contents("/proc/{$pid}/comm", $title);
            }
        }
    }

    /**
     * Signal handler for graceful shutdown.
     */
    public static function handleSignal(int $signal): void
    {
        self::$logger = new Logger(true); // Assuming Logger is already defined
        switch ($signal) {
            case SIGINT:
            case SIGTERM:
                self::$logger->info("Received signal $signal, shutting down...");
                if (is_file(PID_FILE)) {
                    unlink(PID_FILE);
                }
                exit(0);
            case SIGHUP:
                self::$logger->info("Received SIGHUP: Reloading configuration not implemented.");
                break;
        }
    }
}