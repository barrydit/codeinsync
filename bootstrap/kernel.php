<?php
declare(strict_types=1);

// Idempotent: safe if required multiple times in a request
if (defined('APP_KERNEL_LOADED'))
    return;
else
    define('APP_KERNEL_LOADED', true);

// Preconditions: bootstrap/bootstrap.php should have set these.
if (!defined('APP_PATH'))
    throw new RuntimeException('Kernel precondition failed: APP_PATH not defined.');

//if (!defined('APP_BASE'))
//    throw new RuntimeException('Kernel precondition failed: APP_BASE not defined.');
if (!defined('CONFIG_PATH'))
    throw new RuntimeException('Kernel precondition failed: CONFIG_PATH not defined.');
/*
if (!defined('CACHE_PATH'))
    throw new RuntimeException('Kernel precondition failed: CACHE_PATH not defined.');
if (!defined('STORAGE_PATH'))
    throw new RuntimeException('Kernel precondition failed: STORAGE_PATH not defined.');
if (!defined('LOGS_PATH'))
    throw new RuntimeException('Kernel precondition failed: LOGS_PATH not defined.');
if (!defined('TMP_PATH'))
    throw new RuntimeException('Kernel precondition failed: TMP_PATH not defined.');
if (!defined('VIEWS_PATH'))
    throw new RuntimeException('Kernel precondition failed: VIEWS_PATH not defined.');
if (!defined('PLUGINS_PATH'))
    throw new RuntimeException('Kernel precondition failed: PLUGINS_PATH not defined.');
if (!defined('COMMANDS_PATH'))
    throw new RuntimeException('Kernel precondition failed: COMMANDS_PATH not defined.');
if (!defined('APP_ENV'))
    throw new RuntimeException('Kernel precondition failed: APP_ENV not defined.');
if (!defined('APP_DEBUG'))
    throw new RuntimeException('Kernel precondition failed: APP_DEBUG not defined.');
if (!defined('APP_CONTEXT'))
    throw new RuntimeException('Kernel precondition failed: APP_CONTEXT not defined.');
if (!defined('APP_VERSION'))
    throw new RuntimeException('Kernel precondition failed: APP_VERSION not defined.');
if (!defined('APP_RELEASE'))
    throw new RuntimeException('Kernel precondition failed: APP_RELEASE not defined.');
if (!defined('APP_REQUEST_METHOD'))
    throw new RuntimeException('Kernel precondition failed: APP_REQUEST_METHOD not defined.');
if (!defined('APP_IS_CLI'))
    throw new RuntimeException('Kernel precondition failed: APP_IS_CLI not defined.');
if (!defined('APP_IS_WEB'))
    throw new RuntimeException('Kernel precondition failed: APP_IS_WEB not defined.');  */

// Register error/exception handlers (opt-in via env)

// bootstrap/kernel.php (web-only)
if (!defined('APP_RUNNING') || APP_MODE !== 'web') {
    throw new LogicException('kernel.php requires APP_MODE=web after bootstrap.');
}

require_once dirname(__DIR__) . '/config/config.php';

require_once dirname(__DIR__) . '/config/auth.php';

//defined('APP_RUNTIME_READY') || define('APP_RUNTIME_READY', 1);

require_once dirname(__DIR__) . '/config/constants.exec.php';
//defined('APP_EXEC_READY') || define('APP_EXEC_READY', 1);

// Register custom error and exception handlers
if (function_exists('app_error_handler'))
    set_error_handler('app_error_handler');
else
    set_error_handler([Shutdown::class, 'handleError']);

set_exception_handler(function (Throwable $e) { // [Shutdown::class, 'handleException']
    $msg = sprintf(
        'Uncaught %s: %s in %s:%d',
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    if (defined('APP_DEBUG') && APP_DEBUG) {
        if (PHP_SAPI === 'cli')
            fwrite(STDERR, $msg . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
        else
            echo '<pre style="color:#f66">' . $msg . "\n" . $e->getTraceAsString() . '</pre>';
    }
});

// ------------------------------------------------------
// Autoloader Registration (PSR-4 + Legacy Fallback)
// ------------------------------------------------------

function autoload_class(string $class): void
{
    $prefix = 'CodeInSync\\';
    $srcDir = APP_PATH . 'src/';      // NEW primary PSR-4 base
    $legacyPSR = APP_PATH . 'classes/'; // Old PSR-4-ish base

    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $candidate = $srcDir . str_replace('\\', '/', $relative) . '.php';
        if (is_file($candidate)) {
            require_once $candidate;
            return;
        }

        $legacyCandidate = $legacyPSR . str_replace('\\', '/', $relative) . '.php';
        if (is_file($legacyCandidate)) {
            require_once $legacyCandidate;
            return;
        }
    }

    // Legacy flat fallbacks: class.foo.php and interfaces/*.php
    $lower = strtolower($class);
    foreach ([
        APP_PATH . "classes/class.$lower.php",
        APP_PATH . "interfaces/$class.php",
    ] as $f) {
        if (is_file($f)) {
            require_once $f;
            return;
        }
    }

    error_log("Autoloader could not find class: $class");
}
spl_autoload_register('autoload_class');

// ------------------------------------------------------
// Shutdown Hook (for fatal error logging & profiling)
// ------------------------------------------------------

require_once __DIR__ . '/coverage-report.php';

/**
 * Fatal shutdown handler (refactored)
 *
 * Goals:
 * - Only handle *fatal* shutdown errors
 * - Log reliably (error_log when enabled, file fallback otherwise)
 * - Emit JSON 500 only if nothing was emitted yet
 * - Run cleanup (env json, coverage driver stop, etc.)
 */

$reqId  = \bin2hex(\random_bytes(6));
$logFile = APP_PATH . 'var/log/php-error.log';

\register_shutdown_function(static function () use ($reqId, $logFile): void {
    $err = \error_get_last();
    if (!$err) {
        return;
    }

    if (!is_fatal_shutdown_error((int) ($err['type'] ?? 0))) {
        return;
    }

    // Make sure APP_START exists for timing, even if bootstrap died early
    if (!\defined('APP_START')) {
        \define('APP_START', $_SERVER['REQUEST_TIME_FLOAT'] ?? \microtime(true));
    }

    $line = format_fatal_error_line($err);
    log_fatal_line($line, $logFile);

    // Emit JSON response (only if nothing else was emitted)
    maybe_emit_fatal_json($reqId);

    // Load helpers (best-effort; avoid throwing inside shutdown handler)
    safe_require_once(\dirname(__DIR__) . '/config/functions.php');

    // Cleanup env json (best-effort)
    safe_cleanup_env_json();

    // Stop coverage drivers cleanly
    stop_coverage_driver_if_enabled();

    // Optional: if you want timing available for logs, uncomment:
    // $execTime = \round(((\defined('APP_END') ? APP_END : \microtime(true)) - APP_START), 3);
    // \error_log(\date('c') . " | Execution time: {$execTime}s");
});

/* --------------------------------------------------------------------------
 * Helpers (keep these near bootstrap or move into your Shutdown class later)
 * -------------------------------------------------------------------------- */

function is_fatal_shutdown_error(int $type): bool
{
    // Add/remove types here if you want stricter/looser behavior
    return \in_array($type, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true);
}

/**
 * @param array{type:int,message:string,file:string,line:int} $err
 */
function format_fatal_error_line(array $err): string
{
    $msg  = (string) ($err['message'] ?? 'Unknown fatal error');
    $file = (string) ($err['file'] ?? 'unknown file');
    $line = (int) ($err['line'] ?? 0);

    return \sprintf('Fatal error: %s in %s on line %d', $msg, $file, $line);
}

function log_fatal_line(string $line, string $logFile): void
{
    $timestamped = \date('c') . ' | ' . $line;

    // Prefer PHP's configured error_log
    if ((bool) \ini_get('log_errors')) {
        \error_log($timestamped);
        return;
    }

    // Fallback if error_log is disabled
    @\file_put_contents($logFile, $timestamped . PHP_EOL, FILE_APPEND);
}

function maybe_emit_fatal_json(string $reqId): void
{
    if (\headers_sent()) {
        return;
    }

    if (\defined('APP_EMITTED_OUTPUT') || \defined('APP_EMITTED_JSON')) {
        return;
    }

    \http_response_code(500);
    \header('Content-Type: application/json; charset=utf-8');

    echo \json_encode([
        'ok' => false,
        'error' => 'FATAL_ERROR',
        'req_id' => $reqId,
    ]);
}

function safe_require_once(string $path): void
{
    try {
        if (\is_file($path)) {
            require_once $path;
        }
    } catch (\Throwable $e) {
        // Never let shutdown handler crash
        \error_log('[shutdown] require_once failed: ' . $e->getMessage());
    }
}

function safe_cleanup_env_json(): void
{
    try {
        if (\class_exists(\CodeInSync\Infrastructure\Runtime\Shutdown::class, false)) {
            $p = \CodeInSync\Infrastructure\Runtime\Shutdown::getEnvJsonPath();
            if (\is_string($p) && $p !== '' && \is_file($p)) {
                \CodeInSync\Infrastructure\Runtime\Shutdown::unlinkEnvjson();
            }
        } elseif (\class_exists('Shutdown', false)) {
            // If you also have a global alias
            $p = \Shutdown::getEnvJsonPath();
            if (\is_string($p) && $p !== '' && \is_file($p)) {
                \Shutdown::unlinkEnvjson();
            }
        }
    } catch (\Throwable $e) {
        \error_log('[shutdown] env json cleanup failed: ' . $e->getMessage());
    }
}

function stop_coverage_driver_if_enabled(): void
{
    if (!\defined('APP_COVERAGE_DRIVER')) {
        return;
    }

    try {
        if (APP_COVERAGE_DRIVER === 'xdebug' && \function_exists('xdebug_stop_code_coverage')) {
            // Optional: stop if you started it elsewhere
            // \xdebug_stop_code_coverage();
            return;
        }

        if (APP_COVERAGE_DRIVER === 'pcov' && \function_exists('pcov\\stop')) {
            \pcov\stop();
            return;
        }
    } catch (\Throwable $e) {
        \error_log('[shutdown] stopping coverage driver failed: ' . $e->getMessage());
    }
}

// ------------------------------------------------------
// Path Resolution Logic

return true;