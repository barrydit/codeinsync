<?php

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null)))
    require_once($path); 
else die(var_dump($path . ' was not found. file=config.php'));


if ($path = (basename(getcwd()) == 'public')
    ? (is_file('app.timesheet.php') ? 'app.timesheet.php' : (is_file('../app.timesheet.php') ? '../app.timesheet.php' : (is_file('../config/app.timesheet.php') ? '../config/app.timesheet.php' : 'public/app.timesheet.php')))
    : (is_file('../app.timesheet.php') ? '../app.timesheet.php' : (is_file('public/app.timesheet.php') ? 'public/app.timesheet.php' : (is_file('config/app.timesheet.php') ? 'config/app.timesheet.php' : 'app.timesheet.php'))))
  require_once($path); 
else die(var_dump($path . ' was not found. file=app.timesheet.php'));

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


if (in_array(APP_PATH . 'composer.php', get_required_files())) {
  if (class_exists('LogLevel'))
    if (null !== LogLevel::DEBUG) // isset() || 
      if (defined('LogLevel'))
        $errors['LogLevel'] = 'Now let\'s use LogLevel... ' . LogLevel::DEBUG . "\n";

  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('composer_ui.php') ? 'composer_ui.php' : (is_file('../composer_ui.php') ? '../composer_ui.php' : (is_file('../config/composer_ui.php') ? '../config/composer_ui.php' : NULL)))
    : (is_file('../composer_ui.php') ? '../composer_ui.php' : (is_file('public/composer_ui.php') ? 'public/composer_ui.php' : (is_file('config/composer_ui.php') ? 'config/composer_ui.php' : 'composer_ui.php'))))
  require_once($path); 
  else die(var_dump($path . ' was not found. file=composer_ui.php'));
}

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('git_ui.php') ? 'git_ui.php' : (is_file('../git_ui.php') ? '../git_ui.php' : (is_file('../config/git_ui.php') ? '../config/git_ui.php' : NULL)))
    : (is_file('../git_ui.php') ? '../git_ui.php' : (is_file('public/git_ui.php') ? 'public/git_ui.php' : (is_file('config/git_ui.php') ? 'config/git_ui.php' : 'git_ui.php'))))
  require_once($path); 
else die(var_dump($path . ' was not found. file=git_ui.php'));

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../npm.php') ? '../npm.php' : (is_file('../config/npm.php') ? '../config/npm.php' : null))
    : (is_file('npm.php') ? 'npm.php' : (is_file('config/npm.php') ? 'config/npm.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=npm.php'));

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('app.php.php') ? 'app.php.php' : (is_file('../app.php.php') ? '../app.php.php' : (is_file('../config/app.php.php') ? '../config/app.php.php' : NULL)))
    : (is_file('../app.php.php') ? '../app.php.php' : (is_file('public/app.php.php') ? 'public/app.php.php' : (is_file('config/app.php.php') ? 'config/app.php.php' : 'app.php.php'))))
  require_once($path); 
else die(var_dump($path . ' was not found. file=app.php.php'));

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


if ($path = (basename(getcwd()) == 'public')
    ? (is_file('app.text_edit.php') ? 'app.text_edit.php' : (is_file('../app.text_edit.php') ? '../app.text_edit.php' : (is_file('../config/app.text_edit.php') ? '../config/app.text_edit.php' : NULL)))
    : (is_file('../app.text_edit.php') ? '../app.text_edit.php' : (is_file('public/app.text_edit.php') ? 'public/app.text_edit.php' : (is_file('config/app.text_edit.php') ? 'config/app.text_edit.php' : 'app.text_edit.php'))))
  require_once($path); 
else die(var_dump($path . ' was not found. file=app.text_edit.php'));

if ($path = (basename(getcwd()) == 'public') // composer_app.php (depend.)
    ? (is_file('app.console.php') ? 'app.console.php' : (is_file('../app.console.php') ? '../app.console.php' : (is_file('../config/app.console.php') ? '../config/app.console.php' : 'public/app.console.php')))
    : (is_file('../app.console.php') ? '../app.console.php' : (is_file('public/app.console.php') ? 'public/app.console.php' : (is_file('config/app.console.php') ? 'config/app.console.php' : 'app.console.php'))))
  require_once($path); 
else die(var_dump($path . ' was not found. file=app.console.php'));


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //dd($_POST['package']);
    
    list($vendor, $package) = explode('/', $_POST['package']);

    //if ($vendor == 'nesbot' && $package == 'carbon') {

    if (preg_match(DOMAIN_EXPR, packagist_return_source($vendor, $package), $matches))
$raw_url = $initial_url = $matches[0];
    else $raw_url = '';
    
    if (!is_file(APP_BASE['var'].'package-' . $vendor . '-' . $package . '.php')) {

    $source_blob = (check_http_200($raw_url) ? file_get_contents($raw_url) : '' );
    
    //dd('url: ' . $raw_url);
    $raw_url = addslashes($raw_url);

    $source_blob = addslashes(COMPOSER_JSON['json']); // $source_blob
    file_put_contents(APP_BASE['var'].'package-' . $vendor . '-' . $package . '.php', '<?php' . "\n" . ( check_http_200($raw_url) ? '$source = "' . $raw_url . '";' : '' ) . "\n" . 
<<<END
\$composer_json = "{$source_blob}";
return '<form action method="POST">'
. '...'
. '</form>';
END
);
    }

    exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query(APP_QUERY, '', '&amp;')));
    //}

}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$project_code = file_get_contents('project.php');

if (is_file(APP_ROOT . 'project.php') && isset($_GET['project']) && $_GET['project'] == 'show') {
  $project_code = file_get_contents('project.php');
  Shutdown::setEnabled(false)->setShutdownMessage(function() use($project_code) {
      return eval('?>' . $project_code); // -wow
    })->shutdown();
  //die();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <base href="<?=(!is_array(APP_URL) ? APP_URL : APP_URL_BASE)?>" />

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

  <script src="<?= (check_http_200('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_WWW . 'resources/js/tailwindcss-3.3.5.js')?>"></script>

<style type="text/tailwindcss">


.row-container { display: flex; width: 100%; height: 100%; flex-direction: column; overflow: hidden; }

<?= $appComposer['style']; ?>

<?= $appGit['style']; ?>

<?= $appPHP['style']; ?>

<?= $appGithub['style']; ?>

<?= $appPackagist['style']; ?>

<?= $appWhiteboard['style']; ?>

<?= $appNotes['style']; ?> 

<?= $appPong['style']; ?>

<?= $appTextEditor['style']; ?>

<?= $appConsole['style']; ?>

<?= $appTimesheet['style']; ?>



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
    text-align: center;
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

</style>

</head>
<body>

 <div class="row-container" style="position: absolute; left: 0; top: 0;">
    <iframe id="iWindow" src="<?php
if (!empty($_GET['client'])) {
  $path = '/clientele/' . $_GET['client'] . '/';
  $dirs = array_filter(glob(dirname(__DIR__) . '/../' . $path . '*'), 'is_dir');
  
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

  if (!empty($_GET['domain'])) {
    foreach($dirs as $key => $dir) {
      if (basename($dir) == $_GET['domain']) {
        if (is_dir($dirs[$key].'/public/'))
          $path .= basename($dirs[$key]).'/public/';
        else 
          $path .= basename($dirs[$key]);
        break;
      }
    }
    //else 
    //exit(header('Location: http://localhost/clientele/' . $_GET['client']));    
  }
  $path = '?path=' . $path;
} elseif (!empty($_GET['project'])) {
  $path = '/projects/' . $_GET['project'] . '/';   
  //$dirs = array_filter(glob(dirname(__DIR__) . '/projects/' . $_GET['project'] . '/*'), 'is_dir');
  
} else { $path = 'developer.php'; } echo $path;
?>" style="height: 100%;"></iframe>
  </div>
  
  <div style="position: relative; margin: 0px auto; width: 800px;">
<div style="position: absolute; left: -130px; ">

<!--form action="#!" method="GET">
<?= (isset($_GET['debug']) && !$_GET['debug'] ? '' : '<input type="hidden" name="debug" value / >') ?> 
  <input class="input" id="toggle-debug" type="checkbox" onchange="this.form.submit();" <?= (isset($_GET['debug']) ? 'checked' : '') ?> / -->

  <input class="input" id="toggle-debug" type="checkbox" onchange="toggleSwitch(this);" <?= (isset($_GET['debug']) ? 'checked' : '') ?> />

  <label class="label" for="toggle-debug" style="margin-left: -6px;">
    <div class="switch">
      <span class="slider round"></span>
    </div>

    <div class="right">
      Debug
    </div>
  </label>
<!-- /form -->

      </div>
  <div id="debug-content" class="absolute" style="position: absolute; margin-left: auto; margin-right: auto; left: 0; right: 0; text-align: center; background-color: rgba(255, 255, 255, 0.8); border: 1px solid #000; width: 800px; z-index: 1;">
 


      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('app_github-container').style.display='block';"><img src="resources/images/github_icon.png" width="72" height="23"></a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('app_git-container').style.display='block';"><img src="resources/images/git_icon.png" width="58" height="24"></a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('app_composer-container').style.display='block';"><img src="resources/images/composer_icon.png" width="31" height="40"> Composer</a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('app_php-container').style.display='block';"><img src="resources/images/php_icon.png" width="40" height="27"> PHP <?= (preg_match("#^(\d+\.\d+)#", PHP_VERSION, $match) ? $match[1] : '8.0' ) ?></a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('app_packagist-container').style.display='block';"><img src="resources/images/packagist_icon.png" width="30" height="34"> Packagist</a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '')?>" onclick="document.getElementById('app_text_editor-container').style.display='block';"><img src="resources/images/code_icon.png" width="32" height="32"> Text</a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('httpHeaderForm').style.display='block';"><img src="resources/images/http_icon.jpg" width="32" height="32"> HTTP</a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('app_timesheet-container').style.display='block';"><img src="resources/images/clock.gif" width="32" height="32"> Clock-In</a>
      
      
        <div style="position: absolute; top: 20px; right: -10px; ">
    <table class="text-sm" style="width: 300px;">
      <tr><td><div id="idleTime" style="display: inline; margin: 7px 5px;"></div>
      <div><div id="stats">Idle: [0]&nbsp;&nbsp;<span style="color: black;">00:00:00</span></div>  
    </div></td><td><img id="ts-status-light" src="resources/images/timesheet-light-Clear-2.gif" width="80" height="30" style="cursor: pointer;" /></td></tr>
    </table>
    </div>

  </div>

<div id="app_project-container" style="display: none; position: absolute; top: 80px; padding: 20px; margin-left: auto; margin-right: auto; left: 0; right: 0; width: 700px; z-index: 2;">
  <div style="margin: -25px 0 20px 0;">
    <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_project-container').style.display='none';">X</a>]</div>
  </div>
  <form style="background-color: #ddd; padding: 20px;">
    <h3>Psr/Log</h3>
    <label><input type="checkbox" checked> Add to Project.</label>
    <button type="submit" style="float: right;">Save</button>
    <iframe src="<?= APP_WWW ?>?project=show" style="height: 300px; width: 600px;"></iframe>
  </form>
</div>

<div style="position: absolute; top: 40px; margin-left: auto; margin-right: auto; left: 0; right: 0; width: 700px; z-index: 1;">

<form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL_BASE . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ array( 'app' => 'text_editor')*/) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
        
<?php $path = realpath(getcwd() . (isset($_GET['path']) ? DIRECTORY_SEPARATOR . $_GET['path'] : '')) . DIRECTORY_SEPARATOR;

if (isset($_GET['path'])) { ?>
        <!-- <input type="hidden" name="path" value="<?= $_GET['path']; ?>" /> -->
<?php } ?>

<?php echo '<a href="' . (APP_URL['query'] != '' ? '?' . APP_URL['query'] : '') . (isset($_GET['path']) && $_GET['path'] != '' ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : (APP_ENV == 'development' ? '#!' : '') ) . '"><span title="' . $path . '" style="cursor: pointer;" onclick="document.getElementById(\'app_directory-container\').style.display=\'block\';">' . (isset($_GET['project']) ? '/project/' : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) . (isset($_GET['path']) && $_GET['path'] === '' ? '' : (!isset($_GET['path']) ? '' : basename($_GET['path']) . '/')) . '</span></a>'; /* $path; */ ?>

<select name="path" onchange="this.form.submit();">
  <option value="">.</option>
  <option value="">..</option>
<?php

// Bug if the dir does not exist it defaults to the root ...

if ($path)
  foreach (array_filter( glob( $path . DIRECTORY_SEPARATOR . '*'), 'is_dir') as $dir) {
    echo '<option value="' . (isset($_GET['path']) ?  $_GET['path'] . DIRECTORY_SEPARATOR : '') . basename($dir) . '"' . (isset($_GET['path']) && $_GET['path'] == basename($dir) ? ' selected' : '' )  . '>' . basename($dir) . '/</option>' . "\n";
  }
?>
        </select>
</form>
</div>
<div style="position: relative; width: 800px; margin: 0 auto; height: 500px; background-color: rgba(255, 255, 255, 0.9); overflow-x: scroll;">

<div style="position: absolute; margin: 100px 75px; text-align: center;" class="text-sm"><a href="#!" onclick="show_console(); return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/cli.png' ?>" /></a><br /><a href="?app=text_editor&path=&file=app.console.php" style="text-align: center;">(CLI)</a></div>

<!-- 
<a href="javascript:window.open('print.html', 'newwindow', 'width=300,height=250')">Print</a>
onclick="window.open('app.whiteboard.php', 'newwindow', 'width=300,height=250'); return false;"

https://stackoverflow.com/questions/12939928/make-a-link-open-a-new-window-not-tab
 -->

<div style="position: absolute; margin: 100px 165px; text-align: center;" class="text-sm"><a href="app.whiteboard.php" target="_blank" onclick="document.getElementById('app_whiteboard-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/whiteboard.png' ?>" /></a><br /><a href="?app=text_editor&path=&file=app.whiteboard.php" style="text-align: center;">Whiteboard</a></div>

<div style="position: absolute; margin: 100px 260px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_notes-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/notes.png' ?>" /></a><br /><a href="?app=text_editor&path=&file=app.notes.php" style="text-align: center;">Notes</a></div>


<div style="position: absolute; margin: 100px 350px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_project-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/project.png' ?>" /></a><br /><a href="?app=text_editor&path=&file=app.project.php"><span style="text-align: center;">Project</a></span></div>


<div style="position: absolute; margin: 100px 0 0 450px ; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_debug-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/debug.png' ?>" /><br /><span style="text-align: center;">Debug</span></a></div>


<div style="position: absolute; margin: 100px 0 0 540px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_profile-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/user.png' ?>" /><br /><span style="text-align: center;">Profile</span></a></div>


<div style="position: absolute; margin: 100px 0 0 630px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/browser.png' ?>" /><br /><span style="text-align: center;">Browser</span></a></div>


<div style="position: absolute; margin: 200px 75px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_apps-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/apps.png' ?>" /><br /><span style="text-align: center;">Apps.</span></a></div>

<div style="position: absolute; margin: 200px 170px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_calendar-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/calendar.png' ?>" /><br /><span style="text-align: center;">Calendar</span></a></div>

<div style="position: absolute; margin: 190px 240px; padding: 20px 40px; background-color: rgba(255, 255, 255, 0.8);">
<form action="#!" method="GET">
<?= (isset($_GET['project']) && !$_GET['project'] ? '<input type="hidden" name="client" value="" />' : '<input type="hidden" name="project" value="" />') ?>

  <div style="margin: 0 auto; ">
    <div id="clockTime"></div>
  </div>  
  <input class="input" id="toggle-project" type="checkbox" onchange="toggleSwitch(this); this.form.submit();" <?= (isset($_GET['project']) ? 'checked' : '') ?> />

  <label class="label" for="toggle-project" style="margin-left: -6px;">
    <div class="left">
      Client
    </div>

    <div class="switch">
      <span class="slider round"></span>
    </div>

    <div class="right">
      Project
    </div>
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


<div style="position: absolute; top: 400px; left: 65px; width: 80%; margin: 0 auto; height: 25px; border-bottom: 1px solid black; text-align: center; z-index: 0;">
  <span style="font-size: 20px; background-color: #F3F5F6; padding: 0 20px; z-index: 1;">
    USER APPS.
  </span>
</div>


<div style="position: absolute; margin: 430px 75px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_install-container').style.display='block'; return false;"><span style="text-align: center;">New App.</span><br /><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/install.png' ?>" /></a></div>


<div style="position: absolute; margin: 430px 160px; text-align: center;" class="text-sm"><a href="?app=text_editor&path=&file=app.user-app.php"><span style="text-align: center;">App #1</span></a><br /><a href="#!" onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/php-app.png' ?>" /></a></div>



<div id="app_directory-container" style="position: absolute; display: <?= ( isset($_GET['path']) ? 'block' : 'none') . ';'; ?>; background-color: white; overflow-x: scroll; height: 580px; position: absolute; top: 80px; margin-left: auto; margin-right: auto; left: 0; right: 0; width: 700px;">

<?php if (isset($_GET['path']) && preg_match('/^vendor/', $_GET['path'])) { ?>

<!-- iframe src="composer_pkg.php" style="height: 500px; width: 700px;"></iframe -->

<div style="width: 700px;">
<div style="display: inline-block; width: 350px;">Composers Vendor Packages [Installed] List</div>
<div style="display: inline-block; text-align: right; width: 300px; ">
<form action="<?= (!defined('APP_URL_BASE') ? '//' . APP_DOMAIN . APP_URL_PATH . '?' . http_build_query(APP_QUERY, '', '&amp;') : APP_URL_BASE . '?' . http_build_query(APP_QUERY, '', '&amp;')) ?>" method="POST">
<input id="RequirePkg" type="text" title="Enter Text and onSelect" list="RequirePkgs" placeholder="[vendor]/[package]" name="package" value onselect="get_package(this);" autocomplete="off" style=" margin-top: 4px;">
<button type="submit" style="border: 1px solid #000; margin-top: 4px;"> Add </button>
<div style="display: inline-block; float: right; text-align: left; margin-left: 10px;" class="text-xs"><input type="checkbox" name="install" value="" /> Install<br /><input type="checkbox" name="update" value="" /> Update</div>
<datalist id="RequirePkgs">
  <option value=""></option>
</datalist>
</form>
</div>
</div>

<table width="" style="border: none;">
<tr style=" border: none;">
<?php
//$paths = glob($path . '/*');
$paths = COMPOSER_VENDORS;

//dd(urldecode($_GET['path']));
/*
$paths = ['0' => ...];
usort($paths, function ($a, $b) {
    $aIsDir = is_dir('vendor/'.$a);
    $bIsDir = is_dir('vendor/'.$b);
    
    // Directories go first, then files
    if ($aIsDir && !$bIsDir) {
        return -1;
    } elseif (!$aIsDir && $bIsDir) {
        return 1;
    }
    
    // If both are directories or both are files, sort alphabetically
    return strcasecmp($a, $b);
});
*/

$handle = fopen(APP_ROOT . 'project.php', 'r');
$pkgs_matched = [];

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        if (preg_match('/^use\s+(.+?);/', $line, $matches)) {
            $pkgs_matched[] = addslashes($matches[1]);
        }
    }
    fclose($handle);
} else {
    echo "Error opening the file.";
}


$dirs = [];

foreach (array_filter( glob( APP_PATH . APP_BASE['var'] . 'package-*.php'), 'is_file') as $key => $dir) {
  if (preg_match('/^package-(.*)-(.*).php$/', basename($dir), $matches)) {
      $name = $matches[1];
      if (!isset($uniqueNames[$name])) {
          $uniqueNames[$name] = true;
          $dirs[] = $name;
      }
  }
}

$count = 1;
if (!empty($paths))
  foreach($paths as $vendor => $packages) {

    echo '<td style="text-align: center; border: none;" class="text-xs">' . "\n"
    . '<div class="container2">' . "\n";

    $show_notice = true;

    //var_dump(preg_grep('/^Psr\\\\Log/', ['Psr\\Log\\LogLevel']));
    
    //var_dump($dirs);

    foreach ($packages as $package) {
        //var_dump('/^' . ucfirst($vendor) . '\\\\' . ucFirst($package) . '/'); // $pkgs_matched[0]
    //var_dump(preg_grep($grep = '/^'. ucfirst($vendor) . '\\\\\\\\' . ucFirst($package) . '/', $pkgs_matched));
            //if (!in_array(APP_PATH.'vendor/'.$vendor.'/'.$package.'/Psr/Log/LogLevel.php', get_required_files())) { break; }
        //if (isset($pkgs_matched) && !empty($pkgs_matched) && class_exists($pkgs_matched[0])) {
        
        //$grep = '/^' . ucfirst($vendor) . '\\\\' . ucFirst($package) . '/';
        //dd(get_declared_classes());
        //$arr = preg_grep($grep, get_declared_classes());
        //$show_notice = (!empty($arr) ? true : false);
        //if (!empty($arr)) {}
        
          
       // $arr = ;
        //$show_notice = (!empty($arr) ? true : false);
        //if (!empty($arr)) { }

        if ($show_notice)
          $show_notice = (isset($pkgs_matched) && !empty($pkgs_matched) && !empty(preg_grep($grep = '/^'. ucfirst($vendor) . '\\\\\\\\' . ucFirst($package) . '/', $pkgs_matched)) ? false : (in_array($vendor, $dirs) ? true : false)); // $arr[0] class_exists() $pkgs_matched[0]
          
          // (!in_array($vendor, $dirs) ? true : false) 
          
          
        //var_dump($show_notice);
        //var_dump($grep);
        //var_dump(!empty(preg_grep($grep, $pkgs_matched)));
        //}
    }
    if ($show_notice)
        echo '<div style="position: absolute; left: -12px; top: -12px; color: red; font-weight: bold;">[1]</div>';

      if (is_dir('vendor/'.$vendor) || !is_dir('vendor/'.$vendor))
        //if ($vendor == 'barrydit') continue;
        if ($vendor == 'symfony') {
          echo '<a class="pkg_dir" href="?path=vendor/' . $vendor . '">'
          . '<img src="resources/images/directory-symfony.png" width="50" height="32" style="' . (isset(COMPOSER->{'require'}->{$vendor . '/' . $package}) || isset(COMPOSER->{'require-dev'}->{$vendor . '/' . $package})?: 'opacity:0.4;filter:alpha(opacity=40);') . '" /></a><br />'
          . '<div class="overlay">';
          foreach ($packages as $package) {
            if (in_array(APP_PATH.'vendor/'.$vendor.'/'.$package.'/bootstrap.php', get_required_files()))
              echo '<a href="?app=text_editor&path=vendor/'.$vendor.'/'.$package.'/&file=bootstrap.php"><code style="background-color: white; color: #0078D7; font-size: 9px;">' . $package. '</code></a><br />';
            elseif (in_array(APP_PATH.'vendor/'.$vendor.'/'.$package.'/function.php', get_required_files()))
              echo '<a href="?app=text_editor&path=vendor/'.$vendor.'/'.$package.'/&file=function.php"><code style="background-color: white; color: #0078D7; font-size: 9px;">' . $package. '</code></a><br />';
            else 
              echo '<p style="background-color: #0078D7;">' . $package . '</p>' . PHP_EOL;
            //echo APP_PATH.'vendor/'.$vendor.'/'.$package;

            // /mnt/c/www/public/composer/vendor/symfony/deprecation-contracts
          }
          echo '</div>' . '<a href="?path=vendor/' . $vendor . '">' . ucfirst($vendor) . '</a>';
    
        } elseif ($vendor == 'composer') {
          foreach ($packages as $package) {
            if (is_file('var/package-' . $vendor . '-' . $package . '.php'))
              $app['composer'][$vendor][$package]['body'] = file_get_contents('var/package-' . $vendor . '-' . $package . '.php');
            //if (!in_array(APP_PATH.'vendor/'.$vendor.'/'.$package.'/Psr/Log/LogLevel.php', get_required_files())) {
              //echo '<div style="position: absolute; left: -12px; top: -12px; color: red; font-weight: bold;">[1]</div>';
            //  break;
            //}
          }
          echo '<a class="pkg_dir" href="#" onclick="document.getElementById(\'app_project-container\').style.display=\'block\';">' // ?app=text_editor&path=vendor/' . $vendor . '
          . '<img src="resources/images/directory-composer.png" width="50" height="32" style="' . (isset(COMPOSER->{'require'}->{$vendor . '/' . 'composer'}) || isset(COMPOSER->{'require-dev'}->{$vendor . '/' . $package})? '' : 'opacity:0.4;filter:alpha(opacity=40);') . '" /></a><br />'
          . '<div class="pkg_dir overlay">';
          foreach ($packages as $package) {
            if (!in_array(APP_PATH.'vendor/'.$vendor.'/'.$package.'/Psr/Log/LogLevel.php', get_required_files()) && $package == 'log') {
              echo '<a href="?app=text_editor&path=vendor/'.$vendor.'/'.$package.'/Psr/Log/&file=LogLevel.php"><code style="background-color: white; color: #0078D7; font-size: 10px;">' . $package. '</code></a>';
              continue;
            }
            echo '<p style="background-color: #0078D7;">' . $package. '</p>' . PHP_EOL;
          }
          echo '</div>' . '<a href="?path=vendor/' . $vendor . '">' . ucfirst($vendor) . '</a>' . "\n";    
        } elseif ($vendor == 'psr') {
          echo '<a class="pkg_dir" href="#" onclick="document.getElementById(\'app_project-container\').style.display=\'block\';">' // ?app=text_editor&path=vendor/' . $vendor . '
          . '<img src="resources/images/directory-psr.png" width="50" height="32" style="' . (isset(COMPOSER->{'require'}->{$vendor . '/' . $package}) || isset(COMPOSER->{'require-dev'}->{$vendor . '/' . $package}) ? '' : (!$show_notice ? '' : 'opacity:0.4;filter:alpha(opacity=40);')) . '" />' . '</a><br />'
          . '<div class="overlay">';
          foreach ($packages as $package) {
            if (!in_array(APP_PATH.'vendor/'.$vendor.'/'.$package.'/Psr/Log/LogLevel.php', get_required_files()) && $package == 'log') {
              echo '<a href="?app=text_editor&path=vendor/'.$vendor.'/'.$package.'/Psr/Log/&file=LogLevel.php"><code style="background-color: white; color: #0078D7; font-size: 10px;">' . $package. '</code></a>';
              continue;
            }
            
            echo '<p style="background-color: #0078D7;">' . $package. '</p>' . PHP_EOL;
          }
          echo '</div>' . '<a href="?path=vendor/' . $vendor . '">' . ucfirst($vendor) . '</a>' . "\n";    
        } else {

          echo '<a class="pkg_dir" href="?path=vendor/' . $vendor . '">'
          . '<img src="resources/images/directory.png" width="50" height="32" style="' . (isset(COMPOSER->{'require'}->{$vendor . '/' . $package}) || isset(COMPOSER->{'require-dev'}->{$vendor . '/' . $package})?: 'opacity:0.4;filter:alpha(opacity=40);') . '" />' . '</a><br />'
          . '<div class="overlay">';
          foreach ($packages as $package) {
            echo '<code style="background-color: white; color: #0078D7;">' . $package. '</code><br />' . PHP_EOL;
          }
          echo '</div>' . '<a href="/?path=vendor/' . $vendor . '">' . ucfirst($vendor) . '</a>' . "\n";
        }
      echo  '</div>' . "\n"
      . '</td>' . "\n";
      if ($count > 7 || $vendor == end($paths)) echo '</tr>';
      if (isset($count) && $count > 7) $count = 1;
      else $count++;
  }

foreach (COMPOSER_VENDORS as $vendor => $packages) {
    $dirs_diff[] = $vendor;
}

$result = array_diff($dirs, $dirs_diff);

//dd($result);
if (!empty($result))
  foreach ($result as $install) {
    echo '<td style="border: none; text-align: center;" class="text-xs">' . "\n"
    . '<a href="#" onclick="document.getElementById(\'app_git-container\').style.display=\'block\';">' // "?path=' . basename($path) . '" 
    . '<img src="resources/images/directory-install.png" width="50" height="32" ' . /*style="opacity:0.4;filter:alpha(opacity=40);"*/' /><br />' . $install . '/</a>' . "\n";
    echo '</td>' . "\n";
    if ($count > 7 || $install == end($result)) echo '</tr>';
    if (isset($count) && $count > 7) $count = 1;
    else $count++;
  }

?>
</tr>
</table>

<?php } elseif(isset($_GET['project'])) { ?> 

<?php if (readlinkToEnd('/var/www/projects') == '/mnt/c/www/projects') {  ?>
<div style="text-align: center; border: none;" class="text-xs">
<a class="pkg_dir" href="?project=#!" onclick="document.getElementById('app_project-container').style.display='block';">
<img src="resources/images/project-icon.png" width="50" height="32" style="" /></a><br /><a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . '#!' ?>" onclick="document.getElementById('app_text_editor-container').style.display='block';">project.php</a>
</div>

<table width="" style="border: none;">
<tr style=" border: none;">
<?php
$links = array_filter(glob(APP_PATH . '../../projects/*'), 'is_dir'); 

$count = 1;
?>

      <?php
      if (empty($links)) {
        echo '<option value="" selected>---</option>' . "\n"; // label="     "
      } else  //dd($links);
      $old_links = $links;
      while ($link = array_shift($links)) {
        $old_link = $link;
        $link = basename($link);


    echo '<td style="text-align: center; border: none;" class="text-xs">' . "\n";

          echo '<a class="pkg_dir" href="?project=' . $link . '">'
          . '<img src="resources/images/directory.png" width="50" height="32" style="" /><br />' . $link . '</a><br />'

      . '</td>' . "\n";
      if ($count > 7 || $old_link == end($old_links)) echo '</tr>';
      if (isset($count) && $count > 7) $count = 1;
      else $count++;

        }

      }
      
      
?>
</table>
<!--
      <li>
<?php if (readlinkToEnd('/var/www/projects') == '/mnt/c/www/projects') {  ?>
<a href="projects/">project/</a>
        <ul style="padding-left: 10px;">
          <form action method="GET">
            <select id="sproject" name="project" style="color: #000;">
<?php
      while ($link = array_shift($links)) {

        $link = basename($link); // Get the directory name from the full path
        if (is_dir(APP_PATH . '../../projects/' . $link)) {
          echo '  <option value="' . $link . '" ' . (current($_GET) == $link ? 'selected' : '') . '>' . $link . '</option>' . "\n";
        }

      }
      ?>
            </select>
          </form>
	    </ul>
<?php } ?></li>
-->

<?php } elseif(isset($_GET['client'])) { ?> 

<?php if (readlinkToEnd('/var/www/clientele') == '/mnt/c/www/clientele') {  ?>

<table width="" style="border: none;">
<tr style=" border: none;">
<?php
$links = array_filter(glob(APP_PATH . '../../clientele/*'), 'is_dir'); 

$count = 1;
?>

      <?php
      if (empty($links)) {
        echo '<option value="" selected>---</option>' . "\n"; // label="     "
      } else  //dd($links);
      $old_links = $links;
      while ($link = array_shift($links)) {
        $old_link = $link;
        $link = basename($link);


    echo '<td style="text-align: center; border: none;" class="text-xs">' . "\n";

          echo '<a class="pkg_dir" href="?project=' . $link . '">'
          . '<img src="resources/images/directory.png" width="50" height="32" style="" /><br />' . $link . '</a><br />'

      . '</td>' . "\n";
      if ($count > 7 || $old_link == end($old_links)) echo '</tr>';
      if (isset($count) && $count > 7) $count = 1;
      else $count++;

        }

      }
      
      
?>
</table>
<!--
      <li>
<?php if (readlinkToEnd('/var/www/clientele') == '/mnt/c/www/clientele') {  ?>
<a href="clientele/">client/</a>
        <ul style="padding-left: 10px;">
          <form action method="GET">
            <select id="sproject" name="project" style="color: #000;">
<?php
      while ($link = array_shift($links)) {

        $link = basename($link); // Get the directory name from the full path
        if (is_dir(APP_PATH . '../../clientele/' . $link)) {
          echo '  <option value="' . $link . '" ' . (current($_GET) == $link ? 'selected' : '') . '>' . $link . '</option>' . "\n";
        }

      }
      ?>
            </select>
          </form>
	    </ul>
<?php } ?></li>
-->

<?php } else { ?>

<table width="" style="border: 0 solid #000;">
<tr>
<?php
$paths = glob($path . '/' . '{.[!.]*,*}', GLOB_BRACE | GLOB_MARK);
//dd(urldecode($_GET['path']));

usort($paths, function ($a, $b) {
    $aIsDir = is_dir($a);
    $bIsDir = is_dir($b);

    // Check if either $a or $b is the "project.php" file
    $aIsProjectFile = !$aIsDir && basename($a) === 'project.php';
    $bIsProjectFile = !$bIsDir && basename($b) === 'project.php';

    // Handle the case when either $a or $b is the "project.php" file
    if ($aIsProjectFile || $bIsProjectFile) {
        if ($aIsProjectFile && $bIsProjectFile) {   // -1 0 1
            return -1; // Both are "project.php" files, no change in order
        } elseif ($aIsProjectFile) {
            return 0; // $a is "project.php", move it down
        } else {
            return 1; // $b is "project.php", move it up
        }
    }

    // Directories go first, then files
    if ($aIsDir && !$bIsDir) {
        return -1;
    } elseif (!$aIsDir && $bIsDir) {
        return 1;
    }

    // If both are directories or both are files, sort alphabetically
    return strcasecmp($a, $b);
});
/*
usort($paths, function ($a, $b) {
    $aIsDir = is_dir($a);
    $bIsDir = is_dir($b);
    
    // Directories go first, then files
    if ($aIsDir && !$bIsDir) {
        return -1;
    } elseif (!$aIsDir && $bIsDir) {
        return 1;
    }

    // If both are directories or both are files, sort alphabetically
    return strcasecmp($a, $b);
});
*/
$count = 1;
if (!empty($paths))
  foreach($paths as $key => $path) {
      echo '<td style="border: 0 solid #000; text-align: center;" class="text-xs">' . "\n";
      if (is_dir($path))
        if (basename($path) == '.git')
          echo '<a href="#" onclick="document.getElementById(\'app_git-container\').style.display=\'block\';">' // "?path=' . basename($path) . '" 
          . '<img src="resources/images/directory-git.png" width="50" height="32" /><br />' . basename($path) . '/</a>' . "\n";
        elseif (basename($path) == 'vendor')
          echo '<div style="position: relative;">'
          . '<a href="#" onclick="document.getElementById(\'app_composer-container\').style.display=\'block\';"><img src="resources/images/directory-composer.png" width="50" height="32" /></a><br />'
          . '<a href="?path=' . basename($path) . '" onclick="">' . basename($path)  // "?path=' . basename($path) . '"         
          . '/</a></div>' . "\n";
        elseif (basename($path) == 'node_modules')
          echo '<div style="position: relative;">'
          . '<a href="#" onclick="document.getElementById(\'app_composer-container\').style.display=\'block\';"><img src="resources/images/directory-npm.png" width="50" height="32" /></a><br />'
          . '<a href="?path=' . basename($path) . '" onclick="">' . basename($path)  // "?path=' . basename($path) . '"         
          . '/</a></div>' . "\n";
        else 
          echo '<a href="?path=' . basename($path) . '">'
          . '<img src="resources/images/directory.png" width="50" height="32" /><br />' . basename($path) . '/</a>';
      elseif (is_file($path)) {

        
        if (preg_match('/^\..*/', basename($path))) {
        
          if (basename($path) == '.htaccess')
            echo '<div style="position: relative;"><a href="?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '"><img src="resources/images/htaccess_file.png" width="40" height="50" /><br />' . basename($path)
            . '</a>'
/*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
            . '</div>' . "\n";
        
          elseif(basename($path) == '.babelrc')
            echo '<div style="position: relative;"><a href="?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '"><img src="resources/images/babelrc_file.png" width="40" height="50" /><br />' . basename($path)
            . '</a>'
/*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
            . '</div>' . "\n";
          
          elseif (basename($path) == '.gitignore')
            echo '<div style="position: relative;"><a href="?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '"><img src="resources/images/gitignore_file.png" width="40" height="50" /><br />' . basename($path)
            . '</a>'
/*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
            . '</div>' . "\n";
          
          elseif (basename($path) == '.env.development')
            echo '<div style="position: relative;"><a href="?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '"><img src="resources/images/env_file.png" width="40" height="50" /><br />' . basename($path)
          . '</a>'
/*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
          . '</div>' . "\n";
          
          else
            echo '<div style="position: relative;"><a href="?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '"><img src="resources/images/htaccess_file.png" width="40" height="50" /><br />' . basename($path)
            . '</a>'
/*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
            . '</div>' . "\n";

        } elseif (preg_match('/composer(?:-setup)?\.(json|lock|php|phar)/', basename($path))) {
          echo '<a href="?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '">';

          if (basename($path) == 'composer.json')
            echo '<div style="position: relative;"><img src="resources/images/composer_json_file.gif" width="40" height="50" /><br />' . basename($path)
          . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' )
          . '</a></div>' . "\n";

          elseif (basename($path) == 'composer.lock')
            echo '<div style="position: relative;"><img src="resources/images/composer_lock_file.gif" width="40" height="50" /><br />' . basename($path)
            . (isset($errors['COMPOSER-VALIDATE-LOCK']) ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' )
/*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
          . '</a></div>' . "\n";

          elseif (basename($path) == 'composer.phar')
            echo '<div style="position: relative;"><img src="resources/images/phar_file.png" width="40" height="50" /><br />' . basename($path)
/*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
          . '</a></div>' . "\n";

          else
            echo '<div style="position: relative;"><img src="resources/images/composer_php_file.gif" width="40" height="50" /><br />' . basename($path)
            . '</a></div>' . "\n";
        }

/**/
        elseif (preg_match('/.*\.php/', basename($path))) {
          if (preg_match('/^project\.php/', basename($path)))
            echo '<a style="position: relative;" href="' . (isset($_GET['project']) ? '?project#!' : '?project') . '" ' . (isset($_GET['project']) ? 'onclick="document.getElementById(\'app_project-container\').style.display=\'block\';"' : '') . '><div style="position: absolute; left: -60px; top: -20px; color: red; font-weight: bold;">' . (isset($_GET['project']) ? '' : '<img src="resources/images/click-here.gif" width="75" height="35" />') . '</div><img src="resources/images/project-icon.png" width="40" height="50" /></a><br /><a href="' . (isset($_GET['project']) ? '?project#!' : '?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path)) . '" ' . (isset($_GET['project']) ? 'onclick="document.getElementById(\'app_text_editor-container\').style.display=\'block\';"' : '') . '">' . basename($path) . '</a>';
          else echo '<a href="?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '"><img src="resources/images/php_file.png" width="40" height="50" /><br />' . basename($path) . '</a>';

        } elseif (basename($path) == basename(ini_get('error_log')))
          echo '<a href="?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '">'
          . '<div style="position: relative;"><img src="resources/images/error_log.png" width="40" height="50" /></a><br /><a href="?' . basename(ini_get('error_log')) . '=unlink" style="text-decoration: line-through; background-color: red; color: white;">' . basename($path)
          . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '</a><div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' )
          . '</div>' . "\n";
        else
          echo '<a href="?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '"><img src="resources/images/php_file.png" width="40" height="50" /><br />' . basename($path) . '</a>';
      }
      echo '</td>' . "\n";
      if ($count >= 6 || $path == end($paths)) echo '</tr>';
      if (isset($count) && $count >= 6) $count = 1;
      else $count++;
  }
?>
</tr>
</table>

<?php } ?>
</div>

</div>

<?= $appTimesheet['body']; ?>

<?= $appComposer['body']; ?>

<?= $appGit['body']; ?>

<?= $appPHP['body'] ?>

<?= $appGithub['body']; ?>

<?= $appPackagist['body']; ?>

<?= $appWhiteboard['body']; ?>

<?= $appNotes['body']; ?>

<!-- https://pong-2.com/ -->

<?= $appPong['body']; ?>

<?= $appTextEditor['body']; ?>

</div>

<?= $appConsole['body']; ?>
  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  
  <!-- https://code.jquery.com/jquery-3.7.1.min.js -->

  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  

  <!-- script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script -->

<?php

$path = APP_PATH . APP_BASE['resources'] . 'js/jquery/';
is_dir($path) or mkdir($path, 0755);
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

?>
  <script src="<?= (check_http_200('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : $path . 'jquery-3.7.1.min.js') ?>"></script>

<!-- For Text / Ace Editor -->

  <!-- <script src="https://unpkg.com/@popperjs/core@2" type="text/javascript" charset="utf-8"></script> -->
  
<?php
is_dir(APP_PATH . APP_BASE['resources'] . 'js/requirejs') or mkdir(APP_PATH . APP_BASE['resources'] . 'js/requirejs', 0755);
if (is_file(APP_PATH . APP_BASE['resources'] . 'js/requirejs/require-2.3.6.js')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime(APP_PATH . APP_BASE['resources'] . 'js/requirejs/require-2.3.6.js'))))) / 86400)) <= 0 ) {
    $url = 'https://requirejs.org/docs/release/2.3.6/minified/require.js';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($js = curl_exec($handle))) 
      file_put_contents(APP_PATH . APP_BASE['resources'] . 'js/requirejs/require-2.3.6.js', $js) or $errors['JS-REQUIREJS'] = $url . ' returned empty.';
  }
} else {
  $url = 'https://requirejs.org/docs/release/2.3.6/minified/require.js';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($js = curl_exec($handle))) 
    file_put_contents(APP_PATH . APP_BASE['resources'] . 'js/requirejs/require-2.3.6.js', $js) or $errors['JS-REQUIREJS'] = $url . ' returned empty.';
}

?>
  <script src="<?= (check_http_200('https://requirejs.org/docs/release/2.3.6/minified/require.js') ? 'https://requirejs.org/docs/release/2.3.6/minified/require.js' : APP_URL_BASE . APP_BASE['resources'] . 'js/requirejs/require-2.3.6.js') ?>" type="text/javascript" charset="utf-8"></script>

  <script src="resources/js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script> <!-- -->
  <script src="resources/js/ace/src/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>

  <script src="resources/js/ace/src/mode-php.js" type="text/javascript" charset="utf-8"></script>
  
  <script src="resources/js/ace/src/theme-dracula.js" type="text/javascript" charset="utf-8"></script> -->

<!--   <script src="dist/bundle.js" type="text/javascript" charset="utf-8"></script> -->

<!-- End: For Text / Ace Editor -->

  <!-- <script src="resources/js/jquery/jquery.min.js"></script> -->

<?php ?>
  
  <script src="<?= array_rand(array_flip(array_filter(glob(APP_BASE['var'] . 'reels/*.js'), 'is_file')), 1); ?>" type="text/javascript" charset="utf-8"></script>
  
  <script type="text/javascript" charset="utf-8">
  
function toggleSwitch(element) {

  if (element.checked) {
    // Third option is selected
    // Add your logic here
    console.log('checked');
    
$("#debug-content").hide("slide", { direction: "right" }, 1000);

  } else {
    // Third option is not selected
    // Add your logic here
    console.log('(not) checked');
    
$("#debug-content").show("slide", { direction: "left" }, 1000);

  }
}

$(document).ready(function(){
    if ($( "#app_directory-container" ).css('display') == 'none') {
<?php if (!empty($_GET)) { ?>
      $( '#app_directory-container' ).slideDown( "slow", function() {
      // Animation complete.
      });
<?php } ?>
    }
});

<?= $appConsole['script']; ?>

// Define the function to be executed when "c" key is pressed
function executeFunctionOnKeyPress(event) {
  // Check if the pressed key is "c" (you can use event.key or event.keyCode)

}

// Attach the event listener to the window object
window.addEventListener('keydown', show_console);

<?= $appComposer['script']; ?>

<?= $appGit['script']; ?>

<?= $appGithub['script']; ?>

<?= $appPackagist['script']; ?>

<?= /*$appWhiteboard['script'];*/ NULL; ?>

<?= /*$appNotes['script'];*/ NULL; ?>

<?= $appPong['script']; ?>

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
    require.config({
      baseUrl: window.location.protocol + "//" + window.location.host
      + window.location.pathname.split("/").slice(0, -1).join("/"),
      paths: {
        ace: "resources/js/ace/src"
      }
    })
    define('testace', ['ace/ace'],
        function(ace, langtools) {
            console.log(langtools);

<?= $appTextEditor['script']; ?>
            //require(["resources/js/requirejs/require-2.3.6!ace/ace"], function(e){
                //editor.setValue(e);
            //})
        }
    );
    require(['testace']);


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
);
*/

<?= $appTimesheet['script']; ?>

  </script>

  
</body>
</html>