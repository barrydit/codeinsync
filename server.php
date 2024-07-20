<?php
declare(strict_types=1); // First Line Only!

require_once realpath(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');

//dd('test');

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

$address = APP_HOST ?? '0.0.0.0';
$port = APP_PORT ?? 8080;

function clientInputHandler($input) {
    if ($input == '') return;
    error_log('Client [Input]: ' . trim($input));
    echo 'Client [Input]: ' . trim($input) . "\n";
    //$input = trim($input);
    $output = '';
    if (preg_match('/^cmd:\s*(date(\?|)|what\s+is\s+the\s+date(\?|))?(?=\r?\n$)/si', $input, $matches)) { 
      $output = 'The date is: ' . date('Y-m-d');
    } elseif (preg_match('/^cmd:\s*(get\s+defined\s+constants)?(?=\r?\n$)/si', $input, $matches)) { 
      $output = var_export(get_defined_constants(true)['user'], true);
    } elseif (preg_match('/^cmd:\s*(get\s+(required|included)\s+files)?(?=\r?\n$)/si', $input, $matches)) { 
      $output = var_export(get_required_files(), true);
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

//use Logger; // use when not using composer
//use Shutdown;

Logger::init();

  // ps aux | grep server.php
  // kill -SIGTERM <PID>
  // kill -SIGINT <PID>
  // kill -SIGSTOP <PID>
  // kill -SIGCONT <PID>
  
  // [1]+  Stopped                 php server.php
  // kill -SIGKILL / -9 <PID>
  // [1]+  Killed                  php server.php

  // Signal handler to gracefully shutdown
function signalHandler($signal) {
  global $running, $server;
  switch ($signal) {
    case SIGTERM:
    case SIGINT:
      echo "Shutting down server...\n";
      $running = false;
      //fclose($server);
      unlink(PID_FILE);
      exit(1);
  }
}
// Register signal handler
pcntl_signal(SIGTERM, 'signalHandler');
pcntl_signal(SIGINT, 'signalHandler');

// Define some example notification functions
function notifyUser1()
{
    echo "Notifying user 1...\n";
}

//function notifyUser2()
//{
//    echo "Notifying user 2...\n";
//}

// Create Notification objects
$notification1 = new Notification('notifyUser1', true, 300); // Repeatable every 5 minutes
//$notification2 = new Notification('notifyUser2', false); // One-time notification

// Create NotificationManager and add notifications
$manager = new NotificationManager();
$manager->addNotification($notification1);
//$manager->addNotification($notification2);


// server.php
// composer require cboden/ratchet
// Exception: Interface "Ratchet\MessageComponentInterface" not found

//get_included_files()[0] == 
if (is_dir(__DIR__ . '/vendor/cboden/ratchet') && !empty(glob(__DIR__ . '/vendor/cboden/ratchet/')) && file_exists(__DIR__ . '/vendor/autoload.php')) {
  (file_exists(__DIR__ . '/vendor/autoload.php'))
    and require __DIR__ . '/vendor/autoload.php';
  require_once realpath(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class.websocketserver.php');
} else
  try {
  // Check if the stream wrapper for TCP is available
  //if (in_array('tcp', stream_get_wrappers())) {
  // Create a TCP/IP server socket
    if (!$socket = @stream_socket_server('tcp://' . $address . PATH_SEPARATOR . $port, $errno, $errstr)) {
      echo "Error: Unable to create server socket: $errstr ($errno)\n";
      unlink(PID_FILE);
      throw new Exception("Could not create server socket: $errstr ($errno)");
    }
    
    // Set the socket to non-blocking mode
    stream_set_blocking($socket, false);
    echo 'TCP was '. (in_array('tcp', stream_get_wrappers()) ? '' : 'NOT ') . 'found in stream_get_wrappers()' . "\n";
    echo "(Stream) Server started on $address:$port\n";

    while ($running) {
      $manager->checkNotifications();
      ($client = @stream_socket_accept($socket, -1))
        and print 'Client Connected: ' . "\n";
  
      if ($client) {
          // Read data from the client
          $response = clientInputHandler(fread($client, 1024));
  
        // Append notification output to the response
        $response .= "\n" . $manager->getNotificationsOutput();

          fwrite($client, $response);
          // Close the client connection
          fclose($client);
      }
  
      // Dispatch signals
      pcntl_signal_dispatch();
  

      // Sleep for a short time to prevent busy-waiting
      usleep(100000); // 100 ms = 0.1 s | sleep(1);
    }
  if (extension_loaded('sockets')) {
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

    // Set the socket to non-blocking mode
    socket_set_nonblock($socket);

    echo "(Socket) Server started on $address:$port\n";

    while ($running) {
      $manager->checkNotifications();
      ($client = @socket_accept($socket))
        and print 'Client Connected: ' . "\n";

      if ($client) {
        // Handle client requests here
        $response = clientInputHandler(socket_read($client, 1024));
        // Append notification output to the response
        $response .= "\n" . $manager->getNotificationsOutput();

        @socket_write($client, $response);
        @socket_close($client);
      }
  
      // Dispatch signals
      pcntl_signal_dispatch();
  

      // Sleep for a short time to prevent busy-waiting
      usleep(100000); // 100 ms = 0.1 s | sleep(1);
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
