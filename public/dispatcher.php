<?php
declare(strict_types=1);

// Hint bootstrap to do a minimal/core load (no heavy/full boot)
defined('APP_MODE') || define('APP_MODE', 'dispatcher');
require_once __DIR__ . '/../bootstrap/bootstrap.php';

/* ───────────────────────────── Helpers ───────────────────────────── */

function json_out(array $payload, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $message, int $code = 400, array $extra = []): never
{
    json_out(['error' => $message] + $extra, $code);
}

// Lazy include of per-feature constants (idempotent)
function load_feature_constants(string $feature): void
{
    static $loaded = [];

    if (isset($loaded[$feature]))
        return;

    $map = [
        'composer' => CONFIG_PATH . 'constants.composer.php',
        'git' => CONFIG_PATH . 'constants.git.php',
        'npm' => CONFIG_PATH . 'constants.npm.php',
        'socket' => CONFIG_PATH . 'constants.socket.php',
    ];

    if (isset($map[$feature]) && is_file($map[$feature])) {
        require_once $map[$feature];
        $loaded[$feature] = true;
    }
}

// Normalize app path → feature slug (last segment)
function feature_from_app(?string $app): ?string
{
    if (!$app)
        return null;
    $base = basename(trim(preg_replace('~^/+|/+$~', '', strtolower($app))));
    return match ($base) {
        'composer', 'git', 'npm', 'socket' => $base,
        default => null,
    };
}

/* ───────────────────────────── Routing ───────────────────────────── */

// Inputs
$app = $_POST['app'] ?? $_GET['app'] ?? null;
$cmd = $_POST['cmd'] ?? $_GET['cmd'] ?? null;

// API routes (feature → api handler)
$apiRoutes = [
    'composer' => APP_PATH . 'api/composer.php',
    'git' => APP_PATH . 'api/git.php',
    'npm' => APP_PATH . 'api/npm.php',
    'socket' => APP_PATH . 'api/socket.php',
];

// 1) Command route: e.g., cmd="git status"
if (is_string($cmd) && $cmd !== '') {
    $commandRoutes = [
        '/^git\s+/i' => ['feature' => 'git', 'path' => $apiRoutes['git']],
        '/^composer\s+/i' => ['feature' => 'composer', 'path' => $apiRoutes['composer']],
        '/^npm\s+/i' => ['feature' => 'npm', 'path' => $apiRoutes['npm']],
    ];

    foreach ($commandRoutes as $re => $info) {
        if (preg_match($re, $cmd)) {

            load_feature_constants($info['feature']);
            // API handlers may echo/return; we simply require and exit.
            require $info['path'];
            exit;
        }
    }

    json_error('Unknown command', 400, ['cmd' => $cmd]);
}

// 2) Feature API via app=feature (e.g., app=git)
if ($app && ($feat = feature_from_app($app)) && isset($apiRoutes[$feat])) {
    load_feature_constants($feat);
    require $apiRoutes[$feat];
    exit;
}

// 3) UI app route (JSON payload expected from app/*.php)
if (!$app) {
    json_error('No app specified');
}

// Resolve and sandbox under /app
$requested = str_replace(['..', '\\'], ['', '/'], $app);       // coarse sanitize
$resolved = realpath(APP_PATH . 'app' . DIRECTORY_SEPARATOR . $requested . '.php');
$appRoot = realpath(APP_PATH . 'app');

if (!$resolved || !$appRoot || strpos($resolved, $appRoot) !== 0 || !is_file($resolved)) {
    json_error("App {$app} not found", 404);
}

// Initialize UI payload the app file can fill/modify
$UI_APP = [
    'slug' => basename(str_replace('\\', '/', $requested)), // short name for DOM
    'style' => '',
    'body' => '',
    'script' => '',
];

//dd($requested);
// Load the UI app
require $resolved;

// Ensure a consistent JSON response shape
if (!is_array($UI_APP)) {
    json_error('UI app did not produce a valid payload', 500, ['app' => $app]);
}

json_out($UI_APP);