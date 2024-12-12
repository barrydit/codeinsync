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

// Do not include index.php here at the end, $_ENV is not available in index.php ... to be figured out.
//PHP_SAPI === 'cli' ?: require_once APP_PATH . APP_BASE['public'] . 'index.php';

//dd(get_defined_constants(true)['user']);

//dd(APP_ROOT, false);

//dd($_SERVER);
//define('PHP_EXEC', $_ENV['COMPOSER']['PHP_EXEC'] ?? '/usr/bin/php'); // const PHP_EXEC = 'string only/non-block/ternary';