<?php

/**
 * constants.socket.php
 *
 * Defines configuration for socket server/client communication.
 * Assumes APP_PATH, APP_LOG_DIR, APP_TMP_DIR are defined.
 */

// ---------------------------------------------------------
// [1] Socket Defaults
// ---------------------------------------------------------

!defined('SOCKET_HOST') and define('SOCKET_HOST', '127.0.0.1');
!defined('SOCKET_PORT') and define('SOCKET_PORT', 9000);
!defined('SOCKET_BACKLOG') and define('SOCKET_BACKLOG', 10);
!defined('SOCKET_TIMEOUT') and define('SOCKET_TIMEOUT', 5); // seconds
!defined('SOCKET_PROTOCOL') and define('SOCKET_PROTOCOL', getprotobyname('tcp'));

// ---------------------------------------------------------
// [2] Files for Locking / PID (for socket-specific usage)
// ---------------------------------------------------------

!defined('SOCKET_LOCK_FILE') and define('SOCKET_LOCK_FILE', APP_LOG_DIR . 'socket.lock');
!defined('SOCKET_PID_FILE') and define('SOCKET_PID_FILE', APP_LOG_DIR . 'socket.pid');

// ---------------------------------------------------------
// [3] Optional Unix Socket Path
// ---------------------------------------------------------

!defined('SOCKET_PATH') and define('SOCKET_PATH', APP_TMP_DIR . 'app.sock'); // For Unix socket if needed

// ---------------------------------------------------------
// [4] Socket Runtime Flags
// ---------------------------------------------------------

!defined('SOCKET_IS_SECURE') and define('SOCKET_IS_SECURE', false); // Placeholder for SSL/secure sockets

!defined('SOCKET_IS_AVAILABLE') and define(
    'SOCKET_IS_AVAILABLE',
    function_exists('socket_create') && function_exists('socket_bind')
);

if (defined('APP_IS_CLI') && APP_IS_CLI && !SOCKET_IS_AVAILABLE) {
    error_log("[SOCKET] PHP sockets extension is not available.");
}
