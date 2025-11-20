<?php

!defined('APP_START') and define('APP_START', $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));

// If you need coverage, keep it — but ensure it prints NOTHING.
// require_once __DIR__ . '/../bootstrap/coverage-bootstrap.php';

// Hint dispatcher early for fragments/JSON
// if (!defined('APP_MODE') && (isset($_GET['part']) || isset($_GET['json'])))
//  define('APP_MODE', 'dispatcher');

require_once('../bootstrap/bootstrap.php');

// if (defined('APP_MODE') && APP_MODE === 'dispatcher')
//   exit; // was: return


dd('test');