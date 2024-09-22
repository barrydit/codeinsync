<?php

  // if ($path = (basename(getcwd()) == 'public') chdir('..');
//APP_PATH == dirname(APP_PATH_PUBLIC)

if ($path = (basename(getcwd()) == 'public') ? (is_file('../config/config.php') ? '../config/config.php' : 'config.php') :
  (is_file('config.php') ? 'config.php' : dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php' ))
  require_once $path;
else
  die(var_dump("$path was not found. file=config.php"));

require_once APP_PATH . 'index.php';

    //? (is_file('../config.php') ? '../config.php' : 'config.php')
    //: (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null)))

if (APP_DEBUG) {
  defined('PHP_ZTS') and $errors['PHP_ZTS'] = "PHP was built with ZTS enabled.\n";
  defined('PHP_DEBUG') and $errors['PHP_DEBUG'] = "PHP was built with DEBUG enabled.\n";
  defined('PHP_VERSION') and $errors['PHP_VERSION'] = "PHP Version: " . PHP_VERSION . "\n";
  defined('PHP_OS') and $errors['PHP_OS'] = "PHP_OS: " . PHP_OS . "\n";
  defined('PHP_SAPI') and $errors['PHP_SAPI'] = "PHP_SAPI: " . PHP_SAPI . "\n";
  defined('PHP_BINARY') and $errors['PHP_BINARY'] = "PHP_BINARY: " . PHP_BINARY . "\n";
  defined('PHP_BINDIR') and $errors['PHP_BINDIR'] = "PHP_BINDIR: " . PHP_BINDIR . "\n";
  defined('PHP_CONFIG_FILE_PATH') and $errors['PHP_CONFIG_FILE_PATH'] = "PHP_CONFIG_FILE_PATH: " . PHP_CONFIG_FILE_PATH . "\n";
  defined('PHP_CONFIG_FILE_SCAN_DIR') and $errors['PHP_CONFIG_FILE_SCAN_DIR'] = "PHP_CONFIG_FILE_SCAN_DIR: " . PHP_CONFIG_FILE_SCAN_DIR . "\n";
  defined('PHP_SHLIB_SUFFIX') and $errors['PHP_SHLIB_SUFFIX'] = "PHP_SHLIB_SUFFIX: " . PHP_SHLIB_SUFFIX . "\n";
  defined('PHP_EOL') and $errors['PHP_EOL'] = 'PHP_EOL: ' . json_encode(PHP_EOL) . "\n";
  defined('PHP_INT_MIN') and $errors['PHP_INT_MIN'] = "PHP_INT_MIN: " . PHP_INT_MIN . "\n"; // -/+ 2147483648 32-bit
  defined('PHP_INT_MAX') and $errors['PHP_INT_MAX'] = "PHP_INT_MAX: " . PHP_INT_MAX . "\n"; // -/+ 9223372036854775808 64-bit
  defined('PHP_FLOAT_DIG') and $errors['PHP_FLOAT_DIG'] = "PHP_FLOAT_DIG: " . PHP_FLOAT_DIG . "\n";
  defined('PHP_FLOAT_EPSILON') and $errors['PHP_FLOAT_EPSILON'] = "PHP_FLOAT_EPSILON: " . PHP_FLOAT_EPSILON . "\n";
  defined('PHP_FLOAT_MIN') and $errors['PHP_FLOAT_MIN'] = "PHP_FLOAT_MIN: " . PHP_FLOAT_MIN . "\n";
  defined('PHP_FLOAT_MAX') and $errors['PHP_FLOAT_MAX'] = "PHP_FLOAT_MAX: " . PHP_FLOAT_MAX . "\n";
}

//dd(get_defined_constants(true)['user']);

//$path = "/path/to/your/logfile.log"; // Replace with your actual log file path
if (is_readable($path = APP_PATH . APP_ROOT . $_ENV['ERROR_LOG_FILE']) && filesize($path) >= 0 ) {
  $errors['ERROR_PATH'] = "\n$path\n";
  //if (stripos(PHP_OS, 'WIN') === 0) {
  //  $errors['ERROR_LOG'] = shell_exec("powershell Get-Content -Tail 10 $path") . "\n";
  //} else {
  //  $errors['ERROR_LOG'] = shell_exec(APP_SUDO . " tail $path") . "\n";
  //}

  $shellOutput = shell_exec(stripos(PHP_OS, 'LIN') === 0 ? "tail $path" : "powershell Get-Content -Tail 10 $path");
    
  $pattern = '/^\[\d+-\w*-\d*\s+\d+:\d+:\d+\s+\w*\/\w+\]\s+Shutdown\s+constructor\s+called.$/';
  $matches = [];
    
  // Parse the shell output line by line
  foreach (explode("\n", (string) $shellOutput) as $line) {
    if ($line == '') continue;
    elseif (preg_match($pattern, $line)) {
      $matches[] = $line;
    } else {
      // If the line doesn't match the pattern, reset the matches array
      $log_matches[] = $line;
    }
  }

  $log_matches[] = end($matches) . ' [x' . count($matches) . ']';

  if (count($matches) >= 10 && count($log_matches) <= 2) unlink($path) and $errors['ERROR_PATH'] = (!is_file($path) ? trim($errors['ERROR_PATH']) . ' was completely removed.' : 'Error_log failed to be removed completely.') . "\n"; // header('Location: ' . APP_URL);

  $errors['ERROR_LOG'] = implode("\n", $log_matches) . "\n\n";

  if (isset($_GET[$error_log = basename($path)]) && $_GET[$error_log] == 'unlink') 
    unlink($path);
}

$previousFilename = '';

$dirs = [];

!isset($_GET['app']) || $_GET['app'] != 'git' ?:
  //$dirs[] = APP_PATH . APP_BASE['config'] . 'git.php'
  (APP_SELF != APP_PATH_PUBLIC ? : 
    $dirs[] = APP_PATH . APP_BASE['config'] . 'git.php');

/**/

!isset($_GET['app']) || $_GET['app'] != 'composer' ?:
  $dirs = (APP_SELF == APP_PATH_PUBLIC) ? array_merge(
    $dirs,
    [
      (!is_file($include = APP_PATH . APP_BASE['config'] . 'composer.php') ?: $include)
    ]
  ) : array_merge(
    $dirs,
    [
      (!is_file($include = APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php') ?: $include),
      (!is_file($include = APP_PATH . APP_BASE['config'] . 'composer.php') ?: $include)
    ]
  );

!isset($_GET['app']) || $_GET['app'] != 'npm' ?:
  //$dirs[] = APP_PATH . APP_BASE['config'] . 'git.php'
  (APP_SELF != APP_PATH_PUBLIC ?: 
    (!is_file($include = APP_PATH . APP_BASE['config'] . 'npm.php') ?: $dirs[] = $include ));


    //dd($dirs);
unset($include);

//dd(get_required_files(), true); // COMPOSER_VENDORS

  //$dirs = [
    //0 => APP_PATH . APP_BASE['config'] . 'git.php',
    //1 => APP_PATH . APP_BASE['config'] . 'composer.php',
    //2 => APP_PATH . APP_BASE['config'] . 'npm.php',
    //2 => APP_PATH . 'composer-setup.php',
    //1 => APP_PATH . 'config.php',
    //1 => APP_PATH . 'constants.php',
    //2 => APP_PATH . 'functions.php',
    //4 => APP_PATH . APP_BASE['vendor'] . 'autoload.php',
  //]; // array_filter(glob(__DIR__ . DIRECTORY_SEPARATOR . '*.php'), 'is_file');

  usort($dirs, function ($a, $b) {
      // Define your sorting criteria here
    //if (basename($a) === 'composer-setup.php')
    //    return 1; // $a comes after $b
    //elseif (basename($b) === 'composer-setup.php')
    //    return -1; // $a comes before $b/
/*
    //elseif (basename($a) === 'composer.php')
    //    return -1; // $a comes after $b
    //elseif (basename($b) === 'composer.php')
    //    return 1; // $a comes before $b
*/
    if (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_BASE['vendor'] . 'autoload.php') // DIRECTORY_SEPARATOR
        return -1; // $a comes after $b
    elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_BASE['vendor'] . 'autoload.php')
        return 1; // $a comes before $b
    elseif (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_BASE['config'] . 'git.php')
        return -1; // $a comes after $b
    elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_BASE['config'] . 'git.php')
        return 1; // $a comes before $b
    else 
        return strcmp(basename($a), basename($b)); // Compare other filenames alphabetically
  });

  foreach ($dirs as $includeFile) {

    $path = dirname($includeFile);

    if (in_array($includeFile, get_required_files())) continue; // $includeFile == __FILE__

    if (basename($includeFile) === 'composer-setup.php') continue;

    if (!file_exists($includeFile)) {
      error_log("Failed to load a necessary file: " . $includeFile . PHP_EOL);
      break;
    } else {
      $currentFilename = substr(basename($includeFile), 0, -4);
    
      // $pattern = '/^' . preg_quote($previousFilename, '/')  . /*_[a-zA-Z0-9-]*/'(_\.+)?\.php$/'; // preg_match($pattern, $currentFilename)

      if (!empty($previousFilename) && strpos($currentFilename, $previousFilename) !== false) continue;

      // dd('file:'.$currentFilename,false);
      //dd("Trying file: $includeFile", false);
      require_once $includeFile;

      $previousFilename = $currentFilename;     
    }

  }


/** Loading Time: 0.638s **/

  // dd(null, true);

  //dd($_SERVER); php_self, script_name, request_uri /folder/

  // dd(getenv('PATH'));
  if (isset($_SERVER['REQUEST_METHOD']))
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':    
      //dd(get_required_files(), false);
      //dd($_ENV);
      if (isset($_POST['environment'])) {
        switch ($_POST['environment']) {
          case 'develop':
            define('APP_ENV', 'development');
            break;
          case 'math':
            define('APP_ENV', 'math');
            break;
          case 'product':
          default:
            define('APP_ENV', 'production');
            break;
        }
        $_ENV['APP_ENV'] = APP_ENV;
        Shutdown::setEnabled(false)->setShutdownMessage(function() {
          return header('Location: ' . APP_URL); // -wow
        })->shutdown();
      }

    break;
  case 'GET':
      if (isset($_ENV['APP_ENV']) && !empty($_ENV)) !defined('APP_ENV') and define('APP_ENV', $_ENV['APP_ENV']);
      //if (!empty($_GET['path']) && !isset($_GET['app'])) !!infinite loop
      //  exit(header('Location: ' . APP_URL . $_GET['path']));
// http://localhost/?app=composer&path=vendor

// Parse the URL and extract the query string

// Convert the query string into an associative array

// Now $queryArray contains the parsed query parameters as an array
//dd($_SERVER);
      if (preg_match('/^\/(?!\?)$/', $_SERVER['REQUEST_URI'])) exit(header('Location: ' . APP_URL . '?'));

      if (isset($_SERVER['HTTP_REFERER'])) {
        parse_str(parse_url($_SERVER['HTTP_REFERER'])['query'] ?? '', $queryRefererArray);
        if (array_key_exists('debug', $queryRefererArray)) {
          parse_str(parse_url($_SERVER['REQUEST_URI'])['query'] ?? '', $queryArray);
          if (!array_key_exists('debug', $queryArray)) {
            Shutdown::setEnabled(true)->setShutdownMessage(function() use ($queryArray) {
              return header('Location: ' . APP_URL . '?debug&' . http_build_query($queryArray, '', '&')); //$_SERVER['HTTP_REFERER'] -wow
            })->shutdown();
          } //else << NO ELSE!!
        }
      }

      if (isset($_GET['hide']) && $_GET['hide'] == 'update-notice') {
        $_ENV['HIDE_UPDATE_NOTICE'] = true; // var_export(true, true); // true
        Shutdown::setEnabled(true)->setShutdownMessage(function() {
          return header('Location: ' . APP_URL); // -wow
        })->shutdown();
      }
        

      if (isset($_GET['category']) && !empty($_GET['category'])) {
        
        if ($_GET['category'] == 'projects')
          exit(header('Location: ' . APP_URL . '?project='));
        if ($_GET['category'] == 'vendor')
          exit(header('Location: ' . APP_URL . '?app=composer&path=' . $_GET['category']));
        //if ($_GET['category'] == 'applications')
        //  exit(header('Location: ' . APP_URL . '?path=' . $_GET['category']));
        exit(header('Location: ' . APP_URL . '?' . $_GET['category']));
      } elseif (isset($_GET['category']) && empty($_GET['category']))
        exit(header('Location: ' . APP_URL . '?path'));
        
      if (isset($_GET['path']) && !is_dir(APP_PATH . APP_ROOT)) {
        //dd(APP_PATH . APP_ROOT . ' test');


        
          die(header('Location: ' . APP_URL_BASE));
      }
      break;
  }
  //dd(APP_PATH . APP_ROOT);
  /*
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':    
      //dd($_POST);
  
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS | FILTER_SANITIZE_ENCODED, FILTER_REQUIRE_ARRAY ) ?? [];
  
      break;
    case 'GET':
      $_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS | FILTER_SANITIZE_ENCODED, FILTER_REQUIRE_ARRAY ) ?? [];
      break;
    default:
      foreach(${'_'.$_SERVER['REQUEST_METHOD']} as $key => $value) {
        ${'_'.$_SERVER['REQUEST_METHOD']}[$key] = filter_var($value, (
          is_string($value) ? FILTER_SANITIZE_STRING : (
            is_int($value) ? FILTER_VALIDATE_INT : FILTER_SANITIZE_STRING)
          )
        );
      }
      /*$request_method = '_'.$_SERVER['REQUEST_METHOD'];
      foreach($$request_method as $key => $value) {
        $$request_method[$key] = filter_var($value, (
          is_string($value) ? FILTER_SANITIZE_STRING : (
            is_int($value) ? FILTER_VALIDATE_INT : FILTER_SANITIZE_STRING
          )
        ));
      }*/
  //}
  /**/

  if (defined('APP_QUERY') && empty(APP_QUERY) || isset($_GET['CLIENT']) || isset($_GET['DOMAIN']) && !defined('APP_ROOT')) {
    if (!isset($_ENV['DEFAULT_CLIENT'])) $_ENV['DEFAULT_CLIENT'] = $_GET['CLIENT'];

    if (!isset($_ENV['DEFAULT_DOMAIN'])) $_ENV['DEFAULT_DOMAIN'] = $_GET['DOMAIN'];

    if (defined('APP_QUERY') && empty(APP_QUERY))
      Shutdown::setEnabled(false)->setShutdownMessage(function() {
        return header('Location: ' . APP_URL . '?' . http_build_query([
          'client' => $_ENV['DEFAULT_CLIENT'],
          'domain' => $_ENV['DEFAULT_DOMAIN']
        ]) . '#'); // -wow
      })->shutdown();
    else
      $_GET = array_merge($_GET, APP_QUERY);
  }
  
  
  //dd(__DIR__ . DIRECTORY_SEPARATOR);

    
 if (APP_SELF == APP_PATH_PUBLIC) {

  $appPaths = array_filter(glob(__DIR__ . DIRECTORY_SEPARATOR . 'app.*.php'), 'is_file'); // public/
  
  // $globPaths[] = __DIR__ . DIRECTORY_SEPARATOR . 'app.console.php';
  // $paths = array_values(array_unique(array_merge($additionalPaths, $globPaths)));
  
  //if (isset($paths[APP_PATH . APP_BASE['public'] . 'app.install.php']))
  //  unset($paths[APP_PATH . APP_BASE['public'] . 'app.install.php']);
  
  // dd(get_included_files());
  
  usort($appPaths, function ($a, $b) {
    // Define your sorting criteria here
    global $appPaths;
  
    // install, debug, project, timesheet, browser, github, packagist, whiteboard, notes, pong, console
    if (basename($a) === 'app.install.php')
      return -1;
    elseif (basename($b) === 'app.install.php')
      return 1;
    elseif (basename($a) === 'app.debug.php')
      return -1;
    elseif (basename($b) === 'app.debug.php')
      return 1;
    elseif (basename($a) === 'app.project.php')
      return -1;
    elseif (basename($b) === 'app.project.php')
      return 1;
    elseif (basename($a) === 'app.timesheet.php')
      return -1;
    elseif (basename($b) === 'app.timesheet.php')
      return 1;
    elseif (basename($a) === 'app.browser.php')
      return -1;
    elseif (basename($b) === 'app.browser.php')
      return 1;
    elseif (basename($a) === 'app.console.php')
      return 1; // $a comes after $b
    elseif (basename($b) === 'app.console.php')
      return -1; // $a comes before $b
    else 
      return strcmp(basename($a), basename($b)); // Compare other filenames alphabetically
  });
  
  if (in_array(APP_PATH . APP_BASE['public'] . 'app.install.php', $appPaths))
    foreach ($appPaths as $key => $file)
      if (basename($file) === 'app.install.php')
        unset($appPaths[$key]);
  
  $uiPaths = array_filter(glob(__DIR__ . DIRECTORY_SEPARATOR . '{ui}.*.php', GLOB_BRACE), 'is_file');
  
  
  /*
  if (in_array(APP_PATH . APP_BASE['public'] . 'ui.composer.php', $uiPaths))
    foreach ($uiPaths as $key => $file)
      if (basename($file) === 'ui.composer.php')
        unset($uiPaths[$key]);
  */
  
  // If you want to reset the array keys to be numeric (optional)
  $paths = array_values(array_unique(array_merge($uiPaths, $appPaths)));
  
  //$paths = array_values(array_unique(array_merge($globPaths, $additionalPaths)));
  
  /*9.4
  do {
      // Check if $paths is not empty
      if (!empty($paths)) {
          // Shift the first path from the array
          $path = array_shift($paths);
  
          // Check if the path exists
          if ($realpath = realpath($path)) {
              // Require the file
              require_once $realpath;
          } else {
              // Output a message if the file was not found
              echo basename($path) . ' was not found. file=public/' . basename($path) . PHP_EOL;
          }
          
          dd('finish time: ' . $path, false);
      }
      // Unset $paths if it is empty
      if (empty($paths)) unset($paths);
  } while (isset($paths) && !empty($paths));
  */
  // dd(get_defined_vars(), true);
  
  //$path = '';

$app = $apps = [];

// Check if $paths is not empty
if (!empty($paths))
  while ($path = array_shift($paths)) {
          // Shift the first path from the array
          //;
  
          // Check if the path exists
          if ($realpath = realpath($path)) {
  
              // Define a function to include the file
  //            $requireFile = function($file) /*use ($apps)*/ { global $apps; }; */
  
              // Include the file using the function
              //dd("path is $realpath\n", false);
              //dd(get_required_files(), false);
              /* $returnedValue = (function() use ($realpath) {
                ob_start();
                require_once $realpath;
                return ob_get_clean(); // redundant ob_end_clean();
              })(); */
              //dd($realpath, false);
              $returnedValue = (function() use ($realpath, &$app) {
                ob_start();

                require_once $realpath;
                
                if (preg_match('/^app\.([\w\-.]+)\.php$/', basename($realpath), $matches) && isset($matches[1]) /*&& !empty($app[$matches[1]])*/) {
                  return [$matches[1] => ['style' => $app[$matches[1]]['style'] ?? '', 'body' => $app[$matches[1]]['body'] ?? '', 'script' => $app[$matches[1]]['script'] ?? '']];
                } else if (preg_match('/^ui\.([\w\-.]+)\.php$/', basename($realpath), $matches)) {
                  !defined($app_name = 'UI_' . strtoupper($matches[1])) and define($app_name, ['style' => $app['style'] ?? '', 'body' => $app['body'] ?? '', 'script' => $app['script'] ?? '']); // $apps[$matches[1]]
                  //dd('UI_' . strtoupper($matches[1]) . ' created?', false);
                  //$app = [];
                  return null;
                }

                //dd('UI_' . strtoupper($matches[1]) . ' created?', false);

                //dd($realpath . ' created?', false);
              })();
        
              //$returnedValue = require_once $realpath;

              //dd($app, false);
              //ob_start(); $ob_contents = ob_get_contents(); ob_end_clean();
              //dd($ob_contents, false);
              //dd(get_required_files(), false);
              //dd($returnedValue, false);
  
              // Check the type of the returned value
              if (is_array($returnedValue) && !empty($returnedValue)) {
                  // The file returned an array

                  //dd($returnedValue);
                  $apps = array_merge($apps, $returnedValue);
              }  //elseif ($returnedValue !== null) {
                  // The file returned a non-null value
                  //echo 'Returned value: ' . $returnedValue . PHP_EOL;
              //} else {
                  // The file did not return a value
              //    echo 'File did not return a value.' . PHP_EOL;
              //}
          } else {
              // Output a message if the file was not found
              echo basename($path) . ' was not found. file=public/' . basename($path) . PHP_EOL;
          }

      // Unset $paths if it is empty
      //if (empty($paths)) unset($paths);
  
  } // isset($paths) && !empty($paths)
  //dd(array_keys($apps['console']));
      //dd(get_defined_vars(), true); // Check that $files remains unchanged
      //dd($appDirectory['body']); 

  if (defined('APP_ENV'))
    switch (APP_ENV) {
      case 'development':
        require_once 'idx.develop.php';
        break;
      case 'math':
        require_once 'idx.math.php';
        break;
      case 'production':
      default:
        require_once 'idx.product.php';
        break;
  } else {
    define('APP_ENV', 'production');
    require_once 'idx.product.php';
  }


}
