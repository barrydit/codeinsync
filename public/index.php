<?php

!defined('APP_START') and define('APP_START', $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));

const IS_CLIENT = true;
const IS_DEVELOPER = false;

// minimal web entry
if (is_file(dirname(__DIR__, 1) . '/bootstrap/bootstrap.php'))
  require_once __DIR__ . '/../bootstrap/bootstrap.php';

// Fast-path: if routing params present, hint minimal boot
if (!defined('APP_MODE')) {
  define('APP_MODE', 'web');
}

//dd(get_required_files());

/**
 * File Analysis Summary for PHP Project
 * Counts PHP files, lines of code, and included files excluding vendor/.
 */

$baseDir = APP_PATH;
$includedFiles = get_required_files();
$trackedFiles = [];
$directoriesToScan = [];

// Step 1: Track included files excluding vendor/
foreach ($includedFiles as $key => $file) {
  $relativePath = str_replace($baseDir, '', $file);
  $directory = dirname($relativePath);

  if (str_starts_with($directory, 'vendor')) {
    unset($includedFiles[$key]);
    continue;
  }

  if (!in_array($directory, $directoriesToScan)) {
    $directoriesToScan[] = $directory;
  }

  if (pathinfo($relativePath, PATHINFO_EXTENSION) === 'php' && !in_array($relativePath, $trackedFiles)) {
    $trackedFiles[] = $relativePath;
  }
}

// Step 2: Scan non-recursive root for *.php excluding known installer scripts
foreach (glob("{$baseDir}*.php") as $file) {
  $relativePath = str_replace($baseDir, '', $file);
  if ($relativePath === 'composer-setup.php')
    continue;
  if ($relativePath === 'install.php')
    continue;
  if (pathinfo($relativePath, PATHINFO_EXTENSION) === 'php' && !in_array($relativePath, $trackedFiles)) {
    $trackedFiles[] = $relativePath;
  }
}

// Step 3: Scan project directories recursively
scanDirectories($directoriesToScan, $baseDir, $trackedFiles);

// Step 4: Sort files
$sortedFiles = customSort($trackedFiles);

// Step 5: Count stats
$total_include_files = count($includedFiles);
$total_include_lines = 0;
$total_filesize = 0;
$total_files = count($sortedFiles);
$total_lines = 0;

foreach ($sortedFiles as $index => $path) {
  $fullPath = str_starts_with($path, DIRECTORY_SEPARATOR)
    ? $path  // Already absolute, don't prepend
    : "$baseDir$path";

  $fileSize = filesize($fullPath);

  if (file($fullPath) === false) // Handle the error
    throw new Exception("Failed to read the file: $fullPath");

  $lineCount = count(file($fullPath));

  $sortedFiles[$index] = [
    'path' => $fullPath,
    'filesize' => $fileSize,
    'filemtime' => filemtime($fullPath)
  ];

  if (in_array($fullPath, $includedFiles)) {
    $total_include_lines += $lineCount;
  }

  $total_filesize += $fileSize;
  $total_lines += $lineCount;
}

session_start();
$isDev = $_SESSION['mode'] ?? 'unset';
unset($_SESSION['mode']); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <link rel="icon" href="<?= APP_URL ?>/favicon.ico">
  <title>CodeInSync - Skeleton</title>
  <style>
    html,
    body {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow: hidden;
      font-family: sans-serif;
    }

    .container {
      position: relative;
      width: 100%;
      height: 100vh;
      overflow: hidden;
    }

    .developer-group {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      transition: all 0.6s ease-in-out;
    }

    .client-view {
      display: none;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
    }

    .sidebar,
    .top-panel,
    .bottom-panel,
    .free-space {
      position: absolute;
      transition: transform 0.6s ease-in-out;
    }

    .sidebar {
      top: 0;
      left: 0;
      width: 200px;
      height: 100%;
      background-color: #ccc;
      resize: horizontal;
      overflow: auto;
      min-width: 100px;
      z-index: 50;
    }

    a {
      text-decoration: none;
      color: black;
    }

    a:hover {
      text-decoration: underline;
    }

    .top-panel {
      top: 0;
      left: 200px;
      height: 44px;
      width: calc(100% - 200px);
      background: #eee;
      border-bottom: 1px solid #999;
      z-index: 10;
    }

    .bottom-panel {
      bottom: 0;
      left: 200px;
      height: 100px;
      width: calc(100% - 200px);
      background: #444;
      color: white;
      border-top: 1px solid #999;
      z-index: 10;
    }

    .free-space {
      top: 44px;
      bottom: 100px;
      left: 200px;
      width: calc(100% - 200px);
      background: #f5f5f5;
      overflow: auto;
      z-index: 1;
    }

    .app-container {
      /* display: none; */
      position: fixed;
      /* width: 500px;
      height: 400px;
      top: 100px;
      left: 100px; */
      z-index: 100;
      /* padding: 10px; 
      background: #fff;
      border: 1px solid #000;*/
    }

    .toggle-switch {
      position: fixed;
      bottom: 10px;
      right: 10px;
      z-index: 9999;
      background: rgba(255, 255, 255, 0.7);
      padding: 5px 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    iframe {
      width: 100%;
      height: 100%;
      border: none;
    }

    .hidden {
      display: none;
    }

    .exit-sidebar {
      transform: translateX(-100%);
    }

    .exit-top {
      transform: translateY(-100%);
    }

    .exit-bottom {
      transform: translateY(100%);
    }

    .exit-free {
      transform: translateX(100%);
    }

    .form-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: 99999;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .form-box {
      background: white;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
    }

    /* Developer mode reverse-slide animations (returns to original position) */
    .sidebar:not(.exit-sidebar),
    .top-panel:not(.exit-top),
    .bottom-panel:not(.exit-bottom),
    .free-space:not(.exit-free) {
      transform: translate(0, 0);
    }

    /* All sliding panels default to original position */
    .sidebar,
    .top-panel,
    .bottom-panel,
    .free-space {
      transform: translate(0, 0);
      transition: transform 0.4s ease-in-out;
    }

    /* Exit transitions for client mode */
    .exit-sidebar {
      transform: translateX(-100%);
    }

    .exit-top {
      transform: translateY(-100%);
    }

    .exit-bottom {
      transform: translateY(100%);
    }

    .exit-free {
      transform: translateX(100%);
    }

    /* Toggle switch styles */

    .toggle-switch {
      position: absolute;
      bottom: 0px;
      right: 0px;
      display: flex;
      align-items: center;
      float: right;
    }

    .toggle-switch input {
      display: none;
    }

    .slider {
      position: relative;
      width: 60px;
      height: 30px;
      background: #888;
      border-radius: 30px;
      cursor: pointer;
      transition: 0.3s;
    }

    .slider::before {
      content: '';
      position: absolute;
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background: white;
      top: 2px;
      left: 2px;
      transition: 0.3s;
    }

    input:checked+.slider::before {
      transform: translateX(30px);
    }

    input:checked+.slider {
      background: #2196F3;
    }
  </style>
</head>

<body>
  <div class="form-overlay" id="modeForm" style="<?php echo $isDev === 'unset' ? '' : 'display:none;'; ?>">
    <div class="form-box">
      <p>Select interface mode:</p>
      <button onclick="chooseMode('developer')">Developer Mode</button>
      <button onclick="chooseMode('client')">Client Mode</button>
    </div>
  </div>

  <div id="container" class="container">
    <div class="developer-group" id="devGroup">
      <div class="sidebar" id="sidebar" style="border-right: 2px solid #999;">
        <p style="color: white; background-color: #0078D7;">Github: <a href="https://github.com/barrydit/codeinsync"
            target="_blank" rel="noopener noreferrer" style="background-color: white;">barrydit/codeinsync</a></p>
        <p>Sidebar</p>
        <div style="position: relative; background-color: #FFF; width: 300px;">
          <span>Logout: <a href="/?authprobe" rel="nofollow">Logout</a></span><br />
          <span>Loading Time: <?= round(microtime(true) - APP_START, 3); ?>s</span><br />
          <span>OS: <?= PHP_OS; ?></span><br />
          <span>PHP: <?= PHP_VERSION; ?></span><br />
          <span>Debug: <?= APP_DEBUG ? 'true' : 'false'; ?></span><br />
          <span>Context: <?= APP_CONTEXT ?? ''; ?></span><br />
          <span>Mode: <?= APP_MODE ?? ''; ?></span><br />
          <span>Domain: <?= APP_DOMAIN ?? ''; ?></span><br />
          <span>IP Address: <?= $_SERVER['REMOTE_ADDR'] ?? ''; ?></span><br />
          <span>App Path: <?= APP_PATH; ?></span><br />
          <span>Memory: <em><b
                style="color: green;"><?= formatSizeUnits(memory_get_usage()) . '</b> @ <b>' . formatSizeUnits(convertToBytes(ini_get('memory_limit'))); ?></b></em></span><br />
          <span>Source (code): <em
              style="font-size: 13px;"><?= '[<b>' . formatSizeUnits($total_filesize) . '</b>] <b style="color: red;">' . $total_filesize - 1000000 . '</b>' ?>
            </em></span>
          <div style="position: relative; display: block;">
            <div style="position: absolute; display: block; width: 165px; text-align: right;">
              <?= ' [(<a style="font-weight: bolder; color: green;" href="#" onclick="openApp(\'visual/nodes\');">' . $total_include_files . ' loaded</a>) <b>' . $total_files . '</b> files] <br /> [<b style="color: green;">' . $total_include_lines . '</b> @ <b>' . $total_lines . '</b> lines]'; ?>
            </div>
          </div><br /><br />
        </div>
      </div>
      <div class="top-panel" id="top-panel">
        <div style="position: relative;">
          <a href="#"><img src="resources/images/phpclasses_icon.png" alt="Logo"
              style="width: 31px; height: auto; margin: 0 5px;"
              onclick="document.getElementById('app_phpclasses-container').style.display='block'; return false;"></a>
          <a href="#"><img src="resources/images/composer_icon.png" alt="Logo"
              style="width: 31px; height: auto; margin: 0 5px;" onclick="openApp('tools/registry/composer');"></a>
          <a href="#"><img src="resources/images/packagist_icon.png" alt="Logo"
              style="width: 31px; height: auto; margin: 0 5px;"
              onclick="document.getElementById('app_packagist-container').style.display='block'; return false;"></a>
          <a href="#"><img src="resources/images/git_icon.fw.png" width="32" height="32"
              onclick="openApp('tools/code/git');"></a>
          <a href="#"><img src="resources/images/node_js.gif" alt="Logo"
              style="width: 83px; height: auto; margin: 0 5px;"
              onclick="document.getElementById('app_node_js-container').style.display='block'; return false;"></a>
          <a href="#"><img src="resources/images/npm_icon.png" alt="Logo"
              style="width: 31px; height: auto; margin: 10px 5px;"
              onclick="document.getElementById('app_npmjs-container').style.display='block'; return false;"></a>
          <a href="#"><img src="resources/images/console_icon.png" alt="Logo"
              style="width: 31px; height: auto; margin: 0 5px;"
              onclick="isFixed = true; show_console(); return false;"></a>

          <a href="#" style="margin: 5px 0 0 0;"
            onclick="document.getElementById('app_tools-container').style.display='block';">
            <img src="resources/images/apps_icon.gif" style="margin: -5px 0 0 0;" width="20" height="20"> <span
              style="margin-top: -5px;">Tools</a></span>
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
        <div
          style="position: absolute; width: auto; top: 5px; right: 0; border: 1px dashed green; height: 20px; z-index: 99;">
          <div id="clockTime"
            style="padding-right: 10px; background-color: rgba(255, 255, 255, 0.5); text-align: left;">
            <a href="#" onclick="document.getElementById('app_calendar-container').style.display='block';"><i
                style="background-color: white; color: #0078D7;"> <?= date('D, h:i:s A M d Y T') ?? '' ?>
              </i></a>
          </div>
          <div style="display: inline-block; width: auto; background-color: #FFF;">
            <div id="idleTime" style="display: inline; float: left; margin: 10px 5px;"><i style="color: blue;">[Idled]
                for: 0h 0m 0s
              </i></div>
            <div>
              <div id="stats"><!-- Idle: [0]&nbsp;&nbsp;<span style="color: black;">00:00:00</span --></div>
            </div>
          </div>
          <div style="position: relative; top: 0; display: inline-block; width: auto; ">
            <img id="ts-status-light" style="padding-bottom: 10px; cursor: pointer;"
              src="resources/images/timesheet-light-Y.gif" width="80" height="30">
          </div>

        </div>
      </div>
    </div>
    <div class="bottom-panel" id="bottom-panel">Bottom Panel</div>
    <div class="free-space" id="free-space">
      <div id="app_devtools_directory-container" class="app-fixed" data-draggable="false" data-app="devtools/directory">
      </div>
    </div>
    <div id="app_tools-container"
      style="position: absolute; top: 5%; left: 50%; transform: translate(-50%, -50%); width: 800px; height: 500px; background-color: rgba(255, 255, 255, 0.9); margin-top: 350px; z-index: 5; display: none;">
      <div style="position: fixed; margin: -5px 45px; text-align: center; z-index: 5;" class="text-sm"><a href="#!"
          onclick="document.getElementById('app_tools-container').style.display='none'; return false;"><img
            style="text-align: center; position: fixed;" height="25" width="25"
            src="resources/images/close-red.png"></a><br></div>
      <div
        style="position: absolute; overflow-x: scroll; overflow-y: hidden; height: 100%; width: 100%; padding-top: 25px; border: 1px solid #000; ">
        <div style="position: absolute; margin: 10px 75px; text-align: center;" class="text-sm"><a href="#!"
            onclick="isFixed = true; show_console(); return false;"><img style="text-align: center;"
              src="resources/images/cli.png"></a><br><a href="?app=ace_editor&amp;path=&amp;file=app.console.php"
            style="text-align: center;">(CLI)</a></div>
        <!-- 
                    <a href="javascript:window.open('print.html', 'newwindow', 'width=300,height=250')">Print</a>
                    onclick="window.open('app.whiteboard.php', 'newwindow', 'width=300,height=250'); return false;"
                    
                    https://stackoverflow.com/questions/12939928/make-a-link-open-a-new-window-not-tab
                     -->
        <div style="position: absolute; margin: 10px 165px; text-align: center;" class="text-sm"><a href="#"
            target="_blank" onclick="toggleIframeUrl('app.whiteboard.php'); return false;"><img
              style="text-align: center;" src="resources/images/whiteboard.png"></a><br><a
            href="?app=ace_editor&amp;path=&amp;file=app.whiteboard.php" style="text-align: center;">Whiteboard</a>
        </div>
        <div style="position: absolute; margin: 10px 260px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_notes-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/notes.png"></a><br><a
            href="?app=ace_editor&amp;path=&amp;file=app.notes.php" style="text-align: center;">Notes</a></div>
        <div style="position: absolute; margin: 10px 350px; text-align: center;" class="text-sm">
          <a href="#!"
            onclick="document.getElementById('app_project-container').style.display='block'; document.getElementById('toggle-debug').checked = false; toggleSwitch(document.getElementById('toggle-debug')); return false;">
            <img style="text-align: center;" src="resources/images/project.png"></a><br><a
            href="?app=ace_editor&amp;path=&amp;file=app.project.php"><span
              style="text-align: center;">Project</span></a>
        </div>
        <div style="position: absolute; margin: 10px 0 0 450px ; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_errors-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/debug.png"><br><span
              style="text-align: center;">Debug</span></a></div>
        <div style="position: absolute; margin: 10px 0 0 540px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_profile-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/user.png"><br><span
              style="text-align: center;">Profile</span></a></div>
        <div style="position: absolute; margin: 10px 0 0 630px; text-align: center;" class="text-sm"><a href="#!"
            onclick="toggleIframeUrl('app.browser.php'); return false;"><img style="text-align: center;"
              src="resources/images/browser.png"><br><span style="text-align: center;">Browser</span></a></div>
        <div style="position: absolute; margin: 110px 75px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_tools-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/apps.png"><br><span
              style="text-align: center;">Apps.</span></a></div>
        <div style="position: absolute; margin: 110px 170px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_calendar-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/calendar.png"><br><span
              style="text-align: center;">Calendar</span></a></div>
        <div
          style="position: absolute; left: 50%; transform: translate(-50%, -50%); margin: 180px 0 0 80px; text-align: center;">
          <form action="#!" method="GET">


            <input type="hidden" name="project" value="">
            <div style="margin: 0 auto;">
              <div id="clockTime"></div>
            </div>
            <div class="toggle-switch">
              <div class="left"
                style="background-color: rgba(255, 255, 255, 0.8); text-shadow: 2px 2px; display: inline;"> Client
              </div>
              <input type="checkbox" id="viewProject" onchange="toggleSwitch(this); this.form.submit();">
              <div class="slider" style="position: relative;">
                <div class="slider round"></div>
              </div>
              <div class="right"
                style="background-color: rgba(255, 255, 255, 0.8); text-shadow: 2px 2px;  display: inline;"> Project
              </div>
            </div>
          </form>
        </div>
        <div style=" position: absolute; margin: 110px 0 0 540px; text-align: center;" class="text-sm"><a href="#!"
            onclick="toggleIframeUrl('pong.php'); return false;"><img style="text-align: center;"
              src="resources/images/pong.png"><br><span style="text-align: center;">Pong</span></a>
        </div>
        <div style="position: absolute; margin: 110px 0 0 630px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/regexp.png"><br><span
              style="text-align: center;">RegExp</span></a></div>
        <div style="position: absolute; margin: 210px 75px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/chatgpt.png"><br><span
              style="text-align: center;">ChatGPT</span></a></div>
        <div style="position: absolute; margin: 210px 160px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/stackoverflow.png"><br><span
              style="text-align: center;">Stackoverflow</span></a></div>
        <div style="position: absolute; margin: 210px 260px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/validatejs.png"><br><span
              style="text-align: center;">ValidateJS</span></a></div>
        <!-- https://validator.w3.org/#validate_by_input // -->
        <div style="position: absolute; margin: 210px 340px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/w3c.png"><br><span style="text-align: center;">W3C
              Validator</span></a></div>
        <!-- https://tailwindcss.com/docs/ // -->
        <div style="position: absolute; margin: 210px 0 0 445px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/tailwindcss.png"><br><span
              style="text-align: center;">TailwindCSS<br>Docs</span></a></div>
        <!-- https://www.php.net/docs.php // -->
        <div style="position: absolute; margin: 210px 0 0 540px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/php.png"><br><span style="text-align: center;">PHP
              Docs</span></a></div>
        <!-- https://dev.mysql.com/doc/ // -->
        <div class="text-sm" style="position: absolute; margin: 210px 0 0 615px; text-align: center;"><a href="#!"
            onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/mysql.png"><br><span style="text-align: center;">MySQL
              Docs</span></a></div>
        <div
          style="position: absolute; top: 340px; left: 65px; width: 80%; margin: 0 auto; height: 15px; border-bottom: 1px solid black; text-align: center; z-index: 0;">
          <span style="font-size: 20px; background-color: #F3F5F6; padding: 0 20px; z-index: 1;"> USER APPS.
          </span>
        </div>
        <div style="position: absolute; margin: 360px 75px; text-align: center;" class="text-sm"><a href="#!"
            onclick="document.getElementById('app_install-container').style.display='block'; return false;"><span
              style="text-align: center;">New App.</span><br><img style="text-align: center;"
              src="resources/images/install.png"></a></div>
        <div style="position: absolute; margin: 360px 170px; text-align: center;" class="text-sm">
          <a href="?app=ace_editor&amp;path=&amp;file=app.user-app.php"><span style="text-align: center;">App
              #1</span></a><br>
          <a href="#!"
            onclick="document.getElementById('app_browser-container').style.display='block'; return false;"><img
              style="text-align: center;" src="resources/images/php-app.png"></a>
          <div style="height: 75px;"></div>
        </div>
      </div>
    </div>
    <div id="app_tools_code_git-container" class="app-container" data-draggable="true" data-app="tools/code/git">
    </div>
    <div id="app_visual_nodes-container" class="app-container" data-drag-handle="true" data-app="visual/nodes">
    </div>
    <div id="app_tools_registry_composer-container" class="app-container" data-draggable="true"
      data-app="tools/registry/composer">
    </div>
  </div>
  <div class="client-view" id="clientView">
    <iframe src="test.php"></iframe>
  </div>
  <div class="toggle-switch">
    <label>
      <input type="checkbox" id="viewToggle" onchange="toggleMode()">
      <div class="slider"></div>
    </label>
    <span style="margin-left: 0.5em; color: #2196F3;">Developer</span>
  </div>


  <script
    src="<?= APP_IS_ONLINE && check_http_status('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : app_base('resources', null, 'rel') . 'js/jquery/' . 'jquery-3.7.1.min.js' ?>"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
  <!-- You need to include jQueryUI for the extended easing options. -->
  <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
  <?php
  if (!is_file($path = app_base('resources', null, 'abs') . 'js/jquery-ui/' . 'jquery-ui-1.12.1.js') || ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path))))) / 86400)) <= 0) {
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
    src="<?= APP_IS_ONLINE && check_http_status('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js') ? 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js' : app_base('resources', null, 'rel') . 'js/jquery-ui/' . 'jquery-ui-1.12.1.js' ?>"></script>


  <script>
    /* ────────────────────────────────────────────────────────────────────────────
       Small UI helper (your dropdown)
    ──────────────────────────────────────────────────────────────────────────── */
    function myFunction() {
      document.getElementById("myDropdown")?.classList.toggle("show");
    }

    /* ────────────────────────────────────────────────────────────────────────────
       Window stack + focus + draggable (jQuery UI or vanilla fallback)
    ──────────────────────────────────────────────────────────────────────────── */
    (function () {
      const Stack = { zTop: null, active: null };

      function initZTop() {
        if (Stack.zTop != null) return;
        const zs = Array.from(document.querySelectorAll('.app-container'))
          .map(el => parseInt(getComputedStyle(el).zIndex || '0', 10) || 0);
        Stack.zTop = Math.max(1000, ...zs);
      }

      function bringToFront(winEl) {
        initZTop();
        if (Stack.active && Stack.active !== winEl) {
          Stack.active.classList.remove('is-active');
        }
        winEl.style.zIndex = ++Stack.zTop;
        winEl.classList.add('is-active');
        Stack.active = winEl;
      }

      // Vanilla draggable fallback (pointer events)
      function destroyDraggable(windowId) {
        const el = document.getElementById(windowId);
        if (!el || !el._drag) return;
        const { handle, onDown, onMove, onUp } = el._drag;
        handle.removeEventListener('pointerdown', onDown);
        document.removeEventListener('pointermove', onMove);
        document.removeEventListener('pointerup', onUp);
        document.removeEventListener('pointercancel', onUp);
        handle.style.touchAction = '';
        handle.style.cursor = '';
        el._drag = null;
      }

      function makeDraggable(windowId, opts = {}) {
        const el = document.getElementById(windowId);
        if (!el) return console.warn('makeDraggable: not found', windowId);

        // Re-init safe
        if (el._drag) destroyDraggable(windowId);

        const selector = opts.handle || '[data-drag-handle], .window-header';
        const handle = el.matches(selector) ? el : el.querySelector(selector) || el;

        handle.style.touchAction = 'none'; // prevent scroll while dragging
        handle.style.cursor = 'move';

        const style = getComputedStyle(el);
        if (style.position === 'static') el.style.position = 'absolute';

        let dragging = false, offsetX = 0, offsetY = 0;

        function clampPosition(left, top) {
          if (opts.constrain === 'parent') {
            const p = el.parentElement.getBoundingClientRect();
            const r = el.getBoundingClientRect();
            left = Math.min(Math.max(left, p.left), p.right - r.width);
            top = Math.min(Math.max(top, p.top), p.bottom - r.height);
          } else if (opts.constrain === 'viewport') {
            const r = el.getBoundingClientRect();
            left = Math.min(Math.max(left, 0), window.innerWidth - r.width);
            top = Math.min(Math.max(top, 0), window.innerHeight - r.height);
          }
          return { left, top };
        }

        const onMove = (e) => {
          if (!dragging) return;
          let left = e.clientX - offsetX;
          let top = e.clientY - offsetY;
          ({ left, top } = clampPosition(left, top));
          el.style.left = `${left}px`;
          el.style.top = `${top}px`;
        };

        const onUp = () => {
          dragging = false;
          document.body.style.userSelect = '';
        };

        const onDown = (e) => {
          bringToFront(el);
          const rect = el.getBoundingClientRect();
          offsetX = e.clientX - rect.left;
          offsetY = e.clientY - rect.top;
          dragging = true;
          document.body.style.userSelect = 'none';
          if (handle.setPointerCapture) {
            try { handle.setPointerCapture(e.pointerId); } catch { }
          }
          if (!el.style.left) el.style.left = rect.left + 'px';
          if (!el.style.top) el.style.top = rect.top + 'px';
        };

        handle.addEventListener('pointerdown', onDown);
        document.addEventListener('pointermove', onMove);
        document.addEventListener('pointerup', onUp);
        document.addEventListener('pointercancel', onUp);

        el._drag = { handle, onDown, onMove, onUp };
        el.addEventListener('mousedown', () => bringToFront(el));
      }

      function installWindowFocus(rootSelector = '#container') {
        const root = document.querySelector(rootSelector);
        if (!root || root._focusInstalled) return;
        root._focusInstalled = true;

        root.addEventListener('pointerdown', (e) => {
          const handle = e.target.closest('[data-drag-handle], .window-header, .app-container');
          if (!handle) return;
          const win = handle.closest('.app-container, [data-app-window]');
          if (!win) return;
          bringToFront(win);
        }, { capture: true });
      }

      // Expose
      window.AppWindows = {
        bringToFront,
        makeDraggable,
        destroyDraggable,
        installWindowFocus
      };
    })();

    /* ────────────────────────────────────────────────────────────────────────────
       App containers + error UI + asset cleanup + robust fetch guards
    ──────────────────────────────────────────────────────────────────────────── */
    (function () {
      // Default mount points per app
      const APP_MOUNTS = {
        'devtools/directory': '#free-space',
      };

      // Mark any container IDs here to disable dragging on them
      const NON_DRAGGABLE = new Set([
        'app_devtools_directory-container'
      ]);

      function appPathToContainerId(appPath) {
        return `app_${appPath.replace(/[^\w]+/g, '_')}-container`;
      }

      function shouldBeDraggable(appPathOrContainerId) {
        const id = appPathOrContainerId.startsWith('app_')
          ? appPathOrContainerId
          : appPathToContainerId(appPathOrContainerId);
        return !NON_DRAGGABLE.has(id);
      }

      function ensureAppContainer(containerId, mountSelector) {
        const mount = document.querySelector(mountSelector)
          || document.querySelector('#container')
          || document.body;

        let el = document.getElementById(containerId);
        if (!el) {
          el = document.createElement('div');
          el.id = containerId;
          el.className = 'app-container';
          el.innerHTML = `
        <div class="window-header" data-drag-handle>
          <span class="title"></span>
          <button class="close" type="button" aria-label="Close">×</button>
        </div>
        <div class="window-body"></div>`;
          mount.appendChild(el);
          el.querySelector('.close')?.addEventListener('click', () => closeApp(el.dataset.appPath || ''));
        }
        return el;
      }

      function showAppError(containerEl, title, message, details = '') {
        const body = containerEl.querySelector('.window-body') || containerEl;
        body.innerHTML = `
      <div class="app-error" style="padding:12px;border:1px solid #fbb;background:#fff8f8;">
        <strong>${title}</strong>
        <div>${message}</div>
        ${details ? `<pre style="white-space:pre-wrap;margin-top:8px;">${details}</pre>` : ''}
      </div>`;
      }

      function removeAppAssets(appPath) {
        document.querySelector(`[data-app-style="${appPath}"]`)?.remove();
        document.querySelector(`[data-app-script="${appPath}"]`)?.remove();
      }

      function contentTypeIncludes(resp, needle) {
        const ct = resp.headers.get('content-type') || '';
        return ct.toLowerCase().includes(needle);
      }

      async function openApp(app, opts = {}) {
        const params = new URLSearchParams(opts.params || {});
        params.set('json', '1'); // force JSON branch on your endpoint

        const containerId = appPathToContainerId(app);
        const mountSelector = opts.mount || APP_MOUNTS[app] || '#container';
        const el = ensureAppContainer(containerId, mountSelector);
        el.dataset.appPath = app;

        // Focus stack
        window.AppWindows?.bringToFront(el);

        // ----- 1) style + body (JSON) -----
        let data;
        try {
          const r1 = await fetch(`?app=${encodeURIComponent(app)}&${params.toString()}`, {
            headers: { Accept: 'application/json' },
            cache: 'no-store',
            credentials: 'same-origin'
          });

          const raw = await r1.text();

          if (!r1.ok) {
            showAppError(el, 'Load failed', `HTTP ${r1.status} while loading app "${app}".`, raw.slice(0, 4000));
            console.error('[openApp] HTTP error:', app, r1.status, raw.slice(0, 200));
            return;
          }

          // Parse FIRST. JSON may contain HTML snippets in strings.
          try {
            data = JSON.parse(raw);
          } catch (e) {
            // Only now do a tight "full HTML page" check
            const looksLikeFullHTML = /^\s*</.test(raw) && /<(?:!doctype|html|head|body)\b/i.test(raw);
            if (looksLikeFullHTML) {
              showAppError(
                el,
                'Unexpected HTML',
                `Expected JSON for "${app}" but received a full HTML page (routing missing "?app="?).`,
                raw.slice(0, 1000)
              );
            } else {
              showAppError(el, 'Invalid JSON', `Could not parse JSON for "${app}".`, raw.slice(0, 1000));
            }
            console.error('[openApp] JSON parse error:', app, e, raw.slice(0, 200));
            return;
          }
        } catch (e) {
          showAppError(el, 'Network error', `Failed to fetch JSON for "${app}".`, String(e));
          console.error('[openApp] Network error:', app, e);
          return;
        }

        // Inject CSS once
        if (data.style && !document.querySelector(`[data-app-style="${app}"]`)) {
          const styleEl = document.createElement('style');
          styleEl.dataset.appStyle = app;
          styleEl.textContent = data.style;
          document.head.appendChild(styleEl);
        }

        // Mount body
        const body = el.querySelector('.window-body') || el;
        body.innerHTML = data.body || '';

        // ===== STEP 4: script (module) fetch with tight "full HTML page" sniff =====
        try {
          // remove any previous inline module for this app
          document.querySelector(`script[data-app-script="${app}"]`)?.remove();

          const scriptParams = new URLSearchParams(opts.params || {});
          scriptParams.set('part', 'script');

          const r2 = await fetch(`?app=${encodeURIComponent(app)}&${scriptParams.toString()}`, {
            headers: { Accept: 'text/javascript, application/javascript, application/x-javascript' },
            cache: 'no-store',
            credentials: 'same-origin'
          });

          const code = await r2.text();

          if (!r2.ok) {
            showAppError(el, 'Script load failed', `HTTP ${r2.status} while loading script for "${app}".`, code.slice(0, 4000));
            console.error('[openApp] Script HTTP error:', app, r2.status, code.slice(0, 200));
            return;
          }

          // Only consider it "HTML page" if it *starts* like one
          const t = code.trimStart();
          const looksLikeFullHTML = t.startsWith('<') && /^(?:<!doctype|<html\b|<head\b|<body\b)/i.test(t);
          const ctLooksJS = contentTypeIncludes?.(r2, 'javascript') || contentTypeIncludes?.(r2, 'ecmascript');

          if (!ctLooksJS && looksLikeFullHTML) {
            showAppError(
              el,
              'Unexpected script response',
              `Expected JavaScript for "${app}" but got a full HTML page (check "part=script" routing).`,
              t.slice(0, 1000)
            );
            console.error('[openApp] Expected JS, got full HTML page:', app, t.slice(0, 200));
            return;
          }
          
          if (code && code.trim()) {
            const looksLikeHtml = /^\s*<(!doctype|html|head|body)\b/i.test(code);
            if (looksLikeHtml) {
              console.warn('Loaded HTML instead of JS for', app, code.slice(0, 200));
            }

            const mod = document.createElement('script');
            mod.type = 'module';
            mod.dataset.appScript = app;
            mod.textContent = `${code}\n//# sourceURL=/${app}.module.js`;
            document.body.appendChild(mod);

            console.log('Injected module', { app, bytes: code.length, preview: code.slice(0, 200) });
          }

        } catch (e) {
          showAppError(el, 'Script fetch error', `Failed to fetch script for "${app}".`, String(e));
          console.error('[openApp] Script network/error:', app, e);
          return;
        }

        // ----- 5) (Re)enable dragging for the container -----
        try {
          if (window.jQuery) {
            const $el = jQuery(el);
            if ($el.data('uiDraggable')) {
              try { $el.draggable('destroy'); } catch { }
            }
            if (shouldBeDraggable(containerId)) {
              $el.addClass('ui-widget ui-widget-content');
              $el.find('[data-drag-handle]').addClass('ui-widget-header ui-draggable-handle');
              try {
                $el.draggable({
                  handle: '[data-drag-handle]',
                  containment: 'body'
                });
              } catch { }
            }
          } else {
            if (el.dataset.draggableInit === '1' && typeof window.AppWindows?.destroyDraggable === 'function') {
              window.AppWindows.destroyDraggable(containerId);
            }
            if (shouldBeDraggable(containerId)) {
              window.AppWindows?.makeDraggable(containerId);
              el.dataset.draggableInit = '1';
            }
          }
        } catch (e) {
          console.warn('[openApp] draggable init warning:', e);
        }

        console.log('openApp mounted', { app, containerId, mountSelector });
      }

      /* If you don't already have this helper somewhere, add it once: */
      function contentTypeIncludes(resp, needle) {
        const ct = resp.headers.get('content-type') || '';
        return ct.toLowerCase().includes(needle);
      }

      function closeApp(appPath, { fullReset = false } = {}) {
        const id = appPathToContainerId(appPath);
        const el = document.getElementById(id);
        if (!el) return;
        if (fullReset) {
          try {
            if (window.jQuery) {
              const $el = jQuery(el);
              if ($el.data('uiDraggable')) {
                try { $el.draggable('destroy'); } catch { }
              }
            } else if (el.dataset.draggableInit === '1' && typeof window.AppWindows?.destroyDraggable === 'function') {
              window.AppWindows.destroyDraggable(id);
            }
          } catch { }
          el.remove();
          removeAppAssets(appPath);
        } else {
          el.hidden = true;
          el.setAttribute('aria-hidden', 'true');
        }
      }

      // Expose globally
      window.openApp = openApp;
      window.closeApp = closeApp;

      // Window focus handler
      document.addEventListener('DOMContentLoaded', () => {
        window.AppWindows?.installWindowFocus('#container');
      });
    })();

    /* ────────────────────────────────────────────────────────────────────────────
       Developer / Client Mode — toggle + initial boot
       Requires these IDs in DOM: #viewToggle, #sidebar, #top-panel, #bottom-panel,
       #free-space, #devGroup, #clientView (optional #viewProject, #app_git-container,
       #app_visual_nodes-container if you use them).
    ──────────────────────────────────────────────────────────────────────────── */
    (function () {
      // Elements (they can be missing; functions will no-op gracefully)
      const viewProject = document.getElementById('viewProject');
      const viewToggle = document.getElementById('viewToggle'); // checkbox or switch
      const sidebar = document.getElementById('sidebar');
      const topPanel = document.getElementById('top-panel');
      const bottomPanel = document.getElementById('bottom-panel');
      const freeSpace = document.getElementById('free-space');
      const devGroup = document.getElementById('devGroup');
      const clientView = document.getElementById('clientView');
      // Optional app windows you referenced; not used directly here but kept for parity
      const appGit = document.getElementById('app_git-container');
      const appNodes = document.getElementById('app_visual_nodes-container');

      const sessionIsDev =
        (typeof window.SESSION_IS_DEV !== 'undefined')
          ? !!window.SESSION_IS_DEV
          : (document.documentElement.dataset.sessionIsDev === 'true');

      // Public API: switch mode and persist
      function chooseMode(mode) {
        //try { await fetch('?setmode=' + encodeURIComponent(mode)); } catch { }
        if (mode === 'developer') {
          activateDeveloperMode();
          if (viewToggle) viewToggle.checked = true;
        } else {
          activateClientMode();
          if (viewToggle) viewToggle.checked = false;
        }
        // Hide optional mode picker UI if present
        const modeForm = document.getElementById('modeForm');
        if (modeForm) modeForm.style.display = 'none';
      }

      function toggleMode() {
        const dev = viewToggle ? !!viewToggle.checked : !isClientActive();
        if (dev) {
          activateDeveloperMode();
        } else {
          activateClientMode();
        }
      }

      function isClientActive() {
        // If devGroup hidden or sidebar has exit classes, we assume client mode
        return !!(clientView && clientView.style.display !== 'none');
      }

      function activateClientMode() {
        sidebar?.classList.add('exit-sidebar');
        topPanel?.classList.add('exit-top');
        bottomPanel?.classList.add('exit-bottom');
        freeSpace?.classList.add('exit-free');
        if (clientView) clientView.style.display = 'block';

        // Allow CSS animation time then hide devGroup
        setTimeout(() => {
          if (devGroup) devGroup.style.display = 'none';
        }, 600);
      }

      function activateDeveloperMode() {
        if (devGroup) devGroup.style.display = 'block';
        if (clientView) clientView.style.display = 'none';

        // Force reflow before removing classes
        if (sidebar) sidebar.offsetHeight;

        sidebar?.classList.remove('exit-sidebar');
        topPanel?.classList.remove('exit-top');
        bottomPanel?.classList.remove('exit-bottom');
        freeSpace?.classList.remove('exit-free');
      }

      // Wire UI events if present
      if (viewToggle) {
        viewToggle.addEventListener('change', toggleMode);
      }

      // Initial boot state
      document.addEventListener('DOMContentLoaded', () => {
        if (sessionIsDev) {
          activateDeveloperMode();
          if (viewToggle) viewToggle.checked = true;
        } else {
          activateClientMode();
          if (viewToggle) viewToggle.checked = false;
        }
      });

      // Expose globally (optional)
      window.chooseMode = chooseMode;
      window.toggleMode = toggleMode;
      window.activateDeveloperMode = activateDeveloperMode;
      window.activateClientMode = activateClientMode;
    })();
  </script>
  <script src="https://d3js.org/d3.v4.min.js"></script>

  <script>
    if (typeof jQuery === 'undefined') {
      console.error("jQuery is not loaded. Please check the script source.");
    } else {
      console.log("jQuery version:", jQuery.fn.jquery);
    }
  </script>

  <?php
  if (isset($_GET['setmode'])) {
    $_SESSION['mode'] = $_GET['setmode'] === 'developer' ? 'developer' : 'client';
    exit;
  }
  ?>

</body>

</html>