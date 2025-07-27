<?php

//require_once __DIR__ . '/../../config/constants.paths.php';
//require_once APP_PATH . 'classes/class.composer.php';

use App\Core\Registry;
//use App\Handlers\COMPOSER;
//use App\Handlers\ComposerHandler;
//use App\Handlers\ComposerHandlerInterface;

function execute_composer_command(string $cmd): void
{
    $logger = Registry::get('logger');
    $logger->info("Executing composer command: {$cmd}");

    if (preg_match('/^composer\s+(.*)$/i', $cmd, $match)) {
        $command = trim($match[1]);

        // Example: shell_exec, queue, logging, or socket forward
        $output = shell_exec("composer " . escapeshellarg($command));

        echo json_encode([
            'success' => true,
            'output' => $output
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Unrecognized composer command']);
    }
}

$composer = new COMPOSER();

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'install':
        $pkg = $_POST['package'] ?? '';
        $result = $composer->install($pkg);
        echo json_encode(['success' => true, 'output' => $result]);
        break;

    case 'search':
        $query = $_POST['query'] ?? '';
        $result = $composer->search($query);
        echo json_encode(['success' => true, 'output' => $result]);
        break;

    case 'list':
        $packages = $composer->getInstalledPackages();
        echo json_encode(['success' => true, 'packages' => $packages]);
        break;

    default:
        //http_response_code(400);
        //cho json_encode(['error' => 'Unknown action']);
        break;
}



function handle_composer_api(): void
{
    /*
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      echo json_encode(['error' => 'Only POST is allowed']);
      return;
    }*/

    $cmd = $_POST['cmd'] ?? null;

    if ($cmd) {
        execute_composer_command($cmd);
    } //else {
    //http_response_code(400);
    //echo json_encode(['error' => 'Missing cmd']);
    //}
}

handle_composer_api();

//exit;