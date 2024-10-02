<?php
declare(strict_types=1); // First Line Only!

date_default_timezone_set('America/Vancouver');

$errors = []; // (object)

// Custom error handler
/**
 * Summary of customErrorHandler
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @return bool
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
  global $errors;
  !defined('APP_ERROR') and define('APP_ERROR', true); // $hasErrors = true;
  !defined('APP_DEBUG') and define('APP_DEBUG', APP_ERROR);

  foreach([
    E_ERROR => 'Error',
    E_WARNING => 'Warning',
    E_PARSE => 'Parse Error',
    E_NOTICE => 'Notice',
    E_CORE_ERROR => 'Core Error',
    E_CORE_WARNING => 'Core Warning',
    E_COMPILE_ERROR => 'Compile Error',
    E_COMPILE_WARNING => 'Compile Warning',
    E_USER_ERROR => 'User Error',
    E_USER_WARNING => 'User Warning',
    E_USER_NOTICE => 'User Notice',
    E_STRICT => 'Strict Notice',
    E_RECOVERABLE_ERROR => 'Recoverable Error',
    E_DEPRECATED => 'Deprecated',
    E_USER_DEPRECATED => 'User Deprecated',
  ] as $key => $value) {
    if ($errno == $key) {
      $errors[$key] = "$key => $value\n";
      $errors[] = "$value: $errstr in $errfile on line $errline\n";
      break;
    }
  }
  return false;
}
// Set the custom error handler
set_error_handler("customErrorHandler");

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

} else {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL/*E_STRICT |*/);
}

ini_set('error_log', is_dir($path = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config') ? dirname($path, 1) . DIRECTORY_SEPARATOR . 'error_log' : 'error_log');
ini_set('log_errors', 'true');

ini_set('xdebug.debug', '0'); // remote_enable
ini_set('xdebug.mode', 'develop'); // default_enable mode=develop,coverage,debug,gcstats,profile,trace
//ini_set('xdebug.mode', 'profile'); // profiler_enable

putenv("XDEBUG_MODE=off");
// Enable output buffering
ini_set('output_buffering', 'On');

ini_set("include_path", "src"); // PATH_SEPARATOR ;:

if (count(get_included_files()) == ((version_compare(PHP_VERSION, '5.0.0', '>=')) ? 1:0 )):
  exit('Direct access is not allowed.');
endif;

if (isset($_ENV['SHELL']['EXPR_DOMAIN']) && !defined('DOMAIN_EXPR'))
  define('DOMAIN_EXPR', $_ENV['SHELL']['EXPR_DOMAIN']); // const DOMAIN_EXPR = 'string only/non-block/ternary';
elseif(!defined('DOMAIN_EXPR'))
  define('DOMAIN_EXPR', '/(?:[a-z]+\:\/\/)?(?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*)?/i'); // /(?:\.(?:([-a-z0-9]+){1,}?)?)?\.[a-z]{2,6}$/';

if (isset($_ENV['SHELL']['PHP_EXEC']) && !defined('PHP_EXEC'))
  define('PHP_EXEC', $_ENV['SHELL']['PHP_EXEC'] ?? '/usr/bin/php'); // const DOMAIN_EXPR = 'string only/non-block/ternary';
// /(?:\.(?:([-a-z0-9]+){1,}?)?)?\.[a-z]{2,6}$/';

//die(var_dump($_SERVER['PHP_SELF'] . DIRECTORY_SEPARATOR . basename($_SERVER['PHP_SELF'])));

//$path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR ) . 'functions.php');

// (basename(__DIR__) != 'config' ?


//!is_file( dirname($_SERVER['PHP_SELF']) . basename($_SERVER['PHP_SELF']) ?? __FILE__) // (!empty(get_included_files()) ? get_included_files()[0] : __FILE__)

//if (APP_ROOT != '') {}

// Retrieve the latest commit SHA of the main branch from the remote repository

// Directory of this script
$isFile = function ($path) use (&$paths) {
  if (is_file($path)) {
    $paths[] = $path;
  }
};

$isFile(__DIR__ . DIRECTORY_SEPARATOR . 'functions.php') ?: $isFile('config/functions.php') ?: $isFile('functions.php');

if (empty($paths)) {
  die(var_dump("functions.php was not found."));
}

// Load the required files
//$paths[] = __DIR__ . DIRECTORY_SEPARATOR . 'constants.php'; //require('constants.php'); 

while ($path = array_shift($paths)) {
  if (is_file($path = realpath($path))) {
    require_once $path;
  } else {
    die(var_dump(basename($path) . ' was not found. file=' . $path));
  }
}

// Check if the dd function exists
if (!function_exists('dd')) {
  $errors['FUNCTIONS'] = 'functions.php failed to load. Therefore function dd() does not exist (yet).';
}

if (!empty($_GET['client']) || !empty($_GET['domain'])) {
  $path = /*'../../'.*/ 'clientele/' . ($_GET['client'] ?? '') . '/';
  $dirs = array_filter(glob(dirname(__DIR__) . "/$path*"), 'is_dir');

  if (count($dirs) == 1) {
    foreach($dirs as $dir) {
      $dirs[0] = $dirs[array_key_first($dirs)];
      if (preg_match(DOMAIN_EXPR, strtolower(basename($dirs[0])))) {
        $_GET['domain'] = basename($dirs[0]);
        break;
      } else {
        unset($dirs[array_key_first($dirs)]);
        continue;
      }
    }
  }

  $dirs = array_filter(glob(dirname(__DIR__) . '/' . $path . '*'), 'is_dir');

  if (!empty($_GET['domain'])) {
    foreach($dirs as $key => $dir) {
      if (basename($dir) == $_GET['domain']) {
        //if (is_dir($dirs[$key].'/public/')) $path .= basename($dirs[$key]).'/public/';
        $path .= basename($dirs[$key]) . DIRECTORY_SEPARATOR;
        break;
      }
    }
  } else if (!isset($_GET['domain']) && count($dirs) >= 1) {
    if (preg_match(DOMAIN_EXPR, strtolower(basename(array_values($dirs)[0])))) {
      $_GET['domain'] = basename(array_values($dirs)[0]);
      $path .= basename(array_values($dirs)[0]) . DIRECTORY_SEPARATOR;
    } else {
      $path .= ($_GET['domain'] = basename(array_values($dirs)[0])) . DIRECTORY_SEPARATOR;
    }
  }

  if (is_dir(APP_PATH . $path)) {
    defined('APP_CLIENT') ?: define('APP_CLIENT', new clientOrProj($path));
  }
} else if (!empty($_GET['project']) && is_dir(APP_PATH . 'projects/' . $_GET['project'])) {
  $path = /*'../../'.*/ 'projects/' . $_GET['project'] . '/';
  //$dirs = array_filter(glob(dirname(__DIR__) . '/' . $path . '*'), 'is_dir');
/*
  if (count($dirs) == 1) {
    foreach($dirs as $dir) {
      $dirs[0] = $dirs[array_key_first($dirs)];
      if (preg_match(DOMAIN_EXPR, strtolower(basename($dirs[0])))) {
        $_GET['domain'] = basename($dirs[0]);
        break;
      } else {
        unset($dirs[array_key_first($dirs)]);
        continue;
      }
    }
  }*/
}
unset($dirs);

if (!defined('APP_ROOT')) {
  $path = !isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'projects/' . $_GET['project'] . '/') : 'clientele/' . $_GET['client'] . '/' . (isset($_GET['domain']) ? ($_GET['domain'] != '' ? $_GET['domain'] . '/' : '') : ''); /* ($_GET['path'] . '/' ?? '')*/
  //die($path);
  //is_dir(APP_PATH . $_GET['path']) 
  define('APP_ROOT', !empty(realpath(APP_PATH . ($path = rtrim($path, DIRECTORY_SEPARATOR)) ) && $path != '') ? (string) $path . DIRECTORY_SEPARATOR : '');  // basename() does not like null
}
/*
if (isset($_GET['path']) && $_GET['path'] != '' && realpath($_GET['path']) && is_dir($_GET['path']))
  $_GET['path'] = rtrim(ltrim($_GET['path'], '/'), '/'); 
*/

isset($_GET['path'])
  and $_GET['path'] = !empty(realpath(APP_PATH . APP_ROOT . ($path = rtrim(ltrim($_GET['path'], DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR)) ) && $path != '') ? (string) $path . DIRECTORY_SEPARATOR : '';

// dd(getenv('PATH') . ' -> ' . PATH_SEPARATOR);

if (!defined('APP_PATH_CONFIG')) {
  define('APP_PATH_CONFIG', str_replace(APP_PATH, '', basename(dirname(__FILE__))) == 'config' ? __FILE__ : __FILE__);
}

//$errors->{'CONFIG'} = 'OK';

$ob_content = NULL;

ob_start();

if (isset($_GET['project'])) {
  //require_once('composer.php');
  //require_once('project.php');

  if (isset($_GET['app']) && $_GET['app'] == 'project') {
    require_once 'app.project.php';
  }
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

if (!is_dir($path = APP_PATH . 'projects')) {
  $errors['projects'] = "projects/ directory does not exist.\n";
  mkdir($path, 0777, true);
}

if (!is_file($file = APP_PATH . 'projects/index.php')) {
  $errors['projects'] = 'projects/index.php does not exist.';
  if (is_file($source_file = APP_PATH . 'var/source_code.json')) {
    $source_file = json_decode(file_get_contents($source_file));
    if ($source_file) {
      file_put_contents($file, $source_file->{'projects/index.php'});
    }
  }
  unset($source_file);
} elseif (is_file(APP_PATH . 'projects/index.php') && isset($_GET['project']) && $_GET['project'] == 'show') {
  Shutdown::setEnabled(false)->setShutdownMessage(function() {
    return eval('?>' . file_get_contents(APP_PATH . 'projects/index.php'));
  })->shutdown();
}

// function loadEnvConfig($file) {

//}

// Load the environment variables from the .env file
// = loadEnvConfig(APP_PATH . '.env');

$_ENV = (function($file = APP_PATH . '.env') {
  if (!file_exists($file)) {
    throw new \RuntimeException(sprintf('%s file does not exist', $file));
  }
  return parse_ini_file_multi($file);
})();

// Access the variables from the parsed .env file
$domain = $_ENV['SHELL']['DOMAIN'] ?? APP_DOMAIN ?? 'localhost';
$defaultUser = $_ENV['SHELL']['DEFAULT_USER'] ?? 'www-data';
$documentRoot = $_ENV['SHELL']['DOCUMENT_ROOT'] ?? $_SERVER['DOCUMENT_ROOT'];
$homePathEnv = $_ENV['SHELL']['HOME_PATH'] ?? $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '';

if (stripos(PHP_OS, 'WIN') === 0) {
  $shell_prompt = 'www-data' . '@' . $domain . PATH_SEPARATOR . (($homePath = realpath($_SERVER['DOCUMENT_ROOT'])) === getcwd() ? '~': $homePath) . '$ ';
} else if (isset($_SERVER['HOME']) && ($homePath = realpath($_SERVER['HOME'])) !== false && ($docRootPath = realpath($_SERVER['DOCUMENT_ROOT'])) !== false && strpos($homePath, $docRootPath) === 0) {
  $shell_prompt = $_SERVER['USER'] . '@' . $domain . PATH_SEPARATOR . ($homePath == getcwd() ? '~': $homePath) . '$ ';
} elseif (isset($_SERVER['USER'])) {
  $shell_prompt = $_SERVER['USER'] . '@' . $domain . PATH_SEPARATOR . ($homePath == getcwd() ? '~': $homePath) . '$ ';
} else {
  $shell_prompt = 'www-data' . '@' . $domain . PATH_SEPARATOR . (getcwd() == '/var/www' ? '~' : getcwd()) . '$ ';
}


if (basename($dir = getcwd()) != 'config') {
  if (in_array(basename($dir), ['public', 'public_html']))
    chdir('../');


  chdir(APP_PATH . APP_ROOT);
  if (is_file($file = APP_PATH . APP_ROOT . '.env')) {
    $env = parse_ini_file_multi($file);
    $_ENV = array_merge_recursive_distinct($_ENV, $env);

/*
        foreach($env as $key => $value) {
            if (is_array($value)) {
                foreach($value as $k => $v) {
                    // Convert boolean values to strings
                    $_ENV[$key][$k] = is_bool($v) ? ($v ? 'true' : 'false') : (string) $v;
                }
            } else {
                // Convert boolean values to strings
                $_ENV[$key] = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value; // putenv($key.'='.$env_var);
            }
        }*/
    //}
  }

  chdir(APP_PATH);

} elseif (basename(dirname(APP_SELF)) == 'public_html') { // basename(__DIR__) == 'public_html'
  $errors['APP_PATH_PUBLIC'] = "The `public_html` scenario was detected.\n";
  
  if (is_dir(dirname(APP_SELF, 2) . '/config')) {
    $errors['APP_PATH_PUBLIC'] .= "\t" . dirname(APP_SELF, 2) . '/config/*' . ' was found. This is not generally safe-scenario.'; 
  }

  chdir(dirname(__DIR__, 1));  //dd(getcwd());
    // It is under the public_html scenario
    // Perform actions or logic specific to the public_html directory
    // For example:
    // include '/home/user_123/public_html/config.php';
} elseif (basename(dirname(APP_SELF)) == 'public') {    // strpos(APP_SELF, '/public/') !== false
  
  dd(APP_BASE);

  if (!is_file(APP_PATH . APP_BASE['public'] . 'install.php'))
    if (@touch(APP_PATH . APP_BASE['public'] . 'install.php'))
      file_put_contents(APP_PATH . APP_BASE['public'] . 'install.php', '<?php ' . <<<END
if (\$_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach (['composer.php', 'config.php', 'constants.php', 'functions.php', 'git.php'] as \$file) {
        if (!rename(APP_PATH . \$file, APP_PATH . 'config' . DIRECTORY_SEPARATOR . \$file))
            \$errors['INSTALL_DESTPATH'] .= "(config) Failed to move '" . APP_PATH . "\$file'";
    }

    foreach (['composer_app.php', 'index.php'] as \$file) {
        if (!rename(APP_PATH . \$file, APP_PATH . 'public' . DIRECTORY_SEPARATOR . \$file))
            \$errors['INSTALL_DESTPATH'] .= "(public) Failed to move '" . APP_PATH . "\$file'";
    }

    if (!is_file(APP_PATH . 'index.php'))
        if (@touch(APP_PATH . 'index.php'))
            file_put_contents(APP_PATH . 'index.php', '<?php require_once \'public/index.php\';');

    unlink(__FILE__);
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="pragma" content="no-cache">
  <meta http-equiv="expires" content="0">

<style>
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}
</style>
</head>
<body>
<div style="position: relative; margin: 0 auto; border: 1px solid #000;">
<div style="position: absolute; top: 0; left: 50%; transform: translate(-50%, 10%); text-align: center; width: 570px; height: 600px; background-position: center; background-size: cover; background-repeat: no-repeat; background-image: url('/resources/images/install-scenario-small.gif'); opacity: 0.8; z-index: 1; border: 1px solid #000;">

<div style="position: absolute; top: 225px; left: 129px; width: 230px; height: 200px; border: 1px dashed #000;">
<form>
<div style="position: absolute; top: 30px; left: 28px;"><input type="radio" name="scenario" value="1" checked /></div>

<div style="position: absolute; top: 30px; right: 20px;"><input type="radio" name="scenario" value="2" /></div>

<div style="position: absolute; bottom: 34px; right: 20px;"><input type="radio" name="scenario" value="3" /></div>
</form>
</div>

</div>
</div>
</body>
</html>
END
);

  if (basename(get_required_files()[0]) !== 'release-notes.php')
    if (is_dir('config')) {
      $previousFilename = ''; // Initialize the previous filename variable

//$files = glob(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '*.php');
//$files = array_merge($files, glob(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '**' . DIRECTORY_SEPARATOR . '*.php'));

//sort($files);

      foreach (array_filter(glob(__DIR__ . DIRECTORY_SEPARATOR . '*.php'), 'is_file') as $includeFile) {
        //echo $includeFile . "<br />\n";

        if (in_array($includeFile, get_required_files())) continue; // $includeFile == __FILE__

        if (!file_exists($includeFile)) {
          error_log("Failed to load a necessary file: " . $includeFile . PHP_EOL);
          break;
        } else {
          $currentFilename = substr(basename($includeFile), 0, -4);
    
          //$pattern = '/^' . preg_quote($previousFilename, '/')  . /*_[a-zA-Z0-9-]*/'(_\.+)?\.php$/'; // preg_match($pattern, $currentFilename)

          if (!empty($previousFilename) && strpos($currentFilename, $previousFilename) !== false) {
            continue;
          } else {
            // dd('file:'.$currentFilename,false);

            require_once $includeFile;

            $previousFilename = $currentFilename;
          }
        }
      }

    } else if (!in_array($path = realpath('config.php'), get_required_files()))
      require_once $path;

    if (defined('APP_PROJECT')) require_once 'public/install.php';
}

/*
if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR ) . 'constants.php')) // is_file('config/constants.php')) 
  if (!in_array($path, get_required_files()))
    require_once $path;
*/

//dd(get_defined_constants(true)['user']); // true

/*

// Define your installation constants
define('INSTALL_ROOT', $_SERVER['DOCUMENT_ROOT']);  // Document root
define('APP_ROOT', __DIR__);  // Directory of this script
define('SRC_DIR', '../src/');
define('PUBLIC_DIR', '../public/');
define('CONFIG_DIR', '../config/');

// Get the request path from the URL
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Determine if installation is needed
$installNeeded = (realpath(INSTALL_ROOT) === realpath(APP_ROOT));

// Perform installation if needed
if ($installNeeded) {
    // Determine the target directories based on request path
    $targetDirs = [
        '/' => PUBLIC_DIR,
        '/subdir/' => SRC_DIR,
        '/config/' => CONFIG_DIR,
    ];

    // Find the appropriate target directory
    $targetDir = '';
    foreach ($targetDirs as $pathPrefix => $dir) {
        if (strpos($requestPath, $pathPrefix) === 0) {
            $targetDir = $dir;
            break;
        }
    }

    if (!$targetDir) {
        echo "Installation path not found for request path: $requestPath";
    } else {
        // Define source and destination paths
        $sourceFile = __FILE__;
        $destinationFile = $targetDir . basename($sourceFile);

        // Perform installation (copy the script)
        if (copy($sourceFile, $destinationFile)) {
            echo "Installation successful. Copied script to: $destinationFile";
        } else {
            echo "Installation failed. Unable to copy script.";
        }
    }
} else {
    echo "Installation not needed.";
}

*/

/* Install code ...

$installNeeded = (realpath($_SERVER['DOCUMENT_ROOT']) === realpath(APP_PATH));

if ($installNeeded) {
    // Define your target directories
    $srcDir = APP_PATH . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    $publicDir = APP_PATH . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
    $configDir = APP_PATH . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

    // Perform installation (example: copy files)
    // copy('source_path/file.php', $srcDir . 'file.php');
    // copy('source_path/index.php', $publicDir . 'index.php');
    // copy('source_path/config.php', $configDir . 'config.php');
    
    echo "Installation performed.";
} else {
    echo "Installation not needed.";
}


if (dirname(APP_SELF) == __DIR__) {
  if (dirname(APP_PATH_CONFIG) != 'config')
    if (!is_file(APP_PATH . 'install.php'))
      if (@touch(APP_PATH . 'install.php')) {
        file_put_contents(APP_PATH . 'install.php', '<?php ' . <<<END
foreach (['composer.php', 'config.php', 'constants.php', 'functions.php'] as \$file) {
    if (!rename(APP_PATH . \$file, APP_PATH . 'config' . DIRECTORY_SEPARATOR . \$file))
        \$errors['INSTALL_DESTPATH'] .= "(config) Failed to move '" . APP_PATH . "\$file'";
}

foreach (['composer_app.php', 'index.php'] as \$file) {
    if (!rename(APP_PATH . \$file, APP_PATH . 'public' . DIRECTORY_SEPARATOR . \$file))
        \$errors['INSTALL_DESTPATH'] .= "(public) Failed to move '" . APP_PATH . "\$file'";
}

if (!is_file(APP_PATH . 'index.php'))
    if (@touch(APP_PATH . 'index.php'))
        file_put_contents(APP_PATH . 'index.php', '<?php require_once \'public/index.php\';');

unlink(__FILE__);
END
);
      define('APP_INSTALL', true);
    }
}
*/

//(!extension_loaded('gd'))
//  and $errors['ext/gd'] = 'PHP Extension: <b>gd</b> must be loaded inorder to export to xls for (PHPSpreadsheet).';

//dd(); // dd(getcwd());

// var_dump(get_defined_constants(true)['user']);

//echo ;
/*
if (is_array($errors) && !empty($errors)) { ?>
<html>
<head><title>Error page</title></head>
<body>
<ul>
<?php foreach ($errors as $key => $error) { ?>
  <li><?= $key . ' => ' . $error ?></li>
<?php } ?>
</ul>
</body>
</html>
<?php
  die();
} */

use vlucas\phpdotenv;

if (class_exists('Dotenv')) {
  $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
  $dotenv->safeLoad();
}
//dd($_ENV);

// $dotenv->load();


/*
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->safeLoad();
*/
define('APP_ERRORS', $errors ?? (($error = ob_get_contents()) == null ? null : "ob_get_contents() maybe populated/defined/errors... error=$error" ));
ob_end_clean();


//(defined('APP_DEBUG') && APP_DEBUG) and $errors['APP_DEBUG'] = (bool) var_export(APP_DEBUG, APP_DEBUG); // print('Debug (Mode): ' . var_export(APP_DEBUG, true) . "\n");
