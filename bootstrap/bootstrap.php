<?php
declare(strict_types=1);

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


if (defined('BASE_PATH') && BASE_PATH !== BOOTSTRAP_PATH)
    trigger_error('BASE_PATH differs from BOOTSTRAP_PATH; confirm intended semantics.', E_USER_NOTICE);

// --- Minimal env/
// Error reporting (adjust as needed)
//error_reporting(APP_DEBUG ? E_ALL : E_ALL & ~E_NOTICE & ~E_STRICT);
//ini_set('display_errors', APP_DEBUG ? '1' : '0');
//ini_set('log_errors', '1');

// A. Minimal path constants (no env dependence)
require_once __DIR__ . '/../config/constants.paths.php'; // defines APP_PATH, CONFIG_PATH, VENDOR_PATH, etc.

// B. Functions / classes used by env/runtime
require_once __DIR__ . '/../config/functions.php';

// C. Load ENV (sections, typed)
require_once __DIR__ . '/../config/constants.env.php';   // vendor-free

// D. EARLY: PHP ini + sane defaults (env required)
require_once __DIR__ . '/php-ini.php';  // error_reporting, timezone, mb_internal_encoding, etc.

// Define WWW_PATH (public web root) if not already defined
if (!defined('WWW_PATH'))
    // Adjust to your project layout
    define('WWW_PATH', APP_PATH . APP_PUBLIC . '/');

// Define shell prompt (used by console API)
$shell_prompt = (string) ((stripos(PHP_OS, 'WIN') === 0 ? get_current_user() : trim(shell_exec('whoami 2>&1'))) ?? $_ENV['APACHE']['USER']) . '@' . ($_ENV['APACHE']['SERVER']) . ':' . (!getcwd() ?: rtrim(APP_PATH, '/')) . (!isset($_GET['path']) ? '' : rtrim(ltrim($_GET['path'], '/'), '/')) . '# '; // e.g., "user@server:/path/to/app# "

// ---- Single autoloader include (custom or Composer) ------------------------
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
$autoloadFlag = ($_ENV['COMPOSER']['AUTOLOAD'] ?? true) !== false; // default true if unset
if ($autoloadFlag && is_file($composerAutoload)) {
    require_once $composerAutoload;
}

// E. Now runtime/url/app that may depend on ENV
require_once __DIR__ . '/../config/constants.runtime.php';
require_once __DIR__ . '/../config/constants.url.php';
require_once __DIR__ . '/../config/constants.app.php';

// Mark the app as ready/running exactly once
defined('APP_RUNNING') || define('APP_RUNNING', true);

// (Optional) composer autoload, config, constants, etc.
// require APP_PATH . 'vendor/autoload.php';
// require APP_PATH . 'config/constants.php';

// ─────────────────────────────────────────────────────────────────────────────
// 2) Route-or-Shell gate (decide: dispatcher vs render shell)
// ─────────────────────────────────────────────────────────────────────────────
// --- helpers (PHP 7 compatible) ---
$startsWith = static function (string $h, string $n): bool {
    return function_exists('str_starts_with')
        ? str_starts_with($h, $n)
        : substr($h, 0, strlen($n)) === $n;
};
$contains = static function (string $h, string $n): bool {
    return function_exists('str_contains')
        ? str_contains($h, $n)
        : strpos($h, $n) !== false;
};

// --- request facts ---
$isCli = (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
$appParam = isset($_GET['app']) && is_string($_GET['app']) ? trim($_GET['app']) : null;
$partParam = isset($_GET['part']) ? strtolower((string) $_GET['part']) : null;
$hasCmd = isset($_POST['cmd']) && is_string($_POST['cmd']) && $_POST['cmd'] !== '';

$isApiLikePath = !$isCli && (
    $startsWith($uri, '/api/') || isset($_GET['api'])
);

// Heuristic: things that want dispatcher (JSON/JS partials/APIs/commands)
$wantsDispatcher = !$isCli && (
    $hasCmd ||
    $appParam !== null ||
    $isApiLikePath ||
    isset($_GET['json']) ||
    $contains($accept, 'application/json') ||
    in_array($partParam, ['style', 'body', 'script'], true)
);

// Override via query or env (priority: defined > ?mode > env > heuristic)
$modeOverride = $_GET['mode']
    ?? ($_ENV['APP_MODE'] ?? getenv('APP_MODE') ?: null);

if (!defined('APP_MODE')) {
    if (in_array($modeOverride, ['dispatcher', 'web', 'cli'], true)) {
        define('APP_MODE', $modeOverride);
    } else {
        define('APP_MODE', $isCli ? 'cli' : ($wantsDispatcher ? 'dispatcher' : 'web'));
    }
}

// --- route by mode (do not emit HTML before this switch) ---
switch (APP_MODE) {
    case 'dispatcher':
        // no head.php; keep responses lean (JSON/JS/etc)
        require_once __DIR__ . '/dispatcher.php';
        return;

    case 'web':
        // normal web shell: safe to emit HTML
        require_once __DIR__ . '/head.php';
        require_once __DIR__ . '/legacy-aliases.php'; // if needed for web
        require_once __DIR__ . '/kernel.php';
        return;

    case 'cli':
        // no HTML in CLI
        require_once __DIR__ . '/cli.php';
        return;

    default:
        throw new LogicException('Unknown APP_MODE: ' . APP_MODE);
}


// Debug safely (won’t explode if moved): 
// dd(defined('APP_MODE') ? APP_MODE : '(undef)');

// ---- Gate --------------------------------------------------------------------
//if (APP_MODE === 'dispatcher') {
//    require __DIR__ . '/dispatcher.php';
//    return;
//}

// Your normal web shell
//require_once __DIR__ . '/kernel.php';

// ─────────────────────────────────────────────────────────────────────────────
// 3) Normal page shell (only when nothing routed to dispatcher)
// ─────────────────────────────────────────────────────────────────────────────


// Full app bootstrap path
// (web request, or CLI)

// --- end fast-path ---------------------------------------------------------

// [Optional] normalize CWD once (only if your code depends on it)
if (!@chdir(APP_PATH))
    throw new RuntimeException("Failed to chdir() to APP_PATH: " . APP_PATH);

defined('APP_CWD') || define('APP_CWD', getcwd());

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

// use CodeInSync\Core\Registry;
if (!class_exists(\CodeInSync\Core\Registry::class)) {
    require APP_PATH . 'src/Core/Registry.php';
    @class_alias(\CodeInSync\Core\Registry::class, 'Registry');
}

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
