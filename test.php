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

// Profiling starts here
xdebug_start_trace(APP_PATH . 'test_trace');

$result = calculateSum($a, $b);
performComplexTask();

// Profiling ends here
xdebug_stop_trace();

echo "Result: $result";
?>