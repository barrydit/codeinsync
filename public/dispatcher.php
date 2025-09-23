<?php
declare(strict_types=1);

// If you want to force JSON for any ?app=... request:
if (isset($_GET['app']) && !isset($_GET['json'])) {
    $_GET['json'] = 1;
}

// Call the bootstrap dispatcher and handle its contract:
// - true   => already emitted a response
// - array/object => we JSON-emit here
// - string => we echo as-is (HTML/text)
// - false/null/other => 404-ish
define('APP_MODE', 'dispatcher'); // anything !== 'web' triggers the gate
$handled = require __DIR__ . '/../bootstrap/dispatcher.php';

if ($handled === true) {
    exit; // fully handled (output already sent)
}

/*
// IMPORTANT: if bootstrap/dispatcher.php returns a callable, INVOKE it.
function capture(callable $fn): string
{
    ob_start();
    try {
        $fn();
        return ob_get_clean();
    } catch (\Throwable $e) {
        ob_end_clean();
        throw $e;
    }
}

// For debugging: see what actually got loaded

$contents = capture(function () {
    $maybeCallable = require __DIR__ . '/../bootstrap/dispatcher.php';
    if (is_callable($maybeCallable)) {
        $maybeCallable();
    }
});

dd(get_required_files());

echo $contents;

if (is_array($handled) || is_object($handled)) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($handled, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (is_string($handled)) {
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    echo $handled;
    exit;
}
*/

// Not handled
http_response_code(404);
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}
// echo json_encode(['error' => 'Not handled'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);