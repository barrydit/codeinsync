<?php

file_put_contents(
    '/mnt/c/www/error_log',
    date('c') . " | Executed by: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . " | CWD: " . getcwd() . PHP_EOL,
    FILE_APPEND
);