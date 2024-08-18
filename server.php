#!/usr/bin/env php
<?php
declare(strict_types=1, ticks=1); // First Line Only!

//exit(1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

ini_set('error_log', (!defined('APP_PATH') ? __DIR__ . DIRECTORY_SEPARATOR : APP_PATH)  . 'server.log' );
ini_set('log_errors', 'true');

define('PID_FILE', /*getcwd() . */ (!defined('APP_PATH') ? __DIR__ . DIRECTORY_SEPARATOR : APP_PATH) . 'server.pid');


strpos(PHP_OS, 'LIN') === 0 && !extension_loaded('posix') || !extension_loaded('pcntl')
  and die('posix && pcntl required. exiting');

!is_writable('/tmp')
  and die('must be able to write to /tmp to continue. exiting.');

//!file_exists($file = posix_getpwuid(posix_getuid())['dir'].'/.aws/credentials')
//  and die('an aws credentials file is required. exiting file=' . $file);

if (file_exists(PID_FILE) && $pid = (int) file_get_contents(PID_FILE)) {
  //unlink(PID_FILE);
  if (strpos(PHP_OS, 'WIN') === 0) {
    exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\" >NUL", $output, $status);
    if ($status === 0) {
      error_log("Server is already running with PID $pid\n");
      echo "Server is already running with PID $pid\n";
      exit(1);
    }
  } else {
    if (posix_kill($pid, 0)) {
      error_log("Server is already running with PID $pid\n");
      echo "Server is already running with PID $pid\n";
      exit(1);
    }
  }
} else if (isset($_SERVER['SUPERVISOR_ENABLED']) && $_SERVER['SUPERVISOR_ENABLED'] == '1')
  touch(PID_FILE) and exit(1);

file_put_contents(PID_FILE, $pid = getmypid());

/**
 * Set the title of our script that ps(1) sees
 */
if (!cli_set_process_title($title = basename(__FILE__))) {
  echo "Unable to set process title for PID $pid...\n";
  exit(1);
} else {
  echo "The process title '$title' has been set for your process!\n";
  strpos(PHP_OS, 'LIN') === 0 and cli_set_process_name($title);
  function cli_set_process_name($title)
  {
    file_put_contents('/proc/'.getmypid().'/comm',$title);
  }
}

set_time_limit(0);

//dd(get_defined_constants()); // get_required_files()

$address = APP_HOST ?? '0.0.0.0';
$port = APP_PORT ?? 8080;

!empty($parsed_args = parseargs($argv))
  and print("Argv(s): " . var_export($parsed_args, true) . "\n");

  // ps aux | grep server.php
  // kill -SIGTERM <PID>
  // kill -SIGINT <PID>
  // kill -SIGSTOP <PID>
  // kill -SIGCONT <PID>
  
  // [1]+  Stopped                 php server.php
  // kill -SIGKILL / -9 <PID>
  // [1]+  Killed                  php server.php

  // Signal handler to gracefully shutdown
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
        unlink(PID_FILE);
        if (isset($socket) && is_resource($stream))
          switch (get_resource_type($socket)) {
            case 'stream':
              fwrite($stream, 'Shuting down server... PID=' . getmypid());
              fclose($socket);
              break;
            default:
              socket_write($stream, 'Shuting down server... PID=' . getmypid());
              socket_close($socket);
              break;
          }
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

function clientInputHandler($input) {
    global $socket, $stream, $running, $manager;

    if ($input == '') return;
    error_log('Client [Input]: ' . trim($input));
    echo 'Client [Input]: ' . trim($input) . "\n";
    //$input = trim($input);
    $output = '';

    if (preg_match('/^cmd:\s*(shutdown|restart|server\s*(shutdown|restart))\s*?(?:(-f))(?=\r?\n$)?/si', $input, $matches)) { 
      //signalHandler(SIGTERM); // $running = false;
      $output = var_export($matches, true);
      if ($matches[3] == '-f')
        signalHandler(SIGTERM);
    } elseif (preg_match('/^cmd:\s*server\s*status(?=\r?\n$)?/si', $input)) {
      $output = 'Server is running... PID=' . getmypid();
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
          ob_end_clean();
          return $tableGen();// $app['directory']['body'];
        })();
        $output = $resultValue;
      }

    } elseif (preg_match('/^cmd:\s*edit\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
      ini_set('log_errors', 'false');
      $output = file_get_contents(APP_PATH . APP_ROOT . $_GET['path'] . '/' . trim($matches[1]));
    } elseif (preg_match('/^cmd:\s*server\s*backup(?=\r?\n$)?/si', $input, $matches)) {
      require_once 'public/index.php';

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
    } elseif (preg_match('/^cmd:\s*server(\s*variables|)(?=\r?\n$)?/si', $input)) {
      $output = var_export($_SERVER, true);
    } elseif (preg_match('/^cmd:\s*(add\s*notification)(?=\r?\n$)?/si', $input)) {    
      $output = 'Added notification...';
      $manager->addNotification(new Notification('notifyUser2', true, 300));
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
    }
/*
    if ($cmd = $matches[1] ?? NULL) {
      $proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . $cmd,
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
$interval = 300;

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
      if ($stream)
        switch (get_resource_type($socket)) {
          case 'stream':
              fwrite($stream, $output);
              fclose($stream);
              break;
          default:
              socket_write($stream, $output);
              socket_close($stream);
              break;
      }
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
if (is_dir($path = __DIR__ . APP_BASE['vendor'] . 'cboden' . DIRECTORY_SEPARATOR . 'ratchet') && !empty(glob($path)) && file_exists(__DIR__ . APP_BASE['vendor'] . 'autoload.php')) {
  error_log('Creating a websocket server...');
  require_once __DIR__ . DIRECTORY_SEPARATOR . APP_BASE['vendor'] . 'autoload.php';
  require_once __DIR__ . DIRECTORY_SEPARATOR . APP_BASE['config'] . 'classes' . DIRECTORY_SEPARATOR . 'class.websocketserver.php';
} else
  try {
    error_log('Creating a stream/socket server...');

  // Create a TCP/IP server socket
    if (!$socket = @stream_socket_server('tcp://' . $address . PATH_SEPARATOR . $port, $errno, $errstr)) {
      echo "Error: Unable to create server socket: $errstr ($errno)\n";
      unlink(PID_FILE);
      throw new Exception("Could not create server socket: $errstr ($errno)");
    }
    
    // Set the socket to non-blocking mode
    stream_set_blocking($socket, false);

    // Check if the stream wrapper for TCP is available
    echo in_array('tcp', stream_get_wrappers()) ? '' : "TCP was NOT found in stream_get_wrappers()\n";
    echo '(' . get_resource_type($socket) . ") Server started on $address:$port (" . PHP_OS . ") PID=$pid \n\n";
  
  /**
   * Manages the scheduled task based on a given interval.
   */
  function manageScheduledTask(&$lastExecutionTime, $interval) {
      if (time() - $lastExecutionTime >= $interval) {
          // Execute the scheduled task
          checkFileModification();
          
          // Update the last execution time
          $lastExecutionTime = time();
      } else {
          // Display remaining time until next execution
          echo $interval - (time() - $lastExecutionTime) . " seconds left\n";
      }
  }
  
  /**
   * Handles an incoming client connection.
   */
  function handleClientConnection($stream) {
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
   * Processes the data received from the client.
   */
  function processClientData($clientMsg) {
    // Process the client message
    $data = clientInputHandler($clientMsg) . "\n";
    
    // Additional processing or notifications can be added here
    global $manager;
    $manager->checkNotifications();
    
    // Normalize line breaks
    $dataArray = explode("\n", $data);
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
      
      // Accept incoming client connections and handle them
      if ($stream = @stream_socket_accept($socket, -1)) {
          handleClientConnection($stream);
      }
  
      // Dispatch any pending signals
      pcntl_signal_dispatch();
      
      // Sleep for a short time to prevent busy-waiting
      usleep(100000); // 100 ms = 0.1 s
    }


    if (extension_loaded('sockets')) {
      // Create server socket using the Sockets extension
      $socket = createServerSocket($address, $port);
      echo "(Socket) Server started on $address:$port\n";
    
  /**
   * Creates a server socket.
   */
  function createServerSocket($address, $port) {
    // Create the socket
    if (!$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
        throw new Exception("Error: Unable to create server socket: " . socket_strerror(socket_last_error()));
    }

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

/**
 * Handles an incoming client connection using sockets.
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
    global $manager;

    // Process the client message
    $data = clientInputHandler($clientMsg);

    // Append notification output to the response
    $data .= "\n" . $manager->getNotificationsOutput();

    // Normalize line breaks
    $dataArray = explode("\n", $data);
    return implode("\n", $dataArray);
}

      while ($running) {
          // Check notifications
          $manager->checkNotifications();
  
          // Accept incoming client connections
          if ($client = @socket_accept($socket)) {
              handleSocketClientConnection($client);
          }
  
          // Dispatch any pending signals
          pcntl_signal_dispatch();
  
          // Sleep for a short time to prevent busy-waiting
          usleep(100000); // 100 ms = 0.1 s
      }
  
      socket_close($socket);
    } else {
      echo "Neither sockets or TCP stream wrapper are available.\n";
      unlink(PID_FILE);
      exit(1);
    }


  } catch (Exception $e) {
    //Logger::error($e->getMessage());
    //Shutdown::triggerShutdown($e->getMessage());
  } finally {
    if (isset($socket) && is_resource($socket))
      switch (get_resource_type($socket)) {
        case 'stream':
          fclose($socket);
          break;
        default:
          socket_close($socket);
          break;
      }
  }

unlink(PID_FILE);
die('EOF');