<?php
// bootstrap/bootstrap.php

use App\Core\Registry;

// [1] BASE DEFINITIONS (Unchanged)
!defined('BASE_PATH') and
    define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR) and
    is_string(BASE_PATH) ?: $errors['BASE_PATH'] = "BASE_PATH is not a valid string value.\n";

!defined('APP_PATH') && defined('BASE_PATH') and
    define('APP_PATH', realpath(BASE_PATH . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

defined('CONFIG_PATH') or define('CONFIG_PATH', APP_PATH . 'config' . DIRECTORY_SEPARATOR);

// [2] EARLY INCLUDES (Still Required Everywhere)
$autoloadPath = APP_PATH . 'autoload.php';

if (!file_exists($autoloadPath)) {
    die("Autoload file not found at: {$autoloadPath}");
}

require_once $autoloadPath; //file_exists(APP_PATH . 'autoload.php') && require_once APP_PATH . 'autoload.php';

// [3] Bootstrap protection

if (!defined('APP_BOOTSTRAPPED') && (!defined('APP_MODE') || APP_MODE !== 'dispatcher')) {
    // [7] Load constants and config files
    define('APP_BOOTSTRAPPED', true);

    $constants = [
        'constants.env.php',
        'constants.paths.php',
        'constants.runtime.php',
        'constants.url.php',
        'constants.app.php',
    ];

    foreach ($constants as $file) {
        $path = CONFIG_PATH . $file;
        if (file_exists($path))
            require_once $path;
    }

} else {
    // If already bootstrapped, skip loading constants and config
    $errors['APP_BOOTSTRAPPED'] = 'APP_BOOTSTRAPPED is already defined.';
    file_exists(CONFIG_PATH . 'constants.env.php') && require_once CONFIG_PATH . 'constants.env.php';
    file_exists(CONFIG_PATH . 'constants.url.php') && require_once CONFIG_PATH . 'constants.url.php';
}

$configFile = CONFIG_PATH . 'config.php';
if (is_file($configFile)) {
    require_once $configFile;
}

file_exists(CONFIG_PATH . 'functions.php') && require_once CONFIG_PATH . 'functions.php';

// [4] EARLY EXIT if shallow access (dispatcher.php or GET without app/cmd)

function isShallowDispatcherCall(): bool
{
    return $_SERVER['SCRIPT_NAME'] === '/dispatcher.php'
        && $_SERVER['REQUEST_METHOD'] !== 'POST'
        && !isset($_GET['app'], $_POST['app'], $_POST['cmd']);
}

if (isShallowDispatcherCall()) {
    return;
}

// [5] Unified app param
$app = $_POST['app'] ?? $_GET['app'] ?? null;
$cmd = $_POST['cmd'] ?? null;

// [6] App Route Dispatch (Unchanged)
$routes = [
    'composer' => APP_PATH . 'api/composer.php',
    'git' => APP_PATH . 'api/git.php',
    'npm' => APP_PATH . 'api/npm.php',
];

if ($app && isset($routes[$app])) {
    require_once $routes[$app];
    exit;
}
// [6] App Initialization
//$app_id = 'tools_code_git'; // Unique ID for the app
//$container_id = 'tools_code_git-container'; // Unique ID for the container  

// [7] Contextual Bootstraps
if (defined('APP_CONTEXT') && APP_CONTEXT === 'socket') {
    Registry::set('errors', []);
    Registry::set('logger', new Logger());

    // Optional socket constants
    if (is_file(CONFIG_PATH . 'constants.socket.php')) {
        require_once CONFIG_PATH . 'constants.socket.php';
    }

    // Load socket bootstrap
    require_once APP_PATH . 'bootstrap/bootstrap.sockets.php';

    $GLOBALS['runtime'] = ($_SERVER['REQUEST_METHOD'] === 'GET') ? [
        'socket' => Sockets::getInstance(Registry::get('logger')),
        'pid' => getmypid(),
    ] : [
        'socket' => fsockopen('localhost', 9000),
        'pid' => getmypid(),
    ];
} elseif (APP_CONTEXT === 'php') {
    require_once CONFIG_PATH . 'runtime/php.php';
} elseif (APP_CONTEXT !== 'cli') {
    // [8] Command Dispatch
    $commandRoutes = [
        '/^git\s+/i' => APP_PATH . 'api/git.php',
        '/^composer\s+/i' => APP_PATH . 'api/composer.php',
        '/^npm\s+/i' => APP_PATH . 'api/npm.php',
        '/^(chdir|cd)\s+/i' => APP_PATH . 'app/devtools/directory.php',
        '/^ls\s+/i' => APP_PATH . 'app/list.php',
        '/^php\s+/i' => CONFIG_PATH . 'runtime/php.php',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cmd) {
        foreach ($commandRoutes as $pattern => $handlerFile) {
            if (preg_match($pattern, $cmd)) {
                if (is_file($handlerFile)) {
                    require_once $handlerFile;
                    break;
                }
            }
        }
    }

    // [9] Project/Client/Domain Resolution Logic (Unchanged)
    $projectFolder = 'projects/internal/' . ($_GET['project'] ?? '');
    $projectPath = __DIR__ . '/' . $projectFolder;

    $clientFolder = 'projects/clients/' . ($_GET['client'] ?? '');
    $clientPath = __DIR__ . '/' . $clientFolder;

    function resolveProject($dirs, $requestedProject = null)
    {
        if ($requestedProject)
            foreach ($dirs as $dir)
                if (basename($dir) === $requestedProject)
                    return basename($dir);
        return count($dirs) === 1 ? basename(reset($dirs)) : null;
    }

    function resolveDomain($dirs, $requestedDomain = null)
    {
        if ($requestedDomain)
            foreach ($dirs as $dir)
                if (basename($dir) === $requestedDomain)
                    return basename($dir);
        return count($dirs) === 1 ? $_GET['domain'] = basename(reset($dirs)) : null;
    }

    function resolveClient($clientFolder)
    {
        return is_dir(__DIR__ . '/' . $clientFolder) ? $clientFolder . '/' : '';
    }

    $proj_dirs = array_filter(glob(dirname($projectPath) . '/*'), 'is_dir');
    $dirs = array_filter(glob($clientPath . '/*'), 'is_dir');

    $path = null;
    $project = resolveProject($proj_dirs, $_GET['project'] ?? null);
    $domain = resolveDomain($dirs, $_GET['domain'] ?? null);

    if ($project) {
        $path = $projectFolder . '/';
    } elseif ($domain) {
        $path = rtrim($clientFolder, '/') . '/' . $domain . '/';
    } elseif (!empty($_GET['client'])) {
        $path = resolveClient($clientFolder);
    } elseif (count($dirs) === 1) {
        $path = reset($dirs);
    } else {
        $path = '';
    }

    $path = preg_replace('#' . preg_quote(APP_PATH, '#') . '#', '', $path);
    defined('APP_ROOT') || define('APP_ROOT', !is_dir(APP_PATH . $path) ?: $path);

    // [10] Adjust path if needed
    switch (basename(__DIR__)) {
        case 'public':
            chdir(dirname(__DIR__));
            break;
    }

    // [11] Config Load (Unchanged)
    $configFile = CONFIG_PATH . 'config.php';
    if (is_file($configFile)) {
        require_once $configFile;
    } else {
        die(var_dump($configFile));
    }
}