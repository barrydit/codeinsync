<?php

// Ensure this file is loaded and php.php is not yet loaded
//require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'php.php';
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
    private static Logger $logger;

    /**
     * Summary of __construct
     * @throws \SocketException
     */
    public function __construct(Logger $logger)
    {

        require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'constants.url.php';
        defined('SERVER_HOST') or define('SERVER_HOST', 'localhost' ?? '0.0.0.0');
        defined('SERVER_PORT') or define('SERVER_PORT', 9000); // 8080
        try {
            self::$logger = $logger; // Initialize logger with verbose mode
            self::$socket = $this->openSocket(SERVER_HOST, SERVER_PORT);
            //dd([APP_SELF, APP_PATH_SERVER], false);
            // Attempt to open a socket if it's not already set in $_SERVER
            if (!self::isSocketAvailable()) {
                // Handle socket connection if unavailable
                self::handleSocketConnection();
            }

            if (APP_SELF !== APP_PATH_SERVER) {

                // If the app is not running on the server path, handle client requests
                $this->handleClientRequest($_POST['cmd'] ?? null);

                if (!self::isSocketAvailable()) {
                    self::$logger->log("Socket not available after reconnect attempt. Failed to connect to socket at " . SERVER_HOST . ":" . SERVER_PORT); // throw new SocketException()
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
                    return isset($GLOBALS['runtime']['socket']) && is_resource($GLOBALS['runtime']['socket']);
                } else {
                    return isset(Self::$socket) && is_resource(Self::$socket);
                }*/

        // Check for passed socket, or use the class property
        $socketToCheck = $socket ?? $GLOBALS['runtime']['socket'] ?? self::$socket;

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

    private function createSocket(string $host, int $port, int $timeout = 5)
    {
        $oldHandler = set_error_handler(fn($errno, $errstr) => true);
        $socket = fsockopen($host, $port, $this->errno, $this->errstr, $timeout);
        restore_error_handler();

        if (!$socket) {
            error_log("Socket Error [$this->errno]: $this->errstr");
        }

        return $socket;
    }

    /**
     * Summary of handleClientRequest
     * @param mixed $command
     * @return void
     */
    public function handleClientRequest(?string $command): array
    {
        if (!self::isSocketAvailable()) {
            self::$logger->log('Socket not available.', 'INFO'); // throw new RuntimeException
            return []; // Return an empty array if the socket is not available
        }

        $message = "cmd: " . ($command ?? $_SERVER["SCRIPT_FILENAME"]) . "\n";
        fwrite(self::$socket, $message);

        $responses = [];
        while (!feof(self::$socket)) {
            $line = fgets(self::$socket, 1024);
            if ($line !== false) {
                $responses[] = trim($line);
            }
        }

        return $responses;
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
        require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'constants.runtime.php';
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
                $pid = (int) file_get_contents($pidFile);
                if (is_int($pid) && posix_kill($pid, 0))
                    return true; // Process is running
                else
                    unlink($pidFile); // Process not running, remove stale PID file

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
        dd('Sockets is trying to start a server process, tell it no!');
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

    public static function getInstance(?Logger $logger = null): self
    {
        if (self::$instance === null) {
            if (!$logger)
                throw new \RuntimeException('Logger must be provided on first call to Sockets::getInstance()');
            self::$instance = new self($logger);
        } elseif (!self::isSocketAvailable()) {
            $GLOBALS['runtime']['socket'] = self::$socket = self::$instance->openSocket(SERVER_HOST, SERVER_PORT);
        }

        return self::$instance;
    }

    public function getSocket()
    {
        return self::$socket;
    }

    private function __clone()
    {
    }
    public function __wakeup()
    {
    }

    public function __destruct()
    {
        if ($this->isSocketAvailable()) {
            //fclose(self::$socket);
            self::$socket = null; // Ensure the socket is null after closing
        }
    }
}



//if (isset($socket) && is_a($socket, Sockets::class) && is_resource($GLOBALS['runtime']['socket'] = $socket->getSocket())) // realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'server.php')

//  if (!isset($GLOBALS['runtime']['socket']) || empty($GLOBALS['runtime']['socket'])) {


//  }
//  elseif (is_resource($GLOBALS['runtime']['socket'])) {
/*
    $errors['server-1'] = "Connected to Server: " . APP_HOST . "\n";

    // Send a message to the server
    $errors['server-2'] = 'Client request: ' . $message = "cmd: composer update 123 " . $_SERVER["SCRIPT_FILENAME"] . "\n";

    fwrite($GLOBALS['runtime']['socket'], $message);

    // Read response from the server
    while (!feof($GLOBALS['runtime']['socket'])) {
      $response = fgets($GLOBALS['runtime']['socket'], 1024);
      $errors['server-3'] = "Server response [1]: $response\n";
      if (!empty($response)) break;
    }

    // Close the connection
    //fclose($GLOBALS['runtime']['socket']);
*/
//  } else
//    $errors['APP_SOCKET'] = ($GLOBALS['runtime']['socket'] ?: 'Socket is unable to connect: ') . 'No server connection.' . "\n";