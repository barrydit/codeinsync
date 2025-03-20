#!/usr/bin/env php
<?php
declare(/*strict_types=1,*/ ticks=1); // First Line Only!

!defined('APP_PATH') and define('APP_PATH', __DIR__ . DIRECTORY_SEPARATOR);

!defined('PID_FILE') and define('PID_FILE', APP_PATH . 'server.pid');

!defined('DISABLE_SOCKET_INIT') and define('DISABLE_SOCKET_INIT', true);

file_put_contents(PID_FILE, $pid = getmypid());

require_once 'config' . DIRECTORY_SEPARATOR . 'php.php';



ini_set('error_log', APP_PATH . 'server.log');
ini_set('log_errors', 'true');

//dd(APP_ROOT);

/*
!defined('PID_FILE') and define('PID_FILE', APP_PATH . 'server.pid'); // getcwd()

if (file_exists(PID_FILE) && $pid = (int) file_get_contents(PID_FILE)) {
  if (stripos(PHP_OS, 'WIN') === 0) {
    exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\" >NUL", $output, $status);
    if ($status === 0) {
      error_log("Server is already running with PID $pid\n");
      echo "Server is already running with PID $pid\n";
      exit(1);
    }
  } else {
    if (extension_loaded('pcntl') && function_exists('posix_kill') && posix_kill($pid, 0)) {
      error_log("Server is already running with PID $pid\n");
      echo "Server is already running with PID $pid\n";
      exit(1);
    }
  }
} else if (isset($_SERVER['SUPERVISOR_ENABLED']) && $_SERVER['SUPERVISOR_ENABLED'] == '1') {
  file_put_contents(PID_FILE, $pid = getmypid());
  exit(1);
}*/

//!file_exists($file = posix_getpwuid(posix_getuid())['dir'].'/.aws/credentials')
//  and die('an aws credentials file is required. exiting file=' . $file);
if (PHP_SAPI === 'cli')
  if (stripos(PHP_OS, 'LIN') === 0) {
    (!extension_loaded('posix') || !extension_loaded('pcntl')) and die('Extenions posix && pcntl are required. exiting.');
    (!extension_loaded('sockets')) and die('sockets required. exiting');

    /**
     * Set the title of our script that ps(1) sees
     */
    if (!cli_set_process_title($title = basename(__FILE__))) {
      echo "Unable to set process title for PID " . file_get_contents(PID_FILE ?? APP_PATH . 'server.pid') . "...\n";
      exit(1);
    } else {
      //cli_set_process_name($title);
      echo "The process title '$title' has been set for your process!\n";

      /**
       * Summary of cli_set_process_name
       * @param mixed $title
       * @throws Exception
       * @return void
       */
      function cli_set_process_name($title)
      {
        file_put_contents('/proc/' . getmypid() . '/comm', $title);
      }
    }

    //stripos(PHP_OS, 'LIN') === 0

    // ps aux | grep server.php
    // kill -SIGTERM <PID>
    // kill -SIGINT <PID>
    // kill -SIGSTOP <PID>
    // kill -SIGCONT <PID>

    // [1]+  Stopped                 php server.php
    // kill -SIGKILL / -9 <PID>
    // [1]+  Killed                  php server.php

    // Signal handler to gracefully shutdown
    /**
     * Summary of signalHandler
     * @param mixed $signal
     * @throws \Exception
     * @return never
     */
    function signalHandler($signal): void
    {
      global $running, $socket, $stream;
      // define('WNOHANG', 0);

      switch ($signal) {
        case SIGCHLD:
          while (pcntl_waitpid(-1, $running, WNOHANG) > 0) {
            // Reap child processes
          }
          break;

        case SIGHUP:
          // Restart the server
          restartServer();
          break;

        case SIGTERM:
        //echo "Process received SIGTERM. Terminating...\n";
        //exit; // Gracefully exit after handling
        case SIGINT:
          echo "Process received SIGINT (Ctrl+C). Terminating...\n";
          //require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class.sockets.php';
          echo '   Shutting down server... PID=' . getmypid() . PHP_EOL;
          Logger::error('Shutting down server... PID=' . getmypid());

          //$file = PID_FILE;
          !is_file(PID_FILE) ?: unlink(PID_FILE);

          if ($socket)
            socket_close($socket);
          elseif (isset($stream) && is_resource($stream) && get_resource_type($stream) == 'stream')
            fclose($stream);

          //if ($server)
          //  fclose($server); // Close server file descriptor if open

          //if ($stream)
          //  fclose($stream); // Close stream if applicable

          /*
                  if (!empty($socket) && is_resource($stream)) {
                    if (extension_loaded('sockets')) {
                      socket_write($socket, 'Shutting down server... PID=' . getmypid());
                      socket_close($socket);
                    } elseif (get_resource_type($socket) == 'stream') {
                      fwrite($socket, 'Shutting down server... PID=' . getmypid());
                      fclose($socket);
                    }
                  }
          */
          $running = false;
          break;
      }
      //exit(1);
    }

    pcntl_async_signals(true); // Turn on asynchronous signal handling

    // Register signal handlerfor SIGCHLD, SIGTERM, and SIGINT
    pcntl_signal(SIGCHLD, 'signalHandler'); // hangs on sockets with empty cmd: on loop
    pcntl_signal(SIGHUP, 'signalHandler');
    pcntl_signal(SIGTERM, 'signalHandler');
    pcntl_signal(SIGINT, 'signalHandler');

  } elseif (stripos(PHP_OS, 'WIN') === 0) {
    // Windows
    //exec('taskkill /F /PID ' . getmypid());
    //exec('taskkill /F /IM php.exe');
    //exec('taskkill /F /IM php-cgi
  }

function restartServer()
{
  global $running, $socket;

  // Close existing socket and other resources
  socket_close($socket);

  // Restart logic
  echo str_replace('{{STATUS}}', 'Restarting the Server... PID=' . getmypid() . str_pad('', 12, " "), APP_DASHBOARD) . PHP_EOL;

  require_once APP_PATH . APP_BASE['config'] . 'classes' . DIRECTORY_SEPARATOR . 'class.sockets.php';

  unlink(PID_FILE);

  Sockets::handleSocketConnection(true);  // exec( (PHP_EXEC ?? 'php') . ' ' . basename(__FILE__) . ' &'); // Restart server script in the background
  exit(0); // Terminate the current instance
}
/**
 * Summary of safe_chdir
 * @param mixed $path
 * @throws \Exception
 * @return void
 */
function safe_chdir($path)
{
  // Resolve the absolute path of the current directory
  $current_dir = realpath($path);

  // Define the root directory you don't want to go past
  $root_dir = realpath('/mnt/c/www');

  // Resolve the parent directory path
  $parent_dir = realpath("$current_dir/../");

  // Check if the parent directory is within the allowed root
  if (strpos($parent_dir, $root_dir) === 0 && strlen($parent_dir) >= strlen($root_dir)) {
    chdir($parent_dir);
    echo "Changed directory to: " . getcwd();
  } else {
    echo "Cannot go past the root directory: $root_dir";
  }
}

set_time_limit(0);

//dd(get_defined_constants()); // get_required_files()
defined('SERVER_HOST') or define('SERVER_HOST', defined('APP_HOST') ? APP_HOST : '0.0.0.0');
defined('SERVER_PORT') or define('SERVER_PORT', 8080); // 9000

!empty($parsed_args = parseargs())
  and print ("Argv(s): " . var_export($parsed_args, true) . "\n");

/**
 * Summary of clientInputHandler
 * @param mixed $input
 * @throws \Exception
 * @return mixed
 */
function clientInputHandler($input)
{
  global $socket, $stream, $running, $manager;

  if ($input == '')
    return;
  error_log('Client [Input]: ' . trim($input));
  echo 'Client [Input]: ' . trim($input) . "\n";
  //$input = trim($input);
  $output = '';

  if (is_file($pid_file = PID_FILE ?? APP_PATH . 'server.pid') && $pid = (int) file_get_contents($pid_file)) {
    if (stripos(PHP_OS, 'WIN') === 0) {
      exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\" >NUL", $output, $status);
      if ($status === 0) {
        $output = "Server is already running with PID $pid\n";
        echo $output;
        return $output;
      }
    } else {
      if (extension_loaded('pcntl') && function_exists('posix_kill') && posix_kill($pid, 0)) {
        $output = "Server is already running with PID $pid\n";
        echo $output;
        return $output;
      }
    }
  } else if (isset($_SERVER['SUPERVISOR_ENABLED']) && $_SERVER['SUPERVISOR_ENABLED'] == '1') {
    file_put_contents($pid_file, $pid = getmypid());
    return $output;
  }

  if (preg_match('/^cmd:\s*(shutdown|server\s*(shutdown))\s*(?:(-f))?(?=\r?\n$)?/si', $input, $matches)) {
    //signalHandler(SIGTERM); // $running = false;
    //$output .= "Shutting down... \n"; // Written by Barry Dick (2024)";
    $output .= str_replace('{{STATUS}}', 'Shutting down... PID=' . getmypid() . str_pad('', 15, " "), APP_DASHBOARD) . PHP_EOL;
    $running = false;
    //$output .= var_export($matches, true);
    if ($matches[3] == '-f') {
      signalHandler(SIGTERM) and unlink(PID_FILE); /* exit(1); */
    }
    return $output;
  } elseif (preg_match('/^cmd:\s*server\s*restart(?=\r?\n$)?/si', $input, $matches)) {
    signalHandler(SIGHUB); // SIGCHLD
    $output = str_replace('{{STATUS}}', 'Server Restarting... PID=' . getmypid() . str_pad('', 12, " "), APP_DASHBOARD) . PHP_EOL;
  } elseif (preg_match('/^cmd:\s*server\s*status(?=\r?\n$)?/si', $input)) {
    $output .= str_replace('{{STATUS}}', 'Server is running... PID=' . getmypid() . str_pad('', 12, " "), APP_DASHBOARD) . PHP_EOL;
  } elseif (preg_match('/^cmd:\s*chdir\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
    ini_set('log_errors', 'false');
    $output = "Changing directory to " . ($path = APP_PATH . APP_ROOT . trim($matches[1]) . '/');
    if ($path = realpath($path)) {
      $output = "Changing step 2 directory to $matches[1]";
      $resultValue = (function () use ($path) {
        $basePath = rtrim(APP_PATH . APP_ROOT, DIRECTORY_SEPARATOR); // Normalize base path
        $_GET['path'] = preg_replace(
          '#' . preg_quote($basePath, '#') . '#',
          '',
          $path
        );

        // Replace the escaped APP_PATH and APP_ROOT with the actual directory path
        if (realpath($_GET['path']) == realpath($basePath))
          $_GET['path'] = '';
        $tableValue = '';
        require_once 'public' . DIRECTORY_SEPARATOR . 'app.directory.php';
        //dd('Path: ' . $_GET['path'] . "\n", false);
        //dd(getcwd());
        ob_start();
        if (($tableValue = $tableGen()) != null) {
          ob_end_clean();
          return $tableValue; // $app['directory']['body'];
        }
      })();
      $output = $resultValue;
    }
    ini_set('log_errors', 'true');
  } elseif (preg_match('/^cmd:\s*edit\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
    ini_set('log_errors', 'false');
    //$output = ($file = rtrim(realpath(APP_PATH . ($_GET['path'] ?? '')), '/') . '/' . trim($matches[1])) ? file_get_contents($file) : "File not found: $file";

    $file = realpath(APP_PATH . ($_GET['path'] ?? '')) . DIRECTORY_SEPARATOR . trim($matches[1]);

    if (file_exists($file) && is_file($file)) {
      $output = file_get_contents($file);
    } else {
      $output = "File not found test: $file";
      ini_set('log_errors', 'true'); // Ensure logging is enabled
      error_log($output);
    }

  } elseif (preg_match('/^cmd:\s*server\s*backup(?=\r?\n$)?/si', $input, $matches)) {
    //$input = trim($input);
    $output = '';

    $file = APP_PATH . APP_BASE['database'] . 'source_code.json';

    // Retrieve file metadata using stat()
    $fileStats = stat($file);

    // Extract the last modification time from the stat results
    $statMtime = $fileStats['mtime']; // filemtime(__FILE__);

    // Clear the file status cache
    clearstatcache(true, $file);

    // Clear OPcache if enabled
    //if (function_exists('opcache_invalidate')) opcache_invalidate(__FILE__, true);

    // Get the current date (without time)
    $currentDate = date('Y-m-d');

    // Get the modification date of the file (without time)
    $fileModDate = date('Y-m-d', $statMtime);

    // Compare the dates
    if ($fileModDate !== $currentDate) {
      // Run your code only if the file was not modified today
      $output = "File was modified on: " . date('F d Y H:i:s', $statMtime) . "\n";
      require_once 'public' . DIRECTORY_SEPARATOR . 'idx.product.php';

      $files = get_required_files();
      $baseDir = APP_PATH;
      $organizedFiles = [];
      $directoriesToScan = [];

      // Collect directories from the list of files
      foreach ($files as $file) {
        $relativePath = str_replace($baseDir, '', $file);
        $directory = dirname($relativePath);
        if (!in_array($directory, $directoriesToScan))
          $directoriesToScan[] = $directory;

        // Add the relative path to the organizedFiles array if it is a .php file and not already present
        if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles))
          $organizedFiles[] = $relativePath;
        elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'htaccess' && !in_array($relativePath, $organizedFiles))
          $organizedFiles[] = $relativePath;
      }

      // Add non-recursive scanning for the root baseDir for *.php files
      $rootPhpFiles = glob("{$baseDir}{*.php,.env.bck,.gitignore,.htaccess,*.md,LICENSE,*.js,composer.json,package.json,settings.json}", GLOB_BRACE);
      foreach ($rootPhpFiles as $file) {
        if (is_file($file)) {
          $relativePath = str_replace($baseDir, '', $file);
          // Add the relative path to the array if it is a .php file and not already present
          if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles)) {
            if ($relativePath == 'composer-setup.php')
              continue;
            $organizedFiles[] = $relativePath;
          } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'bck' && !in_array($relativePath, $organizedFiles)) {
            if (preg_match('/^\.env\.bck/', $relativePath))
              $organizedFiles[] = $relativePath;
          } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'gitignore' && !in_array($relativePath, $organizedFiles)) {
            $organizedFiles[] = $relativePath;
          } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'htaccess' && !in_array($relativePath, $organizedFiles)) {
            $organizedFiles[] = $relativePath;
          } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'md' && !in_array($relativePath, $organizedFiles)) {
            $organizedFiles[] = $relativePath;
          } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'LICENSE' && !in_array($relativePath, $organizedFiles)) {
            $organizedFiles[] = $relativePath;
          } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'js' && !in_array($relativePath, $organizedFiles)) {
            $organizedFiles[] = $relativePath;
          } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'json' && !in_array($relativePath, $organizedFiles)) {
            $organizedFiles[] = $relativePath;
          }
        }
      }

      // Scan the specified directories
      scanDirectories($directoriesToScan, $baseDir, $organizedFiles);

      // Display the results
      $sortedArray = customSort($organizedFiles);

      $output = var_export($sortedArray, true);

      $json = "{\n";
      while ($path = array_shift($sortedArray)) {
        $json .= match ($path) {
          '.env.bck' => (function () use ($path, $sortedArray) {
              return '".env" : ' . json_encode(file_get_contents($path)) . (end($sortedArray) != $path ? ',' : '') . "\n";
            })(),
          default => '"' . $path . '" : ' . json_encode(file_get_contents($path)) . (end($sortedArray) != $path && !empty($sortedArray) ? ',' : '') . "\n",
        };
      }
      $json .= "}\n";

      /*

            $json = "{\n";  // Display the sorted array

            //dd($path);

            while ($path = array_shift($sortedArray)) {
              $json .= match ($path) {
                '.env.bck' => '".env" : ' . json_encode(file_get_contents($path)) . (end($sortedArray) != $path ? ',' : '') . "\n",
                default => '"' . $path . '" : ' . json_encode(file_get_contents($path)) . (end($sortedArray) != $path && !empty($sortedArray) ? ',' : '') . "\n",
              };
            }
            $json .= "}\n";
      */

      //die($path);
      // Read and sanitize the `.env` file contents
      $envContents = $json;
      $sanitizedContents = preg_replace(
        '/' . $_ENV['GITHUB']['OAUTH_TOKEN'] . '/m',
        '***REDACTED***',
        $envContents
      );

      file_put_contents(APP_PATH . APP_BASE['database'] . 'source_code.json', $sanitizedContents, LOCK_EX);

      signalHandler(SIGTERM);
      // Update the file's modification time if necessary (or other actions)
      // touch($file); // Optional, to update the modification time to current time
    } else {
      $output = "File was already modified today.\n";
    }

    /**/
  } elseif (preg_match('/^cmd:\s*app(\s*connected)?(?=\r?\n$)?/si', $input)) {
    $output = var_export(APP_IS_ONLINE, true);
  } elseif (preg_match('/^cmd:\s*composer\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
    //$output = shell_exec($matches[1]);

    require_once APP_PATH . APP_BASE['config'] . 'composer.php';

    $proc = proc_open(
      'env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ') . "composer $matches[1]",
      [
        ["pipe", "r"],
        ["pipe", "w"],
        ["pipe", "w"]
      ],
      $pipes
    );

    [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    $output = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");
    $output .= "\n" . (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ') . "composer $matches[1]";
    /**/
  } elseif (preg_match('/^cmd:\s*(composer(\s+.*|))(?=\r?\n$)?/si', $input, $matches)) { // ?(?=\r?\n$) // ?
    $cmd = $matches[1]; // $input
    $output = var_export($matches, true);
    //$output = 'test ' . trim(shell_exec($cmd));
    //$output .= " $input";
  } elseif (preg_match('/^cmd:\s*git\s*(.*)(?=\r?\n$)?/si', $input, $gitMatches)) {
    //$output = shell_exec($matches[1]);

    require_once APP_PATH . APP_BASE['config'] . 'git.php';

    $parsedUrl = parse_url($_ENV['GIT']['ORIGIN_URL']);

    //$output[] = $command = $_POST['cmd'] . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" https://' . $_ENV['GITHUB']['OAUTH_TOKEN'] . '@' . $parsedUrl['host'] . $parsedUrl['path'] . '.git';

    $output = 'www-data@localhost:' . getcwd() . '# ' . $command = ((stripos(PHP_OS, 'WIN') === 0) ? '' : APP_SUDO . '-u www-data ') . (defined('GIT_EXEC') ? GIT_EXEC : 'git') . ' ' . trim($gitMatches[1]) . ' https://' . $_ENV['GITHUB']['OAUTH_TOKEN'] . '@' . $parsedUrl['host'] . $parsedUrl['path'] . '.git';

    // (is_dir($path = APP_PATH . APP_ROOT . '.git') || APP_PATH . APP_ROOT != APP_PATH ? ' --git-dir="' . $path . '" --work-tree="' . dirname($path) . '"': '' ) 

    /*//$err =  */

    $proc = proc_open(
      $command,
      [
        ["pipe", "r"],
        ["pipe", "w"],
        ["pipe", "w"]
      ],
      $pipes
    );

    if (is_resource($proc)) {
      // Read stdout and stderr
      $stdout = stream_get_contents($pipes[1]);
      fclose($pipes[1]);


      $stderr = stream_get_contents($pipes[2]);
      fclose($pipes[2]);

      // Close the process and get the exit code
      $exitCode = proc_close($proc);

      // Construct the output string
      //$output = '';

      if (!empty($stdout)) {
        $output .= "\n$stdout";
      }

      if (!empty($stderr)) {
        $output .= " Error: $stderr";
      }

      if ($exitCode !== 0) {
        $output .= " Exit Code: $exitCode";
      }

      // Debugging info for the final executed command
      //$output .= "\n" . /*(stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ') . */ "git $gitMatches[1]";
/*
        socket_write($clientSocket, $stdout);
        return $output;
*/
    } else {
      $output .= "Failed to open process.";
    }


    //$output = var_export(shell_exec('ls'), true); 

    //[$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    //$output = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");
    //$output .= "\n" . /*(stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ')*/ . "git $gitMatches[1]";
/**/
  } elseif (preg_match('/^cmd:\s*server\s*(variables|)(?=\r?\n$)?/si', $input)) {
    $output = var_export($_SERVER, true);
  } elseif (preg_match('/^cmd:\s*(add\s*notification)(?=\r?\n$)?/si', $input)) {
    $output = 'Added notification...';
    $manager->addNotification(new Notification('notifyUser2', true, 20));
  } elseif (preg_match('/^cmd:\s*(include|require)\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
    //var_dump(getcwd() . "/$matches[2]");
    require_once trim($matches[2]);
    $output = "Including/Requiring... $matches[2]";
    var_dump(get_required_files());
  } elseif (preg_match('/^cmd:\s*(notifications)(?=\r?\n$)?/si', $input)) {
    $output = 'Notification(s)...';
    $output .= $manager->getNotifications();
  } elseif (preg_match('/^cmd:\s*(date(\?|)|what\s+is\s+the\s+date(\?|))(?=\r?\n$)?/si', $input)) {
    $output = 'The date is: ' . date('Y-m-d');
  } elseif (preg_match('/^cmd:\s*(get\s+defined\s+constants)(?=\r?\n$)?/si', $input)) {
    $output = var_export(get_defined_constants(true)['user'], true);
  } elseif (preg_match('/^cmd:\s*(get\s+(required|included)\s+files)(?=\r?\n$)?/si', $input)) {
    $output = var_export(get_required_files(), true);
  } elseif (preg_match('/cmd:\s+(.*)(?=\r?\n$)?/s', $input, $matches)) { // cmd: composer update
    $cmd = $matches[1];
    $output = 'input: ' . json_encode($input) . " cmd: $cmd";
    //$output = trim(shell_exec(/*$cmd*/ 'echo $PWD'));
    //$output .= 'input: ' . json_encode($input) . " cmd: $cmd";
  } else {
    // Process the request and send a response
    $output = "Hello, client! Your input was: $input";

    global $manager;

    // Append notification output to the response
    $output .= "\n{$manager->getNotificationsOutput()}";
  }
  /*
      if ($cmd = $matches[1] ?? NULL) {
        $proc=proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ')  $cmd,
        [
          ["pipe", "r"],
          ["pipe", "w"],
          ["pipe", "w"]
        ],
        $pipes);
        [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
        $output = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (!isset($exitCode) && $exitCode == 0 ? NULL : " Exit Code: $exitCode");

        //$output = shell_exec($cmd);
      }
  */
  //$_POST['cmd'] = $cmd;

  //require_once('public' . DIRECTORY_SEPARATOR . 'app.console.php');

  //ob_start(); // Start output buffering    ob_end_flush();

  error_log("Client [Output]: " . (strlen($output) > 50 ? substr($output, 0, 20) . '...' : $output));
  echo "Client [Output]: " . /*(strlen($output) > 50 ? substr($output, 0, 20) . "...\n" : $output)*/ $output;

  return $output;
}

//die(var_dump(stream_get_wrappers()));

$running = true;

$lastExecutionTime = 0;
$interval = 10;

//use Logger; // use when not using composer
//use Shutdown;

Logger::init();

/**
 * Summary of checkFileModification
 * @throws \Exception
 * @return string
 */
function checkFileModification()
{
  global $socket, $stream, $running;

  $file = __FILE__;

  // Retrieve file metadata using stat()
  $fileStats = stat($file);

  // Extract the last modification time from the stat results
  $statMtime = $fileStats['mtime']; // filemtime(__FILE__);

  // Clear the file status cache
  clearstatcache(true, $file);

  // Clear OPcache if enabled
  //if (function_exists('opcache_invalidate')) opcache_invalidate(__FILE__, true);

  //$input = trim($input);
  $output = '';

  if ($statMtime !== filemtime($file)) {
    $output = 'Server has been updated. Please restart. ' . date('F d Y H:i:s', $statMtime) . ' != ' . date('F d Y H:i:s', filemtime($file)) . "\n"
      . 'Server is running... PID=' . getmypid() . "\n"
      . 'Server backup... ';

    $output .= clientInputHandler('cmd: server backup' . "\r" . PHP_EOL);

    //$lastModifiedTime = filemtime(__FILE__);
/*
      if (extension_loaded('sockets')) {
        socket_write($socket, $output);
        socket_close($socket);
      } elseif (get_resource_type($socket) == 'stream') {
        fwrite($socket, $output);
        fclose($socket);
      }
*/
    error_log("Client [Output]: $output");
    echo "Client [Output]: $output\n";

  }
  return $output;
}

// Define some example notification functions
function notifyUser1()
{
  echo "Notifying user 1...\n";
}
function notifyUser2()
{
  echo "Notifying user 2...\n";
}

// Create Notification objects
$notification1 = new Notification('notifyUser1', true, 300); // Repeatable every 5 minutes
//$notification2 = new Notification('notifyUser2', false); // One-time notification

// Create NotificationManager and add notifications
$manager = new NotificationManager();
$manager->addNotification($notification1);
//$manager->addNotification($notification2);

// server.php
// composer require cboden/ratchet
// Exception: Interface "Ratchet\MessageComponentInterface" not found

//get_included_files()[0] ==
if (PHP_SAPI === 'cli')
  if (is_dir($path = __DIR__ . APP_BASE['vendor'] . 'cboden' . DIRECTORY_SEPARATOR . 'ratchet') && !empty(glob($path)) && file_exists(__DIR__ . APP_BASE['vendor'] . 'autoload.php')) {
    error_log('Creating a websocket server...');
    require_once __DIR__ . DIRECTORY_SEPARATOR . APP_BASE['vendor'] . 'autoload.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . APP_BASE['config'] . 'classes' . DIRECTORY_SEPARATOR . 'class.websocketserver.php';
  } else
    try {
      error_log('Creating a stream/socket server...');

      /**
       * Creates a server socket.
       * @param mixed $address
       * @param mixed $port
       * @throws \Exception
       * @return bool|resource|Socket
       */
      function createServerSocket($address, $port)
      {
        is_file($pid_file = PID_FILE ?? APP_PATH . 'server.pid') && $pid = (int) file_get_contents($pid_file);

        echo str_replace('{{STATUS}}', 'Starting...\'' . basename(__FILE__) . '\' PID=' . ($pid ?? getmypid()) . str_pad('', 8, " "), APP_DASHBOARD) . PHP_EOL;

        // Create the socket
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
          throw new Exception("Error: Unable to create server socket: " . socket_strerror(socket_last_error()));
        }

        // Set the SO_REUSEADDR option
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        // Bind the socket to the address and port
        if (@socket_bind($socket, $address, $port) === false) {
          throw new Exception("Could not bind to socket: " . socket_strerror(socket_last_error()));
        }

        // Listen for incoming connections
        if (@socket_listen($socket) === false) {
          throw new Exception("Could not listen on socket: " . socket_strerror(socket_last_error()));
        }

        // Set the socket to non-blocking mode
        if ($_ENV['PHP']['SOCKET_BLOCK']) {
          // Blocking mode with a 5-second timeout
          socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 5, "usec" => 0]);
        } else {
          // Non-blocking mode
          socket_set_nonblock($socket);
        }
        $blockingMode = $_ENV['PHP']['SOCKET_BLOCK'] ? "blocking" : "non-blocking";
        echo PHP_EOL . "Connected to Server: $address:$port (The socket is in $blockingMode mode.)\n";

        /*
            $timeout = socket_get_option($socket, SOL_SOCKET, SO_RCVTIMEO);

            if ($timeout['sec'] === 0 && $timeout['usec'] === 0) {
              echo "The socket is in non-blocking mode.\n";
            } else {
              echo "The socket is in blocking mode.\n";
            }
        */
        return $socket;
      }

      if (extension_loaded('sockets')) {
        // Create server socket using the Sockets extension
        $socket = createServerSocket(SERVER_HOST, SERVER_PORT);
        echo '(Socket) ';
      } else { // if (get_resource_type($socket) == 'stream')
        // Create a TCP/IP server socket using stream_socket_server
        if (!$socket = @stream_socket_server('tcp://' . SERVER_HOST . ':' . SERVER_PORT, $errno, $errstr)) {
          echo "Error: Unable to create server socket: $errstr ($errno)\n";
          unlink(PID_FILE);
          throw new Exception("Could not create server socket: $errstr ($errno)");
        }

        echo in_array('tcp', stream_get_wrappers()) ? '' : "TCP was NOT found in stream_get_wrappers()\n";

        // Display server information  
        echo '(' . get_resource_type($socket) . ') ';
      }

      is_file($pid_file = PID_FILE ?? APP_PATH . 'server.pid') && $pid = (int) file_get_contents($pid_file);
      echo 'Server started on ' . SERVER_HOST . ':' . SERVER_PORT . ' (' . PHP_OS . ") PID=" . ($pid ?? getmypid()) . "\n\n";

      /**
       * Manages the scheduled task based on a given interval.
       */
      function manageScheduledTask(&$lastExecutionTime, $interval)
      {
        static $previous_count = 0;
        if (time() - $lastExecutionTime >= $interval) {
          touch(__FILE__);

          if (strpos($fileContents = file_get_contents($filePath = __FILE__), "#!/usr/bin/env php\r") === 0) {
            // Replace Windows-style line ending (\r\n) with Unix-style (\n)
            $fileContents = preg_replace("/^#!\/usr\/bin\/env php[\r\n]*/", "#!/usr/bin/env php\n", $fileContents);

            // Save the fixed content back to the file
            file_put_contents($filePath, $fileContents);

            echo "Line ending fixed for $filePath.\n";
          }
          // Execute the scheduled task
          checkFileModification();

          // Update the last execution time
          $lastExecutionTime = time();
          $previous_count = null;
        } else {
          // Display remaining time until next execution

          // Calculate remaining time
          $remaining_time = $interval - (time() - $lastExecutionTime);

          // Display remaining time only if it has changed
          if ($remaining_time !== $previous_count) {
            echo "$remaining_time - $interval seconds left\n";
            $previous_count = $remaining_time; // Update previous count
          }
          //sleep(1);
        }
      }

      /**
       * Handles socket connections.
       *
       * @param resource|Socket $socket The socket to handle.
       */
      function handleSocketConnection($socket)
      {
        $error = socket_last_error($socket);

        // Check if the socket is closed
        if ($error == 10057) { // WSAENOTCONN: "Socket is not connected"
          echo "Socket is closed\n";
          socket_close($socket);
          return; // Exit if socket is closed
        }

        // Accept new client connection
        $client = @socket_accept($socket);

        if ($client === false) {
          handleSocketAcceptError($socket);
          return;
        }

        // If a client connected successfully, handle it
        if ($client instanceof Socket) {
          handleSocketClientConnection($client);
        } else {
          echo "Socket connection closed or invalid.\n";
        }
      }

      /**
       * Handles stream connections.
       *
       * @param resource $socket The stream socket to handle.
       */
      function handleStreamConnection($socket)
      {
        $stream = @stream_socket_accept($socket, -1);

        if ($stream) {
          handleStreamClientConnection($stream);
        } else {
          echo "Failed to accept stream connection.\n";
        }
      }

      /**
       * Handles an incoming client connection for streams.
       */
      function handleStreamClientConnection($stream)
      {
        // Retrieve client information
        $clientName = stream_socket_get_name($stream, true);
        [$clientAddress, $clientPort] = explode(':', $clientName);
        echo "Client connected (stream): IP {$clientAddress}:{$clientPort} Port \n";

        // Read and process client data
        $data = processClientData($stream);

        // Send processed data back to the client
        sendDataToClient($stream, $data);

        // Close the client connection
        fclose($stream);
        echo "Client Disconnected: IP {$clientAddress}:{$clientPort} Port \n\n";
      }

      /**
       * Handles an incoming client connection for sockets.
       */
      function handleSocketClientConnection($client)
      {
        // Get the client's address and port
        if (socket_getpeername($client, $clientAddress, $clientPort)) {
          echo "\nClient connected: IP $clientAddress, Port $clientPort\n";
        } else {
          $errorCode = socket_last_error($client);
          $errorMsg = socket_strerror($errorCode);
          echo "\nUnable to get client's address and port: [$errorCode] $errorMsg\n";
          return;
        }

        // Prepare the data
        $jsonData = json_encode(processClientData($client));
        $length = strlen($jsonData);

        try {

          $data = "hello\n"; // Include termination sequence if needed
          $bytesWritten = socket_write($client, $data); // dd(, false);

          if ($bytesWritten === false) {
            echo "Failed to write to socket: " . socket_strerror(socket_last_error($client));
          }

          //usleep(100000); // Delay in microseconds (100ms)
          // Send the length first so the client knows how much data to expect
//        if (!$client || socket_write($client, "$length\n") === false) {
//          dd('Failed to send message length', false);
//          throw new Exception('Failed to send message length');
//        }

          // Split and send the actual data
          if ($data != '') {
            $data = str_split($jsonData, 1024);
            foreach ($data as $chunk) {
              if ($client instanceof Socket && $client && is_resource($client)) {
                if (socket_write($client, $chunk) === false) {
                  dd('Socket write failed', false);
                  throw new Exception('Socket write failed');
                } else
                  socket_write($client, "\r\n"); // Send end-of-transmission signal
              }
            }
          }

        } catch (Exception $e) {
          echo "Error: " . $e->getMessage() . "\n";
        } finally {
          // Close the client connection
          socket_close($client);
          echo "\nClient Disconnected 123: IP $clientAddress, Port $clientPort\n\n";
        }
      }

      /**
       * Processes the data received from the client.
       */
      function processClientData($client)
      {

        if (extension_loaded('sockets')) {
          $clientMsg = socket_read($client, 1024);
        } elseif (get_resource_type($client) == 'stream') { // Do not load first. -Barry
          $clientMsg = fread($client, 1024);
        }

        // Process the client message
        $data = clientInputHandler($clientMsg);

        // Normalize line breaks
        $dataArray = explode("\n", $data ?? '');
        return implode("\n", $dataArray);
      }

      /**
       * Sends data back to the client in chunks.
       */
      function sendDataToClient($stream, $data)
      {
        $totalLength = strlen($data);
        $written = 0;
        $chunkSize = 1024; // Adjust chunk size as needed

        while ($written < $totalLength) {
          $writeLength = min($chunkSize, $totalLength - $written);
          $result = fwrite($stream, substr($data, $written, $writeLength));

          if ($result === false) {
            // Handle write errors here (e.g., log it, retry, etc.)
            break;
          }

          $written += $result;
        }

        fflush($stream); // Ensure all data is sent
      }

      //  if (!is_resource($socket) || get_resource_type($socket) !== ('stream' ?? 'socket'))
//    echo "Socket is not connected or is invalid." . PHP_EOL;
      while ($running) {
        // Perform scheduled tasks
        $manager->checkNotifications();
        manageScheduledTask($lastExecutionTime, $interval);

        //dd(get_resource_type($socket)); // var_export($socket, true) == Socket::__set_state(array( )) != resource
        //PHP7 >= (is_resource($socket) && get_resource_type($socket) === 'Socket')

        /*
        if ($socket instanceof Socket && is_resource($socket)) {
          handleSocketConnection($socket);
        } elseif (is_resource($socket) && get_resource_type($socket) === 'stream') {
          handleStreamConnection($socket);
        } else {
          echo "Socket is not connected or is invalid.\n";
          break;
        }
*/
        if ($socket instanceof Socket /* && is_resource($socket)*/) {
          //$error = socket_last_error($socket);

          // Check if the socket is closed
          if (isset($error) && $error == 10057) { // WSAENOTCONN: "Socket is not connected"
            echo "Socket is closed\n";
            socket_close($socket);
            break; // Exit loop if socket is closed
          }

          //if (!is_resource($socket)) { // Additional check
          //  echo "Socket is no longer a valid resource.\n";
          //break;
          //}

          if ($socket === null) {
            echo "DEBUG: Socket is NULL.\n";
          }

          if ($socket === false) {
            echo "DEBUG: Socket is FALSE.\n";
          }

          // Accept new client connection
          $client = @socket_accept($socket);

          if ($client === false) {
            $error = socket_last_error($socket);

            if ($error && $error != SOCKET_EAGAIN && $error != SOCKET_EWOULDBLOCK) {
              echo "Socket error during accept: " . socket_strerror($error) . "\n";
              socket_clear_error($socket);
              break;
            }

            // No connection available, sleep and continue
            usleep(100000);
            continue;
          }

          // If a client connected successfully, handle it
          if ($client instanceof Socket) {
            handleSocketClientConnection($client);
          } else {
            echo "Socket connection closed or invalid.\n";
          }
        } elseif (get_resource_type($socket) == 'stream') {
          // Accept incoming client connections using streams
          if ($stream = @stream_socket_accept($socket, -1)) {
            handleStreamClientConnection($stream);
          }
        } else {
          echo "Socket is not connected or is invalid.\n";
        }

        // Dispatch any pending signals
        if (stripos(PHP_OS, 'LIN') === 0) {
          pcntl_signal_dispatch();
        }

        // Sleep for a short time to prevent busy-waiting
        usleep(100000); // 100 ms = 0.1 s
      }

      // Close the server socket
      if (extension_loaded('sockets')) {
        $buffer = '';
        (!$socket) and socket_close($socket);
      } elseif (get_resource_type($socket) == 'stream') {
        (!$socket) and fclose($socket);
      }

      echo "Socket closed.\n";
      /*
          $bytesReceived = @socket_recv($socket, $buffer, 1024, MSG_DONTWAIT);
          if ($bytesReceived === false) {
            $error = socket_last_error($socket);

            if ($error == SOCKET_EAGAIN || $error == SOCKET_EWOULDBLOCK) {
                // No data available, continue without blocking
                usleep(100000); // Optional: add a slight delay to prevent CPU overuse
                continue;
            } else {
                // Handle unexpected errors
                echo "Socket error: " . socket_strerror($error) . "\n";
                socket_clear_error($socket);
                break;
            }
          } elseif ($bytesReceived === 0) {
            // Peer has closed the connection
            echo "Socket connection closed by peer." . PHP_EOL;
          } else {
            socket_close($socket);
          }
        } elseif (get_resource_type($socket) == 'stream') {
          fclose($socket);
        }
      */
    } catch (Exception $e) {
      Logger::error($e->getMessage());
      Shutdown::triggerShutdown($e->getMessage());
    } finally {
      //require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class.sockets.php';
      // if (stripos(PHP_OS, 'LIN') === 0) Sockets::handleLinuxSocketConnection(true); else
      if (stripos(PHP_OS, 'WIN') === 0)
        Sockets::handleWindowsSocketConnection();
      /**/
      if (isset($socket) && is_resource($socket) && !empty($socket)) {
        if (get_resource_type($socket) == 'stream') {
          fclose($socket);
        } elseif (extension_loaded('sockets')) {
          socket_close($socket);
        }
      }
    }

register_shutdown_function(function () {
  if (PHP_SAPI === 'cli' && is_file(PID_FILE)) {
    unlink(PID_FILE);
    echo 'Unlinking PID file... ' . PID_FILE . PHP_EOL;
  }

  if (get_required_files()[0] == __FILE__)
    echo str_replace(
      '{{STATUS}}',
      'Server has stopped... PID=' . getmypid() . str_pad('', 10, " "),
      APP_DASHBOARD
    ) . PHP_EOL;
});