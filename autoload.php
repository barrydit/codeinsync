<?php

// ------------------------------------------------------
// Path Constants (fully resolved, consistent trailing slashes)
// ------------------------------------------------------

defined('APP_PATH') || define(
    'APP_PATH',
    rtrim(realpath(__DIR__), '/\\') . DIRECTORY_SEPARATOR
);

defined('APP_SELF') || define(
    'APP_SELF',
    realpath($_SERVER['SCRIPT_FILENAME'] ?? (get_included_files()[0] ?? __FILE__))
);

defined('PATH_PUBLIC') || define(
    'PATH_PUBLIC',
    APP_PATH . 'public' . DIRECTORY_SEPARATOR
);

defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . 'config' . DIRECTORY_SEPARATOR);
defined('BOOTSTRAP_PATH') || define('BOOTSTRAP_PATH', APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR);

// ------------------------------------------------------
// Context Detection (cli, socket, or www)
// ------------------------------------------------------

defined('APP_CONTEXT') || define('APP_CONTEXT', match (true) {
    PHP_SAPI === 'cli' && isset($argv[1]) && str_starts_with($argv[1], 'socket') => 'socket',
    PHP_SAPI === 'cli' => 'cli',
    default => 'www',
});

// ------------------------------------------------------
// Autoloader Registration
// ------------------------------------------------------
defined('BOOTSTRAP_PATH') || define('BOOTSTRAP_PATH', APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR);

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
    $error = error_get_last();

    if ($error) {
        $message = sprintf(
            "Fatal error: %s in %s on line %d",
            $error['message'],
            $error['file'],
            $error['line']
        );
        error_log(date('c') . " | " . $message);
        file_put_contents(APP_PATH . 'error_log', date('c') . " | " . $message . PHP_EOL, FILE_APPEND);
        return;
    }

    defined('APP_START') || define('APP_START', $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
    $execTime = round((defined('APP_END') ? APP_END : microtime(true)) - APP_START, 3);

    error_log("APP_CONTEXT: " . APP_CONTEXT);
    // error_log("Execution time: {$execTime}s | CWD: " . getcwd());
});

// ------------------------------------------------------
// Bootstrap Sequence
// ------------------------------------------------------

require_once BOOTSTRAP_PATH . 'bootstrap.php';


/*if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['composer']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^composer\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'public' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'composer.php';
    if (isset($_POST['git']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^git\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'git.php';
    if (isset($_POST['npm']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^npm\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'npm.php';
    if (isset($_POST['python']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^python\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'python.php';
    if (isset($_POST['perl']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^perl\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'perl.php';
    //if (isset($_POST['php']) || preg_match('/^php\s*(:?.*)/i', $_POST['cmd'], $match))
    require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'php.php';
}*/

//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'routes.php';