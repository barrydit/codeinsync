<?php
// Only set CLI-related constants, class autoloaders, or include paths

defined('APP_PATH') || define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
defined('PID_FILE') || define('PID_FILE', APP_PATH . 'server.pid');

const APP_CLI = true; // Optional marker for CLI-safe checks

// Access the variables from the parsed .env file
$domain = $_ENV['DOMAIN'] ?? APP_DOMAIN ?? 'localhost';
$defaultUser = $_ENV['SHELL']['DEFAULT_USER'] ?? 'www-data';
$documentRoot = $_ENV['SHELL']['DOCUMENT_ROOT'] ?? $_SERVER['DOCUMENT_ROOT'];
$homePathEnv = $_ENV['SHELL']['HOME_PATH'] ?? $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '';

if (stripos(PHP_OS, 'WIN') === 0) {
    $shell_prompt = 'www-data' . '@' . $domain . PATH_SEPARATOR . (($homePath = realpath($_SERVER['DOCUMENT_ROOT'])) === getcwd() ? '~' : $homePath) . '$ ';
} else if (isset($_SERVER['HOME']) && ($homePath = realpath($_SERVER['HOME'])) !== false && ($docRootPath = realpath($_SERVER['DOCUMENT_ROOT'])) !== false && strpos($homePath, $docRootPath) === 0) {
    $shell_prompt = $_SERVER['USER'] . '@' . $domain . PATH_SEPARATOR . ($homePath == getcwd() ? '~' : $homePath) . '$ ';
} elseif (isset($_SERVER['USER'])) {
    $shell_prompt = $_SERVER['USER'] . '@' . $domain . PATH_SEPARATOR . ($homePath == getcwd() ? '~' : $homePath) . '$ ';
} else {
    $shell_prompt = 'www-data' . '@' . $domain . PATH_SEPARATOR . (getcwd() == '/var/www' ? '~' : getcwd()) . '$ ';
}

//require_once APP_PATH . 'config/config.php'; // Only if safe

//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'functions.php';
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.env.php'; // 'constants.php'; // Global constants
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.paths.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.runtime.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.url.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.app.php';

// or manually load classes in order if you don't want auto-loading

