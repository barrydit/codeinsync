<?php
// config/config.php
declare(strict_types=1);

if (defined('APP_CLI') && APP_CLI) {
  // CLI already bootstrapped via bootstrap.cli.php
  return true;
}

defined('APP_PATH') or define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
$C = APP_PATH . 'config' . DIRECTORY_SEPARATOR;

// Always-on constants (same order everywhere)
require_once "{$C}constants.env.php";
require_once "{$C}constants.paths.php";
require_once "{$C}constants.runtime.php";
require_once "{$C}constants.url.php";
require_once "{$C}constants.app.php";

// Shared helpers (nice to have in CLI too)
if (is_file("{$C}functions.php")) {
  require_once "{$C}functions.php";
}

// Composer (intentionally disabled for now)
// if (PHP_SAPI !== 'cli' && is_file(APP_PATH . 'vendor/autoload.php')) {
//     require_once APP_PATH . 'vendor/autoload.php';
// }

return true;