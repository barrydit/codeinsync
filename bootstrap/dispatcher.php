

<?php
// /bootstrap/dispatcher.php

return (function () {
    // Get app or command
    $app = $_POST['app'] ?? $_GET['app'] ?? null;
    $cmd = $_POST['cmd'] ?? null;

    // Route for predefined apps
    $routes = [
        'composer' => APP_PATH . 'api/composer.php',
        'git'      => APP_PATH . 'api/git.php',
        'npm'      => APP_PATH . 'api/npm.php',
    ];

    // === 1. If app route matched
    if ($app && isset($routes[$app])) {
        // Can optionally return API output
        return require $routes[$app];
    }

    // === 2. Command pattern routes
    $commandRoutes = [
        '/^git\s+/i'      => APP_PATH . 'api/git.php',
        '/^composer\s+/i' => APP_PATH . 'api/composer.php',
        '/^npm\s+/i'      => APP_PATH . 'api/npm.php',
        '/^(chdir|cd)\s+/i' => APP_PATH . 'app/directory.php',
        '/^ls\s+/i'       => APP_PATH . 'app/list.php',
        '/^php\s+/i'      => CONFIG_PATH . 'runtime/php.php',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cmd) {
        foreach ($commandRoutes as $pattern => $handlerFile) {
            if (preg_match($pattern, $cmd)) {
                return require $handlerFile;
            }
        }
    }

    // === 3. No app or command matched
    return [
        'status' => 'error',
        'message' => 'No valid app or command matched.',
        'app' => $app,
        'cmd' => $cmd,
    ];
})();

/*
    * This file is part of the project bootstrap sequence.
    * It handles API routing for specific apps or commands.
    * 
    * - If a valid app is requested, it routes to the corresponding API handler.
    * - If a command is posted, it matches against predefined patterns and routes accordingly.
    * - If no match is found, it returns an error response.

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
            file_exists(CONFIG_PATH . 'constants.env.php') && require_once CONFIG_PATH . 'constants.env.php';

            file_exists(CONFIG_PATH . 'constants.env.php') && require_once CONFIG_PATH . 'constants.url.php';

            file_exists(CONFIG_PATH . 'config.php') && require_once CONFIG_PATH . 'config.php';

            require_once BOOTSTRAP_PATH . 'dispatcher.php';
            require_once __DIR__ . '/../app/tools/code/git.php';

            break;
        }
        dd(get_required_files());

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

*/