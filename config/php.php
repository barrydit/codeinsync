<?php

// 

switch (PHP_BINARY) {
    case $_ENV['COMPOSER']['PHP_EXEC']: // isset issue
        define('PHP_EXEC', PHP_BINARY);
        break;
    default:
        define('PHP_EXEC', $_ENV['COMPOSER']['PHP_EXEC'] ?? '/usr/bin/php');
        break;
}

//define('PHP_EXEC', $_ENV['COMPOSER']['PHP_EXEC'] ?? '/usr/bin/php'); // const PHP_EXEC = 'string only/non-block/ternary';