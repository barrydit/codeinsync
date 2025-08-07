<?php

/**
 * constants.cli.php
 *
 * Defines CLI-specific runtime paths and options.
 * Only applies in CLI or socket/server mode.
 */

if (!defined('APP_IS_CLI') || !APP_IS_CLI) {
    return; // Skip if not running in CLI
}

// ---------------------------------------------------------
// [1] Log/Cache Directories
// ---------------------------------------------------------

!defined('APP_LOG_DIR') and define(
    'APP_LOG_DIR',
    APP_PATH . 'var' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR
);

!defined('APP_CACHE_DIR') and define(
    'APP_CACHE_DIR',
    APP_PATH . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR
);

!defined('APP_TMP_DIR') and define(
    'APP_TMP_DIR',
    APP_PATH . 'var' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR
);

// ---------------------------------------------------------
// [2] Lock and PID file paths (for daemonization)
// ---------------------------------------------------------

!defined('APP_PID_FILE') and define(
    'APP_PID_FILE',
    APP_LOG_DIR . 'app.pid'
);

!defined('APP_LOCK_FILE') and define(
    'APP_LOCK_FILE',
    APP_LOG_DIR . 'app.lock'
);

// ---------------------------------------------------------
// [3] Runtime Flags
// ---------------------------------------------------------

!defined('APP_IS_DAEMON') and define(
    'APP_IS_DAEMON',
    in_array('--daemon', $argv ?? [], true)
);

!defined('APP_IS_VERBOSE') and define(
    'APP_IS_VERBOSE',
    in_array('--verbose', $argv ?? [], true)
);

// Future: use getopt() to capture additional options