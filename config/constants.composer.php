<?php
/**
 * config/constants.composer.php
 * Idempotent, side-effect free Composer constants.
 */
declare(strict_types=1);

// ── prereqs ─────────────────────────────────────────────────────────────────
defined('APP_PATH') || define('APP_PATH', dirname(__DIR__) . '/');
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . 'config/');

// If you truly need app_base(), ensure paths first (optional):
$pathsFile = CONFIG_PATH . 'constants.paths.php';
if (is_file($pathsFile) && !function_exists('app_base')) {
    require_once $pathsFile; // defines app_base() in your project
}

// Define-once helper
if (!function_exists('define_if_absent')) {
    function define_if_absent(string $name, $value): void
    {
        if (!defined($name))
            define($name, $value);
    }
}
if (!function_exists('normalize_dir')) {
    function normalize_dir(string $path): string
    {
        return $path === '' ? '' : rtrim($path, "/\\") . DIRECTORY_SEPARATOR;
    }
}

// Optional: only if you truly need it here (you didn't use it):
if (!function_exists('app_context')) {
    // ignore; not needed by this file
}

// ── inputs / defaults (safe fallbacks) ───────────────────────────────────────
$COMPOSER_HOME = $COMPOSER_HOME ?? normalize_dir(APP_PATH . '.composer');
$COMPOSER_PROJECT_ROOT = $COMPOSER_PROJECT_ROOT ?? normalize_dir(
    function_exists('app_base') ? (app_base('project') ?: APP_PATH) : APP_PATH
);
$COMPOSER_VENDOR_DIR = $COMPOSER_VENDOR_DIR ?? normalize_dir($COMPOSER_PROJECT_ROOT . 'vendor');
$COMPOSER_JSON = $COMPOSER_JSON ?? ($COMPOSER_PROJECT_ROOT . 'composer.json');
$COMPOSER_LOCK = $COMPOSER_LOCK ?? ($COMPOSER_PROJECT_ROOT . 'composer.lock');
$COMPOSER_CONFIG_JSON = $COMPOSER_CONFIG_JSON ?? ($COMPOSER_HOME . 'config.json');
$COMPOSER_AUTH_JSON = $COMPOSER_AUTH_JSON ?? ($COMPOSER_HOME . 'auth.json');
$COMPOSER_CACHE_DIR = $COMPOSER_CACHE_DIR ?? normalize_dir($COMPOSER_HOME . 'cache');
$COMPOSER_DEFAULT_ARGS = $COMPOSER_DEFAULT_ARGS ?? ['--no-interaction', '--no-ansi']; // array!
$COMPOSER_BIN = $COMPOSER_BIN ?? null;

// ── define constants (idempotent) ───────────────────────────────────────────
define_if_absent('COMPOSER_HOME', normalize_dir($COMPOSER_HOME));
define_if_absent('COMPOSER_PROJECT_ROOT', normalize_dir($COMPOSER_PROJECT_ROOT));
define_if_absent('COMPOSER_VENDOR_DIR', normalize_dir($COMPOSER_VENDOR_DIR));
define_if_absent('COMPOSER_JSON', $COMPOSER_JSON);
define_if_absent('COMPOSER_LOCK', $COMPOSER_LOCK);
define_if_absent('COMPOSER_CONFIG', $COMPOSER_CONFIG_JSON);
define_if_absent('COMPOSER_AUTH', $COMPOSER_AUTH_JSON);

// Composer GitHub token (env > auth.json > null), idempotent
if (!defined('COMPOSER_GITHUB_OAUTH_TOKEN')) {
    $token = null;

    // 1) Environment variables first (most explicit)
    foreach (['COMPOSER_TOKEN', 'GITHUB_TOKEN', 'GH_TOKEN'] as $env) {
        $v = getenv($env);
        if (is_string($v) && $v !== '') {
            $token = $v;
            break;
        }
    }

    // 2) auth.json next (if present)
    if ($token === null && defined('COMPOSER_AUTH') && is_file(COMPOSER_AUTH)) {
        $json = @file_get_contents(COMPOSER_AUTH);
        if ($json !== false) {
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // composer’s canonical location
                if (!empty($data['github-oauth']['github.com'])) {
                    $token = (string) $data['github-oauth']['github.com'];
                    // sometimes stored as "github-token"
                } elseif (!empty($data['github-token'])) {
                    $token = (string) $data['github-token'];
                }
            }
        }
    }

    define('COMPOSER_GITHUB_OAUTH_TOKEN', $token ?? '');
}

define_if_absent('COMPOSER_CACHE_DIR', normalize_dir($COMPOSER_CACHE_DIR));
define_if_absent('COMPOSER_DEFAULT_ARGS', $COMPOSER_DEFAULT_ARGS);

// Only read JSON if it exists (avoid warnings)
if (!defined('COMPOSER_JSON_RAW') && is_file(COMPOSER_JSON)) {
    define('COMPOSER_JSON_RAW', (string) @file_get_contents(COMPOSER_JSON));
}

// ── discover composer executable ────────────────────────────────────────────
$resolvedBin = null;

// 1) Provided bin
if ($COMPOSER_BIN && is_string($COMPOSER_BIN) && @is_executable($COMPOSER_BIN)) {
    $resolvedBin = $COMPOSER_BIN;
}

// 2) command -v fallback
if (!$resolvedBin) {
    $probe = trim((string) @shell_exec('command -v composer 2>/dev/null'));
    if ($probe !== '' && @is_executable($probe)) {
        $resolvedBin = $probe;
    }
}

// 3) Local phar fallback
$pharPath = APP_PATH . 'composer.phar';
$pharExec = (is_file($pharPath)) ? ('php ' . escapeshellarg($pharPath)) : null;

// Define chosen executables
if ($resolvedBin) {
    define_if_absent('COMPOSER_BIN', $resolvedBin);
}
if ($pharExec) {
    define_if_absent('COMPOSER_PHAR', ['path' => $pharPath, 'exec' => $pharExec]);
}

// ── chosen command + debug struct ───────────────────────────────────────────
if (!defined('COMPOSER_EXEC_CMD')) {
    if (defined('COMPOSER_BIN'))
        define('COMPOSER_EXEC_CMD', COMPOSER_BIN);
    elseif (defined('COMPOSER_PHAR'))
        define('COMPOSER_EXEC_CMD', COMPOSER_PHAR['exec']);
    else
        define('COMPOSER_EXEC_CMD', null);
}

// ── Composer version (local detection only; no network) ─────────────────────
if (!defined('COMPOSER_VERSION')) {
    $version = null;

    // Try system composer
    if (defined('COMPOSER_BIN') && COMPOSER_BIN) {
        $out = trim((string) @shell_exec(escapeshellcmd(COMPOSER_BIN) . ' --version 2>/dev/null'));
        if ($out && preg_match('/Composer\s+version\s+([^\s]+)/i', $out, $m)) {
            $version = $m[1];
        }
    }

    // Try local phar
    if (!$version && defined('COMPOSER_PHAR') && is_array(COMPOSER_PHAR) && !empty(COMPOSER_PHAR['exec'])) {
        $out = trim((string) @shell_exec(COMPOSER_PHAR['exec'] . ' --version 2>/dev/null'));
        if ($out && preg_match('/Composer\s+version\s+([^\s]+)/i', $out, $m)) {
            $version = $m[1];
        }
    }

    define('COMPOSER_VERSION', $version ?: 'unknown');
}

define_if_absent('COMPOSER_EXEC', [
    'exec' => COMPOSER_EXEC_CMD,
    'args' => COMPOSER_DEFAULT_ARGS, // array
    'cwd' => COMPOSER_PROJECT_ROOT,
    'json' => COMPOSER_JSON,
    'lock' => COMPOSER_LOCK,
    'home' => COMPOSER_HOME,
    'config' => COMPOSER_CONFIG,
    'auth' => COMPOSER_AUTH,
    'cache_dir' => COMPOSER_CACHE_DIR,
    'vendor_dir' => COMPOSER_VENDOR_DIR,
]);

// ── DO NOT do network or writes here (keep constants pure) ──────────────────
// If you want a "latest version" value, expose a helper instead of a constant.
// Example helper (uses your existing function if present):
if (!function_exists('composer_latest_cached')) {
    function composer_latest_cached(bool $force = false): array
    {
        $errors = [];
        $version = null;

        if (function_exists('composer_latest_version')) {
            $version = composer_latest_version($errors, $force);
            return [$version, $errors];
        }
        $cacheFile = APP_PATH . 'var/composer/latest.txt';
        if (!$force && is_file($cacheFile)) {
            $v = trim((string) @file_get_contents($cacheFile));
            if ($v !== '')
                $version = $v;
        }
        return [$version, $errors];
    }
}

// If you insist on a constant, define only from cache (no network):
if (!defined('COMPOSER_LATEST')) {
    [$v,] = composer_latest_cached(false);
    if (is_string($v) && $v !== '')
        define('COMPOSER_LATEST', $v);
}

// ── optional runner (fixed '+'' bug and args handling) ──────────────────────
if (!function_exists('run_composer')) {
    /**
     * @return array{code:int,out:string,err:string}
     */
    function run_composer(string $subcmd, string $extra = '', ?string $cwd = null): array
    {
        $cwd = $cwd ?? COMPOSER_PROJECT_ROOT;
        $exec = COMPOSER_EXEC_CMD;
        if (!$exec) {
            return ['code' => 127, 'out' => '', 'err' => 'composer executable not found'];
        }
        $args = COMPOSER_DEFAULT_ARGS;
        $argsStr = is_array($args) ? implode(' ', $args) : (string) $args;

        // FIX: use '.' for concatenation, not '+'
        $cmd = trim($exec . ' ' . $argsStr . ' ' . $subcmd . ' ' . $extra);

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