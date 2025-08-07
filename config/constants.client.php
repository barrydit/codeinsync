<?php

/**
 * constants.client.php
 *
 * Defines constants needed for socket client communication.
 * Assumes APP_PATH is defined.
 */

// ---------------------------------------------------------
// [0] Safe defaults if not CLI
// ---------------------------------------------------------
if (!defined('APP_IS_CLI')) {
    define('APP_IS_CLI', (PHP_SAPI === 'cli'));
}

// ---------------------------------------------------------
// [1] Connection Info (shared with server)
// ---------------------------------------------------------

!defined('SOCKET_HOST') and define('SOCKET_HOST', '127.0.0.1');
!defined('SOCKET_PORT') and define('SOCKET_PORT', 9000);
!defined('SOCKET_PROTOCOL') and define('SOCKET_PROTOCOL', getprotobyname('tcp'));

// ---------------------------------------------------------
// [2] Optional Unix Socket Path (if connecting locally)
// ---------------------------------------------------------

!defined('SOCKET_PATH') and define('SOCKET_PATH', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'app.sock');

// ---------------------------------------------------------
// [3] Socket Runtime Flags
// ---------------------------------------------------------

!defined('SOCKET_IS_SECURE') and define('SOCKET_IS_SECURE', false); // Placeholder for SSL client mode

!defined('SOCKET_IS_AVAILABLE') and define(
    'SOCKET_IS_AVAILABLE',
    function_exists('socket_create') && function_exists('socket_connect')
);

if (APP_IS_CLI && !SOCKET_IS_AVAILABLE) {
    error_log("[CLIENT] Sockets not available in this PHP build.");
}
