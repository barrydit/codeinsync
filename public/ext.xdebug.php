<?php
// test.php


// php -dxdebug.profiler_enable=1 -dxdebug.profiler_output_dir=. public/ui_complete.php

phpinfo();

die();

if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '') require_once $path;
  else die(var_dump("$path path was not found. file=config.php"));

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