<?php
/**
 * config/constants.composer.php (refactored)
 *
 * Single source of truth for Composer-related paths and execution.
 * - Stable types: strings for paths/commands, arrays for debug struct.
 * - Idempotent: safe to include multiple times.
 * - Robust: prefers system composer bin, falls back to local composer.phar.
 */

declare(strict_types=1);

$ctx = app_context();
if ($ctx !== 'web') {
    // skip network fetch / scraping in CLI/socket etc.
}

// -----------------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------------

if (!function_exists('define_if_absent')) {
    function define_if_absent(string $name, $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}

if (!function_exists('normalize_dir')) {
    function normalize_dir(string $path): string
    {
        return $path === '' ? '' : rtrim($path, "/\\") . DIRECTORY_SEPARATOR;
    }
}

// -----------------------------------------------------------------------------
// Base expectations (APP_PATH must be defined earlier in your bootstrap)
// -----------------------------------------------------------------------------

if (!defined('APP_PATH')) {
    throw new \RuntimeException('APP_PATH must be defined before loading constants.composer.php');
}

// Normalize process CWD to project root if you want consistency everywhere.
// Do this once in bootstrap; left here commented as a reminder.
// @chdir(APP_PATH);

// -----------------------------------------------------------------------------
/**
 * Inputs provided by earlier config/boot steps (optional). These are only used
 * as fallbacks if the corresponding constants are not already defined.
 * If you set any of these $VARs before including this file, they will be used.
 */
// -----------------------------------------------------------------------------

$COMPOSER_HOME = $COMPOSER_HOME ?? normalize_dir(APP_PATH . '.composer');
$COMPOSER_PROJECT_ROOT = $COMPOSER_PROJECT_ROOT ?? normalize_dir(APP_PATH);
$COMPOSER_VENDOR_DIR = $COMPOSER_VENDOR_DIR ?? normalize_dir($COMPOSER_PROJECT_ROOT . 'vendor');
$COMPOSER_JSON = $COMPOSER_JSON ?? ($COMPOSER_PROJECT_ROOT . 'composer.json');
$COMPOSER_LOCK = $COMPOSER_LOCK ?? ($COMPOSER_PROJECT_ROOT . 'composer.lock');
$COMPOSER_CONFIG_JSON = $COMPOSER_CONFIG_JSON ?? ($COMPOSER_HOME . 'config.json');
$COMPOSER_AUTH_JSON = $COMPOSER_AUTH_JSON ?? ($COMPOSER_HOME . 'auth.json');
$COMPOSER_CACHE_DIR = $COMPOSER_CACHE_DIR ?? normalize_dir($COMPOSER_HOME . 'cache');
$COMPOSER_DEFAULT_ARGS = $COMPOSER_DEFAULT_ARGS ?? '--no-interaction';

// Optional explicit bin (absolute path) computed earlier
$COMPOSER_BIN = $COMPOSER_BIN ?? null;

// -----------------------------------------------------------------------------
// Define path constants (idempotent)
// -----------------------------------------------------------------------------

define_if_absent('COMPOSER_HOME', normalize_dir($COMPOSER_HOME));
define_if_absent('COMPOSER_PROJECT_ROOT', normalize_dir($COMPOSER_PROJECT_ROOT));
define_if_absent('COMPOSER_VENDOR_DIR', normalize_dir($COMPOSER_VENDOR_DIR));
define_if_absent('COMPOSER_JSON', $COMPOSER_JSON);
define_if_absent('COMPOSER_LOCK', $COMPOSER_LOCK);
define_if_absent('COMPOSER_CONFIG', $COMPOSER_CONFIG_JSON);
define_if_absent('COMPOSER_AUTH', $COMPOSER_AUTH_JSON);
define_if_absent('COMPOSER_CACHE_DIR', normalize_dir($COMPOSER_CACHE_DIR));
define_if_absent('COMPOSER_DEFAULT_ARGS', $COMPOSER_DEFAULT_ARGS);

if (!defined('COMPOSER_VERSION')) {
    $output = @shell_exec('composer --version 2>/dev/null');
    if ($output) {
        // Typical: "Composer version 2.7.9 2025-02-20 10:11:12"
        if (preg_match('/Composer\s+version\s+(\S+)/i', $output, $m)) {
            define('COMPOSER_VERSION', (defined('COMPOSER_VERSION') && version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') !== 0)
                ? highlightVersionDiff($m[1], COMPOSER_LATEST)
                : $m[1]);
        }
    }
}

// Fallback if detection fails
if (!defined('COMPOSER_VERSION')) {
    define('COMPOSER_VERSION', 'unknown');
}

defined('COMPOSER_EXPR_VER') or define(
    'COMPOSER_EXPR_VER',
    '/^(?:' .
    'v?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)' .               // x.y.z
    '(?:-[0-9A-Za-z\-\.]+)?' .                                         // -prerelease
    '(?:\+[0-9A-Za-z\-\.]+)?' .                                        // +build
    '|' .
    'dev-[A-Za-z0-9._\-\/]+' .
    ')$/'
);

defined('COMPOSER_GITHUB_OAUTH_TOKEN') or define(
    'COMPOSER_GITHUB_OAUTH_TOKEN',
    (function () {
        if (is_file(COMPOSER_AUTH)) {
            $json = file_get_contents(COMPOSER_AUTH);
            $data = json_decode($json, true);
            if (
                json_last_error() === JSON_ERROR_NONE &&
                isset($data['github-oauth']['github.com']) &&
                !empty($data['github-oauth']['github.com'])
            ) {
                return $data['github-oauth']['github.com'];
            }
        }
        return '<GitHub OAuth Token>'; // fallback placeholder
    })()
);

// -----------------------------------------------------------------------------
// Resolve the composer executable
// Preference: system bin → local phar → null
// -----------------------------------------------------------------------------

$resolvedBin = null;

// 1) Use provided/known bin if executable
if ($COMPOSER_BIN && is_string($COMPOSER_BIN) && @is_executable($COMPOSER_BIN)) {
    $resolvedBin = $COMPOSER_BIN;
}

// 2) Probe common locations
if (!$resolvedBin) {
    foreach (['/usr/local/bin/composer', '/usr/bin/composer', '/bin/composer'] as $cand) {
        if (@is_executable($cand)) {
            $resolvedBin = $cand;
            break;
        }
    }
}

// 3) Local phar fallback
$pharPath = APP_PATH . 'composer.phar';
$pharExec = null;
if (is_file($pharPath)) {
    $pharExec = 'php ' . escapeshellarg($pharPath);
}

// Define BIN and PHAR (if found)
if ($resolvedBin) {
    define_if_absent('COMPOSER_BIN', $resolvedBin);
}
if ($pharExec) {
    define_if_absent('COMPOSER_PHAR', ['path' => $pharPath, 'exec' => $pharExec]);
}

// -----------------------------------------------------------------------------
// Chosen command to run + Debug struct
// -----------------------------------------------------------------------------

if (!defined('COMPOSER_EXEC_CMD')) {
    if (defined('COMPOSER_BIN')) {
        define('COMPOSER_EXEC_CMD', COMPOSER_BIN);
    } elseif (defined('COMPOSER_PHAR')) {
        define('COMPOSER_EXEC_CMD', COMPOSER_PHAR['exec']);
    } else {
        define('COMPOSER_EXEC_CMD', null);
    }
}

// A compact struct you can dd() for debugging
define_if_absent('COMPOSER_EXEC', [
    'exec' => COMPOSER_EXEC_CMD,     // string|null (bin or "php /path/composer.phar")
    'args' => COMPOSER_DEFAULT_ARGS, // default args string
    'cwd' => COMPOSER_PROJECT_ROOT, // working directory to run in
    'json' => COMPOSER_JSON,         // project composer.json
    'lock' => COMPOSER_LOCK,
    'home' => COMPOSER_HOME,
    'config' => COMPOSER_CONFIG,
    'auth' => COMPOSER_AUTH,
    'cache_dir' => COMPOSER_CACHE_DIR,
    'vendor_dir' => COMPOSER_VENDOR_DIR,
]);

// -----------------------------------------------------------------------------
// Ensure COMPOSER_HOME/config.json exists (best-effort, non-fatal)
// -----------------------------------------------------------------------------

$configJsonPath = COMPOSER_CONFIG;
if (!is_file($configJsonPath)) {
    // Ensure COMPOSER_HOME exists
    if (!is_dir(COMPOSER_HOME)) {
        @mkdir(COMPOSER_HOME, 0775, true);
    }
    @file_put_contents($configJsonPath, "{}", LOCK_EX);
}

// -----------------------------------------------------------------------------
// Optional helper to run composer reliably (use proc_open for more control)
// -----------------------------------------------------------------------------

if (!function_exists('run_composer')) {
    /**
     * @param string $subcmd   e.g. "install", "update symfony/process"
     * @param string $extra    e.g. "--prefer-dist"
     * @param string|null $cwd Working directory; defaults to COMPOSER_PROJECT_ROOT
     * @return array{code:int,out:string,err:string}
     */
    function run_composer(string $subcmd, string $extra = '', ?string $cwd = null): array
    {
        $cwd = $cwd ?? COMPOSER_PROJECT_ROOT;
        $exec = COMPOSER_EXEC_CMD;
        if (!$exec) {
            return ['code' => 127, 'out' => '', 'err' => 'composer executable not found'];
        }

        $cmd = trim($exec . ' ' . COMPOSER_DEFAULT_ARGS . ' ' . $subcmd . ' ' + $extra);
        $desc = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $proc = @proc_open($cmd, $desc, $pipes, $cwd, null);
        if (!\is_resource($proc)) {
            return ['code' => 1, 'out' => '', 'err' => 'proc_open failed'];
        }
        $out = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $code = proc_close($proc);
        return ['code' => (int) $code, 'out' => (string) $out, 'err' => (string) $err];
    }
}
