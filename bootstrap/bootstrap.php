<?php
declare(strict_types=1);

use App\Core\Registry;

if (defined('APP_BOOTSTRAPPED')) // Already fully bootstrapped in this request
    return;
else
    define('APP_BOOTSTRAPPED', true);

// ------------------------------------------------------
// Minimal Environment Setup
// ------------------------------------------------------

// --- Path resolution (symlink-safe)

// ─────────────────────────────────────────────────────────────────────────────
// 1) Minimal environment / path setup (keep your own logic here as needed)
// ─────────────────────────────────────────────────────────────────────────────

$__file = __FILE__;
$__boot = rtrim(str_replace('\\', '/', dirname($__file)), '/') . '/';
$__bootReal = @realpath($__boot) ?: $__boot;
defined('BOOTSTRAP_PATH') || define('BOOTSTRAP_PATH', $__bootReal . DIRECTORY_SEPARATOR);
$__app = rtrim(dirname(BOOTSTRAP_PATH), '/') . '/';
$__appReal = @realpath($__app) ?: $__app;
defined('APP_PATH') || define('APP_PATH', $__appReal . DIRECTORY_SEPARATOR);
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . 'config' . DIRECTORY_SEPARATOR);
// optional legacy: 
// defined('BASE_PATH') || define('BASE_PATH', BOOTSTRAP_PATH);

// ─────────────────────────────────────────────────────────────────────────────
// 0) One-time guard
// ─────────────────────────────────────────────────────────────────────────────

// Define WWW_PATH (public web root) if not already defined
if (!defined('WWW_PATH'))
    // Adjust to your project layout
    define('WWW_PATH', APP_PATH . 'public/');

if (defined('BASE_PATH') && BASE_PATH !== BOOTSTRAP_PATH)
    trigger_error('BASE_PATH differs from BOOTSTRAP_PATH; confirm intended semantics.', E_USER_NOTICE);

// --- Minimal env/debug/timezone
if (!defined('APP_DEBUG'))
    define('APP_DEBUG', false);
if (!ini_get('date.timezone'))
    date_default_timezone_set('America/Vancouver'); // 'UTC'
// Error reporting (adjust as needed)
error_reporting(APP_DEBUG ? E_ALL : E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// ---- helpers first (defines app_context(), app_base(), etc.) ---------------
require_once CONFIG_PATH . 'functions.php';

// ---- minimal constants needed early (env + paths + url + app) -------------
// --- Canonical constants order
require_once CONFIG_PATH . 'constants.env.php';   // vendor-free
require_once CONFIG_PATH . 'constants.paths.php'; // defines APP_PATH, CONFIG_PATH, VENDOR_PATH, etc.

require_once APP_PATH . 'bootstrap/php-ini.php';  // error_reporting, timezone, mb_internal_encoding, etc.

// ---- Single autoloader include (custom or Composer) ------------------------
$composerAutoload = APP_PATH . 'vendor/autoload.php';
$autoloadFlag = ($_ENV['COMPOSER']['AUTOLOAD'] ?? true) !== false; // default true if unset
if ($autoloadFlag && is_file($composerAutoload)) {
    require_once $composerAutoload;
}

require_once CONFIG_PATH . 'constants.runtime.php';
require_once CONFIG_PATH . 'constants.url.php';
require_once CONFIG_PATH . 'constants.app.php';

require_once CONFIG_PATH . 'config.php';

// (Optional) composer autoload, config, constants, etc.
// require APP_PATH . 'vendor/autoload.php';
// require APP_PATH . 'config/constants.php';

// ─────────────────────────────────────────────────────────────────────────────
// 2) Route-or-Shell gate (decide: hand off to dispatcher vs render page shell)
// ─────────────────────────────────────────────────────────────────────────────
$isCli = (PHP_SAPI === 'cli');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');

$appParam = isset($_GET['app']) && is_string($_GET['app']) ? trim($_GET['app']) : null;
$partParam = isset($_GET['part']) ? strtolower((string) $_GET['part']) : null;
$hasCmd = isset($_POST['cmd']) && is_string($_POST['cmd']) && $_POST['cmd'] !== '';

$isApiLikePath = !$isCli && (
    (function ($u) {
        return function_exists('str_starts_with') ? str_starts_with($u, '/api/') : substr($u, 0, 5) === '/api/'; })($uri)
    || isset($_GET['api'])
);

// Want dispatcher if: POST cmd, or any app route, or API-ish path
$wantsDispatcher = !$isCli && ($hasCmd || $appParam !== null || $isApiLikePath);

// Non-web modes force dispatcher too (optional, keep if you use APP_MODE)
if (defined('APP_MODE') && APP_MODE !== 'web') {
    $wantsDispatcher = true;
}

if ($wantsDispatcher) {
    // Hand off to the self-contained dispatcher (emits and exits)
    require APP_PATH . 'bootstrap/dispatcher.php';
    return;
}

// ─────────────────────────────────────────────────────────────────────────────
// 3) Normal page shell (only when nothing routed to dispatcher)
// ─────────────────────────────────────────────────────────────────────────────


// Full app bootstrap path
// (web request, or CLI)

// --- end fast-path ---------------------------------------------------------

require_once CONFIG_PATH . 'auth.php';

//defined('APP_RUNTIME_READY') || define('APP_RUNTIME_READY', 1);

require_once CONFIG_PATH . 'constants.exec.php';
//defined('APP_EXEC_READY') || define('APP_EXEC_READY', 1);

// [Optional] normalize CWD once (only if your code depends on it)
if (!@chdir(APP_PATH))
    throw new RuntimeException("Failed to chdir() to APP_PATH: " . APP_PATH);

defined('APP_CWD') || define('APP_CWD', getcwd());

require_once APP_PATH . 'bootstrap/kernel.php';

/*
//require_once APP_PATH . 'bootstrap/events.php';
//require_once APP_PATH . 'bootstrap/middleware.php';
require_once APP_PATH . 'bootstrap/services.php';
require_once APP_PATH . 'bootstrap/session.php';
require_once APP_PATH . 'bootstrap/user.php';
require_once APP_PATH . 'bootstrap/csrf.php';
require_once APP_PATH . 'bootstrap/flash.php';
require_once APP_PATH . 'bootstrap/routes.php';
require_once APP_PATH . 'bootstrap/template.php';
require_once APP_PATH . 'bootstrap/locale.php';
require_once APP_PATH . 'bootstrap/notifications.php';
require_once APP_PATH . 'bootstrap/recaptcha.php';
require_once APP_PATH . 'bootstrap/validation.php';
require_once APP_PATH . 'bootstrap/markdown.php';
require_once APP_PATH . 'bootstrap/updates.php';
require_once APP_PATH . 'bootstrap/plugins.php';
require_once APP_PATH . 'bootstrap/shortcuts.php';
require_once APP_PATH . 'bootstrap/registry.php';
//require_once APP_PATH . 'bootstrap/commands.php';
//require_once APP_PATH . 'bootstrap/cli.php';
//require_once APP_PATH . 'bootstrap/sockets.php';
//require_once APP_PATH . 'bootstrap/web.php';
//require_once APP_PATH . 'bootstrap/api.php';
//require_once APP_PATH . 'bootstrap/admin.php';
require_once APP_PATH . 'bootstrap/debug.php';
//require_once APP_PATH . 'bootstrap/logging.php';
require_once APP_PATH . 'bootstrap/shutdown.php';
Registry::set('app.start_time', microtime(true));
// ---------------------------------------------------------
// [2] More Path and Context Constants
// ---------------------------------------------------------
require_once CONFIG_PATH . 'constants.paths2.php';
//defined('APP_PATHS_READY') || define('APP_PATHS_READY', 1);
*/
// ---------------------------------------------------------
// [3] Sanitize Input (basic)
// ---------------------------------------------------------
// Example: sanitize ?path= for directory traversal
// (your app may require more advanced input validation/sanitation)
if (isset($_GET['path'])) {
    $path = trim((string) $_GET['path']);
    $path = trim($path, "\\/");           // drop leading/trailing slashes
    $real = realpath(APP_PATH . ($path ? $path . DIRECTORY_SEPARATOR : ''));
    if ($real && str_starts_with($real, APP_PATH)) {
        $_GET['path'] = substr($real, strlen(APP_PATH));
    } else {
        unset($_GET['path']); // invalid
    }
}

defined('APP_BOOTSTRAPPED') || define('APP_BOOTSTRAPPED', 1);