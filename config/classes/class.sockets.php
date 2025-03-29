<?php

//if (!in_array(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'constants.php', get_required_files()))
//  die($errors['CONSTANTS'] = 'Missing config' . DIRECTORY_SEPARATOR . 'constants.php from required files');
//require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config.php';

// Ensure this file is loaded and php.php is not yet loaded
require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'php.php';
class SocketException extends Exception
{
}

/**
 * Summary of Sockets
 */
class Sockets
{
    private static $socket;
    private $errstr;
    private $errno;
    private static $instance = null;

    /**
     * Summary of __construct
     * @throws \SocketException
     */
    public function __construct()
    {
        try {
            self::$socket = $this->openSocket(SERVER_HOST, SERVER_PORT);
            //dd([APP_SELF, APP_PATH_SERVER], false);
            // Attempt to open a socket if it's not already set in $_SERVER
            if (!$this->isSocketAvailable()) {
                // Handle socket connection if unavailable
                $this->handleSocketConnection();
            } elseif (APP_SELF !== APP_PATH_SERVER) {

                // If the app is not running on the server path, handle client requests
                $this->handleClientRequest($_POST['cmd'] ?? null);

                if (!self::$socket) {
                    throw new SocketException("Failed to connect to socket at " . SERVER_HOST . ':' . SERVER_PORT);
                }
            }
        } catch (SocketException $e) {
            // Optionally handle exceptions here, log them, or trigger shutdown
            die($e->getMessage());
        }
    }

    /**
     * Summary of isSocketAvailable
     * @param mixed $socket
     * @return bool
     */
    public static function isSocketAvailable($socket = null)
    {
        /*
                if ($socket !== null) {
                    return isset($_SERVER['SOCKET']) && is_resource($_SERVER['SOCKET']);
                } else {
                    return isset(Self::$socket) && is_resource(Self::$socket);
                }*/

        // Check for passed socket, or use the class property
        $socketToCheck = $socket ?? $_SERVER['SOCKET'] ?? self::$socket;

        // Return true if the socket is set and is a valid resource
        return isset($socketToCheck) && is_resource($socketToCheck);
    }

    /**
     * Summary of openSocket
     * @param mixed $host
     * @param mixed $port
     * @param mixed $timeout
     * @return bool|resource
     */
    private function openSocket($host, $port, $timeout = 5)
    {
        self::$socket = $this->createSocket($host, $port, $timeout);
        if (self::$socket === false) {
            return false;
        }
        return self::$socket; // false;
    }

    /**
     * Summary of createSocket
     * @param mixed $host
     * @param mixed $port
     * @param mixed $timeout
     * @return bool|resource
     */
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
            $errors['SOCKET'] = "[$this->errno] $this->errstr - Could not connect to $host:$port\n";
            error_log("[$this->errno] $this->errstr - Could not connect to $host:$port");
            return false;
        }

        return $socket;
    }

    /**
     * Summary of handleClientRequest
     * @param mixed $command
     * @return void
     */
    public function handleClientRequest($command = null)
    {
        global $errors, $output;

        // Check if socket is available
        if (!$this->isSocketAvailable()) {
            $errors['SOCKET'] = 'Socket is not available.';
            return;
        }

        // Use provided command or default to the current script's filename
        $message = "cmd: " . ($command ?? $_SERVER["SCRIPT_FILENAME"]) . "\n";

        // Log the server connection and client request
        $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";
        $errors['server-2'] = "Client request: $message";

        // Send the message to the server
        fwrite(self::$socket, $message);

        // Add to output array (either POSTed command or the constructed message)
        $output[] = $_POST['cmd'] ?? $message;

        // Read the server's response
        while (!feof(self::$socket)) {
            $response = fgets(self::$socket, 1024);
            if ($response !== false) {
                $errors['server-3'] = "Server response: $response\n";
                $output[] = trim($response); // Store trimmed response in output array
            }
        }
    }

    public static function handleSocketConnection($start = false)
    {
        // if (is_file($pid_file = (!defined('APP_PATH') ? __DIR__ . DIRECTORY_SEPARATOR : APP_PATH ) . 'server.pid')) {}
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            Sockets::handleWindowsSocketConnection();
        } elseif (stripos(PHP_OS, 'LIN') === 0) {
            Sockets::handleLinuxSocketConnection($start);
        }
    }

    /**
     * Summary of handleLinuxSocketConnection
     * @param mixed $start
     * @return void
     */
    public static function handleLinuxSocketConnection($start = false)
    {
        $pidFile = (defined('APP_PATH') ? APP_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR) . 'server.pid';
        $serverExec = defined('APP_PATH_SERVER') ? APP_PATH_SERVER : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'server.php';
        $phpExec = /*APP_SUDO . '-u root ' .*/ PHP_EXEC ?? 'php';
        $temp = tempnam('/tmp', 'server');
        $sudo = 'sudo -u root ';
        $path = APP_PATH;

        if (is_file($file = "{$temp}") || @touch($file)) // unlink($file)
            file_put_contents(
                $file,
                <<<END
<?php
\$runServer = function (\$serverExec, \$phpExec) {
    chdir('$path');
    \$temp = '$temp';
    if (!is_executable(\$serverExec)) {
        echo 'Running (New) Server via PHP' . PHP_EOL;
        shell_exec('nohup ' . '$sudo' . escapeshellcmd("\$phpExec \$serverExec") . ' > /dev/null 2>&1 &');
        return;
    }

    echo 'Running (New) Server' . PHP_EOL;

    \$tty = trim(shell_exec('tty'));
\$tty = (\$tty !== 'not a tty') ? \$tty : '/dev/pts/1'; // Default to 'pts/1' if not a TTY
    echo "Current TTY: \$tty" . PHP_EOL;
    // Define command

    echo "Command: " . (\$command = \$serverExec) . PHP_EOL;

    shell_exec('$sudo' . escapeshellcmd("\$command") . ' > ' . \$tty . ' 2>&1 &');
/*
    // Set up descriptors based on TTY availability
    \$descriptors = [
        0 => ["pipe", "r"],  // stdin
        1 => ["file", "\$tty", "w"],  // stdout
        2 => ["file", "\$tty", "a"]   // stderr
    ];

    // Execute process with proc_open
    \$process = proc_open(\$command, \$descriptors, \$pipes);
    if (is_resource(\$process)) {
        fclose(\$pipes[0]);  // Close stdin pipe
        \$return_value = proc_close(\$process);
        echo "Command finished with return value: \$return_value" . PHP_EOL;
    } else {
        echo "Failed to start the process." . PHP_EOL;
    }
*/
    echo "server.php has been started and output is redirected to \$tty." . PHP_EOL;

    unlink(\$temp);
    exit(1);
};

\$runServer('$serverExec', '$phpExec');
END
            );

        // Function to check if the server is already running
        $isServerRunning = function ($pidFile) {
            if (file_exists($pidFile)) {
                $pid = file_get_contents($pidFile);
                if (posix_kill($pid, 0)) {
                    return true; // Process is running
                } else {
                    unlink($pidFile); // Process not running, remove stale PID file
                }
            }
            return false; // Server not running
        };

        // Run the server using the preferred executable (either serverExec or php server.php)

        $runServer = function ($serverExec, $phpExec) use ($temp, $sudo) {
            if (is_file($temp)) {
                dd(shell_exec($sudo . escapeshellcmd("$phpExec $temp") . ' > /dev/null 2>&1 &'));
                if (unlink($temp) && is_file($temp))
                    dd("$temp should have been deleted?");

                dd('did it work?', false);
                return;
            }
            if (!is_executable($serverExec)) {
                echo 'Running (New) Server via PHP' . PHP_EOL;
                shell_exec('nohup ' . escapeshellcmd("$phpExec $serverExec") . ' > /dev/null 2>&1 &');
                return;
            }
            //include_once $temp; $runServer($serverExec, $phpExec);


        };

        // Main logic
        if ($isServerRunning($pidFile)) {
            return; // Server is already running, exit early
        }

        if ($start && !$isServerRunning($pidFile)) {
            // Start the server if not running or explicitly requested
            $runServer($serverExec, $phpExec);
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

        //(is_resource($process)) and file_put_contents($pidFile, $pid = @proc_get_status($process)['pid']); // Exception

        $process = ($psexec = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin/psexec.exe')) ? pclose(popen($psexec . ' -d C:\xampp\php\php.exe -f ' . APP_PATH . 'server.php', "r")) : pclose(popen('php -f ' . APP_PATH . 'server.php', "r"));

        try {
            if (is_resource($process)) {
                $status = (stripos(PHP_OS, 'WIN') === 0) ?: @proc_get_status($process); // The '@' suppresses any warnings/errors
                if ($status && isset($status['running'])) {
                    if (isset($status['pid']))
                        file_put_contents($pidFile, $status['pid']);
                } else {
                    throw new Exception("Process is not running or status could not be fetched.");
                }
            } else {
                throw new Exception("Invalid process resource.");
            }
        } catch (Exception $e) {
            error_log("Failed to retrieve process ID: " . $e->getMessage());
            // Optionally handle the error or retry
        }
    }

    public static function getInstance()
    {
        // Keep the singleton instance in a static variable
        static $instance = null;

        // If no instance exists, create a new one
        if ($instance === null) {
            $instance = new self();  // Reinitialize and open socket
        } else {
            // Check if the socket is available and open it if necessary
            if (!self::isSocketAvailable()) {
                // Reopen the socket if it is not available
                $_SERVER['SOCKET'] = self::$socket = $instance->openSocket(SERVER_HOST, SERVER_PORT);
            }
        }

        return $instance; // Return the singleton instance
    }

    public function getSocket()
    {
        return self::$socket;
    }

    public function __destruct()
    {
        if ($this->isSocketAvailable()) {
            //fclose(self::$socket);
            self::$socket = null; // Ensure the socket is null after closing
        }
    }
}


if (!isset($_SERVER['SOCKET']) || empty($_SERVER['SOCKET'])) {
    $socketInstance = Sockets::getInstance(); // new Sockets();

    // Check if the socket was initialized properly
    if (isset($socketInstance) && is_a($socketInstance, Sockets::class) && is_resource($_SERVER['SOCKET'] = $socketInstance->getSocket())) {

        !defined('PID_FILE') and define('PID_FILE', /*getcwd() .*/ (!defined('APP_PATH') ? __DIR__ . DIRECTORY_SEPARATOR : APP_PATH) . 'server.pid');

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
                    echo "Server is already running with PID $pid\n";
                    //exit(1);
                }
            }
        }


    } else {
        //die('Socket connection failed.');
        $errors['APP_SOCKET'] = 'Have you checked your server.php file lately?';


        //if (APP_DEBUG) { 
        //var_dump(trim($errors['APP_SOCKET']));
        //}
    }
} else {
    $errors['APP_SOCKET'] = "Socket is not being created: Define \$_SERVER['SOCKET']\n";
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