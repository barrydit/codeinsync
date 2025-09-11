<?php
declare(strict_types=1);

// bootstrap/bootstrap.php

use App\Core\Registry;

/**
 * Bootstrap header (clean + idempotent)
 * - No output, header-safe
 * - Symlink-safe path resolution
 * - Defines: BOOTSTRAP_PATH, APP_PATH, CONFIG_PATH, BASE_PATH (legacy), APP_BOOTSTRAPPED
 * - Loads: config/functions.php, minimal constants set
 */

// ---- tiny helper to define-once -------------------------------------------
if (!function_exists('__def')) {
    function __def(string $name, $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}

// ---- core paths ------------------------------------------------------------
__def('BOOTSTRAP_PATH', rtrim(str_replace('\\', '/', __DIR__), '/') . '/'); // /bootstrap/
$parent = realpath(BOOTSTRAP_PATH . '..') ?: dirname(BOOTSTRAP_PATH);       // project root
__def('APP_PATH', rtrim(str_replace('\\', '/', $parent), '/') . '/');   // project root + /
__def('CONFIG_PATH', APP_PATH . 'config/');

// Legacy alias (if older code still uses BASE_PATH)
__def('BASE_PATH', BOOTSTRAP_PATH);

// Run-once guard
__def('APP_BOOTSTRAPPED', true);

// ---- helpers first (defines app_context(), app_base(), etc.) ---------------
require_once CONFIG_PATH . 'functions.php';

// ---- minimal constants needed early (env + paths + url + app) -------------
require_once CONFIG_PATH . 'constants.env.php';
require_once CONFIG_PATH . 'constants.paths.php';
require_once CONFIG_PATH . 'constants.url.php';
require_once CONFIG_PATH . 'constants.app.php';

// 1) (optional) early/fast dispatcher detection...

function wants_json_request(): bool
{
    // explicit override wins
    if (isset($_GET['json']) && $_GET['json'] !== '0')
        return true;

    $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
    if ($accept === '')
        return false;

    // parse Accept into [mime => q]
    $qs = [];
    foreach (explode(',', $accept) as $part) {
        $part = trim($part);
        if ($part === '')
            continue;
        $mime = $part;
        $q = 1.0;
        if (strpos($part, ';') !== false) {
            [$mime, $params] = array_map('trim', explode(';', $part, 2));
            if (preg_match('/(?:^|;) *q=([0-9.]+)/', $params, $m)) {
                $q = (float) $m[1];
            }
        }
        $qs[$mime] = $q;
    }

    // gather q-values for families
    $qJson = max(
        $qs['application/json'] ?? 0.0,
        $qs['application/*'] ?? 0.0
    );
    $qHtml = max(
        $qs['text/html'] ?? 0.0,
        $qs['application/xhtml+xml'] ?? 0.0,
        $qs['text/*'] ?? 0.0
    );
    $qAny = $qs['*/*'] ?? 0.0;

    // Only prefer JSON if it beats HTML and the wildcard
    return $qJson > $qHtml && $qJson >= $qAny && $qJson > 0.0;
}


$wantsDispatcher = (PHP_SAPI !== 'cli') && (
    (isset($_GET['app']) && $_GET['app'] !== '') ||
    (isset($_POST['cmd']) && is_string($_POST['cmd']) && preg_match('/^(composer|git|npm)\b/i', $_POST['cmd']))
);

// 2) (optional) detect if JSON is wanted (Accept header + ?json=1)
$accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');

// Only JSON if explicitly requested via ?json=1
// OR if Accept includes application/json and does NOT include text/html.
$wantsJson = stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

if ($wantsDispatcher && !defined('APP_MODE'))
    define('APP_MODE', 'dispatcher');

if (defined('APP_MODE') && APP_MODE === 'dispatcher') {
    $handled = require BOOTSTRAP_PATH . 'dispatcher.php';

    if ($handled === true)
        exit;

    if ((is_array($handled) || is_object($handled)) && $wantsJson) {
        if (!headers_sent())
            header('Content-Type: application/json; charset=utf-8');
        echo json_encode($handled, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // IMPORTANT: also suppress string output unless JSON is explicitly wanted
    if (is_string($handled) && $wantsJson) {
        if (!headers_sent())
            header('Content-Type: application/json; charset=utf-8');
        echo $handled;
        exit;
    }

    // Otherwise fall through to normal HTML rendering
}

require_once CONFIG_PATH . 'constants.runtime.php';
require_once CONFIG_PATH . 'constants.exec.php';

// [Optional] normalize CWD once (only if your code depends on it)
if (!@chdir(APP_PATH)) {
    throw new RuntimeException("Failed to chdir() to APP_PATH: " . APP_PATH);
}
defined('APP_CWD') || define('APP_CWD', getcwd());

// Single autoloader include (custom or Composer)
require_once APP_PATH . 'autoload.php'; // or 'vendor/autoload.php'