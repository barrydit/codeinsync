<?php

/**
 * constants.socketloader.php
 *
 * Centralized loader for socket-related constants
 * based on execution context.
 *
 * Define IS_CLIENT or IS_SERVER before including this file.
 *
 * Example:
 *   define('IS_CLIENT', true);
 *   require_once 'constants.socketloader.php';
 */

// Ensure APP_PATH is defined before use
if (!defined('APP_PATH')) {
    throw new RuntimeException("APP_PATH must be defined before loading socket constants.");
}

// Resolve path aliases
$CLIENT_CONSTANTS = APP_PATH . 'config/constants.client.php';
$SERVER_CONSTANTS = APP_PATH . 'config/constants.socket.php';

// Load based on mode flag
if (defined('IS_CLIENT') && IS_CLIENT === true) {
    if (file_exists($CLIENT_CONSTANTS)) {
        require_once $CLIENT_CONSTANTS;
    } else {
        error_log("[LOADER] Missing client constants at $CLIENT_CONSTANTS");
    }
} elseif (defined('IS_SERVER') && IS_SERVER === true) {
    if (file_exists($SERVER_CONSTANTS)) {
        require_once $SERVER_CONSTANTS;
    } else {
        error_log("[LOADER] Missing server constants at $SERVER_CONSTANTS");
    }
} else {
    error_log("[LOADER] Neither IS_CLIENT nor IS_SERVER defined — skipping socket constants.");
}
