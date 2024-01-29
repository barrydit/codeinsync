<?php
// test.php

phpinfo();

die();
require('config/config.php');

function calculateSum($a, $b) {
    return $a + $b;
}

function performComplexTask() {
    for ($i = 0; $i < 1000000; $i++) {
        // Some complex task
    }
}

$a = 5;
$b = 10;

if (function_exists('xdebug_start_trace'))
// Profiling starts here
  xdebug_start_trace(APP_PATH . 'test_trace');

$result = calculateSum($a, $b);
performComplexTask();


if (function_exists('xdebug_stop_trace'))
  // Profiling ends here
  xdebug_stop_trace();

echo "Result: $result";
?>