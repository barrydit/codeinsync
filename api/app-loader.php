<?php
$app = preg_replace('/[^a-z0-9_.]/i', '', $_GET['app'] ?? '');
$appFile = __DIR__ . '/../app/' . $app . '.php';

if (!file_exists($appFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'App not found']);
    exit;
}

ob_start();
include $appFile;
$ui = ob_get_clean();

$response = [
    'style'  => $UI_APPS[$app]['style']  ?? '',
    'body'   => $UI_APPS[$app]['body']   ?? '',
    'script' => $UI_APPS[$app]['script'] ?? ''
];

header('Content-Type: application/json');
echo json_encode($response);