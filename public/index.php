<?php
  //phpinfo();
  //die(var_dump($_GET));

  // if ($path = (basename(getcwd()) == 'public') chdir('..');
//APP_PATH == dirname(APP_PUBLIC) 
  if ($path = (basename(getcwd()) == 'public') ? (is_file('../config/config.php') ? '../config/config.php' : 'config.php') :
    (is_file('config.php') ? 'config.php' : APP_PATH . APP_BASE['config'] . 'config.php' ))

    //? (is_file('../config.php') ? '../config.php' : 'config.php')
    //: (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null)))

    require_once($path);
  else die(var_dump($path . ' was not found. file=config.php'));

//dd(get_defined_constants(true)['user']);


/** Loading Time: 4.65s **/

  // dd(null, true);

  //dd($_SERVER); php_self, script_name, request_uri /folder/

  // dd(getenv('PATH'));

  switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':    
      //dd(get_required_files(), false);
      //dd($_ENV);
      if (isset($_POST['environment'])) {
        switch ($_POST['environment']) {
          case 'product':
            define('APP_ENV', 'production');
            break;
          case 'develop':
            define('APP_ENV', 'development');
            break;
          case 'math':
            define('APP_ENV', 'math');
            break;
          default:
            define('APP_ENV', 'production');
            break;
        }
        $_ENV['APP_ENV'] = APP_ENV;
        Shutdown::setEnabled(false)->setShutdownMessage(function() {
          return header('Location: ' . APP_WWW); // -wow
        })->shutdown();
      }

    break;
  case 'GET':
      if (isset($_ENV['APP_ENV']) && !empty($_ENV)) define('APP_ENV', $_ENV['APP_ENV']);
      //if (!empty($_GET['path']) && !isset($_GET['app'])) !!infinite loop
      //  exit(header('Location: ' . APP_WWW . $_GET['path']));
// http://localhost/?app=composer&path=vendor

      if (isset($_GET['hide']) && $_GET['hide'] == 'update-notice') {
        $_ENV['HIDE_UPDATE_NOTICE'] = true; // var_export(true, true); // true
        Shutdown::setEnabled(true)->setShutdownMessage(function() {
          return header('Location: ' . APP_WWW); // -wow
        })->shutdown();
      }
        

      if (isset($_GET['category']) && !empty($_GET['category'])) {
        
        if ($_GET['category'] == 'projects')
          exit(header('Location: ' . APP_WWW . '?project='));
        if ($_GET['category'] == 'vendor')
          exit(header('Location: ' . APP_WWW . '?app=composer&path=' . $_GET['category']));
        //if ($_GET['category'] == 'applications')
        //  exit(header('Location: ' . APP_WWW . '?path=' . $_GET['category']));
        exit(header('Location: ' . APP_WWW . '?' . $_GET['category']));
      } elseif (isset($_GET['category']) && empty($_GET['category']))
        exit(header('Location: ' . APP_WWW . '?path'));
        
      if (isset($_GET['path']) && !is_dir(APP_PATH . APP_ROOT)) {
        //dd(APP_PATH . APP_ROOT . ' test');
        die(header('Location: ' . APP_URL_BASE));
      }
      break;
  }
  //dd(APP_PATH . APP_ROOT);
  /*
  switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':    
      //dd($_POST);
  
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS | FILTER_SANITIZE_ENCODED, FILTER_REQUIRE_ARRAY ) ?? [];
  
      break;
    case 'GET':
      $_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS | FILTER_SANITIZE_ENCODED, FILTER_REQUIRE_ARRAY ) ?? [];
      break;
    default:
      foreach(${'_'.$_SERVER['REQUEST_METHOD']} as $key => $value) {
        ${'_'.$_SERVER['REQUEST_METHOD']}[$key] = filter_var($value, (
          is_string($value) ? FILTER_SANITIZE_STRING : (
            is_int($value) ? FILTER_VALIDATE_INT : FILTER_SANITIZE_STRING)
          )
        );
      }
      /*$request_method = '_'.$_SERVER['REQUEST_METHOD'];
      foreach($$request_method as $key => $value) {
        $$request_method[$key] = filter_var($value, (
          is_string($value) ? FILTER_SANITIZE_STRING : (
            is_int($value) ? FILTER_VALIDATE_INT : FILTER_SANITIZE_STRING
          )
        ));
      }*/
  //}
  /**/

 register_shutdown_function([new Shutdown(), 'onShutdown']);
 
 if (defined('APP_ENV') and APP_ENV == 'production' )
   require_once 'idx.product.php';
 elseif (defined('APP_ENV') and APP_ENV == 'development')
   require_once 'idx.develop.php';
 elseif (defined('APP_ENV') and APP_ENV == 'math')
   require_once 'idx.math.php';
 else {
   !defined('APP_ENV') and define('APP_ENV', 'production');
   require_once 'idx.product.php';
 }
