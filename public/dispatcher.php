<?php
/*
define('APP_MODE', 'socket');      // For socket server boot
define('APP_MODE', 'cli');         // CLI tasks
define('APP_MODE', 'web');         // Main website front controller
define('APP_MODE', 'dispatcher');  // API router / UI-only context
define('APP_MODE', 'test');        // For PHPUnit or test harness
*/
const APP_MODE = 'dispatcher'; // Prevent full bootstrap
require_once __DIR__ . '/../bootstrap/bootstrap.php';

// App name/path from query
$appPath = $_GET['app'] ?? null;

if (!$appPath) {
    http_response_code(400);
    echo json_encode(['error' => 'No app specified']);
    exit;
}

// Resolve app file safely
$resolvedPath = realpath(__DIR__ . '/../app/' . $appPath . '.php');
$appRoot = realpath(__DIR__ . '/../app');

if (!$resolvedPath || strpos($resolvedPath, $appRoot) !== 0 || !is_file($resolvedPath)) {
    http_response_code(404);
    echo json_encode(['error' => "App {$appPath} not found"]);
    exit;
}

// Init structure
$UI_APP = [
    'slug' => basename(str_replace('\\', '/', $appPath)), // <- short name, safe for DOM
    'style' => '',
    'body' => '',
    'script' => '',
];

// Load app file (should populate $UI_APP parts)
require $resolvedPath;

// Always return JSON
header('Content-Type: application/json');
echo json_encode($UI_APP, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;

//dd(get_required_files());