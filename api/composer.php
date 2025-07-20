<?php

//require_once __DIR__ . '/../config/constants.paths.php';
//require_once APP_PATH . 'classes/class.composer.php';

require_once APP_PATH . 'app/composer.php';

//header('Content-Type: application/json');

$composer = new COMPOSER();
/*
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}*/

$action = $_POST['action'] ?? null;

switch ($action) {
    case 'install':
        $pkg = $_POST['package'] ?? '';
        echo json_encode([
            'success' => true,
            'result' => $composer->install($pkg)
        ]);
        break;

    case 'search':
        $query = $_POST['query'] ?? '';
        echo json_encode([
            'success' => true,
            'result' => $composer->search($query)
        ]);
        break;

    case 'list':
        echo json_encode([
            'success' => true,
            'packages' => $composer->getInstalledPackages()
        ]);
        break;

    default:
        //http_response_code(400);
        //echo json_encode(['error' => 'Unknown action']);
        break;
}