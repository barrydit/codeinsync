<?php
// api/file_save.php

// Ensure bootstrap has run (defines env/paths/url/app and helpers)
if (!defined('APP_BOOTSTRAPPED')) {
  require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Allow ace_save from either POST or GET
  $aceSave = $_POST['ace_save'] ?? $_GET['ace_save'] ?? null;
  if ($aceSave === null) {
    // Not an Ace save request, ignore or return early
    return;
  }

  // 1) Determine contents: prefer $_POST['contents'], otherwise use raw body
  $contents = $_POST['contents'] ?? file_get_contents('php://input');

  if ($contents === false) {
    http_response_code(400);
    exit('No contents provided');
  }

  // 2) Resolve target file path
  //    Keep your existing logic, but written in steps

  // Base path
  $basePath = APP_PATH;

  // Root (either APP_ROOT or /clients/{client}/{domain}/)
  if (defined('APP_ROOT') && APP_ROOT) {
    $root = APP_ROOT;
  } else {
    $client = $_GET['client'] ?? '';
    $domain = $_GET['domain'] ?? '';

    //$root = APP_BASE['clients'] . $client . '/';

    if ($domain !== '') {
      $root .= $domain . '/';
    }
  }

  // Subdirectory inside root
  $pathFromGet = rtrim($_GET['path'] ?? '', '/');
  $rootDir = APP_ROOT_DIR ?? ($pathFromGet !== '' ? $pathFromGet . '/' : '');

  // Filename (prefer explicit ?file=, else POST ace_path)
  $fileName = $_GET['file'] ?? ($_POST['ace_path'] ?? '');

  if ($fileName === '') {
    http_response_code(400);
    exit('Missing file name');
  }

  // Build full path and normalize
  $fullPath = $basePath . $root . $rootDir . $fileName;
  $realpath = realpath($fullPath);

  if ($realpath === false) {
    http_response_code(404);
    exit("File not found: $fullPath");
  }

  // Build a whitelist of allowed roots
  $allowedRoots = [];

  // 1) The app itself
  $allowedRoots[] = realpath(APP_PATH);

  // 2) Clients tree (ex: APP_BASE['clients'] = '../clients/')
  if (!empty(APP_BASE['clients'])) {
    $allowedRoots[] = realpath(APP_PATH . APP_BASE['clients']);
  }

  // 3) Projects tree (if you use it)
  if (!empty(APP_BASE['projects'] ?? null)) {
    $allowedRoots[] = realpath(APP_PATH . APP_BASE['projects']);
  }

  // Normalize & drop any false
  $allowedRoots = array_filter($allowedRoots);

  /**
   * Check if a path is inside ANY allowed root
   */
  $inAllowedRoot = static function (string $path) use ($allowedRoots): bool {
    foreach ($allowedRoots as $root) {
      // ensure trailing slash so /mnt/c/www-evil won't pass a /mnt/c/www check
      $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
      if (strpos($path, $root) === 0) {
        return true;
      }
    }
    return false;
  };

  // 3) Save the file
  if (file_put_contents($realpath, $contents) === false) {
    http_response_code(500);
    exit('Failed to write file');
  }

  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
  /*
    if (isset($_POST['ace_save']) && isset($_POST['contents']) && isset($_POST['path']))
      if ($realpath = realpath(APP_PATH . (APP_ROOT ?? (APP_BASE['clients'] . ($_GET['client'] . '/' . ($_GET['domain'] ?? '') ?? $_GET['domain'] ?? ''))) . (APP_ROOT_DIR ?? rtrim($_GET['path'], '/') . '/') . ($_GET['file'] ?? $_POST['ace_path'])))
        file_put_contents($realpath, $_POST['contents']);

  header('Location: ' . $_SERVER['HTTP_REFERER']);

  exit();
    */

  if (isset($_POST["restore_backup"])) {

    $file = APP_BASE['data'] . 'source_code.json';

    if (is_file($file)) {
      $source_code = json_decode(file_get_contents($file), true);

      if (isset($source_code[$_POST["restore_backup"]])) {
        file_put_contents(APP_PATH . APP_ROOT . $_POST["restore_backup"], $source_code[$_POST["restore_backup"]]);
      }
    }
    die(header('Location: ' . APP_URL_BASE['scheme'] . '://' . APP_URL_BASE['host'] . '/?' . http_build_query(APP_QUERY + ['path' => $_GET['path'] ?? '', 'app' => $_GET['app'] ?? 'ace_editor', 'file' => basename($_POST['restore_backup'])])));
  }

  if (isset($_GET['app']) && $_GET['app'] == 'ace_editor' && isset($_POST['ace_save']))

    if (isset($_POST['ace_path']) && realpath($path = APP_PATH . (APP_ROOT ?? (APP_BASE['clients'] . ($_GET['client'] . '/' . ($_GET['domain'] ?? '') ?? $_GET['domain'] ?? ''))) . ($_GET['path'] . '/' . $_GET['file'] ?? $_POST['ace_path']))) { 
      if (isset($_POST['ace_contents']))

        file_put_contents($path, $_POST['ace_contents']);

      //http://localhost/Array?app=ace_editor&path=&file=test.txt    obj.prop.second = value    obj->prop->second = value

      die(header('Location: ' . APP_URL_BASE['scheme'] . '://' . APP_URL_BASE['host'] . '/?' . http_build_query(APP_QUERY + ['path' => dirname($_POST['ace_path']), 'file' => basename($path)])));
    } else
      dd("Path: $path was not found.", true);
  //dd($_POST);

  //  if (isset($_GET['file'])) {
//    file_put_contents($projectRoot.(!$_POST['path'] ? '' : DIRECTORY_SEPARATOR.$_POST['path']).DIRECTORY_SEPARATOR.$_POST['file'], $_POST['editor']);
//  }

  /*
      if (isset($_POST['cmd'])) {
        if ($_POST['cmd'] && $_POST['cmd'] != '') 
          if (preg_match('/^install/i', $_POST['cmd']))
            include('templates/' . preg_split("/^install (\s*+)/i", $_POST['cmd'])[1] . '.php');
          else if (preg_match('/^php(:?(.*))/i', $_POST['cmd'], $match))
            exec($_POST['cmd'], $output);
          else if (preg_match('/^composer(:?(.*))/i', $_POST['cmd'], $match)) {
          $output[] = 'env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC . ' ' . $match[1];
  $proc=proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC . ' ' . $match[1],
    array(
      array("pipe","r"),
      array("pipe","w"),
      array("pipe","w")
    ),
    $pipes);
  [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
  $output[] = 'Composer: ' . (!isset($stdout) ? NULL : $stdout . (!isset($stderr) ? NULL : ' Error: ' . $stderr) . (!isset($exitCode) ? NULL : ' Exit Code: ' . $exitCode));
  $output[] = $_POST['cmd'];

          } else if (preg_match('/^git(:?(.*))/i', $_POST['cmd'], $match)) {
          $output[] = APP_SUDO . GIT_EXEC . ' ' . $match[1];
  $proc=proc_open(APP_SUDO . GIT_EXEC . ' ' . $match[1],
    array(
      array("pipe","r"),
      array("pipe","w"),
      array("pipe","w")
    ),
    $pipes);
  [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
  $output[] = 'Composer: ' . (!isset($stdout) ? NULL : $stdout . (!isset($stderr) ? NULL : ' Error: ' . $stderr) . (!isset($exitCode) ? NULL : ' Exit Code: ' . $exitCode));
  $output[] = $_POST['cmd'];

          }

            //exec($_POST['cmd'], $output);
          else echo $_POST['cmd'] . "\n";
        //else var_dump(NULL); // eval('echo $repo->status();')
        if (!empty($output)) echo 'PHP >>> ' . join("\n... <<< ", $output) . "\n"; // var_dump($output);
        //else var_dump(get_class_methods($repo));
        exit();
      }
  */
}