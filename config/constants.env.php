<?php

// 1. Define APP_ENV safely
if (!defined('APP_ENV')) {
    $env = getenv('APP_ENV');
    define('APP_ENV', is_string($env) && $env !== '' ? $env : 'production'); // Options: production, development, etc.
} elseif (!is_string(APP_ENV)) {
    $errors['APP_ENV'] = 'App Env must be a string: ' . var_export(APP_ENV, true);
}

// 2. Define debug mode
define('APP_DEBUG', APP_ENV !== 'production');

// Fallback detection for host and domain
if (!defined('APP_HOST')) {
    define('APP_HOST', $_SERVER['HTTP_HOST'] ?? '127.0.0.1');
}
if (!defined('APP_DOMAIN')) {
    define('APP_DOMAIN', parse_url('http://' . APP_HOST, PHP_URL_HOST));
}

// 3. Load connection checker if not available
if (!function_exists('check_internet_connection')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
}

// 4. Define online/offline constants
if (!defined('APP_IS_ONLINE')) {
    $online = check_internet_connection();
    define('APP_IS_ONLINE', $online);
    define('APP_NO_INTERNET_CONNECTION', !$online);
}