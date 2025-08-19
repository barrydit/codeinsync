<?php
declare(strict_types=1); // First Line Only!

!defined('APP_PATH') and define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

//!defined('APP_ROOT') ? define('APP_ROOT', '') : '';

//include 'dotenv.php';

date_default_timezone_set('America/Vancouver');

//$errors = []; // (object)

// Directory of this script
$isFile = function ($path) /*use (&$paths)*/ {
  if (is_file($path))
    require_once $path; // $paths[] = $path;
};

//$isFile(APP_PATH . 'php.php') ?:
//  $isFile('config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'php.php') ?:
//  $isFile('php.php');

if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
  $isFile(APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.php'); // constants.php
}


// require_once APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Check if the dd function exists
if (!function_exists('dd')) {

  // Custom error handler
  /**
   * Summary of customErrorHandler
   * @param mixed $errno
   * @param mixed $errstr
   * @param mixed $errfile
   * @param mixed $errline
   * @return bool
   */
  function customErrorHandler($errno, $errstr, $errfile, $errline)
  {
    global $errors;
    !defined('APP_ERROR') and define('APP_ERROR', true); // $hasErrors = true;
    !defined('APP_DEBUG') and define('APP_DEBUG', APP_ERROR);
    $errors['FUNCTIONS'] = 'functions.php failed to load. Therefore function dd() does not exist (yet).';

    foreach ([E_ERROR => 'Error', E_WARNING => 'Warning', E_PARSE => 'Parse Error', E_NOTICE => 'Notice', E_CORE_ERROR => 'Core Error', E_CORE_WARNING => 'Core Warning', E_COMPILE_ERROR => 'Compile Error', E_COMPILE_WARNING => 'Compile Warning', E_USER_ERROR => 'User Error', E_USER_WARNING => 'User Warning', E_USER_NOTICE => 'User Notice', E_STRICT => 'Strict Notice', E_RECOVERABLE_ERROR => 'Recoverable Error', E_DEPRECATED => 'Deprecated', E_USER_DEPRECATED => 'User Deprecated',] as $key => $value) {
      if ($errno == $key) {
        $errors[$key] = "$key => $value\n";
        $errors[] = "$value: $errstr in $errfile on line $errline\n";
        break;
      }
    }
    var_dump($errors);
    return false;
  }
  // Set the custom error handler
  set_error_handler("customErrorHandler");
}

require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.env.php';

// Enable debugging and error handling based on APP_DEBUG and APP_ERROR constants
!defined('APP_ERROR') and define('APP_ERROR', false);
!defined('APP_DEBUG') and define('APP_DEBUG', isset($_GET['debug']) ? TRUE : FALSE);

if (APP_DEBUG || APP_ERROR) {
  $errors['APP_DEBUG'] = "Debugging is enabled.\n";
  $errors['APP_ERROR'] = "Error handling is enabled.\n";
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL/*E_STRICT |*/);

  defined('PHP_ZTS') and $errors['PHP_ZTS'] = "PHP was built with ZTS enabled.\n";
  defined('PHP_DEBUG') and $errors['PHP_DEBUG'] = "PHP was built with DEBUG enabled.\n";
  defined('PHP_VERSION') and $errors['PHP_VERSION'] = "PHP Version: " . PHP_VERSION . "\n";
  // PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION, PHP_EXTRA_VERSION, PHP_VERSION_ID
  defined('PHP_OS') and $errors['PHP_OS'] = "PHP_OS: " . PHP_OS . "\n";
  // PHP_OS_FAMILY
  // PHP_EXEC
  defined('PHP_SAPI') and $errors['PHP_SAPI'] = "PHP_SAPI: " . PHP_SAPI . "\n";
  defined('PHP_BINARY') and $errors['PHP_BINARY'] = "PHP_BINARY: " . PHP_BINARY . "\n";
  defined('PHP_BINDIR') and $errors['PHP_BINDIR'] = "PHP_BINDIR: " . PHP_BINDIR . "\n";
  defined('PHP_CONFIG_FILE_PATH') and $errors['PHP_CONFIG_FILE_PATH'] = "PHP_CONFIG_FILE_PATH: " . PHP_CONFIG_FILE_PATH . "\n";
  defined('PHP_CONFIG_FILE_SCAN_DIR') and $errors['PHP_CONFIG_FILE_SCAN_DIR'] = "PHP_CONFIG_FILE_SCAN_DIR: " . PHP_CONFIG_FILE_SCAN_DIR . "\n";
  defined('PHP_SHLIB_SUFFIX') and $errors['PHP_SHLIB_SUFFIX'] = "PHP_SHLIB_SUFFIX: " . PHP_SHLIB_SUFFIX . "\n";
  defined('PHP_EOL') and $errors['PHP_EOL'] = 'PHP_EOL: ' . json_encode(PHP_EOL) . "\n";
  defined('PHP_INT_MIN') and $errors['PHP_INT_MIN'] = "PHP_INT_MIN: " . PHP_INT_MIN . "\n"; // -/+ 2147483648 32-bit
  defined('PHP_INT_MAX') and $errors['PHP_INT_MAX'] = "PHP_INT_MAX: " . PHP_INT_MAX . "\n"; // -/+ 9223372036854775808 64-bit
  // PHP_INT_SIZE
  defined('PHP_FLOAT_DIG') and $errors['PHP_FLOAT_DIG'] = "PHP_FLOAT_DIG: " . PHP_FLOAT_DIG . "\n";
  defined('PHP_FLOAT_EPSILON') and $errors['PHP_FLOAT_EPSILON'] = "PHP_FLOAT_EPSILON: " . PHP_FLOAT_EPSILON . "\n";
  defined('PHP_FLOAT_MIN') and $errors['PHP_FLOAT_MIN'] = "PHP_FLOAT_MIN: " . PHP_FLOAT_MIN . "\n";
  defined('PHP_FLOAT_MAX') and $errors['PHP_FLOAT_MAX'] = "PHP_FLOAT_MAX: " . PHP_FLOAT_MAX . "\n";
  // PHP_FD_SETSIZE

} else {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL/*E_STRICT |*/);
}

ini_set('error_log', is_dir($path = APP_PATH . 'config') ? dirname($path, 1) . DIRECTORY_SEPARATOR . 'error_log' : 'error_log');
ini_set('log_errors', 'true');

ini_set('xdebug.debug', '0'); // remote_enable
ini_set('xdebug.mode', 'develop'); // default_enable mode=develop,coverage,debug,gcstats,profile,trace
//ini_set('xdebug.mode', 'profile'); // profiler_enable

putenv("XDEBUG_MODE=off");
// Enable output buffering
ini_set('output_buffering', 'On');

ini_set("include_path", "src"); // PATH_SEPARATOR ;:

//dd($userObj->test(), false);

// Prevent direct access to the file
$isPhpVersion5OrHigher = version_compare(PHP_VERSION, '5.0.0', '>=');
$includedFilesCount = count(get_included_files());

if ($includedFilesCount === ($isPhpVersion5OrHigher ? 1 : 0)) {
  exit('Direct access is not allowed.');
}



!defined('DOMAIN_EXPR') and
  // const DOMAIN_EXPR = 'string only/non-block/ternary'; 
  define('DOMAIN_EXPR', $_ENV['SHELL']['EXPR_DOMAIN'] ?? '/(?:[a-z]+\:\/\/)?(?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*)?/i') and is_string(DOMAIN_EXPR) ? '' : $errors['DOMAIN_EXPR'] = 'DOMAIN_EXPR is not a valid string value.'; // /(?:\.(?:([-a-z0-9]+){1,}?)?)?\.[a-z]{2,6}$/';

// const DOMAIN_EXPR = 'string only/non-block/ternary';
// /(?:\.(?:([-a-z0-9]+){1,}?)?)?\.[a-z]{2,6}$/';

//die(var_dump($_SERVER['PHP_SELF'] . DIRECTORY_SEPARATOR . basename($_SERVER['PHP_SELF'])));

//$path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR ) . 'functions.php');

// (basename(__DIR__) != 'config' ?


//!is_file( dirname($_SERVER['PHP_SELF']) . basename($_SERVER['PHP_SELF']) ?? __FILE__) // (!empty(get_included_files()) ? get_included_files()[0] : __FILE__)

//if (APP_ROOT != '') {}

// Retrieve the latest commit SHA of the main branch from the remote repository


// Load the required files
//$paths[] = __DIR__ . DIRECTORY_SEPARATOR . 'constants.php'; //require('constants.php'); 

if (!defined('APP_ROOT'))
  if (array_key_first($_GET) != 'path') {

    // Determine base paths for client, domain, or project
    $clientPath = isset($_GET['client'])
      ? 'clients' . DIRECTORY_SEPARATOR . $_GET['client'] . DIRECTORY_SEPARATOR
      : (!empty($_ENV['DEFAULT_CLIENT']) && isset($_GET['client']) ? 'clients' . DIRECTORY_SEPARATOR . $_ENV['DEFAULT_CLIENT'] . DIRECTORY_SEPARATOR : '');

    $domainPath = isset($_GET['domain']) && $_GET['domain'] !== ''
      ? (isset($_GET['client'])
        ? $clientPath . $_GET['domain'] . DIRECTORY_SEPARATOR
        : 'clients' . DIRECTORY_SEPARATOR . $_GET['domain'] . DIRECTORY_SEPARATOR)
      : (!empty($_ENV['DEFAULT_DOMAIN']) ? 'clients' . DIRECTORY_SEPARATOR . $_ENV['DEFAULT_CLIENT'] . DIRECTORY_SEPARATOR . (array_key_first($_GET) == 'path' ? '' : (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cmd']) && in_array($_POST['cmd'], ['cd ../', 'chdir ../']) ? '' : $_ENV['DEFAULT_DOMAIN']) . DIRECTORY_SEPARATOR) : '')/*''*/ ;

    $projectPath = isset($_GET['project'])
      ? 'projects' . DIRECTORY_SEPARATOR . $_GET['project'] . DIRECTORY_SEPARATOR
      : '';

    // Final path prioritizing client/domain and falling back to project if present
    $path = $domainPath ?: $clientPath ?: $projectPath;
    //
    //die($path);
    // Validate path and define APP_ROOT if valid
    if ($path && is_dir(APP_PATH . $path)) {
      if (realpath($resolvedPath = rtrim($path, DIRECTORY_SEPARATOR)) !== false) {
        define('APP_ROOT', $resolvedPath ? $resolvedPath . DIRECTORY_SEPARATOR : '');
      }
    }

  } else {
    define('APP_ROOT', '');
    $errors['APP_ROOT'] = 'APP_ROOT was NOT defined.';
  }


!defined('APP_ROOT') and define('APP_ROOT', '');

//!defined('APP_ROOT')) ?: define('APP_ROOT', !empty(realpath(APP_PATH . APP_ROOT)) ? (string) APP_ROOT . DIRECTORY_SEPARATOR : '');

//if (!defined('APP_ROOT'))
//define('APP_ROOT', (!$path || !is_dir($path)) ? '' : $path);


/* if (!defined('APP_ROOT')) {
  $errors['APP_ROOT'] = 'APP_ROOT was NOT defined.';
  // Determine base paths for client, domain, or project
  $clientPath = isset($_GET['client'])
      ? APP_BASE['clients'] . $_GET['client'] . DIRECTORY_SEPARATOR
      : (!empty($_ENV['DEFAULT_CLIENT']) ? APP_BASE['clients'] . $_ENV['DEFAULT_CLIENT'] . DIRECTORY_SEPARATOR : '');

  $domainPath = isset($_GET['domain']) && $_GET['domain'] !== ''
      ? (isset($_GET['client']) 
          ? $clientPath . $_GET['domain'] . DIRECTORY_SEPARATOR
          : APP_BASE['clients'] . $_GET['domain'] . DIRECTORY_SEPARATOR)
      : (!empty($_ENV['DEFAULT_DOMAIN']) ? APP_BASE['clients'] . $_ENV['DEFAULT_DOMAIN'] . DIRECTORY_SEPARATOR : '');

  $projectPath = isset($_GET['project'])
      ? APP_BASE['projects'] . $_GET['project'] . DIRECTORY_SEPARATOR
      : '';

  // Final path prioritizing client/domain and falling back to project if present
  $path = $domainPath ?: $clientPath ?: $projectPath;

  // Validate path and define APP_ROOT if valid
  if ($path && is_dir($path)) {
      $resolvedPath = realpath(rtrim($path, DIRECTORY_SEPARATOR));
      define('APP_ROOT', $resolvedPath ? $resolvedPath . DIRECTORY_SEPARATOR : '');
  }
}*/

//dd($_GET);

/*
if (isset($_GET['path']) && $_GET['path'] != '' && realpath($_GET['path']) && is_dir($_GET['path']))
  $_GET['path'] = rtrim(ltrim($_GET['path'], '/'), '/'); 
*/
/*
if (isset($_GET['path']))
  if (realpath(APP_PATH . APP_ROOT . ($path = rtrim(ltrim($_GET['path'], DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR))) && $path != '')
    $_GET['path'] = (string) $path . DIRECTORY_SEPARATOR;
*/
// dd(getenv('PATH') . ' -> ' . PATH_SEPARATOR);

if (!defined('APP_PATH_CONFIG')) {
  define('APP_PATH_CONFIG', str_replace(APP_PATH, '', basename(dirname(__FILE__))) == 'config' ? __FILE__ : __FILE__);
}

//$errors->{'CONFIG'} = 'OK';


//(defined('APP_DEBUG') && APP_DEBUG) and $errors['APP_DEBUG'] = (bool) var_export(APP_DEBUG, APP_DEBUG); // print('Debug (Mode): ' . var_export(APP_DEBUG, true) . "\n");