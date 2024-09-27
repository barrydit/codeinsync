<?php

//dd(get_defined_constants(true)['user']); dd(get_included_files());

if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"]))
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    if (is_file($path = realpath('config.php'))) require_once $path;
  } else die(var_dump("Path was not found. file=$path"));

header("Content-Type: text/html");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://d3js.org/d3.v7.min.js"></script>

    <base href="<?=(!is_array(APP_URL) ? APP_URL : APP_URL_BASE) . (preg_match('/^\/(?!\?)$/', $_SERVER['REQUEST_URI']) ? '?' : '') . (!defined('APP_DEBUG') ? '#' : '?' . (APP_URL_BASE['query'] != '' ? APP_URL_BASE['query'] : '')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : ''); ?>">

    <title>Multiple Ace Editor Instances</title>

<?php
// (check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/tailwindcss-3.3.5.js')?
// <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
// <link rel="stylesheet" href="resources/css/output.css">

if (!is_file($path = APP_PATH . APP_BASE['resources'] . 'js/tailwindcss-3.3.5.js') || ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path))))) / 86400)) <= 0  ) {
    $url = 'https://cdn.tailwindcss.com';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($js = curl_exec($handle)))
        file_put_contents($path, $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
}

if (is_file($path)) { ?>
    <script src="<?= APP_BASE['resources'] . 'js/tailwindcss-3.3.5.js' ?? $url ?>"></script>
<?php }
unset($path);
?>

    <style type="text/tailwindcss">
        .editor {
            width: 500px;
            height: 200px;
            margin-bottom: 20px;
        }
        * {
        margin: 0;
	      padding: 0;
	      box-sizing: border-box;
<?php if (isset($_GET['debug'])) { ?>
        border: 1px dashed #FF0000;
<?php } else { ?> 
        /* border: 1px dashed #FF0000; */
<?php } ?>
      }
      *:focus {
	      outline: none;
      }

      body {
        background-color: #FFF;
        overflow-x: hidden;
        font-family: Arial, sans-serif;
      }
      .row-container { display: flex; width: 100%; height: 100%; flex-direction: column; overflow: hidden; }

.overlay2 {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10;
}

.dialog2 {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    resize: both; /* Make the div resizable */
    overflow: hidden; /* Hide overflow to ensure proper resizing */
    display: flex;
    flex-direction: column;
}

      <?= defined('UI_GIT') ? UI_GIT['style'] : null; ?>
      <?= defined('UI_PHP') ? UI_PHP['style'] : null; /* print(...) */ ?>
      <?= defined('UI_COMPOSER') ? UI_COMPOSER['style'] : null; /* (isset($appComposer) ? $appComposer['script'] : null); */ ?>
      <?= defined('UI_NPM') ? UI_NPM['style'] : null; ?>
      <?= defined('UI_ACE_EDITOR') ? UI_ACE_EDITOR['style'] : null; ?>
      <?= defined('UI_NODES') ? UI_NODES['style'] : null; ?>

      <?= $apps['browser']['style']; ?>
      <?= $apps['github']['style']; ?>
      <?= $apps['packagist']['style']; ?>
      <?= $apps['whiteboard']['style']; ?>
      <?= $apps['notes']['style']; ?> 
      <?= $apps['pong']['style']; ?>

      <?= /*$appBackup['style']*/ NULL; ?>
      <?= $apps['console']['style']; ?>
      <?= $apps['timesheet']['style']; ?>
      <?= $apps['project']['style']; ?>
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
      max-width: 100px;
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

<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; z-index: 1; border: 1px solid green;"> <!-- position: relative; width: 100%; height: 100%; z-index: 1; -->
<!--
    <div id="overlay2" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; z-index: 10;">
      <div id="confirmDialog" class="dialog2" style="position: relative; height: 175px; width: 350px; text-align: center;">
        <div style="position: absolute; display: block; background-color: #FFFFFF; z-index: 1; right: 0px; float: right; margin-top: -20px;">[<a href="#" onclick="document.getElementById('overlay2').style.display = 'none';">x</a>]</div>

      </div>
    </div>
    -->
<div class="row-container" style="position: absolute; left: 0; top: 0;">
  <?php // https://stackoverflow.com/questions/86428/what-s-the-best-way-to-reload-refresh-an-iframe ?>
  <iframe id="iWindow" src="<?php if (!empty($_GET['client'])) {
      $path = /*'../../'.*/ '/clientele/' . $_GET['client'] . '/';
      $dirs = array_filter(glob(dirname(__DIR__) . '/' . $path . '*'), 'is_dir');

      if (count($dirs) == 1)
        foreach($dirs as $dir) {
          $dirs[0] = $dirs[array_key_first($dirs)];
          if (preg_match(DOMAIN_EXPR, strtolower(basename($dirs[0])))) {
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
            if (is_dir($dirs[$key].'/public/'))
              $path .= basename($dirs[$key]).'/public/';
            else 
              $path .= basename($dirs[$key]);
            break;
          }
        }
      else if (!isset($_GET['domain']) && count($dirs) >= 1) {
        $path .= basename($_GET['domain'] = array_values($dirs)[0]) . DIRECTORY_SEPARATOR;

        if (is_dir(dirname(__DIR__) . $path . 'public/')) {
          $path .= 'public/';
        }
        //die(var_dump($path));
      }
        //else 
        //exit(header('Location: http://localhost/clientele/' . $_GET['client']));    
    
      //$path = '?path=' . $path;
    } elseif (!empty($_GET['project'])) {
      $path = '/projects/' . $_GET['project'] . '/';   
      //$dirs = array_filter(glob(dirname(__DIR__) . '/projects/' . $_GET['project'] . '/*'), 'is_dir');
      
    } else { $path = ''; }  
    //if (empty(APP_URL['query'])) echo 'developer.php';
    //else
    // developer.php
    echo $path; ?>" style="height: 100%;"></iframe>
</div>
<?= /* $apps['backup']['body'] */ NULL;?>
<div id="app_container" style="position: relative; display: none; margin: 0px auto; width: 100%; border: 1px solid #000; background-color: rgba(0, 0, 0, 0.5);">

  <div style="position: relative; margin: 0px auto; width: 800px;">
    <!-- div style="position: absolute; <?= /* (empty($errors) ? 'display: none;' : '') */ NULL; ?>left: -144px; /*width: 150px;*/ z-index: 3;">TEST</div -->
    <div id="debug-content" class="absolute" style="position: absolute; display: none; right: 0; text-align: right; background-color: rgba(255, 255, 255, 0.8); border: 1px solid #000; width: 800px; z-index: 1; overflow: visible;">
      <div style="float: left; display: inline; margin: 5px;"><form style="display: inline;" action="/" method="POST">Stage: 
  <select name="environment" onchange="this.form.submit();">
    <option value="develop" <?= defined('APP_ENV') && APP_ENV == 'development' ? 'selected' : '' ?>>Development</option>
    <option value="product" <?= defined('APP_ENV') && APP_ENV == 'production' ? 'selected' : '' ?>>Production</option>
    <option value="math" <?= defined('APP_ENV') && APP_ENV == 'math' ? 'selected' : '' ?>>Math</option>
  </select></form>
  </div>

      <a href="#" onclick="document.getElementById('app_ace_editor-container').style.display='block';"><img src="resources/images/ace_editor_icon.png" width="32" height="32">ACE Editor</a> |
      <a href="#" onclick="document.getElementById('app_tools-container').style.display='block';"><img src="resources/images/apps_icon.gif" width="20" height="20"> Tools</a> |
      <a href="#" onclick="document.getElementById('app_timesheet-container').style.display='block';"><img src="resources/images/clock.gif" width="30" height="30"> Clock-In</a> |
      <a href="#" onclick="document.getElementById('app_git-container').style.display='block';"><img src="resources/images/git_icon.fw.png" width="18" height="18">Git/ <img src="resources/images/github.fw.png" width="18" height="18">Hub</a>

      <div style="position: relative; margin-left: 10px; right: 6px; float: right; z-index: 1;">
      <div class="text-sm" style="display: inline-block; margin: 0 auto;">
        <form class="app_git-push" action="<?= APP_URL . '?' . http_build_query(APP_QUERY + ['app' => 'git']) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
          <!-- <input type="hidden"  /> -->
          <button type="submit" name="cmd" value="push" disabled><img src="resources/images/green_arrow.fw.png" width="20" height="25" style="cursor: pointer; margin-left: 6px;" title="This feature is disabled." /><br />Push</button>
        </form>
      </div>
      <div class="text-sm" style="position: relative; display: inline-block; margin: 0 auto; border: 2px dashed #F00;">
        <div style="position: absolute; display: <?= (isset($errors['GIT_UPDATE']) ? 'block' : 'none') ?>; left: 26px; top: 5px; width: 126px; background-color: #0078D7; color: #FFF; z-index: -1; font-variant-caps: all-small-caps;"><span style="background-color: #FFF; color: #0078D7;">&lt;- </span><span style="background-color: #FFF; color: red; margin-right: 2px;">Click to update&nbsp;</span></div>
        <form class="app_git-pull" action="<?= APP_URL . '?' . http_build_query(APP_QUERY + array( 'app' => 'git')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
          <!-- <input type="hidden"  /> -->
          <button type="submit" name="cmd" value="pull"><img src="resources/images/red_arrow.fw.png" width="20" height="25" style="cursor: pointer; margin-left: 4px;" /><br />Pull</button>
        </form>
      </div>
    </div>

      
      <div style="position: absolute; top: 40px; left: -15px; z-index: 1; background-color: white; border: <?= defined('APP_ROOT') && APP_ROOT != '' || isset($_GET['path']) ? '2px dashed red' : '1px solid #000'; ?>;">
        <div style="display: inline; margin-top: -7px; float: left; "><a style="font-size: 18pt; font-weight: bold; padding: 0 3px;" href="<?= isset($_GET['path']) ? '/' : '/?path' ?>" onclick="<?= isset($_GET['path']) ? '' : 'handleClick(event, \'/\')' ?>">&#8962; </a></div>
        <?php $path = realpath(APP_ROOT . (isset($_GET['path']) ? DIRECTORY_SEPARATOR . $_GET['path'] : '')) . DIRECTORY_SEPARATOR; // getcwd()
          if (isset($_GET['path'])) { ?>
        <!-- <input type="hidden" name="path" value="<?= $_GET['path']; ?>" /> -->
        <?php } ?>
        <?= 
          //APP_URL_BASE . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ array( 'app' => 'ace_editor')*/) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') 
          
          /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ NULL; ?>
        <?= "          <button id=\"displayDirectoryBtn\" style=\"margin: 2px 5px 0 0;\" type=\"\">&nbsp;&#9660;</button> \n"; ?>
        <?php
          $main_cat = '        <form style="display: inline;" autocomplete="off" spellcheck="false" action="" method="GET">/'  . "\n"
          . '            <select name="category" onchange="this.form.submit();">' . "\n"
          
          . '              <option value="" ' . (empty(APP_QUERY) ? 'selected' : '') . '>' . basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) . '</option>' . "\n"
          . '              <option value="application" ' . (isset($_GET['application']) ? 'selected' : '') . ' ' . (realpath(APP_PATH . /*'../../'.*/ 'applications/') ? '' : 'disabled') . '>applications</option>' . "\n"
          . '              <option value="client" ' . (isset($_GET['client']) ? 'selected' : '') . '>clientele</option>' . "\n"
          . '              <option value="projects" ' . (isset($_GET['project']) && $_GET['project'] || preg_match('/(?:^|&)project(?:[^&]*=)/', $_SERVER['QUERY_STRING']) ? 'selected' : '') . '>projects</option>' . "\n"
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
          </select> /</span> <a href="#" onclick="document.getElementById('info').style.display = 'block';">+</a></form>

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
                  echo '              <option value="' . $link . '" ' . (current($_GET) == $link || $_GET['client'] == $link ? 'selected' : '') . '>' . $link . '</option>' . "\n";
              }
              ?>
          </select> /</span>
          </form><?php if (!empty($_GET['client'])) {
          $dirs = array_filter(glob(dirname(__DIR__) . /*'../../'.*/ '/clientele/' . $_GET['client'] . '/*'), 'is_dir'); ?><form style="display: inline;" autocomplete="off" spellcheck="false" action="" method="GET"><?= isset($_GET['client']) && !$_GET['client'] ? '' : '<input type="hidden" name="client" value="' . $_GET['client'] . '" / >' ?><select id="domain" name="domain" onchange="this.form.submit();">
            <option value="" <?= (isset($_GET['domain']) && $_GET['domain'] == '' ? 'selected' : '') ?>>---</option>
            <?php foreach ($dirs as $dir) { ?>
            <option <?= (isset($_GET['domain']) && $_GET['domain'] == basename($dir) ? 'selected' : '') ?>><?= basename($dir); ?></option>
            <?php } ?>
          </select> / <a href="#" onclick="document.getElementById('info').style.display = 'block';">+</a></form>
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
      </select> / <a href="#" onclick="document.getElementById('info').style.display = 'block';">+</a>
      </span>
      </form>

<?php } ?>
      </div>
      <div style="position: absolute; width: auto; top: 40px; right: -10px; border: 1px dashed green; height: 25px;">
        <div id="clockTime"></div>
      </div>
      <div id="app_tools-container" style="position: absolute; display: none; width: 800px; margin: 0 auto; height: 500px; background-color: rgba(255, 255, 255, 0.9); overflow-x: scroll;">
        <div style="position: absolute; margin: 80px 45px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_tools-container').style.display='none'; return false;"><img style="text-align: center;" height="25" width="25" src="<?= APP_BASE['resources'] . 'images/close-red.gif' ?>" /></a><br /></div>
        <div style="position: absolute; margin: 100px 75px; text-align: center;" class="text-sm"><a href="#!" onclick="isFixed = true; show_console(); return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/cli.png' ?>" /></a><br /><a href="?app=ace_editor&path=&file=app.console.php" style="text-align: center;">(CLI)</a></div>
        <!-- 
          <a href="javascript:window.open('print.html', 'newwindow', 'width=300,height=250')">Print</a>
          onclick="window.open('app.whiteboard.php', 'newwindow', 'width=300,height=250'); return false;"
          
          https://stackoverflow.com/questions/12939928/make-a-link-open-a-new-window-not-tab
           -->
        <div style="position: absolute; margin: 100px 165px; text-align: center;" class="text-sm"><a href="#" target="_blank" onclick="toggleIframeUrl('app.whiteboard.php'); return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/whiteboard.png' ?>" /></a><br /><a href="?app=ace_editor&path=&file=app.whiteboard.php" style="text-align: center;">Whiteboard</a></div>
        <div style="position: absolute; margin: 100px 260px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_notes-container').style.display='block'; return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/notes.png' ?>" /></a><br /><a href="?app=ace_editor&path=&file=app.notes.php" style="text-align: center;">Notes</a></div>
        <div style="position: absolute; margin: 100px 350px; text-align: center;" class="text-sm"><a href="#!" onclick="document.getElementById('app_project-container').style.display='block'; document.getElementById('toggle-debug').checked = false; toggleSwitch(document.getElementById('toggle-debug')); return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/project.png' ?>" /></a><br /><a href="?app=ace_editor&path=&file=app.project.php"><span style="text-align: center;">Project</span></a></div>
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
            <input class="input" id="toggle-project" type="checkbox" onchange="toggleSwitch(this); this.form.submit();" <?= isset($_GET['project']) ? 'checked' : '' ?> />
            <label class="label" for="toggle-project" style="margin-left: -6px;">
              <div class="left"> Client </div>
              <div class="switch" style="position: relative;"><span class="slider round"></span></div>
              <div class="right"> Project </div>
            </label>
          </form>
        </div>
        <div style="position: absolute; margin: 200px 0 0 540px; text-align: center;" class="text-sm"><a href="#!" onclick="toggleIframeUrl('app.pong.php'); return false;"><img style="text-align: center;" src="<?= APP_BASE['resources'] . 'images/pong.png' ?>" /><br /><span style="text-align: center;">Pong</span></a></div>
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
  <?php if (isset($_GET['client']) && $_GET['client'] != '') { 
    
if (!empty(APP_HOST) && APP_HOST != '127.0.0.1' || APP_DOMAIN != 'localhost') {
  if (check_http_status() && class_exists('Whois')) {
    $whois = new Whois();

    !defined('APP_WHOIS') and define('APP_WHOIS', $whois->lookup($query = APP_DOMAIN, false)); 
  }
} ?>
  <div id="app_client-container" style="position: relative; display: <?= (!defined('APP_WHOIS') ? 'none' : 'block') . ';'; ?> top: 100px; margin: 0 auto; width: 800px; height: 600px; background-color: rgba(255, 255, 255, 0.9); overflow-x: scroll;">
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
$output = (preg_match('/^\d*-(\w+)[,]\s*(\w+)$/', $decoded, $matches)) ? "$matches[2] $matches[1]" : 'Invalid Input';
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
          <option><?= "$statusCode - $status" ?></option>
<?php $count = 1; } ?>
        </select>
        <br />
        Name: <input type="text" value="<?= $output; ?>" /><br />
        Hours: <input type="text" value="999" />
      </div>
      <div style="display: inline-block; float: right; text-align: right;">
        <span style="">
          Domain: 
          <select>
            <option><?= $_GET['domain'] ?? 'example.com' ?></option>
          </select>
        </span>
        <br />
        <span style="">Add Domain: <input type="text"></span><br />
        <span>Domain Expiry: <input type="text" value="
<?php

// Get the domain name
if (defined('APP_WHOIS') && !empty(APP_WHOIS)) {
  $result = APP_WHOIS;
  echo !empty($result) && isset($result['regrinfo']['domain']['expires']) ? $result['regrinfo']['domain']['expires'] : 'Unknown';
} else {
  echo 'Unknown';
}

//dd(get_required_files());


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
                      echo "            <option>$ip_addr</option>\n";
                    }
                  else
                    echo "            <option></option>\n";
                  
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
  <?php } echo $apps['directory']['body']; ?>
  
  <div id="app_notes-container" style="position: absolute; display: none; top: 100px; margin: 0 auto; width: 800px; height: 600px; background-color: rgba(255, 255, 255, 0.9); overflow-x: scroll;">
    <div style="display: inline;">
      <span style="background-color: #B0B0B0; color: white;">
      <input type="checkbox" checked /> Preview Domain
      </span>
    </div>
    <div style="display: inline; float: right; text-align: center; ">
      <code style=" background-color: white; color: #0078D7;">
      <a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_notes-container').style.display='none';">[X]</a>
      </code>
    </div>
    <div style="margin: 0 10px;">
      <div style="display: inline-block; float: left; width: 49%;">
        <h3>Notes</h3>
        <textarea style="width: 100%; height: 300px;"></textarea>
      </div>
      <div style="display: inline-block; float: right; text-align: right;">
        <h3>Notes</h3>
        <textarea style="width: 100%; height: 300px;"></textarea>
      </div>
    </div>
  </div>
</div>

<?= $apps['timesheet']['body']; ?>
<?= $apps['browser']['body']; ?>
<?= $apps['github']['body']; ?>
<?= $apps['packagist']['body']; ?>
<?= $apps['whiteboard']['body']; ?>
<?= $apps['notes']['body']; ?>
<!-- https://pong-2.com/ -->
<?= $apps['pong']['body']; ?>

</div>
</div>
<!-- /div -->

<?= defined('UI_GIT') ? UI_GIT['body'] : null; ?>
<?= defined('UI_PHP') ? UI_PHP['body'] : null; /* print(...) */ ?>
<?= defined('UI_COMPOSER') ? UI_COMPOSER['body'] : null; /* (isset($appComposer) ? $appComposer['script'] : null); */ ?>
<?= defined('UI_NPM') ? UI_NPM['body'] : null; ?>
<?= defined('UI_ACE_EDITOR') ? UI_ACE_EDITOR['body'] : null; ?>
<?= defined('UI_NODES') ? UI_NODES['body'] : null; ?>

<?= $apps['project']['body']; ?>

<?= $apps['console']['body']; ?>
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
        file_put_contents("{$path}jquery-3.7.1.min.js", $js) or $errors['JS-JQUERY'] = "$url returned empty.";
    }
  } else {
    $url = 'https://code.jquery.com/jquery-3.7.1.min.js';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  
    if (!empty($js = curl_exec($handle))) 
      file_put_contents("{$path}jquery-3.7.1.min.js", $js) or $errors['JS-JQUERY'] = "$url returned empty.";
  }
  unset($path);
include 'app.debug.php';
  ?>


<div id="details-container" style="position: fixed; display: <?= is_resource($_SERVER['SOCKET']) ? 'none' : 'block' ?>; top: 0; right: 0; padding: 4px; z-index: 1; text-align: right; border: 1px solid #000; height: auto; background-color: #FFF; width: 245px;">
  <div style="float: left;">$_ENV</div>
  <h3 style="font-weight: bolder;"><?= !is_file($pid_file = APP_PATH . 'server.pid') ? '' : '(PID=' . (int) file_get_contents(APP_PATH . 'server.pid') . ')'?> Server</h3>
  <div style="display: inline-block;">On / Off <input id="check_server_start" type="checkbox" <?= is_resource($_SERVER['SOCKET']) ? 'checked="checked"' : '' ?> onclick="validate()" /><br /><a href="">Status</a><br /><a href="">Help</a><br />Error Log</div>
  <img id="serverStatus" src="/resources/images/server<?= is_resource($_SERVER['SOCKET']) ? '-green' : '' ?>.gif" style="float: right;" width="100" height="103">
  <!--form action="#!" method="GET">
      <?= isset($_GET['debug']) && !$_GET['debug'] ? '' : '<input type="hidden" name="debug" value / >' ?> 

              <input class="input" id="toggle-debug" type="checkbox" onchange="this.form.submit();" <?= isset($_GET['debug']) || defined('APP_ENV') && APP_ENV == 'development' ? 'checked' : '' ?> / -->
<div style="position: relative;">
      <div style="">&nbsp;&nbsp;&nbsp;&nbsp;<?= APP_NAME ?></div>
    <!-- /form -->
</div>
<hr />
<a href="?logout=true">Logout</a><br />

<div id="" style="">
  <span>Loading Time: <?= round(microtime(true) - APP_START, 3); ?>s</span><br />
  <span>Environment: <?= PHP_OS; ?></span><br />
  <span>Domain: <?= APP_DOMAIN; ?></span><br />
  <span>IP Address: <?= APP_HOST; ?></span><br />
  <span><?= isset($_GET['client']) ? 'Client: ' . '<a href="?client=' . $_GET['client'] . '">' . $_GET['client'] . '</a>': (isset($_GET['project']) ? 'Project: ' . '<a href="?project=' . $_GET['project'] . '">' . $_GET['project'] . '</a>' : 'Document Root: ' . $_SERVER['DOCUMENT_ROOT']); ?></span><br />
  <span>App Path: <?= APP_PATH; ?></span><br />
  <span>Memory: <em ><b style="color: green;"><?= formatSizeUnits(memory_get_usage()) . '</b> @ <b>' . formatSizeUnits(convertToBytes(ini_get('memory_limit'))); ?></b></em></span><br />
  <span>Source (code): <em style="font-size: 13px;"><?= '[<b>' . formatSizeUnits($total_filesize) . '</b>] <b style="color: red;">' . $total_filesize - 1000000 . '</b>' ?></em></span>
  <div style="position: relative; display: block;"><div style="position: absolute; display: block; float: right; right: 10px; width: 165px; text-align: right;"><?= ' [(<a style="font-weight: bolder; color: green;" href="#" onclick="document.getElementById(\'app_nodes-container\').style.display = \'block\';">' . $total_include_files . ' loaded</a>) <b>'. $total_files . '</b> files] <br /> [<b style="color: green;">' . $total_include_lines . '</b> @ <b>' . $total_lines . '</b> lines]'; ?></div></div><br /><br />
</div>
</div>


<div id="toggle-container" style="position: fixed; display: block; bottom: 0; right: 0; padding: 4px; z-index: 1; text-align: right; border: 1px solid #000; height: auto;">

<div style="display: inline-block;"><input class="input" id="toggle-debug" type="checkbox" onchange="toggleSwitch(this); return null;" <?= isset($_GET['debug']) || defined('APP_ENV') && APP_ENV == 'development' ? 'checked' : '' ?> /><label class="label" for="toggle-debug" style="margin: 0 auto; padding-top: 10px;">
        <div class="switch" style="display: inline; z-index: 1;">
          <span class="slider round"></span>
        </div>
      </label></div>

<div style="display: inline-block;">
<p style="color: #fff; background-color: #0078D7;">Developed by <a href="mailto:barryd.it@gmail.com" style="color: #eee">Barry R. Dick</a><br />
<?=APP_NAME?> (<a href="release-notes-<?=APP_VERSION?>.html" style="color: #eee;">v<?=APP_VERSION?></a>)</p></div>

</div>

<div id="adhd_song-container" style="position: fixed; display: none; bottom: 0; right: 0; z-index: 2;">
  <img src="/resources/reels/adhd_song.gif" />
</div>
<!--
    <div id="ui_ace_editor" class="editor">This is the first editor.</div>
    <div id="app_project_editor" class="editor">This is the second editor.</div>
-->

    <script src="<?= check_http_status('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : APP_BASE['resources'] . 'js/jquery/' . 'jquery-3.7.1.min.js' ?>"></script>
    <!-- You need to include jQueryUI for the extended easing options. -->
        <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
    
    <script src="<?= check_http_status('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js') ? 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js' : APP_BASE['resources'] . 'js/jquery-ui/' . 'jquery-ui-1.12.1.js' ?>"></script> <!-- Uncaught ReferenceError: jQuery is not defined -->
    
    <!-- <script src="resources/js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script> 
    <script src="resources/js/ace/src/ext-language_tools.js" type="text/javascript" charset="utf-8"></script> -->
    <!-- For Text / Ace Editor -->
    <!-- <script src="https://unpkg.com/@popperjs/core@2" type="text/javascript" charset="utf-8"></script> -->

<!--
    <script>
$(document).ready(function() {
    var editor = ace.edit("ui_ace_editor");
    // Rest of your initialization code
});
    </script>
</body>
</html>
-->
<?php

// (check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/tailwindcss-3.3.5.js')?
//!is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/') or mkdir($path, 0755, true);

if (!is_file($path = APP_PATH . APP_BASE['resources'] . 'js/requirejs/require.js') || ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path))))) / 86400)) <= 0  ) {
  !is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/requirejs') or @mkdir($path, 0755, true);
  !is_dir($path) and $errors['JS-REQUIREJS'] = "JS-REQUIREJS - Failed to create directory: $path";
  $url = 'https://requirejs.org/docs/release/2.3.6/minified/require.js';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($js = curl_exec($handle)))
    file_put_contents($path, $js) or $errors['JS-REQUIREJS'] = "$url returned empty.";
}

if (!is_file($path)) { ?>
  
    <script src="<?= APP_BASE['resources']; ?>js/requirejs/require.js" type="text/javascript" charset="utf-8"></script>

    <script>
    var globalEditor;
    require.config({
        baseUrl: window.location.protocol + "//" + window.location.host + window.location.pathname.split("/").slice(0, -1).join("/"),
        paths: {
            jquery: 'resources/js/jquery/jquery-3.7.1.min',
            'jquery-ui': 'resources/js/jquery-ui/jquery-ui-1.12.1',
            //domReady: 'resources/js/domReady',
            //bootstrap: 'resources/js/bootstrap/dist/js/bootstrap',
            ace: 'resources/js/ace/src/ace',
            'ace/ext-language_tools': 'resources/js/ace/src/ext-language_tools',
            'ace/mode/javascript': 'resources/js/ace/src/mode-javascript',
            'ace/mode/html': 'resources/js/ace/src/mode-html',
            'ace/mode/php': 'resources/js/ace/src/mode-php',
            'ace/theme/monokai': 'resources/js/ace/src/theme-monokai',
            'ace/theme/github': 'resources/js/ace/src/theme-github'
        },
        shim: {
            'ace': {
                deps: ['ace/ext-language_tools'],
                exports: 'ace'
            },
            //'ace/ext-language_tools': ['ace'],
            'ace/mode/javascript': ['ace'],
            'ace/mode/html': ['ace'],
            'ace/mode/php': ['ace'],
            'ace/theme/monokai': ['ace'],
            'ace/theme/github': ['ace']
        }
    });

    //require(['jquery', 'domReady', 'ace', 'ace/ext-language_tools', 'ace/mode/javascript', 'ace/mode/html', 'ace/theme/monokai', 'ace/theme/github'], function($, domReady, ace) {
    //    domReady(function() {}
    //});

    require(['ace', 'ace/ext-language_tools', 'ace/mode/php', 'ace/mode/javascript', 'ace/mode/html', 'ace/theme/monokai', 'ace/theme/github'], function () {
        if (!ace) {
                console.error("Ace editor not loaded");
                return;
        }

        var editor1 = ace.edit("ui_ace_editor");
        //var JavaScriptMode = ace.require("ace/mode/javascript").Mode;
        editor1.setTheme("ace/theme/monokai");
        editor1.session.setMode("ace/mode/php");
        editor1.setAutoScrollEditorIntoView(true);
        editor1.setShowPrintMargin(false);
        editor1.setOptions({
            //  resize: "both"
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            enableSnippets: true
        });

        var editor2 = ace.edit("app_project_editor");
        editor2.setTheme("ace/theme/dracula");
        // (file_ext .js = javascript, .php = php)
        editor2.session.setMode("ace/mode/php");
        editor2.setAutoScrollEditorIntoView(true);
        editor2.setShowPrintMargin(false);
        editor2.setOptions({
            //  resize: "both"
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            enableSnippets: true
        });
        
        globalEditor = editor2;
    }, function (err) {
        console.error("Error loading Ace modules: ", err.requireModules);
        console.error(err);
    });
    </script>
<?php
} elseif (is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/ace')) { ?>

    <script src="resources/js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script> 
    <script src="resources/js/ace/src/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>
    
    <script>
    var globalEditor;
    var editor1, editor2;

    document.addEventListener("DOMContentLoaded", function() {

<?php if (!$_SERVER['SOCKET']) { ?>

  //if (confirm('(Re)Start Server?')) {
  //  $('#requestInput').val('server start');
  //  $('#requestSubmit').click();
  //} else {
  //  console.log('Cancel (Re)Start.');
  //}
<?php } ?>
/**/
        editor1 = ace.edit("ui_ace_editor");
        //var JavaScriptMode = ace.require("ace/mode/javascript").Mode;
        editor1.setTheme("ace/theme/monokai"); // github
        editor1.session.setMode("ace/mode/php");
        editor1.setAutoScrollEditorIntoView(true);
        editor1.setShowPrintMargin(false);
        editor1.setOptions({
            //  resize: "both"
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            enableSnippets: true
        });

        var editor2 = ace.edit("app_project_editor");
        editor2.setTheme("ace/theme/dracula");
        // (file_ext .js = javascript, .php = php)
        editor2.session.setMode("ace/mode/php");
        editor2.setAutoScrollEditorIntoView(true);
        editor2.setShowPrintMargin(false);
        editor2.setOptions({
            //  resize: "both"
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            enableSnippets: true
        });
        globalEditor = editor2;
    });
    </script>

<?php
}
unset($path);

if (date(/*Y-*/ 'm-d') == /*1928-*/ '08-07' ?? /*2023-*/ '03-30') { ?>
    <script src="resources/reels/leave-a-light-on.js" type="text/javascript" charset="utf-8"></script>
    <?php } elseif (date(/*Y-*/ 'm-d') == /*1976-*/ '03-20' ?? /*2017-*/ '07-20') { ?>
    <script src="resources/reels/leave-a-light-on.js" type="text/javascript" charset="utf-8"></script>
    <?php } else {  // array_rand() can't be empty ?>
    <script src="<?= APP_BASE['resources'] . 'reels/something_happened.js'; /*disturbed_-_it_wasnt_me.js adhd_song.js !empty($reels = glob(APP_PATH . 'resources/reels/*.js')) ? APP_BASE['resources'] . 'reels/' . basename(array_rand(array_flip(array_filter($reels, 'is_file')), 1)) : ''; APP_BASE['resources'] */?>" type="text/javascript" charset="utf-8"></script>
    <?php } ?>
    <script type="text/javascript" charset="utf-8">

let isDragging = false;
let activeWindow = null;

function makeDraggable(windowId) {
    const windowElement = document.getElementById(windowId);
    const headerElement = windowElement.querySelector('.ui-widget-header');
    let offsetX, offsetY;

    headerElement.addEventListener('mousedown', function(event) {
        if (!isDragging) {
            // Bring the clicked window to the front
            document.body.appendChild(windowElement);
            offsetX = event.clientX - windowElement.getBoundingClientRect().left;
            offsetY = event.clientY - windowElement.getBoundingClientRect().top;
            isDragging = true;
            activeWindow = windowElement;
        }
    });

    document.addEventListener('mousemove', function(event) {
        if (isDragging && activeWindow === windowElement) {
            const left = event.clientX - offsetX;
            const top = event.clientY - offsetY;

            // Boundary restrictions
            const maxX = window.innerWidth - windowElement.clientWidth; //  - 100
            const maxY = window.innerHeight - windowElement.clientHeight;

            windowElement.style.left = `${Math.max(0, Math.min(left, maxX))}px`;
            windowElement.style.top = `${Math.max(0, Math.min(top, maxY))}px`;
        }
    });

    document.addEventListener('mouseup', function() {
        if (activeWindow === windowElement) {
            isDragging = false;
            activeWindow = null;
        }
    });
}
      
      makeDraggable('app_ace_editor-container');
      makeDraggable('app_composer-container');
      makeDraggable('app_project-container');
      makeDraggable('app_git-container');
      makeDraggable('app_npm-container');
      makeDraggable('app_php-container');
      makeDraggable('app_nodes-container');
      makeDraggable('app_timesheet-container');
      //makeDraggable('console-settings');

      displayDirectoryBtn.addEventListener('click', () => {
      
      event.preventDefault();
      const appDirectoryContainer = document.getElementById('app_directory-container');
      
      //const styles = window.getComputedStyle(appDirectoryContainer);
      const displayDirectoryBtn = document.getElementById('displayDirectoryBtn');
      
      console.log('state : ' + appDirectoryContainer.style.display );

/**/
      if (appDirectoryContainer.style.display == 'none') {   

<?php if (isset($_GET['client']) && !$_GET['client']) { ?>
      if (confirm('Do you wish to display clients?')) {
    // User clicked OK
    console.log('User clicked OK');
    $( '#app_directory-container' ).slideDown( "slow", function() {
           // Animation complete.
    });
} else {
    // User clicked Cancel
    console.log('User clicked Cancel');

    $( '#app_directory-container' ).slideUp( "slow", function() {
           // Animation complete.
    });
}<?php } else { ?>
      $( '#app_directory-container' ).slideDown( "slow", function() {
          // Animation complete.
     });
     <?php } ?>
        console.log('hide');
          displayDirectoryBtn.innerHTML = '&nbsp;&#9650;';
      } else {
 

        $( '#app_directory-container' ).slideUp( "slow", function() {
          // Animation complete.
        });
      
          displayDirectoryBtn.innerHTML = '&nbsp;&#9660;';  
        console.log('show');
      }

      //show_console();
      
      });
      
      
      
      
      function toggleSwitch(element) {
      
      if (element.checked) {
        // Third option is selected
        // Add your logic here
        console.log('checked');

        //getElementById('hide_notice-container');

        $( '#app_container' ).slideDown( "slow", function() {
          // Animation complete.
        });
        
        $( '#details-container' ).slideDown( "slow", function() {
          // Animation complete.
        });
        
        <?php if (isset($errors['GIT_UPDATE'])) { ?> 
        $( '#hide_notice-container' ).slideDown( "slow", function() {
          // Animation complete.
        });
        <?php } ?>
      
        $( '#app_console-container' ).slideDown( "slow", function() {
          // Animation complete.
        });

        <?php if (isset($_GET['client']) && $_GET['client']) { } ?>
        $( '#app_directory-container' ).slideDown( "slow", function() {
        // Animation complete.
        });


        
        $("#debug-content").css('overflow', 'visible');
        
      $("#debug-content").show("slide", { direction: "up" }, 1000);
      
      //$("#app_backup-container").show("slide", { direction: "right" }, 1000);
      
      } else {

        $( '#app_container' ).slideUp( "slow", function() {
          // Animation complete.
        });

        $( '#details-container' ).slideUp( "slow", function() {
          // Animation complete.
        });
        
        $( '#hide_notice-container' ).slideUp( "slow", function() {
          // Animation complete.
        });
        
        $( '#app_directory-container' ).slideUp( "slow", function() {
         // Animation complete.
        });
        
        // Third option is not selected
        // Add your logic here
        console.log('(not) checked');

        $( '#app_console-container' ).slideUp( "slow", function() {
          // Animation complete.
        });
        
<?= !empty($errors) ? ' show_console();' : 'show_console();' ?>

        $('#requestInput').attr('autofocus', true);
            
      $("#debug-content").hide("slide", { direction: "up" }, 1000);
      
      //$("#app_backup-container").hide("slide", { direction: "right" }, 1000);
      }
      }
      
      function toggleIframeUrl(uri_location) {
            // Uncheck the checkbox
            document.getElementById('toggle-debug').checked = false;
            
            toggleSwitch(document.getElementById('toggle-debug'));

            // Set the src attribute of the iframe
            document.getElementById('iWindow').src = uri_location;
        }
      
      $(document).ready(function(){
        $( "#app_console-container").css('display', 'none');

        <?php if (isset($_GET['path'])) { ?>

document.getElementById('toggle-debug').checked = true;

toggleSwitch(document.getElementById('toggle-debug'));
/**/
$( '#app_directory-container' ).slideDown( "slow", function() {
 // Animation complete.
});

<?php } ?>

        if ($( "#app_directory-container" ).css('display') == 'none') {
      <?php 
       if (!empty(APP_URL['query']) || isset($_GET['debug']) || (defined(APP_DEBUG) && APP_DEBUG)) { ?>

document.getElementById('toggle-debug').checked = true;

toggleSwitch(document.getElementById('toggle-debug'));
/**/
$( '#app_directory-container' ).slideDown( "slow", function() {
 // Animation complete.
});
      <?php } else if (isset($_GET['project'])) { ?>
        document.getElementById('toggle-debug').checked = true;

        toggleSwitch(document.getElementById('toggle-debug'));
/**/
        $( '#app_directory-container' ).slideDown( "slow", function() {
         // Animation complete.
        });

      <?php } else if (defined('APP_ROOT') && APP_ROOT != '' && isset($errors['GIT_UPDATE']) && isset($_ENV['HIDE_UPDATE_NOTICE']) && $_ENV['HIDE_UPDATE_NOTICE'] != true ) { //  isset($_GET['client'])  !$_GET['client'] 
      
        
      if ($_GET['client'] != '') { ?>
          document.getElementById('toggle-debug').checked = true;

          toggleSwitch(document.getElementById('toggle-debug'));
/*
          $( '#app_directory-container' ).slideDown( "slow", function() {
           // Animation complete.
          });
*/
        <?php } else if (!empty($_GET)) { ?>

          document.getElementById('toggle-debug').checked = true;
            
          toggleSwitch(document.getElementById('toggle-debug'));
          
          <?php } else { ?>
      
      <?php if (!isset($_GET['domain'])) { // !$_GET['client'] ?>

          document.getElementById('toggle-debug').checked = true;
            
          toggleSwitch(document.getElementById('toggle-debug'));
          
      <?php }  }  } else { if (isset($_GET['client']) && !$_GET['client']) { ?>


        if (confirm('Do you wish to display clients?')) {
// User clicked OK
console.log('User clicked OK');
$( '#app_directory-container' ).slideDown( "slow", function() {
       // Animation complete.
});
} else {
// User clicked Cancel
console.log('User clicked Cancel');
$( '#app_directory-container' ).slideUp( "slow", function() {
       // Animation complete.
});
}



          <?php  if (!isset($_GET['domain'])) { // !$_GET['client'] ?>

          document.getElementById('toggle-debug').checked = true;
            
          toggleSwitch(document.getElementById('toggle-debug'));
          
      <?php } else {?>
/*
          document.getElementById('toggle-debug').checked = true;

          toggleSwitch(document.getElementById('toggle-debug'));

          $( '#app_directory-container' ).slideDown( "slow", function() {
           // Animation complete.
          });
*/
        <?php } } else if (isset($_GET['client']) && $_GET['client'] != '') { ?>
          document.getElementById('toggle-debug').checked = false;


          //toggleSwitch(document.getElementById('toggle-debug'));

          //$( '#app_directory-container' ).slideDown( "slow", function() {
           // Animation complete.
          //});

          <?php } } ?>
        }
      });
      
      <?= !isset($apps['console']['script'])?: $apps['console']['script']; ?>
      
      // Define the function to be executed when "c" key is pressed


      function validate(){
        var serverStatus = document.getElementById('serverStatus');
        serverStatus.src = '/resources/images/server-green.gif';

    var remember = document.getElementById('check_server_start');
    if (remember.checked){
        //alert("checked") ;
        $('#requestInput').val('server start');
        $('#requestSubmit').click();
    }else{
        //alert("You didn't check it! Let me check it for you.");
        $('#requestInput').val('server shutdown -f');
        $('#requestSubmit').click();

    }
}
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

      <?= defined('UI_GIT') ? UI_GIT['script'] : null; ?>
      <?= defined('UI_PHP') ? UI_PHP['script'] : null; /* print(...) */ ?>
      <?= defined('UI_COMPOSER') ? UI_COMPOSER['script'] : null; /* (isset($app['composer']) ? $app['composer']['script'] : null); */ ?>
      <?= defined('UI_NPM') ? UI_NPM['script'] : null; ?>
      <?= defined('UI_NODES') ? UI_NODES['script'] : null; ?>

      
      <?= defined('UI_ACE_EDITOR') ? UI_ACE_EDITOR['script'] : null; /* NULL; */?>
      
      <?= !isset($apps['directory']['script'])?: $apps['directory']['script']; ?>

      <?= !isset($apps['browser']['script'])?: $apps['browser']['script']; ?>
      
      <?= !isset($apps['github']['script'])?: $apps['github']['script']; ?>
      
      <?= !isset($apps['packagist']['script'])?: $apps['packagist']['script']; ?>
      
      <?= /*$app['whiteboard']['script'];*/ NULL; ?>
      
      <?= /*$app['notes']['script'];*/ NULL; ?>
      
      
      <?= /*$app['backup']['script']*/ NULL; ?>
      
      
      <?= !isset($apps['pong']['script'])?: $apps['pong']['script']; ?>
      
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
      
      <?= !isset($apps['timesheet']['script'])?: $apps['timesheet']['script']; ?>
      <?= !isset($apps['project']['script'])?: $apps['project']['script']; ?>



      function showConfirm(message) {
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('overlay2').style.display = 'flex';
}

function confirmAction(isConfirmed) {
    document.getElementById('overlay2').style.display = 'none';
    if (isConfirmed) {
        alert('Confirmed!');
    } else {
        alert('Cancelled!');
    }
}
    </script>
  </body>
</html>

<?= 
 NULL; /** Loading Time: 15.0s **/
//  dd(get_required_files(), true); 
?>