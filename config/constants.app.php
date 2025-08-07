<?php

// Application configuration

const APP_NAME = 'CodeInSync'; // define('APP_NAME', 'Dashboard');
!is_string(APP_NAME)
  and $errors['APP_NAME'] = 'APP_NAME is not a string => ' . var_export(APP_NAME, true); // print('Name: ' . APP_NAME  . ' v' . APP_VERSION . "\n");

const APP_AUTHOR = 'Barry Dick'; // define('APP_AUTHOR', 'Barry Dick');

const APP_VERSION = '1.0.0'; // define('APP_VERSION', '1.0.0');

!is_string(APP_VERSION) and $errors['APP_VERSION'] = 'APP_VERSION is not a valid string value.';

(version_compare(APP_VERSION, '1.0.0', '>=') == 0)
  and $errors['APP_VERSION'] = 'APP_VERSION is not a valid version (' . APP_VERSION . ').';

//!defined('APP_DEBUG') and define('APP_DEBUG', false);
//!defined('APP_ERROR') and define('APP_ERROR', false); // $hasErrors = true;

!defined('APP_DASHBOARD') and define('APP_DASHBOARD', "\n" . sprintf(<<<EOL
  %s
        ______   _______  _______           ______   _______  _______  _______  ______  
       (  __  \ (  ___  )(  ____ \|\     /|(  ___ \ (  ___  )(  ___  )(  ____ )(  __  \ 
       | (  \  )| (   ) || (    \/| )   ( || (   ) )| (   ) || (   ) || (    )|| (  \  )
       | |   ) || (___) || (_____ | (___) || (__/ / | |   | || (___) || (____)|| |   ) |
       | |   | ||  ___  |(_____  )|  ___  ||  __ (  | |   | ||  ___  ||     __)| |   | |
       | |   ) || (   ) |      ) || (   ) || (  \ \ | |   | || (   ) || (\ (   | |   ) |
       | (__/  )| )   ( |/\____) || )   ( || )___) )| (___) || )   ( || ) \ \__| (__/  )
       (______/ |/     \|\_______)|/     \||/ \___/ (_______)|/     \||/   \__/(______/ 
       {{STATUS}}            Written by Barry Dick (2024)
  %s
  EOL
  ,
  $padding = str_pad('', 90, '='),
  $padding
));

const CONSOLE = true;

!defined('APP_ERRORS') and define('APP_ERRORS', $errors ?? []); // $errors = [];

/* if (APP_ENV == 'development') { 
  if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'constants_backup.php')) // is_file('config' . DIRECTORY_SEPARATOR . 'constants.php')) 
    require_once $path;

  if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'constants_client-project.php')) // is_file('config' . DIRECTORY_SEPARATOR . 'constants.php')) 
    require_once $path;
} */