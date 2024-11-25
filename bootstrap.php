<?php

// Define APP_PATH constant
!defined('APP_PATH') and define('APP_PATH', __DIR__ . DIRECTORY_SEPARATOR) and is_string(APP_PATH) ? '' : $errors['APP_PATH'] = 'APP_PATH is not a valid string value.';

if (!defined('DOMAIN_EXPR')) {
  // const DOMAIN_EXPR = 'string only/non-block/ternary';
  define('DOMAIN_EXPR', $_ENV['SHELL']['EXPR_DOMAIN'] ?? '/(?:[a-z]+\:\/\/)?(?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*)?/i'); // /(?:\.(?:([-a-z0-9]+){1,}?)?)?\.[a-z]{2,6}$/';
}

/*
if (!defined('APP_ROOT')) {
  $path = !isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'projects' . DIRECTORY_SEPARATOR . $_GET['project']) : 'clientele' . DIRECTORY_SEPARATOR . $_GET['client'] . DIRECTORY_SEPARATOR . (isset($_GET['domain']) && $_GET['domain'] != '' ? $_GET['domain'] : '') . DIRECTORY_SEPARATOR; /* ($_GET['path'] . '/' ?? '')*/
  //die($path);
  //is_dir(APP_PATH . $_GET['path'])
/*  !$path || !is_dir(APP_PATH . $path) ?:  
    define('APP_ROOT', !empty(realpath(APP_PATH . ($path = rtrim($path, DIRECTORY_SEPARATOR)) ) && $path != '') ? (string) $path . DIRECTORY_SEPARATOR : '');  // basename() does not like null
}*/


$path = null;

if (!empty($_GET['client']) || !empty($_GET['domain'])) {
    $clientFolder = 'clientele' . DIRECTORY_SEPARATOR . ($_GET['client'] ?? '') . DIRECTORY_SEPARATOR;
    $clientPath = __DIR__ . DIRECTORY_SEPARATOR . $clientFolder;

    // Retrieve directories that match the client path
    $dirs = array_filter(glob("$clientPath*"), 'is_dir');

    // Attempt to resolve the domain if only one directory is found
    if (count($dirs) === 1) {
        $dirName = strtolower(basename(reset($dirs)));
        if (preg_match(DOMAIN_EXPR, $dirName)) {
            $_GET['domain'] = $dirName;
        }
    }

    // Set the path based on the domain if provided
    if (!empty($_GET['domain'])) {
        foreach ($dirs as $dir) {
            if (basename($dir) === $_GET['domain']) {
                $path = $clientFolder . basename($dir) . DIRECTORY_SEPARATOR;
                break;
            }
        }
    } elseif (count($dirs) == 1) {
        // Default to the first available directory if no specific domain is provided
        $firstDir = reset($dirs);
        $_GET['domain'] = basename($firstDir);
        $path = $clientFolder . basename($firstDir) . DIRECTORY_SEPARATOR;
    } else {
        $path = $clientFolder;
    }

    if ($path && is_dir($path)) {
        //defined('APP_CLIENT') ?: define('APP_CLIENT', new clientOrProj($path));
        defined('APP_ROOT') ?: define('APP_ROOT', $path ?? $clientFolder);
    }

} elseif (!empty($_GET['project'])) {
    $projectFolder = 'projects' . DIRECTORY_SEPARATOR . $_GET['project'] . DIRECTORY_SEPARATOR;
    $projectPath = APP_PATH . $projectFolder;

    if (is_dir($projectPath)) {
        $path = $projectFolder;
        defined('APP_PROJECT') ?: define('APP_PROJECT', new clientOrProj($path));
    }
}

//die(var_dump($path));

defined('APP_ROOT') ?: define('APP_ROOT', $path ?? '');

// Check if the config file exists in various locations based on the current working directory
$path = null;

// Determine the path based on current location and check if file exists
switch (basename(__DIR__)) { // getcwd()
  case 'public':
    chdir(dirname(__DIR__));

    require_once 'config/php.php';

    if ($config = realpath(APP_PATH . 'config/config.php')) {
      $path = $config;
    }  // elseif (is_file('config.php')) { $path = $config; }
    break;
  default:
    require_once 'config/php.php';

    if ($config = realpath(APP_PATH . 'config/config.php')) {
      $path = $config;
    } // elseif (is_file($config = 'config.php')) { $path = $config; }
    break;
}

// Load the config file if found
if ($path) {
    require_once $path;
} else {
    die(var_dump($path));
}

$previousFilename = '';

$dirs = [APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'php.php'];

!isset($_GET['app']) || $_GET['app'] != 'git' ?:
  (APP_SELF != APP_PATH_PUBLIC ?: $dirs[] = APP_PATH . APP_BASE['config'] . 'git.php');

!isset($_GET['app']) || $_GET['app'] != 'composer' ?:
  $dirs = (APP_SELF != APP_PATH_PUBLIC) 
    ? array_merge(
      $dirs,
      [
        (!is_file($include = APP_PATH . APP_BASE['config'] . 'composer.php') ?: $include)
      ]
      )
    : array_merge(
      $dirs,
      [
        (!is_file($include = APP_PATH . APP_BASE['config'] . 'composer.php') ?: $include),
        (!is_file($include = APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php') ?: $include)
      ]
    );

//if (is_file($path = APP_PATH . APP_BASE['config'] . 'composer.php')) require_once $path; 
//else die(var_dump("$path path was not found. file=" . basename($path)));

!isset($_GET['app']) || $_GET['app'] != 'npm' ?:
  (APP_SELF != APP_PATH_PUBLIC ?: 
    (!is_file($include = APP_PATH . APP_BASE['config'] . 'npm.php') ?: $dirs[] = $include ));

unset($include);

usort($dirs, function ($a, $b) {
  if (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . APP_BASE['config'] . 'php.php')
    return -1; // $a comes after $b
  elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_PATH . APP_BASE['config'] . 'php.php')
    return 1; // $a comes before $b
  elseif (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . APP_BASE['config'] . 'composer.php') // DIRECTORY_SEPARATOR
    return -1; // $a comes after $b
  elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_PATH . APP_BASE['config'] . 'composer.php')
    return 1; // $a comes before $b
  elseif (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php') // DIRECTORY_SEPARATOR
    return -1; // $a comes after $b
  elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php')
    return 1; // $a comes before $b
  elseif (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . APP_BASE['config'] . 'git.php')
    return -1; // $a comes after $b
  elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_PATH . APP_BASE['config'] . 'git.php')
    return 1; // $a comes before $b
  //elseif (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . APP_BASE['config'] . 'npm.php')
  //  return -1; // $a comes after $b
  //elseif (dirname($b) . DIRECTORY_SEPARATOR . basename($b) === APP_PATH . APP_BASE['config'] . 'npm.php')
  //  return 1; // $a comes before $b
  else 
    return strcmp(basename($a), basename($b)); // Compare other filenames alphabetically
});

//dd($dirs, false);

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

    switch ($includeFile) {
      case APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php':
        (!isset($_ENV['COMPOSER']['AUTOLOAD']) || (bool) $_ENV['COMPOSER']['AUTOLOAD'] !== true || APP_SELF !== APP_PATH_SERVER) ?: require_once $includeFile;
        break;
      default:

        require_once $includeFile;
        break;
    }

    $previousFilename = $currentFilename;     
  }
}

// Check if the user has requested logout
if (filter_input(INPUT_GET, 'logout')) { // ?logout=true
  // Set headers to force browser to drop Basic Auth credentials
  header('WWW-Authenticate: Basic realm="Logged Out"');
  header('HTTP/1.0 401 Unauthorized');
    
  // Add cache control headers to prevent caching of the authorization details
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  header("Pragma: no-cache");
    
  // Unset the authentication details in the server environment
  unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    
  // Optional: Clear any existing headers related to authorization
  if (function_exists('header_remove')) {
    header_remove('HTTP_AUTHORIZATION');
    //header_remove('PHP_AUTH_USER');
    //header_remove('PHP_AUTH_PW');
  }

  // Provide feedback to the user and exit the script
  //header('Location: http://test:123@localhost/');
  exit('You have been logged out.');
}

//die(var_dump($_SERVER));
if (PHP_SAPI !== 'cli') {
  // Ensure the HTTP_AUTHORIZATION header exists
  if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    // Decode the HTTP Authorization header
    $authHeader = base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6));
    if ($authHeader) {
      // Split the decoded authorization string into user and password
      [$user, $password] = explode(':', $authHeader);

      // Set the PHP_AUTH_USER and PHP_AUTH_PW if available
      $_SERVER['PHP_AUTH_USER'] = $user ?? '';
      $_SERVER['PHP_AUTH_PW'] = $password ?? '';
    }
  }

  // Check if user credentials are provided
  if (empty($_SERVER['PHP_AUTH_USER'])) {
    // Prompt for Basic Authentication if credentials are missing
    header('WWW-Authenticate: Basic realm="Dashboard"');
    header('HTTP/1.0 401 Unauthorized');
  
    // Stop further script execution
    exit('Authentication required.');
  } else {
    // Display the authenticated user's details
    //echo "<p>Hello, {$_SERVER['PHP_AUTH_USER']}.</p>";
    //echo "<p>You entered '{$_SERVER['PHP_AUTH_PW']}' as your password.</p>";
    //echo "<p>Authorization header: {$_SERVER['HTTP_AUTHORIZATION']}</p>";
  }
}
/*
if (isset($_GET['debug'])) 
  require_once 'public/index.php';
else
  die(header('Location: public/index.php'));
*/