<?php

require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'index.php';

$htaccess = <<<END
# Enable Rewrite Engine
RewriteEngine On

# Set the base directory (adjust if your application is in a subfolder)
RewriteBase /

# Redirect all requests to index.php except if the file or directory exists
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
END;

// if ($path = (basename(getcwd()) == 'public') chdir('..');
//APP_PATH == dirname(PATH_PUBLIC)

//

!defined('APP_SELF') and define('APP_SELF', !empty(get_included_files()) ? get_included_files()[0] : __FILE__); //mnt/c/www/public/index.php

switch (APP_SELF) {
  case __FILE__:
    require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'bootstrap.php';
    break;
  default:
    if ($php = realpath(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'php.php'))
      require_once $php; // APP_PATH . 'index.php'
    else
      die(var_dump("$php was not found."));
}

if ($file = realpath(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'index.php'))
  require_once $file; // APP_PATH . 'index.php'


//dd($_ENV, false);

require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'auth.php';

//dd(get_defined_constants(true)['user']);



//die(var_dump(APP_ROOT));

//die(var_dump(get_required_files()));

/*
switch ($_SERVER['REQUEST_URI']) {
  case '/notes':
      require_once __DIR__ . '/app.notes.php';
      break;
  case '/console':
      require_once __DIR__ . '/app.console.php';
      break;
  default:
      require_once __DIR__ . '/app.browser.php';
      break;
}

*/
/*
if ($path = (basename(getcwd()) == 'public') ? (is_file('../config/config.php') ? '../config/config.php' : 'config.php') :
  (is_file('config.php') ? 'config.php' : dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php' ))
  require_once $path;
else
  die(var_dump("$path was not found. file=config.php"));

if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"])) {
  if (is_file($path = dirname(getcwd()) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php')) require_once $path; 
  else die(var_dump("$path path was not found. file=" . basename($path)));
}
*/

//? (is_file('../config.php') ? '../config.php' : 'config.php')
//: (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null)))

//$path = "/path/to/your/logfile.log"; // Replace with your actual log file path
if (isset($_ENV['PHP']['LOG_PATH']) && is_readable($path = APP_PATH . APP_ROOT . $_ENV['PHP']['LOG_PATH']) && filesize($path) >= 0) {
  $errors['ERROR_PATH'] = "\nwww-data@localhost:" . getcwd() . '# ' . basename($path) . "\n";

  $shellOutput = shell_exec(stripos(PHP_OS, 'LIN') === 0 ? "tail $path" : "powershell Get-Content -Tail 10 $path");

  $patterns = [
    '/^\[\d+-\w*-\d*\s+\d+:\d+:\d+\s+\w*\/\w+\]\s+Connection\s+refused\s+-\s+Could\s+not\s+connect\s+to\s+' . SERVER_HOST . ':' . SERVER_PORT . '$/',
    '/^\[\d+-\w*-\d*\s+\d+:\d+:\d+\s+\w*\/\w+\]\s+Shutdown\s+constructor\s+called\.$/'
  ];

  $matches = [];
  $log_matches = [];

  // Parse the shell output line by line
  foreach (explode("\n", (string) $shellOutput) as $line) {
    if ($line == '')
      continue;

    $matched = false;
    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $line)) {
        $matches[] = $line;
        $matched = true;
        break; // Exit loop early on first match
      }
    }

    if (!$matched)
      $log_matches[] = $line;
  }

  // Append the last match count
  if (!empty($matches)) {
    $log_matches[] = end($matches) . ' [x' . count($matches) . ']';
  }

  // Remove the log file if it exceeds conditions
  if (count($matches) >= 10 && count($log_matches) <= 2) {
    unlink($path);
    $errors['ERROR_PATH'] = !is_file($path)
      ? trim($errors['ERROR_PATH']) . ' was completely removed.'
      : 'Error_log failed to be removed completely.';
  }

  $errors['ERROR_LOG'] = implode("\n", $log_matches) . "\n\n";

  // Allow manual log file deletion via GET request
  if (isset($_GET[$error_log = basename($path)]) && $_GET[$error_log] == 'unlink') {
    unlink($path);
  }
}

//dd($_SERVER); php_self, script_name, request_uri /folder/

// dd(getenv('PATH'));
//die(phpinfo()); // get_defined_constants(true)['user']

if (isset($_SERVER['REQUEST_METHOD']))
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
    case 'GET':

      //dd('what the heck is going on here?', false);
      if (isset($_POST['environment'])) {
        switch ($_POST['environment']) {
          case 'develop':
            define('APP_ENV', 'development');
            break;
          case 'math':
            define('APP_ENV', 'math');
            break;
          case 'stage':
            define('APP_ENV', 'staging');
            break;
          case 'product':
          default:
            define('APP_ENV', 'production');
            break;
        }

        $_ENV['APP_ENV'] = APP_ENV;
        //die('testing ' . $_SERVER['REQUEST_METHOD'] );
        Shutdown::setEnabled(false)->setShutdownMessage(fn() => header('Location: ' . APP_URL))->shutdown();
      }

      if (isset($_ENV['APP_ENV']) && !empty($_ENV))
        !defined('APP_ENV') and define('APP_ENV', $_ENV['APP_ENV']);
      //if (!empty($_GET['path']) && !isset($_GET['app'])) !!infinite loop
      //  exit(header('Location: ' . APP_URL . $_GET['path']));
// http://localhost/?app=composer&path=vendor

      // Parse the URL and extract the query string

      // Convert the query string into an associative array

      // Now $queryArray contains the parsed query parameters as an array
//dd($_SERVER);
      if (preg_match('/^\/(?!\?)$/', $_SERVER['REQUEST_URI']))
        exit(header('Location: ' . APP_URL . '?'));

      if (isset($_SERVER['HTTP_REFERER'])) {
        parse_str(parse_url($_SERVER['HTTP_REFERER'])['query'] ?? '', $queryRefererArray);
        if (array_key_exists('debug', $queryRefererArray)) {
          parse_str(parse_url($_SERVER['REQUEST_URI'])['query'] ?? '', $queryArray);
          if (!array_key_exists('debug', $queryArray)) {
            Shutdown::setEnabled(true)->setShutdownMessage(fn() => header('Location: ' . APP_URL . '?debug&' . http_build_query($queryArray, '', '&')))->shutdown();
          } //else << NO ELSE!!
        }
      }

      if (isset($_GET['hide']) && $_GET['hide'] == 'update-notice') {
        $_ENV['DEFAULT_UPDATE_NOTICE'] = true; // var_export(true, true); // true
        Shutdown::setEnabled(true)->setShutdownMessage(fn() => header('Location: ' . APP_URL))->shutdown();
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
//dd('req method==' . $_SERVER['REQUEST_METHOD'], false);

if ($_SERVER['REQUEST_METHOD'] == 'GET' && __FILE__ == PATH_PUBLIC)
  if (defined('APP_QUERY') && empty(APP_QUERY) || isset($_GET['CLIENT']) || isset($_GET['DOMAIN']) && !defined('APP_ROOT')) {

    //dd('does this do anything? 1234 ' . $_SERVER['REQUEST_METHOD']);
    if (!isset($_ENV['DEFAULT_CLIENT']))
      $_ENV['DEFAULT_CLIENT'] = $_GET['CLIENT'];

    if (!isset($_ENV['DEFAULT_DOMAIN']))
      $_ENV['DEFAULT_DOMAIN'] = $_GET['DOMAIN'];

    if (defined('APP_QUERY') && empty(APP_QUERY))
      Shutdown::setEnabled(false)->setShutdownMessage(fn() => header('Location: ' . APP_URL . '?' . http_build_query([
        'client' => $_ENV['DEFAULT_CLIENT'],
        'domain' => $_ENV['DEFAULT_DOMAIN']
      ]) . '#'))->shutdown();
    else
      $_GET = array_merge($_GET, APP_QUERY);
  }
//dd($_GET);
//dd(__DIR__ . DIRECTORY_SEPARATOR);

if (/*APP_SELF === PATH_PUBLIC*/ dirname(APP_SELF) === dirname(PATH_PUBLIC)) {

  require_once 'idx.product.php';
  if (!empty($paths)) {
    do {
      $path = array_shift($paths);

      require_once 'idx.product.php'; // Always execute this
    } while ($path);
  }


  //require_once 'idx.product.php';
  // isset($paths) && !empty($paths)
  //dd(array_keys($apps['console']));

  //dd($appDirectory['body']); 
  //if ($_SERVER['REQUEST_METHOD'] == 'POST')
  //  dd($_POST);

  if (defined('APP_ENV'))
    switch (APP_ENV) {
      case 'develop':
        require_once 'idx.develop.php';
        break;
      case 'math':
        require_once 'idx.math.php';
        break;
      case 'staging':
        require_once 'idx.stage.php';
        break;
      case 'product':
      default:
        require_once 'idx.product.php';
        break;
    }
  else {
    define('APP_ENV', 'production');
    require_once 'idx.product.php';
  }
  // comment
}
