<?php
/*
 * Bootstrap: Head Section
 *
 * This file sets up meta information and base URL for the application.
 * It is intended to be included within the <head> section of HTML documents.
 *
 * @package    CodeInSync\SkeletonApp
 * @author     Barry Dick
 * @license    MIT
 * @link       https://codeinsync.io/
 */

// ---------- URL + <base> calculation (proxy-aware, no constants required) ----------

if (!class_exists(\CodeInSync\Infrastructure\Http\UrlContext::class)) {
    require APP_PATH . 'src/Infrastructure/Http/UrlContext.php';
    @class_alias(\CodeInSync\Infrastructure\Http\UrlContext::class, 'UrlContext');
}
/*
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
*/

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

// -----------------------------
// Case 1: public root is under DOCUMENT_ROOT
// -----------------------------
if ($publicFs && $docFs && strpos($publicFs, $docFs) === 0) {
    $suffix = substr($publicFs, strlen($docFs));     // e.g. '' or '/codeinsync/public'
    $suffix = rtrim($suffix, '/');
    $appUrlPath = ($suffix === '') ? '/' : $suffix . '/';

    // -----------------------------
// Case 2: public root NOT under DOCUMENT_ROOT (aliases, direct script access, etc.)
// -----------------------------
} elseif ($publicFs) {

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $scriptFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    $appRootFs = rtrim(str_replace('\\', '/', APP_PATH), '/');

    // Use "/{basename(public)}/" (usually "/public/") as the clamp marker
    $needle = '/' . basename($publicFs) . '/';

    // 2a) If the URL already contains "/public/", clamp to it
    $pos = strpos($scriptName, $needle);
    if ($pos !== false) {
        $appUrlPath = substr($scriptName, 0, $pos + strlen($needle));

    } else {
        // 2b) If script is under APP_PATH (but URL is /src/... or similar), compute URL prefix then add "/public/"
        // Example:
        //   SCRIPT_NAME:     /clients/X/site/src/Presentation/.../EntryFormController.php
        //   SCRIPT_FILENAME: /mnt/c/clients/X/site/src/Presentation/.../EntryFormController.php
        //   APP_PATH:        /mnt/c/clients/X/site
        // => URL prefix:     /clients/X/site
        // => base path:      /clients/X/site/public/
        if ($scriptFile !== '' && strpos($scriptFile, $appRootFs . '/') === 0) {

            $fsRel = substr($scriptFile, strlen($appRootFs)); // "/src/.../EntryFormController.php"

            // If the URL ends with the same relative FS path, strip it to get the URL prefix
            if ($fsRel !== '' && substr($scriptName, -strlen($fsRel)) === $fsRel) {
                $urlPrefix = substr($scriptName, 0, -strlen($fsRel)); // "/clients/.../site"
                $appUrlPath = rtrim($urlPrefix, '/') . $needle;        // "/clients/.../site/public/"
            } else {
                // 2c) last fallback: just use the URL directory of the script
                $appUrlPath = rtrim(dirname($scriptName), '/') . '/';
            }

        } else {
            // 2c) last fallback: just use the URL directory of the script
            $appUrlPath = rtrim(dirname($scriptName), '/') . '/';
        }
    }
}

// IMPORTANT: use the correct variable name here
defined('APP_PUBLIC_URL_PREFIX') or define('APP_PUBLIC_URL_PREFIX', $appUrlPath);

UrlContext::setBaseHref(APP_ORIGIN . APP_PUBLIC_URL_PREFIX);

// Final base href
//$baseHref = UrlContext::getBaseHref();

// Helper to build asset URLs relative to base
$asset = static function (string $path): string {
    return /* UrlContext::getBaseHref() .*/ ltrim($path, '/');
};

// ---------- Meta defaults (use constants if defined; else fallbacks) ----------
$appName = defined('APP_NAME') ? APP_NAME : 'CodeInSync - Skeleton App';
$appDescription = defined('APP_DESCRIPTION') ? APP_DESCRIPTION : 'A helpful web application.';
$appAuthor = defined('APP_AUTHOR') ? APP_AUTHOR : 'Your Team';
$appLocale = defined('APP_LOCALE') ? APP_LOCALE : 'en';
$appThemeColor = defined('APP_THEME_COLOR') ? APP_THEME_COLOR : '#ffffff';

// Page-specific title (customize as needed)
$pageTitle = $appName;