<?php
declare(strict_types=1);

use App\Core\Registry;

if (defined('APP_BOOTSTRAPPED')) // Already fully bootstrapped in this request
    return;

// ------------------------------------------------------
// Minimal Environment Setup
// ------------------------------------------------------

// --- Path resolution (symlink-safe)
$__file = __FILE__;
$__boot = rtrim(str_replace('\\', '/', dirname($__file)), '/') . '/';
$__bootReal = @realpath($__boot) ?: $__boot;
defined('BOOTSTRAP_PATH') || define('BOOTSTRAP_PATH', $__bootReal);
$__app = rtrim(dirname(BOOTSTRAP_PATH), '/') . '/';
$__appReal = @realpath($__app) ?: $__app;
defined('APP_PATH') || define('APP_PATH', $__appReal . DIRECTORY_SEPARATOR);
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . 'config' . DIRECTORY_SEPARATOR);
// optional legacy: 
// defined('BASE_PATH') || define('BASE_PATH', BOOTSTRAP_PATH);

if (defined('BASE_PATH') && BASE_PATH !== BOOTSTRAP_PATH)
    trigger_error('BASE_PATH differs from BOOTSTRAP_PATH; confirm intended semantics.', E_USER_NOTICE);

// --- Minimal env/debug/timezone
if (!defined('APP_DEBUG'))
    define('APP_DEBUG', false);
if (!ini_get('date.timezone'))
    date_default_timezone_set('America/Vancouver'); // 'UTC'
error_reporting(APP_DEBUG ? E_ALL : E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

// ---- helpers first (defines app_context(), app_base(), etc.) ---------------
require_once CONFIG_PATH . 'functions.php';

// ---- minimal constants needed early (env + paths + url + app) -------------
// --- Canonical constants order
require_once CONFIG_PATH . 'constants.env.php';
require_once CONFIG_PATH . 'constants.paths.php';
require_once CONFIG_PATH . 'constants.runtime.php';
require_once CONFIG_PATH . 'constants.url.php';
require_once CONFIG_PATH . 'constants.app.php';

// --- Fast dispatcher path (avoid loading heavy constants) --------------------
// --- Early dispatcher fast-path (API only) --------------------------------
$isCli = (PHP_SAPI === 'cli');
$appParam = isset($_GET['app']) ? (string) $_GET['app'] : null;
$hasCmd = isset($_POST['cmd']) && is_string($_POST['cmd']) && $_POST['cmd'] !== '';
$accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');

// explicit JSON?
$wantsJson = !$isCli && (
    (isset($_GET['json']) && $_GET['json'] !== '0') ||
    strpos($accept, 'application/json') !== false ||
    strpos($accept, 'text/json') !== false
);

// explicit JS? (also allow ?part=script as a convenience)
$wantsJs = !$isCli && (
    strpos($accept, 'text/javascript') !== false ||
    strpos($accept, 'application/javascript') !== false ||
    (isset($_GET['part']) && $_GET['part'] === 'script')
);

// API iff: command (always) OR app request that asks for JSON or JS
$wantsDispatcher = !$isCli && ($hasCmd || ($appParam && ($wantsJson || $wantsJs)));

if ($wantsDispatcher) {
    $obLevel = ob_get_level();

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Vary: Accept');
    }

    ob_start();
    try {
        $res = require APP_PATH . 'bootstrap/dispatcher.php';
        $buffer = ob_get_clean();
    } catch (\Throwable $e) {
        while (ob_get_level() > $obLevel)
            ob_end_clean();
        throw $e;
    }

    // ===================== SELECTIVE EMIT (drop-in) =====================

    // Commands: always JSON straight through
    if ($hasCmd) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Vary: Accept');
        }
        $payload = ($res !== null && $res !== '') ? $res : ($buffer !== '' ? $buffer : []);
        echo is_string($payload) ? $payload : json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit;
    }

    // App route: dispatcher returns full UI payload array
    $ui = is_array($res) ? $res : [];
    $style = isset($ui['style']) ? (string) $ui['style'] : '';
    $body = isset($ui['body']) ? (string) $ui['body'] : '';
    $script = isset($ui['script']) ? (string) $ui['script'] : '';

    // If JS requested, emit ONLY the script as JavaScript
    if ($wantsJs) {
        if (!headers_sent()) {
            header('Content-Type: text/javascript; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Vary: Accept');
        }
        echo $script;
        exit;
    }

    // Otherwise (JSON), emit ONLY style & body (omit script)
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Vary: Accept');
    }
    echo json_encode(['style' => $style, 'body' => $body, 'script' => $script], JSON_UNESCAPED_SLASHES);
    exit; // API path only

    // =================== END SELECTIVE EMIT (drop-in) ===================
}
// --- end fast-path ---------------------------------------------------------

require_once CONFIG_PATH . 'auth.php';

//defined('APP_RUNTIME_READY') || define('APP_RUNTIME_READY', 1);

require_once CONFIG_PATH . 'constants.exec.php';
//defined('APP_EXEC_READY') || define('APP_EXEC_READY', 1);

// [Optional] normalize CWD once (only if your code depends on it)
if (!@chdir(APP_PATH))
    throw new RuntimeException("Failed to chdir() to APP_PATH: " . APP_PATH);

defined('APP_CWD') || define('APP_CWD', getcwd());

// Single autoloader include (custom or Composer)
if (is_file(APP_PATH . 'vendor/autoload.php') && $_ENV['COMPOSER']['AUTOLOAD'] !== false)
    require_once APP_PATH . 'vendor/autoload.php';

require_once APP_PATH . 'bootstrap/php-ini.php';

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