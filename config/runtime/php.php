<?php

!defined('APP_PATH') and define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Define PHP_EXEC if not already defined
if (!defined('PHP_EXEC')) {
    define('PHP_EXEC', $_ENV['PHP']['EXEC'] ?? PHP_BINARY ?? '/usr/bin/php');
}

// Register PHP runner in the global runtime registry
$GLOBALS['runtimes']['php'] = [
    'name' => 'PHP',
    'exec' => PHP_EXEC,
    'file_ext' => 'php',
    'args' => '-d display_errors=1',
    'template' => "<?php\n\n%s\n",  // Optional: wrap code in a template
    'run' => function (string $code, array $options = []) {
        $exec = $options['exec'] ?? PHP_EXEC;
        $args = $options['args'] ?? '-d display_errors=1';

        // Create a temporary file
        $tmpFile = tempnam(sys_get_temp_dir(), 'code_') . '.php';
        $wrappedCode = sprintf($options['template'] ?? "<?php\n\n%s\n", $code);
        file_put_contents($tmpFile, $wrappedCode);

        // Build and run command
        $cmd = escapeshellcmd("$exec $args " . escapeshellarg($tmpFile));
        $output = shell_exec($cmd);

        // Optionally clean up
        if (empty($options['keep_file'])) {
            unlink($tmpFile);
        }

        return $output;
    }
];

/*
if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"]))
    if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
        chdir('../');
        if ($path = realpath('bootstrap.php')) // is_file()
            require_once $path;
        //die('does this do anything?');

    } else
        die(var_dump("Path was not found. file=$path"));
else
    require_once APP_PATH . 'bootstrap.php';

require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.env.php';
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.paths.php';
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.runtime.php';
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.url.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.server.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.app.base.php';
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.app.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.client.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.project.php';

require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'functions.php';
require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.php'; // This is not needed, constants are already defined below */

if (!defined('APP_BOOTSTRAPPED')) {
    //require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.env.php';
    //require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.paths.php';
    //require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.runtime.php';
    //require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.url.php';
    //require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.app.php';
    require_once APP_PATH . 'bootstrap.php';
}

file_put_contents(
    APP_PATH . '.env.json',
    json_encode(
        array_filter(get_defined_constants(true)['user'], fn($k) => strpos($k, 'APP_') === 0 || strpos($k, 'PATH_') === 0, ARRAY_FILTER_USE_KEY),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    )
);

//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.client-project.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.backup.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.php'; // This is not needed, constants are already defined above

//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'autoload.php';

!defined('APP_SELF') and define('APP_SELF', get_required_files()[0] ?? realpath($_SERVER["SCRIPT_FILENAME"])); /*__FILE__*/
// define('PATH_PUBLIC', __DIR__);

$previousFilename = '';

// 0.381 seconds

// Handle the 'php' app configuration
$dirs = [APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'php.php'];  // pre-load?

// Handle the 'git' app configuration
!isset($_GET['app']) || $_GET['app'] != 'git' ?:
    (APP_SELF != PATH_PUBLIC ?: $dirs[] = APP_PATH . APP_BASE['config'] . 'git.php');

// Handle the 'composer' app configuration
!isset($_GET['app']) || $_GET['app'] != 'composer' ?:
    $dirs = (APP_SELF != PATH_PUBLIC)
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
            (!file_exists($include = APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php') && !is_file($include) ?: $include),
        ]
    );

//if (is_file($path = APP_PATH . APP_BASE['config'] . 'composer.php')) require_once $path; 
//else die(var_dump("$path path was not found. file=" . basename($path)));

// Handle the 'npm' app configuration
!isset($_GET['app']) || $_GET['app'] != 'npm' ?:
    (APP_SELF != PATH_PUBLIC ?:
        (!is_file($include = APP_PATH . APP_BASE['config'] . 'npm.php') ?: $dirs[] = $include));

unset($include);

if (APP_SELF != PATH_PUBLIC) {
    $priorityFiles = [
        //APP_PATH . APP_BASE['config'] . 'php.php',
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


//die(var_dump(get_defined_constants(true)['user']));

// Get all PHP files in the 'classes' directory
$paths = array_filter(glob(APP_PATH . 'config/classes' . DIRECTORY_SEPARATOR . '*.php'), 'is_file');

// Define the filenames to be excluded
$excludedFiles = [
    //'class.sockets.php',
    'class.websocketserver.php'
];

// Remove excluded files from $paths
$paths = array_filter($paths, function ($path) use ($excludedFiles) {
    return !in_array(basename($path), $excludedFiles);
});

// Sort $paths alphabetically by filename
usort($paths, function ($a, $b) {
    return strcmp(basename($a), basename($b));
});

// 0.242 secs

// Require each file in $paths
foreach ($paths as $path) {
    if ($resolvedPath = realpath($path)) {
        require_once $resolvedPath;
    } else {
        die(var_dump(basename($path) . ' was not found. file=' . $path));
    }
}

// 0.249 secs


//$output = run_code($runtime, $code);
//echo "<pre>$output</pre>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cmd'])) {
    $cmd = trim($_POST['cmd']);
    $output = [];


    //dd(APP_QUERY);
    require_once APP_PATH . 'app' . DIRECTORY_SEPARATOR . 'directory.php';

    // Match: php <inline code>  (not -r)
    if (preg_match('/^php\s+(?!-r)(.*)$/i', $cmd, $match)) {
        $code = rtrim(trim($match[1], "\"'"));
        if (substr($code, -1) !== ';')
            $code .= ';';


        $runtime = $_POST['runtime'] ?? 'php';
        $output[] = run_code($runtime, $code); // $output[] = eval ($match[1] . (substr($match[1], -1) != ';' ? ';' : ''));

        // Fallback to eval if no callable runtime found
        try {
            // Sanitize/normalize code to end with semicolon
            $code = rtrim($code);
            if ($code !== '' && substr($code, -1) !== ';') {
                $code .= ';';
            }
            // Use eval to execute the code
            $output[] = eval ($code);

        } catch (Throwable $e) {
            return "Error: eval failed — " . $e->getMessage();
        }

        // ***   Match: php -r "<code>"   ****
    } elseif (preg_match('/^php\s+-r\s+["\']?(.*?)["\']?$/i', $cmd, $match)) {
        $inlineCode = rtrim(trim($match[1]), ';') . ';';
        $cmdFormatted = "php -r \"$inlineCode\"";

        if (!isset($_SERVER['SOCKET']) || !$_SERVER['SOCKET']) {
            exec($cmdFormatted, $output);
        } else {
            $message = "cmd: $cmdFormatted\n";
            $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT;
            $errors['server-2'] = "Client request: $message";

            fwrite($_SERVER['SOCKET'], $message);
            $output[] = "$cmdFormatted: ";

            while (!feof($_SERVER['SOCKET'])) {
                $response = fgets($_SERVER['SOCKET'], 1024);
                if (!empty($response)) {
                    $errors['server-3'] = "Server response: $response";
                    $output[count($output) - 1] .= trim($response);
                }
            }
        }

        // Match: hello
    } elseif (preg_match('/^hello/i', $cmd)) {
        $output[] = shell_exec('./hello');

        // Fallback: php -v
    } else {
        exec('php -v', $output);
    }
    //dd($_POST);
    // Output logic
    if (!empty($output)) {
        echo (count($output) === 1)
            ? join("\n... <<< ", $output)
            : join("\n", $output);
    }

    // Optional shutdown behavior
    Shutdown::setEnabled(true)->setShutdownMessage(function () use ($output) {
        //return var_export($output, true); // return getcwd(); // fallback to current working directory
    })->shutdown();
}

/* else if (preg_match('/^composer\s+(:?(.*))/i', $_POST['cmd'], $match)) {

 if (!isset($_SERVER['SOCKET']) || !$_SERVER['SOCKET']) {

   //$output[] = dd(COMPOSER_EXEC);
   //$output[] = APP_SUDO . COMPOSER_EXEC['bin'] . ' ' . $match[1];
   $proc=proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' ' . $match[1] . ' --working-dir="' . APP_PATH . APP_ROOT . '"',
   [
     ["pipe", "r"],
     ["pipe", "w"],
     ["pipe", "w"]
   ],
   $pipes);
   [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
   $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (!isset($exitCode) && $exitCode == 0 ? NULL : " Exit Code: $exitCode");
         //$output[] = $_POST['cmd'];        
   //exec($_POST['cmd'], $output);
   //die(var_dump($output));

 } else {

   $errors['server-1'] = "Connected to " . SERVER_HOST . " on port " . SERVER_PORT . "\n";

   // Send a message to the server
   $errors['server-2'] = 'Client request: ' . $message = "cmd: " . $_POST['cmd'] . "\n";

   $output[] = $_POST['cmd'] . ': ';

   //dd($message, false);
   if (isset($_SERVER['SOCKET']) && is_resource($_SERVER['SOCKET'])) {
     switch (get_resource_type($_SERVER['SOCKET'])) {
       case 'stream':
         fwrite($_SERVER['SOCKET'], $message);
         break;
       default:
         socket_write($_SERVER['SOCKET'], $message);
         break;
     }
   }

   // Read response from the server
   while (!feof($_SERVER['SOCKET'])) {
     $response = fgets($_SERVER['SOCKET'], 1024);

     $errors['server-3'] = "Server responce: $response\n";
     if (isset($output[end($output)])) $output[end($output)] .= trim($response);
     else $output[1] .= trim($response);
     //if (!empty($response)) break;
   }

   //die(var_dump($output));
 }


} */


// Do not include index.php here at the end, $_ENV is not available in index.php ... to be figured out.
//PHP_SAPI === 'cli' ?: require_once APP_PATH . APP_BASE['public'] . 'index.php';

//dd(get_defined_constants(true)['user']);

//dd(APP_ROOT, false);

//dd($_SERVER);
//define('PHP_EXEC', $_ENV['PHP']['EXEC'] ?? '/usr/bin/php'); // const PHP_EXEC = 'string only/non-block/ternary';