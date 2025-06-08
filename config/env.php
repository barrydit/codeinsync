<?php

// Only uses functions, no constants yet assumed
if (!function_exists('check_internet_connection')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
}

if (!defined('APP_IS_ONLINE')) {
    define('APP_IS_ONLINE', check_internet_connection());
    define('APP_NO_INTERNET_CONNECTION', !APP_IS_ONLINE);
}