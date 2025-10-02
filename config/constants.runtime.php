<?php
declare(strict_types=1);
/**
 * constants.runtime.php
 *
 * Runtime and environment state detection.
 * Assumes APP_PATH and APP_BASE are defined.
 */

// Sensible defaults
ini_set('assert.exception', '1');
if (!APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

// ---------------------------------------------------------
// [1] Request Context Detection
// ---------------------------------------------------------

!defined('APP_REQUEST_METHOD') and define(
    'APP_REQUEST_METHOD',
    $_SERVER['REQUEST_METHOD'] ?? 'cli'
);

!defined('APP_IS_CLI') and define(
    'APP_IS_CLI',
    PHP_SAPI === 'cli'
);

!defined('APP_IS_WEB') and define(
    'APP_IS_WEB',
    !APP_IS_CLI
);

!defined('APP_START') and define(
    'APP_START',
    microtime(true)
);

// ---------------------------------------------------------
// [2] Execution Contexts
// ---------------------------------------------------------

!defined('APP_EXEC') and define(
    'APP_EXEC',
    PHP_BINARY
);

!defined('PHP_EXEC') and define(
    'PHP_EXEC',
    PHP_BINARY
);

!defined('APP_IS_LOCALHOST') and define(
    'APP_IS_LOCALHOST',
    in_array($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', ['127.0.0.1', '::1'])
);

!defined('APP_IS_ONLINE') and define(
    'APP_IS_ONLINE',
    (bool) gethostbyname('github.com') !== 'github.com'
);

// Optional SUDO detection
!defined('APP_IS_SUDO') and define(
    'APP_IS_SUDO',
    (function () {
        return stripos(PHP_OS, 'LINUX') === 0 && function_exists('posix_geteuid')
            ? posix_geteuid() === 0
            : false;
    })()
);

if (!defined('APP_SELF')) {
    if (PHP_SAPI === 'cli') {
        $first = get_included_files()[0] ?? __FILE__;
        define('APP_SELF', realpath($first) ?: $first);
    } else {
        $sf = $_SERVER['SCRIPT_FILENAME'] ?? (get_included_files()[0] ?? __FILE__);
        define('APP_SELF', realpath($sf) ?: $sf);
    }
}

// !defined('APP_MODE') and define('APP_MODE', 'web');

// ------------------------------------------------------
// Context Detection (cli, socket, or www)
// ------------------------------------------------------

// APP_CONTEXT: tells runtime *what kind of environment* we're in
defined('APP_CONTEXT') || define('APP_CONTEXT', match (true) {
    (defined('APP_MODE') && APP_MODE === 'socket') || (PHP_SAPI === 'cli' && isset($argv[1]) && str_starts_with($argv[1], 'socket')) => 'socket',
    (defined('APP_MODE') && APP_MODE === 'cli') || PHP_SAPI === 'cli' => 'cli',
    default => 'www', // most likely browser
});

// ---------------------------------------------------------
// [3] Dashboard / Versioning
// ---------------------------------------------------------

!defined('APP_DASHBOARD') and define(
    'APP_DASHBOARD',
    APP_PATH . 'dashboard.php'
);

!defined('APP_VERSION_FILE') and define(
    'APP_VERSION_FILE',
    APP_PATH . 'VERSION.md'
);

// ---------------------------------------------------------
// [4] Runtime Executables
// ---------------------------------------------------------

// Resolve common executables like node, composer, etc.
$executables = ['node', 'composer', 'npm', 'phpunit'];
foreach ($executables as $bin) {
    $constant = strtoupper($bin) . '_EXEC';
    if (!defined($constant)) {
        $path = trim(@shell_exec("which $bin") ?? '');
        if ($path && is_executable($path)) {
            define($constant, $path);
        }
    }
}

// ---- read inputs ----------------------------------------------------------

// Apply
$client = clean_client(get_str('client'));     // ex: 000-clientname
$domain = clean_domain(get_str('domain'));     // ex: example.com
$project = clean_project(get_str('project'));    // ex: 123project
$path = clean_path(get_str('path'));       // ex: sub-directory/ (may be '')

// Optional: hard-block traversal in path
if (strpos($path, '..') !== false)
    $path = '';

$APP_PATH_N = rtrim(str_replace('\\', '/', APP_PATH), '/') . '/';
$BASE_CLIENTS = base_val('clients');
$BASE_PROJECTS = base_val('projects');

$hasClient = array_key_exists('client', $_GET);
$hasDomain = array_key_exists('domain', $_GET);
$hasProject = array_key_exists('project', $_GET);
$hasPath = array_key_exists('path', $_GET);

// ---- Install root (APP_ROOT) — per your rules
$installRoot = '';
if ($hasClient && $client !== '' && $hasDomain && $domain !== '') {
    $installRoot = $BASE_CLIENTS . $client . '/' . $domain . '/';
} elseif (!$hasClient && $hasDomain && $domain !== '') {
    $installRoot = $BASE_CLIENTS . $domain . '/';
} elseif ($hasProject && $project !== '') {
    $installRoot = $BASE_PROJECTS . $project . '/';
}
// client-only (even empty) → no install root

// ---- Subpath (APP_ROOT_DIR) — from ?path (with your “clients/ label → base” quirk)
$norm = static fn($s) => trim(str_replace('\\', '/', $s), '/');
$trail = static fn($s) => $s === '' ? '' : rtrim(str_replace('\\', '/', $s), '/') . '/';

$installSub = isset($_GET['path']) ? (string) $_GET['path'] : '';
$installSub = preg_replace('~[^a-z0-9._\-/]~i', '', $installSub);
if (strpos($installSub, '..') !== false)
    $installSub = '';

$clientsLabel = basename(rtrim($BASE_CLIENTS, '/'));    // "clients"
$projectsLabel = basename(rtrim($BASE_PROJECTS, '/'));   // "projects"
$installSubNorm = $norm($installSub);

// map label to configured base (handles '../clients/')
if ($installSubNorm === $clientsLabel)
    $installSub = $BASE_CLIENTS;
if ($installSubNorm === $projectsLabel)
    $installSub = $BASE_PROJECTS;

define('APP_ROOT', $trail($installRoot));  // '' | '../clients/.../' | 'projects/.../'
define('APP_ROOT_DIR', $trail($installSub));   // '' | 'public/' | '../clients/' etc.

// Ensure var directory exists
$varDir = app_base('var');

if (!is_dir($varDir)) {
    mkdir($varDir, 0755, true);
}

$cacheFile = "{$varDir}getcomposer.org.html";
$url = 'https://getcomposer.org/';
$needsUpdate = true;

// Refresh only if older than 5 days
if (is_file($cacheFile)) {
    $ageDays = (time() - filemtime($cacheFile)) / 86400;
    $needsUpdate = ($ageDays >= 5);
}

if ($needsUpdate) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($ch);
    curl_close($ch);

    if (!empty($html)) {
        file_put_contents($cacheFile, $html);
    } else {
        $errors['COMPOSER_LATEST'] = "$url returned empty.";
    }
}

// Parse the cached HTML

if (!function_exists('getElementsByClass')) {
    function getElementsByClass($node, $tagName, $className)
    {
        $elements = [];
        foreach ($node->getElementsByTagName($tagName) as $el) {
            if (in_array($className, explode(' ', $el->getAttribute('class')))) {
                $elements[] = $el;
            }
        }
        return $elements;
    }
}

if (is_file($cacheFile)) {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'utf-8');
    $doc->loadHTML(file_get_contents($cacheFile));

    if ($main = $doc->getElementById("main")) {
        $nodes = getElementsByClass($main, 'p', 'latest');
        if (!empty($nodes)) {
            $pattern = '/Latest: (\d+\.\d+\.\d+) \(\w+\)/';
            if (preg_match($pattern, $nodes[0]->nodeValue, $matches)) {
                defined('COMPOSER_LATEST') || define('COMPOSER_LATEST', $matches[1]);
            } else {
                $errors['COMPOSER_LATEST'] = $nodes[0]->nodeValue . ' did not match $version';
            }
        }
    }
}

// 3. Load connection checker if not available
/*
if (!function_exists('check_internet_connection')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
}

if (!defined('APP_IS_ONLINE')) {
    $online = function_exists('check_internet_connection')
        ? check_internet_connection()
        : true; // or a cheap fallback
    define('APP_IS_ONLINE', $online);
    define('APP_NO_INTERNET_CONNECTION', !$online);
}*/