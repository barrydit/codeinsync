<?php
require_once '../bootstrap/bootstrap.php'; // if needed

// TODO: validate & sanitize $_GET['path'] so it's inside allowed dirs
$path = $_GET['path'] ?? '';
// e.g. ensure it's under some ROOT and not doing ../ tricks

$filepath = realpath(APP_PATH . APP_ROOT . APP_ROOT_DIR . ltrim($_GET['file'], '/'));

if (!is_file($filepath)) {
    http_response_code(404);
    echo "File not found";
    exit;
}

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: text/plain; charset=utf-8');

//header('X-Debug-Content-Type', headers_list()[0] ?? 'none');

readfile($filepath);
exit;