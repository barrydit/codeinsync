#!/usr/bin/env php
<?php 
//declare(strict_types=1, ticks=1); // First Line Only!

!defined('APP_PATH') and define('APP_PATH', __DIR__ . DIRECTORY_SEPARATOR);

require_once APP_PATH . DIRECTORY_SEPARATOR . 'index.php';

ini_set('error_log', APP_PATH . 'server.log');
ini_set('log_errors', 'true');

!defined('PID_FILE') and define('PID_FILE', /*getcwd() . */ APP_PATH . 'server.pid');

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
}
file_put_contents(PID_FILE, $pid = getmypid());


//!file_exists($file = posix_getpwuid(posix_getuid())['dir'].'/.aws/credentials')
//  and die('an aws credentials file is required. exiting file=' . $file);
if (PHP_SAPI === 'cli' && stripos(PHP_OS, 'LIN') === 0 ) {
  (!extension_loaded('posix') || !extension_loaded('pcntl')) and die('posix && pcntl required. exiting');

!is_writable('/tmp')
  and die('must be able to write to /tmp to continue. exiting.');

/**
 * Set the title of our script that ps(1) sees
 */
if (!cli_set_process_title($title = basename(__FILE__))) {
  echo "Unable to set process title for PID $pid...\n";
  exit(1);
} else {
  echo "The process title '$title' has been set for your process!\n";

  /**
   * Summary of cli_set_process_name
   * @param mixed $title
   * @throws Exception
   * @return void
   */
  function cli_set_process_name($title)
  {
    file_put_contents('/proc/'.getmypid().'/comm',$title);
  }
}
stripos(PHP_OS, 'LIN') === 0 and cli_set_process_name($title);
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
  function signalHandler($signal) {
    global $running, $socket, $server, $stream;
    switch ($signal) {
      case SIGCHLD:
        while (pcntl_waitpid(-1, $status, WNOHANG) > 0) {
          // Reap child processes
        }
        break;
      case SIGTERM:
      case SIGINT:
        echo 'Shutting down server... PID=' . getmypid() . "\n";
        Logger::error('Shutting down server... PID=' . getmypid());
        //fclose($server); 
        $file = PID_FILE;
        !is_file($file)?: unlink($file);
        
        if (isset($socket) && is_resource($socket)) {
          socket_close($socket);
        } elseif (is_resource($stream) && get_resource_type($stream) == 'stream') {
          fclose($stream);
        }
/*
        if (isset($socket) && is_resource($stream)) {
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
    exit(1);
  }

  pcntl_async_signals(true); // Turn on asynchronous signal handling

  // Register signal handlerfor SIGCHLD, SIGTERM, and SIGINT
  //pcntl_signal(SIGCHLD, 'signalHandler'); // hangs on sockets with empty cmd: on loop
  pcntl_signal(SIGTERM, 'signalHandler');
  pcntl_signal(SIGINT, 'signalHandler');

} else if (PHP_SAPI === 'cli' && stripos(PHP_OS, 'WIN') === 0 ) {
  (!extension_loaded('sockets')) and die('sockets required. exiting');
}


/**
 * Summary of safe_chdir
 * @param mixed $path
 * @throws \Exception
 * @return void
 */
function safe_chdir($path) {
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
defined('SERVER_PORT') or define('SERVER_PORT', '8080'); // 9000

!empty($parsed_args = parseargs())
  and print("Argv(s): " . var_export($parsed_args, true) . "\n");

function clientInputHandler($input) {
    global $socket, $stream, $running, $manager;

    if ($input == '') return;
    error_log('Client [Input]: ' . trim($input));
    echo 'Client [Input]: ' . trim($input) . "\n";
    //$input = trim($input);
    $output = '';

    $pid = file_get_contents($pidFile = APP_PATH . 'server.pid');

    if (preg_match('/^cmd:\s*(shutdown|restart|server\s*(shutdown|restart))\s*?(?:(-f))(?=\r?\n$)?/si', $input, $matches)) { 
      //signalHandler(SIGTERM); // $running = false;
      $output = "Shutting down... Written by Barry Dick (2024)";
      //$output .= var_export($matches, true);
      if ($matches[3] == '-f') { signalHandler(SIGTERM) and unlink($pidFile); /* exit(1); */ }
    } elseif (preg_match('/^cmd:\s*server\s*status(?=\r?\n$)?/si', $input)) {
      $output = "Server is running... PID=$pid";
    } elseif (preg_match('/^cmd:\s*chdir\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
      ini_set('log_errors', 'false');
      $output = "Changing directory to " . ($path = APP_PATH . APP_ROOT . trim($matches[1]) . '/');
      if ($path = realpath($path)) {
        $output = "Changing step 2 directory to $matches[1]";
        $resultValue = (function() use ($path) {
          // Replace the escaped APP_PATH and APP_ROOT with the actual directory path
          if (realpath($_GET['path'] = preg_replace('/' . preg_quote(APP_PATH . APP_ROOT, '/') . '/', '', $path)) == realpath(APP_PATH . APP_ROOT))
            $_GET['path'] = '';

          //dd('Path: ' . $_GET['path'] . "\n", false);
          ob_start();
          require 'public/app.directory.php';
          $tableValue = $tableGen();
          ob_end_clean();
          return $tableValue; // $app['directory']['body'];
        })();
        $output = $resultValue;
      }
      ini_set('log_errors', 'true');
    } elseif (preg_match('/^cmd:\s*edit\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
      ini_set('log_errors', 'false');
      $output = ($file = rtrim(realpath(APP_PATH . APP_ROOT . $_GET['path'] ?? ''), '/') . '/' . trim($matches[1])) ? file_get_contents($file) : "File not found: $file";
      ini_set('log_errors', 'true');
    } elseif (preg_match('/^cmd:\s*server\s*backup(?=\r?\n$)?/si', $input, $matches)) {
      require_once 'public/idx.product.php';

      $files = get_required_files();
      $baseDir = APP_PATH;
      $organizedFiles = [];
      $directoriesToScan = [];
      
      // Collect directories from the list of files
      foreach ($files as $file) {
          $relativePath = str_replace($baseDir, '', $file);
          $directory = dirname($relativePath);
          if (!in_array($directory, $directoriesToScan)) {
              $directoriesToScan[] = $directory;
          }
          // Add the relative path to the organizedFiles array if it is a .php file and not already present
          if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles)) {
            $organizedFiles[] = $relativePath;
          } else if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'htaccess' && !in_array($relativePath, $organizedFiles)) {
            $organizedFiles[] = $relativePath;
          }
      }
            
      // Add non-recursive scanning for the root baseDir for *.php files
      $rootPhpFiles = glob("{$baseDir}{*.php,.env.bck,.gitignore,.htaccess,*.md,LICENSE,*.js,composer.json,package.json,settings.json}", GLOB_BRACE);
      foreach ($rootPhpFiles as $file) {
          if (is_file($file)) {
              $relativePath = str_replace($baseDir, '', $file);
              // Add the relative path to the array if it is a .php file and not already present
              if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles)) {
                  if ($relativePath == 'composer-setup.php') continue;
                  $organizedFiles[] = $relativePath;
              } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'bck' && !in_array($relativePath, $organizedFiles)) {
                if (preg_match('/^\.env\.bck/', $relativePath)) $organizedFiles[] = $relativePath;
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

      $json = "{\n";  // Display the sorted array

      while ($path = array_shift($sortedArray)) {
        $json .= match ($path) {
          '.env.bck' => '".env" : ' . json_encode(file_get_contents($path)) . (end($sortedArray) != $path ? ',' : '') . "\n",
          default => '"' . $path . '" : ' . json_encode(file_get_contents($path)) . (end($sortedArray) != $path && !empty($sortedArray) ? ',' : '') . "\n",
        };
      }
      $json .= "}\n";

      file_put_contents(APP_PATH . APP_BASE['var'] . 'source_code.json', $json, LOCK_EX);
/**/
    } elseif (preg_match('/^cmd:\s*app(\s*connected)?(?=\r?\n$)?/si', $input)) {
      $output = var_export(APP_CONNECTED, true);
    } elseif (preg_match('/^cmd:\s*composer\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
      //$output = shell_exec($matches[1]);

      $proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ') . "composer $matches[1]",
      [
        ["pipe", "r"],
        ["pipe", "w"],
        ["pipe", "w"]
      ],
      $pipes);

      [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
      $output = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");
      $output .= "\n" . (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ') . "composer $matches[1]";

    } elseif (preg_match('/^cmd:\s*server(\s*variables|)(?=\r?\n$)?/si', $input)) {
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
    } elseif (preg_match('/^cmd:\s*(composer(\s+.*|))(?=\r?\n$)?/si', $input, $matches)) { // ?(?=\r?\n$) // ?
      $cmd = $matches[1]; // $input
      $output = var_export($matches, true);
      //$output = 'test ' . trim(shell_exec($cmd));
      //$output .= " $input";
    } elseif (preg_match('/cmd:\s+(.*)(?=\r?\n$)?/s', $input, $matches)) { // cmd: composer update
      $cmd = $matches[1];
      $output = trim(shell_exec(/*$cmd*/ 'echo $PWD'));
      $output .= " cmd: $cmd";
    } else {
      // Process the request and send a response
      $output = "Hello, client! Your input was: $input";

      global $manager;

      // Append notification output to the response
      $output .= "\n" . $manager->getNotificationsOutput();
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
      list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
      $output = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (!isset($exitCode) && $exitCode == 0 ? NULL : " Exit Code: $exitCode");

      //$output = shell_exec($cmd);
    }
*/
    //$_POST['cmd'] = $cmd;

    //require_once('public/app.console.php');
    error_log("Client [Output]: " . (strlen($output) > 50 ? substr($output, 0, 20) . '...' : $output));

    echo "Client [Output]: " . (strlen($output) > 50 ? substr($output, 0, 20) . "...\n" : $output);
    return $output;
}

//die(var_dump(stream_get_wrappers()));

$running = true;

$lastExecutionTime = 0;
$interval = 10;

//use Logger; // use when not using composer
//use Shutdown;

Logger::init();

function checkFileModification() {
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
      . 'Server backup...' . "\n";

      $output .= clientInputHandler('cmd: server backup' . PHP_EOL);

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

      signalHandler(SIGTERM);

      return $output;
  }
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
  function createServerSocket($address, $port) {
    // Create the socket
    if (!$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
        throw new Exception("Error: Unable to create server socket: " . socket_strerror(socket_last_error()));
    }

    // Set the SO_REUSEADDR option
    socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

    // Bind the socket to the address and port
    if (!@socket_bind($socket, $address, $port)) {
        throw new Exception("Could not bind to socket: " . socket_strerror(socket_last_error()));
    }

    // Listen for incoming connections
    if (!@socket_listen($socket)) {
        throw new Exception("Could not listen on socket: " . socket_strerror(socket_last_error()));
    }

    // Set the socket to non-blocking mode
    socket_set_nonblock($socket);

    return $socket;
  }

  if (extension_loaded('sockets')) {
    // Create server socket using the Sockets extension
    $socket = createServerSocket(SERVER_HOST, SERVER_PORT);

    echo '(Socket) ';
  } elseif (get_resource_type($socket) == 'stream') {
    // Create a TCP/IP server socket using stream_socket_server
    if (!$socket = @stream_socket_server('tcp://' . SERVER_HOST . ':' . SERVER_PORT, $errno, $errstr)) {
      echo "Error: Unable to create server socket: $errstr ($errno)\n";
      unlink(PID_FILE);
      throw new Exception("Could not create server socket: $errstr ($errno)");
    }

    echo in_array('tcp', stream_get_wrappers()) ? '' : "TCP was NOT found in stream_get_wrappers()\n";
    // Set the socket to non-blocking mode
    stream_set_blocking($socket, false);  
    // Display server information  
    echo '(' . get_resource_type($socket) . ') ';
  } 
  echo 'Server started on ' . SERVER_HOST . ':' . SERVER_PORT . ' (' . PHP_OS . ") PID=$pid\n\n";

  /**
   * Manages the scheduled task based on a given interval.
   */
  function manageScheduledTask(&$lastExecutionTime, $interval) {
      static $previous_count = 0;
      if (time() - $lastExecutionTime >= $interval) {
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
   * Handles an incoming client connection for streams.
   */
  function handleStreamClientConnection($stream) {
      // Retrieve client information
      $clientName = stream_socket_get_name($stream, true);
      [$clientAddress, $clientPort] = explode(':', $clientName);
      echo "Client connected: IP {$clientAddress}:{$clientPort} Port \n";

      // Read and process client data
      $data = processClientData(fread($stream, 1024));

      // Send processed data back to the client
      sendDataToClient($stream, $data);

      // Close the client connection
      fclose($stream);
      echo "Client Disconnected: IP {$clientAddress}:{$clientPort} Port \n\n";
  }

  /**
   * Handles an incoming client connection for sockets.
   */
  function handleSocketClientConnection($client) {
      // Get the client's address and port
      if (socket_getpeername($client, $clientAddress, $clientPort)) {
          echo "Client connected: IP $clientAddress, Port $clientPort\n";
      } else {
          $errorCode = socket_last_error($client);
          $errorMsg = socket_strerror($errorCode);
          echo "Unable to get client's address and port: [$errorCode] $errorMsg\n";
      }

      // Read and process client data
      $data = processClientData(socket_read($client, 1024));

      // Send processed data back to the client
      socket_write($client, $data);

      // Close the client connection
      socket_close($client);
      echo "Client Disconnected: IP $clientAddress, Port $clientPort\n\n";
  }

  /**
   * Processes the data received from the client.
   */
  function processClientData($clientMsg) {

      // Process the client message
      $data = clientInputHandler($clientMsg);


      // Normalize line breaks
      $dataArray = explode("\n", $data ?? '');
      return implode("\n", $dataArray);
  }

  /**
   * Sends data back to the client in chunks.
   */
  function sendDataToClient($stream, $data) {
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

  while ($running) {
      // Check notifications at the start of each loop
      $manager->checkNotifications();

      // Print the time left until the next scheduled execution
      manageScheduledTask($lastExecutionTime, $interval);

      if (extension_loaded('sockets')) {
        // Accept incoming client connections using sockets
        if ($client = @socket_accept($socket)) {
          handleSocketClientConnection($client);
        }
      } elseif (get_resource_type($socket) == 'stream') {
        // Accept incoming client connections using streams
        if ($stream = @stream_socket_accept($socket, -1)) {
          handleStreamClientConnection($stream);
        }
      } 

      // Dispatch any pending signals
      (stripos(PHP_OS, 'LIN') === 0) and pcntl_signal_dispatch();

      // Sleep for a short time to prevent busy-waiting
      usleep(100000); // 100 ms = 0.1 s
  }

  // extension_loaded('sockets') && socket_close($socket);
  if (extension_loaded('sockets')) {
    socket_close($socket);
  } elseif (get_resource_type($socket) == 'stream') {
    fclose($socket);
  }
} catch (Exception $e) {
  //Logger::error($e->getMessage());
  //Shutdown::triggerShutdown($e->getMessage());
} finally {
  require_once APP_PATH . 'config/classes/class.sockets.php';

  if (stripos(PHP_OS, 'LIN') === 0) Sockets::handleLinuxSocketConnection(true);
  else if (stripos(PHP_OS, 'WIN') === 0) Sockets::handleWindowsSocketConnection();

  print "Written by Barry Dick (2024)\n";

  if (isset($socket) && is_resource($socket)) {
    if (extension_loaded('sockets')) {
      socket_close($socket);
    } elseif (get_resource_type($socket) == 'stream') {
      fclose($socket);
    } 
  }
}

(PHP_SAPI !== 'cli' || !is_file(PID_FILE) ?: unlink(PID_FILE)) or die('EOF');
