<?php

!defined('APP_PATH') and
    define('APP_PATH', realpath(__DIR__ /*. '..'*/) . DIRECTORY_SEPARATOR);

require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'auth.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    //if (isset($_POST['php']) || preg_match('/^php\s*(:?.*)/i', $_POST['cmd'], $match))
    require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'php.php';
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
    /* if (preg_match('/^ruby\s*(:?.*)/i', $_POST['cmd'], $match))
       require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'ruby.php';
     if (preg_match('/^go\s*(:?.*)/i', $_POST['cmd'], $match))
       require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'go.php';
     if (preg_match('/^java\s*(:?.*)/i', $_POST['cmd'], $match))
       require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'java.php';
     if (preg_match('/^csharp\s*(:?.*)/i', $_POST['cmd'], $match))
       require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'csharp.php'; */
    //require_once 'config' . DIRECTORY_SEPARATOR . 'javascript.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'ruby.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'go.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'java.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'csharp.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'rust.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'php.php'; // PHP config
    //require_once 'config' . DIRECTORY_SEPARATOR . 'nodejs.php'; // Node.js config
    //require_once 'config' . DIRECTORY_SEPARATOR . 'composer.php'; // Composer config
    //require_once 'config' . DIRECTORY_SEPARATOR . 'autoload.php'; // Autoload configuration
    //require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'; // Vendor autoload
    //require_once 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'perl.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'python.php';
}

require_once APP_PATH . 'bootstrap.php';

require_once APP_PATH . APP_BASE['config'] . 'init.php';
//require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'routes.php';