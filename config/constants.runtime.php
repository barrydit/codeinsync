<?php

/**
 * constants.runtime.php
 *
 * Runtime and environment state detection.
 * Assumes APP_PATH and APP_BASE are defined.
 */

// ---------------------------------------------------------
// [1] Request Context Detection
// ---------------------------------------------------------

!defined('APP_REQUEST_METHOD') and define(
    'APP_REQUEST_METHOD', $_SERVER['REQUEST_METHOD'] ?? 'cli'
);

!defined('APP_IS_CLI') and define(
    'APP_IS_CLI', PHP_SAPI === 'cli'
);

!defined('APP_IS_WEB') and define(
    'APP_IS_WEB', !APP_IS_CLI
);

!defined('APP_START') and define(
    'APP_START', microtime(true)
);

// ---------------------------------------------------------
// [2] Execution Contexts
// ---------------------------------------------------------

!defined('APP_EXEC') and define(
    'APP_EXEC', PHP_BINARY
);

!defined('PHP_EXEC') and define(
    'PHP_EXEC', PHP_BINARY
);

!defined('APP_IS_LOCALHOST') and define(
    'APP_IS_LOCALHOST', in_array($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', ['127.0.0.1', '::1'])
);

!defined('APP_IS_ONLINE') and define(
    'APP_IS_ONLINE', (bool) gethostbyname('github.com') !== 'github.com'
);

// Optional SUDO detection
!defined('APP_IS_SUDO') and define(
    'APP_IS_SUDO', (function () {
        return stripos(PHP_OS, 'LINUX') === 0 && function_exists('posix_geteuid')
            ? posix_geteuid() === 0
            : false;
    })()
);

// ---------------------------------------------------------
// [3] Dashboard / Versioning
// ---------------------------------------------------------

!defined('APP_DASHBOARD') and define(
    'APP_DASHBOARD', APP_PATH . 'dashboard.php'
);

!defined('APP_VERSION_FILE') and define(
    'APP_VERSION_FILE', APP_PATH . 'VERSION.md'
);

// ---------------------------------------------------------
// [4] Runtime Executables
// ---------------------------------------------------------

// Resolve common executables like node, composer, etc.
$executables = ['node', 'composer', 'npm', 'phpunit'];
foreach ($executables as $bin) {
    $constant = strtoupper($bin) . '_EXEC';
    if (!defined($constant)) {
        $path = trim(@shell_exec("which $bin") ?? '');
        if ($path && is_executable($path)) {
            define($constant, $path);
        }
    }
}