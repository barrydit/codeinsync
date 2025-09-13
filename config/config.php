<?php
declare(strict_types=1);

defined('APP_PATH') or define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
$C = APP_PATH . 'config' . DIRECTORY_SEPARATOR;

require_once "{$C}constants.env.php";
require_once "{$C}constants.paths.php";
require_once "{$C}constants.runtime.php";
require_once "{$C}constants.url.php";
require_once "{$C}constants.app.php";

if (is_file("{$C}functions.php"))
  require_once "{$C}functions.php";
if (is_file(APP_PATH . 'vendor/autoload.php'))
  require_once APP_PATH . 'vendor/autoload.php';

return true;