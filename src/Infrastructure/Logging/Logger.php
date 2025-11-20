<?php
declare(strict_types=1);
// src/Infrastructure/Logging/Logger.php

namespace CodeInSync\Infrastructure\Logging;

final class Logger
{
    private bool $verbose;

    private bool $useErrorLog = false;

    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function log(string $message, string $level = 'INFO'): void
    {
        $output = "[$level] $message\n";

        if ($this->useErrorLog) {
            error_log($output);
        } elseif ($this->verbose)
            echo $output;

        //error_log(trim($output));
    }

    public function info(string $message): void
    {
        $this->log($message, 'INFO');
    }

    public function error(string $message): void
    {
        $this->log($message, 'ERROR');
    }

    public function debug(string $message): void
    {
        $this->log($message, 'DEBUG');
    }
    public function warn(string $message): void
    {
        $this->log($message, 'WARN');
    }
}

/* * Logger class for PHP CLI applications
 * 
 * This class provides a simple logging mechanism that can log messages to a file
 * and optionally echo them to the console if verbose mode is enabled.
 * 
 * Usage:
 * $logger = new Logger(true); // Enable verbose mode
 * $logger->info("This is an info message.");
 * $logger->error("This is an error message.");
 */
/* class Logger
{
    public bool $verbose = false;
    private static string $logFile = APP_PATH . 'server.log';
    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $line = "[$timestamp] [$level] $message" . PHP_EOL;

        // Always log to file
        file_put_contents(self::$logFile, $line, FILE_APPEND); // error_log(trim($line)); // Always logs to PHP error log

        if ($this->verbose) echo $line; // Only echo if verbose is enabled
    }

    public function info(string $message): void
    {
        $this->log($message, 'INFO');
    }

    public function debug(string $message): void
    {
        $this->log($message, 'DEBUG');
    }

    public function error(string $message): void
    {
        $this->log($message, 'ERROR');
    }

    public function warn(string $message): void
    {
        $this->log($message, 'WARN');
    }
} */