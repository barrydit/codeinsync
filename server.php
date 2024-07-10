<?php

require('config/config.php');

$address = '0.0.0.0';
$port = 12345;

function clientInputHandler($input) {
    if (preg_match('/cmd:\s(.*)?(?=\r?\n$)/s', $input, $matches)) { // cmd: composer update
        $cmd = $matches[1];
        $output = shell_exec(/*$cmd*/ 'echo $PWD');
        $output .= ' cmd: ' . $cmd;
    } else {
        // Process the request and send a response
        $output = "Hello, client! You said: " . $input . "\n";
    }
    return $output;
  }

// Check if the stream wrapper for TCP is available
if (in_array('tcp', stream_get_wrappers())) {

  // Create a TCP/IP server socket
  (!$server = stream_socket_server('tcp://' . $address . ':' . $port, $errno, $errstr))
    and die("Error: Unable to create server socket: $errstr ($errno)\n");

  // ps aux | grep server.php
  // kill -SIGTERM <PID>

  $running = true;

  // Signal handler to gracefully shutdown
  function signalHandler($signal) {
    global $running, $server;
    switch ($signal) {
        case SIGTERM:
        case SIGINT:
            echo "Shutting down server...\n";
            $running = false;
            fclose($server);
            exit;
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
  }
} else if (extension_loaded('sockets')) {
    // Using Sockets Extension for creating server socket.
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_bind($sock, $address, $port);
    socket_listen($sock);
    //echo "Server started on $address:$port\n";

    while ($running) {
        $client = socket_accept($sock);
        $response = clientInputHandler(socket_read($client, 1024));
        socket_write($client, $response);
        socket_close($client);
    }
    socket_close($sock);
}

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
