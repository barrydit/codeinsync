<?php

define('PID_FILE', /*getcwd() . */'C:\xampp\htdocs\server.pid'); 

if (file_exists(PID_FILE)) {
  $pid = file_get_contents(PID_FILE);
  if (strpos(PHP_OS, 'WIN') === 0) {
    exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\" >NUL", $output, $status);
    if ($status === 0) {
      echo "Server is already running with PID $pid\n";
      exit(1);
    }
  } else {
    if (posix_kill($pid, 0)) {
      echo "Server is already running with PID $pid\n";
      exit(1);
    }
  }
}

file_put_contents(PID_FILE, getmypid());

set_time_limit(0);

// 

$address = '0.0.0.0';
$port = 12345;

function clientInputHandler($input) {
    $input = trim($input);
    echo 'Client [Input]: ' . $input . "\n";
    if (preg_match('/cmd:\s(.*)?(?=\r?\n$)/s', $input, $matches)) { // cmd: composer update
        $cmd = $matches[1];
        $output = shell_exec(/*$cmd*/ 'echo $PWD');
        $output .= ' cmd: ' . $cmd;
    } else {
        // Process the request and send a response
        $output = 'Hello, client!' . "\n";
    }
    echo 'Client [Output]: ' . $output;
    return $output;
  }

//die(var_dump(stream_get_wrappers()));

$running = true;

// Check if the stream wrapper for TCP is available
if (in_array('tcp', stream_get_wrappers())) {

  // Create a TCP/IP server socket
  if (!$server = stream_socket_server('tcp://' . $address . ':' . $port, $errno, $errstr)) {
    echo "Error: Unable to create server socket: $errstr ($errno)\n";
    unlink(PID_FILE);
    exit(1);
  }

  //require('config/config.php');

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

  while ($running) {
    $client = @stream_socket_accept($server, -1);

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
    //if (!is_resource($connection = @fsockopen('localhost', 80)))
    //  break;
  }
} else if (extension_loaded('sockets')) {
    // Using Sockets Extension for creating server socket.
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_bind($sock, $address, $port);
    socket_listen($sock);

    if (!$sock) {
      echo "Error: Unable to create server socket: \n";
      unlink(PID_FILE);
      exit(1);
    }

    echo "Server started on $address:$port\n";

    while ($running) {
        $client = socket_accept($sock);
        $response = clientInputHandler(socket_read($client, 1024));
        socket_write($client, $response);
        socket_close($client);
    }
    socket_close($sock);
} else {
  echo "Neither sockets or tcp streams were detected.";
}

unlink(PID_FILE);

/*
    // Check if the port is open
    $connection = @fsockopen($host, $port);

    if (is_resource($connection)) {
        echo "Port $port is open.\n";
        fclose($connection);
    } else {
        echo "Port $port is not open. Performing actions...\n";
        // Perform your long-running actions here
        // For example, calling a function to process data
        performLongRunningTask();
    }
*/

// fclose($server);
/*function performLongRunningTask() {
    // Simulate a long-running task
    echo "Starting long-running task...\n";
    sleep(10); // Simulate a task that takes 10 seconds
    echo "Long-running task completed.\n";
}*/
