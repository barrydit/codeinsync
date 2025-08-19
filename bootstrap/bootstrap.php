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

// [2] Normalize process CWD to project root
if (!@chdir(APP_PATH /*. APP_ROOT*/)) {
    throw new RuntimeException("Failed to chdir() to APP_PATH: " . APP_PATH);
}

!defined('APP_CWD') and define('APP_CWD', getcwd()); // optional: for debugging/logs

// Ensure assertions throw exceptions globally
//ini_set('assert.exception', 1);

// Verify setting is applied (optional in production)
//if (!ini_get('assert.exception')) {
//    die('assert.exception is not enabled');
//}

// [3] EARLY INCLUDES (Still Required Everywhere)
$autoloadPath = APP_PATH . 'autoload.php';

if (!file_exists($autoloadPath)) {
    die("Autoload file not found at: {$autoloadPath}");
}

/*
if (isset($_GET['project']))
    if (isset($_GET['app']) && $_GET['app'] == 'project')
        require_once 'app' . DIRECTORY_SEPARATOR . 'project.php';*/

require_once $autoloadPath; //file_exists(APP_PATH . 'autoload.php') && require_once APP_PATH . 'autoload.php';

require_once CONFIG_PATH . 'environment.php';
require_once CONFIG_PATH . 'constants.env.php';
require_once CONFIG_PATH . 'functions.php';
require_once CONFIG_PATH . 'constants.paths.php';
require_once CONFIG_PATH . 'auth.php';

/* require_once CONFIG_PATH . 'constants.runtime.php';
require_once CONFIG_PATH . 'constants.exec.php';
require_once CONFIG_PATH . 'constants.url.php';   // ← add this back
require_once CONFIG_PATH . 'constants.app.php'; */

// [3] Bootstrap protection

$context = defined('APP_CONTEXT') ? APP_CONTEXT : 'web';
$dir = rtrim(APP_PATH, '/\\') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

// --- helpers ---------------------------------------------------------------
$requireIf = static function (string $path): void {
    if (is_file($path))
        require_once $path;
};

$requireNamed = static function (string $name) use ($dir, $requireIf): void {
    $requireIf("$dir$name");
};

// --- priority map (lower = earlier) ---------------------------------------
$PRIORITY = [
    'constants.env.php' => 10,
    'constants.paths.php' => 20,
    'constants.runtime.php' => 30,
    'constants.cli.php' => 40,
    'constants.socketLoader.php' => 45,
    'constants.client.php' => 50,
    'constants.exec.php' => 60,
    'constants.url.php' => 70,
    'constants.app.php' => 80,
];

// --- full load or minimal fallback? ---------------------------------------
$needsFullLoad = !defined('APP_BOOTSTRAPPED') && (!defined('APP_MODE') || APP_MODE !== 'dispatcher');

if ($needsFullLoad) {
    // mark bootstrapped
    define('APP_BOOTSTRAPPED', true);

    // dotenv (safe)
    if (class_exists('Dotenv\\Dotenv', false)) {
        Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1))->safeLoad();
    }

    // discover files (constants* plus your extra)
    $files = glob("{$dir}constants*.php") ?: [];
    if (is_file("{$dir}filesystem.ensure.php")) {
        $files[] = "{$dir}filesystem.ensure.php";
    }

    // filter by context (normalize to 'web'/'cli'/'socket')
    $files = array_values(array_filter($files, function ($f) use ($context) {
        $base = basename($f);
        if ($base === 'constants.cli.php' && $context !== 'cli')
            return false;
        if ($base === 'constants.client.php' && $context !== 'web')
            return false; // was 'www'
        if ($base === 'constants.socketLoader.php' && $context !== 'socket')
            return false;
        if ($base === 'constants.composer.php' && $context !== 'web')
            return false; // was 'www'
        if ($base === 'constants.git.php' && $context !== 'web')
            return false; // was 'www'
        if ($base === 'constants.npm.php' && $context !== 'web')
            return false; // was 'www'
        if ($base === 'constants.socket.php' && $context !== 'web')
            return false; // was 'www'
        return true;
    }));

    // sort by priority then name
    usort($files, function ($a, $b) use ($PRIORITY) {
        $A = basename($a);
        $B = basename($b);
        $pa = $PRIORITY[$A] ?? 90;
        $pb = $PRIORITY[$B] ?? 90;
        return ($pa <=> $pb) ?: strcmp($A, $B);
    });

    // require once in order
    foreach ($files as $file) {
        require_once $file;
    }

} else {

    // --- minimal fallback path --------------------------------------------
    // Don’t sweep-load everything. Only the prerequisites needed for URLs,
    // lightweight flags, and app-level constants.
    if (defined('APP_DEBUG') && APP_DEBUG) {
        error_log('[bootstrap] Minimal constants load (already bootstrapped or dispatcher mode).');
    }

    // dotenv is cheap and safe to call here too (it’s no-op if absent)
    if (class_exists('Dotenv\\Dotenv', false)) {
        Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1))->safeLoad();
    }

    // strictly minimal chain — adjust if you need one more file:
    // env -> paths -> runtime -> url -> app
    $requireNamed('constants.env.php');
    //$requireNamed('constants.paths.php');
    //$requireNamed('constants.runtime.php');
    $requireNamed('constants.url.php');
    $requireNamed('constants.app.php');
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