<?php $require = function($path) { if (is_file($path)) return require_once($path); else echo '<!DOCTYPE html>'; };  // require_once('../projects/project.php');

echo $require('../projects/project.php');