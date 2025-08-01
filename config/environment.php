<?php
// config/environment.php

// APP_MODE: tells bootstrap *how* we intend to load
defined('APP_MODE') or define('APP_MODE', match (true) {
    // Add more conditions if needed
    isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], 'dispatcher') => 'dispatcher',
    php_sapi_name() === 'cli' => 'cli',
    default => 'web',
});

// APP_CONTEXT: tells runtime *what kind of environment* we're in
defined('APP_CONTEXT') or define('APP_CONTEXT', match (true) {
    APP_MODE === 'socket' => 'socket',
    APP_MODE === 'cli' => 'cli',
    default => 'www', // most likely browser
});