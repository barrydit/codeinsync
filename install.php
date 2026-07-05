<?php

require_once('bootstrap/bootstrap.php');

dd([
    'APP_PATH' => APP_PATH,
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? null,
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? null,
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
    'APP_PUBLIC_FS_ROOT' => defined('APP_PUBLIC_FS_ROOT') ? APP_PUBLIC_FS_ROOT : null,
    'APP_PUBLIC_URL_PREFIX' => defined('APP_PUBLIC_URL_PREFIX') ? APP_PUBLIC_URL_PREFIX : null,
    'BASE_HREF' => UrlContext::getBaseHref(),
]);