<?php

//var_dump(APP_PATH . basename(dirname(__DIR__, 2)) . '/' . basename(dirname(__DIR__, 1)));
if (PHP_SAPI === 'cli') {
    // Replace the script name from REQUEST_URI when run in CLI
    $scriptName = $_SERVER['PHP_SELF'];
    $requestUri = preg_replace('/' . preg_quote($scriptName, '/') . '$/', '/', $_SERVER['REQUEST_URI'] ?? '');
} else {
    $requestUri = $_SERVER['REQUEST_URI'] ?? ($_SERVER['PHP_SELF'] ?? '');
    if ($requestUri !== '') {
        $queryString = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
        $requestUri .= $queryString;
    }
}
// Ensure the request URI is properly formatted
$requestUri = rtrim($requestUri, '/'); // Remove trailing slash for consistency
$parsedUrl = parse_url($requestUri);

// substr( str_replace('\\', '/', __FILE__), strlen($_SERVER['DOCUMENT_ROOT']), strrpos(str_replace('\\', '/', __FILE__), '/') - strlen($_SERVER['DOCUMENT_ROOT']) + 1 )

// const APP_URL = 'http://localhost/'; // define('APP_URL', 'http://localhost/');
// Define the base URL for the application
// This file defines constants for the URL configuration of the application
// Check if the script is running in CLI or HTTP environment
if (php_sapi_name() === 'cli' || defined('STDIN')) {
    // CLI environment: set a default URL or placeholder
    define('APP_URL', 'http://localhost/');
} else {
    // HTTP environment: construct the URL dynamically
    define(
        'APP_URL',
        'http' . (defined('APP_HTTPS') ? 's' : '') . '://' .
        ($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_ADDR'] ?? 'localhost') .
        parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH)
    );
}

// Define the application URL
//const APP_ENV = 'development'; // define('APP_ENV', 'production') ? 'production' : 'development'; // APP_DEV |  APP_PROD

//const APP_ENV = getenv('APP_ENV') ?: 'production';
define('APP_ENV', getenv('APP_ENV') ?: 'production');
; // ? 'production' : 'development'; // APP_DEV |  APP_PROD
// Define the application environment
// Check if APP_ENV is defined and is a string  

// Now safely parse APP_DOMAIN
$parsed = parse_url(APP_URL);
define('APP_DOMAIN', array_key_exists('host', $parsed) ? $parsed['host'] : getenv('APP_DOMAIN') ?? 'localhost');
!is_string(APP_DOMAIN) and $errors['APP_DOMAIN'] = 'APP_DOMAIN is not valid. (' . APP_DOMAIN . ')' . "\n";

if (defined('APP_DOMAIN') && !in_array(APP_DOMAIN, [/*'localhost',*/ '127.0.0.1', '::1'])) {
    /* if (!is_file($file = APP_PATH . '.env') && @touch($file)) file_put_contents($file, "DB_UNAME=\nDB_PWORD="); */
    //  defined('APP_ENV') or define('APP_ENV', 'production');
} else {
    /* if (!is_file($file = APP_PATH . '.env') && @touch($file)) file_put_contents($file, "DB_UNAME=\nDB_PWORD="); */
    //  defined('APP_ENV') or define('APP_ENV', 'development'); // development
} // APP_DEV |  APP_PROD

// Derive APP_HOST
define('APP_HOST', gethostbyname(APP_DOMAIN) ?: 'localhost');
!is_string(APP_HOST) and $errors['APP_HOST'] = 'APP_HOST is not valid. (' . APP_HOST . ')' . "\n";

// Constants (no define needed)
const APP_PORT = '80';
!is_int((int) APP_PORT) and $errors['APP_PORT'] = 'APP_PORT is not valid. (' . APP_PORT . ')' . "\n";

const SERVER_PORT = '8080'; // 9000
!is_int((int) SERVER_PORT) and $errors['SERVER_PORT'] = 'SERVER_PORT is not valid. (' . SERVER_PORT . ')' . "\n";

// Use APP_HOST directly for SERVER_HOST
const SERVER_HOST = APP_HOST ?? '0.0.0.0';
!is_string(SERVER_HOST) and $errors['SERVER_HOST'] = 'SERVER_HOST is not valid. (' . SERVER_HOST . ')' . "\n";

if (defined('APP_BASE') && !is_array(APP_BASE)) {
    $protocol = 'http' . (!defined('APP_HTTPS') || !APP_HTTPS ? '' : 's');
    $appUrl = $protocol . '://' . APP_DOMAIN . $parsedUrl['path'];
} else {
    $appUrl = [
        'scheme' => 'http' . (!defined('APP_HTTPS') || !APP_HTTPS ? '' : 's'), // ($_SERVER['HTTPS'] == 'on', (isset($_SERVER['HTTPS']) === true ? 'https' : 'http')
        /* https://www.php.net/manual/en/features.http-auth.php */
        'user' => $_SERVER['PHP_AUTH_USER'] ?? null,
        'pass' => $_SERVER['PHP_AUTH_PW'] ?? null,
        'host' => APP_DOMAIN,
        'port' => (int) ($_SERVER['SERVER_PORT'] ?? 80),
        'path' => $parsedUrl['path'],
        'query' => $_SERVER['QUERY_STRING'] ?? '', // array( key($_REQUEST) => current($_REQUEST) )
        'fragment' => parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_FRAGMENT),
    ];
}

define('APP_URL_BASE', $appUrl ?? [
    'scheme' => 'http' . (!defined('APP_HTTPS') || !APP_HTTPS ? '' : 's'),
    'host' => 'localhost',
    'port' => 80,
    'path' => '/',
    'query' => 'client=000-Doe%2CJohn&domain=johndoe.ca',
]);

// define('APP_URL_PATH', APP_URL_BASE['path'] ?? '/');
//define('APP_URL_PATH', is_array(APP_URL) ? APP_URL['path'] : APP_URL_BASE['path']); // substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1)

if (!defined('APP_URL_PATH')) {
    $parsedUrl = is_array(APP_URL) ? APP_URL : parse_url(APP_URL);
    define('APP_URL_PATH', parse_url(APP_URL, PHP_URL_PATH) ?? '/');
}