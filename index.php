<?php

// Check if the config file exists in various locations based on the current working directory
$path = null;
$publicDir = basename(getcwd()) == 'public';

// Determine the path based on current location and check if file exists
if ($publicDir) {
    // We are in the public directory
    if (is_file('../config/config.php')) {
        $path = realpath('../config/config.php');
    } elseif (is_file('config.php')) {
        $path = realpath('config.php');
    }
} else {
    // We are not in the public directory
    if (is_file('config/config.php')) {
        $path = realpath('config/config.php');
    } elseif (is_file(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php')) {
        $path = realpath(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
    }
}

// Load the config file if found
if ($path) {
    require_once $path;
} else {
    die(var_dump("Config file was not found."));
}

$previousFilename = '';

$dirs = [];

isset($_GET['app']) && $_GET['app'] == 'git' ?:
  (APP_SELF != APP_PATH_PUBLIC ? : 
    $dirs[] = APP_PATH . APP_BASE['config'] . 'git.php');

isset($_GET['app']) && $_GET['app'] == 'composer' ?:
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
        (!is_file($include = APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php') ?:
          (!isset($_ENV['COMPOSER']['AUTOLOAD']) || (bool) $_ENV['COMPOSER']['AUTOLOAD'] !== true) ?: $include)
      ]
    );

//if (is_file($path = APP_PATH . APP_BASE['config'] . 'composer.php')) require_once $path; 
//else die(var_dump("$path path was not found. file=" . basename($path)));

isset($_GET['app']) && $_GET['app'] == 'npm' ?:
  (APP_SELF != APP_PATH_PUBLIC ?: 
    (!is_file($include = APP_PATH . APP_BASE['config'] . 'npm.php') ?: $dirs[] = $include ));

unset($include);

usort($dirs, function ($a, $b) {
  if (dirname($a) . DIRECTORY_SEPARATOR . basename($a) === APP_PATH . APP_BASE['config'] . 'composer.php') // DIRECTORY_SEPARATOR
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