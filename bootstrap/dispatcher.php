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

    // Head section (URL context, public FS root, etc.)

    require_once __DIR__ . '/head.php';
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
/*
$populated = [
    'client' => ($client !== null && $client),
    'domain' => ($domain !== null && $domain),
    'project' => ($project !== null && $project),
    'path' => ($path !== null && $path),
];*/

$client = clean_client(get_str('client'));     // ex: 000-clientname
$domain = clean_domain(get_str('domain'));     // ex: example.com
$project = clean_project(get_str('project'));    // ex: 123project
$path = clean_path(get_str('path'));       // ex: sub-directory/ (may be '')

$hasClient = array_key_exists('client', $_GET);
$hasDomain = array_key_exists('domain', $_GET);
$hasProject = array_key_exists('project', $_GET);
$hasPath = array_key_exists('path', $_GET);

// Consider empty path as not present for the 3a base-cases:
$hasNonEmptyPath = $hasPath && $path;

// 3a) ONLY empty client/project → base

// 3a) ONLY empty base listings (presence-aware)
// no params -> app
if (!$hasClient && !$hasDomain && !$hasProject && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N;
    $absDir = $APP_PATH_N;
    $context = 'app';
} elseif ($hasClient && !$client && $hasDomain && $domain && !$hasProject && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
    $absDir = $ctxRoot;
    $context = 'clients-base';
} elseif ($hasProject && !$project && !$hasClient && !$hasDomain && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_PROJECTS;
    $absDir = $ctxRoot;
    $context = 'projects-base';
} elseif ($hasClient && !$client && !$hasProject && !$hasDomain && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
    $absDir = $ctxRoot;
    $context = 'clients-base';
} elseif ($hasDomain && !$domain && !$hasClient && !$hasProject && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
    $absDir = $ctxRoot;
    $context = 'clients-base';
}

$accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
$part = $_GET['part'] ?? '';
$isJson = isset($_GET['json']) || str_contains($accept, 'application/json');
$isJs = ($part === 'script') || preg_match('/\b(?:javascript|ecmascript|application\/x-javascript)\b/', $accept);


// 3b) ONLY path → redirect (run only if still undecided)
if ($absDir === null && $path && !$client && !$domain && !$project) {
    // ⛔ Redirect only if it's NOT JSON and NOT JS (script branch)
    if (!($isJson || $isJs)) {
        $target = ($_SERVER['SCRIPT_NAME'] ?? '/') . '?' . http_build_query([
            'app' => 'devtools/directory',
            'path' => $path,
        ]);

        $curPath = $_SERVER['SCRIPT_NAME'] ?? '/';
        parse_str($_SERVER['QUERY_STRING'] ?? '', $curQS);
        ksort($curQS);
        $current = $curPath . (empty($curQS) ? '' : '?' . http_build_query($curQS));

        if ($current !== $target && !headers_sent()) {
            //header("Location: $target", true, 302);
            //exit;
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
    if ($hasClient && $client && $hasDomain && $domain) {
        // client + domain (+ optional path)
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/' . $domain . '/';
        $absDir = $ctxRoot . ($hasPath && $path ? rtrim($path, '/') . '/' : '');
        $context = 'clients';

    } elseif ($hasClient && $client && (!$hasDomain || $domain) && $hasPath && $path) {
        // client + path (no domain)
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/';
        $absDir = $ctxRoot . rtrim($path, '/') . '/';
        $context = 'clients';

    } elseif ($hasClient && $client && (!$hasDomain || $domain) && (!$hasPath || $path)) {
        // client only
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/';
        $absDir = $ctxRoot;
        $context = 'clients';

    } elseif ($hasDomain && $domain) {
        // domain only (+ optional path)
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $domain . '/';
        $absDir = $ctxRoot . ($hasPath && $path ? rtrim($path, '/') . '/' : '');
        $context = 'clients';

    } elseif ($hasProject && $project) {
        // project (+ optional path)
        $ctxRoot = $APP_PATH_N . $BASE_PROJECTS . $project . '/';
        $absDir = $ctxRoot . ($hasPath && $path ? rtrim($path, '/') . '/' : '');
        $context = 'projects';

    } elseif (
        ($hasClient && $client) ||
        ($hasDomain && $domain)
    ) {
        // clients fallbacks
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . ($client ?? $domain) . '/';
        $absDir = $ctxRoot;
        $context = 'clients';
    } elseif ($hasPath && $path) {
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
    // --- helpers ---
    $load_ui_app = static function (string $app): array {

        $rel = ltrim($app, '/');
        $file = APP_PATH . 'app/' . $rel . '.php';
        if (!is_file($file)) {
            return ['error' => 'App not found', 'app' => $app, 'file' => $file];
        }
        // Capture ANY stray output (CSS, HTML, warnings)
        ob_start();
        $UI_APP = null;
        $result = require $file; // may set $UI_APP or return an array 
        $echoed = ob_get_clean();
        // Normalize payload
        if (is_array($result)) {
            $payload = $result;
        } elseif (isset($UI_APP) && is_array($UI_APP)) {
            $payload = $UI_APP;
        } else {
            $payload = ['style' => '', 'body' => '', 'script' => ''];
        }
        // If the app echoed CSS/HTML, fold it into 'style' (or 'body' if you prefer) 
        if ($echoed !== '') {
            if (isset($payload['style'])) {
                $payload['style'] = $echoed . $payload['style'];
            } else {
                $payload['body'] = $echoed . ($payload['body'] ?? '');
            }
        }
        return $payload;
    };

    $load_api_handler = static function (string $api): array {
        $rel = ltrim($api, '/');
        $file = APP_PATH . 'api/' . $rel . '.php';

        if (!is_file($file)) {
            return [
                'ok' => false,
                'api' => $api,
                'file' => $file,
                'error' => 'API not found',
            ];
        }

        $wrapOk = static function ($result, array $extra = []): array {
            return ['ok' => true] + $extra + ['result' => $result];
        };

        $wrapErr = static function (string $message, array $extra = []): array {
            return ['ok' => false, 'error' => $message] + $extra;
        };

        $decodeJsonIfArray = static function (string $s): ?array {
            $s = trim($s);
            if ($s === '')
                return null;

            $decoded = json_decode($s, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return null;
            }
            return $decoded;
        };

        $normalizeReturn = static function ($ret) use ($wrapOk, $wrapErr): array {
            if (is_array($ret)) {
                return $ret; // pass-through contract
            }

            if (is_scalar($ret) || $ret === null) {
                return $wrapOk($ret);
            }

            if ($ret instanceof \JsonSerializable) {
                return $wrapOk($ret->jsonSerialize());
            }

            if (is_object($ret)) {
                return $wrapOk(get_object_vars($ret));
            }

            return $wrapErr('Unsupported return type', ['type' => gettype($ret)]);
        };

        ob_start();
        try {
            $ret = require $file; // handler may echo or return
        } catch (\Throwable $e) {
            $echoed = ob_get_clean(); // discard or include below if you want

            return $wrapErr('API exception', [
                'api' => $api,
                'file' => $file,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                // Optional (dev only): include trace
                // 'trace'  => $e->getTraceAsString(),
                // Optional: include whatever was echoed before crash
                // 'echo'   => $echoed,
            ]);
        }

        $echoed = ob_get_clean();

        // 1) If handler echoed JSON, that wins.
        if ($echoed !== '') {
            $decoded = $decodeJsonIfArray($echoed);
            if (is_array($decoded)) {
                return $decoded;
            }

            // 2) Otherwise, treat echo as a result channel and include return value too.
            return $wrapOk($ret ?? null, [
                'echo' => $echoed,
                'api' => $api,
                'file' => $file,
            ]);
        }

        // 3) No echo; normalize return value.
        $out = $normalizeReturn($ret);
        // Optional: attach api/file context for debugging
        // $out['api'] = $out['api'] ?? $api;
        // $out['file'] = $out['file'] ?? $file;

        return $out;
    };
    
    // Emit JSON safely and STOP any further output
    $emit_json = static function ($data, int $code = 200): void {
        // Ensure nothing leaked before
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('X-Accel-Buffering: no');
        }
        // Flag to suppress shutdown debug/footers
        if (!defined('APP_EMITTED_JSON'))
            define('APP_EMITTED_JSON', true);
        try {
            echo json_encode($data, JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_INVALID_UTF8_SUBSTITUTE
                | JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            echo json_encode([
                'error' => 'JSON_ENCODE_FAILED',
                'message' => $e->getMessage(),
            ]);
        }
        exit; // <- prevents any footer from appending
    };

    // --- helper: emit either text or JSON, keeping existing $emit_json semantics
    $emit_response = static function (array $res) use ($emit_json): void {
        $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
        $wantsPlain = (isset($_GET['plain']) && $_GET['plain'] === '1') || str_contains($accept, 'text/plain');

        // 1) Explicit handler instruction to emit raw text
        if (($res['_format'] ?? '') === 'text') {
            if (!headers_sent()) {
                header('Content-Type: text/plain; charset=UTF-8');
                if (isset($res['status']) && is_numeric($res['status'])) {
                    http_response_code((int) $res['status']);
                }
                // make streaming snappy (optional)
                @ini_set('output_buffering', 'off');
                @ini_set('zlib.output_compression', '0');
                while (ob_get_level() > 0) {
                    @ob_end_flush();
                }
                ob_implicit_flush(true);
            }
            echo (string) ($res['body'] ?? $res['echo'] ?? '');
            exit;
        }

        // 2) Client prefers plain and the handler echoed text
        if ($wantsPlain && isset($res['echo']) && is_string($res['echo']) && $res['echo'] !== '') {
            if (!headers_sent()) {
                header('Content-Type: text/plain; charset=UTF-8');
            }
            echo $res['echo'];
            exit;
        }

        // 3) Default: JSON (existing behavior)
        $emit_json(is_array($res) ? $res : ['ok' => true, 'result' => $res]);
        // exit happens inside emit_json()
    };

    $emit_text = static function (string $body, string $contentType = 'text/plain; charset=utf-8', int $code = 200, array $extraHeaders = []): void {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: ' . $contentType, true);
            header('Cache-Control: no-store, no-cache, must-revalidate', true);
            header('Pragma: no-cache', true);
            header('X-Accel-Buffering: no', true);
            foreach ($extraHeaders as $h) {
                // e.g. 'Content-Security-Policy: ...'
                header($h, true);
            }
        }

        // Generic “we emitted a response” guard (name no longer JSON-specific)
        if (!defined('APP_EMITTED_OUTPUT'))
            define('APP_EMITTED_OUTPUT', true);
        echo $body;
        exit;
    };
    // --- routing decisions ---
    $appRaw = (string) ($_GET['app'] ?? $_POST['app'] ?? '');
    $apiParam = (string) ($_GET['api'] ?? $_POST['api'] ?? '');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $isJson = isset($_GET['json']) || stripos($accept, 'application/json') !== false;
    $part = isset($_GET['part']) ? strtolower((string) $_GET['part']) : null;

    // 0) Method guard (optional)
    if (!in_array($method, ['GET', 'POST'], true)) {
        $emit_json(['error' => 'METHOD_NOT_ALLOWED'], 405);
    }

    // NORMALIZE: prefer explicit api param; fallback to basename(app) for legacy
    $apiName = $apiParam !== '' ? $apiParam
        : ($appRaw !== '' ? basename(str_replace('\\', '/', $appRaw)) : '');

    // 1) API route (GET or POST) -> api/{name}.php
//    - Always emit JSON
//    - If GET, treat as read-only (your api scripts can enforce this per action)
    if ($apiParam !== '') {
        $res = $load_api_handler($apiParam);

        // IMPORTANT: do NOT pre-encode; $emit_json will encode it
        //$emit_json(is_array($res) ? $res : ['ok' => true, 'result' => $res]);
        $emit_response(is_array($res) ? $res : ['ok' => true, 'result' => $res]);
        // exit inside emit_json()
    }

    // 2) UI PART route (?app=...&part=style|body|script) -> raw asset
    if ($appRaw !== '' && in_array($part, ['style', 'body', 'script'], true)) {
        $payload = $load_ui_app($appRaw);
        $ctypeMap = [
            'style' => 'text/css; charset=utf-8',
            'body' => 'text/html; charset=utf-8',
            'script' => 'application/javascript; charset=utf-8',
        ];
        $content = (string) ($payload[$part] ?? '');
        $emit_text($content, $ctypeMap[$part], $content === '' ? 204 : 200);
        // exit inside emit_text()
    }

    // 2b) Full UI payload (?app=...)
    if ($appRaw !== '' && in_array($method, ['GET', 'POST'], true)) {
        $payload = $load_ui_app($appRaw);
        if ($isJson) {
            $emit_json($payload);
        } else {
            $buffer = ob_get_clean();
            if ($buffer !== '')
                echo $buffer;
            return;
        }
    }

    // 3) Legacy command routing by cmd=... (optional)
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

                $reqId = bin2hex(random_bytes(6)); // short request id

                try {
                    // Make the command available to the required file
                    $_POST['cmd'] = $cmd;

                    // Require the file; it should return an array response
                    $r = require $file;

                    if (is_array($r)) {
                        // Preserve handler response and attach req_id
                        $res = $r + ['req_id' => $reqId];
                    } else {
                        // Handler did not return an array (require returns 1 if no return)
                        $res = [
                            'ok' => false,
                            'error' => 'HANDLER_NO_RETURN',
                            'req_id' => $reqId,
                            'cmd' => $cmd,
                            'file' => $file,
                            'returned_type' => gettype($r),
                            'returned_value' => is_scalar($r) ? (string) $r : null,
                        ];
                    }

                    break;
                } catch (\Throwable $e) {
                    error_log("[{$reqId}] INTERNAL_ERROR in {$file}: {$e->getMessage()} @ {$e->getFile()}:{$e->getLine()}");

                    $res = [
                        'ok' => false,
                        'error' => 'INTERNAL_ERROR',
                        'req_id' => $reqId,
                        'cmd' => $cmd,
                        'handler' => $file,
                        'message' => $e->getMessage(),
                        'file' => basename($e->getFile()),
                        'line' => $e->getLine(),
                    ];
                }
            }
        }

        $emit_json($res);

    }

    // 4) Not handled (default route)
    else {
        // fall through to outer buffer/handler (could 404 upstream)
    }
    // ====================== END ROUTER (inlined) =======================
    $buffer = ob_get_clean();
    if (!defined('APP_EMITTED_OUTPUT') && !defined('APP_EMITTED_JSON')) {
        if ($buffer !== '')
            echo $buffer;
    }
    //dd(APP_MODE);
} catch (\Throwable $e) {
    $logFile = APP_PATH . 'var/log/router-error.log';
    $line = sprintf(
        "[%s] EXCEPTION %s in %s:%d\n",
        $reqId ?? 'noid',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    @file_put_contents($logFile, $line, FILE_APPEND);

    // (keep your existing unwind logic)

    $wantsJson = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')
        || (stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
        || defined('APP_EMITTED_JSON');

    if (defined('APP_DEBUG') && APP_DEBUG) {
        throw $e;
    }

    if ($wantsJson && !headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'INTERNAL_ERROR', 'req_id' => $reqId ?? null]);
        exit;
    }

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
