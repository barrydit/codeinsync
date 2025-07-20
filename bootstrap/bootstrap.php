<?php

use App\Core\Registry;

defined('BASE_PATH') and
  define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR) and
  is_string(BASE_PATH) ?: $errors['BASE_PATH'] = "BASE_PATH is not a valid string value.\n"; // APP_PATH
// Define APP_PATH constant
!defined('APP_PATH') && defined('BASE_PATH') and
  define('APP_PATH', realpath(BASE_PATH /*. '..' . DIRECTORY_SEPARATOR*/) . DIRECTORY_SEPARATOR);
// Define base paths

defined('CONFIG_PATH') or define('CONFIG_PATH', BASE_PATH . 'config' . DIRECTORY_SEPARATOR);

require_once APP_PATH . 'autoload.php';

require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'functions.php';

// Unified app param
$app = $_POST['app'] ?? $_GET['app'] ?? null;
$cmd = $_POST['cmd'] ?? null;

// Map app names to handler files
$routes = [
  'composer' => APP_PATH . 'api/composer.php',
  'git' => APP_PATH . 'api/git.php',
  'npm' => APP_PATH . 'api/npm.php',
];
//die(var_dump(get_required_files()));
// Early dispatch for known app via GET/POST param
if ($app && isset($routes[$app])) {
  require_once $routes[$app];
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
  if (!defined('APP_BOOTSTRAPPED')) {
    define('APP_BOOTSTRAPPED', true);
    // Load constants and initialize the app
    //require_once APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.cli.php';
  }

if (APP_CONTEXT === 'socket') {

  Registry::set('errors', []);
  Registry::set('logger', new Logger());
  // Load socket bootstrap if in socket context
  require_once APP_PATH . 'bootstrap/bootstrap.sockets.php';

  $GLOBALS['runtime'] = ($_SERVER['REQUEST_METHOD'] === 'GET') ? [
    'socket' => Sockets::getInstance(Registry::get('logger')), //  fsockopen('localhost', 9000),
    'pid' => getmypid(),
    //'client_id' => $clientId,
    //'errors' => [],
  ] : [
    'socket' => fsockopen('localhost', 9000),
    'pid' => getmypid(),
    //'client_id' => $clientId,
    //'errors' => [],
  ];
} elseif (APP_CONTEXT === 'php') {


} elseif (APP_CONTEXT !== 'cli') {

  // Dispatch by command pattern
  $commandRoutes = [
    '/^git\s+/i' => APP_PATH . 'config/git.php',
    '/^composer\s+/i' => APP_PATH . 'api/composer.php',
    '/^npm\s+/i' => APP_PATH . 'config/npm.php',
    '/^chdir\s+/i' => APP_PATH . 'app/directory.php',
    // Optional future handlers:
    // '/^ruby\s+/i'     => APP_PATH . 'config/runtime/ruby.php',
    // '/^go\s+/i'       => APP_PATH . 'config/runtime/go.php',
    // '/^java\s+/i'     => APP_PATH . 'config/runtime/java.php',
    // '/^csharp\s+/i'   => APP_PATH . 'config/runtime/csharp.php',
  ];

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cmd) {
    foreach ($commandRoutes as $pattern => $handlerFile) {
      if (preg_match($pattern, $cmd, $match)) {
        if (is_file($handlerFile)) {
          require_once $handlerFile;
          break; // exit;
        }
      }
    }
  }

  if (!isset($_GET['path'])) {
    // No match found
    //http_response_code(404);
    //echo json_encode(['error' => 'Invalid or missing app or command']);
    //exit;
  }




  // CONFIG_PATH, PUBLIC_PATH, STORAGE_PATH, VENDOR_PATH, VIEW_PATH, CACHE_PATH, LOG_PATH, TEMP_PATH, UPLOAD_PATH, ASSETS_PATH, APP_PATH, BASE_PATH, ROOT_PATH, SRC_PATH, TEST_PATH, WWW_PATH
/*
!defined('DOMAIN_EXPR') and 
  // const DOMAIN_EXPR = 'string only/non-block/ternary'; 
  define('DOMAIN_EXPR', $_ENV['SHELL']['EXPR_DOMAIN'] ?? '/(?:[a-z]+\:\/\/)?(?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*)?/i') and is_string(DOMAIN_EXPR) ? '' : $errors['DOMAIN_EXPR'] = 'DOMAIN_EXPR is not a valid string value.'; // /(?:\.(?:([-a-z0-9]+){1,}?)?)?\.[a-z]{2,6}$/';
*/

  /*
  if (!defined('APP_ROOT')) {
    $path = !isset($_GET['client']) ? (!isset($_GET['project']) ? '' : APP_BASE['projects'] . $_GET['project']) : APP_BASE['clients'] . $_GET['client'] . DIRECTORY_SEPARATOR . (isset($_GET['domain']) && $_GET['domain'] != '' ? $_GET['domain'] : '') . DIRECTORY_SEPARATOR; /* ($_GET['path'] . '/' ?? '')*/
  //die($path);
//is_dir(APP_PATH . $_GET['path'])
/*  !$path || !is_dir(APP_PATH . $path) ?:  
    define('APP_ROOT', !empty(realpath(APP_PATH . ($path = rtrim($path, DIRECTORY_SEPARATOR)) ) && $path != '') ? (string) $path . DIRECTORY_SEPARATOR : '');  // basename() does not like null
}*/

  // Define project and client folders based on GET parameters

  $projectFolder = 'projects' . DIRECTORY_SEPARATOR . 'internal' . DIRECTORY_SEPARATOR . ($_GET['project'] ?? '');
  $projectPath = __DIR__ . DIRECTORY_SEPARATOR . $projectFolder;

  $clientFolder = 'projects' . DIRECTORY_SEPARATOR . 'clients' . DIRECTORY_SEPARATOR . ($_GET['client'] ?? '');
  $clientPath = __DIR__ . DIRECTORY_SEPARATOR . $clientFolder;

  /**
   * Resolve domain from available directories or fallback to the client folder.
   */
  function resolveProject($dirs, $requestedProject = null)
  {
    // Match requested domain to available directories
    if ($requestedProject)
      foreach ($dirs as $dir) {
        if (basename($dir) === $requestedProject) {
          return basename($dir);
        }
      }

    // If no domain requested and exactly one directory exists, use it
    if (count($dirs) === 1)
      return basename(reset($dirs));

    // No valid domain found
    return null;
  }


  /**
   * Resolve domain from available directories or fallback to the client folder.
   */
  function resolveDomain($dirs, $requestedDomain = null)
  {
    // Match requested domain to available directories
    if ($requestedDomain)
      foreach ($dirs as $dir) {
        if (basename($dir) === $requestedDomain)
          return basename($dir);
      }


    // If no domain requested and exactly one directory exists, use it
    if (count($dirs) === 1)
      return $_GET['domain'] = basename(reset($dirs));

    // No valid domain found
    return null;
  }

  /**
   * Resolve the client folder path if no domain is provided.
   */
  function resolveClient($clientFolder)
  {
    // Check if the provided client folder exists
    if (is_dir(__DIR__ . DIRECTORY_SEPARATOR . $clientFolder))
      return $clientFolder . DIRECTORY_SEPARATOR;


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
  defined('APP_ROOT') || define('APP_ROOT', !is_dir(APP_PATH . $path) ?: $path);

  //die(APP_ROOT);
// Check if the config file exists in various locations based on the current working directory
  $path = null;

  // Determine the path based on current location and check if file exists
  switch (basename(__DIR__)) { // getcwd()
    case 'public':
      chdir(dirname(__DIR__));
      break;
  }

  require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.env.php'; // 'constants.php'; // Global constants
  require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.paths.php';
  require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.runtime.php';
  require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.url.php';
  require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.app.php';
  if ($GLOBALS['runtime']['socket'] ?? false) {
    if (APP_CONTEXT === 'socket' && is_file($socketConstants = CONFIG_PATH . 'constants.socket.php')) {
      require_once $socketConstants;
    }

    require_once APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.sockets.php';
  }
  // Check if the config file exists in the expected location
// If the config file is not found, it will die with a var_dump of the path
  if ($path = realpath(APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php'))
    require_once $path; // Load the config file if found  project settings
// elseif (is_file('config.php')) $path = $config;
  else
    die(var_dump($path));


  require_once 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'php.php'; // environment-level PHP config
}
//dd(get_required_files());
// 0.257 seconds

//require_once 'config' . DIRECTORY_SEPARATOR . 'autoload.php'; // Autoload configuration
/*
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_functions.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_classes.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_interfaces.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_traits.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_exceptions.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_constants.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_variables.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_globals.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_paths.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_config.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_helpers.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_services.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_commands.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_events.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_middlewares.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_routes.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_views.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_templates.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_assets.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_scripts.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_styles.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_migrations.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_seeds.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_tests.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_settings.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_translations.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_languages.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_locales.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_packages.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_modules.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_plugins.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_extensions.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_paths.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_config.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_helpers.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_services.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_commands.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_events.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_middlewares.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_routes.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_views.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_templates.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_assets.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_scripts.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_styles.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_migrations.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_seeds.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_tests.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_settings.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_translations.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_languages.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_locales.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_packages.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_modules.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_plugins.php';
require_once 'config' . DIRECTORY_SEPARATOR . 'autoload_system_extensions.php';
*/

//require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'; // Composer autoload


/*
if (isset($_GET['debug'])) 
  require_once 'public' . DIRECTORY_SEPARATOR . 'index.php';
else
  die(header('Location: public' . DIRECTORY_SEPARATOR . 'index.php'));
*/
