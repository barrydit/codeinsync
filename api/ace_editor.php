<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['ace_save']) && isset($_POST['contents']) && isset($_POST['path']))
    if ($realpath = realpath(APP_PATH . (APP_ROOT ?? (APP_BASE['clients'] . ($_GET['client'] . '/' . ($_GET['domain'] ?? '') ?? $_GET['domain'] ?? ''))) . (APP_ROOT_DIR ?? rtrim($_GET['path'], '/') . '/') . ($_GET['file'] ?? $_POST['ace_path'])))
      file_put_contents($realpath, $_POST['contents']);
 
  header('Location: ' . $_SERVER['HTTP_REFERER']);

  exit();

  // dd([$_POST, realpath(APP_PATH . (APP_ROOT ?? (APP_BASE['clients'] . ($_GET['client'] . '/' . ($_GET['domain'] ?? '') ?? $_GET['domain'] ?? ''))) . (APP_ROOT_DIR ?? rtrim($_GET['path'], '/') . '/') . ($_GET['file'] ?? $_POST['ace_path'])), $_SERVER['HTTP_REFERER']]);


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

  //dd($_POST, false);

  if (isset($_GET['app']) && $_GET['app'] == 'ace_editor' && isset($_POST['ace_save']))

    if (isset($_POST['ace_path']) && realpath($path = APP_PATH . (APP_ROOT ?? (APP_BASE['clients'] . ($_GET['client'] . '/' . ($_GET['domain'] ?? '') ?? $_GET['domain'] ?? ''))) . ($_GET['path'] . '/' . $_GET['file'] ?? $_POST['ace_path']))) {
      //dd($path, false);   
      if (isset($_POST['ace_contents']))
        //dd($_POST['ace_contents']);
        file_put_contents($path, $_POST['ace_contents']);

      //dd($_POST, true);
      //http://localhost/Array?app=ace_editor&path=&file=test.txt    obj.prop.second = value    obj->prop->second = value
      //dd( APP_URL . '1234?' . http_build_query(['path' => dirname( $_POST['ace_path']), 'app' => 'ace_editor', 'file' => basename($path)]), true);


      //dd(APP_URL_BASE);

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