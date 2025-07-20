<?php
// Only set CLI-related constants, class autoloaders, or include paths

defined('APP_PATH') || define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
defined('PID_FILE') || define('PID_FILE', APP_PATH . 'server.pid');

const APP_CLI = true; // Optional marker for CLI-safe checks

//require_once APP_PATH . 'config/config.php'; // Only if safe

//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'functions.php';
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.env.php'; // 'constants.php'; // Global constants
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.paths.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.runtime.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.url.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.app.php';

// or manually load classes in order if you don't want auto-loading

