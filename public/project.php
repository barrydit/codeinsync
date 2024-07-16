<?php

//$require = function($path) { if (is_file($path)) return require_once($path); else echo '<!DOCTYPE html>'; };  // require_once('../projects/index.php');

//echo $require('../projects/index.php');

$path = realpath('../projects/index.php') or die('<!DOCTYPE html>');;

//die($path);

die(require_once $path); //  
