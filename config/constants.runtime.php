<?php
// This file defines constants for the Realtime application environment
if (!defined('APP_IS_ONLINE')) {
    if (is_file(APP_PATH . 'config/constants.env.php'))
        require_once APP_PATH . 'config/constants.env.php';
    else {
        die('APP_IS_ONLINE not defined and constants.env.php missing');
    }
} else {
    define('PATH_ASSETS', APP_IS_ONLINE ? 'cdn/' : 'local/');
}



if (isset($_ENV['PHP']['EXEC']) && $_ENV['PHP']['EXEC'] != '' && !defined('PHP_EXEC'))
    switch (PHP_BINARY) {
        case $_ENV['PHP']['EXEC']: // isset issue
            define('PHP_EXEC', PHP_BINARY);
            break;
        default:
            define('PHP_EXEC', $_ENV['PHP']['EXEC'] ?? stripos(PHP_OS, 'LIN') === 0 ? '/usr/bin/php' : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin/psexec.exe -d C:\xampp\php\php.exe -f ');
            break;
    }

if (!defined('PHP_EXEC'))
    define('PHP_EXEC', stripos(PHP_OS, 'LIN') === 0 ? '/usr/bin/php' : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin/psexec.exe -d C:\xampp\php\php.exe -f ');

/*
define('PATH_ASSETS_CSS', PATH_ASSETS . 'css/');
define('PATH_ASSETS_JS', PATH_ASSETS . 'js/');
define('PATH_ASSETS_IMG', PATH_ASSETS . 'img/');
define('PATH_ASSETS_FONTS', PATH_ASSETS . 'fonts/');
define('PATH_ASSETS_LIB', PATH_ASSETS . 'lib/');
define('PATH_ASSETS_LIB_BOOTSTRAP', PATH_ASSETS_LIB . 'bootstrap/');
define('PATH_ASSETS_LIB_JQUERY', PATH_ASSETS_LIB . 'jquery/');
define('PATH_ASSETS_LIB_JQUERY_UI', PATH_ASSETS_LIB . 'jquery-ui/');
define('PATH_ASSETS_LIB_JQUERY_UI_CSS', PATH_ASSETS_LIB_JQUERY_UI . 'css/');
define('PATH_ASSETS_LIB_JQUERY_UI_JS', PATH_ASSETS_LIB_JQUERY_UI . 'js/');
define('PATH_ASSETS_LIB_JQUERY_UI_IMAGES', PATH_ASSETS_LIB_JQUERY_UI . 'images/');
define('PATH_ASSETS_LIB_JQUERY_UI_ICONS', PATH_ASSETS_LIB_JQUERY_UI . 'icons/');
define('PATH_ASSETS_LIB_JQUERY_UI_ICONS_CSS', PATH_ASSETS_LIB_JQUERY_UI_ICONS . 'css/');
define('PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT', PATH_ASSETS_LIB_JQUERY_UI_ICONS . 'font/');
define('PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT_CSS', PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT . 'css/');
define('PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT_TTF', PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT . 'ttf/');
define('PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT_WOFF', PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT . 'woff/');
define('PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT_WOFF2', PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT . 'woff2/');
define('PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT_EOT', PATH_ASSETS_LIB_JQUERY_UI_ICONS_FONT . 'eot/');
// Define the path to the dashboard assets
define('PATH_ASSETS_DASHBOARD', PATH_ASSETS . 'dashboard/');
*/


define('APP_DASHBOARD', "\n" . sprintf(<<<EOL
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
