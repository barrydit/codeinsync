<?php

!defined('APP_PATH') and
    define('APP_PATH', realpath(__DIR__ /*. '..'*/) . DIRECTORY_SEPARATOR);

require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'auth.php';
/*if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['composer']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^composer\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'composer.php';
    if (isset($_POST['git']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^git\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'git.php';
    if (isset($_POST['npm']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^npm\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'npm.php';
    if (isset($_POST['python']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^python\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'python.php';
    if (isset($_POST['perl']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^perl\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'perl.php';
    //if (isset($_POST['php']) || preg_match('/^php\s*(:?.*)/i', $_POST['cmd'], $match))
    require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'php.php';
}*/
require_once APP_PATH . 'bootstrap.php';

require_once APP_PATH . APP_BASE['config'] . 'init.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'routes.php';