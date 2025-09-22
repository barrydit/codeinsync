#!/usr/bin/env php
<?php
// declare(/*strict_types=1*/); // no ticks
/**
 * server.php
 *
 * Simple TCP server with start/stop/restart/status commands.
 * Usage: php server.php [start|stop|restart|status|run] [--verbose]
 *
 * - 'start'    : Start the server as a background daemon.
 * - 'stop'     : Stop the running server.
 * - 'restart'  : Restart the server.
 * - 'status'   : Check if the server is running.
 * - 'run'      : Run the server in the foreground (for debugging).
 *
 * The server listens for incoming TCP connections and can handle basic commands.
 * It uses a PID file to track the running process.
 *
 * Note: This script requires PHP 7.4 or higher and the sockets extension enabled.
 */

// server.php (top)
defined('APP_PATH') || define('APP_PATH', rtrim(realpath(__DIR__), '/\\') . DIRECTORY_SEPARATOR);

define('PID_FILE', APP_PATH . 'server.pid');
define('SERVER_SCRIPT', __FILE__);
//defined('SERVER_HOST')  or define('SERVER_HOST', '127.0.0.1');
//defined('SERVER_PORT')  or define('SERVER_PORT', 9000);

defined('SERVER_APP_HOST') or define('SERVER_APP_HOST', '127.0.0.1'); // web app traffic
defined('SERVER_APP_PORT') or define('SERVER_APP_PORT', 9000);

defined('SERVER_CMD_HOST') or define('SERVER_CMD_HOST', '127.0.0.1'); // admin commands
defined('SERVER_CMD_PORT') or define('SERVER_CMD_PORT', 9001);

defined('SERVER_CMD_ENABLED') or define('SERVER_CMD_ENABLED', false); // turn admin port off

defined('SERVER_DEBUG') or define('SERVER_DEBUG', false);
//defined('SERVER_LOG')   or define('SERVER_LOG', APP_PATH . 'server.log');

// 1) INI early
require_once APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.cli.php';

// 2) Constants & config (single source of truth)
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php';

// 3) Classes
require_once APP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class.logger.php';
require_once APP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class.socketserver.php';
require_once APP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class.serverdaemon.php';

// CLI args
$argv    = $_SERVER['argv'] ?? [];
$command = $argv[1] ?? 'run';
$verbose = in_array('--verbose', $argv, true);

$logger  = new Logger($verbose);
$daemon  = new ServerDaemon(SERVER_SCRIPT, PID_FILE, $logger);

switch ($command) {
    case 'start':
        $logger->info('Booting up...');
        $daemon->start();   // background daemon
        break;

    case 'stop':
        $daemon->stop();
        break;

    case 'restart':
        $daemon->restart();
        break;

    case 'status':
        $daemon->status();
        break;

    case 'run':
        $logger->info('Starting in foreground...');
        $logger->info('Press Ctrl-C to exit.');
        // $logger->info(dd(get_required_files(), false) ?? '');
        $daemon->run();     // foreground (debug)
        break;
/*
    case 'cmd': 
        $msg = implode(' ', array_slice($argv, 2)) ?: 'status';
        $sock = @stream_socket_client(sprintf('tcp://%s:%d', SERVER_CMD_HOST, SERVER_CMD_PORT), $errno, $errstr, 2);
        if (!$sock) { fwrite(STDERR, "connect failed: $errstr\n"); exit(1); }
        fwrite($sock, $msg . "\n");
        $resp = fgets($sock);
        if ($resp !== false) echo rtrim($resp), PHP_EOL;
        fclose($sock);
        break;
*/
    default:
        echo "Usage: php server.php [start|stop|restart|status|run] [--verbose]\n";
        exit(1);
}
/*
try {
    $server = new SocketServer(SERVER_HOST, SERVER_PORT, PID_FILE, $logger);
    $server->start();
} catch (Throwable $e) {
    $logger->error("Startup failed: " . $e->getMessage());
    exit(1);
}
*/

/* * Simple TCP server script
 * Listens for incoming connections and handles basic commands
 * Commands:
 * - 'ping' responds with 'pong'
 * - 'time' responds with the current server time
 */
/*
function logMessage($msg, $level = 'INFO') {
    $line = "[" . date('Y-m-d H:i:s') . "] [$level] $msg\n";
    file_put_contents(APP_PATH . 'server.log', $line, FILE_APPEND);
}

if (file_exists(PID_FILE)) {
    $pid = (int) file_get_contents(PID_FILE);
    if (posix_kill($pid, 0)) {
        logMessage("Server already running with PID $pid");
        exit(0);
    }
    unlink(PID_FILE);
}

$socket = stream_socket_server("tcp://" . SERVER_HOST . ":" . SERVER_PORT, $errno, $errstr);
if (!$socket) {
    logMessage("Socket Error [$errno]: $errstr", 'ERROR');
    exit(1);
}

file_put_contents(PID_FILE, getmypid());
logMessage("Server started on " . SERVER_HOST . ":" . SERVER_PORT);

register_shutdown_function(function () use ($socket) {
    fclose($socket);
    if (file_exists(PID_FILE)) {
        unlink(PID_FILE);
    }
    logMessage("Server shut down.");
});

while ($conn = @stream_socket_accept($socket)) {
    $msg = trim(fgets($conn));
    logMessage("Received: $msg", 'DEBUG');

    switch ($msg) {
        case 'ping':
            fwrite($conn, "pong\n");
            break;
        case 'time':
            fwrite($conn, date('Y-m-d H:i:s') . "\n");
            break;
        default:
            fwrite($conn, "Unknown command: $msg\n");
    }

    fclose($conn);
} */