<?php

/**
 * constants.paths.php
 *
 * Defines all directory path constants for the application.
 * Ensures normalization and proper fallback handling.
 */

// ------------------------------
// [1] Directory Normalizer
// ------------------------------

if (!function_exists('normalize_dir')) {
    /**
     * Ensures a directory ends with DIRECTORY_SEPARATOR.
     * Returns an empty string as-is.
     */
    function normalize_dir(string $path): string
    {
        return $path === '' ? '' : rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }
}

// ------------------------------
// [2] Core App Paths
// ------------------------------

// Absolute base path of the application (usually /mnt/c/www/)
!defined('APP_PATH') and define(
    'APP_PATH',
    normalize_dir(realpath(dirname(__DIR__, 1)))
);

// Optional: Scoped project/client/domain folder (e.g. "projects/foo/" or "clients/bar/")
// Must be set elsewhere (like from ENV or a router)
!defined('APP_SCOPE_DIR') and define('APP_SCOPE_DIR', normalize_dir(''));

// Combined resolved path to current working directory
!defined('APP_WORKING_DIR') and define(
    'APP_WORKING_DIR',
    APP_PATH . APP_SCOPE_DIR
);

// ------------------------------
// [3] Common Base Directories
// ------------------------------

// Validate and define common base folders dynamically
$base_paths = [
    'app',
    'config',
    'data',
    'public',
    'resources',
    'src',
    'var',
    'vendor',

    // Aliases
    'clients' => 'projects' . DIRECTORY_SEPARATOR . 'clients',
    'projects' => 'projects' . DIRECTORY_SEPARATOR . 'internal',
    'node_modules',
];

$validated_paths = [];
$errors ??= [];

foreach ($base_paths as $key => $subpath) {
    $alias = is_string($key) ? $key : $subpath;
    $full = realpath(APP_PATH . $subpath);

    if ($full && is_dir($full)) {
        $validated_paths[$alias] = normalize_dir($subpath);
    } else {
        $errors['INVALID_PATHS'][] = "Missing or invalid: [$alias] => $subpath";
    }
}

define('APP_BASE', $validated_paths);

// ------------------------------
// [4] Public Path Default
// ------------------------------

!defined('PATH_PUBLIC') and define(
    'PATH_PUBLIC',
    APP_BASE['public'] ?? APP_PATH . 'public' . DIRECTORY_SEPARATOR
);

// ------------------------------
// [5] Working Composer File Path (scoped)
// ------------------------------

$composerFile = APP_WORKING_DIR . 'composer.json';

defined('COMPOSER_CONFIG') or define('COMPOSER_CONFIG', [
    'file_path' => $composerFile,
    'raw_json' => is_file($composerFile) ? file_get_contents($composerFile) : '{}',
]);

// ------------------------------
// [6] Optionally dump errors
// ------------------------------

if (!empty($errors)) {
    // dd($errors); or handle appropriately
}

