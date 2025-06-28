<?php //if (__FILE__==get_required_files()[0] && __FILE__==realpath($_SERVER["SCRIPT_FILENAME"]))

if (dirname(get_required_files()[0]) == getcwd()) {
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { //
    if (basename(getcwd())) {
      // if (is_file($path = realpath('index.php'))) require_once $path;
/*
      if (is_file($path = realpath('..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'php.php')))
        require_once $path;
      else
        die(var_dump("Path was not found. file=$path"));

      if ($index = realpath(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'index.php'))
        require_once $index; // APP_PATH . 'index.php'
*/
    }
  }
}

if (!headers_sent()) {
  header("Content-Type: text/html");
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  header("Pragma: no-cache");
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!--
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
  <meta charset="UTF-8">
  <title>CodeInSync DASHBOARD</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="CodeInSync Dashboard">
  <meta name="author" content="CodeInSync">
  <meta name="keywords" content="CodeInSync, Dashboard, Web Application">
  <meta name="theme-color" content="#ffffff">
  <!-- meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="CodeInSync">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="mobile-web-app-title" content="CodeInSync" -->

  <base
    href="<?= (!is_array(APP_URL) ? APP_URL : APP_URL_BASE) . (preg_match('/^\/(?!\?)$/', $_SERVER['REQUEST_URI'] ?? '') ? '?' : '') . (!defined('APP_DEBUG') ? '#' : '?' . (APP_URL_BASE['query'] != '' ? APP_URL_BASE['query'] : '')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : ''); ?>">

  <link rel="icon" href="resources/images/favicon.ico" type="image/x-icon">
  <link rel="shortcut icon" href="resources/images/favicon.ico" type="image/x-icon">
  <!-- link rel="apple-touch-icon" href="resources/images/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" sizes="57x57" href="resources/images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="resources/css/style.css">
  <link rel="stylesheet" href="resources/css/bootstrap.min.css">
  <link rel="stylesheet" href="resources/css/bootstrap.css">
  <link rel="stylesheet" href="resources/css/bootstrap-grid.min.css">
  <link rel="stylesheet" href="resources/css/bootstrap-grid.css">
  <link rel="stylesheet" href="resources/css/bootstrap-reboot.min.css">
  <link rel="stylesheet" href="resources/css/bootstrap-reboot.css">
    <link rel="stylesheet" href="resources/css/font-awesome.min.css">
  <link rel="stylesheet" href="resources/css/font-awesome.css">
<link rel="stylesheet" href="resources/css/normalize.css" -->
  <!-- link rel="stylesheet" href="resources/css/tailwind.min.css">
  <link rel="stylesheet" href="resources/css/tailwind.css">
  <link rel="stylesheet" href="resources/css/tailwind-utilities.min.css">
  <link rel="stylesheet" href="resources/css/tailwind-utilities.css">
  <link rel="stylesheet" href="resources/css/tailwind-components.min.css">
  <link rel="stylesheet" href="resources/css/tailwind-components.css" -->
  <base
    href="<?= (!is_array(APP_URL) ? APP_URL : APP_URL_BASE) . (preg_match('/^\/(?!\?)$/', $_SERVER['REQUEST_URI'] ?? '') ? '?' : '') . (!defined('APP_DEBUG') ? '#' : '?' . (APP_URL_BASE['query'] != '' ? APP_URL_BASE['query'] : '')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : ''); ?>">

  <?php
  // (check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/tailwindcss-3.3.5.js')?
// <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
// <link rel="stylesheet" href="resources/css/output.css">
  
  //
//
//
//
//
  


  if (!is_file($path = 'css' . DIRECTORY_SEPARATOR . 'tailwindcss.js') || ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path))))) / 86400)) <= 0) {
    $url = 'https://cdn.tailwindcss.com';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($js = curl_exec($handle)))
      file_put_contents($path, $js) or $errors['JS-TAILWIND'] = "$url returned empty.";
  }

  if (is_file($path = 'css' . DIRECTORY_SEPARATOR . 'tailwindcss.js')) { ?>
    <script src="<?= '/css/styles.css' ?? $url ?>"></script><?php }
  unset($path); ?>

  <script src="https://d3js.org/d3.v7.min.js"></script>

  <style type="text/tailwindcss"></style>
  <style>
* {
  overflow: hidden;
}

html,
body {
  margin: 0;
  height: 100%;
  overflow: hidden;
  font-family: sans-serif;
}

.container {
  position: relative;
  height: 100vh;
  width: 100%;
  overflow: hidden;
}

.sidebar {
  position: absolute;
  top: 0;
  left: 0;
  width: 200px;
  height: 100%;
  background: #ccc;
  border-right: 2px solid #999;
  resize: horizontal;
  overflow: auto;
  min-width: 100px;
  max-width: auto;
  z-index: 20;
}

.top-panel,
.bottom-panel,
.free-space {
  position: absolute;
  left: 200px;
  width: calc(100% - 200px);
}

.top-panel {
  top: 0;
  height: 44px;
  background: #EEEEEE;
  border-bottom: 1px solid #999;
  z-index: 20;
}

.bottom-panel {
  bottom: 0;
  height: 100px;
  background: #444444;
  color: white;
  border-top: 1px solid #999;
  z-index: 1;
}

.free-space {
  top: 44px;
  bottom: 100px;
  background: #f5f5f5;
  z-index: 1;
}

/* App container styles */
#app_notes-container,
#app_ace_editor-container,
#app_calendar-container,
/* #app_medication_log-container, */
#app_composer-container,
#app_git-container,
#app_node_js-container,
#app_nodes-container,
#app_npmjs-container,
#app_packagist-container,
#app_php-container,
#app_phpclasses-container,
#app_directory-container,
#app_project-container,
#app_timesheet-container,
#app_browser-container,
#app_github-container,
#app_whiteboard-container,
/* #app_console-container, */
#app_backup-container,
#app_pong-container {
  position: fixed;
  display: none;
  z-index: 1;
  background-color: rgba(255, 255, 255, 0.9);
  color: black;
}

/* Specific container styles */
#app_notes-container {
  width: 1207px;
  height: 500px;
  top: calc(50% - 250px);
  left: calc(50% - 603.5px);
}

#app_ace_editor-container {
  width: 550px;
  height: 450px;
  top: 60px;
  left: 30%;
}

#app_medication_log-container {
  width: 320px;
  height: 220px;
  border: 1px solid #000;
}

#app_composer-container {
  left: 832px;
  top: 96px;
}

#app_git-container {
  top: 20%;
  left: 40%;
}

#app_node_js-container {
  left: 832px;
  top: 96px;
}

#app_nodes-container {
  width: 550px;
  height: 450px;
  top: 60px;
  left: 30%;
}

#app_npmjs-container,
#app_packagist-container,
#app_phpclasses-container {
  top: 5%;
  left: 50%;
  transform: translateX(-50%);
  height: 600px;
  text-align: center;
  padding: 10px;
}

#app_php-container {
  width: 669px;
  height: 567px;
  top: 60px;
  left: 30%;
}

#app_directory-container,
#app_project-container {
  left: 832px;
  top: 96px;
  height: auto;
}

#app_timesheet-container {
  height: 575px;
  top: calc(50% - 350px);
  left: calc(50% - 400px);
  padding: 10px;
}

#app_browser-container {
  top: 5%;
  left: 50%;
  transform: translateX(-50%);
  height: 100vh;
  text-align: center;
  padding: 10px;
}

#app_github-container,
#app_whiteboard-container {
  top: 5%;
  left: 50%;
  transform: translateX(-50%);
  text-align: center;
  padding: 10px;
}

#app_github-container {
  height: 600px;
}

#app_whiteboard-container {
  height: 500px;
}

#app_console-container {
  position: fixed;
  display: block;
  bottom: 50px;
  left: 50%;
  /* transform: translateX(-50%); */
  /* height: 45px; */
  background-color: #FFA6A6;
  color: white;
  text-align: center;
}

#app_backup-container {
  top: 40%;
  left: 40%;
  width: 425px;
  text-align: center;
  padding: 10px;
}

#app_pong-container {
  top: 5%;
  left: 50%;
  transform: translateX(-50%);
  height: 400px;
  text-align: center;
  padding: 10px;
}

/* Selected state */
.selected {
  display: block;
  resize: both;
  overflow: hidden;
}

/* UI elements */
#ui_errors,
#ui_medication_log,
#ui_ace_editor {
  width: 100%;
  height: calc(100% - 80px);
  position: absolute;
}

#errors,
#medication_log,
#ace-editor {
  margin: 0;
  position: relative;
  overflow: auto;
  white-space: pre-wrap;
}

#responseConsole {
  position: relative;
  display: block;
  margin: 0 auto;
  background-color: #D0D0D0;
  color: black;
  cursor: pointer;
  height: 235px;
}

/* Tables */
.containerTbl {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

table {
  border-collapse: collapse;
}

td, th {
  border: black solid 1px;
  padding: 8px;
}

/* Form elements */
input {
  color: black;
}

input[type="radio"].green-radio,
input[type="radio"].red-radio {
  appearance: none;
  -webkit-appearance: none;
  margin: 0 5px 0 0;
  width: 1em;
  height: 1em;
  border: 2px solid white;
  border-radius: 50%;
  display: inline-block;
  vertical-align: middle;
  cursor: pointer;
}

input[type="radio"].green-radio {
  background-color: lightgreen;
  border-color: green;
}

input[type="radio"].green-radio:checked {
  border-color: green;
}

input[type="radio"].red-radio {
  background-color: red;
  border-color: red;
}

input[type="radio"].red-radio:checked {
  border-color: darkred;
}

/* Splitter */
.splitter {
  width: 100%;
  height: 250px;
  position: relative;
}

#separator {
  cursor: row-resize;
  background-color: #aaa;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='3'%3E%3Cpath d='M2 30 L0 M5 0 V30 M8 0 V30' fill='none' stroke='black' /%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: center;
  width: 100%;
  height: 15px;
  z-index: 2;
  transform: translateZ(0);
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

#first {
  background-color: #dde;
  width: 100%;
  height: 100%;
  min-width: 10px;
  z-index: 1;
  transform: translateZ(0);
}

#second {
  background-color: #eee;
  width: 100%;
  height: 100%;
  min-width: 10px;
  z-index: 1;
  transform: translateZ(0);
  overflow-x: hidden;
}

/* Process list */
.process-list {
  position: absolute;
  background-color: #FFA6A6;
  left: -150px;
  width: 150px;
  height: 117px;
  border: 2px solid #000;
  overflow: hidden;
  display: block;
}

.process {
  border: #000 solid 1px;
  color: #fff;
  padding: 10px;
  margin: 5px 0;
  display: block;
  position: relative;
  white-space: nowrap;
  width: fit-content;
  clear: both;
  overflow: hidden;
}

@keyframes scroll {
  0% { transform: translateX(15%); }
  100% { transform: translateX(-75%); }
}

.scrolling {
  animation: scroll 10s linear infinite;
}

/* Vertical slider */
.vert-slider-container {
  position: relative;
  float: right;
  width: 10px;
  height: 100px;
  background-color: #f0f0f0;
}

.vert-slider {
  position: absolute;
  top: 45px;
  left: -70px;
  width: 100px;
  height: 10px;
  background: #4CAF50;
  transform: rotate(90deg);
  cursor: pointer;
}

.vert-slider::-webkit-slider-thumb {
  appearance: none;
  width: 20px;
  height: 20px;
  background: #fff;
  border: #4CAF50 solid 2px;
  border-radius: 50%;
  cursor: pointer;
  margin-top: -10px;
  margin-left: -10px;
}

.vert-slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  background: #fff;
  border: #4CAF50 solid 2px;
  border-radius: 50%;
  cursor: pointer;
}

/* Directory grid */
.directory-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  padding: 10px;
}

.directory-entry {
  flex: 1 0 10%;
  max-width: 115px;
  text-align: center;
  border: 0;
  padding: 4px;
  box-sizing: border-box;
}

.directory-entry img {
  display: block;
  margin: 0 auto 4px auto;
  width: 40px;
  height: auto;
}

/* Node visualization */
#visualization {
  margin-top: -60px;
  width: 100%;
  height: calc(100% - 260px);
  position: absolute;
  z-index: 1;
}

.node circle {
  fill: #999;
  stroke: #fff;
  stroke-width: 3px;
}

.link {
  fill: none;
  stroke: #555;
  stroke-width: 1.5px;
}

.link.green {
  stroke: green;
}

text {
  font: 10px sans-serif;
}

/* Dropdown */
.dropbtn {
  background-color: #3498DB;
  color: white;
  padding: 2px 7px;
  font-size: 14px;
  border: none;
  cursor: pointer;
}

.dropbtn:hover, .dropbtn:focus {
  background-color: #2980B9;
}

.dropdown {
  position: relative;
  display: inline-block;
  float: right;
  z-index: 1;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: #f1f1f1;
  min-width: 160px;
  margin: -100px;
  overflow: auto;
}

.dropdown-content a {
  color: black;
  padding: 8px 12px;
  text-decoration: none;
  display: block;
}

.dropdown a:hover {
  background-color: #ddd;
}

.show {
  display: block;
}

/* Buttons */
.btn {
  @apply rounded-md px-2 py-1 text-center font-medium text-slate-900 shadow-sm ring-1 ring-slate-900/10 hover:bg-slate-50;
}

/* Links */
a {
  text-decoration: none;
  color: #000;
}

a:hover {
  text-decoration: underline;
}

.sidebar a {
  text-decoration: none;
  color: #000;
}

.sidebar a:hover {
  text-decoration: underline;
}

.sidebar ul {
  list-style-type: none;
  padding: 0;
}

.sidebar li {
  padding: 5px 10px;
}

.sidebar li:hover {
  background-color: #ddd;
}

/* Images */
img {
  display: inline;
}

/* Utility classes */
.hidden {
  display: none;
}
    <?php /*
$ui_style = '';
$app_style = '';

foreach (UI_APPS as $key => $app) {
 //dd($key, false);
 if ($app['type'] === 'ui') {
   $ui_style .= $app['style'];
 } elseif ($app['type'] === 'app') {
   $app_style .= $app['style'];
 }
}

// Print UI scripts first
echo $ui_style;

// Then app scripts
echo $app_style; ?>
*/ ?>    
  </style>

</head>


<body>


  <div class="container1">
    <div class="sidebar"><p style="color: white; background-color: #0078D7;">Github: <a href="https://github.com/barrydit/codeinsync" target="_blank" rel="noopener noreferrer" style="background-color: white;">barrydit/codeinsync</a></p>
  <pre style="position: absolute; bottom: 0; z-index: 999;">
  <?= $errors['NPM-WEBPACK']; ?>
  </pre>
  </div>
    <div class="top-panel">
      <div>
        <a href="#"><img src="resources/images/phpclasses_icon.png" alt="Logo"
            style="width: 31px; height: auto; margin: 0 5px;"
            onclick="document.getElementById('app_phpclasses-container').style.display='block'; return false;"></a>
        <a href="#"><img src="resources/images/composer_icon.png" alt="Logo"
            style="width: 31px; height: auto; margin: 0 5px;"
            onclick="document.getElementById('app_composer-container').style.display='block'; return false;"></a>
        <a href="#"><img src="resources/images/packagist_icon.png" alt="Logo"
            style="width: 31px; height: auto; margin: 0 5px;"
            onclick="document.getElementById('app_packagist-container').style.display='block'; return false;"></a>
        <a href="#"><img src="resources/images/git_icon.fw.png" width="32" height="32"
            onclick="document.getElementById('app_git-container').style.display='block'; return false;"></a>
        <a href="#"><img src="resources/images/node_js.gif" alt="Logo" style="width: 83px; height: auto; margin: 0 5px;"
            onclick="document.getElementById('app_node_js-container').style.display='block'; return false;"></a>
        <a href="#"><img src="resources/images/npm_icon.png" alt="Logo"
            style="width: 31px; height: auto; margin: 10px 5px;"
            onclick="document.getElementById('app_npmjs-container').style.display='block'; return false;"></a>
        <a href="#"><img src="resources/images/console_icon.png" alt="Logo"
            style="width: 31px; height: auto; margin: 0 5px;"
            onclick="isFixed = true; show_console(); return false;"></a>
      </div>

      <div style="position: absolute; top: 5px; right: 270px;">
        <img src="resources/images/php_icon.png" alt="Logo" style="width: 31px; height: auto; margin: 0 0;"
          onclick="document.getElementById('app_php-container').style.display='block'; return false;"> PHP
        <button style="border: 1px solid black; border-radius: 5px;">Clock-In</button>&nbsp;
        <button style="border: 1px solid black; border-radius: 5px;">Github</button>&nbsp;
        <input type="submit" value="Test" style="border: 1px solid black; border-radius: 5px;">
        <div class="" style="position: relative; display: inline-block; top: 0; right: 2px;">
          <img src="resources/images/calendar_icon.png" width="41" height="41"
            onclick="document.getElementById('app_calendar-container').style.display='block'; return false;"
            style="cursor: pointer; margin: -6px 5px;">
        </div>
        <!-- button>Git</button>
        <button>GitHub</button>
        <button>GitLab</button>
        <button>Bitbucket</button>
        <button>GitKraken</button>
        <button>GitExtensions</button>
        <button>GitUp</button>
        <button>GitX</button>
        <button>GitAhead</button -->
      </div>
      <div style="position: absolute; width: auto; top: 5px; right: 0; border: 1px dashed green; height: 20px;">
        <div id="clockTime" style="padding-right: 35px; background-color: rgba(255, 255, 255, 0.5); text-align: left;">
          <a href="#" onclick="document.getElementById('app_calendar-container').style.display='block';"><i
              style="background-color: white; color: #0078D7;"> Mon, 04:52:49 PM May 12 2025 </i></a>
        </div>
        <div style="display: inline-block; width: auto; background-color: #FFF;">
          <div id="idleTime" style="display: inline; margin: 10px 5px;"><i style="color: blue;">[Idled] for: 0h 0m 11s
            </i></div>
          <div>
            <div id="stats"><!-- Idle: [0]&nbsp;&nbsp;<span style="color: black;">00:00:00</span --></div>
          </div>
        </div>
        <div style="display: inline-block; width: auto; ">
          <img id="ts-status-light" style="padding-bottom: 10px; cursor: pointer;"
            src="resources/images/timesheet-light-Y.gif" width="80" height="30">
        </div>

      </div>

    </div>
    <div class="bottom-panel">Bottom Panel

      <div style="position: absolute; left: 15px; top: 0; text-align: center;" class="text-sm"><a href="#!"
          onclick="document.getElementById('app_notes-container').style.display='block'; return false;"><img
            style="text-align: center;" src="resources/images/notes.png"></a><br><a
          href="?app=ace_editor&amp;path=&amp;file=app.notes.php"
          style="text-align: center; background-color: #0078D7;">Notes</a></div>


      <div style="position: absolute; left: 100px; top: 0; text-align: center;" class="text-sm"><a href="#!"
          onclick="document.getElementById('app_ace_editor-container').style.display='block'; return false;"><img
            style="text-align: center;" src="resources/images/ace_editor.png"></a><br>
        <a href="?app=ace_editor&amp;path=&amp;file=app.notes.php"
          style="text-align: center; background-color: #0078D7;">Ace Editor</a>
      </div>

    </div>
    <div class="free-space" id="free-space" style="overflow: auto; padding: 0 5px 0 5px;">
      <div id="info-plus"
        style="display: none; position: absolute; top: 0; left: 0; background-color: white; border: 1px solid black; padding: 10px;">
        <p>Info about the directory structure and usage.</p>
        <button onclick="document.getElementById('info').style.display = 'none';">Close</button>
      </div>
      <?= UI_APPS['directory']['body'] ?? ''; ?>
      <!-- div class="free-space">Main Workspace</div -->
    </div>
  </div>

  <?php
  $ui_body = '';
  $app_body = '';

  foreach (UI_APPS as $key => $app) {
    //dd($key, false);
    if ($key == 'directory')
      continue;
    if ($app['type'] === 'ui') {
      $ui_body .= $app['body'];
    } elseif ($app['type'] === 'app') {
      $app_body .= $app['body'];
    }
  }

  // Print UI scripts first
  echo $ui_body;

  // Then app scripts
  echo $app_body; ?>


  <script
    src="<?= APP_IS_ONLINE && check_http_status('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : APP_BASE['resources'] . 'js/jquery/' . 'jquery-3.7.1.min.js' ?>"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
  <!-- You need to include jQueryUI for the extended easing options. -->
  <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
  <?php
  if (!is_file($path = APP_PATH . APP_BASE['resources'] . 'js/jquery-ui/' . 'jquery-ui-1.12.1.js') || ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path))))) / 86400)) <= 0) {
    if (!realpath($pathdir = dirname($path)))
      if (!mkdir($pathdir, 0755, true))
        $errors['DOCS'] = "$pathdir does not exist";

    $url = 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($js = curl_exec($handle)))
      file_put_contents($path, $js) or $errors['JS-JQUERY-UI'] = "$url returned empty.";
  } ?>

  <script
    src="<?= APP_IS_ONLINE && check_http_status('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js') ? 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js' : APP_BASE['resources'] . 'js/jquery-ui/' . 'jquery-ui-1.12.1.js' ?>"></script>


  <!-- Uncaught ReferenceError: jQuery is not defined -->

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

  // APP_IS_ONLINE && check_http_status('https://cdn.tailwindcss.com' ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/tailwindcss.js')?
//!is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/') or mkdir($path, 0755, true);
  
  if (!is_file($path = APP_PATH . APP_BASE['resources'] . 'js/requirejs/require.js') || ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path))))) / 86400)) <= 0) {
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
      var editors = {}; // relative path => [ace instances]

      /*document.addEventListener("DOMContentLoaded", function () {
       document.querySelectorAll('.editor').forEach(function (el) {
         const id = el.id;
         const relPath = el.dataset.filename;

         const editor = ace.edit(id);
         editor.setTheme("ace/theme/monokai");
         editor.session.setMode("ace/mode/php");
         editor.setAutoScrollEditorIntoView(true);
         editor.setShowPrintMargin(false);
         editor.setOptions({
           enableBasicAutocompletion: true,
           enableLiveAutocompletion: true,
           enableSnippets: true
         });

         if (!editors[relPath]) editors[relPath] = [];
         editors[relPath].push(editor);
       });
     });*/

      function openNewEditorWindow(filepath, content = '') {
        const baseId = 'editor_' + btoa(filepath).replace(/[^a-zA-Z0-9]/g, '_');
        if (document.getElementById(baseId)) {
          alert('File is already open.');
          return;
        }

        const template = document.getElementById('editor_template');
        const clone = template.firstElementChild.cloneNode(true);
        const editorDiv = clone.querySelector('.editor');
        const label = clone.querySelector('.filename-label');

        clone.id = baseId;
        editorDiv.id = baseId + '_ace';
        clone.dataset.filename = filepath;
        label.textContent = filepath;

        document.body.appendChild(clone);

        // Init ACE Editor
        const editor = ace.edit(editorDiv.id);
        editor.setTheme("ace/theme/dracula");
        editor.session.setMode("ace/mode/php");
        editor.setValue(content, -1);
        editor.setOptions({
          enableBasicAutocompletion: true,
          enableLiveAutocompletion: true,
          enableSnippets: true,
          showPrintMargin: false
        });

        // Save reference
        if (!window.editors) window.editors = {};
        window.editors[filepath] = editor;

        // Make draggable
        makeDraggable2(clone);
      }

      /*
      var globalEditor;
      var editor1, editor2;

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
  */
    </script>

    <?php
  }
  unset($path);
  if (!is_file($path = APP_PATH . APP_BASE['resources'] . 'js/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js') || ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path))))) / 86400)) <= 0) {
    !is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/jquery-ui-touch-punch') or @mkdir($path, 0755, true); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>

  <?php } ?>

  <script>
    const sidebar = document.querySelector('.sidebar');
    const topPanel = document.querySelector('.top-panel');
    const bottomPanel = document.querySelector('.bottom-panel');
    //const freeSpace = document.querySelector('.free-space');

    const updatePanelWidths = () => {
      const sidebarWidth = sidebar.offsetWidth + 'px';
      [topPanel, bottomPanel].forEach(panel => {
        panel.style.left = sidebarWidth;
        panel.style.width = `calc(100% - ${sidebarWidth})`;
      });
    };

    // Observe sidebar resizing in real-time
    const resizeObserver = new ResizeObserver(updatePanelWidths);
    resizeObserver.observe(sidebar);


    let isDragging = false;
    let activeWindow = null;

    function makeDraggable(windowId) {
      const windowElement = document.getElementById(windowId);
      const headerElement = windowElement.querySelector('.ui-widget-header');
      let offsetX, offsetY;

      headerElement.addEventListener('mousedown', function (event) {
        if (!isDragging) {
          // Bring the clicked window to the front
          document.body.appendChild(windowElement);
          offsetX = event.clientX - windowElement.getBoundingClientRect().left;
          offsetY = event.clientY - windowElement.getBoundingClientRect().top;
          isDragging = true;
          activeWindow = windowElement;
        }
      });

      document.addEventListener('mousemove', function (event) {
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

      document.addEventListener('mouseup', function () {
        if (activeWindow === windowElement) {
          isDragging = false;
          activeWindow = null;
        }
      });
    }

    function makeDraggable2(el) {
      const header = el.querySelector('.window-header');
      let offsetX = 0, offsetY = 0, startX = 0, startY = 0;

      header.onmousedown = function (e) {
        e.preventDefault();
        startX = e.clientX;
        startY = e.clientY;
        document.onmouseup = () => document.onmousemove = document.onmouseup = null;
        document.onmousemove = function (e) {
          offsetX = e.clientX - startX;
          offsetY = e.clientY - startY;
          el.style.top = (el.offsetTop + offsetY) + "px";
          el.style.left = (el.offsetLeft + offsetX) + "px";
          startX = e.clientX;
          startY = e.clientY;
        };
      };
    }

    //makeDraggable('app_medication_log-container');
    makeDraggable('app_notes-container');
    //makeDraggable('app_calendar-container');
    //makeDraggable('app_errors-container');
    makeDraggable('app_git-container');
    makeDraggable('app_ace_editor-container');
    makeDraggable('app_composer-container');
    makeDraggable('app_project-container');
    makeDraggable('app_node_js-container');
    makeDraggable('app_npmjs-container');
    makeDraggable('app_php-container');
    makeDraggable('app_phpclasses-container');
    makeDraggable('app_nodes-container');
    makeDraggable('app_timesheet-container');
    //makeDraggable('console-settings');

    $(document).ready(function () {

      <?= !APP_NO_INTERNET_CONNECTION && APP_IS_ONLINE ? '' : 'alert(\'The internet is not connected.\');' ?>

      if ($("#app_directory-container").css('display') == 'none') {
        <?php
        //if (!empty(APP_URL['query']) || isset($_GET['debug']) || (defined(APP_DEBUG) && APP_DEBUG)) { ?>
        //$('#app_directory-container').css('display', 'block');
        $('#app_directory-container').css('visibility', 'visible');
        $('#app_directory-container').css('opacity', '1');
        $('#app_directory-container').css('height', 'auto');
        //$('#app_directory-container').css('width', 'auto');
        $('#app_directory-container').css('overflow', 'auto');
        $('#app_directory-container').css('position', 'absolute');
        $('#app_directory-container').css('top', '0px');
        $('#app_directory-container').css('left', '0px');
        //$('#app_directory-container').css('z-index', '1000');
        $('#app_directory-container').css('background-color', 'white');
        $('#app_directory-container').css('border', '1px solid black');
        //$('#app_directory-container').css('padding', '10px');
        $('#app_directory-container').css('box-shadow', '0 0 10px rgba(0, 0, 0, 0.5)');
        $('#app_directory-container').css('border-radius', '5px');

        /**/
        $('#app_directory-container').slideDown("slow", function () {
          // Animation complete.
        });
        <?php //} ?>
      }
    });


    <?php
    $ui_script = '';
    $app_script = '';

    foreach (UI_APPS as $key => $app) {
      //dd($key, false);
      if ($key == 'whiteboard')
        continue;
      if ($app['type'] === 'ui') {
        $ui_script .= $app['script'];
      } elseif ($app['type'] === 'app') {
        $app_script .= $app['script'];
      }
    }

    // Print UI scripts first
    echo $ui_script;

    // Then app scripts
    echo $app_script; ?>
  </script>
</body>

</html>