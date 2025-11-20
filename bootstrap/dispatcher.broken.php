<?php
/**
 * bootstrap/dispatcher.php
 *
 * Preserves your 3a–3c context resolution, defines $GLOBALS['__ctx'],
 * computes APP_ROOT / APP_ROOT_DIR / COMPLETE_PATH, and cleanly handles:
 *  - JSON fragments: ?app=...&json=1  or Accept: application/json
 *  - PARTS:          ?app=...&part=style|body|script
 *  - FULL page:      ?app=...&full=1
 *  - API POST:       api/{name}.php via POST (cmd/api handlers)
 *
 * Apps:
 *  - Echo fragment HTML (no <!DOCTYPE>) for default branch
 *  - Optional: set $APP_STYLE / $APP_SCRIPT (strings)
 *  - Optional: if (($_GET['part'] ?? '')==='script') { echo JS; return; }
 */

declare(strict_types=1);

/* ────────────────────── Standalone-safe prelude ────────────────────── */
if (!defined('APP_PATH')) {
    define('APP_PATH', rtrim(dirname(__DIR__), "/\\") . DIRECTORY_SEPARATOR);
}
$safeRequire = static function (string $rel): void {
    $abs = APP_PATH . ltrim($rel, '/');
    if (is_file($abs))
        require_once $abs;
};

$safeRequire('config/functions.php');
$safeRequire('config/constants.env.php');
$safeRequire('config/constants.paths.php');
$safeRequire('bootstrap/php-ini.php');
if (($_ENV['COMPOSER']['AUTOLOAD'] ?? true) !== false) {
    $safeRequire('vendor/autoload.php');
}
$safeRequire('config/constants.runtime.php'); // usually defines $client/$domain/$project/$path via clean_*
$safeRequire('config/constants.url.php');
$safeRequire('config/constants.app.php');

/* ────────────────────── Helpers ────────────────────── */
$respond_json = static function (array $payload, int $status = 200): void {
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        header('Vary: Accept');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};
$respond_text = static function (string $body, string $ct = 'text/plain; charset=utf-8', int $status = 200): void {
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: ' . $ct);
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        header('Vary: Accept');
    }
    echo $body;
    exit;
};
$respond_js = static function (string $code, int $status = 200): void {
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/javascript; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
    }
    echo $code;
    exit;
};
$sanitize_app = static function (string $s): string {
    $s = trim($s, "/ \t\n\r\0\x0B");
    $s = preg_replace('~[^a-z0-9_/\-]~i', '', $s);
    $s = preg_replace('~/{2,}~', '/', $s);
    if (strpos($s, '..') !== false)
        return '';
    return $s;
};
$norm = static fn($s) => trim(str_replace('\\', '/', (string) $s), '/');
$trail = static fn($s) => ($s === null || $s === '') ? '' : rtrim(str_replace('\\', '/', (string) $s), '/') . '/';

/* ────────────────────── Input (fallback if runtime didn’t set) ────────────────────── */
$client = isset($client) ? $client : (function_exists('clean_client') ? clean_client(get_str('client')) : ($_GET['client'] ?? null));
$domain = isset($domain) ? $domain : (function_exists('clean_domain') ? clean_domain(get_str('domain')) : ($_GET['domain'] ?? null));
$project = isset($project) ? $project : (function_exists('clean_project') ? clean_project(get_str('project')) : ($_GET['project'] ?? null));
$path = isset($path) ? $path : (function_exists('clean_path') ? clean_path(get_str('path')) : ($_GET['path'] ?? null));
$path = ($path === '') ? null : $path;

$APP_PATH_N = rtrim(str_replace('\\', '/', APP_PATH), '/') . '/';
$BASE_CLIENTS = function_exists('base_val') ? base_val('clients') : 'clients/';
$BASE_PROJECTS = function_exists('base_val') ? base_val('projects') : 'projects/';

/* ────────────────────── Presence flags ────────────────────── */
$hasClientParam = array_key_exists('client', $_GET);
$hasDomainParam = array_key_exists('domain', $_GET);
$hasProjectParam = array_key_exists('project', $_GET);
$hasPathParam = array_key_exists('path', $_GET);
$hasNonEmptyPath = $hasPathParam && $path !== null && $path !== '';

$populated = [
    'client' => ($client !== null && $client !== ''),
    'domain' => ($domain !== null && $domain !== ''),
    'project' => ($project !== null && $project !== ''),
    'path' => ($path !== null && $path !== ''),
];

/* ────────────────────── 3a) Base listings (presence-aware) ────────────────────── */
$ctxRoot = null;
$absDir = null;
$context = null;

if (!$hasClientParam && !$hasDomainParam && !$hasProjectParam && !$hasNonEmptyPath) {
    // no params -> app root
    $ctxRoot = $APP_PATH_N;
    $absDir = $APP_PATH_N;
    $context = 'app';

} elseif ($hasClientParam && $client === '' && $hasDomainParam && $domain === '' && !$hasProjectParam && !$hasNonEmptyPath) {
    // client=&domain= (empty) -> clients base
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
    $absDir = $ctxRoot;
    $context = 'clients-base';

} elseif ($hasProjectParam && $project === '' && !$hasClientParam && !$hasDomainParam && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_PROJECTS;
    $absDir = $ctxRoot;
    $context = 'projects-base';

} elseif ($hasClientParam && $client === '' && !$hasProjectParam && !$hasDomainParam && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
    $absDir = $ctxRoot;
    $context = 'clients-base';

} elseif ($hasDomainParam && $domain === '' && !$hasClientParam && !$hasProjectParam && !$hasNonEmptyPath) {
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
    $absDir = $ctxRoot;
    $context = 'clients-base';
}

/* ────────────────────── 3b) Only path → redirect (non-JSON) ────────────────────── */
if ($absDir === null && $populated['path'] && !$populated['client'] && !$populated['domain'] && !$populated['project']) {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $isJson = isset($_GET['json']) || stripos($accept, 'application/json') !== false;
    if (!$isJson) {
        $target = ($_SERVER['SCRIPT_NAME'] ?? '/') . '?' . http_build_query(['app' => 'devtools/directory', 'path' => $path]);
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

/* ────────────────────── 3c) Main decision tree ────────────────────── */
if ($absDir === null) {
    $hasClient = ($client !== null);
    $hasDomain = ($domain !== null);
    $hasProject = ($project !== null);
    $hasPath = ($path !== null && $path !== '');

    $appendPath = static function (string $root, ?string $p): string {
        return $root . (($p !== null && $p !== '') ? rtrim($p, '/') . '/' : '');
    };

    if ($hasClient && $hasDomain) {
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/' . $domain . '/';
        $absDir = $appendPath($ctxRoot, $path);
        $context = 'clients';

    } elseif ($hasClient && !$hasDomain && $hasPath) {
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/';
        $absDir = $appendPath($ctxRoot, $path);
        $context = 'clients';

    } elseif ($hasClient && !$hasDomain && !$hasPath) {
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/';
        $absDir = $ctxRoot;
        $context = 'clients';

    } elseif ($hasDomain) {
        $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $domain . '/';
        $absDir = $appendPath($ctxRoot, $path);
        $context = 'clients';

    } elseif ($hasProject) {
        $ctxRoot = $APP_PATH_N . $BASE_PROJECTS . $project . '/';
        $absDir = $appendPath($ctxRoot, $path);
        $context = 'projects-base';

    } elseif (($client === null) || ($domain === null)) {
        $ctxRoot = $APP_PATH_N /*. $BASE_CLIENTS*/ ;
        $absDir = $ctxRoot;
        $context = 'app';

    } elseif ($hasPath) {
        $p = trim((string) $path, '/');

        $clientsBaseNorm = rtrim(str_replace('\\', '/', $BASE_CLIENTS), '/');
        $projectsBaseNorm = rtrim(str_replace('\\', '/', $BASE_PROJECTS), '/');
        $clientsLabel = basename($clientsBaseNorm);
        $projectsLabel = basename($projectsBaseNorm);

        if ($p === $clientsLabel || str_starts_with($p, $clientsLabel . '/')) {
            $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
            $remainder = ($p === $clientsLabel) ? '' : substr($p, strlen($clientsLabel) + 1) . '/';
            $absDir = $ctxRoot . $remainder;
            $context = 'clients-base';

        } elseif ($p === $projectsLabel || str_starts_with($p, $projectsLabel . '/')) {
            $ctxRoot = $APP_PATH_N . $BASE_PROJECTS;
            $remainder = ($p === $projectsLabel) ? '' : substr($p, strlen($projectsLabel) + 1) . '/';
            $absDir = $ctxRoot . $remainder;
            $context = 'projects-base';

        } else {
            $ctxRoot = $APP_PATH_N;
            $absDir = $appendPath($APP_PATH_N, $path);
            $context = 'app';
        }

    } else {
        $ctxRoot = $APP_PATH_N;
        $absDir = $APP_PATH_N;
        $context = 'app';
    }
}

/* ────────────────────── Normalize ctxRoot/absDir ────────────────────── */
$ctxRoot = $trail($ctxRoot ?? '');
$absDir = $trail($absDir ?? '');

/* ────────────────────── APP_ROOT & APP_ROOT_DIR ────────────────────── */
/* APP_ROOT = install root derived from (client,domain,project) only */
$installRoot = '';
if ($hasClientParam && $client !== '' && $hasDomainParam && $domain !== '') {
    $installRoot = $BASE_CLIENTS . $client . '/' . $domain . '/';
} elseif (!$hasClientParam && $hasDomainParam && $domain !== '') {
    $installRoot = $BASE_CLIENTS . $domain . '/';
} elseif ($hasProjectParam && $project !== '') {
    $installRoot = $BASE_PROJECTS . $project . '/';
}
$APP_ROOT_REL = $trail($installRoot); // '' | '../clients/.../' | 'projects/.../'
if (!defined('APP_ROOT'))
    define('APP_ROOT', $APP_ROOT_REL);

/* APP_ROOT_DIR = subpath requested via ?path (sanitized) */
$INSTALL_SUB = (string) ($_GET['path'] ?? '');
$INSTALL_SUB = preg_replace('~[^a-z0-9._\-/]~i', '', $INSTALL_SUB);
if (strpos($INSTALL_SUB, '..') !== false)
    $INSTALL_SUB = '';
$INSTALL_SUB_N = $norm($INSTALL_SUB);

$clientsLabel = basename(rtrim(str_replace('\\', '/', $BASE_CLIENTS), '/'));
$projectsLabel = basename(rtrim(str_replace('\\', '/', $BASE_PROJECTS), '/'));
if ($INSTALL_SUB_N === $clientsLabel)
    $INSTALL_SUB = $BASE_CLIENTS;
if ($INSTALL_SUB_N === $projectsLabel)
    $INSTALL_SUB = $BASE_PROJECTS;

$INSTALL_SUB = $trail($INSTALL_SUB);
if (!defined('APP_ROOT_DIR'))
    define('APP_ROOT_DIR', $INSTALL_SUB);

/* COMPLETE_PATH */
$COMPLETE_PATH = $APP_PATH_N . (APP_ROOT !== '' ? APP_ROOT : '') . APP_ROOT_DIR;
$COMPLETE_PATH = $trail($COMPLETE_PATH);

/* ────────────────────── Publish context ────────────────────── */
$GLOBALS['__ctx'] = [
    'context' => $context,        // 'app' | 'clients' | 'clients-base' | 'projects-base'
    'ctxRoot' => $ctxRoot,        // absolute context root
    'absDir' => $absDir,         // absolute dir currently browsed
    'APP_PATH' => $APP_PATH_N,     // normalized
    'APP_ROOT' => APP_ROOT,        // install root (relative to APP_PATH)
    'APP_ROOT_DIR' => APP_ROOT_DIR,    // subpath within install root
    'COMPLETE_PATH' => $COMPLETE_PATH,  // absolute effective target
];

// dd($GLOBALS['__ctx']);

/* ────────────────────── Dispatcher proper ────────────────────── */
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
$appRaw = $_GET['app'] ?? $_POST['app'] ?? '';
$part = strtolower((string) ($_GET['part'] ?? $_POST['part'] ?? ''));

$appPath = $sanitize_app((string) $appRaw);
if ($appPath === '')
    $appPath = 'devtools/directory';
$appFile = APP_PATH . 'app/' . $appPath . '.php';
if (!is_file($appFile)) {
    $msg = "Not Found: app '{$appPath}'";
    if (stripos($accept, 'application/json') !== false || isset($_GET['json'])) {
        $respond_json(['error' => $msg], 404);
    }
    $respond_text($msg, 'text/plain; charset=utf-8', 404);
}

/* POST → API handler (composer/git/npm…) */
if ($method === 'POST') {
    $apiName = $_GET['api'] ?? $_POST['api'] ?? basename(str_replace('\\', '/', $appPath));
    $apiName = preg_replace('~[^a-z0-9_\-]~i', '', (string) $apiName);
    $apiFile = APP_PATH . 'api/' . $apiName . '.php';
    if (!is_file($apiFile))
        $respond_json(['error' => 'API not found', 'api' => $apiName], 404);
    ob_start();
    $ret = require $apiFile;
    $echoed = ob_get_clean();
    if ($echoed !== '')
        $respond_json(['echo' => $echoed]);
    if (is_array($ret))
        $respond_json($ret);
    $respond_json(['ok' => true, 'result' => (string) $ret]);
}

/* Mode selection */
$explicitFull = (isset($_GET['full']) && $_GET['full'] === '1');
$wantsScript = in_array($part, ['script', 'js'], true);
$wantsPart = in_array($part, ['style', 'body'], true);
$wantsJson = !$explicitFull && !$wantsScript && !$wantsPart && (
    (isset($_GET['json']) && $_GET['json'] !== '0') ||
    stripos($accept, 'application/json') !== false ||
    ($appRaw !== '')
);

/* PART: script */
if ($wantsScript) {
    $APP_STYLE = '';
    $APP_SCRIPT = '';
    ob_start();
    try {
        require $appFile;
        $buf = (string) ob_get_clean();
    } catch (Throwable $e) {
        while (ob_get_level() > 0)
            @ob_end_clean();
        $respond_js("// Script error: " . addslashes($e->getMessage()), 500);
    }
    $code = (string) ($APP_SCRIPT ?? '');
    if ($code === '')
        $code = $buf;
    if (preg_match('~<(?:!doctype|html|head|body)\b~i', $code)) {
        $code = "// Expected JS; app returned HTML for {$appPath} (part=script).";
    }
    $respond_js($code);
}

/* PART: style/body */
if ($wantsPart) {
    $APP_STYLE = '';
    $APP_SCRIPT = '';
    ob_start();
    try {
        require $appFile;
        $payload = ['style' => (string) ($APP_STYLE ?? ''), 'body' => (string) ob_get_clean(), 'script' => (string) ($APP_SCRIPT ?? '')];
    } catch (Throwable $e) {
        while (ob_get_level() > 0)
            @ob_end_clean();
        $respond_text("Part error: " . $e->getMessage(), 'text/plain; charset=utf-8', 500);
    }

    if ($part === 'style') {
        $respond_text($payload['style'], 'text/css; charset=utf-8', $payload['style'] === '' ? 204 : 200);
    } else {
        $body = $payload['body'];
        if (preg_match('~<(?:!doctype|html|head|body)\b~i', $body)) {
            $body = '<div class="app-error">Expected fragment HTML for body part; got full HTML.</div>';
        }
        $respond_text($body, 'text/html; charset=utf-8', $body === '' ? 204 : 200);
    }
}

/* JSON fragments */
if ($wantsJson) {
    $APP_STYLE = '';
    $APP_SCRIPT = '';
    ob_start();
    try {
        require $appFile;
        $body = (string) ob_get_clean();
    } catch (Throwable $e) {
        while (ob_get_level() > 0)
            @ob_end_clean();
        $respond_json(['error' => 'App crashed', 'detail' => $e->getMessage()], 500);
    }

    $style = (string) ($APP_STYLE ?? '');
    $script = (string) ($APP_SCRIPT ?? '');

    if (preg_match('~<(?:!doctype|html|head|body)\b~i', $body)) {
        $body = '<div class="app-error">Expected fragment HTML but app returned a full HTML page.</div>';
    }

    $respond_json(['style' => $style, 'body' => $body, 'script' => $script]);
}

/* FULL page */
try {
    require $appFile;
} catch (Throwable $e) {
    $respond_text("App crashed: " . $e->getMessage(), 'text/plain; charset=utf-8', 500);
}