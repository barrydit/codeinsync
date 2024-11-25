CHANGELOG

angular, node, npm, json, react, git, vue, api


.git/config

fatal: could not read Username for 'https://github.com': No such device or address

[remote "origin"]
	url = git@github.com:barrydit/repository.git

git remote set-url origin https://github.com/barrydit/repository.git



$composerUser = !isset($_ENV['COMPOSER']['USER']) ?: $_ENV['COMPOSER']['USER'];  
$componetPkg = !isset($_ENV['COMPOSER']['PACKAGE']) ?: $_ENV['COMPOSER']['PACKAGE'];
$user = getenv('USERNAME') ?: (getenv('APACHE_RUN_USER') ?: getenv('USER') ?: '');



(APP_SELF !== APP_PATH_SERVER) and $socketInstance = Sockets::getInstance();
//$socketInstance->handleClientRequest("composer self-update\n");
fclose($socketInstance->getSocket());


config/config.php

die(getcwd() . ' == ' . __DIR__); // /mnt/c/www/public == /mnt/c/www/config/classes


get_required_files() == 
  ===
  [0] => "/mnt/c/www/server.php" (Socket Server 0.0.0.0:8080) || Web Application "/mnt/c/www/public/index.php" ,.. app.console.php, app.browser.php, app.notes.php, app.directory.php 
  ===
  1  "/mnt/c/www/index.php" (inaccessbile index.php file used to include config.php)
  2    "/mnt/c/www/config/config.php"
  3      "/mnt/c/www/config/functions.php"
  4        "/mnt/c/www/config/classes/class.clientorproj.php"
  5        "/mnt/c/www/config/classes/class.logger.php"
  6        "/mnt/c/www/config/classes/class.notification.php"
  7        "/mnt/c/www/config/classes/class.shutdown.php"
  8      "/mnt/c/www/config/constants.php"
  9      "/mnt/c/www/config/php.php"
  10        "/mnt/c/www/config/classes/class.sockets.php"

get_required_files() == 
 [0]=> "/mnt/c/www/server.php" (Socket Server 0.0.0.0:8080) ||
       "/mnt/c/www/public/index.php" Web Application ,.. app.console.php, app.browser.php, app.notes.php, app.directory.php 
 [1]=>   "/mnt/c/www/bootstrap.php"
 [2]=>     "/mnt/c/www/config/php.php"
 [3]=>       "/mnt/c/www/config/config.php"
 [4]=>       "/mnt/c/www/config/functions.php"
 [5]=>         "/mnt/c/www/config/classes/class.clientorproj.php"
 [6]=>         "/mnt/c/www/config/classes/class.logger.php"
 [7]=>         "/mnt/c/www/config/classes/class.notification.php"
 [8]=>       "/mnt/c/www/config/constants.php"
 [9]=>         "/mnt/c/www/config/classes/class.sockets.php"
 [10]=>    "/mnt/c/www/public/ui.ace_editor.php"
 [11]=>    "/mnt/c/www/public/ui.composer.php"
 [12]=>    "/mnt/c/www/config/composer.php"
 [13]=>      "/mnt/c/www/vendor/autoload.php"

.htpasswd

/*
list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = 
  explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Text to send if user hits Cancel button';
    exit;
} else { echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p><p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>"; }
*/




if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"])) 
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    if (is_file($path = realpath('index.php')))
      require_once $path;
  } else {
    die(var_dump("Path was not found. file=$path"));
  }

switch (__FILE__) {
  case get_required_files()[0]:
    if ($path = (basename(getcwd()) == 'public') ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '') require_once $path;
    else die(var_dump("$path path was not found. file=config.php"));

    break;
  default:
}

PHP

  $cmd = 'test';

  $msg = (FALSE == FALSE) . 1 ? : 'another test'; // 11

  die($msg);



        $runServer = function($serverExec, $phpExec) {
            if (is_executable($serverExec)) {
                echo 'Running (New) Server' . PHP_EOL;

                $tty = trim(shell_exec('tty'));
                $tty = ($tty !== 'not a tty') ? $tty : null;

                // Verify that we successfully retrieved a TTY
                if ($tty) { 
                  $command = "$phpExec $serverExec";
                  // Open a process with custom descriptors
                  $descriptors = [
                      0 => ["pipe", "r"],  // stdin
                      1 => ["file", "/dev/$tty", "w"],  // stdout to TTY or change to a file if needed
                      2 => ["file", "/dev/$tty", "a"]   // stderr to TTY or change to a file if needed
                  ];

                  $process = proc_open($command, $descriptors, $pipes);

                  // Check if the process was successfully started
                  if (is_resource($process)) {
                      // Close pipes as needed
                      fclose($pipes[0]);
                  
                      // Wait for the process to finish and close it
                      $return_value = proc_close($process);
                      echo "Command finished with return value: $return_value\n";
                  } else {
                      echo "Failed to start the process.\n";
                  }

                } else {
                    $tty ??= 'pts/1';

                  $command = "$phpExec $serverExec > /dev/$tty 2>&1";
                  $descriptors = [
                      0 => ["pipe", "r"],  // stdin
                      1 => ["pipe", "w"],  // stdout
                      2 => ["pipe", "w"]   // stderr
                  ];

                  $process = proc_open($command, $descriptors, $pipes);

                  // Check if the process was successfully started
                  if (is_resource($process)) {
                      // Close pipes as needed

                    foreach ($pipes as $pipe) {
                        fclose($pipe);
                    }
                  
                      // Wait for the process to finish and close it
                    $return_value = trim(proc_close($process));
                    echo "Command finished with return value: $return_value\n";
                  } else {
                    echo "Failed to start the process.\n";
                  }
                }

                echo "Current TTY: " . ($tty ? : 'pts/1 (default)') . "\n";
                echo "Command: $command\n";
                $return_value = shell_exec((string) $command /*. ' > ' . escapeshellarg("/dev/pts/1") . ' 2>&1 &'*/); // Preferred executable (bash/php with shebang)

                echo "server.php has been started and output is redirected to $tty.\nResult: $return_value";

            } else {
                echo 'Running (New) Server via PHP' . PHP_EOL;
                shell_exec('nohup ' . escapeshellcmd($phpExec) . ' ' . escapeshellcmd($serverExec) . ' > /dev/null 2>&1 &');
            }
        };





Sockets



        $bytesReceived = @socket_recv($socket, $buffer, 1024, MSG_DONTWAIT);
        if ($bytesReceived !== 0) {
            ///echo "Socket connection is open.\n";
            if ($client = @socket_accept($socket)) {
              handleSocketClientConnection($client);
            }
        } 
        



/*

stream_set_blocking($_SERVER['SOCKET'], false);

stream_set_timeout($_SERVER['SOCKET'], 10);

$writtenBytes = fwrite($_SERVER['SOCKET'], $message);
if ($writtenBytes === false) {
  $error = socket_last_error($_SERVER['SOCKET']);
  $errorMessage = socket_strerror($error);
  echo "Socket write error: $errorMessage\n";

} else {
  fflush($_SERVER['SOCKET']); // Flush the buffer
  echo "Bytes written: $writtenBytes\n";
}

// Check if the socket is ready for reading
$read = [$_SERVER['SOCKET']];
$write = null;
$except = null;
$ready = stream_select($read, $write, $except, 5); // 5 seconds timeout
if ($ready > 0) {

} else {
    echo "Socket not ready for reading.";
}


// Stream error check
$error = error_get_last();
if ($error) {
    echo "Stream Error: " . $error['message'];
}

*/



HTACCESS

RewriteEngine On
RewriteBase "/clientele/000-Lastname,\ Firstname/example.ca/public"

# Rewrite requests for /assets to the /../resources directory if the file doesn't exist in /assets
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^assets/(.*)$ ../resources/$1 [L]


vendor/composer/installed.php

<?php return array(
    'root' => array(
        'name' => 'barrydit/1234',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '1d21ed08f33201dc985e9f2dca4984dd1783d9a9',
        'type' => 'project',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'barrydit/1234' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '1d21ed08f33201dc985e9f2dca4984dd1783d9a9',
            'type' => 'project',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);




:: PHP

if (!is_resource($_SERVER['SOCKET']) || empty($_SERVER['SOCKET'])) { // get_resource_type($stream) == 'stream'

  $proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'APPLICATION', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

  [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

  if ($exitCode !== 0) {
    if (empty($stdout)) {
      if (!empty($stderr)) {
        $errors['APPLICATION'] = $stderr;
        error_log($stderr);
      }
    } else {
      $errors['APPLICATION'] = $stdout;
    }
  }

} else {
  $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";
  
  // Send a message to the server
  $errors['server-2'] = 'Client request: ' . $message = "cmd: composer update\n";
    
  fwrite($_SERVER['SOCKET'], $message);
  $output[] = trim($message) . ': ';
  // Read response from the server
  while (!feof($_SERVER['SOCKET'])) {
    $response = fgets($_SERVER['SOCKET'], 1024);
    $errors['server-3'] = "Server responce: $response\n";
    if (isset($output[end($output)])) $output[end($output)] .= $response = trim($response);
    //if (!empty($response)) break;
  }
}


exec() shell_exec() escapeshellarg()


      $proc=proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . "composer $matches[1]",  
        array(
          array("pipe","r"),
          array("pipe","w"),
          array("pipe","w")
        ),
        $pipes);
                list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
                $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");



$proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO ) . basename($bin) . ' --version;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        $exitCode = proc_close($proc);


if (is_resource($socket) || !empty($socket)) {
  //socket_close($socket);
  socket_write
} elseif (is_resource($stream) && get_resource_type($stream) == 'stream') {
  //fclose($stream);
  fwrite()
}



ui.composer.php

  autoload.php
  composer.php


Many php functions either produce a forward slash, indicating the end of folder/path

  $path = dirname(__DIR__) . '/config/config.php'


Which is the better format ... 

1. stripos(PHP_OS, 'LIN') === 0
...
2. strpos(PHP_OS, 'WIN') === 0
...
3. strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'


(isset($_GET['client']) ? 'client=' . $_GET['client'] . '&' : '') . (isset($_GET['domain']) ? 'domain=' . $_GET['domain'] . '&' : '') . (isset($_GET['project']) ? 'project=' . $_GET['project'] . '&' : '')

(!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '' ) . (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') ) // client | project | client / ??domain / project

(!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '/') : 'client=' . $_GET['client'] . '/' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '/' : '') : '' ) . (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') ) client/project/domain/project

:: JavaScript

JScript
  tailwindcss-3.3.5.js
Style (type="text/tailwindcss")


- Non-indented/tab code means that it is included/required/imported

Javascript libraries must be loaded in a particular order. If a library can not be loaded, "ace is not defined", then they are in the wrong order.

jQuery, jQuery-ui, ace-editor, requirejs


    <script src="https://d3js.org/d3.v4.min.js"></script>
    <script src="https://d3js.org/d3-selection-multi.v1.min.js"></script>
    <script src="https://d3js.org/d3-force.v1.min.js"></script>
    <script src="https://d3js.org/d3-drag.v1.min.js"></script>
    <script src="https://d3js.org/d3-timer.v1.min.js"></script>
    <script src="https://d3js.org/d3-dispatch.v1.min.js"></script>
    <script src="https://d3js.org/d3-selection.v1.min.js"></script>
    <script src="https://d3js.org/d3-transition.v1.min.js"></script>
    <script src="https://d3js.org/d3-quadtree.v1.min.js"></script>
    <script src="https://d3js.org/d3-interpolate.v1.min.js"></script>
    <script src="https://d3js.org/d3-color.v1.min.js"></script>
    <script src="https://d3js.org/d3-ease.v1.min.js"></script>
    <script src="https://d3js.org/d3-scale.v1.min.js"></script>
    <script src="https://d3js.org/d3-scale-chromatic.v1.min.js"></script>
    <script src="https://d3js.org/d3-path.v1.min.js"></script>
    <script src="https://d3js.org/d3-shape.v1.min.js"></script>
    <script src="https://d3js.org/d3-array.v1.min.js"></script>
    <script src="https://d3js.org/d3-format.v1.min.js"></script>
    <script src="https://d3js.org/d3-time-format.v1.min.js"></script>
    <script src="https://d3js.org/d3-time.v1.min.js"></script>
    <script src="https://d3js.org/d3-axis.v1.min.js"></script>
    <script src="https://d3js.org/d3-brush.v1.min.js"></script>
    <script src="https://d3js.org/d3-zoom.v1.min.js"></script>
    <script src="https://d3js.org/d3-fetch.v1.min.js"></script>
    <script src="https://d3js.org/d3-geo.v1.min.js"></script>
    <script src="https://d3js.org/d3-geo-projection.v2.min.js"></script>
    <script src="https://d3js.org/d3-polygon.v1.min.js"></script>
    <script src="https://d3js.org/d3-quadtree.v1.min.js"></script>
    <script src="https://d3js.org/d3-random.v1.min.js"></script>
    <script src="https://d3js.org/d3-request.v1.min.js"></script>
    <script src="https://d3js.org/d3-voronoi.v1.min.js"></script>
    <script src="https://d3js.org/d3-delaunay.v1.min.js"></script>
    <script src="https://d3js.org/d3-hierarchy.v1.min.js"></script>






