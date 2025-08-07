<?php

/**
 * filesystem.ensure.php
 *
 * Ensures required directories and bootstrap files exist.
 * Depends on APP_PATH, APP_BASE, APP_SCOPE_DIR, etc.
 */

$errors ??= []; // $errors ?? [];

// ---------------------------------------------------------
// [1] Ensure base directories exist (mkdir if missing)
// ---------------------------------------------------------

foreach (APP_BASE as $key => $dir) {
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            $errors['DIR_CREATE'][] = "Failed to create directory: $dir [$key]";
        }
    }
}

// ---------------------------------------------------------
// [2] Ensure core files like .env, .htaccess, LICENSE exist
// ---------------------------------------------------------

$files_to_check = [
    '.env',
    '.htaccess',
    '.gitignore',
    'LICENSE',
    'README.md',
];

foreach ($files_to_check as $filename) {
    $file = APP_PATH . $filename;
    if (!is_file($file)) {
        @touch($file);
    }
}

// ---------------------------------------------------------
// [3] Populate core files from source_code.json (if available)
// ---------------------------------------------------------

$sourceMapFile = APP_PATH . APP_BASE['data'] . 'source_code.json';
$source_code = [];

if (is_file($sourceMapFile)) {
    $source_code = json_decode(file_get_contents($sourceMapFile), true) ?? [];
}

foreach ($files_to_check as $filename) {
    $file = APP_PATH . $filename;
    if (empty(file_get_contents($file)) && isset($source_code[$filename])) {
        file_put_contents($file, $source_code[$filename]);
    }
    
    // Special case: LICENSE from online source
    if ($filename === 'LICENSE' && empty(file_get_contents($file))) {
        if (defined('APP_IS_ONLINE') && APP_IS_ONLINE) {
            $wtfpl = 'http://www.wtfpl.net/txt/copying';
            $licenseText = @file_get_contents($wtfpl);
            if ($licenseText) {
                file_put_contents($file, $licenseText);
            } elseif (isset($source_code[$filename])) {
                file_put_contents($file, $source_code[$filename]);
            }
        }
    }
}

// ---------------------------------------------------------
// [4] Optional: Log any issues
// ---------------------------------------------------------

if (!empty($errors)) {
    // dd($errors); // or Logger::log($errors);
}
// End of filesystem.ensure.php