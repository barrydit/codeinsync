<?php
// CLI bootstrap: constants, INI, helpers — no Composer here.

defined('APP_PATH') || define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
if (!defined('PID_FILE'))
    define('PID_FILE', APP_PATH . 'server.pid');

const APP_CLI = true; // marker for CLI-only checks

// ---- INI (CLI-safe defaults) ----
@ini_set('display_errors', '1');           // or '0' in prod
@ini_set('log_errors', '1');
@ini_set('error_log', APP_PATH . 'var/php-cli-error.log'); // ensure writable
error_reporting(E_ALL);
@ini_set('memory_limit', '512M');
@set_time_limit(0);
@ignore_user_abort(true);
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'UTC');
if (function_exists('mb_internal_encoding'))
    @mb_internal_encoding('UTF-8');

// ---- Constants & helpers (always same order) ----
$C = APP_PATH . 'config' . DIRECTORY_SEPARATOR;
require_once "{$C}constants.env.php";
require_once "{$C}constants.paths.php";
require_once "{$C}constants.runtime.php";
require_once "{$C}constants.url.php";
require_once "{$C}constants.app.php";
require_once "{$C}functions.php";

// ---- Safe environment lookups (avoid $_SERVER in CLI) ----
$envShell = (isset($_ENV['SHELL']) && is_array($_ENV['SHELL'])) ? $_ENV['SHELL'] : [];

$domain = $_ENV['DOMAIN'] ?? (defined('APP_DOMAIN') ? APP_DOMAIN : 'localhost');
$user = $envShell['DEFAULT_USER']
    ?? getenv('USER')
    ?? getenv('USERNAME')
    ?? 'www-data';

$home = $envShell['HOME_PATH']
    ?? getenv('HOME')
    ?? getenv('USERPROFILE')
    ?? '';
$home = $home ? (realpath($home) ?: $home) : '';

$documentRoot = $envShell['DOCUMENT_ROOT'] ?? (getenv('DOCUMENT_ROOT') ?: '');
$cwd = getcwd() ?: '/';

// ---- Build a shell-like prompt ----
// Use ":" as delimiter (NOT PATH_SEPARATOR which is for include_path)
$delimiter = ':';
$pathForPrompt = ($home && str_starts_with($cwd, $home)) // realpath($cwd) === $home
    ? '~' . substr($cwd, strlen($home))
    : $cwd;

$prompt = sprintf('%s@%s%s%s$ ', $user, $domain, $delimiter, $pathForPrompt);

if (!defined('SHELL_PROMPT'))
    define('SHELL_PROMPT', $prompt);