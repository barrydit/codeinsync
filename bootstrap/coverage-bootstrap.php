<?php
// bootstrap/coverage-bootstrap.php
if (!defined('APP_COVERAGE_ENABLED')) {
    define('APP_COVERAGE_ENABLED', true);
}

if (APP_COVERAGE_ENABLED) {
    if (extension_loaded('xdebug') && function_exists('xdebug_start_code_coverage')) {
        // Track executed, unused, and dead code lines
        xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
        define('APP_COVERAGE_DRIVER', 'xdebug');
    } elseif (extension_loaded('pcov') && function_exists('pcov\start')) {
        // Enable PCOV globally for this request
        \pcov\start();
        define('APP_COVERAGE_DRIVER', 'pcov');
    } else {
        define('APP_COVERAGE_DRIVER', 'none');
    }
}