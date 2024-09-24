CHANGELOG


$composerUser = !isset($_ENV['COMPOSER']['USER']) ?: $_ENV['COMPOSER']['USER'];  
$componetPkg = !isset($_ENV['COMPOSER']['PACKAGE']) ?: $_ENV['COMPOSER']['PACKAGE'];
$user = getenv('USERNAME') ?: (getenv('APACHE_RUN_USER') ?: getenv('USER') ?: '');


.htpasswd

/*
list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = 
  explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Text to send if user hits Cancel button';
    exit;
} else { echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p><p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>"; }
*/




if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"])) 
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    if (is_file($path = realpath('index.php')))
      require_once $path;
  } else {
    die(var_dump("Path was not found. file=$path"));
  }

switch (__FILE__) {
  case get_required_files()[0]:
    if ($path = (basename(getcwd()) == 'public') ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '') require_once $path;
    else die(var_dump("$path path was not found. file=config.php"));

    break;
  default:
}




HTACCESS

RewriteEngine On
RewriteBase "/clientele/000-Lastname,\ Firstname/example.ca/public"

# Rewrite requests for /assets to the /../resources directory if the file doesn't exist in /assets
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^assets/(.*)$ ../resources/$1 [L]


vendor/composer/installed.php

<?php return array(
    'root' => array(
        'name' => 'barrydit/1234',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '1d21ed08f33201dc985e9f2dca4984dd1783d9a9',
        'type' => 'project',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'barrydit/1234' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '1d21ed08f33201dc985e9f2dca4984dd1783d9a9',
            'type' => 'project',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);




:: PHP



      $proc=proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . "composer $matches[1]",  
        array(
          array("pipe","r"),
          array("pipe","w"),
          array("pipe","w")
        ),
        $pipes);
                list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
                $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");

$proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO ) . basename($bin) . ' --version;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        $exitCode = proc_close($proc);


ui.composer.php

  autoload.php
  composer.php


Many php functions either produce a forward slash, indicating the end of folder/path

  $path = dirname(__DIR__) . '/config/config.php'


Which is the better format ... 

1. stripos(PHP_OS, 'LIN') === 0
...
2. strpos(PHP_OS, 'WIN') === 0
...
3. strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'


(isset($_GET['client']) ? 'client=' . $_GET['client'] . '&' : '') . (isset($_GET['domain']) ? 'domain=' . $_GET['domain'] . '&' : '') . (isset($_GET['project']) ? 'project=' . $_GET['project'] . '&' : '')

(!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '' ) . (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') ) // client | project | client / ??domain / project

(!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '/') : 'client=' . $_GET['client'] . '/' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '/' : '') : '' ) . (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') ) client/project/domain/project

:: JavaScript

JScript
  tailwindcss-3.3.5.js
Style (type="text/tailwindcss")


- Non-indented/tab code means that it is included/required/imported

Javascript libraries must be loaded in a particular order. If a library can not be loaded, "ace is not defined", then they are in the wrong order.

jQuery, jQuery-ui, ace-editor, requirejs