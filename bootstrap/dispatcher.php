<?php
declare(strict_types=1);

// /bootstrap/dispatcher.php — unified dispatcher (public + bootstrap)

// Hint bootstrap to do a minimal/core load (no heavy/full boot)
defined('APP_MODE') || define('APP_MODE', 'dispatcher');
require_once __DIR__ . '/bootstrap.php';

return (function (): bool{
    /* ───────────────────────────── Helpers ───────────────────────────── */

    $acceptsJson = function (): bool{
        if (isset($_GET['json']))
            return true;
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return stripos($accept, 'application/json') !== false;
    };

    $json_out = function (array $payload, int $code = 200): bool{
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        }
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return true; // handled
    };

    $json_error = function (string $message, int $code = 400, array $extra = []) use ($json_out): bool{
        return $json_out(array_merge(['error' => $message], $extra), $code);
    };

    $bail = function (string $message, int $code = 500) {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo $message;
        exit;
    };

    // On-demand constants loading for specific features
    $ensure_constants_for = function (string $key): void{
        $need = function (string $file): void{
            if (is_file($file))
                require_once $file;
            // else: silently skip or log; your call
        };

        switch ($key) {
            case 'composer':
                $need(APP_PATH . 'config/constants.composer.php');
                $need(APP_PATH . 'config/functions.composer.php');

                $errors = [];
                $force = !empty($_GET['refresh']) || (!empty($_POST['refresh']) && $_POST['refresh']); // optional manual refresh

                $latest = composer_latest_version($errors, $force);
                // Use it however you need:
                if ($latest) {
                    defined('COMPOSER_LATEST') || define('COMPOSER_LATEST', $latest);
                }

                break;

            case 'git':
                $need(APP_PATH . 'config/constants.git.php');
                // $need(APP_PATH . 'config/functions.git.php');
                break;

            case 'npm':
                $need(APP_PATH . 'config/constants.npm.php');
                // $need(APP_PATH . 'config/functions.npm.php');
                break;
        }


        // Map feature keys to constants files; extend as needed
/*        $map = [
            'composer' => APP_PATH . 'config/constants.composer.php',
            'git' => APP_PATH . 'config/constants.git.php',
            'npm' => APP_PATH . 'config/constants.npm.php',
            // 'nodes'  => APP_PATH . 'config/constants.nodes.php',
        ];
        if (isset($map[$key]) && is_file($map[$key])) {
            require_once $map[$key];
        }
*/
    };

    // Safe include runner
    $run = function (string $file): bool{
        require $file;
        return true; // handled
    };

    /* ───────────────────────────── Inputs ───────────────────────────── */

    $app = $_POST['app'] ?? $_GET['app'] ?? null;   // route slug
    $cmd = $_POST['cmd'] ?? null;                   // command string

    /* ─────────────────────── API / Command Routing ───────────────────── */

    // Whitelisted API app routes (server-side actions)
    $apiRoutes = [
        'composer' => APP_PATH . 'api/composer.php',
        'git' => APP_PATH . 'api/git.php',
        'npm' => APP_PATH . 'api/npm.php',
        // add more whitelisted slugs here
    ];

    // Command pattern routes for POST cmd="git ...", "composer ...", "npm ..."
    $commandRoutes = [
        '/^git\s+/i' => ['file' => APP_PATH . 'api/git.php', 'key' => 'git'],
        '/^composer\s+/i' => ['file' => APP_PATH . 'api/composer.php', 'key' => 'composer'],
        '/^npm\s+/i' => ['file' => APP_PATH . 'api/npm.php', 'key' => 'npm'],
    ];

    // 1) Command route takes priority if present
    if ($cmd) {
        foreach ($commandRoutes as $pattern => $target) {
            if (preg_match($pattern, $cmd)) {
                $ensure_constants_for($target['key']);
                return $run($target['file']); // handled by the API script
            }
        }
        // Unknown command
        return $acceptsJson()
            ? $json_error('Unsupported command', 400, ['cmd' => $cmd])
            : false;
    }

    // 2) API app routes (?app=composer|git|npm)
    if ($app !== null && isset($apiRoutes[$app])) {
        $ensure_constants_for($app);
        return $run($apiRoutes[$app]); // handled
    }

    /* ────────────────────────── UI App Routing ─────────────────────────
       If you request something like ?app=visual/nodes&json, we’ll resolve
       a file under APP_PATH/app/... and expect it to set $UI_APP = [
         'style' => '...', 'body' => '...', 'script' => '...'
       ] which we will return as JSON when JSON is requested.
    -------------------------------------------------------------------- */

    $wantsHtmlView = isset($_GET['view']) && $_GET['view'] === 'html';

    // Only attempt UI app JSON flow if the client wants JSON or explicitly asked `?json`
    if ($app !== null && !isset($apiRoutes[$app])) {

        if ($wantsHtmlView) {
            // render an HTML shell using $UI_APP['style'], ['body'], ['script']
            // (or return false to let index.php render)
            // return false; 
        }

        // Hard guard against traversal
        if (strpos($app, '..') !== false) {
            return $json_error('Invalid app slug', 400, ['app' => $app]);
        }

        // Optional whitelist of allowed UI roots
        $uiRoots = [
            APP_PATH . 'app/',
            APP_PATH . 'app/tools/',
            APP_PATH . 'app/visual/',
        ];

        // Resolve to a PHP file under the allowed roots
        $relative = trim($app, "/ \t\n\r\0\x0B");
        $candidates = [
            APP_PATH . 'app/' . $relative . '.php',
            APP_PATH . 'app/' . $relative . '/index.php',
            APP_PATH . 'app/tools/' . $relative . '.php',
            APP_PATH . 'app/tools/' . $relative . '/index.php',
            APP_PATH . 'app/visual/' . $relative . '.php',
            APP_PATH . 'app/visual/' . $relative . '/index.php',
        ];

        $resolved = null;
        foreach ($candidates as $file) {
            // Ensure it sits inside an allowed root
            foreach ($uiRoots as $root) {
                if (str_starts_with(realpath($file) ?: '', realpath($root) ?: '') && is_file($file)) {
                    $resolved = $file;
                    break 2;
                }
            }
        }

        if (!$resolved) {
            return $json_error('Unknown UI app route', 404, ['app' => $app]);
        }

        // Include constants on-demand by slug hint (customize as needed)
        if (stripos($relative, 'composer') !== false)
            $ensure_constants_for('composer');
        if (stripos($relative, 'git') !== false)
            $ensure_constants_for('git');
        if (stripos($relative, 'npm') !== false)
            $ensure_constants_for('npm');

        // Prepare a default envelope (in case the UI app partially fills it)
        $UI_APP = ['style' => '', 'body' => '', 'script' => ''];

        // Catch accidental echoes from the UI app
        ob_start();

        // Load the UI app — it should set/update $UI_APP
        require $resolved;
        $leak = ob_get_clean();

        if ($leak !== '') {
            // If any UI app echoed output, that would corrupt JSON — fail loudly.
            return $json_error('UI app produced unexpected output', 500, [
                'app' => $app,
                'leaked_output' => $leak,
            ]);
        }

        if (!is_array($UI_APP)) {
            return $json_error('UI app did not produce a valid payload', 500, ['app' => $app]);
        }

        return $json_out($UI_APP);
    }

    /* ───────────────────────── Not handled ───────────────────────── */
    // No command, no API match, and either no ?app or not asking for JSON => let index.php render HTML
    return false;
})();

/*
return (function () {
    // Get app or command
    $app = $_POST['app'] ?? $_GET['app'] ?? null;
    $cmd = $_POST['cmd'] ?? null;

    // Route for predefined apps
    $routes = [
        'composer' => APP_PATH . 'api/composer.php',
        'git' => APP_PATH . 'api/git.php',
        'npm' => APP_PATH . 'api/npm.php',
    ];

    // === 1. If app route matched
    if ($app && isset($routes[$app])) {
        // Can optionally return API output
        return require $routes[$app];
    }

    // === 2. Command pattern routes
    $commandRoutes = [
        '/^git\s+/i' => APP_PATH . 'api/git.php',
        '/^composer\s+/i' => APP_PATH . 'api/composer.php',
        '/^npm\s+/i' => APP_PATH . 'api/npm.php',
        '/^(chdir|cd)\s+/i' => APP_PATH . 'app/directory.php',
        '/^ls\s+/i' => APP_PATH . 'app/list.php',
        '/^php\s+/i' => CONFIG_PATH . 'runtime/php.php',
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
*/
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