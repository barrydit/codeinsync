<?php
// Return JSON from this endpoint
header('Content-Type: application/json'); // NOT Accept: application/json

// For browser testing: force JSON mode for any ?app=...
if (isset($_GET['app']) && !isset($_GET['json'])) {
    $_GET['json'] = 1;
}

// Ensure we’re in dispatcher mode early
if (!defined('APP_MODE') && (isset($_GET['app']) || isset($_POST['cmd']))) {
    define('APP_MODE', 'dispatcher');
}

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

// dd(get_required_files());

echo $contents;