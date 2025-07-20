<?php

use App\Core\Registry;

if (PHP_SAPI !== 'cli' || (defined('FORCE_SOCKET') && FORCE_SOCKET)) {
    require_once __DIR__ . '/../bootstrap/bootstrap.cli.php';
    //require_once __DIR__ . '/../classes/class.sockets.php';

    if (!Registry::has('logger')) {
        Registry::set('logger', new Logger());
    }

    $logger = Registry::get('logger');

    if (!isset($GLOBALS['runtime']['socket']) || empty($GLOBALS['runtime']['socket'])) {
        $socketInstance = Sockets::getInstance($logger); // new Sockets();
        $errors['APP_SOCKET'] = '';
        // Check if the socket was initialized properly
        if (isset($socketInstance) && is_a($socketInstance, Sockets::class) && is_resource($GLOBALS['runtime']['socket'] = $socketInstance->getSocket())) {

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
                        $errors['APP_SOCKET'] = "Server is already running with PID $pid (classes/" . basename(__FILE__) . ")\n";
                        //exit(1);
                    }
                } else if (!posix_kill($pid, 0)) {
                    Sockets::handleLinuxSocketConnection();
                    //error_log("Server is already running with PID $pid\n");
                    $errors['APP_SOCKET'] = "Server is already running with PID $pid (classes/" . basename(__FILE__) . ")\n";
                    //echo "";
                    //exit(1);
                }
            }

        } else {
            //die('Socket connection failed.');
            $errors['APP_SOCKET'] .= "\n\tHave you checked your server.php file lately?\n";

            //if (APP_DEBUG) { 
            //var_dump(trim($errors['APP_SOCKET']));
            //}
        }
    } else {
        $errors['APP_SOCKET'] = "Socket is not being created: Define \$GLOBALS['runtime']['socket']\n";
    }
}