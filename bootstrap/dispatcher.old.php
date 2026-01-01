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

defined('APP_MODE') || define('APP_MODE', 'dispatcher');
require_once __DIR__ . '/bootstrap.php';

// Ensure bootstrap completed
if (!defined('APP_RUNNING')) {
    throw new RuntimeException('Bootstrap did not complete.');
}

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
    require_once dirname(__DIR__) . '/config/functions.php';

    // Minimal constants (vendor-free)
    require_once dirname(__DIR__) . '/config/constants.env.php';
    require_once dirname(__DIR__) . '/config/constants.paths.php';

    // php.ini tweaks BEFORE vendor/autoload
    require_once __DIR__ . '/php-ini.php';

    // Composer autoload (guarded by env)
    $autoloadFlag = (($_ENV['COMPOSER']['AUTOLOAD'] ?? true) !== false);
    $autoloadFile = dirname(__DIR__) . '/vendor/autoload.php';
    if ($autoloadFlag && is_file($autoloadFile))
        require_once $autoloadFile;

    // Remaining constants (may use vendor)
    require_once dirname(__DIR__) . '/config/constants.runtime.php';
    require_once dirname(__DIR__) . '/config/constants.url.php';
    require_once dirname(__DIR__) . '/config/constants.app.php';

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
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoload) && isset($_ENV['COMPOSER']['AUTOLOAD']) && $_ENV['COMPOSER']['AUTOLOAD'] === TRUE)
    //if (!class_exists('Composer\Autoload\ClassLoader', false))
    require_once $autoload;

function is_top_marker_at(string $absPath, array $names, string $rootDir): bool
{
    $root = rtrim(str_replace('\\', '/', $rootDir), '/');
    $rp = str_replace('\\', '/', @realpath($absPath) ?: $absPath);

    // must be inside the browse root
    $prefix = $root . '/';
    if ($rp !== $root && strncmp($rp, $prefix, strlen($prefix)) !== 0)
        return false;

    // relative path from root must be a single segment
    $rel = ltrim(substr($rp, strlen($root)), '/');
    if ($rel === '' || strpos($rel, '/') !== false)
        return false;

    return in_array($rel, $names, true);
}

// Prefer APP_PATH constant; fallback to $_ENV
$APP_PATH = defined('APP_PATH') ? APP_PATH : ($_ENV['APP_PATH'] ?? '/');
// Normalized copy used ONLY for APP_ROOT stripping
$APP_PATH_N = rtrim($APP_PATH, '/\\') . '/';

// ---- Precompute bases -----------------------------------------------------
$BASE_CLIENTS = base_val('clients');
$BASE_PROJECTS = base_val('projects');

$absDir = null;
$ctxRoot = null;    // NEW: context root (for APP_ROOT)
$context = null;

// $nullish = fn($v) => $v === null || $v === '';

$populated = [
    'client' => ($client !== null && $client !== ''),
    'domain' => ($domain !== null && $domain !== ''),
    'project' => ($project !== null && $project !== ''),
    'path' => ($path !== null && $path !== ''),
];

$hasClient = array_key_exists('client', $_GET);
$hasDomain = array_key_exists('domain', $_GET);
$hasProject = array_key_exists('project', $_GET);
$hasPath = array_key_exists('path', $_GET);

// Consider empty path as not present for the 3a base-cases:
$hasNonEmptyPath = $hasPath && $path !== '';

// 3a) ONLY empty client/project → base

// 3a) ONLY empty base listings (presence-aware)
// no params -> app
if (!$hasClient && !$hasDomain && !$hasProject && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N;
    $absDir = $APP_PATH_N;
    $context = 'app';
} elseif ($hasClient && $client === '' && $hasDomain && $domain === '' && !$hasProject && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
    $absDir = $ctxRoot;
    $context = 'clients-base';
} elseif ($hasProject && $project === '' && !$hasClient && !$hasDomain && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_PROJECTS;
    $absDir = $ctxRoot;
    $context = 'projects-base';
} elseif ($hasClient && $client === '' && !$hasProject && !$hasDomain && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
    $absDir = $ctxRoot;
    $context = 'clients-base';
} elseif ($hasDomain && $domain === '' && !$hasClient && !$hasProject && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
    $absDir = $ctxRoot;
    $context = 'clients-base';
}

// 3b) ONLY path → redirect (run only if still undecided)
if ($absDir === null && $populated['path'] && !$populated['client'] && !$populated['domain'] && !$populated['project']) {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $isJson = isset($_GET['json']) || stripos($accept, 'application/json') !== false;

    if (!$isJson) {
        $target = ($_SERVER['SCRIPT_NAME'] ?? '/') . '?' . http_build_query([
            'app' => 'devtools/directory',
            'path' => $path,
        ]);

        $curPath = $_SERVER['SCRIPT_NAME'] ?? '/';
        parse_str($_SERVER['QUERY_STRING'] ?? '', $curQS);
        ksort($curQS);
        $current = $curPath . (empty($curQS) ? '' : '?' . http_build_query($curQS));

        if ($current !== $target && !headers_sent()) {
            header("Location: $target", true, 302);
            exit;
        }
    }
}

/*
if (($populated['path'] ?? false) && count($populated) === 1) {
  $redir = '/?app=' . urlencode('devtools/directory') . '&path=' . urlencode($path);
  if (($_SERVER['REQUEST_URI'] ?? '') !== $redir) {
    header('Location: ' . $redir, true, 302);
    exit;
  }
}*/

// ---- Decide the effective directory (only if not decided yet) ------------
// 3c) Main decision tree (only if still undecided)
if ($absDir === null) {
    // ---- decide the effective directory --------------------------------------
//
// Priority by your rules:
//
// 1) client + domain + optional path:
//    APP_PATH . APP_BASE['clients'] . client . '/' . domain . '/' . path
// 2) domain + path (no client):
//    APP_PATH . APP_BASE['clients'] . domain . '/' . path
// 3) project + path:
//    APP_PATH . APP_BASE['projects'] . project . '/' . path
// 4) only path:
//    APP_PATH . path
//
// Empty fallbacks you specified:
// - client=='' OR domain=='' OR path=='clients/'  => show base clients dir: APP_PATH . APP_BASE['clients']
// - project==''                                    => show base projects dir: APP_PATH . APP_BASE['projects']
//
    if ($hasClient && $client !== '' && $hasDomain && $domain !== '') {
        // client + domain (+ optional path)
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/' . $domain . '/';
        $absDir = $ctxRoot . ($hasPath && $path !== '' ? rtrim($path, '/') . '/' : '');
        $context = 'clients';

    } elseif ($hasClient && $client !== '' && (!$hasDomain || $domain === '') && $hasPath && $path !== '') {
        // client + path (no domain)
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/';
        $absDir = $ctxRoot . rtrim($path, '/') . '/';
        $context = 'clients';

    } elseif ($hasClient && $client !== '' && (!$hasDomain || $domain === '') && (!$hasPath || $path === '')) {
        // client only
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/';
        $absDir = $ctxRoot;
        $context = 'clients';

    } elseif ($hasDomain && $domain !== '') {
        // domain only (+ optional path)
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $domain . '/';
        $absDir = $ctxRoot . ($hasPath && $path !== '' ? rtrim($path, '/') . '/' : '');
        $context = 'clients';

    } elseif ($hasProject && $project !== '') {
        // project (+ optional path)
        $ctxRoot = $APP_PATH_N . $BASE_PROJECTS . $project . '/';
        $absDir = $ctxRoot . ($hasPath && $path !== '' ? rtrim($path, '/') . '/' : '');
        $context = 'projects';

    } elseif (
        ($hasClient && $client === '') ||
        ($hasDomain && $domain === '')
    ) {
        // clients fallbacks
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
        $absDir = $ctxRoot;
        $context = 'clients-base';
    } elseif ($hasPath && $path !== '') {
        // Map virtual labels to configured bases
        $p = trim((string) $path, '/');

        // derive labels from APP_BASE values (handles "../clients/")
        $clientsBaseNorm = rtrim(str_replace('\\', '/', $BASE_CLIENTS), '/');  // "../clients"
        $projectsBaseNorm = rtrim(str_replace('\\', '/', $BASE_PROJECTS), '/'); // "projects"
        $clientsLabel = basename($clientsBaseNorm);                          // "clients"
        $projectsLabel = basename($projectsBaseNorm);                         // "projects"

        if ($p === $clientsLabel || strpos($p, $clientsLabel . '/') === 0) {
            // ?path=clients/… → APP_PATH + APP_BASE['clients'] + remainder
            $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
            $remainder = ($p === $clientsLabel) ? '' : substr($p, strlen($clientsLabel) + 1) . '/';
            $absDir = $ctxRoot . $remainder;
            $context = 'clients-base';

        } elseif ($p === $projectsLabel || strpos($p, $projectsLabel . '/') === 0) {
            // ?path=projects/… → APP_PATH + APP_BASE['projects'] + remainder
            $ctxRoot = $APP_PATH_N . $BASE_PROJECTS;
            $remainder = ($p === $projectsLabel) ? '' : substr($p, strlen($projectsLabel) + 1) . '/';
            $absDir = $ctxRoot . $remainder;
            $context = 'projects-base';

        } else {
            // regular app-relative path
            $ctxRoot = $APP_PATH_N;
            $absDir = $APP_PATH_N . rtrim($path, '/') . '/';
            $context = 'app';
        }
    } else {
        // default app root
        $ctxRoot = $APP_PATH_N;
        $absDir = $APP_PATH_N;
        $context = 'app';
    }
}
/*
    // ---- APP_ROOT + normalize + existence ------------------------------------
    $ctxRoot = rtrim($ctxRoot, '/\\') . '/';
    $absDir = rtrim($absDir, '/\\') . '/'; // norm_path($absDir);

    // ---- APP_ROOT from CONTEXT ROOT ONLY (no path)
    $APP_ROOT_REL = preg_replace('#^' . preg_quote($APP_PATH_N, '#') . '#', '', $ctxRoot);
    $APP_ROOT_REL = rtrim($APP_ROOT_REL, '/'); // '' | 'clients/Client/Domain' | 'projects/Proj'

    if (!defined('APP_ROOT')) {
      define('APP_ROOT', $APP_ROOT_REL);
    }

    if (!defined('APP_ROOT_DIR')) {
      define('APP_ROOT_DIR', $ctxRoot);
    }
    // ---- existence check ------------------------------------------------------
    $absDir = rtrim($absDir ?? '', '/\\') . '/';
*/

// ---- normalize -------------------------------------------------------------
$norm = static fn($s) => trim(str_replace('\\', '/', (string) $s), '/');
$trail = static fn($s) => ($s === '' ? '' : rtrim(str_replace('\\', '/', (string) $s), '/') . '/');

$ctxRoot = $trail($ctxRoot ?? '');
$absDir = $trail($absDir ?? '');

/*
// --------------------------------------------------------------------------
// APP_ROOT: compute from INSTALL RULES ONLY
// (client= alone or client=empty MUST NOT set an install root)
// --------------------------------------------------------------------------
$installRoot = '';
if ($hasClient && $client !== '' && $hasDomain && $domain !== '') {
    // client + domain
    $installRoot = $BASE_CLIENTS . $client . '/' . $domain . '/';
} elseif (!$hasClient && $hasDomain && $domain !== '') {
    // domain only
    $installRoot = $BASE_CLIENTS . $domain . '/';
} elseif ($hasProject && $project !== '') {
    // project
    $installRoot = $BASE_PROJECTS . $project . '/';
}
// NOTE: no branch for ($hasClient && $client === '') → leave '' as requested

$APP_ROOT_REL = $trail($installRoot);  // '' | '../clients/.../' | 'projects/.../'

// --------------------------------------------------------------------------
// APP_ROOT_DIR: subpath inside context (from ?path)
// Special-case: if user typed the label (e.g. "clients/"), map to APP_BASE['clients']
// --------------------------------------------------------------------------
$INSTALL_SUB = isset($_GET['path']) ? (string) $_GET['path'] : '';
$INSTALL_SUB = preg_replace('~[^a-z0-9._\-/]~i', '', $INSTALL_SUB);
if (strpos($INSTALL_SUB, '..') !== false)
    $INSTALL_SUB = '';
$INSTALL_SUB_N = $norm($INSTALL_SUB);

$clientsLabel = $norm($BASE_CLIENTS);    // '../clients/' -> 'clients'
$clientsBasename = basename($clientsLabel); // 'clients'
if ($INSTALL_SUB_N === $clientsLabel || $INSTALL_SUB_N === $clientsBasename) {
    // (keeps your “?path=clients/” behaving as your configured base, not literally 'clients/')
    $INSTALL_SUB = $BASE_CLIENTS;
}

$INSTALL_SUB = $trail($INSTALL_SUB);         // '' | 'public/' | '../clients/' etc.

// ---- define constants (once) ----------------------------------------------
if (!defined('APP_ROOT'))
    define('APP_ROOT', $APP_ROOT_REL);
if (!defined('APP_ROOT_DIR'))
    define('APP_ROOT_DIR', $INSTALL_SUB);

// --------------------------------------------------------------------------
// COMPLETE_PATH: absolute target used by composer/npm/git
// Default: APP_PATH + APP_ROOT + APP_ROOT_DIR
// Special-case: client-only + path → point into that client's folder
// --------------------------------------------------------------------------
$COMPLETE_PATH = rtrim($APP_PATH_N, '/') . '/';
if (APP_ROOT !== '') {
    // install root established (domain or project present)
    $COMPLETE_PATH .= APP_ROOT . APP_ROOT_DIR;
} elseif ($hasClient && $client !== '' && $INSTALL_SUB !== '') {
    // client-only + path (no domain) → /../clients/<client>/<path>
    $COMPLETE_PATH .= $BASE_CLIENTS . $client . '/' . $INSTALL_SUB;
} else {
    // path-only or nothing → app-root + subpath (if any)
    $COMPLETE_PATH .= APP_ROOT_DIR;
}
$COMPLETE_PATH = $trail($COMPLETE_PATH);
*/
// ---- (optional) existence check for browsing UIs --------------------------
$exists = is_dir($absDir);

$GLOBALS['__ctx'] = [
    'context' => $context,       // 'app' | 'clients' | 'clients-base' | 'projects' | ...
    'ctxRoot' => $ctxRoot,       // absolute context root
    'absDir' => $absDir,        // absolute path currently browsed
    'APP_PATH' => $APP_PATH_N,    // normalized APP_PATH
    'APP_ROOT' => APP_ROOT,       // install root (relative to APP_PATH)
    'APP_ROOT_DIR' => APP_ROOT_DIR,   // subpath inside install root
    'COMPLETE_PATH' => rtrim(APP_PATH, '/') . '/' . APP_ROOT . APP_ROOT_DIR, // absolute target for composer/npm/git
];

//dd($GLOBALS['__ctx']);

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
if (defined('APP_MODE') && APP_MODE !== 'web')
    $wantsDispatcher = true;

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
    //dd(APP_MODE);
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
            require_once dirname(__DIR__) . '/app/tools/code/git.php';

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