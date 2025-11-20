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

$appFs = APP_PATH;                          // ex: /mnt/c/www
$docFs = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/'); // ex: /mnt/c/www

// If APP_ROOT begins with DOCUMENT_ROOT, we can compute URL prefix
$appUrlPath = '/';
if (strpos($appFs, $docFs) === 0) {
    $suffix = substr($appFs, strlen($docFs)); // ex: '' or '/codeinsync'
    $appUrlPath = rtrim($suffix, '/') . '/';  // always end with /
}

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