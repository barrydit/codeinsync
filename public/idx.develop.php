<?php

/**This Program Should be Disabled by default ... for debugging purposes only!**/
if (isset($_GET['src']) && is_readable($path = $_GET['src']) && filesize($path) > 0 ) {

  Shutdown::setEnabled(false)->setShutdownMessage(function() use ($path) {
    highlight_file($path); /* return ''; eval('?>' . $project_code); // -wow */
  })->shutdown();

}
  /*
  $_SERVER['REQUEST_SCHEME']
  
  DOMAIN
  
  dd(parse_url($_SERVER['REQUEST_URI'], PHP_URL_HOST));
  */
  
  
  /*
  //use mso\idna_convert;
  
  use phpWhois\Whois;
  
  $whois = new Whois();
  $whois->deepWhois = true;
  
  $query = isset($argv[1]) ? $argv[1] : 'google.com';
  $result = $whois->lookup($query);
  
  $registered = isset($result['regrinfo']['registered']) && $result['regrinfo']['registered'] == 'yes';
  if (!$registered) {
      echo 'Domain: '.$query.' not registered.'.PHP_EOL;
  } else {
      if (isset($result['regrinfo']['domain']['expires'])) {
          echo 'Domain: '.$query.PHP_EOL;
          echo 'Expired: '.$result['regrinfo']['domain']['expires'].PHP_EOL;
      } else {
          echo 'Domain: '.$query.PHP_EOL;
          echo 'Trying to find expires date...'.PHP_EOL;
          foreach ($result['rawdata'] as $raw) {
              if (strpos($raw, 'Expiry Date:') !== false) {
                  echo 'Expired: '.trim(explode(':', $raw)[1]).PHP_EOL;
              }
          }
      }
  }
  dd();
  */


/** Loading Time: 4.77s **/
  
  // dd(null, true);


  use phpWhois\Whois;
  /*
  $whois = new Whois(); // Domain lookup / nserver (Domain lookup)
  $query = 'example.com';
  $result = $whois->lookup($query,false);
  */
  //dd($result);

  // composer require ipinfo/ipinfo:^2.2.0 (^3.1+ req. php 8.2.5)
  //use ipinfo\ipinfo\IPinfo; // api / key service
  //--use ipinfo\ipinfo\IPinfoException;

  
/** Loading Time: 4.84s **/

  //dd(null, true);

  //die(basename(getcwd()) . ' ==' . 'public');
  
  // use Psr\Log\LogLevel;
  
  /* */
  /*
  symfony/
    console
    filesystem
    finder
    process
    service-contracts
    string
  
  --deprecation-contracts    vendor/symfony/deprecation-contracts/function.php
  --polyfill-ctype           vendor/symfony/polyfill-ctype/bootstrap[?:80].php
  --polyfill-intl-grapheme   vendor/symfony/polyfill-intl-grapheme/bootstrap[?:80].php
  --polyfill-intl-normalizer vendor/symfony/polyfill-intl-normalizer/bootstrap[?:80].php
  --polyfill-mbstring        vendor/symfony/polyfill-mbstring/bootstrap[?:80].php
  --polyfill-php73           vendor/symfony/polyfill-php73/bootstrap.php
  --polyfill-php80           vendor/symfony/polyfill-php80/bootstrap.php
  
  
  Psr/
  --Log/LogLevel   vendor/psr/log/Psr/Log/LogLevel.php
  */
  
  //composer[config][require][]


/** Loading Time: 3.67s **/
  
  // dd(null, true);


//echo getcwd();
/**/
  if (in_array(APP_PATH . APP_BASE['config'] . 'composer.php', get_required_files())) {
    if (class_exists('LogLevel'))
      if (null !== LogLevel::DEBUG) // isset() || 
        if (defined('LogLevel'))
          $errors['LogLevel'] = 'Now let\'s use LogLevel... ' . LogLevel::DEBUG . "\n";
  
    //if ($path = (basename(getcwd()) == 'public'))
      //? (is_file('public/ui.composer.php') ? 'public/ui.composer.php' : 'ui.composer.php') : (is_file('ui.composer.php') ? 'ui.composer.php' : 'public/ui.composer.php'))
      //require_once($path); 
    $additionalPaths = [__DIR__ . DIRECTORY_SEPARATOR . 'ui.php.php', __DIR__ . DIRECTORY_SEPARATOR . 'ui.composer.php']; //require_once('public/ui.composer.php'); 
    //else die(var_dump($path . ' was not found. file=ui.composer.php'));
    //dd('wtf');
  }

//dd(__DIR__ . DIRECTORY_SEPARATOR); 

$globPaths = array_filter(glob(__DIR__ . DIRECTORY_SEPARATOR . 'ui.*.php'), 'is_file'); // public/

$paths = array_values(array_unique(array_merge($additionalPaths, $globPaths)));

/*9.4
do {
    // Check if $paths is not empty
    if (!empty($paths)) {
        // Shift the first path from the array
        $path = array_shift($paths);

        // Check if the path exists
        if ($realpath = realpath($path)) {
            // Require the file
            require_once($realpath);
        } else {
            // Output a message if the file was not found
            echo basename($path) . ' was not found. file=public/' . basename($path) . PHP_EOL;
        }
        
        dd('finish time: ' . $path, false);
    }
    // Unset $paths if it is empty
    if (empty($paths)) unset($paths);
} while (isset($paths) && !empty($paths));
*/
// dd(get_defined_vars(), true);

do {
    // Check if $paths is not empty
    if (!empty($paths)) {
        // Shift the first path from the array
        $path = array_shift($paths);

        // Check if the path exists
        if ($realpath = realpath($path)) {

            // Define a function to include the file
//            $requireFile = function($file) /*use ($apps)*/ { global $apps; }; */

            // Include the file using the function
            $returnedValue = require_once $realpath;

            // Check the type of the returned value
            if (is_array($returnedValue)) {
                // The file returned an array
                if (preg_match('/^.*?\.(\w+)\.php$/', $realpath, $matches))
                  !defined($app_name = 'UI_' . strtoupper($matches[1])) and define($app_name, $returnedValue); // $apps[$matches[1]]
            } //elseif ($returnedValue !== null) {
                // The file returned a non-null value
                //echo 'Returned value: ' . $returnedValue . PHP_EOL;
            //} else {
                // The file did not return a value
            //    echo 'File did not return a value.' . PHP_EOL;
            //}
        } else {
            // Output a message if the file was not found
            echo basename($path) . ' was not found. file=public/' . basename($path) . PHP_EOL;
        }
    }
    // Unset $paths if it is empty
    if (empty($paths)) unset($paths);
} while (isset($paths) && !empty($paths));

//dd(get_defined_constants(true)['user']);

/* 9.69 secs
  while ($path = array_shift($paths)) {
    if ($path = realpath($path)) {
      // dd('file:'.basename($path),false);
      require_once($path);
    }
    else dd(basename($path) . ' was not found. file=public/' . basename($path));
  }
 */



//dd('test');


/** Loading Time: 11.27s - 4.77s == 6.51s **/

  //dd('start time: ', false);

  //require_once(__DIR__ . DIRECTORY_SEPARATOR . 'public/ui_complete.php');
  
  //dd('final time: ', true);
/*
  // >> This guy makes the visual screwed up!
  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('ui.git.php') ? 'ui.git.php' : (is_file('../ui.git.php') ? '../ui.git.php' : (is_file('../config/ui.git.php') ? '../config/ui.git.php' : NULL)))
      : (is_file('../ui.git.php') ? '../ui.git.php' : (is_file('public/ui.git.php') ? 'public/ui.git.php' : (is_file('config/ui.git.php') ? 'config/ui.git.php' : 'ui.git.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=ui.git.php'));
*/
/** Loading Time: 9.0s **/
  
  //dd(null, true);
/*
  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('ui.npm.php') ? 'ui.npm.php' : (is_file('../ui.npm.php') ? '../ui.npm.php' : (is_file('../config/ui.npm.php') ? '../config/ui.npm.php' : NULL)))
      : (is_file('../ui.npm.php') ? '../ui.npm.php' : (is_file('public/ui.npm.php') ? 'public/ui.npm.php' : (is_file('config/ui.npm.php') ? 'config/ui.npm.php' : 'ui.npm.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=ui.npm.php'));
*/
/** Loading Time: 11.1s **/
  
  //dd(null, true);
/*
  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('ui.php.php') ? 'ui.php.php' : (is_file('../ui.php.php') ? '../ui.php.php' : (is_file('../config/ui.php.php') ? '../config/ui.php.php' : NULL)))
      : (is_file('../ui.php.php') ? '../ui.php.php' : (is_file('public/ui.php.php') ? 'public/ui.php.php' : (is_file('config/ui.php.php') ? 'config/ui.php.php' : 'ui.php.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=ui.php.php'));
*/
/** Loading Time: 11.3s **/
  
  //dd(null, true);
  
/*
  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('ui.ace_editor.php') ? 'ui.ace_editor.php' : (is_file('../ui.ace_editor.php') ? '../ui.ace_editor.php' : (is_file('../config/ui.ace_editor.php') ? '../config/ui.ace_editor.php' : NULL)))
      : (is_file('../ui.ace_editor.php') ? '../ui.ace_editor.php' : (is_file('public/ui.ace_editor.php') ? 'public/ui.ace_editor.php' : (is_file('config/ui.ace_editor.php') ? 'config/ui.ace_editor.php' : 'ui.ace_editor.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=ui.ace_editor.php'));
*/
/** Loading Time: 4.95s **/
  //dd(null, true);
/*  */
  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('app.timesheet.php') ? 'app.timesheet.php' : (is_file('../app.timesheet.php') ? '../app.timesheet.php' : (is_file('../config/app.timesheet.php') ? '../config/app.timesheet.php' : 'public/app.timesheet.php')))
      : (is_file('../app.timesheet.php') ? '../app.timesheet.php' : (is_file('public/app.timesheet.php') ? 'public/app.timesheet.php' : (is_file('config/app.timesheet.php') ? 'config/app.timesheet.php' : 'app.timesheet.php'))))
    require_once($path);
  else die(var_dump($path . ' was not found. file=app.timesheet.php'));

  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('app.browser.php') ? 'app.browser.php' : (is_file('../app.browser.php') ? '../app.browser.php' : (is_file('../config/app.browser.php') ? '../config/app.browser.php' : NULL)))
      : (is_file('../app.browser.php') ? '../app.browser.php' : (is_file('public/app.browser.php') ? 'public/app.browser.php' : (is_file('config/app.browser.php') ? 'config/app.browser.php' : 'app.browser.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=app.browser.php'));
  
  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('app.github.php') ? 'app.github.php' : (is_file('../app.github.php') ? '../app.github.php' : (is_file('../config/app.github.php') ? '../config/app.github.php' : NULL)))
      : (is_file('../app.github.php') ? '../app.github.php' : (is_file('public/app.github.php') ? 'public/app.github.php' : (is_file('config/app.github.php') ? 'config/app.github.php' : 'app.github.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=app.github.php'));
  
  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('app.packagist.php') ? 'app.packagist.php' : (is_file('../app.packagist.php') ? '../app.packagist.php' : (is_file('../config/app.packagist.php') ? '../config/app.packagist.php' : NULL)))
      : (is_file('../app.packagist.php') ? '../app.packagist.php' : (is_file('public/app.packagist.php') ? 'public/app.packagist.php' : (is_file('config/app.packagist.php') ? 'config/app.packagist.php' : 'app.packagist.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=app.packagist.php'));
  
  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('app.whiteboard.php') ? 'app.whiteboard.php' : (is_file('../app.whiteboard.php') ? '../app.whiteboard.php' : (is_file('../config/app.whiteboard.php') ? '../config/app.whiteboard.php' : NULL)))
      : (is_file('../app.whiteboard.php') ? '../app.whiteboard.php' : (is_file('public/app.whiteboard.php') ? 'public/app.whiteboard.php' : (is_file('config/app.whiteboard.php') ? 'config/app.whiteboard.php' : 'app.whiteboard.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=app.whiteboard.php'));
  
  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('app.notes.php') ? 'app.notes.php' : (is_file('../app.notes.php') ? '../app.notes.php' : (is_file('../config/app.notes.php') ? '../config/app.notes.php' : NULL)))
      : (is_file('../app.notes.php') ? '../app.notes.php' : (is_file('public/app.notes.php') ? 'public/app.notes.php' : (is_file('config/app.notes.php') ? 'config/app.notes.php' : 'app.notes.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=app.notes.php'));
  
  if ($path = (basename(getcwd()) == 'public')
      ? (is_file('app.pong.php') ? 'app.pong.php' : (is_file('../app.pong.php') ? '../app.pong.php' : (is_file('../config/app.pong.php') ? '../config/app.pong.php' : NULL)))
      : (is_file('../app.pong.php') ? '../app.pong.php' : (is_file('public/app.pong.php') ? 'public/app.pong.php' : (is_file('config/app.pong.php') ? 'config/app.pong.php' : 'app.pong.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=app.pong.php'));

  if ($path = (basename(getcwd()) == 'public') // composer_app.php (depend.)
      ? (is_file('app.backup.php') ? 'app.backup.php' : (is_file('../app.backup.php') ? '../app.backup.php' : (is_file('../config/app.backup.php') ? '../config/app.backup.php' : 'public/app.backup.php')))
      : (is_file('../app.backup.php') ? '../app.backup.php' : (is_file('public/app.backup.php') ? 'public/app.backup.php' : (is_file('config/app.backup.php') ? 'config/app.backup.php' : 'app.backup.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=app.backup.php'));

  if ($path = (basename(getcwd()) == 'public') // composer_app.php (depend.)
      ? (is_file('app.project.php') ? 'app.project.php' : (is_file('../app.project.php') ? '../app.project.php' : (is_file('../config/app.project.php') ? '../config/app.project.php' : 'public/app.project.php')))
      : (is_file('../app.project.php') ? '../app.project.php' : (is_file('public/app.project.php') ? 'public/app.project.php' : (is_file('config/app.project.php') ? 'config/app.project.php' : 'app.project.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=app.project.php'));

  if ($path = (basename(getcwd()) == 'public') // composer_app.php (depend.)
      ? (is_file('app.console.php') ? 'app.console.php' : (is_file('../app.console.php') ? '../app.console.php' : (is_file('../config/app.console.php') ? '../config/app.console.php' : 'public/app.console.php')))
      : (is_file('../app.console.php') ? '../app.console.php' : (is_file('public/app.console.php') ? 'public/app.console.php' : (is_file('config/app.console.php') ? 'config/app.console.php' : 'app.console.php'))))
    require_once($path); 
  else die(var_dump($path . ' was not found. file=app.console.php'));

/** Loading Time: 12.2s **/
  
  //dd(get_required_files(), true);

//  header("Content-Type: text/html");
//  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
//  header("Pragma: no-cache"); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?=(!is_array(APP_URL) ? APP_URL : APP_URL_BASE) . (APP_URL_BASE['query'] != '' ? '?'. APP_URL_BASE['query'] : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : ''); ?>">
    <!-- link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" / -->
    
    <title>WebPortal</title>
    <?php
      // (check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/tailwindcss-3.3.5.js')?
      is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/') or mkdir($path, 0755, true);
      if (is_file($path . 'tailwindcss-3.3.5.js')) {
        if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime("{$path}tailwindcss-3.3.5.js"))))) / 86400)) <= 0 ) {
          $url = 'https://cdn.tailwindcss.com';
          $handle = curl_init($url);
          curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
      
          if (!empty($js = curl_exec($handle))) 
            file_put_contents("{$path}tailwindcss-3.3.5.js", $js) or $errors['JS-TAILWIND'] = "$url returned empty.";
        }
      } else {
        $url = 'https://cdn.tailwindcss.com';
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
      
        if (!empty($js = curl_exec($handle))) 
          file_put_contents("{$path}tailwindcss-3.3.5.js", $js) or $errors['JS-TAILWIND'] = "$url returned empty.";
      }
      unset($path);
      ?>
    <script src="<?= APP_BASE['resources'] . 'js/tailwindcss-3.3.5.js' ?? $url ?>"></script>
    <style type="text/tailwindcss">
      * {
<?php if (isset($_GET['debug'])) { ?>
        border: 1px dashed #FF0000;
<?php } else { ?> 
        /* border: 1px dashed #FF0000; */
<?php } ?>
      }
      body {
        background-color: #FFF;
        overflow: hidden;
      }
      .row-container { display: flex; width: 100%; height: 100%; flex-direction: column; overflow: hidden; }
      <?= (defined('UI_GIT') ? UI_GIT['style'] : null); ?>
      <?= (defined('UI_PHP') ? UI_PHP['style'] : null); /* print(...) */ ?>
      <?= (defined('UI_COMPOSER') ? UI_COMPOSER['style'] : null); /* (isset($appComposer) ? $appComposer['script'] : null); */ ?>
      <?= (defined('UI_NPM') ? UI_NPM['style'] : null); ?>
      <?= (defined('UI_ACE_EDITOR') ? UI_ACE_EDITOR['style'] : null); ?>

      <?= $app['browser']['style']; ?>
      <?= $app['github']['style']; ?>
      <?= $app['packagist']['style']; ?>
      <?= $app['whiteboard']['style']; ?>
      <?= $app['notes']['style']; ?> 
      <?= $app['pong']['style']; ?>

      <?= $app['backup']['style']; ?>
      <?= $app['console']['style']; ?>
      <?= $app['timesheet']['style']; ?>
      <?= $app['project']['style']; ?>
      .container2 {
      position: relative;
      display: inline-block;
      text-align: center;
      z-index: 1;
      }
      .overlay {
      position: absolute;
      top: 25px;
      left: 10px;
      width: 150px;
      height: 225px;
      background-color: rgba(0, 120, 215, 0.7);
      color: white;
      /*font-size: 24px;*/
      text-align: left;
      opacity: 0;
      transition: opacity 0.8s;
      }
      .pkg_dir:hover .overlay {
      opacity: 1;
      }
      table {
      border-collapse: separate;
      border-spacing: 10px;
      border-color: #fff;
      }
      td, th {
      padding: 8px;
      /* text-align: center; */
      }
      /* the interesting bit */
      .label {
      pointer-events: none;
      display: flex;
      align-items: center;
      }
      .switch,
      .input:checked + .label .left,
      .input:not(:checked) + .label .right {
      pointer-events: all;
      cursor: pointer;
      }
      /* most of the stuff below is the same as the W3Schools stuff,
      but modified a bit to reflect changed HTML structure */
      .input {
      display: none;
      }
      .switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
      }
      .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      -webkit-transition: 0.4s;
      transition: 0.4s;
      }
      .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      -webkit-transition: 0.4s;
      transition: 0.4s;
      }
      
      input:checked + .label .slider {
      background-color: #2196f3;
      }
      input:focus + .label .slider {
      box-shadow: 0 0 1px #2196f3;
      }
      input:checked + .label .slider:before {
      -webkit-transform: translateX(26px);
      -ms-transform: translateX(26px);
      transform: translateX(26px);
      }
      .slider.round {
      border-radius: 34px;
      }
      .slider.round:before {
      border-radius: 50%;
      }
      /* styling to make it look like your screenshot */
      .left, .right {
      margin: 0 .5em;
      font-weight: bold;
      text-transform: uppercase;
      font-family: sans-serif;
      }
      .ui-widget-header {
      cursor: pointer;
      }
    </style>
  </head>
  <body>

    <div style="position: relative; width: 100%; height: 100%; z-index: 1; border: 1px solid green;">

    <div class="row-container" style="position: absolute; left: 0; top: 0;">
      <?php // https://stackoverflow.com/questions/86428/what-s-the-best-way-to-reload-refresh-an-iframe ?>
      <iframe id="iWindow" src="<?php if (!empty($_GET['client'])) {
          $path = /*'../../'.*/ 'clientele/' . $_GET['client'] . '/';
          $dirs = array_filter(glob(dirname(__DIR__) . '/' . $path . '*'), 'is_dir');
          
          if (count($dirs) == 1)
            foreach($dirs as $dir) {
              $dirs[0] = $dirs[array_key_first($dirs)];
              if (preg_match(DOMAIN_EXP, strtolower(basename($dirs[0])))) {
                $_GET['domain'] = basename($dirs[0]);
                break;
              }
              else unset($dirs[array_key_first($dirs)]);
              continue;
            }
        
          $dirs = array_filter(glob(dirname(__DIR__) . '/' . $path . '*'), 'is_dir');
        
          if (!empty($_GET['domain']))
            foreach($dirs as $key => $dir) {
              if (basename($dir) == $_GET['domain']) {
                //$path .= 'davidraymant.ca/';
        
                if (is_dir($dirs[$key].'/public/'))
                  $path .= basename($dirs[$key]).'/public/';
                else 
                  $path .= basename($dirs[$key]);
                break;
              }
            }
            //else 
            //exit(header('Location: http://localhost/clientele/' . $_GET['client']));    
        
          //$path = '?path=' . $path;
        } elseif (!empty($_GET['project'])) {
          $path = '/projects/' . $_GET['project'] . '/';   
          //$dirs = array_filter(glob(dirname(__DIR__) . '/projects/' . $_GET['project'] . '/*'), 'is_dir');
          
        } else { $path = ''; } 
        
        if (empty(APP_URL['query'])) echo 'developer.php';
        else echo $path; // developer.php
        ?>" style="height: 100%;"></iframe>
    </div>
    <?= $app['backup']['body']; ?>
    <div style="position: relative; margin: 0px auto; width: 100%; border: 1px solid #000;">
      <div style="position: relative; margin: 0px auto; width: 800px;">
        <div style="position: absolute; left: -130px; width: 150px; z-index: 3;">
          <!--form action="#!" method="GET">
            <?= (isset($_GET['debug']) && !$_GET['debug'] ? '' : '<input type="hidden" name="debug" value / >') ?> 
                  <input class="input" id="toggle-debug" type="checkbox" onchange="this.form.submit();" <?= (isset($_GET['debug']) || defined('APP_ENV') && APP_ENV == 'development'? 'checked' : '') ?> / -->
          <input class="input" id="toggle-debug" type="checkbox" onchange="toggleSwitch(this); return null;" <?= (isset($_GET['debug']) || defined('APP_ENV') && APP_ENV == 'development' ? 'checked' : '') ?> />
          <label class="label" for="toggle-debug" style="margin-left: -6px;">
            <div class="switch">
              <span class="slider round"></span>
            </div>
            <div class="right"> <a href="?debug">Debug</a> </div>
          </label>
          <!-- /form -->
        </div>
        <div id="debug-content" class="absolute" style="position: absolute; margin-left: auto; margin-right: auto; left: 0; right: 0; text-align: center; background-color: rgba(255, 255, 255, 0.8); border: 1px solid #000; width: 800px; z-index: 1;">
          <div style="position: absolute; top: 35px; left: 403px; z-index: 2;">
            <a href="#" onclick="document.getElementById('app_packagist-container').style.display='block';"><img src="resources/images/packagist_icon.png" width="30" height="34"> Packagist</a>
          </div>
          <a href="#" onclick="document.getElementById('app_github-container').style.display='block';"><img src="resources/images/github_icon.png" width="72" height="23"></a> |
          <a href="#" onclick="document.getElementById('app_git-container').style.display='block';"><img src="resources/images/git_icon.png" width="58" height="24"></a> | <a href="#" onclick="document.getElementById('app_npm-container').style.display='block';"><img src="resources/images/npm_icon.png" width="32" height="32"> Node.js</a>
          |
          <a href="#" onclick="document.getElementById('app_php-container').style.display='block';"><img src="resources/images/php_icon.png" width="40" height="27"> PHP <?= (preg_match("#^(\d+\.\d+)#", PHP_VERSION, $match) ? $match[1] : '8.0' ) ?></a> | <a href="#" onclick="document.getElementById('app_composer-container').style.display='block';"><img src="resources/images/composer_icon.png" width="31" height="40"> Composer</a> |
          <a href="#" onclick="document.getElementById('app_ace_editor-container').style.display='block';"><img src="resources/images/ace_editor_icon.png" width="32" height="32"> Editor</a> |
          <a href="#" onclick="document.getElementById('app_tools-container').style.display='block';"><img src="resources/images/apps_icon.gif" width="20" height="20"> Tools</a> |
          <a href="#" onclick="document.getElementById('app_timesheet-container').style.display='block';"><img src="resources/images/clock.gif" width="30" height="30"> Clock-In</a>
          <div style="position: absolute; top: 40px; left: 0; z-index: 1;">
            <?php $path = realpath(APP_ROOT . (isset($_GET['path']) ? DIRECTORY_SEPARATOR . $_GET['path'] : '')) . DIRECTORY_SEPARATOR; // getcwd()
              if (isset($_GET['path'])) { ?>
            <!-- <input type="hidden" name="path" value="<?= $_GET['path']; ?>" /> -->
            <?php } ?>
            <?= 
              //APP_URL_BASE . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ array( 'app' => 'ace_editor')*/) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') 
              
              /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ NULL; ?>
            <?= '          <button id="displayDirectoryBtn" style="float: left; margin: 2px 5px 0 0;" type="">&#9660;</button> ' . "\n"; ?>
            <?php
              $main_cat = '        <form style="display: inline;" autocomplete="off" spellcheck="false" action="" method="GET">/'  . "\n"
              . '            <select name="category" onchange="this.form.submit();">' . "\n"
              
              . '              <option value="" ' . (empty(APP_QUERY) ? 'selected' : '') . '>' . basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) . '</option>' . "\n"
              . '              <option value="application" ' . (isset($_GET['application']) ? 'selected' : '') . ' ' . (realpath(APP_PATH . /*'../../'.*/ 'applications/') ? '' : 'disabled') . '>../applications</option>' . "\n"
              . '              <option value="client" ' . (isset($_GET['client']) ? 'selected' : '') . '>../clientele</option>' . "\n"
              . '              <option value="projects" ' . (isset($_GET['project']) && $_GET['project'] || preg_match('/(?:^|&)project(?:[^&]*=)/', $_SERVER['QUERY_STRING']) ? 'selected' : '') . '>../projects</option>' . "\n"
              . '              <option value="node_module" ' . (isset($_GET['node_module']) && !$_GET['node_module'] && preg_match('/(?:^|&)node_module(?![^&]*=)/', $_SERVER['QUERY_STRING']) ? 'selected' : '') . '>./node_modules</option>' . "\n"
              . '              <option value="resources" ' . (isset($_GET['path']) && $_GET['path'] == 'resources' ? 'selected' : '') . '>./resources</option>' . "\n"
              . '              <option value="project" ' . (isset($_GET['project']) && !$_GET['project'] && preg_match('/(?:^|&)project(?![^&]*=)/', $_SERVER['QUERY_STRING']) ? 'selected' : '') . '>./project</option>' . "\n"
              . '              <option value="vendor" ' . (isset($_GET['path']) && $_GET['path'] == 'vendor' ? 'selected' : '') . '>./vendor</option>' . "\n"
              . '            </select>' . "\n"
              . '        </form>';

              if (isset($_GET['project']) /*&& $_GET['project'] != ''*/) {
                if ($_GET['project'] == '' || !empty($_GET['project'])) echo $main_cat;

                $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'projects/*'), 'is_dir');
              ?>

            <form style="display: inline;" autocomplete="off" spellcheck="false" action="" method="GET">
              <span title="" style="cursor: pointer; margin: 2px 5px 0 0; " onclick="">/
              <select name="project" style="" onchange="this.form.submit(); return false;">
                <option value="">---</option>
                <?php
                  while ($link = array_shift($links)) {
                    $link = basename($link); // Get the directory name from the full path
                    if (is_dir(APP_PATH . /*'../../'.*/ 'projects/' . $link))
                      echo '              <option value="' . $link . '" ' . (current($_GET) == $link ? 'selected' : '') . '>' . $link . '</option>' . "\n";
                  } ?>
              </select> /
            </span>
            </form>

            <?php
              } elseif (isset($_GET['client']) /*&& $_GET['client'] != ''*/ ) {
              if ($_GET['client'] == '') echo $main_cat;
              
              $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'clientele/*'), 'is_dir');
                       /* */
              ?>
            <form style="display: inline;" autocomplete="off" spellcheck="false" action="" method="GET">
              <span title="" style="cursor: pointer; margin: 2px 5px 0 0; " onclick="">/
              <select name="client" style="" onchange="this.form.submit(); return false;">
                <option value="" style="text-align: center;">--clientele--</option>
                <?php
                  while ($link = array_shift($links)) {
                    $link = basename($link); // Get the directory name from the full path
                    if (is_dir(APP_PATH . /*'../../'.*/ 'clientele/' . $link))
                      echo '              <option value="' . $link . '" ' . (current($_GET) == $link ? 'selected' : '') . '>' . $link . '</option>' . "\n";
                  }
                  ?>
              </select>/
              </span>
            </form>

            
            <?php if (!empty($_GET['client'])) {
              $dirs = array_filter(glob(dirname(__DIR__) . /*'../../'.*/ '/clientele/' . $_GET['client'] . '/*'), 'is_dir'); ?>            
            <form style="display: inline;" autocomplete="off" spellcheck="false" action="" method="GET">
              <?= (isset($_GET['client']) && !$_GET['client'] ? '' : '<input type="hidden" name="client" value="' . $_GET['client'] . '" / >') ?> 
              <select id="domain" name="domain" onchange="this.form.submit();">
                <option value="">---</option>
                <?php foreach ($dirs as $dir) { ?>
                <option <?= (isset($_GET['domain']) && $_GET['domain'] == basename($dir) ? 'selected' : '') ?>><?= basename($dir); ?></option>
                <?php } ?>
              </select>/


            </form>
              <?php } ?>

          <?php } else {
            //.'<a style="" href="' . (APP_URL['query'] != '' ? '?' . APP_URL['query'] : '') . (isset($_GET['path']) && $_GET['path'] != '' ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') ) . '"></a>'
            
            
            echo //'        <form style="display: inline;" action method="GET">'
            $main_cat;
            //. '        </form>' . "\n";
            
            echo '        <form style="display: inline;" action method="GET">' . "\n"
            . '          <span title="' . APP_PATH . '" style="margin: 2px 5px 0 0; cursor: pointer;" onclick=""> / ' . "\n"; /* $path; */ ?>
          <select name="path" style="" onchange="this.form.submit(); return false;">
            <option value="">.</option>
            <option value="">..</option>
            <?php
              // Bug if the dir does not exist it defaults to the root ...

              if (APP_PATH)
                foreach (array_filter( glob( APP_PATH . APP_ROOT . '*'), 'is_dir') as $dir) {
                  echo '              <option value="' . (isset($_GET['path']) ?  $_GET['path'] . DIRECTORY_SEPARATOR : '') . basename($dir) . '"' . (isset($_GET['path']) && $_GET['path'] == basename($dir) ? ' selected' : '' )  . '>' . basename($dir) . '/</option>' . "\n";
                }
              ?>
          </select>/
          </span>
          </form>

<?php } ?>
          </div>
          <div style="position: absolute; width: 285px; top: 40px; right: -10px; border: 1px solid #000; height: 25px;">
            <div style="display: inline-block; width: 175px; ">
              <div id="idleTime" style="display: inline; margin: 7px 5px;"></div>
              <div>
                <div id="stats">Idle: [0]&nbsp;&nbsp;<span style="color: black;">00:00:00</span></div>
              </div>
            </div>
            <div style="display: inline-block; width: 100px;">
              <img id="ts-status-light" style="padding-bottom: 25px; cursor: pointer;" src="resources/images/timesheet-light-Clear-2.gif" width="80" height="30" />
            </div>
          </div>
          <div id="app_tools-container" style="position: absolute; display: none; width: 800px; margin: 0 auto; height: 500px; background-color: rgba(255, 255, 255, 0.9); overflow-x: scroll;">
            <div style="position: absolute; margin: 80px 45px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_tools-container').style.display='none'; return false;"><img style="text-align: center;" height="25" width="25" src="<?= APP_BASE['resources'] . 'images/close-red.gif' ?>" /></a><br /></div>
            <div style="position: absolute; margin: 100px 75px; text-align: center;" class="text-sm"><a href="#!" onclick="isFixed = true; show_console(); return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/cli.png' ?>" /></a><br /><a href="?app=ace_editor&path=&file=app.console.php" style="text-align: center;">(CLI)</a></div>
            <!-- 
              <a href="javascript:window.open('print.html', 'newwindow', 'width=300,height=250')">Print</a>
              onclick="window.open('app.whiteboard.php', 'newwindow', 'width=300,height=250'); return false;"
              
              https://stackoverflow.com/questions/12939928/make-a-link-open-a-new-window-not-tab
               -->
            <div style="position: absolute; margin: 100px 165px; text-align: center;" class="text-sm"><a href="app.whiteboard.php" target="_blank" onclick="document.getElementById('app_whiteboard-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/whiteboard.png' ?>" /></a><br /><a href="?app=ace_editor&path=&file=app.whiteboard.php" style="text-align: center;">Whiteboard</a></div>
            <div style="position: absolute; margin: 100px 260px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_notes-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/notes.png' ?>" /></a><br /><a href="?app=ace_editor&path=&file=app.notes.php" style="text-align: center;">Notes</a></div>
            <div style="position: absolute; margin: 100px 350px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_project-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/project.png' ?>" /></a><br /><a href="?app=ace_editor&path=&file=app.project.php"><span style="text-align: center;">Project</span></a></div>
            <div style="position: absolute; margin: 100px 0 0 450px ; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_debug-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/debug.png' ?>" /><br /><span style="text-align: center;">Debug</span></a></div>
            <div style="position: absolute; margin: 100px 0 0 540px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_profile-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/user.png' ?>" /><br /><span style="text-align: center;">Profile</span></a></div>
            <div style="position: absolute; margin: 100px 0 0 630px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/browser.png' ?>" /><br /><span style="text-align: center;">Browser</span></a></div>
            <div style="position: absolute; margin: 200px 75px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_tools-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/apps.png' ?>" /><br /><span style="text-align: center;">Apps.</span></a></div>
            <div style="position: absolute; margin: 200px 170px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_calendar-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/calendar.png' ?>" /><br /><span style="text-align: center;">Calendar</span></a></div>
            <div style="position: absolute; margin: 190px 240px; padding: 20px 40px; background-color: rgba(255, 255, 255, 0.8);">
              <form action="#!" method="GET">
                <?= '            ' . (isset($_GET['project']) && !$_GET['project'] ? '<input type="hidden" name="client" value="" />' : '<input type="hidden" name="project" value="" />') ?>
                <div style="margin: 0 auto;">
                  <div id="clockTime"></div>
                </div>
                <input class="input" id="toggle-project" type="checkbox" onchange="toggleSwitch(this); this.form.submit();" <?= (isset($_GET['project']) ? 'checked' : '') ?> />
                <label class="label" for="toggle-project" style="margin-left: -6px;">
                  <div class="left"> Client </div>
                  <div class="switch"><span class="slider round"></span></div>
                  <div class="right"> Project </div>
                </label>
              </form>
            </div>
            <div style="position: absolute; margin: 200px 0 0 540px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_pong-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/pong.png' ?>" /><br /><span style="text-align: center;">Pong</span></a></div>
            <div style="position: absolute; margin: 200px 0 0 630px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/regexp.png' ?>" /><br /><span style="text-align: center;">RegExp</span></a></div>
            <div style="position: absolute; margin: 300px 75px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/chatgpt.png' ?>" /><br /><span style="text-align: center;">ChatGPT</span></a></div>
            <div style="position: absolute; margin: 300px 160px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/stackoverflow.png' ?>" /><br /><span style="text-align: center;">Stackoverflow</span></a></div>
            <div style="position: absolute; margin: 300px 260px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/validatejs.png' ?>" /><br /><span style="text-align: center;">ValidateJS</span></a></div>
            <!-- https://validator.w3.org/#validate_by_input // -->
            <div style="position: absolute; margin: 300px 340px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/w3c.png' ?>" /><br /><span style="text-align: center;">W3C Validator</span></a></div>
            <!-- https://tailwindcss.com/docs/ // -->
            <div style="position: absolute; margin: 300px 0 0 445px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/tailwindcss.png' ?>" /><br /><span style="text-align: center;">TailwindCSS<br />Docs</span></a></div>
            <!-- https://www.php.net/docs.php // -->
            <div style="position: absolute; margin: 300px 0 0 540px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/php.png' ?>" /><br /><span style="text-align: center;">PHP Docs</span></a></div>
            <!-- https://dev.mysql.com/doc/ // -->
            <div style="position: absolute; margin: 300px 0 0 625px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/mysql.png' ?>" /><br /><span style="text-align: center;">MySQL Docs</span></a></div>
            <div style="position: absolute; top: 400px; left: 65px; width: 80%; margin: 0 auto; height: 15px; border-bottom: 1px solid black; text-align: center; z-index: 0;">
              <span style="font-size: 20px; background-color: #F3F5F6; padding: 0 20px; z-index: 1;"> USER APPS. </span>
            </div>
            <div style="position: absolute; margin: 430px 75px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_install-container').style.display='block'; return false;"><span style="text-align: center;">New App.</span><br /><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/install.png' ?>" /></a></div>
            <div style="position: absolute; margin: 430px 170px; text-align: center;" class="text-sm">
              <a href="?app=ace_editor&path=&file=app.user-app.php"><span style="text-align: center;">App #1</span></a><br />
              <a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/php-app.png' ?>" /></a>
              <div style="height: 75px;"></div>
            </div>
          </div>
        </div>

      </div>


      <?php /*
        <div id="app_project-container" style="display: none; position: absolute; top: 80px; padding: 20px; margin-left: auto; margin-right: auto; left: 0; right: 0; width: 700px; z-index: 2;">
          <div style="margin: -25px 0 20px 0;">
            <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_project-container').style.display='none';">X</a>]</div>
          </div>
          <form style="background-color: #ddd; padding: 20px;">
            <h3>Psr/Log</h3>
            <label><input type="checkbox" checked> Add to Project.</label>
            <button type="submit" style="float: right;">Save</button>
            <iframe src="<?= APP_URL ?>?project=show" style="height: 300px; width: 600px;"></iframe>
      </form>
    </div>
    */ ?>
    <div style="position: relative;">
      <?php if (isset($_GET['client']) && $_GET['client'] != '' && isset($_GET['domain']) && $_GET['domain'] != '') { ?>
      <div id="app_client-container" style="position: relative; top: 100px; margin: 0 auto; width: 800px; height: 600px; background-color: rgba(255, 255, 255, 0.9); overflow-x: scroll;">
        <div style="display: inline;">
          <span style="background-color: #B0B0B0; color: white;">
          <input type="checkbox" checked /> Preview Domain
          </span>
        </div>
        <div style="display: inline; float: right; text-align: center; ">
          <code style=" background-color: white; color: #0078D7;">
          <a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_client-container').style.display='none';">[X]</a>
          </code>
        </div>
        <div style="margin: 0 10px;">
          <div style="display: inline-block; float: left; width: 49%;">
<?php
  $input = $_GET['client'];
              
  // Decode the URL-encoded string
  $decoded = urldecode($input);
              
  // Use regex to extract name components
  if (preg_match('/^\d*-(\w+)[,]\s*(\w+)$/', $decoded, $matches)) {
    // $matches[1] contains the last name, $matches[2] contains the first name
    $output = $matches[2] . ' ' . $matches[1];
  } else {
    $output = 'Invalid Input';
  }
?>
            Work Status: 
            <select>
              <?php
                foreach(['000', '100', '200', '300', '400'] as $key => $status) {
                
                $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'clientele/' . $status . '*'), 'is_dir');
                $statusCode = $status;
                $status = ($status == 000) ? "On-call" :
                         (($status == 100) ? "Working" :
                         (($status == 200) ? "Planning" :
                         (($status == 300) ? "Previous" :
                         (($status == 400) ? "Future" : "Unknown"))));
                ?>
              <option><?= $statusCode . ' - ' . $status ?></option>
              <?php
                $count = 1;
                }
                ?>
            </select>
            <br />
            Name: <input type="text" value="<?= $output; ?>" /><br />
            Hours: <input type="text" value="999" />
          </div>
          <div style="display: inline-block; float: right; text-align: right;">
            <span style="">
              Domain: 
              <select>
                <option>davidraymant.ca</option>
              </select>
            </span>
            <br />
            <span style="">Add Domain: <input type="text"></span><br />
            <span>Domain Expiry: <input type="text" value="
              <?php
                $result = [];
                
                if (check_http_status()) {
                  $whois = new Whois();
                  $query = 'example.com';
                  $result = $whois->lookup($query,false);
                }
                
                echo !empty($result) && isset($result['regrinfo']['domain']['expires']) ? $result['regrinfo']['domain']['expires'] : 'Unknown';
                 ?>" style="text-align: right;"/></span><br /><br />
          </div>
          <div style="clear: both;"></div>
          <div>
            <span>Domain Information</span><br />
            <ul class="text-xs">
              <li>[regrinfo][domain][name] == Domain Name,  [regrinfo][type] == 'domain',  [regyinfo][registered] == 'yes'<br />
                [???] Registrar Information, <br />
                <br />
                [regrinfo][domain][expires] == Domain Expiry Date (Timestamp), <br />
                <br />
                [regyinfo][registrar] == 'RESERVED-Internet Assigned Numbers Authority' (DNS Provider)<br />
                [regrinfo][domain][nserver][a.iana-servers.net] == 199.43.135.53<br />
                [regrinfo][domain][nserver][b.iana-servers.net] == 199.43.135.53<br />
                <br />
                [regrinfo][domain][status][0..2]<br />
                Domain Status: clientDeleteProhibited https://icann.org/epp#clientDeleteProhibited<br />
                Domain Status: clientTransferProhibited https://icann.org/epp#clientTransferProhibited<br />
                Domain Status: clientUpdateProhibited https://icann.org/epp#clientUpdateProhibited<br />
              </li>
            </ul>
            <br />
            <span>Server/Hosting Information</span>
            <ul class="text-xs">
              <li>
                Hosting Provider, intranet (localhost) / internet (google.ca)<br />
                <div style="display: inline-block; float: right; text-align: right;">        
                  <span>Name: <input type="text"></span><br />
                </div>
                <div style="clear: both;"></div>
                Server IP Address<br />
                <div style="display: inline-block; float: right; text-align: right;">
                  <span>
                    IPv4/IPv6: <!-- input type="text" -->
                    <select>
                    <?php
                      /* $access_token = '123456789abc'; */
                      //$client = new IPinfo(/*$access_token*/);
                      //$ip_address = '93.184.216.34';
                      //$details = $client->getDetails($ip_address);
                      
                      //dd($details->all);
                      
                      
                      if (!empty($ip_addrs = gethostbynamel($dname['regrinfo']['domain']['name'] = 'example.com')))
                        foreach ($ip_addrs as $ip_addr) {
                          echo '            <option>' . $ip_addr . '</option>' . "\n";
                        }
                      else
                        echo '            <option></option>' . "\n";
                      
                      ?>
                    </select>
                  </span>
                  <br />
                </div>
                <div style="clear: both;"></div>
                Control Panel URL<br />
                <div style="display: inline-block; float: right; text-align: right;">        
                  <span>URL: <input type="text"></span><br />
                </div>
                <div style="clear: both;"></div>
                FTP Credentials (as you mentioned)<br />
                <div style="display: inline-block; float: right; text-align: right;">        
                  <span>FTP Host: <input type="text"></span><br />
                  <span>FTP User: <input type="text"></span><br />
                  <span>FTP Password: <input type="text"></span><br />
                </div>
                <div style="clear: both;"></div>
                SSH Credentials<br />
                <div style="display: inline-block; float: right; text-align: right;">        
                  <span>SSH Host: <input type="text"></span><br />
                  <span>SSH User: <input type="text"></span><br />
                  <span>SSH Password: <input type="text"></span><br />
                </div>
                <div style="clear: both;"></div>
                Database Access Credentials<br />
                <div style="display: inline-block; float: right; text-align: right;">        
                  <span>DB Host: <input type="text"></span><br />
                  <span>DB User: <input type="text"></span><br />
                  <span>DB Password: <input type="text"></span><br />
                </div>
                <div style="clear: both;"></div>
              </li>
            </ul>
            <br />
            <span>Website Configuration</span><br />
            <ul class="text-xs">
              <li>Content Management System (CMS) Information<br />
                Configuration Files (e.g., wp-config.php for WordPress)<br />
                API Keys and Secrets<br />
                CDN Configuration<br />
              </li>
            </ul>
            <br />
            <span>SSL Certificate</span><br />
            <ul class="text-xs">
              <li>SSL Certificate Details<br />
                Expiry Date<br />
              </li>
            </ul>
            <br />
            <span>Development and Deployment</span><br />
            <ul class="text-xs">
              <li>Version Control Information (e.g., Git repository URL)<br />
                Deployment Scripts/Procedures<br />
                Staging Environment Information<br />
              </li>
            </ul>
            <br />
            <span>Analytics and SEO</span><br />
            <ul class="text-xs">
              <li>Google Analytics Code<br />
                SEO Keywords<br />
                Meta Tags<br />
                Search Console Information<br />
              </li>
            </ul>
            <br />
            <span>Backup and Recovery</span><br />
            <ul class="text-xs">
              <li>Backup Schedule<br />
                Backup Storage Location<br />
                Disaster Recovery Plan<br />
              </li>
            </ul>
            <br />
            <span>Contact Information</span><br />
            <ul class="text-xs">
              <li>Technical Contact<br />
                Administrative Contact<br />
                Support Contact<br />
              </li>
            </ul>
            <br />
            <span>Monitoring and Alerts</span><br />
            <ul class="text-xs">
              <li>Monitoring Tools and URLs<br />
                Alert Configuration<br />
              </li>
            </ul>
            <br />
            <span>Third-Party Services</span><br />
            <ul class="text-xs">
              <li>API Keys for External Services (e.g., Email Service, Payment Gateway)<br />
                Integration Details<br />
              </li>
            </ul>
            <br />
            <span>Content and Media</span><br />
            <ul class="text-xs">
              <li>Content Inventory<br />
                Media Files and Storage Locations<br />
              </li>
            </ul>
            <br />
            <span>Security</span><br />
            <ul class="text-xs">
              <li>Security Measures in Place<br />
                Incident Response Plan<br />
              </li>
            </ul>
            <br />
            <span>Documentation</span><br />
            <ul class="text-xs">
              <li>Wiki/Documentation URLs<br />
                Standard Operating Procedures (SOPs)<br />
              </li>
            </ul>
            <br />
            <span>Testing and Quality Assurance</span><br />
            <ul class="text-xs">
              <li>Testing Environments<br />
                Test Cases<br />
              </li>
            </ul>
            <br />
            <span>License Information</span><br />
            <ul class="text-xs">
              <li>Software Licenses<br />
                Theme/Plugin Licenses<br />
              </li>
            </ul>
            <br />
          </div>
        </div>
      </div>
      <?php } ?>
      <?= $app['directory']['body']; ?>
    </div>

    <?= $app['timesheet']['body']; ?>
    <?= $app['browser']['body']; ?>
    <?= $app['github']['body']; ?>
    <?= $app['packagist']['body']; ?>
    <?= $app['whiteboard']['body']; ?>
    <?= $app['notes']['body']; ?>
    <!-- https://pong-2.com/ -->
    <?= $app['pong']['body']; ?>

    </div>
    </div>
    <!-- /div -->

    <?= (defined('UI_GIT') ? UI_GIT['body'] : null); ?>
    <?= (defined('UI_PHP') ? UI_PHP['body'] : null); /* print(...) */ ?>
    <?= (defined('UI_COMPOSER') ? UI_COMPOSER['body'] : null); /* (isset($appComposer) ? $appComposer['script'] : null); */ ?>
    <?= (defined('UI_NPM') ? UI_NPM['body'] : null); ?>
    <?= (defined('UI_ACE_EDITOR') ? UI_ACE_EDITOR['body'] : null); ?>
    
    <?= $app['project']['body']; ?>

    <?= $app['console']['body']; ?>
    <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
    <!-- https://code.jquery.com/jquery-3.7.1.min.js -->
    <!-- script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script -->
    <?php
      is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/jquery/') or mkdir($path, 0755, true);
      if (is_file($path . 'jquery-3.7.1.min.js')) {
        if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path . 'jquery-3.7.1.min.js'))))) / 86400)) <= 0 ) {
          $url = 'https://code.jquery.com/jquery-3.7.1.min.js';
          $handle = curl_init($url);
          curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
      
          if (!empty($js = curl_exec($handle))) 
            file_put_contents($path . 'jquery-3.7.1.min.js', $js) or $errors['JS-JQUERY'] = $url . ' returned empty.';
        }
      } else {
        $url = 'https://code.jquery.com/jquery-3.7.1.min.js';
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
      
        if (!empty($js = curl_exec($handle))) 
          file_put_contents($path . 'jquery-3.7.1.min.js', $js) or $errors['JS-JQUERY'] = $url . ' returned empty.';
      }
      unset($path);
      ?>
    <div style="position: fixed; top: 0; left: 0; z-index: 1; height: 500px; background-color: #FFF; width: 300px;">
      <span>Loading Time: <?= round(microtime(true) - APP_START, 3); ?>s</span><br />
      <span>Environment: <form style="display: inline;" action="" method="POST">
      <select name="environment" onchange="this.form.submit();">
        <option value="develop" <?= defined('APP_ENV') && APP_ENV == 'development' ? 'selected' : null ?>>Development</option>
        <option value="product" <?= defined('APP_ENV') && APP_ENV == 'production' ? 'selected' : null ?>>Production</option>
        <option value="math" <?= defined('APP_ENV') && APP_ENV == 'math' ? 'selected' : null ?>>Math</option>
      </select></form></span><br />
      <span>Domain: <?= APP_DOMAIN; ?></span><br />
      <span>IP Address: </span><br />
      <span>App Path: <?= APP_PATH; ?></span><br />
    </div>
    <script src="<?= check_http_status('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : APP_BASE['resources'] . 'js/jquery/' . 'jquery-3.7.1.min.js' ?>"></script>
    <!-- You need to include jQueryUI for the extended easing options. -->
    <?php /* https://stackoverflow.com/questions/12592279/typeerror-p-easingthis-easing-is-not-a-function */ ?>
    <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script> <!-- Uncaught ReferenceError: jQuery is not defined -->
    <!-- For Text / Ace Editor -->
    <!-- <script src="https://unpkg.com/@popperjs/core@2" type="text/javascript" charset="utf-8"></script> -->
    <?php
      is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/requirejs/') or mkdir($path, 0755, true);
      if (is_file($path . 'require-2.3.6.js')) {
        if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path . '/require-2.3.6.js'))))) / 86400)) <= 0 ) {
          $url = 'https://requirejs.org/docs/release/2.3.6/minified/require.js';
          $handle = curl_init($url);
          curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

          if (!empty($js = curl_exec($handle)))
            file_put_contents($path . 'require-2.3.6.js', $js) or $errors['JS-REQUIREJS'] = $url . ' returned empty.';
        }
      } else {
        $url = 'https://requirejs.org/docs/release/2.3.6/minified/require.js';
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
      
        if (!empty($js = curl_exec($handle)))
          file_put_contents($path . 'require-2.3.6.js', $js) or $errors['JS-REQUIREJS'] = $url . ' returned empty.';
      }
      unset($path);
      ?>
    <script src="<?= APP_BASE['resources'] . 'js/requirejs/require-2.3.6.js' ?? $url ?>" type="text/javascript" charset="utf-8"></script>
    <script src="resources/js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script> <!-- -->
    <script src="resources/js/ace/src/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>
      <script src="resources/js/ace/src/mode-php.js" type="text/javascript" charset="utf-8"></script>
      
      <script src="resources/js/ace/src/theme-dracula.js" type="text/javascript" charset="utf-8"></script> -->
    <!--   <script src="dist/bundle.js" type="text/javascript" charset="utf-8"></script> -->
    <!-- End: For Text / Ace Editor -->
    <!-- <script src="resources/js/jquery/jquery.min.js"></script> -->
    <?php if (date(/*Y-*/ 'm-d') == /*1928-*/ '08-07' ?? /*2023-*/ '03-30') { ?>
    <script src="/resources/reels/leave-a-light-on.js" type="text/javascript" charset="utf-8"></script>
    <?php } elseif (date(/*Y-*/ 'm-d') == /*1976-*/ '03-20' ?? /*2017-*/ '07-20') { ?>
    <script src="/resources/reels/leave-a-light-on.js" type="text/javascript" charset="utf-8"></script>
    <?php } else {  // array_rand() can't be empty ?>
    <script src="<?= !empty($reels = glob(APP_PATH . 'resources/reels/*.js')) ? APP_BASE['resources'] . 'reels/' . basename(array_rand(array_flip(array_filter($reels, 'is_file')), 1)) : ''; /* APP_BASE['resources'] */?>" type="text/javascript" charset="utf-8"></script>
    <?php } ?>
    <script type="text/javascript" charset="utf-8">
      function makeDraggable(windowId) {
        const windowElement = document.getElementById(windowId);
        const headerElement = windowElement.querySelector('.ui-widget-header');
      
        let isDragging = false;
        let offsetX, offsetY;
      
        headerElement.addEventListener('mousedown', function(event) {
          // Bring the clicked window to the front
          document.body.appendChild(windowElement);
          offsetX = event.clientX - windowElement.getBoundingClientRect().left;
          offsetY = event.clientY - windowElement.getBoundingClientRect().top;
          isDragging = true;
        });
      
        document.addEventListener('mousemove', function(event) {
          if (isDragging) {
            const left = event.clientX - offsetX;
            const top = event.clientY - offsetY;
            //windowElement.style.left = `${left}px`;
            //windowElement.style.top = `${top}px`;
      
            // Boundary restrictions
            const maxX = window.innerWidth - windowElement.clientWidth - 100;
            const maxY = window.innerHeight - windowElement.clientHeight;
      
            windowElement.style.left = `${Math.max(-200, Math.min(left, maxX))}px`;
            windowElement.style.top = `${Math.max(0, Math.min(top, maxY))}px`;
          }
        });
      
        document.addEventListener('mouseup', function() {
          isDragging = false;
        });
      }
      
      makeDraggable('app_ace_editor-container');
      makeDraggable('app_composer-container');
      // makeDraggable('app_project-container');
      makeDraggable('app_git-container');
      makeDraggable('app_npm-container');
      makeDraggable('app_php-container');
      
      
      displayDirectoryBtn.addEventListener('click', () => {
      
      event.preventDefault();
      const appDirectoryContainer = document.getElementById('app_directory-container');
      
      //const styles = window.getComputedStyle(appDirectoryContainer);
      const displayDirectoryBtn = document.getElementById('displayDirectoryBtn');
      
      console.log('state : ' + appDirectoryContainer.style.display );
      if (appDirectoryContainer.style.display == 'none') {
      $( '#app_directory-container' ).slideDown( "slow", function() {
          // Animation complete.
      });
        console.log('hide');
          displayDirectoryBtn.innerHTML = '&#9650;';
      } else {
      
        $( '#app_directory-container' ).slideUp( "slow", function() {
          // Animation complete.
        });
      
          displayDirectoryBtn.innerHTML = '&#9660;';  
        console.log('show');
      }
      //show_console();
      
      });
      
      
      
      
      function toggleSwitch(element) {
      
      if (element.checked) {
        // Third option is selected
        // Add your logic here
        console.log('checked');
      
        $( '#app_directory-container' ).slideDown( "slow", function() {
          // Animation complete.
        });
        
      $("#debug-content").show("slide", { direction: "left" }, 1000);
      
      $("#app_backup-container").show("slide", { direction: "right" }, 1000);
      
      } else {
        // Third option is not selected
        // Add your logic here
        console.log('(not) checked');
      
        $( '#app_directory-container' ).slideUp( "slow", function() {
          // Animation complete.
        });
      
      $("#debug-content").hide("slide", { direction: "left" }, 1000);
      
      $("#app_backup-container").hide("slide", { direction: "right" }, 1000);
      }
      }
      
      $(document).ready(function(){
        if ($( "#app_directory-container" ).css('display') == 'none') {
      <?php if (isset($_GET['debug'])) { ?>
          $( '#app_directory-container' ).slideDown( "slow", function() {
          // Animation complete.
          });
      <?php } ?>
        }
      });
      
      <?= $app['console']['script']; ?>
      
      // Define the function to be executed when "c" key is pressed

      
      document.addEventListener('keydown', function() {
      // Check if the pressed key is "c" (you can use event.key or event.keyCode)
        if (event.key === '`' || event.keyCode === 192) // c||67
            if (document.activeElement !== requestInput) {
                // Replace the following line with your desired function
                // If it's currently absolute, change to fixed
                if (!isFixed)
                    requestInput.focus();
                event.preventDefault();
                show_console();
            } else {
                document.activeElement = null;
                return false;
            }
        else if (event.key === 'c' || event.keyCode === 67) {
          // Execute your desired function or code here
          console.log('The "c" key was pressed!');
          // Replace the above line with the actual code you want to execute
        }
        console.log('keyboard shortcut');
      });
      
      // Attach the event listener to the window object
      window.addEventListener('keydown', function() {
            // Check the condition before calling the show_console function
            //if (myDiv.style.position !== 'fixed')

        if (event.key === '`' || event.keyCode === 192) // c||67
            if (document.activeElement !== requestInput) {
                // Replace the following line with your desired function
                // If it's currently absolute, change to fixed
                if (!isFixed)
                    requestInput.focus();
                event.preventDefault();
                if (isFixed) isFixed = !isFixed;
                isFixed = false;
                show_console();
                return false;
            } else {
                document.activeElement = null;
                return false;
            }


            console.log('windowEvent');
            
            var textField = document.getElementById('requestInput');

            // Check if the text field is focused
            var isFocused = textField === document.activeElement;
            
            if (  document.getElementById('app_console-container').style.position != 'absolute') {

              if (!isFixed) {
                //requestInput.focus();
              } else {
                //show_console();
              }
            } else {


              if (isFixed) isFixed = !isFixed;
              isFixed = true;
            
              if (isFocused)  show_console();
            }
        });

      <?= (defined('UI_GIT') ? UI_GIT['script'] : null); ?>
      <?= (defined('UI_PHP') ? UI_PHP['script'] : null); /* print(...) */ ?>
      <?= (defined('UI_COMPOSER') ? UI_COMPOSER['script'] : null); /* (isset($appComposer) ? $appComposer['script'] : null); */ ?>
      <?= (defined('UI_NPM') ? UI_NPM['script'] : null); ?>
      <?= /* Defined later! (defined('UI_ACE_EDITOR') ? UI_ACE_EDITOR['script'] : null);*/ NULL; ?>

      <?= $app['browser']['script']; ?>
      
      <?= $app['github']['script']; ?>
      
      <?= $app['packagist']['script']; ?>
      
      <?= /*$appWhiteboard['script'];*/ NULL; ?>
      
      <?= /*$appNotes['script'];*/ NULL; ?>
      
      
      <?= $app['backup']['script']; ?>
      
      
      <?= $app['pong']['script']; ?>
      
      /*
      require.config({
      baseUrl: window.location.protocol + "//" + window.location.host
      + window.location.pathname.split("/").slice(0, -1).join("/"),
      
      paths: {
        jquery: 'resources/js/jquery/jquery.min',
        domReady: 'resources/js/domReady',
        bootstrap: 'resources/js/bootstrap/dist/js/bootstrap',
        ace: 'resources/js/ace/src/ace',
        'lib/dom': 'resources/js/ace/src/lib/dom',
        useragent: 'resources/js/ace/src/lib/useragent',
        exports: 'resources/js/ace/src/lib/',
        
        //'../snippets': 'resources/js/ace/lib/ace/snippets',
        //'./lib/oop': 'resources/js/ace/src/lib'
      }
      });
      */
      
      var globalEditor; // Define a global variable
      
        require.config({
          baseUrl: window.location.protocol + "//" + window.location.host
          + window.location.pathname.split("/").slice(0, -1).join("/"),
          paths: {
            ace: "resources/js/ace/src"
          }
        });
        
        define('testace', ['ace/ace'], function(ace) {
                //console.log(langtools);
      
      <?= UI_ACE_EDITOR['script']; ?>
                //require(["resources/js/requirejs/require-2.3.6!ace/ace"], function(e){
                    //editor.setValue(e);
                //})
                
            globalEditor = editor1;
            return editor;
        });
        
        require(['testace'], function (editor) {
            console.log(editor);
        });
      
      
      /*
        require.config({paths: {ace: "../src"}})
        define('testace', ['ace/ace'],
            function(ace, langtools) {
                console.log("This is the testace module");
                var editor = ace.edit("editor");
                editor.setTheme("ace/theme/twilight");
                editor.session.setMode("ace/mode/javascript");
                require(["ace/requirejs/text!src/ace"], function(e){
                    editor.setValue(e);
                })
            }
        );
        require(['testace'])
      
      require(['jquery', 'domReady', 'bootstrap', 'ace', 'lib/dom', 'useragent'], function($, domReady) {
      console.log('domReady is working ... ');
      // Code that uses 'lib/dom'
      });
      */
      // ,'lib/dom', '../snippets', './lib/oop'
      /*
      require(['jquery','domReady','bootstrap','ace/ace'], function($, domReady) {
        domReady(function () {
      
      console.log(require.config);
      console.log('domReady is working ... ');
      
        })
      });
      */
      
      <?= $app['timesheet']['script']; ?>
      
      <?= $app['project']['script']; ?>
      
    </script>
  </body>
</html>

<?= NULL; /** Loading Time: 15.0s **/
//  dd(get_required_files(), true); 
?>