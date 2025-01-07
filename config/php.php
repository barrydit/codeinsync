<?php

require_once 'functions.php';
require_once 'config.php';

if (isset($_ENV['COMPOSER']['PHP_EXEC']) && $_ENV['COMPOSER']['PHP_EXEC'] != '' && !defined('PHP_EXEC'))
    switch (PHP_BINARY) {
        case $_ENV['COMPOSER']['PHP_EXEC']: // isset issue
            define('PHP_EXEC', PHP_BINARY);
            break;
        default:
            define('PHP_EXEC', $_ENV['COMPOSER']['PHP_EXEC'] ?? stripos(PHP_OS, 'LIN') === 0 ? '/usr/bin/php' : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin/psexec.exe -d C:\xampp\php\php.exe -f ');
            break;
    }

if (!defined('PHP_EXEC'))
    define('PHP_EXEC', stripos(PHP_OS, 'LIN') === 0 ? '/usr/bin/php' : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin/psexec.exe -d C:\xampp\php\php.exe -f ');

require_once 'constants.php';

//die(var_dump(get_defined_constants(true)['user']));

// Get all PHP files in the 'classes' directory
$paths = array_filter(glob(__DIR__ . DIRECTORY_SEPARATOR . 'classes/*.php'), 'is_file');

// Define the filenames to be excluded
$excludedFiles = [
    //'class.sockets.php',
    'class.websocketserver.php'
];

// Remove excluded files from $paths
$paths = array_filter($paths, function ($path) use ($excludedFiles) {
    return !in_array(basename($path), $excludedFiles);
});

// Sort $paths alphabetically by filename
usort($paths, function ($a, $b) {
    return strcmp(basename($a), basename($b));
});

// Require each file in $paths
foreach ($paths as $path) {
    if ($resolvedPath = realpath($path)) {
        require_once $resolvedPath;
    } else {
        die(var_dump(basename($path) . ' was not found. file=' . $path));
    }
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
    if (isset($_POST['cmd']) && $_POST['cmd'] != '')
        if (preg_match('/^php\s+(:?(.*))/i', $_POST['cmd'], $match))
            if (preg_match('/^php\s+(?!(-r))/i', $_POST['cmd'])) {
                $match[1] = trim($match[1], '"');
                $output[] = eval ($match[1] . (substr($match[1], -1) != ';' ? ';' : ''));
            } else if (preg_match('/^php\s+(?:(-r))\s+(:?(.*))/i', $_POST['cmd'], $match)) {
                $match[2] = trim($match[2], '"');
                $_POST['cmd'] = 'php -r "' . $match[2] . (substr($match[2], -1) != ';' ? ';' : '') . '"';

                if (!isset($_SERVER['SOCKET']) || !$_SERVER['SOCKET'])
                    exec($_POST['cmd'], $output);
                else {
                    $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";

                    // Send a message to the server
                    $errors['server-2'] = 'Client request: ' . $message = "cmd: " . $_POST['cmd'] . "\n";

                    fwrite($_SERVER['SOCKET'], $message);
                    $output[] = $_POST['cmd'] . ': ';
                    // Read response from the server
                    while (!feof($_SERVER['SOCKET'])) {
                        $response = fgets($_SERVER['SOCKET'], 1024);
                        $errors['server-3'] = "Server responce: $response\n";
                        if (isset($output[end($output)]))
                            $output[end($output)] .= trim($response);
                        //if (!empty($response)) break;
                    }
                }
                //$output[] = $_POST['cmd'];
            }

require_once 'perl.php';

require_once 'python.php';

/* else if (preg_match('/^composer\s+(:?(.*))/i', $_POST['cmd'], $match)) {

 if (!isset($_SERVER['SOCKET']) || !$_SERVER['SOCKET']) {

   //$output[] = dd(COMPOSER_EXEC);
   //$output[] = APP_SUDO . COMPOSER_EXEC['bin'] . ' ' . $match[1];
   $proc=proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' ' . $match[1] . ' --working-dir="' . APP_PATH . APP_ROOT . '"',
   [
     ["pipe", "r"],
     ["pipe", "w"],
     ["pipe", "w"]
   ],
   $pipes);
   [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
   $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (!isset($exitCode) && $exitCode == 0 ? NULL : " Exit Code: $exitCode");
         //$output[] = $_POST['cmd'];        
   //exec($_POST['cmd'], $output);
   //die(var_dump($output));

 } else {

   $errors['server-1'] = "Connected to " . SERVER_HOST . " on port " . SERVER_PORT . "\n";

   // Send a message to the server
   $errors['server-2'] = 'Client request: ' . $message = "cmd: " . $_POST['cmd'] . "\n";

   $output[] = $_POST['cmd'] . ': ';

   //dd($message, false);
   if (isset($_SERVER['SOCKET']) && is_resource($_SERVER['SOCKET'])) {
     switch (get_resource_type($_SERVER['SOCKET'])) {
       case 'stream':
         fwrite($_SERVER['SOCKET'], $message);
         break;
       default:
         socket_write($_SERVER['SOCKET'], $message);
         break;
     }
   }

   // Read response from the server
   while (!feof($_SERVER['SOCKET'])) {
     $response = fgets($_SERVER['SOCKET'], 1024);
       
     $errors['server-3'] = "Server responce: $response\n";
     if (isset($output[end($output)])) $output[end($output)] .= trim($response);
     else $output[1] .= trim($response);
     //if (!empty($response)) break;
   }

   //die(var_dump($output));
 }


} */


// Do not include index.php here at the end, $_ENV is not available in index.php ... to be figured out.
//PHP_SAPI === 'cli' ?: require_once APP_PATH . APP_BASE['public'] . 'index.php';

//dd(get_defined_constants(true)['user']);

//dd(APP_ROOT, false);

//dd($_SERVER);
//define('PHP_EXEC', $_ENV['COMPOSER']['PHP_EXEC'] ?? '/usr/bin/php'); // const PHP_EXEC = 'string only/non-block/ternary';