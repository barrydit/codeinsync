<?php
/**
 * bootstrap/dispatcher.php
 *
 * Contract:
 *  - Return TRUE  -> response fully emitted (echo/headers done)
 *  - Return ARRAY -> structured payload; caller may JSON-emit
 *  - Return FALSE/NULL -> not handled; caller continues normal boot
 */
declare(strict_types=1);

// ─────────────────────────────────────────────────────────────────────────────
// 0) Minimal guards so this file can run standalone (e.g., /public/dispatcher.php)
//    If bootstrap already ran, these are no-ops.
// ─────────────────────────────────────────────────────────────────────────────
if (!defined('BOOTSTRAP_PATH')) {
    define('BOOTSTRAP_PATH', rtrim(str_replace('\\', '/', __DIR__), '/') . '/');
}
if (!defined('APP_PATH')) {
    $root = realpath(BOOTSTRAP_PATH . '..') ?: dirname(BOOTSTRAP_PATH);
    define('APP_PATH', rtrim(str_replace('\\', '/', $root), '/') . '/');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', APP_PATH . 'config/');
}
if (!function_exists('app_context') || !function_exists('app_base')) {
    $fn = CONFIG_PATH . 'functions.php';
    if (is_file($fn)) {
        require_once $fn;   // defines app_context(), app_base(), etc.
    }
}
//die(var_dump($_ENV['COMPOSER']['AUTOLOAD']));
// Optional: single autoloader (cheap if already loaded)
$autoload = APP_PATH . 'vendor/autoload.php';
if (is_file($autoload) && isset($_ENV['COMPOSER']['AUTOLOAD']) && $_ENV['COMPOSER']['AUTOLOAD'] === TRUE)
    //if (!class_exists('Composer\Autoload\ClassLoader', false))
    require_once $autoload;


// ─────────────────────────────────────────────────────────────────────────────
// 1) Small utilities
// ─────────────────────────────────────────────────────────────────────────────
// Put this once (e.g., in bootstrap.php after functions are loaded)
if (!function_exists('wants_json_request')) {
    function wants_json_request(): bool
    {
        if (isset($_GET['json']) && $_GET['json'] !== '0')
            return true;
        $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
        if ($accept === '')
            return false;

        // Only prefer JSON when it beats HTML via q-values
        $scores = [];
        foreach (explode(',', $accept) as $p) {
            $p = trim($p);
            if ($p === '')
                continue;
            $mime = $p;
            $q = 1.0;
            if (strpos($p, ';') !== false) {
                [$mime, $params] = array_map('trim', explode(';', $p, 2));
                if (preg_match('/(?:^|;) *q=([0-9.]+)/', $params, $m))
                    $q = (float) $m[1];
            }
            $scores[$mime] = $q;
        }
        $qJson = max($scores['application/json'] ?? 0, $scores['application/*'] ?? 0);
        $qHtml = max($scores['text/html'] ?? 0, $scores['application/xhtml+xml'] ?? 0, $scores['text/*'] ?? 0);
        $qAny = $scores['*/*'] ?? 0;
        return $qJson > $qHtml && $qJson >= $qAny && $qJson > 0;
    }
}

// Use it in bootstrap/dispatcher.php instead of the old closure:
$wantsJson = wants_json_request();

$emitJson = static function ($data): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
};

$safeJoin = static function (string $base, string $rel): string {
    // Normalize and prevent path traversal
    $base = rtrim(str_replace('\\', '/', $base), '/') . '/';
    $rel = ltrim(str_replace('\\', '/', $rel), '/');
    $full = $base . $rel;
    $realBase = realpath($base) ?: $base;
    $realFull = realpath($full) ?: $full; // allow non-existing until .php suffix added
    $realBase = rtrim(str_replace('\\', '/', $realBase), '/') . '/';
    $realFull = str_replace('\\', '/', $realFull);
    return (strpos($realFull, $realBase) === 0) ? $full : $base; // fallback to base if unsafe
};

$includeAndReturn = static function (string $file) {
    // Standardize contract: include file and interpret result
    $result = require $file;

    // If the included file printed its own response and signals TRUE, we’re done.
    if ($result === true) {
        return true;
    }

    // If it returned a string and didn't echo, treat as body we need to print.
    if (is_string($result)) {
        echo $result;
        return true;
    }

    // If it returned array/object, hand back to caller (bootstrap may JSON-emit).
    if (is_array($result) || is_object($result)) {
        return $result;
    }

    // Otherwise, consider it handled if it produced output.
    if (function_exists('headers_sent')) {
        // We can't easily detect prior echo; just fall through as not-fully-handled.
    }
    return null;
};

// ─────────────────────────────────────────────────────────────────────────────
// 2) Inputs
// ─────────────────────────────────────────────────────────────────────────────
$app = $_POST['app'] ?? $_GET['app'] ?? null;
$cmd = $_POST['cmd'] ?? null;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ─────────────────────────────────────────────────────────────────────────────
// 3) Predefined API routes (explicit whitelist)
// ─────────────────────────────────────────────────────────────────────────────
$routes = [
    // /?app=composer -> /api/composer.php
    'composer' => APP_PATH . 'api/composer.php',
    'git' => APP_PATH . 'api/git.php',
    'npm' => APP_PATH . 'api/npm.php',
];

// If alias route requested, dispatch it
if ($app && isset($routes[$app]) && is_file($routes[$app])) {
    $res = $includeAndReturn($routes[$app]);
    if ($res === true) {
        return true;
    }
    if (is_array($res) || is_object($res)) {
        // If called directly (not via bootstrap), JSON-emit.
        if (!defined('APP_MODE') && $GLOBALS['__DISPATCHER_STANDALONE__'] = true) {
            if ($wantsJson) {
                $emitJson($res);
                return true;
            }
        }
        return $res;
    }
    // Fall through otherwise.
}

// ===================== BEGIN ROUTER (drop-in) =====================
$app = $_GET['app'] ?? $_POST['app'] ?? null;
$cmd = $_POST['cmd'] ?? null;

// Helper as a scoped closure (cannot be redeclared)
$load_ui_app = static function (string $app): array {
    $rel = ltrim($app, '/');
    $file = APP_PATH . 'app/' . $rel . '.php';
    if (!is_file($file)) {
        return ['error' => 'App not found', 'app' => $app, 'file' => $file];
    }
    $UI_APP = null;
    $result = require $file;
    if (is_array($result))
        return $result;
    if (isset($UI_APP) && is_array($UI_APP))
        return $UI_APP;
    return ['error' => 'App file did not return a UI payload', 'app' => $app, 'file' => $file];
};

// 1) App route (?app=devtools/directory, tools/registry/composer, etc.)
if (is_string($app) && $app !== '') {
    $payload = $load_ui_app($app);
    return $payload; // [ 'ok' => empty($payload['error']), 'app' => $app, 'data' => $payload, 'meta' => ['loaded_at' => gmdate('c')], ];
}

// 2) Command routes (POST cmd)
if (is_string($cmd) && $cmd !== '') {
    $routes = [
        '/^composer\b/i' => APP_PATH . 'api/composer.php',
        '/^git\b/i' => APP_PATH . 'api/git.php',
        '/^npm\b/i' => APP_PATH . 'api/npm.php',
    ];
    foreach ($routes as $rx => $file) {
        if (preg_match($rx, $cmd)) {
            $res = require $file;
            return is_array($res) ? $res : ['ok' => true, 'output' => (string) $res];
        }
    }
    return ['ok' => false, 'error' => 'Unknown command', 'cmd' => $cmd];
}

// ─────────────────────────────────────────────────────────────────────────────
// 6) Not handled; let bootstrap continue
// ─────────────────────────────────────────────────────────────────────────────
return null;
// ====================== END ROUTER (drop-in) ======================

/*
return (function () {
    // Get app or command
    $app = $_POST['app'] ?? $_GET['app'] ?? null;
    $cmd = $_POST['cmd'] ?? null;

    // Route for predefined apps
    $routes = [
        'composer' => APP_PATH . 'api/composer.php',
        'git' => APP_PATH . 'api/git.php',
        'npm' => APP_PATH . 'api/npm.php',
    ];

    // === 1. If app route matched
    if ($app && isset($routes[$app])) {
        // Can optionally return API output
        return require $routes[$app];
    }

    // === 2. Command pattern routes
    $commandRoutes = [
        '/^git\s+/i' => APP_PATH . 'api/git.php',
        '/^composer\s+/i' => APP_PATH . 'api/composer.php',
        '/^npm\s+/i' => APP_PATH . 'api/npm.php',
        '/^(chdir|cd)\s+/i' => APP_PATH . 'app/directory.php',
        '/^ls\s+/i' => APP_PATH . 'app/list.php',
        '/^php\s+/i' => CONFIG_PATH . 'runtime/php.php',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cmd) {
        foreach ($commandRoutes as $pattern => $handlerFile) {
            if (preg_match($pattern, $cmd)) {
                return require $handlerFile;
            }
        }
    }

    // === 3. No app or command matched
    return [
        'status' => 'error',
        'message' => 'No valid app or command matched.',
        'app' => $app,
        'cmd' => $cmd,
    ];
})();
*/
/*
    * This file is part of the project bootstrap sequence.
    * It handles API routing for specific apps or commands.
    * 
    * - If a valid app is requested, it routes to the corresponding API handler.
    * - If a command is posted, it matches against predefined patterns and routes accordingly.
    * - If no match is found, it returns an error response.

if (!defined('APP_CONTEXT')) {
    define('APP_CONTEXT', PHP_SAPI === 'cli' ? 'cli' : 'www');
}

switch (APP_CONTEXT) {
    case 'cli':
        require_once __DIR__ . '/bootstrap.cli.php';
        break;

    case 'www':

        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'auth.php';

        if ($_SERVER['SCRIPT_NAME'] == '/dispatcher.php') {
            file_exists(CONFIG_PATH . 'constants.env.php') && require_once CONFIG_PATH . 'constants.env.php';

            file_exists(CONFIG_PATH . 'constants.env.php') && require_once CONFIG_PATH . 'constants.url.php';

            file_exists(CONFIG_PATH . 'config.php') && require_once CONFIG_PATH . 'config.php';

            require_once BOOTSTRAP_PATH . 'dispatcher.php';
            require_once __DIR__ . '/../app/tools/code/git.php';

            break;
        }
        dd(get_required_files());

        $app = $_GET['app'] ?? null;

        if (!$app) {
            // No app specified: fallback full preload
            if (!defined('UI_LOADED')) {
                include_once APP_PATH . 'bootstrap/load_ui_apps.php';
            }
            break;
        }

        // Only allow safe filenames
        $app = basename($app);
        $file = APP_PATH . "app/{$app}.php";

        if (!is_file($file)) {
            http_response_code(404);
            echo json_encode(['error' => "App '{$app}' not found"]);
            break;
        }

        // 🚀 NEW: Dynamic UI app format support
        ob_start();
        $UI_APP = ['style' => '', 'body' => '', 'script' => ''];

        include $file;

        ob_end_clean(); // prevent any loose echo output

        header('Content-Type: application/json');
        echo json_encode($UI_APP);
        break;
    // Optional
    case 'php':

        echo 'APP_CONTEXT == ' . APP_CONTEXT;
        dd(get_required_files());
        break;
    // Optional
    case 'socket':
        require_once __DIR__ . '/bootstrap.sockets.php';


        echo 'APP_CONTEXT == ' . APP_CONTEXT;
        dd(get_required_files());
        break;
}

*/