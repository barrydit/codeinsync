<?php

//$require = function($path) { if (is_file($path)) return require_once $path; else echo '<!DOCTYPE html>'; };  // require_once '../' . APP_BASE['projects'] . DIRECTORY_SEPARATOR . 'index.php';

//echo $require('../index.php');

$path = realpath('../projects/index.php') or die('<!DOCTYPE html>');

die(require_once $path);
