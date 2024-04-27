<?php
// This may not be a good idea...
//if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'config.php')) // is_file('config/config.php')) 
//  require_once($path);

// Enable output buffering
//ini_set('output_buffering', 'On');

// Increase the maximum execution time to 60 seconds
//ini_set('max_execution_time', 60);

/* This code sets up some basic configuration constants for a PHP application. */

require_once 'functions.php';

define('APP_CONNECTIVITY', check_ping(/*'8.8.8.8'*/));

define('APP_START', microtime(true));

(defined('APP_START') && !is_float(APP_START)) and $errors['APP_START'] = APP_START;

define('APP_DEBUG', isset($_GET['debug']) ? TRUE : FALSE);

//echo 'Checking Constants: ' . "\n\n";

// Application configuration

define('APP_VERSION',   number_format(1.0, 1) . '.0');
(version_compare(APP_VERSION, '1.0.0', '>=') == 0)
  and $errors['APP_VERSION'] = 'APP_VERSION is not a valid version (' . APP_VERSION . ').';

const APP_NAME = 'Dashboard';
(!is_string(APP_NAME))
  and $errors['APP_NAME'] = 'APP_NAME is not a string => ' . var_export(APP_NAME, true); // print('Name: ' . APP_NAME  . ' v' . APP_VERSION . "\n");

//define('APP_HOURS', ['open' => '08:00', 'closed' => '17:00']);
//if (defined('APP_HOURS')) echo 'Hours of Operation: ' . APP_HOURS['open'] . ' -> ' . APP_HOURS['closed']  . "\n";

define('APP_DOMAIN', $_SERVER['HTTP_HOST'] ?? isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');
(!is_string(APP_DOMAIN)) and $errors['APP_DOMAIN'] = 'APP_DOMAIN is not valid. (' . APP_DOMAIN . ')' . "\n";

(isset($_SERVER['HTTPS']) === true && $_SERVER['HTTPS'] == 'on') // strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'
  and define('APP_HTTPS', TRUE);
(defined('APP_HTTPS') && APP_HTTPS) and $errors['APP_HTTPS'] = (bool) var_export(APP_HTTPS, APP_HTTPS); // print("\t" . 'Http(S)://' . APP_DOMAIN . '/' . '... ' .  var_export(defined('APP_HTTPS'), true) . "\n");

define('APP_WWW', 'http' . (defined('APP_HTTPS') ? 's':'') . '://' . APP_DOMAIN . parse_url(isset($_SERVER['SERVER_NAME']) ? $_SERVER['REQUEST_URI'] : '' , PHP_URL_PATH));

define('APP_TIMEOUT',   strtotime("1970-01-01 08:00:00GMT"));
(defined('APP_TIMEOUT') && !is_int(APP_TIMEOUT)) and $errors['APP_TIMEOUT'] = APP_TIMEOUT; // print('Timeout: ' . APP_TIMEOUT . "\n");

(!empty($login = [/*'UNAME' => '', 'PWORD' => ''*/]))
and define('APP_LOGIN', $login);
(defined('APP_LOGIN') && is_array(APP_LOGIN)) and (empty(APP_LOGIN['UNAME']) || empty(APP_LOGIN['PWORD']) ?: $errors['APP_LOGIN'] = APP_LOGIN); //print('Auth: ' . "\n\t" . '(User => ' . APP_LOGIN['UNAME'] . ' Password => ' . APP_LOGIN['PWORD'] . ")\n");

// absolute pathname 
switch (basename(__DIR__)) {
  case 'config':
    define('APP_BASE', [ // https://stackoverflow.com/questions/8037266/get-the-url-of-a-file-included-by-php
      'config' => 'config' . DIRECTORY_SEPARATOR,
      'database' => 'database' . DIRECTORY_SEPARATOR,
      'public' => 'public' . DIRECTORY_SEPARATOR,
      'resources' => 'resources' . DIRECTORY_SEPARATOR,
      'projects' => 'projects' . DIRECTORY_SEPARATOR,
      'src' => 'src' . DIRECTORY_SEPARATOR,
      'var' => 'var' . DIRECTORY_SEPARATOR,
      'vendor' => 'vendor' . DIRECTORY_SEPARATOR,
      //'tmp' => 'var' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
      //'export' =>  'var' . DIRECTORY_SEPARATOR . 'export' . DIRECTORY_SEPARATOR,
      //'session' => 'var' . DIRECTORY_SEPARATOR . 'session' . DIRECTORY_SEPARATOR,
    ]);
    break;
  default:
    define('APP_BASE', [
      'config' => 'config' . DIRECTORY_SEPARATOR,
      'database' => 'database' . DIRECTORY_SEPARATOR,
      //'public' => 'public' . DIRECTORY_SEPARATOR,   // basename(APP_PATH) could be composer||public[_html]/
      'resources' => 'resources' . DIRECTORY_SEPARATOR,
      'var' => 'var' . DIRECTORY_SEPARATOR,
      'vendor' => 'vendor' . DIRECTORY_SEPARATOR,
    ]);
    break;
}

//define('APP_ENV', 'production');

if (defined('APP_DOMAIN') && !in_array(APP_DOMAIN, [/*'localhost',*/ '127.0.0.1', '::1'])) {
/* if (!is_file($file = APP_PATH . '.env') && @touch($file)) file_put_contents($file, "DB_UNAME=\nDB_PWORD="); */
//  defined('APP_ENV') or define('APP_ENV', 'production');
} else {
/* if (!is_file($file = APP_PATH . '.env') && @touch($file)) file_put_contents($file, "DB_UNAME=\nDB_PWORD="); */
//  defined('APP_ENV') or define('APP_ENV', 'development'); // development
} // APP_DEV |  APP_PROD

(defined('APP_ENV') && !is_string(APP_ENV)) and $errors['APP_ENV'] = 'App Env: ' . APP_ENV; // print('App Env: ' . APP_ENV . "\n");
/* if (APP_ENV == 'development') { 
  if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'constants_backup.php')) // is_file('config/constants.php')) 
    require_once($path);

  if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'constants_client-project.php')) // is_file('config/constants.php')) 
    require_once($path);
} */

//(defined('APP_PATH') && truepath(APP_PATH)) and $errors['APP_PATH'] = truepath(APP_PATH); // print('App Path: ' . APP_PATH . "\n" . "\t" . '$_SERVER[\'DOCUMENT_ROOT\'] => ' . $_SERVER['DOCUMENT_ROOT'] . "\n");

if (defined('APP_BASE'))
  if (empty(APP_BASE))
    $errors['APP_BASE'] = json_encode(array_keys(APP_BASE)); // print('App Base: ' .  . "\n");
  else {
    foreach (APP_BASE as $key => $path) { // << -- This only works when debug=true
      if (!is_dir(APP_PATH . $path) && APP_DEBUG)
        (@!mkdir(APP_PATH . $path, 0755, true) ?: $errors['APP_BASE'][$key] = "$path could not be created." );
    //else $errors['APP_BASE'][$key] = $path;
    }
  }

define('APP_PUBLIC',  str_replace(APP_PATH, '', basename(dirname(APP_SELF)) == 'public' ? APP_PATH . APP_BASE['public'] . basename(APP_SELF) : basename(APP_SELF)) );

//var_dump(APP_PATH . basename(dirname(__DIR__, 2)) . '/' . basename(dirname(__DIR__, 1)));

!isset($_SERVER['REQUEST_URI'])
  and $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 0) . ((isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "") AND '?' . $_SERVER['QUERY_STRING']);

// substr( str_replace('\\', '/', __FILE__), strlen($_SERVER['DOCUMENT_ROOT']), strrpos(str_replace('\\', '/', __FILE__), '/') - strlen($_SERVER['DOCUMENT_ROOT']) + 1 )

!is_array(APP_BASE) ?
  substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1) == '/' // $_SERVER['DOCUMENT_ROOT']
    and define('APP_URL', 'http' . (defined('APP_HTTPS') ? 's':'') . '://' . APP_DOMAIN . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1)) :
  define('APP_URL', [
    'scheme' => 'http' . (defined('APP_HTTPS') && APP_HTTPS ? 's': ''), // ($_SERVER['HTTPS'] == 'on', (isset($_SERVER['HTTPS']) === true ? 'https' : 'http')
    'host' => APP_DOMAIN,
    'port' => (int) ($_SERVER['SERVER_PORT'] ?? 80),
    /* https://www.php.net/manual/en/features.http-auth.php */
    'user' => (!isset($_SERVER['PHP_AUTH_USER']) ? NULL : $_SERVER['PHP_AUTH_USER']),
    'pass' => (!isset($_SERVER['PHP_AUTH_PW']) ? NULL : $_SERVER['PHP_AUTH_PW']),
    'path' => substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1), // https://stackoverflow.com/questions/7921065/manipulate-url-serverrequest-uri
    'query' => ($_SERVER['QUERY_STRING'] ?? ''), // array( key($_REQUEST) => current($_REQUEST) )
    'fregment' => '',
  ]);

// APP_BASE_URL
!is_array(APP_URL) ? define('APP_URL_BASE', APP_URL) :
  define('APP_URL_BASE', APP_URL['scheme'] . '://' . APP_URL['host'] . APP_URL['path']);

// APP_BASE_URI
define('APP_URL_PATH', !is_array(APP_URL) ? substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1) : APP_URL['path']);

define('APP_QUERY', !empty(parse_url($_SERVER['REQUEST_URI'])['query']) ? (parse_str(parse_url($_SERVER['REQUEST_URI'])['query'], $query) ? [] : $query) : []);

!is_array(APP_URL)
  or define('APP_URI',   // BASEURL
    preg_replace('!([^:])(//)!', "$1/",
      str_replace('\\', '/',
        htmlspecialchars(APP_URL['scheme'] . '://' . (isset($_SERVER['PHP_AUTH_USER']) ? APP_URL['user'] . ':' . APP_URL['pass'] . '@' : '') . APP_URL['host'] . (APP_URL['port'] !== '80' ? ':' . APP_URL['port'] : '') . APP_URL['path'] . (!basename($_SERVER["SCRIPT_NAME"]) ? '' : basename($_SERVER["SCRIPT_NAME"])) . (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '')) // dirname($_SERVER['PHP_SELF'])  dirname($_SERVER['REQUEST_URI'])
      )
    )
  );

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

  case APP_PATH . 'login.php':

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
//die('hello world');
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



//die();
 
//print <<<EOD
//{$outputVar}
//<h3>This is not SSL</h3>
//EOD;
