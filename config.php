<?php
declare(strict_types=1); // First Line Only!

error_reporting(E_ALL/*E_STRICT |*/);

date_default_timezone_set('America/Vancouver');

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
ini_set('error_log', (is_dir($path = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config') ? dirname($path, 1) . DIRECTORY_SEPARATOR . 'error_log' : 'error_log'));
ini_set('log_errors', true);
ini_set('xdebug.remote_enable', '0');
ini_set('xdebug.profiler_enable', '0');
ini_set('xdebug.default_enable', '0');

putenv('XDEBUG_MODE=' . 'off');

// Enable output buffering
ini_set('output_buffering', 'On');

ini_set("include_path", "src"); // PATH_SEPARATOR ;:

if (count(get_included_files()) == ((version_compare(PHP_VERSION, '5.0.0', '>=')) ? 1:0 )):
  exit('Direct access is not allowed.');
endif;

$errors = []; // (object)

  // file_get_contents($path)

//die(var_dump($_SERVER['PHP_SELF'] . DIRECTORY_SEPARATOR . basename($_SERVER['PHP_SELF'])));

//$path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR ) . 'functions.php');

// (basename(__DIR__) != 'config' ?

$links = array_filter(glob(__DIR__ . DIRECTORY_SEPARATOR . 'classes/*.php'), 'is_file');

while ($link = array_shift($links)) {
  if ($path = realpath($link))
    require_once($path);
  else die(var_dump(basename($path) . ' was not found. file=classes/' . basename($path)));
}

if ($path = (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'functions.php') ? __DIR__ . DIRECTORY_SEPARATOR . 'functions.php' : (is_file('config/functions.php') ? 'config/functions.php' : 'functions.php'))) // is_file('config/constants.php')) 
  require_once($path);
else die(var_dump($path . ' was not found. file=functions.php'));

!function_exists('dd') and $errors['FUNCTIONS'] = 'functions.php failed to load. Therefor dd() does not exist.';
//else dd('test');

//!is_file( dirname($_SERVER['PHP_SELF']) . basename($_SERVER['PHP_SELF']) ?? __FILE__) // (!empty(get_included_files()) ? get_included_files()[0] : __FILE__)
!defined('APP_SELF') and define('APP_SELF', get_included_files()[0] ?? __FILE__); // get_included_files()[0] | str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']) | $_SERVER['PHP_SELF']

//var_dump(get_defined_constants(true)['user']);

!defined('APP_ROOT') and define('APP_ROOT', dirname(APP_SELF) . DIRECTORY_SEPARATOR);  // Directory of this script
  
!defined('APP_PATH') and define('APP_PATH', implode(DIRECTORY_SEPARATOR, array_intersect_assoc(
  explode(DIRECTORY_SEPARATOR, __DIR__),
  explode(DIRECTORY_SEPARATOR, dirname(APP_SELF))
)) . DIRECTORY_SEPARATOR);

!defined('APP_CONFIG') and define('APP_CONFIG',  str_replace(APP_PATH, '', basename(dirname(__FILE__))) == 'config' ? __FILE__ : __FILE__);

//$errors->{'CONFIG'} = 'OK';


$ob_content = NULL;
//var_dump(dirname(APP_SELF) . ' == ' . __DIR__);
//dd(APP_PATH  . '  ' .  __DIR__);

if (!realpath($path = 'CHANGELOG.md')) {
  if (!is_file($path))
    if (@touch($path))
      file_put_contents($path, <<<END
CHANGELOG
END
);
}

if (!realpath($path = 'CONTRIBUTING.md')) {
  if (!is_file($path))
    if (@touch($path))
      file_put_contents($path, <<<END
CONTRIBUTING
END
);
}

if (!realpath($path = 'LICENSE')) {
  if (!is_file($path))
    if (@touch($path))
      file_put_contents($path, <<<END
LICENSE
END
);
}

if (!realpath($path = 'README.md')) {
  if (!is_file($path))
    if (@touch($path))
      file_put_contents($path, <<<END
README
END
);
}

if (!realpath($path = 'docs/')) {
  if (!mkdir($path, 0755, true))
    $errors['DOCS'] = $path . ' does not exist';

  if (!is_file('docs/getting-started.md'))
    if (@touch('docs/getting-started.md'))   // https://github.com/auraphp/Aura.Session/docs/getting-started.md
      file_put_contents('docs/getting-started.md', <<<END
getting-started
END
);
}

if (!realpath($path = 'public/policies/')) {
  if (!mkdir($path, 0755, true))
    $errors['POLICIES'] = $path . ' does not exist';

  if (!is_file('public/policies/privacy-policy'))
    if (@touch('public/policies/privacy-policy'))
      file_put_contents('public/policies/privacy-policy', <<<END
Privacy Policy
END
);

  if (!is_file('public/policies/terms-of-use'))
    if (@touch('public/policies/terms-of-use'))
      file_put_contents('public/policies/terms-of-use', <<<END
Terms of Use
END
);
}

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

  if (!is_file(APP_PATH . 'LICENSE'))
    if (@touch(APP_PATH . 'LICENSE'))
      if (check_http_200('http://www.wtfpl.net/txt/copying'))
        file_put_contents(APP_PATH . 'LICENSE', file_get_contents('http://www.wtfpl.net/txt/copying'));
      else 
        file_put_contents(APP_PATH . 'LICENSE', <<<END
This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <https://unlicense.org>
END
);

}
ob_start();
// write content

// defined('PHP_ZTS') and $errors['PHP_ZTS'] = 'PHP was built with ZTS enabled.';

//echo APP_SELF;

//dd(getRelativePath(APP_SELF, '/public'));

// Check if the directory structure is /public_html/

require('constants.php');

if (APP_ENV == 'development') {

if (is_readable($path = ini_get('error_log')) && filesize($path) >= 0 ) {
  $errors['ERROR_LOG'] = shell_exec('sudo tail ' . $path);
  if (isset($_GET[$error_log = basename(ini_get('error_log'))]) && $_GET[$error_log] == 'unlink') {
    unlink($path);
    exit(); // header('Location: ' . APP_WWW)
  }
}
/**This Program Should be Disabled by default ... for debugging purposes only!**/
if (isset($_GET['src']) && is_readable($path = $_GET['src']) && filesize($path) > 0 ) {
  Shutdown::setEnabled(false)->setShutdownMessage(function() use($path) {
    return highlight_file($path); /* eval('?>' . $project_code); // -wow */
  })->shutdown();
}
/*****/
}


if (isset($_GET['project'])) {
  //require_once('composer.php');
  //require_once('project.php');

if (isset($_GET['app']) && $_GET['app'] == 'project') require_once('app.project.php');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (is_file(APP_ROOT . 'project.php') && isset($_GET['project']) && $_GET['project'] == 'show') {
  Shutdown::setEnabled(false)->setShutdownMessage(function() {
      return eval('?>' . file_get_contents('project.php')); // -wow
    })->shutdown(); // die();
} elseif (!is_file(APP_ROOT . 'project.php')) {
file_put_contents(APP_ROOT . 'project.php', '<?php ' . <<<END
if (__FILE__ == get_required_files()[0])
  if (\$path = (basename(getcwd()) == 'public')
    ? '' : (is_file('config.php') ? 'config.php' : '')) require_once(\$path); 
else die(var_dump(\$path . ' path was not found. file=config.php'));

ob_start();
// Dump the variable

echo 'Hello World!';

// Capture the output into a variable
\$output = ob_get_clean();
ob_end_clean();

return <<<END
<!DOCTYPE html>
<html>
<head>
  <title></title>

<style>
code { 
    background: hsl(220, 80%, 90%);
    display:block;
    white-space:pre-wrap;
}

pre {
    white-space: pre-wrap;
    background: hsl(30,80%,90%);
}
</style>

</head>
<body style="background-color: #fff;">
<pre><code>{\$output}</code></pre>
</body>
</html>
END
);
}


}

if (basename($dir = APP_PATH) != 'config') {
  if (in_array(basename($dir), ['public', 'public_html']))
    chdir('../');

//dd($dir);

  //else chdir('../'); //
  //dd((__DIR__ . DIRECTORY_SEPARATOR . '*.php'));
    //if (is_dir('config')) {}
  $previousFilename = '';

  $dirs = [
    0 => APP_PATH . 'composer.php',
    1 => APP_PATH . 'composer-setup.php',
    //1 => APP_PATH . 'config.php',
    //1 => APP_PATH . 'constants.php',
    //2 => APP_PATH . 'functions.php',
    2 => APP_PATH . 'git.php',
    3 => APP_PATH . 'npm.php',

  ]; // array_filter(glob(__DIR__ . DIRECTORY_SEPARATOR . '*.php'), 'is_file');

  usort($dirs, function ($a, $b) {
      // Define your sorting criteria here
    if (basename($a) === 'composer-setup.php')
        return 1; // $a comes after $b
    elseif (basename($b) === 'composer-setup.php')
        return -1; // $a comes before $b
    else 
        return strcmp(basename($a), basename($b)); // Compare other filenames alphabetically
  });

//dd($dirs);

  foreach ($dirs as $includeFile) {
    if (in_array($includeFile, get_required_files())) continue; // $includeFile == __FILE__

    if (basename($includeFile) === 'composer-setup.php') continue;

    //echo basename($includeFile) . "<br />\n"; 

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
  //dd(get_required_files());

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
            file_put_contents(APP_PATH . 'index.php', '<?php require_once(\'public/index.php\');');

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

define('APP_ERRORS', $errors ?? (($error = ob_get_contents()) == null ? null : 'ob_get_contents() maybe populated/defined/errors... error=' . $error ));
ob_end_clean();

//var_dump(APP_ERRORS);




//Shutdown::setEnabled(false)->setShutdownMessage(function() {
//echo 'hello world';
//})->shutdown();

//(defined('APP_DEBUG') && APP_DEBUG) and $errors['APP_DEBUG'] = (bool) var_export(APP_DEBUG, APP_DEBUG); // print('Debug (Mode): ' . var_export(APP_DEBUG, true) . "\n");
/*
Shutdown::setEnabled(false)->setShutdownMessage(function() {
      global $pdo, $session_save;
      //if (defined('APP_INSTALL') && APP_INSTALL && $path = APP_PATH . 'install.php') // is_file('config/constants.php')) 
      //    require_once($path);

      defined('APP_END') or define('APP_END', microtime(true));
      //include('checksum_md5.php'); // your_logger(get_included_files());
      //unset($pdo);
    
      //echo "Executing shutdown function...\n";
    })->shutdown();

*/

//Shutdown::create()->setEnabled(true)->shutdown();

//$shutdown = new Shutdown();
//$shutdown->setEnabled(true)->shutdown();



