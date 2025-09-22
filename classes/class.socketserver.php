<?php
// classes/class.socketserver.php

class SocketServer
{
    /** @var resource|null */
    private $serverSock = null;
    /** @var array<int, resource> */
    private array $clients = [];
    private string $host;
    private int $port;
    private string $pidFile;
    private ?Logger $logger;

    // Adjust your constructor to match this shape if needed:
    public function __construct(string $host, int $port, string $pidFile, ?Logger $logger = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->pidFile = $pidFile;
        $this->logger = $logger;

        $addr = sprintf('tcp://%s:%d', $host, $port);
        $errno = $errstr = null;

        $this->serverSock = @stream_socket_server(
            $addr,
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
        );

        if (!$this->serverSock) {
            $this->log('error', "Bind failed on $addr: [$errno] $errstr");
            throw new \RuntimeException("Socket bind failed: $errstr", $errno ?: 1);
        }

        stream_set_blocking($this->serverSock, false);
        $this->log('info', "Listening on $addr");
    }

    /* ------------------- NEW: expose the listening socket ------------------- */
    /** @return resource|null */
    public function getServerSocket()
    {
        return $this->serverSock;
    }

    /* --------------- OPTIONAL: expose client sockets to the daemon ---------- */
    /** @return array<int, resource> */
    public function getClientSockets(): array
    {
        return $this->clients;
    }

    /* ------------------- NEW: non-blocking accept + tick -------------------- */
    public function accept(): void
    {
        if (!$this->serverSock)
            return;
        $c = @stream_socket_accept($this->serverSock, 0);
        if ($c) {
            stream_set_blocking($c, false);
            $this->clients[] = $c;
        }
    }

    /** Do one non-blocking IO pass over clients */
    public function tick(): void
    {
        if (!$this->clients)
            return;

        $read = $this->clients;
        $write = $except = null;

        // Short timeout so we don’t block the daemon
        @stream_select($read, $write, $except, 0, 0);

        foreach ($read as $sock) {
            $line = @fgets($sock);
            if ($line === '' || $line === false) {
                $this->closeClient($sock);
                continue;
            }

            $resp = $this->dispatchCommand(trim($line));
            @fwrite($sock, $resp . "\n");
        }
    }

    /* ------------------- NEW: blocking loop version (fallback) -------------- */
    public function run(): void
    {
        // Simple blocking loop for standalone use
        while (true) {
            $read = $this->clients;
            if ($this->serverSock)
                $read[] = $this->serverSock;
            $write = $except = null;

            @stream_select($read, $write, $except, 1, 0);

            foreach ($read as $sock) {
                if ($sock === $this->serverSock) {
                    $this->accept();
                    continue;
                }
                $line = @fgets($sock);
                if ($line === '' || $line === false) {
                    $this->closeClient($sock);
                    continue;
                }
                $resp = $this->dispatchCommand(trim($line));
                @fwrite($sock, $resp . "\n");
            }
        }
    }

    /* ------------------- NEW: graceful shutdown ----------------------------- */
    public function shutdown(): void
    {
        foreach ($this->clients as $c) {
            @fwrite($c, "server: closing\n");
            @fclose($c);
        }
        $this->clients = [];
        if (is_resource($this->serverSock)) {
            @fclose($this->serverSock);
            $this->serverSock = null;
        }
        // PID file is managed by the daemon; don’t unlink here unless you own it.
    }

    /* ------------------- Command dispatcher (customize) --------------------- */
    private function dispatchCommand(string $line): string
    {
        $parts = preg_split('/\s+/', $line);
        $cmd = strtolower($parts[0] ?? '');
        $args = array_slice($parts, 1);

        switch ($cmd) {
            case 'ping':
                return 'pong';
            case 'status':
                return 'ok app-socket alive';
            // Add your app protocol here:
            // case 'composer': return $this->handleComposer($args);
            default:
                return 'error unknown_command';
        }
    }

    /* ------------------- Helpers ------------------------------------------- */
    private function closeClient($sock): void
    {
        $i = array_search($sock, $this->clients, true);
        if ($i !== false) {
            @fclose($this->clients[$i]);
            array_splice($this->clients, $i, 1);
        }
    }

    private function log(string $level, string $msg): void
    {
        if ($this->logger && method_exists($this->logger, $level)) {
            $this->logger->{$level}($msg);
        }
    }
}