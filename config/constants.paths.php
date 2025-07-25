<?php
// This may not be a good idea...
//if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'config.php')) // is_file('config' . DIRECTORY_SEPARATOR . 'config.php')) 
//  require_once $path;

// Enable output buffering
//ini_set('output_buffering', 'On');

// Increase the maximum execution time to 60 seconds
//ini_set('max_execution_time', 60);

/* This code sets up some basic configuration constants for a PHP application. */

$user = ''; // www-data
$password = ''; // password
$use_sudo = $_ENV['SHELL']['SUDO'] ?? true;

// Define APP_SUDO constant based on OS
if (!$use_sudo && !defined('APP_SUDO'))
  if (stripos(PHP_OS, 'WIN') === 0) {
    // Windows command (insert specific command if needed)
    define('APP_SUDO', null /*'runas /user:Administrator "cmd /c" '*/);
  } else {
    // Linux command setup
    $sudoCommand = $use_sudo ? 'sudo -S ' : '';
    $passwordPart = $password ? 'echo ' . escapeshellarg($password) . ' | ' : '';
    $userPart = $user ? '-u ' . escapeshellarg($user) . ' ' : '';

    define('APP_SUDO', "{$passwordPart}{$sudoCommand}{$userPart}");
  }

/*
!defined('APP_SUDO') and define('APP_SUDO', stripos(PHP_OS, 'WIN') === 0
  ? ''  // For Windows, you can insert the appropriate command 
  : ($use_sudo 
    ? (!isset($password) && !$password
      ?: 'echo ' . escapeshellarg($password ?? '') . ' | ' . escapeshellarg($password ?? '')) . 'sudo -S ' . (!isset($user) && !$user ?: '-u ' . escapeshellarg($user ?? '') . ' ') // For Linux, you can insert the appropriate command
    : '')
);*/

const CONSOLE = true;

const APP_RUNNING = true;

// Define APP_START constant
!defined('APP_START') and define('APP_START', $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)) ?: is_float(APP_START) or $errors['APP_START'] = 'APP_START is not a valid float value.';

// Define APP_SELF constant
!defined('APP_SELF') and define('APP_SELF', get_included_files()[0] ?? __FILE__) and is_string(APP_SELF) ?: $errors['APP_SELF'] = 'APP_SELF is not a valid string value.';

// Define APP_PATH constant
!defined('APP_PATH') and define('APP_PATH', realpath(dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR) and is_string(APP_PATH) ?: $errors['APP_PATH'] = 'APP_PATH is not a valid string value.';

/*
!defined('APP_DEV') and define('APP_DEV', true) ?: is_bool(APP_DEV) or $errors['APP_DEV'] = 'APP_DEV is not a valid boolean value.';
!defined('APP_PROD') and define('APP_PROD', false) ?: is_bool(APP_PROD) or $errors['APP_PROD'] = 'APP_PROD is not a valid boolean value.';
!defined('APP_TEST') and define('APP_TEST', false) ?: is_bool(APP_TEST) or $errors['APP_TEST'] = 'APP_TEST is not a valid boolean value.';
!defined('APP_DEBUG') and define('APP_DEBUG', true) ?: is_bool(APP_DEBUG) or $errors['APP_DEBUG'] = 'APP_DEBUG is not a valid boolean value.';
*/

//echo 'Checking Constants: ' . "\n\n";

$auto_clear = false;

/*
define('SOCKET_INFO', sprintf(<<<EOD
%s
EOD, 'test'));
*/

// Check if the request is using HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') // $_SERVER['REQUEST_SCHEME']   
  define('APP_HTTPS', TRUE);

if (defined('APP_HTTPS') && APP_HTTPS)
  $errors['APP_HTTPS'] = (bool) var_export(APP_HTTPS, true); // print('HTTPS: ' . APP_HTTPS . "\n");


//define('APP_URL', 'http' . (defined('APP_HTTPS') ? 's' : '') . '://' . isset($_SERVER['SERVER_NAME']) ? '' : ($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_ADDR'] ?? 'localhost' . parse_url(isset($_SERVER['SERVER_NAME']) ? $_SERVER['REQUEST_URI'] : '' , PHP_URL_PATH)));

/*
!defined('APP_PATH_SERVER') and define('APP_PATH_SERVER', (defined('APP_PATH') ? APP_PATH
  : __DIR__ . DIRECTORY_SEPARATOR) . str_replace(defined('APP_PATH') ? APP_PATH
  : __DIR__ . DIRECTORY_SEPARATOR, '', basename(dirname(APP_SELF)) == 'public' ? basename(APP_SELF)
  : 'server.php'));
*/

if (!defined('APP_PATH_SERVER')) {
  $basePath = defined('APP_PATH') ? APP_PATH : __DIR__ . DIRECTORY_SEPARATOR;
  $fileName = (basename(dirname(APP_SELF)) === 'public') ? basename(APP_SELF) : 'server.php';

  define('APP_PATH_SERVER', "$basePath$fileName");
}

// Define APP_TIMEOUT constant
define('APP_TIMEOUT', strtotime("1970-01-01 08:00:00GMT"));
if (defined('APP_TIMEOUT') && !is_int(APP_TIMEOUT)) {
  $errors['APP_TIMEOUT'] = APP_TIMEOUT;
}

if (!empty($login = [/*'UNAME' => '', 'PWORD' => ''*/])) {
  define('APP_LOGIN', $login);
}
if (defined('APP_LOGIN') && is_array(APP_LOGIN)) {
  if (empty(APP_LOGIN['UNAME']) || empty(APP_LOGIN['PWORD'])) {
    $errors['APP_LOGIN'] = APP_LOGIN;
  }
}

// require_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.url.php';

switch (basename(__DIR__)) {
  case 'config':
  default:
    $host = defined('APP_HOST') ? APP_HOST : ($_SERVER['HTTP_HOST'] ?? '127.0.0.1');
    $domain = defined('APP_DOMAIN') ? APP_DOMAIN : ($host ?? 'localhost');

    if ($host === '127.0.0.1' || $domain === 'localhost') {
      $errors['WHOIS'] = "WHOIS is disabled on localhost.\n";
    }

    // Define your paths as pure array (no associative keys unless you plan to use them)
    $base_paths = [ // https://stackoverflow.com/questions/8037266/get-the-url-of-a-file-included-by-php
      'app',
      'config',
      'clients' => 'projects' . DIRECTORY_SEPARATOR . 'clients',
      'data',
      'public',
      'projects' => 'projects' . DIRECTORY_SEPARATOR . 'internal',
      'resources',
      'node_modules',
      'src',
      'var',
      'vendor',
      // 'tmp' => 'var' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
      // 'export' =>  'var' . DIRECTORY_SEPARATOR . 'export' . DIRECTORY_SEPARATOR,
      // 'session' => 'var' . DIRECTORY_SEPARATOR . 'session' . DIRECTORY_SEPARATOR,
    ]/*+['docs' => 'public' . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR, 'policies' => 'public' . DIRECTORY_SEPARATOR . 'policies' . DIRECTORY_SEPARATOR]*/ ;

    $baseDir = defined('APP_PATH') ? APP_PATH : __DIR__ . DIRECTORY_SEPARATOR;
    $validated_paths = [];
    //$errors = [];
    $processedCommon = [];

    // Step 1: Validate base paths
    foreach ($base_paths as $key => $subpath) {
      $alias = is_string($key) ? $key : $subpath;
      $full_path = realpath("$baseDir$subpath");

      if ($full_path && is_dir($full_path)) {
        $validated_paths[$alias] = $full_path;
      } else {
        $errors['INVALID_PATHS'][] = "Missing or invalid: [$alias] => '$subpath'";
      }
    }

    // Save valid resolved paths to ENV
    $_ENV['APP_BASE'] = json_encode(array_values($validated_paths), JSON_UNESCAPED_SLASHES);

    // Step 2: Build $common from validated aliases and original subpaths
    $common = [];
    foreach ($validated_paths as $alias => $full_path) {
      if (is_dir($full_path)) {
        // Use the original subpath (e.g., "projects/clients") if available
        $common[$alias] = rtrim($base_paths[$alias] ?? $alias, '/') . DIRECTORY_SEPARATOR;
      }
    }

    // Step 3: Unmapped or extra directories
    $dirnames = array_map('basename', array_filter(glob($baseDir . '*'), 'is_dir'));
    $mapped_names = array_keys($validated_paths);
    $unmapped_dirs = array_diff($dirnames, $mapped_names);

    if (!empty($unmapped_dirs))
      $errors['APP_BASE'] = 'Base directories are not in $base_paths: ' . implode(', ', $unmapped_dirs) . "\n";

    // Step 4: Final check + auto-create if missing (debug only)
    if (empty($common)) {
      $errors['APP_BASE'] = json_encode([], JSON_PRETTY_PRINT);
    } else {
      foreach ($common as $key => $subpath) {
        if (basename(__DIR__) === 'public' && $subpath === 'public' . DIRECTORY_SEPARATOR) {
          continue;
        }

        $fullPath = "$baseDir$subpath";

        if (!is_dir($fullPath) && defined('APP_DEBUG') && APP_DEBUG) {
          if (!@mkdir($fullPath, 0755, true)) {
            $errors['APP_BASE'][$key] = "$subpath could not be created.";
          }
        }

        $processedCommon[$key] = $subpath;
      }
    }

    // Optional: dump errors
    if (!empty($errors)) {
      // dd($errors); // Or your own error handler
    }

    // Get directories in the base path and filter them
    foreach (array_map('basename', array_filter(glob("{$basePath}{.env, .htaccess, .gitignore, LICENSE, *.md}", GLOB_BRACE), 'is_file')) as $filename) {
      if (!is_file($file = APP_PATH . $filename)) {
        if (@touch($file)) {
          if (is_file($source_file = APP_PATH . APP_BASE['data'] . 'source_code.json'))
            $source_file = json_decode(file_get_contents($source_file));
          if ($source_file) {
            if (!is_file($file = APP_PATH . 'public/.htaccess')) {
              if (@touch($file)) {
                file_put_contents($file, $source_file->{'public/.htaccess'});
              }
            }
            if (isset($source_file->{$filename}))
              switch ($filename) {
                case 'LICENSE':
                  if (APP_IS_ONLINE && check_http_status('http://www.wtfpl.net/txt/copying'))
                    file_put_contents($file, file_get_contents('http://www.wtfpl.net/txt/copying'));
                  else
                    file_put_contents($file, $source_file->{$filename});
                  break;
                default:

                  /*
                  if (!realpath($path = APP_PATH . 'docs/')) {
                    if (!mkdir($path, 0755, true))
                      $errors['DOCS'] = $path . ' does not exist';

                    if (!is_file($path . 'getting-started.md'))
                      if (@touch($path . 'getting-started.md'))   // https://github.com/auraphp/Aura.Session/docs/getting-started.md
                        file_put_contents($path . 'getting-started.md', <<<END
                  getting-started
                  END
                  );
                  }

                  if (!realpath($path = APP_PATH . APP_BASE['public'] . 'policies/')) {
                    if (!mkdir($path, 0755, true))
                      $errors['POLICIES'] = $path . ' does not exist';

                    if (!is_file($path . 'privacy-policy'))
                      if (@touch($path . 'privacy-policy'))
                        file_put_contents($path . 'privacy-policy', <<<END
                  Privacy Policy
                  END
                  );

                    if (!is_file($path . 'terms-of-use'))
                      if (@touch($path . 'terms-of-use'))
                        file_put_contents($path . 'terms-of-use', <<<END
                  Terms of Use
                  END
                  );

                    if (!is_file($path . 'legal/cookies'))
                      if (@touch($path . 'legal/cookies'))
                        file_put_contents($path . 'legal/cookies', <<<END
                  Cookies
                  END
                  );
                  }
                  */
                  file_put_contents($file, $source_file->{$filename});
                  break;
              }
            unset($source_file);
          }
        }
      }
    }

    // Define APP_BASE
    define('APP_BASE', $processedCommon);

    //dd(APP_BASE);
    //(defined('APP_PATH') && truepath(APP_PATH)) and $errors['APP_PATH'] = truepath(APP_PATH); // print('App Path: ' . APP_PATH . "\n" . "\t" . '$_SERVER[\'DOCUMENT_ROOT\'] => ' . $_SERVER['DOCUMENT_ROOT'] . "\n");

    defined('PATH_PUBLIC') or define(
      'PATH_PUBLIC',
      (defined('APP_PATH')
        ? APP_PATH
        : __DIR__ . DIRECTORY_SEPARATOR) . APP_BASE['public'] . str_replace(defined('APP_PATH')
        ? APP_PATH
        : __DIR__ . DIRECTORY_SEPARATOR, '', APP_BASE['public'] . basename(dirname($_SERVER['PHP_SELF'])) == 'public'
        ? basename($_SERVER['PHP_SELF'])
        : 'index.php')
    ); // APP_PATH . 'public' . DIRECTORY_SEPARATOR . 'index.php'

    //if (!defined('PHP_EXEC'))
    //define('PHP_EXEC', stripos(PHP_OS, 'LIN') === 0 ? '/usr/bin/php' : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin/psexec.exe -d C:\xampp\php\php.exe -f ');

    //if (APP_SELF != APP_PATH_SERVER || PHP_SAPI !== 'cli' && in_array(PATH_PUBLIC, get_included_files()) /*APP_SELF == PATH_PUBLIC*/)
    //  require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class.sockets.php';

    //dd(get_defined_constants(true)['user']);
    //error_log(var_export(get_required_files(), true));

    // if (APP_SELF !== APP_PATH_SERVER) {}

    //(APP_SELF !== APP_PATH_SERVER) and $socketInstance = Sockets::getInstance();
    //$socketInstance->handleClientRequest("composer self-update\n");
    // Resolve host to IP and check internet connection
/*
    $ip = resolve_host_to_ip('google.com');
    if (APP_SELF == PATH_PUBLIC && check_internet_connection($ip)) {
      define('APP_IS_ONLINE', true);
    } else {
      define('APP_NO_INTERNET_CONNECTION', "Not connected to the internet."); // APP_CONNECTIVITY
    }
*/
    /*
        if (isset($GLOBALS['runtime']['socket']) && is_resource($GLOBALS['runtime']['socket']) && !empty($GLOBALS['runtime']['socket']) && $_SERVER['REQUEST_METHOD'] != 'POST') {
          $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";

          // Send a message to the server
          $errors['server-2'] = 'Client request: ' . $message = "cmd: app connected\n";

          fwrite($GLOBALS['runtime']['socket'], $message);
          $output[] = trim($message); // . ': '
          // Read response from the server
          while (!feof($GLOBALS['runtime']['socket'])) {
              $response = fgets($GLOBALS['runtime']['socket'], 1024);
              $errors['server-3'] = "Server responce: $response\n";
              if (isset($output[end($output)])) $output[end($output)] .= $response = trim($response);
              else $output[1] = "$response\n";
              //if (!empty($response)) break;
          }
          // Close and reopen socket
          //fclose($socketInstance->getSocket());
          if (!empty($response))
            switch ((bool) $response) {
              case true:
                define('APP_IS_ONLINE', true);
                define('APP_NO_INTERNET_CONNECTION', "Network is connected to the Internet, via Server.");
                break;
              default:
                define('APP_NO_INTERNET_CONNECTION', "Not connected to the internet.");
                break;
            }
        } elseif (!isset($GLOBALS['runtime']['socket']) || !$GLOBALS['runtime']['socket']) {
          $ip = resolve_host_to_ip('google.com');
          if (check_internet_connection($ip)) {
            define('APP_IS_ONLINE', true);
          } else {
            define('APP_NO_INTERNET_CONNECTION', "Not connected to the internet.");
          }
        } else {
          $ip = resolve_host_to_ip('google.com');
          define('APP_NO_INTERNET_CONNECTION', "Failed to resolve host to IP=$ip");
        }
      */
    // Set connectivity error if not connected
    if (!defined('APP_IS_ONLINE'))
      if (is_file(APP_PATH . 'config/constants.env.php'))
        require_once APP_PATH . 'config/constants.env.php';
      else {
        die('APP_IS_ONLINE not defined and constants.env.php missing');
      } elseif (APP_IS_ONLINE) {
      $errors['APP_IS_ONLINE'] = 'APP Connect(ed): ' . (var_export(APP_IS_ONLINE, true) === true ? 'false (offline)' : 'true (online)') . "\n";
    } elseif (!APP_NO_INTERNET_CONNECTION) {

      $errors['APP_NO_INTERNET_CONNECTION'] = 'APP Connect(ed): ' . (var_export(APP_IS_ONLINE, true) === true ? 'false (offline)' : 'true (online)') . "\n";
    }

    break;
}



//die(var_dump(APP_URL));

/*
!is_array(APP_BASE) ?
  substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1) == '/' 
    and define('APP_URL', 'http' . (defined('APP_HTTPS') ? 's' : '') . '://' . APP_DOMAIN . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1)) :
  define('APP_URL', [
    'scheme' => 'http' . (defined('APP_HTTPS') && APP_HTTPS ? 's' : ''), 
    'user' => (!isset($_SERVER['PHP_AUTH_USER']) ? NULL : $_SERVER['PHP_AUTH_USER']),
    'pass' => (!isset($_SERVER['PHP_AUTH_PW']) ? NULL : $_SERVER['PHP_AUTH_PW']),
    'host' => APP_DOMAIN,
    'port' => (int) ($_SERVER['SERVER_PORT'] ?? 80),
    'path' => $_SERVER['REQUEST_URI'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1), // https://stackoverflow.com/questions/7921065/manipulate-url-serverrequest-uri
    'query' => $_SERVER['QUERY_STRING'] ?? '',
    'fragment' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_FRAGMENT),
  ]);
*/
/* var_dump(parse_url(APP_URL));
var_dump(parse_url(APP_URL, PHP_URL_SCHEME));
var_dump(parse_url(APP_URL, PHP_URL_USER));
var_dump(parse_url(APP_URL, PHP_URL_PASS));
var_dump(parse_url(APP_URL, PHP_URL_HOST));
var_dump(parse_url(APP_URL, PHP_URL_PORT));
var_dump(parse_url(APP_URL, PHP_URL_PATH));
var_dump(parse_url(APP_URL, PHP_URL_QUERY));
var_dump(parse_url(APP_URL, PHP_URL_FRAGMENT)); */

// Define APP_BASE_URL
//is_array(APP_URL) ? define('APP_URL_BASE', APP_URL) :
//  define('APP_URL_BASE', APP_URL['scheme'] . '://' . APP_URL['host'] . APP_URL['path']);

// Define APP_BASE_URI
/*
define('APP_QUERY', !empty(parse_url($_SERVER['REQUEST_URI'] ?? '')['query']) ? (parse_str(parse_url($_SERVER['REQUEST_URI'])['query'], $query) ? [] : $query) : []);

!is_array(APP_URL)
  or define(
    'APP_URI',   // BASEURL
    preg_replace(
      '!([^:])(//)!',
      "$1/",
      str_replace(
        '\\',
        '/',
        htmlspecialchars(APP_URL['scheme'] . '://' . (isset($_SERVER['PHP_AUTH_USER']) ? APP_URL['user'] . PATH_SEPARATOR . APP_URL['pass'] . '@' : '') . APP_URL['host'] . (APP_URL['port'] !== '80' ? PATH_SEPARATOR . APP_URL['port'] : '') . APP_URL['path'] . (!basename($_SERVER["SCRIPT_NAME"]) ? '' : basename($_SERVER["SCRIPT_NAME"])) . (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '')) // dirname($_SERVER['PHP_SELF'])  dirname($_SERVER['REQUEST_URI'])
      )
    )
  );
*/

//dd(get_defined_constants(true)['user']);

/*
require_once('functions.php');

require_once('debug.php');

require_once('session.php');
*/
/*
switch(get_included_files()[0]) {
  case APP_PATH . 'assets' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'jquery.tinymce-config.js.php':

  break;

  case APP_PATH . 'index.php':

  break;

  case APP_PATH . 'install.php':

  break;

  case APP_PATH . 'auth.php':

  break;

  case APP_PATH . 'logout.php':

  break;

  default:

    //var_dump(get_included_files());
    //header('Location: ' . APP_BASE_URL);
    //exit;

  break;
}
*/

//if (basename(get_included_files()[0]) == 'jquery.tinymce-config.js.php') {
//exit;
//} else if (basename($_SERVER["SCRIPT_FILENAME"]) !== 'index.php') {
//  header('Location: ' . APP_BASE_URL . basename($_SERVER["SCRIPT_FILENAME"]));
//  exit;
//}

//var_dump($_REQUEST);

//$str_1 = htmlentities($_REQUEST['history']);

/*
$if = function($condition, $true, $false) { $condition ? header('Location: ' . preg_replace("/^http:/i", "https:", $baseurl)) : ''; };

echo <<<TEXT

   {$if(!isset($_SERVER['HTTPS']) && $use_ssl == TRUE, 'yes', 'no')}
   content

TEXT;
*/

//Ternary operator vs Null coalescing operator in PHP 
// ($condition) ?? "NULL";

//exit;

//print <<<EOD
//{$outputVar}
//<h3>This is not SSL</h3>
//EOD;
