<?php
declare(strict_types=1);
/**
 * constants.runtime.php
 *
 * Runtime and environment state detection.
 * Assumes APP_PATH and APP_BASE are defined.
 */

// Timezone (env wins; default your local)
$tz = $_ENV['APP_TZ'] ?? 'America/Vancouver';
@date_default_timezone_set(is_string($tz) && $tz ? $tz : 'America/Vancouver');

// Debug toggle
$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
defined('APP_DEBUG') or define('APP_DEBUG', $debug);

// Sensible defaults
ini_set('assert.exception', '1');
if ($debug) {
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

!defined('APP_MODE') and define('APP_MODE', 'web');

// ------------------------------------------------------
// Context Detection (cli, socket, or www)
// ------------------------------------------------------

// APP_CONTEXT: tells runtime *what kind of environment* we're in
defined('APP_CONTEXT') || define('APP_CONTEXT', match (true) {
    APP_MODE === 'socket' || (PHP_SAPI === 'cli' && isset($argv[1]) && str_starts_with($argv[1], 'socket')) => 'socket',
    APP_MODE === 'cli' || PHP_SAPI === 'cli' => 'cli',
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
if (!function_exists('check_internet_connection')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
}

if (!defined('APP_IS_ONLINE')) {
    $online = function_exists('check_internet_connection')
        ? check_internet_connection()
        : true; // or a cheap fallback
    define('APP_IS_ONLINE', $online);
    define('APP_NO_INTERNET_CONNECTION', !$online);
}