#!/usr/bin/env php
<?php
declare(/*strict_types=1,*/ ticks=1); // First Line Only!

define('APP_PATH', __DIR__ . '/');
define('PID_FILE', APP_PATH . 'server.pid');
define('SERVER_SCRIPT', __FILE__);
const SERVER_HOST = '127.0.0.1';
const SERVER_PORT = 9000;

// Minimal includes (no bootstrap.php for CLI)
//require_once APP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class.logger.php';
//require_once APP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class.socketserver.php';
//require_once APP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class.serverdaemon.php';
//require_once APP_PATH . 'classes' . DIRECTORY_SEPARATOR . 'class.clienthandler.php';
require_once APP_PATH . 'autoload.php';
require_once APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.cli.php';
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php';

// Parse CLI arguments
$argv = $_SERVER['argv'] ?? [];
$command = $argv[1] ?? 'run';
$verbose = in_array('--verbose', $argv);

$logger = new Logger($verbose);

// Set up Linux signal handling and process title
ServerDaemon::initializeSignalHandlers();
ServerDaemon::setProcessTitle('php-socket-server');

// Daemon instance
$daemon = new ServerDaemon(SERVER_SCRIPT, PID_FILE, $logger);

// Main command switch
switch ($command) {
    case 'start':
        $logger->info('Booting up...');
        $daemon->start();
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
        $logger->info('Starting SocketServer...');
        $server = new SocketServer(SERVER_HOST, SERVER_PORT, PID_FILE, $logger);
        $server->start(); // Blocks here � main loop

        dd(get_required_files());
        break;

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