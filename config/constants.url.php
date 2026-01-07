<?php
// config/constants.url.php
declare(strict_types=1);

/**
 * URL / host related constants.
 * - Honors reverse-proxy headers when present
 * - Works in both web and CLI contexts (CLI falls back to localhost)
 * - Backwards compatible with older constants.url.php (APP_HTTPS, APP_URL_PARTS, APP_URI, APP_URL_PATH)
 */

$errors ??= [];

// Normalized server array (avoid notices; still works in CLI)
$server = $_SERVER ?? [];
$isCli = (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');

// ---------------------------------------------------------
// 1) Scheme (http / https)
// ---------------------------------------------------------
$xfp = strtolower($server['HTTP_X_FORWARDED_PROTO'] ?? '');
$rs = strtolower($server['REQUEST_SCHEME'] ?? '');
$httpsFlag = $server['HTTPS'] ?? '';
$rawPort = (int) ($server['HTTP_X_FORWARDED_PORT'] ?? ($server['SERVER_PORT'] ?? 80));

$isHttps =
    $xfp === 'https' ||
    $rs === 'https' ||
    (!empty($httpsFlag) && $httpsFlag !== 'off') ||
    $rawPort === 443;

$scheme = $isHttps ? 'https' : 'http';

// ---------------------------------------------------------
// 2) Host
//    X-Forwarded-Host > Host > Server Name
//    Also handle "host:port" form.
// ---------------------------------------------------------
$host =
    $server['HTTP_X_FORWARDED_HOST']
    ?? $server['HTTP_HOST']
    ?? $server['SERVER_NAME']
    ?? 'localhost';

if (strpos($host, ':') !== false) {
    [$hostOnly, $hostPort] = explode(':', $host, 2);
    $host = $hostOnly;

    // If no other explicit port, use the one from host header
    if (!isset($server['HTTP_X_FORWARDED_PORT']) && !isset($server['SERVER_PORT'])) {
        $rawPort = (int) $hostPort;
    }
}

// ---------------------------------------------------------
// 3) Port (honor proxy port, strip default)
// ---------------------------------------------------------
$port = $rawPort ?: ($isHttps ? 443 : 80);
$isDefaultPort = (!$isHttps && $port === 80) || ($isHttps && $port === 443);
$portPart = $isDefaultPort ? '' : ':' . $port;

// ---------------------------------------------------------
// 4) Request URI, path, query, fragment
// ---------------------------------------------------------
if ($isCli) {
    // No real HTTP request in CLI; just sane defaults
    $requestUri = '/';
} else {
    $requestUri = $server['REQUEST_URI'] ?? $server['PHP_SELF'] ?? '/';
    if ($requestUri === '') {
        $requestUri = '/';
    }
}

$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$queryString = (string) (parse_url($requestUri, PHP_URL_QUERY) ?? '');
$fragment = parse_url($requestUri, PHP_URL_FRAGMENT) ?: null;

// ---------------------------------------------------------
// 5) Core URL-related constants
// ---------------------------------------------------------
defined('APP_SCHEME') or define('APP_SCHEME', $scheme);
defined('APP_IS_HTTPS') or define('APP_IS_HTTPS', $isHttps);

// Domain without leading "www."
$bareDomain = preg_replace('/^www\./i', '', $host) ?: 'localhost';
defined('APP_DOMAIN') or define('APP_DOMAIN', $bareDomain);

// Host as seen by the client (may include subdomain, no port)
defined('APP_HOST') or define('APP_HOST', $host);

// Port as integer
defined('APP_PORT') or define('APP_PORT', $port);

// Origin: scheme://host[:port]
defined('APP_ORIGIN') or define('APP_ORIGIN', APP_SCHEME . '://' . APP_HOST . '/' . $portPart);

// ---------------------------------------------------------
// 6) Query string parsed into array (APP_QUERY)
// ---------------------------------------------------------
if (!defined('APP_QUERY')) {
    $parsedQuery = [];
    if ($queryString !== '') {
        parse_str($queryString, $parsedQuery);
    }
    define('APP_QUERY', $parsedQuery);
}

// ---------------------------------------------------------
// 7) Full current URL (no fragment) - canonical APP_URL
// ---------------------------------------------------------
if (!defined('APP_URL')) {
    $qsPart = $queryString !== '' ? '?' . $queryString : '';
    define('APP_URL', APP_ORIGIN . '/' . $path . $qsPart);
}

// ---------------------------------------------------------
// 8) DOMAIN_EXPR (hostname / URL regex) with ENV override
// ---------------------------------------------------------
if (!defined('DOMAIN_EXPR')) {
    $expr = $_ENV['SHELL']['EXPR_DOMAIN'] ?? '/(?:[a-z]+\:\/\/)?(?:[a-z0-9\-]+\.)+[a-z]{2,}(?:\/\S*)?/i';

    if (!is_string($expr) || $expr === '') {
        $errors['DOMAIN_EXPR'] = 'DOMAIN_EXPR is not a valid string value.';
    } else {
        define('DOMAIN_EXPR', $expr);
    }
}

// ---------------------------------------------------------
// 9) Socket server defaults (if you still want them here)
// ---------------------------------------------------------
defined('SERVER_PORT') or define('SERVER_PORT', 9000);
if (!is_int((int) SERVER_PORT)) {
    $errors['SERVER_PORT'] = 'SERVER_PORT is not valid. (' . SERVER_PORT . ')' . "\n";
}

defined('SERVER_HOST') or define('SERVER_HOST', APP_HOST ?: '0.0.0.0');
if (!is_string(SERVER_HOST)) {
    $errors['SERVER_HOST'] = 'SERVER_HOST is not valid. (' . SERVER_HOST . ')' . "\n";
}

// ---------------------------------------------------------
// 10) APP_URL_BASE (structured array of URL pieces)
// ---------------------------------------------------------
if (!defined('APP_URL_BASE')) {
    define('APP_URL_BASE', [
        'scheme' => APP_SCHEME,
        'host' => APP_HOST,
        'port' => APP_PORT,
        'path' => $path,
        'query' => $queryString,
        'fragment' => $fragment,
        'user' => $server['PHP_AUTH_USER'] ?? null,
        'pass' => $server['PHP_AUTH_PW'] ?? null,
    ]);
}

// ---------------------------------------------------------
// 11) APP_URL_PATH (path only) - used by both versions
// ---------------------------------------------------------
if (!defined('APP_URL_PATH')) {
    define('APP_URL_PATH', $path ?: '/');
}

// =====================================================================
// BACKWARDS COMPATIBILITY WITH YOUR OLDER constants.url.php
// =====================================================================

// Old: APP_HTTPS (bool)
if (!defined('APP_HTTPS')) {
    define('APP_HTTPS', APP_IS_HTTPS);
}

// Old: APP_URL_PARTS (array of pieces)
//   Previously: scheme/host/port/user/pass/path/query/fragment
if (!defined('APP_URL_PARTS')) {
    define('APP_URL_PARTS', [
        'scheme' => APP_SCHEME,
        'host' => APP_HOST,
        'port' => APP_PORT,
        'user' => $server['PHP_AUTH_USER'] ?? null,
        'pass' => $server['PHP_AUTH_PW'] ?? null,
        'path' => APP_URL_PATH,
        'query' => $queryString,
        'fragment' => $fragment ?? '',
    ]);
}

// Old: APP_URI (full current URI incl. script + query, normalized)
//   The old version used basename(SCRIPT_NAME) + APP_QUERY
if (!defined('APP_URI')) {
    $scriptName = $server['SCRIPT_NAME'] ?? '';
    $scriptBase = basename($scriptName);
    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $dir = ($dir === '') ? '' : $dir . '/';

    $qs = APP_QUERY ? ('?' . http_build_query(APP_QUERY)) : '';

    $uri = APP_SCHEME . '://' . APP_HOST . $portPart . '/' . $dir . $scriptBase . $qs;
    // collapse accidental double slashes except after scheme:
    $uri = preg_replace('!([^:])/+!', '$1/', $uri);

    define('APP_URI', htmlspecialchars($uri, ENT_QUOTES, 'UTF-8'));
}

// Done � clean up local variables if you want
unset(
    $xfp,
    $rs,
    $httpsFlag,
    $rawPort,
    $isHttps,
    $scheme,
    $host,
    $bareDomain,
    $port,
    $isDefaultPort,
    $portPart,
    $requestUri,
    $path,
    $queryString,
    $fragment,
    $parsedQuery,
    $requestUri,
    $scriptName,
    $scriptBase,
    $dir
);