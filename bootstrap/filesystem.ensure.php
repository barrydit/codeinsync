<?php

/**
 * filesystem.ensure.php
 *
 * Ensures required directories and bootstrap files exist.
 * Depends on APP_PATH, APP_BASE, APP_SCOPE_DIR, etc.
 */
if (!defined('APP_DEBUG') || !APP_DEBUG) {
    return; // Only run in debug/development mode
}

$errors ??= []; // $errors ?? [];

// ---------------------------------------------------------
// [1] Ensure base directories exist (mkdir if missing)
// ---------------------------------------------------------

foreach (APP_BASE as $key => $dir) {
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            $errors['DIR_CREATE'][] = "Failed to create directory: $dir [$key]";
        }
    }
}

// ---------------------------------------------------------
// [2] Ensure core files like .env, .htaccess, LICENSE exist
// ---------------------------------------------------------

$files_to_check = [
    '.env',
    '.htaccess',
    '.gitignore',
    'LICENSE',
    'README.md',
];

foreach ($files_to_check as $filename) {
    $file = APP_PATH . $filename;
    if (!is_file($file)) {
        @touch($file);
    }
}


$htaccess = <<<END
# Enable Rewrite Engine
RewriteEngine On

# Set the base directory (adjust if your application is in a subfolder)
RewriteBase /

# Redirect all requests to index.php except if the file or directory exists
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
END;

// ---------------------------------------------------------
// [3] Populate core files from source_code.json (if available)
// ---------------------------------------------------------

$sourceMapFile = APP_BASE['data'] . 'source_code.json';
$source_code = [];

if (is_file($sourceMapFile)) {
    $source_code = json_decode(file_get_contents($sourceMapFile), true) ?? [];
}

foreach ($files_to_check as $filename) {
    $file = APP_PATH . $filename;
    if (empty(file_get_contents($file)) && isset($source_code[$filename])) {
        file_put_contents($file, $source_code[$filename]);
    }
    
    // Special case: LICENSE from online source
    if ($filename === 'LICENSE' && empty(file_get_contents($file))) {
        if (defined('APP_IS_ONLINE') && APP_IS_ONLINE) {
            $wtfpl = 'http://www.wtfpl.net/txt/copying';
            $licenseText = @file_get_contents($wtfpl);
            if ($licenseText) {
                file_put_contents($file, $licenseText);
            } elseif (isset($source_code[$filename])) {
                file_put_contents($file, $source_code[$filename]);
            }
        }
    }
}

// ---------------------------------------------------------
// [4] Optional: Log any issues
// ---------------------------------------------------------


// Capture and suppress any accidental output from this block
$ob_content = (function () {
    ob_start();
    // Set headers to prevent caching

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
    //header("Content-Type: text/html; charset=UTF-8");

    if (!is_dir($path = APP_PATH . 'projects' . DIRECTORY_SEPARATOR . 'internal')) {
        $errors['projects'] = "projects/internal directory does not exist.\n";
        mkdir($path, 0777, true);
    }

    if (!is_file($file = APP_PATH . 'projects' . DIRECTORY_SEPARATOR . 'index.php')) {
        $errors['projects'] = 'projects' . DIRECTORY_SEPARATOR . 'index.php does not exist.';
        if (is_file($source_file = APP_PATH . 'data' . DIRECTORY_SEPARATOR . 'source_code.json')) {
            $source_file = json_decode(file_get_contents($source_file));
            if ($source_file)
                file_put_contents($file, $source_file->{'projects/internal/index.php'});
        }
        unset($source_file);
    } elseif (is_file(APP_PATH . 'projects' . DIRECTORY_SEPARATOR . 'index.php') && isset($_GET['project']) && $_GET['project'] == 'show') {
        /* Shutdown::setEnabled(false)->setShutdownMessage(fn() => eval ('?>' . file_get_contents(APP_PATH . 'projects' . DIRECTORY_SEPARATOR . 'index.php')))->shutdown(); */
    }

    // function loadEnvConfig($file) {

    //}

    // Load the environment variables from the .env file
// = loadEnvConfig(APP_PATH . '.env');

    if (basename($dir = getcwd()) != 'config') {

        if (in_array(basename($dir), ['public', 'public_html']))
            chdir('../');

        //require_once 'constants.php';
        //require_once 'config' . DIRECTORY_SEPARATOR . 'auth.php';
        //dd(getcwd());

        if (!defined('APP_CLI') || !APP_CLI) {
            // If running in CLI mode, load the bootstrap file
            // This is useful for command-line scripts that need to initialize the application
            require_once APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.php';
        }

        chdir(APP_PATH . APP_ROOT);
        if (is_file($file = APP_PATH . APP_ROOT . '.env')) {

            // Usage Example
            $globalPath = APP_PATH . '.env'; // Global .env
            $clientPath = APP_PATH . APP_ROOT . '.env'; // Client-specific .env
            $envData = Shutdown::loadEnvFiles($globalPath, $clientPath);

            if ($envData === false || empty($envData)) {
                $errors['ENV'] = 'Failed to load the .env file.';
            }

            // Populate $_ENV with the merged values
            $_ENV = array_replace($envData, $_ENV); // array_merge

            //dd($_ENV);
            define('ENV_CHECKSUM', hash('sha256', json_encode($_ENV, JSON_UNESCAPED_SLASHES)));
            //dd($_ENV);
            //dd('   ' . ENV_CHECKSUM);

            /*
                $parsedEnv = Shutdown::parse_ini_file_multi($file);
                $_ENV = array_merge_recursive_distinct($_ENV, $parsedEnv);
            */
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
        $errors['PATH_PUBLIC'] = "The `public_html` scenario was detected.\n";

        if (is_dir(dirname(APP_SELF, 2) . DIRECTORY_SEPARATOR . 'config')) {
            $errors['PATH_PUBLIC'] .= "\t" . dirname(APP_SELF, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '*' . ' was found. This is not generally safe-scenario.';
        }

        chdir(dirname(__DIR__, 1));  //dd(getcwd());
        // It is under the public_html scenario
        // Perform actions or logic specific to the public_html directory
        // For example:
        // include '/home/user_123/public_html/config.php';
    } elseif (basename(dirname(APP_SELF)) == 'public') {    // strpos(APP_SELF, '/public/') !== false

        //dd(APP_BASE);

        if (!is_file(APP_PATH . 'public' . DIRECTORY_SEPARATOR . 'install.php'))
            if (@touch(APP_PATH . 'public' . DIRECTORY_SEPARATOR . 'install.php'))
                file_put_contents(
                    APP_PATH . 'public' . DIRECTORY_SEPARATOR . 'install.php',
                    '<?php ' . <<<END
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
        // dd('testing...');
        if (basename(get_required_files()[0]) !== 'release-notes.php')
            if (is_dir('config')) {
                $previousFilename = ''; // Initialize the previous filename variable

                //$files = glob(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '*.php');
//$files = array_merge($files, glob(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '**' . DIRECTORY_SEPARATOR . '*.php'));

                //sort($files);

                foreach (array_filter(glob(__DIR__ . DIRECTORY_SEPARATOR . '*.php'), 'is_file') as $includeFile) {
                    echo "$includeFile<br />\n";

                    if (in_array($includeFile, get_required_files()))
                        continue; // $includeFile == __FILE__

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

        if (isset($_GET['install']))
            require_once 'public' . DIRECTORY_SEPARATOR . 'install.php';
    }



    //dd(get_defined_constants(true)['user']);

    /*
    if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR ) . 'constants.php')) // is_file('config' . DIRECTORY_SEPARATOR . 'constants.php')) 
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
  exit;
} */

    //use vlucas\phpdotenv;




    //dd($_ENV));

    // $dotenv->load();

    /*
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
    $dotenv->safeLoad();
    */

    !defined('APP_ERRORS') and define('APP_ERRORS', $errors ?? [($error = ob_get_contents()) == null ? null : "ob_get_contents() maybe populated/defined/errors... error=$error"]) /*and (empty(APP_ERRORS) ? '' :  throw new \RuntimeException((string)var_dump(APP_ERRORS)))*/ ;

    // Return any buffered output (if you want to log it)
    return ob_get_clean();
})();

// Optionally log what happened for debugging
if (!empty($ob_content)) {
    error_log("[filesystem.ensure] Output captured:\n$ob_content");
}

if (!empty($errors)) {
    // dd($errors); // or Logger::log($errors);
}
// End of filesystem.ensure.php