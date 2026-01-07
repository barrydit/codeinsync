<?php
// public/ext.xdebug.php
declare(strict_types=1);

/**
 * Bootstrap first.
 * - defines APP_PATH/CONFIG_PATH
 * - loads Composer autoload (PSR-4 for ShellPrompt)
 * - defines APP_BOOTSTRAPPED and other runtime constants
 */

if (!defined('APP_BOOTSTRAPPED')) {
  require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';
}

// php -dxdebug.profiler_enable=1 -dxdebug.profiler_output_dir=. public/ui_complete.php

phpinfo();

exit;

function calculateSum($a, $b)
{
  return $a + $b;
}

function performComplexTask()
{
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
