<?php
declare(strict_types=1);

defined('APP_START') || define('APP_START', $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
defined('ROOT_PATH') || define('ROOT_PATH', __DIR__);

require_once __DIR__ . '/src/Infrastructure/Runtime/BootstrapTracker.php';

use Bioage_App\Infrastructure\Runtime\BootstrapTracker;

BootstrapTracker::initStages();

BootstrapTracker::requireOnce(
    8,
    'Debugging & Diagnostics',
    ROOT_PATH . '/src/Infrastructure/Runtime/BootstrapTracker.php',
    [
        'Defines the BootstrapTracker class used to track bootstrap stages, successes, warnings, errors, debug details, and documentation notes.',
    ]
);

$notices = $notices ?? [];
$warnings = $warnings ?? [];
$errors = $errors ?? [];

if (!class_exists(BootstrapTracker::class, false))
    class_alias(BootstrapTracker::class, 'BootstrapTracker');

BootstrapTracker::success(
    1,
    'PHP Version',
    'PHP runtime detected.',
    [
        'php_version' => PHP_VERSION,
        'php_os_family' => PHP_OS_FAMILY,
        'php_sapi' => PHP_SAPI,
    ],
    [
        'Confirms the PHP version, operating system family, and server API used to run the application.',
    ]
);

BootstrapTracker::success(
    1,
    'PHP Extensions',
    'Required PHP extensions checked.',
    [
        'json' => extension_loaded('json'),
        'mbstring' => extension_loaded('mbstring'),
        'openssl' => extension_loaded('openssl'),
        'fileinfo' => extension_loaded('fileinfo'),
    ],
    [
        'Checks whether required PHP extensions are available before the application continues bootstrapping.',
    ]
);

BootstrapTracker::success(
    2,
    'Request Superglobals',
    '$_GET, $_POST, $_COOKIE are available.',
    [
        '$_SERVER' => $_SERVER,
        '$_GET' => $_GET,
        '$_POST' => $_POST,
        '$_FILES' => $_FILES,
        '$_COOKIE' => $_COOKIE,
    ],
    [
        '$_SERVER contains server and execution environment information.',
        '$_GET contains query string values.',
        '$_POST contains submitted form values.',
        '$_FILES contain uploaded file information.',
        '$_COOKIE contains browser cookie values.',
        'These are available before session_start().',
    ]
);

$notices[] = '$_SERVER == ' . (var_export($_SERVER, true) ?? []);
$notices[] = '$_SERVER[\'REQUEST_URI\']: ' . ($_SERVER['REQUEST_URI'] ?? '(unknown)');
$notices[] = '$_SERVER[\'HTTP_USER_AGENT\']: ' . ($_SERVER['HTTP_USER_AGENT'] ?? '(unknown)');

is_dir(ROOT_PATH) && $notices[] = 'ROOT_PATH: ' . ROOT_PATH;

defined('INDEX_FILE_PATH') || define('INDEX_FILE_PATH', 'index.php');

$notices[] = 'INDEX_FILE_PATH: ' . (\defined('ROOT_PATH')
    ? str_replace(ROOT_PATH . DIRECTORY_SEPARATOR, '', INDEX_FILE_PATH)
    : INDEX_FILE_PATH);

defined('APP_PATH_APP') || define('APP_PATH_APP', 'bootstrap/app.php');

if (!is_file(ROOT_PATH . '/' . APP_PATH_APP)) {
    error_log($warnings[array_key_last($notices)] = 'Bootstrap file not found: ' . APP_PATH_APP);
    exit;
}

BootstrapTracker::requireOnce(
    5,
    'Application Bootstrap',
    ROOT_PATH . '/' . APP_PATH_APP,
    [
        'APP_PATH_APP' => APP_PATH_APP,
        'resolved_path' => realpath(ROOT_PATH . '/' . APP_PATH_APP) ?: '(not found)',
    ],
);

require __DIR__ . '/public/index.php';

exit;