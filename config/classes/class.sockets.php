<?php

//if (!in_array(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'constants.php', get_required_files()))
//  die($errors['CONSTANTS'] = 'Missing config/constants.php from required files');

class SocketException extends Exception {}

class Sockets
{
    private $socket;
    private $errstr;
    private $errno;
    private static $instance = null;

    public function __construct()
    {
        try {
            $this->socket = $this->openSocket(SERVER_HOST, SERVER_PORT);

            //die(var_dump($this->socket));
            // Attempt to open a socket if it's not already set in $_SERVER
            if (!isset($_SERVER['SOCKET']) || !$this->isSocketAvailable()) {
                $this->handleSocketConnection();
            } elseif (APP_SELF !== APP_PATH_SERVER) {
                // If the app is not running on the server path, handle client requests
                $this->handleClientRequest();

                //$this->socket = fsockopen(SERVER_HOST, SERVER_PORT, $this->errno, $this->errstr, 5);

                if (!$this->socket) {
                    throw new SocketException("Failed to connect to socket at " . SERVER_HOST . ':' . SERVER_PORT);
                }
                // $_SERVER['SOCKET'] = $this->socket; // Store the socket in $_SERVER for global access
            }
        } catch (SocketException $e) {
            // Optionally handle exceptions here, log them, or trigger shutdown
            die($e->getMessage());
        }

    }

    private function isSocketAvailable()
    {
        return isset($this->socket) && is_resource($this->socket);
    }

    private function openSocket($host, $port, $timeout = 5)
    {
        $socket = $this->createSocket($host, $port, $timeout);
        if ($socket === false) {
            return false;
        }
        return $socket; // false;
    }

    private function createSocket($host, $port, $timeout)
    {
        global $errors;
        // Error handling during socket creation
        //ob_start();
        $oldErrorHandler = set_error_handler(function ($errno, $errstr) {
            $this->errno = $errno;
            $this->errstr = $errstr;
            return true; // Prevent PHP's default error handling
        });

        $socket = fsockopen($host, $port, $this->errno, $this->errstr, $timeout);

        restore_error_handler(); // Restore the original error handler

        if ($socket === false) {
            $errors['SOCKET'] = "Error: [$this->errno] $this->errstr - Could not connect to $host:$port\n";
            error_log("Error: [$this->errno] $this->errstr - Could not connect to $host:$port");
            return false;
        }

        return $socket;
    }

    public function handleClientRequest($command = null)
    {
        global $errors, $output;

        if (!$this->isSocketAvailable()) {
            $errors['SOCKET'] = 'Socket not available.';
            return;
        }

        // Default command if not provided
        $message = "cmd: " . ($command ?? $_SERVER["SCRIPT_FILENAME"]) . "\n";

        $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";

        // Send a message to the server
        $errors['server-2'] = "Client request: $message";

        fwrite($this->socket, $message);
        $output[] = !empty($_POST['cmd']) ? $_POST['cmd'] : $message /*. ': '*/;

        // Read server response
        while (!feof($this->socket)) {
            $response = fgets($this->socket, 1024);
            if ($response !== false) {
                $errors['server-3'] = "Server response: $response\n";
                $output[] = trim($response);
            }
        }

    }

    private function handleSocketConnection()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->handleWindowsSocketConnection();
        } elseif (stripos(PHP_OS, 'LIN') === 0) {
            $this->handleLinuxSocketConnection();
        }
    }

    public static function handleLinuxSocketConnection($start = false)
    {
        $pidFile = (defined('APP_PATH') ? APP_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR) . 'server.pid';
        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            if (!posix_kill($pid, 0)) {
                unlink($pidFile);
            }
        }
        if ($start) {
            shell_exec('nohup php server.php > /dev/null 2>&1 &');
        }
    }

    public static function handleWindowsSocketConnection()
    {
        $pidFile = (defined('APP_PATH') ? APP_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR) . 'server.pid';
        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\" >NUL", $output, $status);
            if ($status === 0) {
                // Process is already running, return
                //shell_exec("taskkill /PID $pid /F") and unlink($pidFile);
                return;
            } else {
                // Process is not running, remove the PID file
                unlink($pidFile);
            }
        }
        // Start a new process and store the PID
        $process = popen((defined('APP_PATH') ? APP_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR) . 'bin/psexec.exe -d C:\xampp\php\php.exe -f ' . APP_PATH . 'server.php', "r");
        $pid = proc_get_status($process)['pid'];
        file_put_contents($pidFile, $pid);
    }

    public static function getInstance()
    {
        //if (self::$instance === null) {
        //    self::$instance = new Sockets();
        //}
        static $instance = null;

        if ($instance === null) {
            $instance = new self();  // Reinitialize and open socket
        } else {
            // Optionally, reopen socket if needed
            if (!$instance->isSocketAvailable()) {
                $_SERVER['SOCKET'] = $instance->socket = $instance->openSocket(SERVER_HOST, SERVER_PORT);
            }
        }

        return $instance; // self::$instance;
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function __destruct()
    {
        if ($this->isSocketAvailable()) {
            fclose($this->socket);
        }
    }
}


$socketInstance = Sockets::getInstance(); // new Sockets();

// Check if the socket was initialized properly
if (isset($socketInstance) && is_a($socketInstance, Sockets::class) && is_resource($_SERVER['SOCKET'] = $socketInstance->getSocket())) {
  if (!isset($_SERVER['SOCKET']) || empty($_SERVER['SOCKET'])) {
    !defined('PID_FILE') and define('PID_FILE', /*getcwd() .*/(!defined('APP_PATH') ? __DIR__ . DIRECTORY_SEPARATOR : APP_PATH ) . 'server.pid');

    if (file_exists(PID_FILE)) {
      $pid = (int) file_get_contents(PID_FILE);
      //unlink(PID_FILE);
      if (strpos(PHP_OS, 'WIN') === 0) {
        exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\" >NUL", $output, $status);
        if ($status !== 0) {
          Sockets::handleWindowsSocketConnection();
          //error_log("Server is already running with PID $pid\n");
          //echo "Server is already running with PID $pid\n";
          //exit(1);
        }
      } else {
        if (!posix_kill($pid, 0)) {
          Sockets::handleLinuxSocketConnection();
          //error_log("Server is already running with PID $pid\n");
          //echo "Server is already running with PID $pid\n";
          //exit(1);
        }
      }
    }


    die($errors['APP_SOCKET'] = 'Have you checked your server.php file lately?');
  }
} else {
    //die('Socket connection failed.');

    $errors['APP_SOCKET'] = "Socket is not being created: Define \$_SERVER['SOCKET']\n";
  
    //if (APP_DEBUG) { 
      //var_dump(trim($errors['APP_SOCKET']));
    //}
}


//if (isset($socket) && is_a($socket, Sockets::class) && is_resource($_SERVER['SOCKET'] = $socket->getSocket())) // realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'server.php')

//  if (!isset($_SERVER['SOCKET']) || empty($_SERVER['SOCKET'])) {


//  }
//  elseif (is_resource($_SERVER['SOCKET'])) {
/*
    $errors['server-1'] = "Connected to Server: " . APP_HOST . "\n";

    // Send a message to the server
    $errors['server-2'] = 'Client request: ' . $message = "cmd: composer update 123 " . $_SERVER["SCRIPT_FILENAME"] . "\n";

    fwrite($_SERVER['SOCKET'], $message);

    // Read response from the server
    while (!feof($_SERVER['SOCKET'])) {
      $response = fgets($_SERVER['SOCKET'], 1024);
      $errors['server-3'] = "Server response [1]: $response\n";
      if (!empty($response)) break;
    }

    // Close the connection
    //fclose($_SERVER['SOCKET']);
*/
//  } else
//    $errors['APP_SOCKET'] = ($_SERVER['SOCKET'] ?: 'Socket is unable to connect: ') . 'No server connection.' . "\n";