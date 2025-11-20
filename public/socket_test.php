<?php
// Simple script to test if a TCP socket server is running on a specified port
// and send a test command to it.
$host = '127.0.0.1';
$port = 9000;
$timeout = 2;

$socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

if (is_resource($socket) || $socket) {
    echo "Port $port is open\n";
    fwrite($socket, "status\n");
    $response = fgets($socket);
    echo "Response: $response\n";
    fclose($socket);
} else {
    echo "Port $port is closed ($errstr)\n";
}