<?php

/**
 * constants.exec.php
 *
 * Defines paths to runtime executables used by the application.
 * Must be included after APP_IS_CLI, APP_PATH, etc.
 */

$executables = [
    'php',
    'node',
    'composer',
    'npm',
    'phpunit',
    'git'
];

foreach ($executables as $bin) {
    $const = strtoupper($bin) . '_EXEC';

    if (!defined($const)) {
        $path = trim(@shell_exec("which $bin") ?? '');

        if ($path && is_executable($path)) {
            define($const, $path);
        } else {
            define($const, null); // Still define it for reference
        }
    }
}
