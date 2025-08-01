<?php

file_exists(dirname(__DIR__, 1) . '/bootstrap/bootstrap.php') && require_once dirname(__DIR__, 1) . '/bootstrap/bootstrap.php';

$files = get_required_files();
$baseDir = APP_PATH;
$organizedFiles = [];
$directoriesToScan = [];

// Collect directories from the list of files
foreach ($files as $key => $file) {
  $relativePath = str_replace($baseDir, '', $file);
  $directory = dirname($relativePath);
  //var_dump($directory);
  if (preg_match('/^vendor(\/.*$|)/', $directory)) {
    unset($files[$key]);
    continue;
  }
  if (!in_array($directory, $directoriesToScan)) {
    $directoriesToScan[] = $directory;
  }
  // Add the relative path to the organizedFiles array if it is a .php file and not already present
  if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles)) {
    $organizedFiles[] = $relativePath;
  }
}

// Add non-recursive scanning for the root baseDir for *.php files
$rootPhpFiles = glob("{$baseDir}{*.php}", GLOB_BRACE);
foreach ($rootPhpFiles as $file) {
  if (is_file($file)) {
    $relativePath = str_replace($baseDir, '', $file);
    // Add the relative path to the array if it is a .php file and not already present
    if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles)) {
      if ($relativePath == 'composer-setup.php')
        continue;
      $organizedFiles[] = $relativePath;
    }
  }
}

// Scan the specified directories
scanDirectories($directoriesToScan, $baseDir, $organizedFiles);

// Display the results
$sortedArray = customSort($organizedFiles);

$total_include_files = count($files);
$total_include_lines = 0;
$total_filesize = 0;
$total_files = count($sortedArray);
$total_lines = 0;

//dd($files);

foreach ($sortedArray as $key => $path) {
  $sortedArray[$key] = ['path' => APP_PATH . $path, 'filesize' => filesize(APP_PATH . $path), 'filemtime' => filemtime(APP_PATH . $path)];
  //$total_files++;
  in_array($sortedArray[$key]['path'], $files) and $total_include_lines += count(file($sortedArray[$key]['path'])); //or $total_include_files++;
  $total_filesize += $sortedArray[$key]['filesize'];
  $total_lines += count(file($sortedArray[$key]['path']));
}

session_start();
$isDev = $_SESSION['mode'] ?? 'unset';
unset($_SESSION['mode']) ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
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
      display: none;
      position: fixed;
      width: 500px;
      height: 300px;
      top: 100px;
      left: 100px;
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

  <div class="container">
    <div class="developer-group" id="devGroup">
      <div class="sidebar" id="sidebar" style="border-right: 2px solid #999;">
        <p style="color: white; background-color: #0078D7;">Github: <a href="https://github.com/barrydit/codeinsync"
            target="_blank" rel="noopener noreferrer" style="background-color: white;">barrydit/codeinsync</a></p>
        <p>Sidebar</p>
        <div style="position: relative; background-color: #FFF; width: 300px;">
          <span>Loading Time: <?= round(microtime(true) - APP_START, 3); ?>s</span><br />
          <span>OS: <?= PHP_OS; ?></span><br />
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
              style="width: 31px; height: auto; margin: 0 5px;"
              onclick="document.getElementById('app_composer-container').style.display='block'; return false;"></a>
          <a href="#"><img src="resources/images/packagist_icon.png" alt="Logo"
              style="width: 31px; height: auto; margin: 0 5px;"
              onclick="document.getElementById('app_packagist-container').style.display='block'; return false;"></a>
          <a href="#"><img src="resources/images/git_icon.fw.png" width="32" height="32"
              onclick="openApp('tools/code/git'); document.getElementById('app_git-container').style.display='block'; return false;"></a>
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
              style="margin-top: -5px;">Tools</span></a>
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
    <div class="free-space" id="free-space">Free Space</div>
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
    <div id="app_git-container" class="app-container">
    </div>
    <div id="app_nodes-container" class="app-container">
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



  <script>

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
    const viewProject = document.getElementById('viewProject');
    const viewToggle = document.getElementById('viewToggle');
    const sidebar = document.getElementById('sidebar');
    const topPanel = document.getElementById('top-panel');
    const bottomPanel = document.getElementById('bottom-panel');
    const freeSpace = document.getElementById('free-space');
    const devGroup = document.getElementById('devGroup');
    const clientView = document.getElementById('clientView');
    const appGit = document.getElementById('app_git-container');
    const appNodes = document.getElementById('app_nodes-container');

    const sessionIsDev = <?php echo ($isDev === 'developer') ? 'true' : 'false'; ?>;

    if (!sessionIsDev) {
      activateClientMode();
    } else {
      activateDeveloperMode();
      viewToggle.checked = true;
    }

    function chooseMode(mode) {
      fetch('?setmode=' + mode);
      document.getElementById('modeForm').style.display = 'none';
      if (mode === 'developer') {
        activateDeveloperMode();
        viewToggle.checked = true;
      } else {
        activateClientMode();
        viewToggle.checked = false;
      }
    }

    function toggleMode() {
      if (viewToggle.checked) {
        activateDeveloperMode();
      } else {
        activateClientMode();
      }
    }

    function activateClientMode() {
      sidebar.classList.add('exit-sidebar');
      topPanel.classList.add('exit-top');
      bottomPanel.classList.add('exit-bottom');
      freeSpace.classList.add('exit-free');
      clientView.style.display = 'block';
      setTimeout(() => {
        devGroup.style.display = 'none';
      }, 600);
    }

    function activateDeveloperMode() {
      devGroup.style.display = 'block';
      clientView.style.display = 'none';

      // Force reflow before removing classes
      sidebar.offsetHeight;

      sidebar.classList.remove('exit-sidebar');
      topPanel.classList.remove('exit-top');
      bottomPanel.classList.remove('exit-bottom');
      freeSpace.classList.remove('exit-free');
    }

    function openApp(appPath) {
      // Extract slug (last segment of path, safe for DOM IDs)
      const slug = appPath.split('/').pop();

      console.log(`Opening app: ${appPath} with slug: ${slug}`);

      fetch(`/dispatcher.php?app=${appPath}`)
        .then(res => res.json())
        .then(app => {
          const container = document.getElementById(`app_${slug}-container`);
          // Inject styles
          if (app.style && !document.getElementById(`style-${slug}`)) {
            const styleEl = document.createElement(" style"); styleEl.id = `style-${slug}`; styleEl.innerHTML = app.style;
            document.head.appendChild(styleEl);
          } // Inject scripts
          if (app.script && !document.getElementById(`script-${slug}`)) {
            const scriptEl = document.createElement("script");
            scriptEl.id = `script-${slug}`; scriptEl.innerHTML = app.script; document.body.appendChild(scriptEl);
          } // Show body
          if (container) { container.innerHTML = app.body; container.style.display = "block"; }
        }).catch(err => {
          console.error(err);
          document.getElementById(`app_${slug}-container`).textContent =
            'Error loading app.';
        });
    }

    function closeApp(appPath) {
      const slug = appPath.split('/').pop();

      document.getElementById(`style-${slug}`)?.remove();
      document.getElementById(`script-${slug}`)?.remove();

      const container = document.getElementById(`app_${slug}-container`);
      if (container) {
        container.innerHTML = "";
        container.style.display = "none";
      }
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