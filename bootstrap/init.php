<?php

use App\Core\Registry;

/*
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}*/

require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'functions.php';

$included = get_required_files();

if (!in_array(APP_PATH . 'bootstrap/bootstrap.php', $included)) {
    // Autoloader (register once)
    if (!class_exists('Sockets')) {
        spl_autoload_register(function ($class) {
            $paths = [
                APP_PATH . 'classes/class.' . strtolower($class) . '.php',
            ];
            foreach ($paths as $file) {
                if (is_file($file)) {
                    require_once $file;
                    return;
                }
            }
        });
    }
}

// Registry-based logger setup
if (!Registry::has('logger')) {
    Registry::set('logger', new Logger());
}

// require_once APP_PATH . 'bootstrap/bootstrap.sockets.php';
