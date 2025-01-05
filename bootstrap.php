<?php

// Define APP_PATH constant
!defined('APP_PATH')
  and define('APP_PATH', __DIR__ . DIRECTORY_SEPARATOR)
    and is_string(APP_PATH) 
    ? ''
    : $errors['APP_PATH'] = 'APP_PATH is not a valid string value.' . "\n";

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
  $path = ''; // resolveClient($clientFolder);
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

// Define APP_ROOT using the directory of the resolved path
defined('APP_ROOT') || define('APP_ROOT', is_dir(APP_PATH . $path) ? $path : '');
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

$previousFilename = '';

// Handle the 'php' app configuration
$dirs = [APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'php.php'];

// Handle the 'git' app configuration
!isset($_GET['app']) || $_GET['app'] != 'git' ?:
  (APP_SELF != APP_PATH_PUBLIC ?: $dirs[] = APP_PATH . APP_BASE['config'] . 'git.php');

// Handle the 'composer' app configuration
!isset($_GET['app']) || $_GET['app'] != 'composer' ?:
  $dirs = (APP_SELF != APP_PATH_PUBLIC)
  ? array_merge(
    $dirs,
    [
      (file_exists($include = APP_PATH . APP_BASE['config'] . 'composer.php') && !is_file($include) ?: $include)
    ]
  )
  : array_merge(
    $dirs,
    [
      (!file_exists($include = APP_PATH . APP_BASE['config'] . 'composer.php') && !is_file($include) ?: $include),
      (!file_exists($include = APP_PATH . APP_BASE['vendor'] . 'autoload.php') && !is_file($include) ?: $include),
    ]
  );

//if (is_file($path = APP_PATH . APP_BASE['config'] . 'composer.php')) require_once $path; 
//else die(var_dump("$path path was not found. file=" . basename($path)));

// Handle the 'npm' app configuration
!isset($_GET['app']) || $_GET['app'] != 'npm' ?:
  (APP_SELF != APP_PATH_PUBLIC ?:
    (!is_file($include = APP_PATH . APP_BASE['config'] . 'npm.php') ?: $dirs[] = $include));

unset($include);

if (APP_SELF != APP_PATH_PUBLIC) {
  $priorityFiles = [
    APP_PATH . APP_BASE['config'] . 'php.php',
    APP_PATH . APP_BASE['config'] . 'composer.php',
    APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php',
    APP_PATH . APP_BASE['config'] . 'git.php',
    // APP_PATH . APP_BASE['config'] . 'npm.php', // Uncomment if needed
  ];

  usort($dirs, function ($a, $b) use ($priorityFiles) {
    $fullPathA = dirname($a) . DIRECTORY_SEPARATOR . basename($a);
    $fullPathB = dirname($b) . DIRECTORY_SEPARATOR . basename($b);

    $priorityA = array_search($fullPathA, $priorityFiles);
    $priorityB = array_search($fullPathB, $priorityFiles);

    // Compare based on priority if either $a or $b is in the priority list
    if ($priorityA !== false || $priorityB !== false) {
      return ($priorityA !== false ? $priorityA : PHP_INT_MAX)
        - ($priorityB !== false ? $priorityB : PHP_INT_MAX);
    }

    // Fallback: Compare alphabetically by basename
    return strcmp(basename($a), basename($b));
  });
}


//dd($dirs, false);
foreach ($dirs as $includeFile) {
  $path = dirname($includeFile);

  // Skip already included files or specific files like 'composer-setup.php'
  if (in_array($includeFile, get_required_files()) || basename($includeFile) === 'composer-setup.php') {
    continue;
  }

  // Log an error and exit if the file does not exist
  if (!file_exists($includeFile)) {
    error_log("Failed to load a necessary file: {$includeFile}" . PHP_EOL);
    break;
  }

  $currentFilename = substr(basename($includeFile), 0, -4); // Remove file extension

  // Skip files if they are related to the previously processed filename
  if (!empty($previousFilename) && strpos($currentFilename, $previousFilename) !== false) {
    continue;
  }

  // Include files based on specific conditions
  if ($includeFile === APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php') {
    if (
      isset($_ENV['COMPOSER']['AUTOLOAD']) &&
      (bool) $_ENV['COMPOSER']['AUTOLOAD'] === true &&
      APP_SELF === APP_PATH_SERVER
    ) {
      require_once $includeFile;
    }
  } else {
    require_once $includeFile;
  }

  // Track the current file for the next iteration
  $previousFilename = $currentFilename;
}

// Handle logout requests
if (filter_input(INPUT_GET, 'logout')) {
  logoutUser();
  exit('You have been logged out.');
}

// Ensure authentication for non-CLI environments
if (PHP_SAPI !== 'cli') {
  authenticateUser();
}

/**
 * Logs out the user by forcing the browser to clear Basic Auth credentials.
 */
function logoutUser(): void
{
  // Send headers to clear Basic Auth credentials
  header('WWW-Authenticate: Basic realm="Logged Out"');
  header('HTTP/1.0 401 Unauthorized');

  // Prevent caching of authorization details
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
  header('Pragma: no-cache');

  // Clear authentication details from the server environment
  unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

  // Remove authorization headers if supported
  if (function_exists('header_remove')) {
    header_remove('HTTP_AUTHORIZATION');
  }
}

/**
 * Authenticates the user using Basic Auth.
 */
function authenticateUser(): void
{
  // Decode HTTP_AUTHORIZATION if present
  if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    decodeAuthHeader();
  }

  // Prompt for credentials if missing
  if (empty($_SERVER['PHP_AUTH_USER'])) {
    sendAuthPrompt();
  } else {
    // Optional: Display user details (for debugging or logging)
    // echo "<p>Hello, {$_SERVER['PHP_AUTH_USER']}.</p>";
    // echo "<p>You entered '{$_SERVER['PHP_AUTH_PW']}' as your password.</p>";
  }
}

/**
 * Decodes the HTTP Authorization header into user and password.
 */
function decodeAuthHeader(): void
{
  $authHeader = base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6));
  if ($authHeader) {
    [$user, $password] = explode(':', $authHeader);
    $_SERVER['PHP_AUTH_USER'] = $user ?? '';
    $_SERVER['PHP_AUTH_PW'] = $password ?? '';
  }
}

/**
 * Sends a Basic Auth prompt to the client.
 */
function sendAuthPrompt(): void
{
  header('WWW-Authenticate: Basic realm="Dashboard"');
  header('HTTP/1.0 401 Unauthorized');
  exit('Authentication required.');
}


/*
if (isset($_GET['debug'])) 
  require_once 'public/index.php';
else
  die(header('Location: public/index.php'));
*/