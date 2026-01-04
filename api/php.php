<?php
// api/php.php
declare(strict_types=1);

use CodeInSync\Infrastructure\Runtime\Shutdown;
use CodeInSync\Infrastructure\Runtime\ProcessRunner;
use CodeInSync\Infrastructure\Runtime\PhpRuntime;

if (!defined('APP_BOOTSTRAPPED')) {
    // require_once dirname(__DIR__, 2) . '/bootstrap/bootstrap.php';
}

if (!defined('APP_DEV_MODE') || APP_DEV_MODE !== true) {
    throw new RuntimeException('Inline PHP execution is disabled.');
}

if (!class_exists(Shutdown::class, false)) {
    require APP_PATH . 'src/Infrastructure/Runtime/Shutdown.php';
    @class_alias(Shutdown::class, 'Shutdown');
}

if (!class_exists(ProcessRunner::class, false)) {
    require APP_PATH . 'src/Infrastructure/Runtime/ProcessRunner.php';
    @class_alias(ProcessRunner::class, 'ProcessRunner');
}

header('X-Content-Type-Options: nosniff');
header('Content-Type: application/json; charset=utf-8');

function cis_strip_wrapping_quotes(string $s): string
{
    $s = trim($s);
    if ($s === '')
        return $s;

    $a = $s[0];
    $b = $s[strlen($s) - 1];

    if (($a === '"' && $b === '"') || ($a === "'" && $b === "'")) {
        return substr($s, 1, -1);
    }
    return $s;
}

function cis_normalize_inline_php(string $s): string
{
    $s = trim($s);

    // 1) If input arrives as \"...\" or \'...\', strip those first
    if (preg_match('/^\\\\(["\'])(.*)\\\\\\1$/s', $s, $m)) {
        $s = $m[2];
    }

    // 2) Strip normal wrapping quotes "..." or '...'
    if (
        (str_starts_with($s, '"') && str_ends_with($s, '"')) ||
        (str_starts_with($s, "'") && str_ends_with($s, "'"))
    ) {
        $s = substr($s, 1, -1);
    }

    // 3) Unescape remaining \" and \'
    $s = str_replace(['\\"', "\\'"], ['"', "'"], $s);

    // 4) Ensure trailing semicolon
    $s = rtrim($s);
    if ($s !== '' && substr($s, -1) !== ';') {
        $s .= ';';
    }

    return $s;
}

/**
 * Accept:
 *  - php <code>
 *  - php -r "<code>"
 */
function cis_parse_php_cmd(string $cmd): ?array
{
    $cmd = trim($cmd);

    // php -r "<code>"
    if (preg_match('/^php\s+-r\s+(.+)$/is', $cmd, $m)) {
        $code = cis_normalize_inline_php($m[1]);
        return ['mode' => 'php -r', 'code' => $code];
    }

    // php <code> (not php -r)
    if (preg_match('/^php\s+(?!-r\b)(.+)$/is', $cmd, $m)) {
        $code = cis_normalize_inline_php($m[1]); // reuse: strips quotes + fixes escapes + adds ;
        return ['mode' => 'php', 'code' => $code];
    }

    return null;
}

function cis_php_exec(): string
{
    // 1) explicit constant wins
    if (defined('PHP_EXEC') && is_string(PHP_EXEC) && PHP_EXEC !== '') {
        return PHP_EXEC;
    }

    // 2) common absolute paths
    foreach (['/usr/bin/php', '/bin/php', '/usr/local/bin/php'] as $p) {
        if (is_file($p) && is_executable($p)) {
            return $p;
        }
    }

    // 3) last resort: rely on PATH
    return 'php';
}

function cis_php_build_cmd(string $code): array
{
    $php = cis_php_exec();
    return [
        $php,
        '-d',
        'display_errors=1',
        '-d',
        'html_errors=0',
        '-r',
        $code,
    ];
}

// --- HTTP execution point ---
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST required']);
    exit;
}

$cmd = (string) ($_POST['cmd'] ?? '');
if ($cmd === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing cmd']);
    exit;
}

// Dev gate (supports both "included by dispatcher" and direct HTTP access)
function cis_php_dev_gate(string $api = 'php'): void
{
    if (defined('APP_DEV_MODE') && APP_DEV_MODE === true) {
        return; // allowed
    }

    $msg = 'PHP runner disabled outside dev mode.';

    $payload = [
        'ok' => false,
        'api' => $api,
        'exit' => 403,
        'stdout' => '',
        'stderr' => $msg,
        'result' => null,
        'errors' => [$msg],
    ];

    // If INCLUDED by dispatcher, return payload to caller.
    if (\function_exists('cis_is_included_file') && cis_is_included_file(__FILE__)) {
        // returning from a function doesn't return from the file,
        // so we throw a special-purpose exception OR use a global.
        // Simpler: just return payload from file scope by ending execution here:
        $GLOBALS['__CIS_API_RETURN__'] = $payload;
        return;
    }

    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

// --- usage (file scope) ---
cis_php_dev_gate('php');

// If the gate stored a return payload, return it now (dispatcher include mode)
if (isset($GLOBALS['__CIS_API_RETURN__']) && is_array($GLOBALS['__CIS_API_RETURN__'])) {
    return $GLOBALS['__CIS_API_RETURN__'];
}

$parsed = cis_parse_php_cmd($cmd);
if ($parsed === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Unsupported command format', 'cmd' => $cmd]);
    exit;
}

$phpCmd = cis_php_build_cmd($parsed['code']);

// Run via your unified pipeline
$res = ProcessRunner::run($phpCmd, ['cwd' => getcwd(), 'timeout' => 10]);

// Normalize response fields if needed
$exit = (int) ($res['exit'] ?? 0);
$stdout = (string) ($res['out'] ?? '');
$stderr = (string) ($res['err'] ?? '');
$ok = ($exit === 0);

echo json_encode([
    'ok' => $ok,
    'api' => 'php',
    'mode' => $parsed['mode'],
    'cmd' => $phpCmd,
    'php_exec' => $phpCmd[0],
    'command' => $cmd,
    'prompt' => '$ ' . $cmd,
    'exit' => $exit,
    'stdout' => $stdout,
    'stderr' => $stderr,
    'result' => $stdout !== '' ? $stdout : null,
    'errors' => $stderr !== '' ? $stderr : null,
], JSON_UNESCAPED_SLASHES);

exit;

// Optional: if directly hit without cmd
if (cis_is_direct_http_file(__FILE__)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Missing cmd']);
    exit;
}