<?php
declare(strict_types=1);

// Idempotent: safe if required multiple times in a request
if (defined('APP_KERNEL_LOADED'))
    return;
else
    define('APP_KERNEL_LOADED', true);

// Preconditions: bootstrap/bootstrap.php should have set these.
if (!defined('APP_PATH'))
    throw new RuntimeException('Kernel precondition failed: APP_PATH not defined.');

//if (!defined('APP_BASE'))
//    throw new RuntimeException('Kernel precondition failed: APP_BASE not defined.');
if (!defined('CONFIG_PATH'))
    throw new RuntimeException('Kernel precondition failed: CONFIG_PATH not defined.');
/*
if (!defined('CACHE_PATH'))
    throw new RuntimeException('Kernel precondition failed: CACHE_PATH not defined.');
if (!defined('STORAGE_PATH'))
    throw new RuntimeException('Kernel precondition failed: STORAGE_PATH not defined.');
if (!defined('LOGS_PATH'))
    throw new RuntimeException('Kernel precondition failed: LOGS_PATH not defined.');
if (!defined('TMP_PATH'))
    throw new RuntimeException('Kernel precondition failed: TMP_PATH not defined.');
if (!defined('VIEWS_PATH'))
    throw new RuntimeException('Kernel precondition failed: VIEWS_PATH not defined.');
if (!defined('PLUGINS_PATH'))
    throw new RuntimeException('Kernel precondition failed: PLUGINS_PATH not defined.');
if (!defined('COMMANDS_PATH'))
    throw new RuntimeException('Kernel precondition failed: COMMANDS_PATH not defined.');
if (!defined('APP_ENV'))
    throw new RuntimeException('Kernel precondition failed: APP_ENV not defined.');
if (!defined('APP_DEBUG'))
    throw new RuntimeException('Kernel precondition failed: APP_DEBUG not defined.');
if (!defined('APP_CONTEXT'))
    throw new RuntimeException('Kernel precondition failed: APP_CONTEXT not defined.');
if (!defined('APP_VERSION'))
    throw new RuntimeException('Kernel precondition failed: APP_VERSION not defined.');
if (!defined('APP_RELEASE'))
    throw new RuntimeException('Kernel precondition failed: APP_RELEASE not defined.');
if (!defined('APP_REQUEST_METHOD'))
    throw new RuntimeException('Kernel precondition failed: APP_REQUEST_METHOD not defined.');
if (!defined('APP_IS_CLI'))
    throw new RuntimeException('Kernel precondition failed: APP_IS_CLI not defined.');
if (!defined('APP_IS_WEB'))
    throw new RuntimeException('Kernel precondition failed: APP_IS_WEB not defined.');  */

// Register error/exception handlers (opt-in via env)


// Register custom error and exception handlers
if (function_exists('app_error_handler'))
    set_error_handler('app_error_handler');
else
    set_error_handler([Shutdown::class, 'handleError']);

set_exception_handler(function (Throwable $e) { // [Shutdown::class, 'handleException']
    $msg = sprintf(
        'Uncaught %s: %s in %s:%d',
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    if (defined('APP_DEBUG') && APP_DEBUG) {
        if (PHP_SAPI === 'cli')
            fwrite(STDERR, $msg . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
        else
            echo '<pre style="color:#f66">' . $msg . "\n" . $e->getTraceAsString() . '</pre>';
    }
});

// ------------------------------------------------------
// Autoloader Registration (PSR-4 + Legacy Fallback)
// ------------------------------------------------------

function autoload_class(string $class): void
{
    $prefix = 'App\\';
    $baseDir = APP_PATH . 'classes/';

    // PSR-4-style loading
    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require_once $file;
            return;
        }
    }

    // Legacy fallback support
    $lowerClass = strtolower($class);
    $fallbackPaths = [
        APP_PATH . "classes/class.$lowerClass.php",
        APP_PATH . "interfaces/$class.php",
        APP_PATH . "commands/$class.php",
    ];

    foreach ($fallbackPaths as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }

    error_log("Autoloader could not find class: $class");
}

spl_autoload_register('autoload_class');

// ------------------------------------------------------
// Shutdown Hook (for fatal error logging & profiling)
// ------------------------------------------------------

register_shutdown_function(function () {
    //Shutdown::triggerShutdown('');  //

    //if (!empty($_ENV))
    //  Shutdown::saveEnvToFile();
    is_file(Shutdown::getEnvJsonPath()) && Shutdown::unlinkEnvjson();
    if ($error = error_get_last()) {
        $message = sprintf(
            "Fatal error: %s in %s on line %d",
            $error['message'],
            $error['file'],
            $error['line']
        );

        file_put_contents(APP_PATH . 'error_log', date('c') . " | " . $message . PHP_EOL, FILE_APPEND);
        error_log(date('c') . " | " . $message);
        return;
    }

    !defined('APP_START') and define('APP_START', $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
    $execTime = round((defined('APP_END') ? APP_END : microtime(true)) - APP_START, 3);

    error_log("APP_CONTEXT: " . APP_CONTEXT);
    // error_log("Execution time: {$execTime}s | CWD: " . getcwd());

    // check_internet_connection();
    // Shutdown::handleParseError();
});
// ------------------------------------------------------
// Path Resolution Logic

return true;