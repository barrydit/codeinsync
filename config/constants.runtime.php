<?php
// config/constants.runtime.php
declare(strict_types=1);

/**
 * constants.runtime.php
 *
 * Runtime and environment state detection.
 * Assumes APP_PATH and APP_BASE are defined.
 */

use CodeInSync\Infrastructure\Dom\DomHelpers;

if (!class_exists(DomHelpers::class)) {
    require APP_PATH . 'src/Infrastructure/Dom/DomHelpers.php';
    @class_alias(DomHelpers::class, 'DomHelpers');
}

/*
use function CodeInSync\Infrastructure\Dom\getElementsByClass;
if (!function_exists('CodeInSync\\Infrastructure\\Dom\\getElementsByClass')) {
    require_once APP_PATH . 'src/Infrastructure/Dom/DomHelpers.php';
}*/

// Sensible defaults
ini_set('assert.exception', '1');

if (!empty($_ENV['APP_TIMEZONE'])) {
    $tz = (string) $_ENV['APP_TIMEZONE'];
    if (@timezone_open($tz)) {
        date_default_timezone_set($tz);
    }
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
    gethostbyname('github.com') !== 'github.com'
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

!defined('APP_DASHBOARD') and require_once __DIR__ . '/constants.app.php'; // defines APP_DASHBOARD

!defined('APP_VERSION_FILE') and define(
    'APP_VERSION_FILE',
    APP_PATH . 'VERSION.md'
);

// ---------------------------------------------------------
// [4] Runtime Executables
// ---------------------------------------------------------

function findExecutable(string $bin): ?string
{
    // If the caller passed a path, just validate it.
    if (strpbrk($bin, "/\\") !== false) {
        $p = realpath($bin);
        return ($p && is_file($p) && is_executable($p)) ? $p : null;
    }

    $isWin = (PHP_OS_FAMILY === 'Windows');

    // Search the PATH
    $path = getenv('PATH') ?: '';
    $dirs = array_filter(array_map('trim', $isWin ? explode(';', $path) : explode(':', $path)));

    // On Windows, executables can have extensions from PATHEXT
    $exts = ['']; // POSIX: exact name is fine
    if ($isWin) {
        $pathext = getenv('PATHEXT') ?: '.EXE;.BAT;.CMD;.COM';
        $exts = array_map('strtolower', array_map('trim', explode(';', $pathext)));

        // If the name already includes a known extension, try it first as-is.
        $lower = strtolower($bin);
        foreach ($exts as $e) {
            if ($e !== '' && str_ends_with($lower, $e)) {
                $exts = array_merge([''], $exts); // try as-is before appending others
                break;
            }
        }
    }

    foreach ($dirs as $dir) {
        // Strip surrounding quotes some Windows PATH entries have
        $dir = trim($dir, "\"' ");
        if ($dir === '' || !is_dir($dir))
            continue;

        foreach ($exts as $e) {
            $candidate = $isWin ? ($dir . DIRECTORY_SEPARATOR . $bin . ($e === '' ? '' : $e))
                : ($dir . DIRECTORY_SEPARATOR . $bin);
            if (is_file($candidate) && is_readable($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }
    }

    // Fallback to shell (optional)
    if (!$isWin) {
        $out = @shell_exec('command -v ' . escapeshellarg($bin));
        if ($out) {
            $p = trim($out);
            return (is_file($p) && is_executable($p)) ? $p : null;
        }
    } else {
        $exitCode = null;
        $cmd = 'where ' . escapeshellarg($bin) . ' 2>NUL';
        $out = @exec($cmd, $lines, $exitCode); // returns multiple lines sometimes
        if ($exitCode !== 0) {
            // no results → same condition as “INFO: ”
            return 'info was detected (no matches)';
        }
        if ($out) {
            foreach (preg_split('/\r?\n/', trim($out)) as $line) {
                $p = trim($line, "\" \t");
                if ($p !== '' && is_file($p) && is_readable($p) && is_executable($p)) {
                    return $line;
                }
            }
        }
    }

    return null;
}

// Resolve common executables like node, composer, etc.
$executables = ['node', 'composer', 'npm', 'phpunit'];
foreach ($executables as $bin) {
    $constant = strtoupper($bin) . '_EXEC';
    if (!defined($constant)) {
        $path = @findExecutable($bin);
        if ($path !== null) {
            define($constant, $path);
        }
    }
    //echo "  $constant => $path\n";
}

// ---- read inputs ----------------------------------------------------------

// Apply
$client = clean_client(get_str('client'));     // ex: 000-clientname
$domain = clean_domain(get_str('domain'));     // ex: example.com
$project = clean_project(get_str('project'));    // ex: 123project
$path = clean_path(get_str('path'));       // ex: sub-directory/ (may be '')

// Optional: hard-block traversal in path
//if (strpos($path, '..') !== false)
//    $path = '';

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

!defined('APP_ROOT') and define('APP_ROOT', $trail($installRoot));  // '' | '../clients/.../' | 'projects/.../'

/*
$installSub = isset($_GET['path']) ? (string) $_GET['path'] : '';
//$installSub = preg_replace('~[^a-z0-9._\-/]~i', '', $installSub);
//if (strpos($installSub, '..') !== false)
//    $installSub = '';

$clientsLabel = basename(rtrim($BASE_CLIENTS, '/'));    // "clients"
$projectsLabel = basename(rtrim($BASE_PROJECTS, '/'));   // "projects"
$installSubNorm = $norm($installSub);

// map label to configured base (handles '../clients/')
if ($installSubNorm === $clientsLabel)
    $installSub = $BASE_CLIENTS;
if ($installSubNorm === $projectsLabel)
    $installSub = $BASE_PROJECTS;
*/

$path = clean_path(get_str('path'), ['allow_leading_slash' => false, 'allow_dot_segments' => true]);
!defined('APP_ROOT_DIR') and define('APP_ROOT_DIR', $trail((string) ($path ?? ''))); // '' | 'subdir/' | 'sub/dir/'

// Optional: avoid leaking into later includes
unset($client, $domain, $project, $path, $hasClient, $hasDomain, $hasProject, $hasPath);

// Ensure var directory exists
$varDir = app_base('var');

if (!is_dir($varDir))
    mkdir($varDir, 0755, true);

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

if (is_file($cacheFile)) {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'utf-8');
    $doc->loadHTML(file_get_contents($cacheFile));

    if ($main = $doc->getElementById("main")) {
        $nodes = DomHelpers::getElementsByClass($main, 'p', 'latest');
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