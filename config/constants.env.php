<?php
declare(strict_types=1);
// config/constants.env.php

// use CodeInSync\Shared\Filesystem\PathUtils;
if (!class_exists(\CodeInSync\Shared\Filesystem\PathUtils::class)) {
    require APP_PATH . 'src/Shared/Filesystem/PathUtils.php';
    @class_alias(\CodeInSync\Shared\Filesystem\PathUtils::class, 'PathUtils');
}

defined('APP_PATH') or define('APP_PATH', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR);

if (!defined('APP_ROOT')) {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referer !== '') {
            $query = parse_url($referer, PHP_URL_QUERY);
            if ($query) {
                // Parse query string and merge into $_GET
                parse_str($query, $refParams);
                if (is_array($refParams)) {
                    $_GET = [...$_GET, ...$refParams]; // array_merge()
                }
            }
        }
    }

    if (array_key_first($_GET) != 'path') {
        // Determine base paths for client, domain, or project
        $clientPath = isset($_GET['client']) && $_GET['client'] !== ''
            ? PathUtils::rel(APP_PATH, PATH_CLIENTS) . DIRECTORY_SEPARATOR . $_GET['client'] . DIRECTORY_SEPARATOR
            : (!empty($_ENV['DEFAULT_CLIENT']) && isset($_GET['client']) ? PathUtils::rel(APP_PATH, PATH_CLIENTS) . DIRECTORY_SEPARATOR . $_ENV['DEFAULT_CLIENT'] . DIRECTORY_SEPARATOR : '');

        $domainPath = isset($_GET['domain']) && $_GET['domain'] !== ''
            ? (isset($_GET['client'])
                ? $clientPath . $_GET['domain'] . DIRECTORY_SEPARATOR
                : PathUtils::rel(APP_PATH, PATH_CLIENTS) . DIRECTORY_SEPARATOR . $_GET['domain'] . DIRECTORY_SEPARATOR)
            : (!empty($_ENV['DEFAULT_DOMAIN']) ? PathUtils::rel(APP_PATH, PATH_CLIENTS) . DIRECTORY_SEPARATOR . $_ENV['DEFAULT_CLIENT'] . DIRECTORY_SEPARATOR . (array_key_first($_GET) == 'path'
                ? ''
                : (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cmd']) && in_array($_POST['cmd'], ['cd ../', 'chdir ../'])
                    ? ''
                    : $_ENV['DEFAULT_DOMAIN']) . DIRECTORY_SEPARATOR) : '')/*''*/ ;

        $projectPath = isset($_GET['project'])
            ? PathUtils::rel(APP_PATH, PATH_PROJECTS) . DIRECTORY_SEPARATOR . $_GET['project'] . DIRECTORY_SEPARATOR
            : (!empty($_ENV['DEFAULT_PROJECT']) && isset($_GET['project']) ? PathUtils::rel(APP_PATH, PATH_PROJECTS) . DIRECTORY_SEPARATOR . $_ENV['DEFAULT_PROJECT'] . DIRECTORY_SEPARATOR : '');

        // Final path prioritizing client/domain and falling back to project if present
        $path = $domainPath ?: $clientPath ?: $projectPath;

        //
        //die($path);
        // Validate path and define APP_ROOT if valid

        if (/* $domainPath &&*/ $path && is_dir(APP_PATH . $path)) {
            // no realpath() – keep it relative
            define('APP_ROOT', rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
        } else {
            define('APP_ROOT', '');
        }

        //if ($path) {
        //    $abs = realpath(APP_PATH . $path); // resolve relative against APP_PATH
        //    if ($abs !== false && is_dir($abs)) {
        //        error_log('Determining APP_ROOT from $path : ' . $path);
        //        define('APP_ROOT', rtrim($abs, "/\\") . DIRECTORY_SEPARATOR);
        //    }
        //}


        //if ($path && is_dir(APP_PATH . $path)) {
        //    error_log('Determining APP_ROOT from $path : ' . $path);
        //    if (realpath($resolvedPath = rtrim($path, DIRECTORY_SEPARATOR)) !== false) {
        // define('APP_ROOT', $resolvedPath ? $resolvedPath . DIRECTORY_SEPARATOR : '');
        //    }
        //}


    } elseif (array_key_first($_GET) == 'path') {

        $path = isset($_GET['path'])
            ? $_GET['path'] . DIRECTORY_SEPARATOR
            : '';
        if ($path && is_dir($path)) {
            if (realpath($resolvedPath = rtrim($path, DIRECTORY_SEPARATOR)) !== false) {
                define('APP_ROOT_DIR', $resolvedPath ? $resolvedPath . DIRECTORY_SEPARATOR : '');
            }
        }
    } else {
        //define('APP_ROOT_DIR', $_GET['path']);
        define('APP_ROOT', '');
        $errors['APP_ROOT'] = 'APP_ROOT was NOT defined.';
    }
}

$ENV_FILE_GLOBAL = APP_PATH . '.env';
$ENV_FILE_SCOPED = defined('APP_ROOT') ? APP_PATH . APP_ROOT . '.env' : null;

class EnvReader
{
    public static function read(string $file): array
    {
        if (!is_file($file))
            return [];
        $data = parse_ini_file($file, true, INI_SCANNER_RAW) ?: [];
        return self::normalize($data);
    }
    private static function normalize(mixed $v): mixed
    {
        if (is_array($v)) {
            foreach ($v as $k => $vv)
                $v[$k] = self::normalize($vv);
            return $v;
        }
        if (!is_string($v))
            return $v;
        $raw = trim($v);
        // keep quoted values as strings (strip quotes)
        if (strlen($raw) >= 2 && (($raw[0] === '"' && $raw[-1] === '"') || ($raw[0] === "'" && $raw[-1] === "'"))) {
            return substr($raw, 1, -1);
        }
        $l = strtolower($raw);
        if (in_array($l, ['true', 'on', 'yes'], true))
            return true;
        if (in_array($l, ['false', 'off', 'no'], true))
            return false;
        if ($raw === '1')
            return true;
        if ($raw === '0')
            return false;
        return $v;
    }
}

// load order: global then scoped (scoped overrides)
$env = [];
$env = array_replace($env, EnvReader::read($ENV_FILE_GLOBAL));
//if ($ENV_FILE_SCOPED)
//    $env = array_replace($env, EnvReader::read($ENV_FILE_SCOPED));

$_ENV = array_replace($_ENV, $env);

// Optionally export UPPER_SNAKE to constants
foreach ($env as $k => $v) {
    if (is_string($k) && preg_match('/^[A-Z][A-Z0-9_]*$/', $k) && !defined($k)) {
        if (!defined($k))
            if ($k === 'APP_DEBUG' && !is_string($v)) {
                define($k, isset($_GET['debug']) ?? $v ?? false);
            } else
                define($k, $v);

    }
}

// Validate essential PHP settings
$order = ini_get("variables_order");   // e.g. "EGPCS"

// Required letters
$required = [/*'E', */ 'G', 'P', 'C', 'S'];

// Find missing
$missing = array_diff($required, str_split($order));

if (!empty($missing)) {
    $errors['MISSING'][] = implode(', ', $missing) . ' missing from variables_order';
} // else { }

// 1. Define APP_ENV safely
if (!defined('APP_ENV')) {
    $env = getenv('APP_ENV');
    define('APP_ENV', is_string($env) && $env !== '' ? $env : 'production'); // Options: production, development, etc.
} elseif (!is_string(APP_ENV)) {
    $errors['APP_ENV'] = 'App Env must be a string: ' . var_export(APP_ENV, true);
}

define('APP_DEV_MODE', in_array(APP_ENV, ['development', 'dev', 'local'], true));

// Define WWW_PATH (public web root) if not already defined
if (!defined('WWW_PATH'))
    // Adjust to your project layout
    define('WWW_PATH', APP_PATH . $env['APACHE']['PUBLIC']);

// Git owner (used for sudo -u <user>)
if (!defined('GIT_OWNER') && !empty($env['GIT']['OWNER'])) {
    define('GIT_OWNER', $env['GIT']['OWNER']);
}

// Fallback detection for host and domain
if (!defined('APP_HOST')) {
    define('APP_HOST', $_SERVER['HTTP_HOST'] ?? '127.0.0.1');
}
if (!defined('APP_DOMAIN')) {
    define('APP_DOMAIN', parse_url('http://' . APP_HOST, PHP_URL_HOST));
}
