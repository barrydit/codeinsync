<?php
declare(strict_types=1); // First Line Only!

require_once realpath(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');

//die(var_dump(get_required_files()));

ini_set('error_log', is_dir(dirname($path = __DIR__ . DIRECTORY_SEPARATOR . 'server.log')) ? $path : 'server.log');
ini_set('log_errors', 'true');

define('PID_FILE', /*getcwd() . */(!defined('APP_PATH') ? __DIR__ . DIRECTORY_SEPARATOR : APP_PATH ) . 'server.pid');

if (file_exists(PID_FILE)) {
  $pid = (int) file_get_contents(PID_FILE);
  //unlink(PID_FILE);
  if (strpos(PHP_OS, 'WIN') === 0) {
    exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\" >NUL", $output, $status);
    if ($status === 0) {
      error_log("Server is already running with PID $pid\n");
      echo "Server is already running with PID $pid\n";
      exit(1);
    }
  } else {
    if (posix_kill($pid, 0)) {
      error_log("Server is already running with PID $pid\n");
      echo "Server is already running with PID $pid\n";
      exit(1);
    }
  }
}

file_put_contents(PID_FILE, getmypid());

set_time_limit(0);

//dd(get_defined_constants()); // get_required_files()

$address = '0.0.0.0';
$port = 12345;

function clientInputHandler($input) {
    error_log('Client [Input]: ' . trim($input));
    echo 'Client [Input]: ' . trim($input) . "\n";
    //$input = trim($input);
    $output = '';
    if (preg_match('/^cmd:\s*(date(\?|)|what\s+is\s+the\s+date(\?|))?(?=\r?\n$)/si', $input, $matches)) { 
      $output = 'The date is: ' . date('Y-m-d');
    } elseif (preg_match('/^cmd:\s*(get\s+defined\s+constants)?(?=\r?\n$)/si', $input, $matches)) { 
      $output = var_export(get_defined_constants(), true);
    } elseif (preg_match('/^cmd:\s*(get\s+(required|included)\s+files)?(?=\r?\n$)/si', $input, $matches)) { 
      $output = var_export(get_required_files(), true);
      dd($output, false);
    } elseif (preg_match('/cmd:\s(.*)?(?=\r?\n$)/s', $input, $matches)) { // cmd: composer update
        $cmd = $matches[1];
        $output = trim(shell_exec(/*$cmd*/ 'echo $PWD'));
        $output .= ' cmd: ' . $cmd;
    } else {
        // Process the request and send a response
        $output = 'Hello, client!';
    }

    //$_POST['cmd'] = $cmd;
    
    //require_once('public/app.console.php');
    error_log('Client [Output]: ' . $output);
    echo 'Client [Output]: ' . $output . "\n";
    return $output;
}

//die(var_dump(stream_get_wrappers()));

$running = true;

//use Logger;
//use Shutdown;

Logger::init();

  // ps aux | grep server.php
  // kill -SIGTERM <PID>

  // Signal handler to gracefully shutdown
function signalHandler($signal) {
  global $running, $server;
  switch ($signal) {
    case SIGTERM:
    case SIGINT:
      echo "Shutting down server...\n";
      $running = false;
      fclose($server);
      unlink(PID_FILE);
      exit(1);
  }
}
// Register signal handler
pcntl_signal(SIGTERM, 'signalHandler');
pcntl_signal(SIGINT, 'signalHandler');

try {
  // Check if the stream wrapper for TCP is available
  if (in_array('tcp', stream_get_wrappers())) {

  // Create a TCP/IP server socket
    if (!$socket = @stream_socket_server('tcp://' . $address . ':' . $port, $errno, $errstr)) {
      echo "Error: Unable to create server socket: $errstr ($errno)\n";
      unlink(PID_FILE);
      throw new Exception("Could not create server socket: $errstr ($errno)");
    }

    while ($running) {
      $client = @stream_socket_accept($socket, -1);
  
      if ($client) {
          // Read data from the client
          $response = clientInputHandler(fread($client, 1024));
  
          fwrite($client, $response);
          // Close the client connection
          fclose($client);
      }
  
      // Dispatch signals
      pcntl_signal_dispatch();
  
      // Sleep for 1 second
      sleep(1);
    }
  } else if (extension_loaded('sockets')) {
    // Using Sockets Extension for creating server socket.
    if (!$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
      echo "Error: Unable to create server socket: \n";
    }

    if (!@socket_bind($socket, $address, $port)) {
      throw new Exception("Could not bind to socket: " . socket_strerror(socket_last_error()));
    }

    if (!@socket_listen($socket)) {
      throw new Exception("Could not listen on socket: " . socket_strerror(socket_last_error()));
    }

    echo "Server started on $address:$port\n";

    while ($running) {
      $client = @socket_accept($socket);
      if ($client) {
        // Handle client requests here
        $response = clientInputHandler(socket_read($client, 1024));
        @socket_write($client, $response);
        @socket_close($client);
      }
  
      // Dispatch signals
      pcntl_signal_dispatch();
  
      // Sleep for 1 second
      sleep(1);
    }

    socket_close($socket);
  } else {
    echo "Neither sockets or TCP stream wrapper are available.\n";
    unlink(PID_FILE);
    exit(1);
  }
} catch (Exception $e) {
    Logger::error($e->getMessage());
    Shutdown::triggerShutdown($e->getMessage());
} finally {
    if (isset($socket)) {
        if (is_resource($socket)) {
            if (get_resource_type($socket) == 'stream') {
                fclose($socket);
            } else {
                socket_close($socket);
            }
        }
    }
}


unlink(PID_FILE);
