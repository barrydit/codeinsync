<?php
// Debug entry point (for development only; do not use in production)

if (!defined('APP_MODE') && (isset($_GET['part']) || isset($_GET['json']))) {
    define('APP_MODE', 'dispatcher');
}

/* ---- lock dot-path + neutralize redirects that try to slashify it ---- */
if (isset($_GET['path']) && is_string($_GET['path']) && $_GET['path'] !== '' && $_GET['path'][0] === '.') {
    define('DOTPATH_LOCK', $_GET['path']); // e.g., ".ssh"

    // keep REQUEST_URI in sync (prevents self-redirect loops downstream)
    if (!empty($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = preg_replace(
            '/([?&])path=[^&]*/',
            '$1path=' . rawurlencode(DOTPATH_LOCK),
            $_SERVER['REQUEST_URI']
        );
    }

    // If any code emits a Location: header with /ssh or ssh, rewrite it back to .ssh
    if (function_exists('header_register_callback')) {
        header_register_callback(function () {
            if (!defined('DOTPATH_LOCK'))
                return;
            foreach (headers_list() as $h) {
                if (stripos($h, 'Location:') !== 0)
                    continue;
                $url = trim(substr($h, 9)); // after "Location:"
                $parts = @parse_url($url);
                if (!$parts)
                    continue;

                $query = [];
                if (isset($parts['query']))
                    parse_str($parts['query'], $query);
                if (!isset($query['path']))
                    continue;

                $slashified = '/' . ltrim(DOTPATH_LOCK, '.'); // "/ssh"
                $bare = ltrim(DOTPATH_LOCK, '.');       // "ssh"

                if ($query['path'] === $slashified || $query['path'] === $bare) {
                    $query['path'] = DOTPATH_LOCK;
                    $parts['query'] = http_build_query($query);

                    $fixed =
                        (isset($parts['scheme']) ? $parts['scheme'] . '://' : '') .
                        ($parts['host'] ?? '') .
                        ($parts['path'] ?? '') .
                        (isset($parts['query']) ? '?' . $parts['query'] : '') .
                        (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');

                    header_remove('Location');
                    header('Location: ' . $fixed, true); // same status
                }
            }
        });
    }
}

error_log('TRACE A path=' . ($_GET['path'] ?? '(unset)'));


// ---------------------------------------------------------
// [3] Sanitize Input (basic)
// ---------------------------------------------------------
// Example: sanitize ?path= for directory traversal
// (your app may require more advanced input validation/sanitation)
if (isset($_GET['path'])) {
    $path = trim((string) $_GET['path']);
    $path = trim($path, "\\/");           // drop leading/trailing slashes
    $real = realpath(APP_PATH . ($path ? $path . DIRECTORY_SEPARATOR : ''));
    if ($real && str_starts_with($real, APP_PATH)) {
        $_GET['path'] = substr($real, strlen(APP_PATH));
    } else {
        unset($_GET['path']); // invalid
    }
}
