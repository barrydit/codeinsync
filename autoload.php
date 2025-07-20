<?php

!defined('APP_PATH') and
    define('APP_PATH', realpath(__DIR__ /*. '..'*/) . DIRECTORY_SEPARATOR);

!defined('APP_SELF') ? define('APP_SELF', basename($_SERVER['SCRIPT_FILENAME'] ?? '')) : '';

!defined('PATH_PUBLIC') ? define('PATH_PUBLIC', basename(APP_PATH . 'public/index.php' ?? '')) : '';

// Resolve CONFIG_PATH, APP_PATH, etc., if not already set
defined('CONFIG_PATH') or define('CONFIG_PATH', APP_PATH . 'config');
defined('BOOTSTRAP_PATH') or define('BOOTSTRAP_PATH', APP_PATH . 'bootstrap');

if (!defined('APP_CONTEXT')) {
    define('APP_CONTEXT', match (true) {
        PHP_SAPI === 'cli' && isset($argv[1]) && str_starts_with($argv[1], 'socket') => 'socket',
        PHP_SAPI === 'cli' => 'cli',
        //PHP_SAPI !== 'cli' => 'socket', // Default to 'cmd' for web requests
        default => 'www',
    });
}

require_once APP_PATH . 'autoload-trap.php'; // log or intercept paths

// Basic manual class loader (if no composer autoload)
spl_autoload_register(function ($class) {
    // PSR-4-style autoload for namespaced classes like App\Core\Registry
    $prefix = 'App\\';
    $baseDir = APP_PATH . 'classes/';

    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix)); // e.g. Core\Registry
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require_once $file;
            return;
        }
    }

    // Legacy support for global class.classname.php, interfaces/, commands/
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

    // Optional: Log failure to load class
    error_log("Autoloader could not find class: $class");
});


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

require_once APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'dispatcher.php';

//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'routes.php';