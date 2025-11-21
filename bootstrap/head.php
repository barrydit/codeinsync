<?php
/*
 * Bootstrap: Head Section
 *
 * This file sets up meta information and base URL for the application.
 * It is intended to be included within the <head> section of HTML documents.
 *
 * @package    CodeInSync\SkeletonApp
 * @author     Your Team
 * @license    MIT
 * @link       https://codeinsync.io/
 */

// ---------- URL + <base> calculation (proxy-aware, no constants required) ----------

// Detect scheme (honor reverse proxy headers if present)
$proto = (
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (isset($_SERVER['REQUEST_SCHEME']) && strtolower($_SERVER['REQUEST_SCHEME']) === 'https')
    || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443)
) ? 'https' : 'http';
$scheme = strtolower($proto) === 'https' ? 'https' : 'http';

// Detect host (X-Forwarded-Host > Host > Server Name)
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');

// Detect port (X-Forwarded-Port > SERVER_PORT); omit default 80/443 in URL
$port = (int) ($_SERVER['HTTP_X_FORWARDED_PORT'] ?? ($_SERVER['SERVER_PORT'] ?? 80));
$isDefaultPort = ($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443);
$portPart = $isDefaultPort ? '' : ":$port";

// Compute current directory from SCRIPT_NAME (avoids query string issues)
//$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
//$dir = ($scriptDir === '') ? '/' : $scriptDir . '/';
//$baseHref = $scheme . '://' . $host . $portPart . $dir;


defined('APP_PUBLIC_FS_ROOT') or define(
    'APP_PUBLIC_FS_ROOT',
    rtrim(str_replace('\\', '/', APP_PATH . 'public'), '/')
);

// --- Filesystem roots ---
$publicFs = defined('APP_PUBLIC_FS_ROOT')
    ? str_replace('\\', '/', APP_PUBLIC_FS_ROOT)
    : null;

$docFs = isset($_SERVER['DOCUMENT_ROOT'])
    ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/')
    : null;

// Default URL path
$appUrlPath = '/';

// Case 1: public root is under DOCUMENT_ROOT  (the “nice” case)
if ($publicFs && $docFs && strpos($publicFs, $docFs) === 0) {
    $suffix = substr($publicFs, strlen($docFs));     // e.g. '' or '/codeinsync/public'
    $suffix = rtrim($suffix, '/');
    $appUrlPath = ($suffix === '') ? '/' : $suffix . '/';

    // Case 2: Fallback – DOCUMENT_ROOT doesn't match filesystem (aliases, symlinks, etc.)
} elseif ($publicFs) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    $scriptPath = str_replace('\\', '/', $scriptName);

    // Try to clamp at "/public/"
    $needle = '/' . basename(APP_PUBLIC_FS_ROOT) . '/';
    $pos = strpos($scriptPath, $needle);

    if ($pos !== false) {
        // e.g. "/clients/000-AuthSecure/public/api/test.php"
        //  -> "/clients/000-AuthSecure/public/"
        $appUrlPath = substr($scriptPath, 0, $pos + strlen($needle));
    } else {
        // Fallback: just use the script directory
        $scriptDir = rtrim(dirname($scriptPath), '/') . '/';
        $appUrlPath = $scriptDir;
    }
}

// IMPORTANT: use the correct variable name here
defined('APP_PUBLIC_URL_PREFIX') or define('APP_PUBLIC_URL_PREFIX', $appUrlPath);

// Final base href
$baseHref = "$scheme://$host$portPart$appUrlPath";

// Helper to build asset URLs relative to base
$asset = static function (string $path) use ($baseHref): string {
    return /* $baseHref .*/ ltrim($path, '/');
};

// ---------- Meta defaults (use constants if defined; else fallbacks) ----------
$appName = defined('APP_NAME') ? APP_NAME : 'CodeInSync - Skeleton App';
$appDescription = defined('APP_DESCRIPTION') ? APP_DESCRIPTION : 'A helpful web application.';
$appAuthor = defined('APP_AUTHOR') ? APP_AUTHOR : 'Your Team';
$appLocale = defined('APP_LOCALE') ? APP_LOCALE : 'en';
$appThemeColor = defined('APP_THEME_COLOR') ? APP_THEME_COLOR : '#ffffff';

// Page-specific title (customize as needed)
$pageTitle = $appName;