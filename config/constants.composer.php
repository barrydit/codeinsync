<?php

/**
 * config/constants.composer.php
 *
 * Centralized Composer constants + tiny helpers.
 * - No shell exec here (safe for early bootstrap)
 * - Supports env overrides
 * - Works cross-platform (Windows/Unix)
 * - Optional auto-create of COMPOSER_HOME/config.json
 *
 * Depends on:
 *   - APP_PATH (required)
 *   - APP_BASE['vendor'] (optional; defined by constants.paths.php)
 */

// ---------------------------------------------------------------------------
// Small shared helpers (guarded)
// ---------------------------------------------------------------------------

if (!function_exists('normalize_dir')) {
    function normalize_dir(string $path): string
    {
        if ($path === '')
            return '';
        $sep = DIRECTORY_SEPARATOR;
        $path = str_replace(['\\', '/'], $sep, $path);
        $path = rtrim($path, $sep) . $sep;
        return preg_replace('#' . preg_quote($sep, '#') . '+#', $sep, $path);
    }
}

if (!function_exists('join_path')) {
    function join_path(string ...$parts): string
    {
        $sep = DIRECTORY_SEPARATOR;
        $path = implode($sep, array_filter($parts, fn($p) => $p !== '' && $p !== $sep));
        $path = str_replace(['\\', '/'], $sep, $path);
        return preg_replace('#' . preg_quote($sep, '#') . '+#', $sep, $path);
    }
}

if (!function_exists('ensure_dir')) {
    function ensure_dir(string $abs, int $mode = 0775): bool
    {
        if (is_dir($abs))
            return true;
        return @mkdir($abs, $mode, true);
    }
}

if (!function_exists('safe_json_encode')) {
    function safe_json_encode($data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return $json === false ? "{}" : $json;
    }
}

// ---------------------------------------------------------------------------
// Platform flags
// ---------------------------------------------------------------------------

defined('IS_WINDOWS') || define('IS_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

// ---------------------------------------------------------------------------
// Defaults + env overrides (no IO yet)
// ---------------------------------------------------------------------------

$errors ??= [];

// 1) Composer home
//    Priority: APP_COMPOSER_HOME -> COMPOSER_HOME -> default to app-local var/composer/
$envHome = getenv('APP_COMPOSER_HOME') ?: getenv('COMPOSER_HOME') ?: '';
$defaultHome = defined('APP_BASE') && !empty(APP_BASE['var'])
    ? join_path(APP_BASE['var'], 'composer')
    : join_path(APP_PATH, 'var', 'composer');

$COMPOSER_HOME = $envHome !== '' ? $envHome : $defaultHome;
$COMPOSER_HOME = normalize_dir($COMPOSER_HOME);

// 2) Composer bin (how we will invoke it)
//    Priority: APP_COMPOSER_BIN -> COMPOSER_BIN -> project composer.phar -> vendor/bin/composer -> 'composer'
$envBin = getenv('APP_COMPOSER_BIN') ?: getenv('COMPOSER_BIN') ?: '';
$candPhar = join_path(APP_PATH, 'composer.phar');
$candVend = defined('APP_BASE') && !empty(APP_BASE['vendor'])
    ? join_path(APP_BASE['vendor'], 'bin', IS_WINDOWS ? 'composer.bat' : 'composer')
    : join_path(APP_PATH, 'vendor', 'bin', IS_WINDOWS ? 'composer.bat' : 'composer');

$COMPOSER_BIN = 'composer';
if ($envBin && (is_file($envBin) || !str_contains($envBin, DIRECTORY_SEPARATOR))) {
    $COMPOSER_BIN = $envBin;
} elseif (is_file($candPhar)) {
    $COMPOSER_BIN = PHP_BINARY . ' -d detect_unicode=0 ' . escapeshellarg($candPhar);
} elseif (is_file($candVend)) {
    $COMPOSER_BIN = $candVend;
} // else rely on PATH: "composer"

// 3) Project composer.json, lock, vendor dir
$COMPOSER_PROJECT_ROOT = normalize_dir(getenv('APP_COMPOSER_PROJECT_ROOT') ?: APP_PATH);
$COMPOSER_JSON = join_path($COMPOSER_PROJECT_ROOT, 'composer.json');
$COMPOSER_LOCK = join_path($COMPOSER_PROJECT_ROOT, 'composer.lock');
$COMPOSER_VENDOR_DIR = defined('APP_BASE') && !empty(APP_BASE['vendor'])
    ? rtrim(APP_BASE['vendor'], DIRECTORY_SEPARATOR)  // already absolute + trailing slash removed below
    : join_path($COMPOSER_PROJECT_ROOT, 'vendor');
$COMPOSER_VENDOR_DIR = rtrim($COMPOSER_VENDOR_DIR, DIRECTORY_SEPARATOR);

// 4) Derived files in COMPOSER_HOME
$COMPOSER_CONFIG_JSON = join_path($COMPOSER_HOME, 'config.json');
$COMPOSER_AUTH_JSON = join_path($COMPOSER_HOME, 'auth.json');
$COMPOSER_CACHE_DIR = join_path($COMPOSER_HOME, 'cache'); // you may wire this via env later

// 5) Behavior flags (no side effects here)
defined('COMPOSER_AUTOCREATE_HOME') || define('COMPOSER_AUTOCREATE_HOME', (getenv('COMPOSER_AUTOCREATE_HOME') ?: '1') === '1');
defined('COMPOSER_AUTOCREATE_CONFIG') || define('COMPOSER_AUTOCREATE_CONFIG', (getenv('COMPOSER_AUTOCREATE_CONFIG') ?: '1') === '1');

// 6) Default args (tune per your API handler)
$COMPOSER_DEFAULT_ARGS = trim(getenv('APP_COMPOSER_DEFAULT_ARGS') ?: '--no-interaction');

// ---------------------------------------------------------------------------
// Validation (non-fatal; populate $errors)
// ---------------------------------------------------------------------------

if (!is_dir($COMPOSER_PROJECT_ROOT)) {
    $errors['COMPOSER'][] = "Project root not found: $COMPOSER_PROJECT_ROOT";
}

if (!is_dir($COMPOSER_VENDOR_DIR)) {
    // Not an error: vendor/ may not exist yet
    // $errors['COMPOSER'][] = "Vendor dir missing (ok if not installed): $COMPOSER_VENDOR_DIR";
}

if (!is_dir($COMPOSER_HOME)) {
    if (COMPOSER_AUTOCREATE_HOME) {
        if (!ensure_dir($COMPOSER_HOME)) {
            $errors['COMPOSER'][] = "Unable to create COMPOSER_HOME: $COMPOSER_HOME";
        }
    } else {
        $errors['COMPOSER'][] = "COMPOSER_HOME directory missing: $COMPOSER_HOME";
    }
}

if (is_dir($COMPOSER_HOME) && !is_file($COMPOSER_CONFIG_JSON) && COMPOSER_AUTOCREATE_CONFIG) {
    // Create a minimal config.json
    $ok = @file_put_contents($COMPOSER_CONFIG_JSON, "{}\n");
    if ($ok === false) {
        $errors['COMPOSER'][] = "Failed to create config.json at: $COMPOSER_CONFIG_JSON";
    }
}

// ---------------------------------------------------------------------------
// Defines (idempotent)
// ---------------------------------------------------------------------------

defined('COMPOSER_HOME') || define('COMPOSER_HOME', $COMPOSER_HOME);
defined('COMPOSER_BIN') || define('COMPOSER_BIN', $COMPOSER_BIN);
defined('COMPOSER_PROJECT_ROOT') || define('COMPOSER_PROJECT_ROOT', $COMPOSER_PROJECT_ROOT);
defined('COMPOSER_JSON') || define('COMPOSER_JSON', $COMPOSER_JSON);
defined('COMPOSER_LOCK') || define('COMPOSER_LOCK', $COMPOSER_LOCK);
defined('COMPOSER_VENDOR_DIR') || define('COMPOSER_VENDOR_DIR', $COMPOSER_VENDOR_DIR);
defined('COMPOSER_CONFIG') || define('COMPOSER_CONFIG', $COMPOSER_CONFIG_JSON);
defined('COMPOSER_AUTH') || define('COMPOSER_AUTH', $COMPOSER_AUTH_JSON);
defined('COMPOSER_CACHE_DIR') || define('COMPOSER_CACHE_DIR', $COMPOSER_CACHE_DIR);
defined('COMPOSER_DEFAULT_ARGS') || define('COMPOSER_DEFAULT_ARGS', $COMPOSER_DEFAULT_ARGS);

// A compact struct you can dd() for debugging
defined('COMPOSER_EXEC') || define('COMPOSER_EXEC', [
    'bin' => COMPOSER_BIN,           // string, executable or phar invocation
    'args' => COMPOSER_DEFAULT_ARGS,  // string, default args
    'cwd' => COMPOSER_PROJECT_ROOT,  // default working directory
    'json' => COMPOSER_JSON,          // project composer.json
    'lock' => COMPOSER_LOCK,
    'home' => COMPOSER_HOME,
    'config' => COMPOSER_CONFIG,
    'auth' => COMPOSER_AUTH,
    'cache_dir' => COMPOSER_CACHE_DIR,
    'vendor_dir' => COMPOSER_VENDOR_DIR,
]);

// ---------------------------------------------------------------------------
// Tiny helpers for your API layer (no execution here)
// ---------------------------------------------------------------------------

if (!function_exists('composer_build_command')) {
    /**
     * Build a composer command string with safe quoting.
     * Usage: composer_build_command(['install', '--no-dev'])
     *        composer_build_command('update symfony/*')
     */
    function composer_build_command(array|string $args, ?string $cwd = null): string
    {
        $bin = COMPOSER_BIN;
        $cwd = $cwd ?: COMPOSER_PROJECT_ROOT;

        // Normalize arguments
        if (is_string($args)) {
            $args = trim($args) === '' ? [] : preg_split('/\s+/', $args);
        }

        // Default args
        $default = trim(COMPOSER_DEFAULT_ARGS);
        $argv = $default !== '' ? preg_split('/\s+/', $default) : [];
        foreach ($args as $a) {
            if ($a === null || $a === '')
                continue;
            $argv[] = $a;
        }

        // Env for Composer to honor home/cache (build a prefix like KEY=VAL KEY2=VAL2 cmd ...)
        $env = [
            'COMPOSER_HOME' => COMPOSER_HOME,
            'COMPOSER_CACHE_DIR' => COMPOSER_CACHE_DIR,
            // honor vendor dir if you want: 'COMPOSER_VENDOR_DIR' => COMPOSER_VENDOR_DIR,
        ];

        $prefix = '';
        if (!IS_WINDOWS) {
            // POSIX: KEY=VAL KEY2=VAL2
            foreach ($env as $k => $v) {
                $prefix .= $k . '=' . escapeshellarg($v) . ' ';
            }
        }
        // Windows: rely on proc_open to set env (recommended). If you *must* build a string for logs, include no env prefix.

        // Quote args safely
        $quoted = array_map(
            fn($a) => IS_WINDOWS ? escapeshellarg($a) : escapeshellarg($a),
            $argv
        );

        $cmd = trim($prefix . $bin . ' ' . implode(' ', $quoted));

        // Include a cwd comment for logs/debug
        return $cmd . '  # cwd=' . $cwd;
    }
}

// Optional: very small JSON helpers for composer config/auth (API code can call these)

if (!function_exists('composer_read_json')) {
    function composer_read_json(string $file): array
    {
        if (!is_file($file))
            return [];
        $raw = @file_get_contents($file);
        if ($raw === false || trim($raw) === '')
            return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}

if (!function_exists('composer_write_json')) {
    function composer_write_json(string $file, array $data): bool
    {
        $dir = dirname($file);
        if (!is_dir($dir) && !ensure_dir($dir))
            return false;
        return @file_put_contents($file, safe_json_encode($data) . "\n") !== false;
    }
}