<?php

if (!defined('APP_CONTEXT')) {
    define('APP_CONTEXT', PHP_SAPI === 'cli' ? 'cli' : 'www');
}

switch (APP_CONTEXT) {
    case 'cli':
        require_once __DIR__ . '/bootstrap.cli.php';
        break;

    case 'www':

        // Load the main configuration file
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'auth.php';

        if (!isset($_GET['app'])) {
            // fallback to load_ui_apps.php only if no app selected
            require_once __DIR__ . '/../config/load_ui_apps.php';
        } else {
            // Load a specific app, e.g. /app/git.php or /app/dashboard.php
            $app = basename($_GET['app']);
            $file = __DIR__ . '/../app/' . $app . '.php';
            if (is_file($file)) {
                require_once $file;
            } else {
                // fallback, error, or load_ui_apps.php
                foreach (glob(APP_PATH . 'config/load_*.php') as $file) {
                    require_once $file;
                }
            }
        }
        break;
    // Optional
    case 'php':

        echo 'APP_CONTEXT == ' . APP_CONTEXT;
        dd(get_required_files());
        break;
    // Optional
    case 'socket':
        require_once __DIR__ . '/bootstrap.sockets.php';


        echo 'APP_CONTEXT == ' . APP_CONTEXT;
        dd(get_required_files());
        break;
}

