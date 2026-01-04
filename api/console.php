<?php
// api/console.php
declare(strict_types=1);

use CodeInSync\Infrastructure\Runtime\CommandRouter;
use CodeInSync\Infrastructure\Runtime\BuiltinsRuntime;
use CodeInSync\Infrastructure\Runtime\GitRuntime;
use CodeInSync\Infrastructure\Runtime\PhpRuntime;

header('X-Content-Type-Options: nosniff');

if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
}

// Manual includes (until PSR-4/autoload is always available)
$req = static function (string $rel): void {
    $file = APP_PATH . ltrim($rel, '/');
    if (is_file($file))
        require_once $file;
};

$req('src/Infrastructure/Runtime/RuntimeInterface.php');
$req('src/Infrastructure/Runtime/CommandRouter.php');
$req('src/Infrastructure/Runtime/BuiltinsRuntime.php');
$req('src/Infrastructure/Runtime/GitRuntime.php');
$req('src/Infrastructure/Git/GitManager.php'); // GitRuntime uses it
$req('src/Infrastructure/Runtime/PhpRuntime.php');
$req('src/Infrastructure/Runtime/ProcessRunner.php'); // PhpRuntime needs it

// --- helpers (your existing environment probably has these already) ---
$included = function_exists('cis_is_included_file') && cis_is_included_file(__FILE__);
$direct = function_exists('cis_is_direct_http_file') && cis_is_direct_http_file(__FILE__);

// --- POST validation ---
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $payload = ['ok' => false, 'error' => 'POST required'];
    if ($included)
        return $payload;

    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$cmd = (string) ($_POST['cmd'] ?? '');
$ctx = [
    'cwd' => (string) ($_POST['cwd'] ?? getcwd()),
];

// Optional: dev gate
if (!defined('APP_DEV_MODE') || APP_DEV_MODE !== true) {
    $payload = [
        'ok' => false,
        'runtime' => 'console',
        'command' => $cmd,
        'prompt' => '$ ' . $cmd,
        'exit' => 403,
        'stdout' => '',
        'stderr' => 'Console disabled outside dev mode.',
        'meta' => ['code' => 'DEV_GATE'],
    ];

    if ($included)
        return $payload;

    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// Set working dir (safe-ish)
if (!empty($ctx['cwd']) && is_dir($ctx['cwd'])) {
    @chdir($ctx['cwd']);
}

// Register runtimes (order matters: builtins first, then tool runtimes)
$router = new CommandRouter([
    new BuiltinsRuntime(),
    new GitRuntime(),
    // next:
    // new ComposerRuntime(),
    // new NpmRuntime(),
    new PhpRuntime(),
]);

$res = $router->dispatch($cmd, $ctx);

// If included by dispatcher, return array
if ($included) {
    return $res;
}

// Direct HTTP response (JSON or text/plain)
$wantText = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'text/plain');
if ($wantText) {
    header('Content-Type: text/plain; charset=utf-8');
    $p = $res['prompt'] ?? ('$ ' . $cmd);
    $out = trim((string) ($res['stdout'] ?? ''));
    $err = trim((string) ($res['stderr'] ?? ''));
    echo $p . "\n";
    if ($out !== '')
        echo $out . "\n";
    if ($err !== '')
        echo $err . "\n";
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
