<?php

class SocketException extends Exception {}

class Sockets
{
    private $socket;

    public function __construct()
    {
        try {
            if (!isset($_SERVER['SOCKET']) && !$this->socket = $this->openSocket(APP_HOST, APP_PORT)) {
                $this->handleSocketConnection();
            } elseif (APP_SELF === APP_PUBLIC) {
                $this->handleClientRequest();
            }
        } catch (SocketException $e) {
            Logger::error($e->getMessage());
            Shutdown::triggerShutdown($e->getMessage());
        }
    }

    private function openSocket($host, $port, $timeout = 5)
    {
        $socket = $this->createSocket($host, $port, $timeout);
    
        if ($socket === false) {
            throw new SocketException("Unable to open socket: $errstr", $errno);
        }
    
        return $socket;
    }
    
    private function createSocket($host, $port, $timeout)
    {
        // Your implementation of creating a socket goes here
        return @fsockopen($host, $port, $errno, $errstr, $timeout);
    }

    private function handleSocketConnection()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->handleWindowsSocketConnection();
        } elseif (stripos(PHP_OS, 'LIN') === 0) {
            $this->handleLinuxSocketConnection();
        }
    }
    public static function handleLinuxSocketConnection()
    {
        $pidFile = (defined('APP_PATH') ? APP_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR) . 'server.pid';

        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            if (posix_kill($pid, 0)) posix_kill($pid, SIGTERM) and unlink($pidFile);
        }
        shell_exec('nohup php server.php > /dev/null 2>&1 &');
    }
    public static function handleWindowsSocketConnection()
    {
        $pidFile = (defined('APP_PATH') ? APP_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR) . 'server.pid';

        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\" >NUL", $output, $status);

            if ($status === 0) shell_exec("taskkill /PID $pid /F") and unlink($pidFile);
        }
        pclose(popen((defined('APP_PATH') ? APP_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR) . 'bin/psexec.exe -d C:\xampp\php\php.exe -f ' . APP_PATH . 'server.php', "r"));
    }

    private function handleClientRequest()
    {
        global $errors, $output;
        $errors['server-1'] = "Connected to Server: " . APP_HOST . ':' . APP_PORT . "\n";

        // Send a message to the server
        $errors['server-2'] = 'Client request: ' . $message = "cmd: " . $_SERVER["SCRIPT_FILENAME"] . "\n";

        fwrite($this->socket, $message);
        $output[] = (!isset($_POST['cmd']) ?: $_POST['cmd']) . ': ';

        // Read response from the server
        while (!feof($this->socket)) {
            $response = fgets($this->socket, 1024);
            $errors['server-3'] = "Server response: $response\n\n";
            //if (isset($output[end($output)])) $output[end($output)] .= trim($response);
            //else $output[] = trim($response);
        }
    }

    public function getSocket()
    {
        return $this->socket;
    }
    public function __destruct()
    {
        if (isset($this->socket)) {
            if (is_resource($this->socket)) {
                if (get_resource_type($this->socket) == 'stream') {
                    fclose($this->socket);
                } else {
                    socket_close($this->socket);
                }
            }
        }
    }
}

$socket = new Sockets();

if ($_SERVER['SOCKET'] = $socket->getSocket()) // realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'server.php')
  if ($_SERVER['SOCKET'] === false) dd($errors['APP_SOCKET'] = 'Have you checked your server.php file lately?', false);
  elseif ($_SERVER['SOCKET']) {
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
  } else
    $errors['APP_SOCKET'] = ($_SERVER['SOCKET'] ?: 'Socket is unable to connect: ') . 'No server connection.' . "\n";

if (!$_SERVER['SOCKET']) {

  define('PID_FILE', /*getcwd() . */(!defined('APP_PATH') ? __DIR__ . DIRECTORY_SEPARATOR : APP_PATH ) . 'server.pid');

  if (file_exists(PID_FILE)) {
    $pid = (int) file_get_contents(PID_FILE);
    //unlink(PID_FILE);
    if (strpos(PHP_OS, 'WIN') === 0) {
      exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\" >NUL", $output, $status);
      if ($status !== 0) {
        $socket->handleWindowsSocketConnection();
        //error_log("Server is already running with PID $pid\n");
        //echo "Server is already running with PID $pid\n";
        //exit(1);
      }
    } else {
      if (!posix_kill($pid, 0)) {
        $socket->handleLinuxSocketConnection();
        //error_log("Server is already running with PID $pid\n");
        //echo "Server is already running with PID $pid\n";
        //exit(1);
      }
    }
  }

  var_dump($errors['APP_SOCKET'] = "Socket is not being created: Define \$_SERVER['SOCKET']");


}
