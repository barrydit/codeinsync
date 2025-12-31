<?php
declare(strict_types=1); // First Line Only!

if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
    // load_if_file(APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.php'); // constants.php
}

// Timezone (env wins; default your local)
$tz = 'America/Vancouver'; // 'UTC';
if (ini_get('date.timezone') !== $tz ?? false)
    @date_default_timezone_set(is_string($tz) && $tz ? $tz : 'UTC');

// Enable debugging and error handling based on APP_DEBUG and APP_ERROR constants
!defined('APP_ERROR') and define('APP_ERROR', false);

!defined('APP_DEBUG') and define('APP_DEBUG', isset($_GET['debug']) ? TRUE : FALSE);

if (APP_DEBUG || APP_ERROR) {
    $errors['APP_DEBUG'] = "Debugging is enabled.\n";
    $errors['APP_ERROR'] = "Error handling is enabled.\n";
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL/*E_STRICT |*/);

    defined('PHP_ZTS') and $errors['PHP_ZTS'] = "PHP was built with ZTS enabled.\n";
    defined('PHP_DEBUG') and $errors['PHP_DEBUG'] = "PHP was built with DEBUG enabled.\n";
    defined('PHP_VERSION') and $errors['PHP_VERSION'] = "PHP Version: " . PHP_VERSION . "\n";
    // PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION, PHP_EXTRA_VERSION, PHP_VERSION_ID
    defined('PHP_OS') and $errors['PHP_OS'] = "PHP_OS: " . PHP_OS . "\n";
    // PHP_OS_FAMILY
    // PHP_EXEC
    defined('PHP_SAPI') and $errors['PHP_SAPI'] = "PHP_SAPI: " . PHP_SAPI . "\n";
    defined('PHP_BINARY') and $errors['PHP_BINARY'] = "PHP_BINARY: " . PHP_BINARY . "\n";
    defined('PHP_BINDIR') and $errors['PHP_BINDIR'] = "PHP_BINDIR: " . PHP_BINDIR . "\n";
    defined('PHP_CONFIG_FILE_PATH') and $errors['PHP_CONFIG_FILE_PATH'] = "PHP_CONFIG_FILE_PATH: " . PHP_CONFIG_FILE_PATH . "\n";
    defined('PHP_CONFIG_FILE_SCAN_DIR') and $errors['PHP_CONFIG_FILE_SCAN_DIR'] = "PHP_CONFIG_FILE_SCAN_DIR: " . PHP_CONFIG_FILE_SCAN_DIR . "\n";
    defined('PHP_SHLIB_SUFFIX') and $errors['PHP_SHLIB_SUFFIX'] = "PHP_SHLIB_SUFFIX: " . PHP_SHLIB_SUFFIX . "\n";
    defined('PHP_EOL') and $errors['PHP_EOL'] = 'PHP_EOL: ' . json_encode(PHP_EOL) . "\n";
    defined('PHP_INT_MIN') and $errors['PHP_INT_MIN'] = "PHP_INT_MIN: " . PHP_INT_MIN . "\n"; // -/+ 2147483648 32-bit
    defined('PHP_INT_MAX') and $errors['PHP_INT_MAX'] = "PHP_INT_MAX: " . PHP_INT_MAX . "\n"; // -/+ 9223372036854775808 64-bit
    // PHP_INT_SIZE
    defined('PHP_FLOAT_DIG') and $errors['PHP_FLOAT_DIG'] = "PHP_FLOAT_DIG: " . PHP_FLOAT_DIG . "\n";
    defined('PHP_FLOAT_EPSILON') and $errors['PHP_FLOAT_EPSILON'] = "PHP_FLOAT_EPSILON: " . PHP_FLOAT_EPSILON . "\n";
    defined('PHP_FLOAT_MIN') and $errors['PHP_FLOAT_MIN'] = "PHP_FLOAT_MIN: " . PHP_FLOAT_MIN . "\n";
    defined('PHP_FLOAT_MAX') and $errors['PHP_FLOAT_MAX'] = "PHP_FLOAT_MAX: " . PHP_FLOAT_MAX . "\n";
    // PHP_FD_SETSIZE

} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL/*E_STRICT |*/);
}

// 1) Resolve log directory
$logDir = $_ENV['PHP']['LOG_PATH'] ?? '/var/log/';
$logDir = rtrim((string) $logDir, "\\/") . '/';

// Ensure directory exists
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}

// If not writable, fall back to Apache log dir if available
if (!is_writable($logDir)) {
    $apacheDir = getenv('APACHE_LOG_DIR');
    if (is_string($apacheDir) && $apacheDir !== '' && is_dir($apacheDir) && is_writable($apacheDir)) {
        $logDir = rtrim($apacheDir, "\\/") . '/';
    }
}

// 2) Resolve log file name
$logFile = $_ENV['PHP']['ERROR_LOG'] ?? (getenv('PHP_ERROR_LOG') ?: 'php-error.log');
$logFile = ltrim((string) $logFile, "\\/");

// 3) Enable/disable logging (default: true)
$logErrors = filter_var($_ENV['PHP']['LOG'] ?? true, FILTER_VALIDATE_BOOLEAN);

// 4) Apply INI settings
ini_set('log_errors', $logErrors ? '1' : '0');

if ($logErrors) {
    $logPath = "$logDir$logFile";

    // If you only want to set when unset/blank, keep this guard:
    $current = ini_get('error_log');
    if (!is_string($current) || trim($current) === '') {
        ini_set('error_log', $logPath);
    }

    // If you want to ALWAYS force it, use this instead:
    // ini_set('error_log', $logPath);
}

// Set error_log path if unset/empty (or always set it if you prefer)


ini_set('xdebug.debug', '0'); // remote_enable
ini_set('xdebug.mode', 'develop'); // default_enable mode=develop,coverage,debug,gcstats,profile,trace
//ini_set('xdebug.mode', 'profile'); // profiler_enable

putenv("XDEBUG_MODE=off");
// Enable output buffering
ini_set('output_buffering', 'On');

ini_set("include_path", "src"); // PATH_SEPARATOR ;:

// Prevent direct access to the file
$isPhpVersion5OrHigher = version_compare(PHP_VERSION, '5.0.0', '>=');
$includedFilesCount = count(get_included_files());

if ($includedFilesCount === ($isPhpVersion5OrHigher ? 1 : 0))
    exit('Direct access is not allowed.');

/*
$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

$file = $trace['file'];
$line = $trace['line'];

dd("Executing in: $file @ line $line\n"); */