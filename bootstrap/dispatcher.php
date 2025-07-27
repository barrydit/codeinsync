<?php

if (!defined('APP_CONTEXT')) {
    define('APP_CONTEXT', PHP_SAPI === 'cli' ? 'cli' : 'www');
}

switch (APP_CONTEXT) {
    case 'cli':
        require_once __DIR__ . '/bootstrap.cli.php';
        break;

    case 'www':

        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'auth.php';

        if ($_SERVER['SCRIPT_NAME'] == '/dispatcher.php') {
            require_once BOOTSTRAP_PATH . 'dispatcher.php';
            require_once __DIR__ . '/../app/tools/code/git.php';
            exit;
        }


        $app = $_GET['app'] ?? null;

        if (!$app) {
            // No app specified: fallback full preload
            if (!defined('UI_LOADED')) {
                include_once APP_PATH . 'bootstrap/load_ui_apps.php';
            }
            break;
        }

        // Only allow safe filenames
        $app = basename($app);
        $file = APP_PATH . "app/{$app}.php";

        if (!is_file($file)) {
            http_response_code(404);
            echo json_encode(['error' => "App '{$app}' not found"]);
            break;
        }

        // 🚀 NEW: Dynamic UI app format support
        ob_start();
        $UI_APP = ['style' => '', 'body' => '', 'script' => ''];

        include $file;

        ob_end_clean(); // prevent any loose echo output

        header('Content-Type: application/json');
        echo json_encode($UI_APP);
        break;
    // Optional
    case 'php':

        echo 'APP_CONTEXT == ' . APP_CONTEXT;
        dd(get_required_files());
        break;
    // Optional
    case 'socket':
        require_once __DIR__ . '/bootstrap.sockets.php';


        echo 'APP_CONTEXT == ' . APP_CONTEXT;
        dd(get_required_files());
        break;
}

