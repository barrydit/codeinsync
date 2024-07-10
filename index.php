<?php
if (isset($_GET['debug'])) 
  require_once('public/index.php');
else
  die(header('Location: public/index.php'));
