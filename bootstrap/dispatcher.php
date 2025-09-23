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

// ───────────────────────── Canonical prelude (standalone-safe) ─────────────────────────
// Ensures constants & ini are loaded BEFORE any app file, even when /public/dispatcher.php is hit directly.
if (!defined('APP_CANONICAL_PRELUDE')) {
    define('APP_CANONICAL_PRELUDE', true);

    // Resolve APP_PATH from this file if not already defined
    if (!defined('APP_PATH')) {
        $__file = __FILE__;
        $__dir = rtrim(str_replace('\\', '/', dirname($__file)), '/') . '/';
        // adjust if your dispatcher lives under APP_PATH.'bootstrap/dispatcher.php'
        define('APP_PATH', dirname($__dir) . '/'); // one level up from /bootstrap/
    }

    // Common paths (idempotent; require_once below is safe if bootstrap already ran)
    if (!defined('CONFIG_PATH'))
        define('CONFIG_PATH', APP_PATH . 'config/');
    if (!defined('WWW_PATH'))
        define('WWW_PATH', APP_PATH . 'public/');
    if (!defined('VENDOR_PATH'))
        define('VENDOR_PATH', APP_PATH . 'vendor/');

    // Helpers first — MUST be vendor-free
    require_once CONFIG_PATH . 'functions.php';

    // Minimal constants (vendor-free)
    require_once CONFIG_PATH . 'constants.env.php';
    require_once CONFIG_PATH . 'constants.paths.php';

    // php.ini tweaks BEFORE vendor/autoload
    require_once APP_PATH . 'bootstrap/php-ini.php';

    // Composer autoload (guarded by env)
    $autoloadFlag = (($_ENV['COMPOSER']['AUTOLOAD'] ?? true) !== false);
    $autoloadFile = VENDOR_PATH . 'autoload.php';
    if ($autoloadFlag && is_file($autoloadFile)) {
        require_once $autoloadFile;
    }

    // Remaining constants (may use vendor)
    require_once CONFIG_PATH . 'constants.runtime.php';
    require_once CONFIG_PATH . 'constants.url.php';
    require_once CONFIG_PATH . 'constants.app.php';

    require_once CONFIG_PATH . 'config.php';
}
// ─────────────────────── End canonical prelude ───────────────────────


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
// 0) Env & Request
// ─────────────────────────────────────────────────────────────────────────────
$isCli = (PHP_SAPI === 'cli');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');

$appParam = isset($_GET['app']) && is_string($_GET['app']) ? trim($_GET['app']) : null;
$partParam = isset($_GET['part']) ? strtolower((string) $_GET['part']) : null;
$hasCmd = isset($_POST['cmd']) && is_string($_POST['cmd']) && $_POST['cmd'] !== '';

// Content negotiation (explicit JSON?)
$acceptsJson = !$isCli && (
    (isset($_GET['json']) && $_GET['json'] !== '0') ||
    strpos($accept, 'application/json') !== false ||
    strpos($accept, 'text/json') !== false ||
    ($partParam === 'json')
);

// explicit JS? (?part=script or JS Accept)
$acceptsJs = !$isCli && (
    strpos($accept, 'text/javascript') !== false ||
    strpos($accept, 'application/javascript') !== false ||
    in_array($partParam, ['script', 'js'], true)
);

// Intent flags: treat API-ish as dispatcher territory
$isApiLikePath = !$isCli && (
    (function ($u) {
        return function_exists('str_starts_with') ? str_starts_with($u, '/api/') : substr($u, 0, 5) === '/api/'; })($uri)
    || isset($_GET['api'])
);

// Decide if dispatcher should handle this request (standalone safe)
$wantsDispatcher = !$isCli && ($hasCmd || $appParam !== null || $isApiLikePath);
if (defined('APP_MODE') && APP_MODE !== 'web') {
    $wantsDispatcher = true;
}
if (!$wantsDispatcher) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "No route matched.";
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// 1) Header helpers
// ─────────────────────────────────────────────────────────────────────────────
$setHeaders = static function (string $ct) {
    if (headers_sent())
        return;
    header('Content-Type: ' . $ct);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Vary: Accept');
};
$emitJson = static function ($payload) use ($setHeaders) {
    $setHeaders('application/json; charset=UTF-8');
    if (is_string($payload)) {
        echo $payload;
        return;
    }
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
};
$emitJs = static function (string $js) use ($setHeaders) {
    $setHeaders('text/javascript; charset=UTF-8');
    echo $js;
};

// ─────────────────────────────────────────────────────────────────────────────
// 2) Inline ROUTER (your logic, inlined here)
//    Contract:
//      - UI app route → return ['style','body','script']
//      - Command route (POST cmd) → return array/scalar (JSON-able)
//      - If it echoes, we capture the buffer below.
// ─────────────────────────────────────────────────────────────────────────────
$obLevel = ob_get_level();
ob_start();
$res = null;

try {
    // ===================== BEGIN ROUTER (inlined) ======================
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
        $res = $payload;
        // fall through to emit rules
    }
    // 2) Command routes (POST cmd)
    elseif (is_string($cmd) && $cmd !== '') {
        $routes = [
            '/^composer\b/i' => APP_PATH . 'api/composer.php',
            '/^git\b/i' => APP_PATH . 'api/git.php',
            '/^npm\b/i' => APP_PATH . 'api/npm.php',
        ];
        $matched = false;
        foreach ($routes as $rx => $file) {
            if (preg_match($rx, $cmd)) {
                $matched = true;
                $r = require $file;
                $res = is_array($r) ? $r : ['ok' => true, 'output' => (string) $r];
                break;
            }
        }
        if (!$matched) {
            $res = ['ok' => false, 'error' => 'Unknown command', 'cmd' => $cmd];
        }
    }
    // 3) Not handled (shouldn’t usually happen because wantsDispatcher=true)
    else {
        $res = null; // will 404 higher up if needed
    }
    // ====================== END ROUTER (inlined) =======================

    $buffer = ob_get_clean();

    //dd(get_required_files());
} catch (\Throwable $e) {
    while (ob_get_level() > $obLevel)
        ob_end_clean();
    throw $e;
}

// ─────────────────────────────────────────────────────────────────────────────
// 3) Selective emit (single authority here)
// ─────────────────────────────────────────────────────────────────────────────

// Commands: always JSON straight through
if ($hasCmd) {
    $payload = ($res !== null && $res !== '') ? $res : ($buffer !== '' ? $buffer : []);
    $emitJson($payload);
    exit;
}

// App/UI routes: expect $res to be ['style','body','script']
$ui = is_array($res) ? $res : [];
$style = isset($ui['style']) ? (string) $ui['style'] : '';
$body = isset($ui['body']) ? (string) $ui['body'] : '';
$script = isset($ui['script']) ? (string) $ui['script'] : '';

// If JS explicitly requested, emit ONLY the script as JavaScript
if ($acceptsJs) {
    $emitJs($script);
    exit;
}

// Otherwise, emit JSON (include script by default; set false to omit)
$includeScriptInJson = true;
$payload = $includeScriptInJson
    ? ['style' => $style, 'body' => $body, 'script' => $script]
    : ['style' => $style, 'body' => $body];

$emitJson($payload);
exit;

// ─────────────────────────────────────────────────────────────────────────────
// 6) Not handled; let bootstrap continue
// ─────────────────────────────────────────────────────────────────────────────
// return null;
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