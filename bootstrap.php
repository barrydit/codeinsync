<?php

// Define APP_PATH constant
!defined('APP_PATH')
  and define('APP_PATH', __DIR__ . DIRECTORY_SEPARATOR)
  and is_string(APP_PATH)
  ? ''
  : $errors['APP_PATH'] = "APP_PATH is not a valid string value.\n";

/*
!defined('DOMAIN_EXPR') and 
  // const DOMAIN_EXPR = 'string only/non-block/ternary'; 
  define('DOMAIN_EXPR', $_ENV['SHELL']['EXPR_DOMAIN'] ?? '/(?:[a-z]+\:\/\/)?(?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*)?/i') and is_string(DOMAIN_EXPR) ? '' : $errors['DOMAIN_EXPR'] = 'DOMAIN_EXPR is not a valid string value.'; // /(?:\.(?:([-a-z0-9]+){1,}?)?)?\.[a-z]{2,6}$/';
*/

/*
if (!defined('APP_ROOT')) {
  $path = !isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'projects' . DIRECTORY_SEPARATOR . $_GET['project']) : 'clientele' . DIRECTORY_SEPARATOR . $_GET['client'] . DIRECTORY_SEPARATOR . (isset($_GET['domain']) && $_GET['domain'] != '' ? $_GET['domain'] : '') . DIRECTORY_SEPARATOR; /* ($_GET['path'] . '/' ?? '')*/
//die($path);
//is_dir(APP_PATH . $_GET['path'])
/*  !$path || !is_dir(APP_PATH . $path) ?:  
    define('APP_ROOT', !empty(realpath(APP_PATH . ($path = rtrim($path, DIRECTORY_SEPARATOR)) ) && $path != '') ? (string) $path . DIRECTORY_SEPARATOR : '');  // basename() does not like null
}*/

$projectFolder = 'projects' . DIRECTORY_SEPARATOR . ($_GET['project'] ?? '');
$projectPath = __DIR__ . DIRECTORY_SEPARATOR . $projectFolder;

$clientFolder = 'clientele' . DIRECTORY_SEPARATOR . ($_GET['client'] ?? '');
$clientPath = __DIR__ . DIRECTORY_SEPARATOR . $clientFolder;

/**
 * Resolve domain from available directories or fallback to the client folder.
 */
function resolveProject($dirs, $requestedProject = null)
{
  // Match requested domain to available directories
  if ($requestedProject) {
    foreach ($dirs as $dir) {
      if (basename($dir) === $requestedProject) {
        return basename($dir);
      }
    }
  }

  // If no domain requested and exactly one directory exists, use it
  if (count($dirs) === 1) {
    return basename(reset($dirs));
  }

  // No valid domain found
  return null;
}


/**
 * Resolve domain from available directories or fallback to the client folder.
 */
function resolveDomain($dirs, $requestedDomain = null)
{
  // Match requested domain to available directories
  if ($requestedDomain) {
    foreach ($dirs as $dir) {
      if (basename($dir) === $requestedDomain) {
        return basename($dir);
      }
    }
  }

  // If no domain requested and exactly one directory exists, use it
  if (count($dirs) === 1) {
    return $_GET['domain'] = basename(reset($dirs));
  }

  // No valid domain found
  return null;
}

/**
 * Resolve the client folder path if no domain is provided.
 */
function resolveClient($clientFolder)
{
  // Check if the provided client folder exists
  if (is_dir(__DIR__ . DIRECTORY_SEPARATOR . $clientFolder)) {
    return $clientFolder . DIRECTORY_SEPARATOR;
  }

  // Return empty if client folder doesn't exist
  return '';
}

// Retrieve directories that match the client path
$proj_dirs = array_filter(glob(dirname($projectPath) . DIRECTORY_SEPARATOR . '*'), 'is_dir');

// Retrieve directories that match the client path
$dirs = array_filter(glob($clientPath . DIRECTORY_SEPARATOR . '*'), 'is_dir');

// Main logic to resolve the path
$path = null;
$project = resolveProject($proj_dirs, $_GET['project'] ?? null);
$domain = resolveDomain($dirs, $_GET['domain'] ?? null);

//die(var_dump($_GET['domain']));
//die(var_dump($projectFolder));
if ($project) {
  $path = $projectFolder . DIRECTORY_SEPARATOR;
} elseif ($domain) {
  // Resolve path based on domain
  $path = rtrim($clientFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR;
} elseif (!empty($_GET['client'])) {
  // Special case: resolve based on client folder
  $path = resolveClient($clientFolder); // ;
} elseif (count($dirs) === 1) {
  // Default to the only directory if one exists
  $path = reset($dirs);
} else {
  // Fallback to an empty path
  $path = '';
}

// Remove APP_PATH prefix for clean path definition
$path = preg_replace(
  '#' . preg_quote(APP_PATH, '#') . '#',
  '',
  $path
);
//define('APP_ROOT', $_GET['client'] . '/' . $_GET['domain']);
// Define APP_ROOT using the directory of the resolved path
defined('APP_ROOT') || define('APP_ROOT', is_dir(APP_PATH . $path) ? $path : '/test');

//die(APP_ROOT);
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


/*
if (isset($_GET['debug'])) 
  require_once 'public/index.php';
else
  die(header('Location: public/index.php'));
*/