<?php
declare(strict_types=1);
// config/constants.app.php
// Application configuration

!defined('APP_NAME') and define('APP_NAME', 'CodeInSync - Skeleton App'); // const APP_NAME = 'CodeInSync';
!is_string(APP_NAME)
  and $errors['APP_NAME'] = 'APP_NAME is not a string => ' . var_export(APP_NAME, true); // print('Name: ' . APP_NAME  . ' v' . APP_VERSION . "\n");

!defined('APP_AUTHOR') and define('APP_AUTHOR', 'Barry Dick');  // const APP_AUTHOR = 'Barry Dick';

!defined('APP_VERSION') and define('APP_VERSION', '1.0.0'); // const APP_VERSION = '1.0.0';
!is_string(APP_VERSION) and $errors['APP_VERSION'] = 'APP_VERSION is not a valid string value.';

(version_compare(APP_VERSION, '1.0.0', '>=') == 0)
  and $errors['APP_VERSION'] = 'APP_VERSION is not a valid version (' . APP_VERSION . ').';

//defined('APP_MODE') or define('APP_MODE', 'dispatcher');
//defined('APP_CONTEXT') or define('APP_CONTEXT', 'www');

//!defined('APP_ERROR') and define('APP_ERROR', false); // $hasErrors = true;

/*
        ______   _______  _______           ______   _______  _______  _______  ______  
       (  __  \ (  ___  )(  ____ \|\     /|(  ___ \ (  ___  )(  ___  )(  ____ )(  __  \ 
       | (  \  )| (   ) || (    \/| )   ( || (   ) )| (   ) || (   ) || (    )|| (  \  )
       | |   ) || (___) || (_____ | (___) || (__/ / | |   | || (___) || (____)|| |   ) |
       | |   | ||  ___  |(_____  )|  ___  ||  __ (  | |   | ||  ___  ||     __)| |   | |
       | |   ) || (   ) |      ) || (   ) || (  \ \ | |   | || (   ) || (\ (   | |   ) |
       | (__/  )| )   ( |/\____) || )   ( || )___) )| (___) || )   ( || ) \ \__| (__/  )
       (______/ |/     \|\_______)|/     \||/ \___/ (_______)|/     \||/   \__/(______/ 

        #####                       ###         #####                      
       #     #  ####  #####  ######  #  #    # #     # #   # #    #  ####  
       #       #    # #    # #       #  ##   # #        # #  ##   # #    # 
       #       #    # #    # #####   #  # #  #  #####    #   # #  # #      
       #       #    # #    # #       #  #  # #       #   #   #  # # #      
       #     # #    # #    # #       #  #   ## #     #   #   #   ## #    # 
        #####   ####  #####  ###### ### #    #  #####    #   #    #  #### 

       .o88b.  .d88b.  d8888b. d88888b d888888b d8b   db .d8888. db    db d8b   db  .o88b. 
      d8P  Y8 .8P  Y8. 88  `8D 88'       `88'   888o  88 88'  YP `8b  d8' 888o  88 d8P  Y8 
      8P      88    88 88   88 88ooooo    88    88V8o 88 `8bo.    `8bd8'  88V8o 88 8P      
      8b      88    88 88   88 88~~~~~    88    88 V8o88   `Y8b.    88    88 V8o88 8b      
      Y8b  d8 `8b  d8' 88  .8D 88.       .88.   88  V888 db   8D    88    88  V888 Y8b  d8 
       `Y88P'  `Y88P'  Y8888D' Y88888P Y888888P VP   V8P `8888Y'    YP    VP   V8P  `Y88P'
*/


!defined('APP_DASHBOARD') and define('APP_DASHBOARD', "\n" . sprintf(<<<EOL
  %s

                mmm             #         mmmmm          mmmm                      
              m"   "  mmm    mmm#   mmm     #    m mm   #"   " m   m  m mm    mmm  
              #      #" "#  #" "#  #"  #    #    #"  #  "#mmm  "m m"  #"  #  #"  " 
              #      #   #  #   #  #""""    #    #   #      "#  #m#   #   #  #     
               "mmm" "#m#"  "#m##  "#mm"  mm#mm  #   #  "mmm#"  "#    #   #  "#mm" 
                                                                m"                 
                                                               ""                  
  %s
      {{STATUS}} Version: %s       Written by %s (%s)
  EOL
  ,
  $padding = str_pad('', 92, '='),
  $padding,
  APP_VERSION,
  APP_AUTHOR,
  date('Y')
));

const CONSOLE = true;

!defined('APP_ERRORS') and define('APP_ERRORS', $errors ?? []); // $errors = [];

/* if (APP_ENV == 'development') { 
  if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'constants_backup.php')) // is_file('config' . DIRECTORY_SEPARATOR . 'constants.php')) 
    require_once $path;

  if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'constants_client-project.php')) // is_file('config' . DIRECTORY_SEPARATOR . 'constants.php')) 
    require_once $path;
} */