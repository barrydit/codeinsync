<?php
declare(strict_types=1); // First Line Only!
error_reporting(E_ALL/*E_STRICT |*/);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
ini_set('error_log', (is_dir($path = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config') ? dirname($path, 1) . DIRECTORY_SEPARATOR . 'error_log' : 'error_log'));
ini_set('log_errors', true);

if (count(get_included_files()) == ((version_compare(PHP_VERSION, '5.0.0', '>=')) ? 1:0 )):
  exit('Direct access is not allowed.');
endif;

//ini_set("include_path", "src"); // PATH_SEPARATOR ;:

//die(var_dump($_SERVER['PHP_SELF'] . DIRECTORY_SEPARATOR . basename($_SERVER['PHP_SELF'])));


//$path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR ) . 'functions.php');

// (basename(__DIR__) != 'config' ?

if ($path = (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'functions.php') ? __DIR__ . DIRECTORY_SEPARATOR . 'functions.php' : (is_file('config/functions.php') ? 'config/functions.php' : 'functions.php'))) // is_file('config/constants.php')) 
  require_once($path);
else die(var_dump($path . ' does not exist.'));

//!is_file( dirname($_SERVER['PHP_SELF']) . basename($_SERVER['PHP_SELF']) ?? __FILE__) // (!empty(get_included_files()) ? get_included_files()[0] : __FILE__)
!defined('APP_SELF') and define('APP_SELF', get_included_files()[0] ?? __FILE__); // get_included_files()[0] | str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']) | $_SERVER['PHP_SELF']
  
!defined('APP_PATH') and define('APP_PATH', implode(DIRECTORY_SEPARATOR, array_intersect_assoc(
  explode(DIRECTORY_SEPARATOR, __DIR__),
  explode(DIRECTORY_SEPARATOR, dirname(APP_SELF))
)) . DIRECTORY_SEPARATOR);

define('APP_CONFIG',  str_replace(APP_PATH, '', basename(dirname(__FILE__))) == 'config' ? __FILE__ : basename(__FILE__));

date_default_timezone_set('America/Vancouver');

// Enable output buffering
ini_set('output_buffering', 'On');

$errors = NULL;

$ob_content = NULL;
//var_dump(dirname(APP_SELF) . ' == ' . __DIR__);
//dd(APP_PATH  . '  ' .  __DIR__);

if (basename(dirname(APP_SELF)) == 'public') {
  if (!is_file('.htaccess'))
    if (@touch('.htaccess'))
      file_put_contents('.htaccess', <<<END
RewriteEngine On

# Redirect resource calls from /assets/ to /resources/
RewriteRule ^resources/(.*)$ ../resources/$1 [L]
END
);
} elseif (dirname(APP_SELF) == __DIR__) {
  if (!is_file(APP_PATH . '.htaccess'))
    if (@touch(APP_PATH . '.htaccess'))
      file_put_contents(APP_PATH. '.htaccess', <<<END
RewriteEngine On

# Check if the request is for an existing file in the resources/ directory
RewriteCond %{DOCUMENT_ROOT}/resources%{REQUEST_URI} -f
RewriteRule ^(.*)$ ./resources/$1 [L]

# Redirect all requests to index.php (assuming a typical front controller pattern)
#RewriteCond %{REQUEST_FILENAME} !-f
#RewriteRule ^ index.php [L]
END
);
  
  if (!is_file(APP_PATH . '.gitignore'))
    if (@touch(APP_PATH . '.gitignore'))
      file_put_contents(APP_PATH . '.gitignore', <<<END
/var
.env.*
error_log
composer.phar
composer-setup.php
END
);

}
ob_start();
// write content

// defined('PHP_ZTS') and $errors['PHP_ZTS'] = 'PHP was built with ZTS enabled.';

//echo APP_SELF;

//dd(getRelativePath(APP_SELF, '/public'));

// Check if the directory structure is /public_html/

if (($dir = basename(APP_PATH)) != 'config') {
  if (in_array($dir, ['public', 'public_html']))
    chdir('../');
  //dd((__DIR__ . DIRECTORY_SEPARATOR . '*.php'));
    //if (is_dir('config')) {}
  $previousFilename = '';
  $dirs = glob(__DIR__ . DIRECTORY_SEPARATOR . '*.php');

  usort($dirs, function ($a, $b) {
      // Define your sorting criteria here
    if (basename($a) === 'composer-setup.php') {
        return 1; // $a comes after $b
    } elseif (basename($b) === 'composer-setup.php') {
        return -1; // $a comes before $b
    } else {
        return strcmp(basename($a), basename($b)); // Compare other filenames alphabetically
    }
  });

  foreach ($dirs as $includeFile) {
    if (basename($includeFile) === 'composer-setup.php') continue;

    //echo basename($includeFile) . "<br />\n"; 
    
    if (in_array($includeFile, get_required_files())) continue; // $includeFile == __FILE__

    if (!file_exists($includeFile)) {
      error_log("Failed to load a necessary file: " . $includeFile . PHP_EOL);
      break;
    }

    $currentFilename = substr(basename($includeFile), 0, -4);
    
      //$pattern = '/^' . preg_quote($previousFilename, '/')  . /*_[a-zA-Z0-9-]*/'(_\.+)?\.php$/'; // preg_match($pattern, $currentFilename)

    if (!empty($previousFilename) && strpos($currentFilename, $previousFilename) !== false) continue;

    require_once $includeFile;

    $previousFilename = $currentFilename;
  }
} elseif (basename(dirname(APP_SELF)) == 'public_html') { // basename(__DIR__) == 'public_html'
  $errors['APP_PUBLIC'] = 'The `public_html` scenario was detected.' . "\n";
  
  if (is_dir(dirname(APP_SELF, 2) . '/config')) {
    $errors['APP_PUBLIC'] .= "\t" . dirname(APP_SELF, 2) . '/config/*' . ' was found. This is not generally safe-scenario.'; 
  }

  chdir(dirname(__DIR__, 1));  //dd(getcwd());
    // It is under the public_html scenario
    // Perform actions or logic specific to the public_html directory
    // For example:
    // include '/home/user_123/public_html/config.php';
} elseif (basename(dirname(APP_SELF)) == 'public') {    // strpos(APP_SELF, '/public/') !== false

  //dd(APP_SELF . '   ' . __DIR__);

  if (basename(get_required_files()[0]) !== 'release-notes.php')
    if (is_dir('config')) {
      $previousFilename = ''; // Initialize the previous filename variable

//$files = glob(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '*.php');
//$files = array_merge($files, glob(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '**' . DIRECTORY_SEPARATOR . '*.php'));

//sort($files);

      foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . '*.php') as $includeFile) {
        //echo $includeFile . "<br />\n";

        if (in_array($includeFile, get_required_files())) continue; // $includeFile == __FILE__

        if (!file_exists($includeFile)) {
          error_log("Failed to load a necessary file: " . $includeFile . PHP_EOL);
          break;
        }

        $currentFilename = substr(basename($includeFile), 0, -4);
    
        //$pattern = '/^' . preg_quote($previousFilename, '/')  . /*_[a-zA-Z0-9-]*/'(_\.+)?\.php$/'; // preg_match($pattern, $currentFilename)

        if (!empty($previousFilename) && strpos($currentFilename, $previousFilename) !== false) {
          continue;
        }

        require_once $includeFile;

        $previousFilename = $currentFilename;
      }

    } else if (!in_array($path = realpath('config.php'), get_required_files())) {
      //die($path . ' test');
      require_once($path);
    }

    if (defined('APP_PROJECT')) require_once('public/install.php');
}
/*
if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR ) . 'constants.php')) // is_file('config/constants.php')) 
  if (!in_array($path, get_required_files()))
    require_once($path);
*/

//dd(get_defined_constants(true)['user']); // true

if (dirname(APP_SELF) == __DIR__) {
  if (dirname(APP_CONFIG) != 'config')
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
        file_put_contents(APP_PATH . 'index.php', '<?php require_once(\'public/index.php\');');

unlink(__FILE__);
END
);
      define('APP_INSTALL', true);
    }

}

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

define('APP_ERRORS', $errors ?? (($error = ob_get_contents()) == null ? null : 'ob_get_contents() maybe populated/defined/errors... error=' . $error ));
ob_end_clean();

//var_dump(APP_ERRORS);

if (defined('APP_ERRORS') && APP_ERRORS && defined('APP_DEBUG') && APP_DEBUG == false) // is_array($ob_content)
  dd(APP_ERRORS); // get_defined_constants(true)['user']'

//(defined('APP_DEBUG') && APP_DEBUG) and $errors['APP_DEBUG'] = (bool) var_export(APP_DEBUG, APP_DEBUG); // print('Debug (Mode): ' . var_export(APP_DEBUG, true) . "\n");

/* function shutdown()
{
	global $pdo; //$myiconnect;
    // This is our shutdown function, in 
    // here we can do any last operations
    // before the script is complete.
	//mysqli_close($myiconnect);

  unset($pdo);
} */

register_shutdown_function( // 'shutdown'
  function() {
    //global $pdo, $session_save;

    //isset($session_save) and $session_save();

    if (defined('APP_INSTALL') && APP_INSTALL && $path = APP_PATH . 'install.php')// is_file('config/constants.php')) 
        require_once($path);
    //else if (!is_file($path) && !in_array($path, get_required_files()))
    //    die(var_dump($path . ' was not found. file=install.php'));

    defined('APP_END') or define('APP_END', microtime(true));
    //include('checksum_md5.php'); // your_logger(get_included_files());
    //unset($pdo);
  }
);
