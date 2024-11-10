<?php

require_once 'config.php';
require_once 'functions.php'; 
require_once 'constants.php';


if (isset($_ENV['COMPOSER']['PHP_EXEC']) && $_ENV['COMPOSER']['PHP_EXEC'] != '' && !defined('PHP_EXEC'))
  switch (PHP_BINARY) {
    case $_ENV['COMPOSER']['PHP_EXEC']: // isset issue
        define('PHP_EXEC', PHP_BINARY);
        break;
    default:
        define('PHP_EXEC', $_ENV['COMPOSER']['PHP_EXEC'] ?? stripos(PHP_OS, 'LIN') === 0 ? '/usr/bin/php' : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin/psexec.exe -d C:\xampp\php\php.exe -f ' );
        break;
  }

if (!defined('PHP_EXEC'))
  define('PHP_EXEC', stripos(PHP_OS, 'LIN') === 0 ? '/usr/bin/php' : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin/psexec.exe -d C:\xampp\php\php.exe -f ' );


require_once APP_PATH . APP_BASE['public'] . 'index.php';
//dd(get_defined_constants(true)['user']);

//dd($_SERVER);
//define('PHP_EXEC', $_ENV['COMPOSER']['PHP_EXEC'] ?? '/usr/bin/php'); // const PHP_EXEC = 'string only/non-block/ternary';