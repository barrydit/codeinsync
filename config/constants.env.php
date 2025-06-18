<?php
require_once 'constants.url.php';
if (defined('APP_ENV') && !is_string(APP_ENV)) {
    $errors['APP_ENV'] = 'App Env: ' . var_export(APP_ENV, true);
} else {
    //define('APP_ENV', getenv('APP_ENV') ?: 'production'); // APP_DEV | APP_PROD
}

define('APP_DEBUG', APP_ENV !== 'production');

// Only uses functions, no constants yet assumed
if (!function_exists('check_internet_connection')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
}

if (!defined('APP_IS_ONLINE')) {
    define('APP_IS_ONLINE', !check_internet_connection());
    define('APP_NO_INTERNET_CONNECTION', !APP_IS_ONLINE);
}