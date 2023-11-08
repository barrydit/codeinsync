<?php

if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=config.php'));


ob_start(); ?>

/* Styles for the absolute div */
#app_notes-container {
position: absolute;
display: none;
top: 5%;
//bottom: 60px;
left: 50%;
transform: translateX(-50%);
width: auto;
height: 500px;
background-color: rgba(255, 255, 255, 0.9);
color: black;
text-align: center;
padding: 10px;
z-index: 1;
}

<?php $appNotes['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
  <div id="app_pong-container" class="<?= (APP_SELF == __FILE__ || (isset($_GET['app']) && $_GET['app'] == 'pong') || (defined('COMPOSER') && !is_object(COMPOSER)) || count((array)COMPOSER) === 0 || version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? 'selected' : '') ?>" style="border: 1px solid #000; overflow-x: scroll;">
    <div class="header ui-widget-header">
      <div style="display: inline-block;">Notes</div>
      <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_pong-container').style.display='none';">X</a>]</div> 
    </div>

      <div style="display: inline-block; width: auto;">
        <iframe src="<?= basename(__FILE__) ?>" style="height: 460px; width: 800px;"></iframe>
      </div>
      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->
  </div>
<!-- </div> -->

<?php $appNotes['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>



<?php $appNotes['script'] = ob_get_contents();
ob_end_clean();

//dd($_SERVER);
ob_start(); ?>


<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Pong 2.0</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="resources/css/app.css" />
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

<script src="https://cdn.tailwindcss.com"></script>

<style type="text/tailwindcss">
<?= /*$appWhiteboard['style'];*/ NULL; ?>
* { margin: 0; padding: 0; } /* to remove the top and left whitespace */

html, body { width: 100%; height: 100%; <?= ($_SERVER['SCRIPT_FILENAME'] == __FILE__ ? 'overflow:hidden;' : '') ?> } /* just to be sure these are full screen*/
</style>
</head>

<body class="bg-gray-300">
  <div class="h-screen flex justify-center items-center text-white">
    <div class="flex">
      <div class="w-40"></div>
      <div id="pong-panel" class="bg-black mx-4 px-8 py-4 rounded-md shadow-[5px_5px_20px_rgba(0,0,0,.4)]">
        <div class="text-right mb-2">
          Speed:
          <label class="mx-2">
            <input type="radio" name="speed" value="5">
            5x
          </label>
          <label class="mr-2">
            <input type="radio" name="speed" value="3">
            3x
          </label>
          <label>
            <input type="radio" name="speed" value="1" checked="checked">
            1x
          </label>
        </div>
        <div class="flex justify-between">
          <h3>Player: Wang</h3>
          <h3>Player: Barry</h3>
        </div>
        <canvas id="pong_game" width="500" height="170" class="border border-neutral-300">Canvas not supported</canvas>
        <div class="grid grid-cols-2 justify-items-center">
          <div id="score_1">0</div>
          <div id="score_2">0</div>
        </div>
      </div>
      <div id="right-panel" class="w-40 px-8 pt-12 bg-black rounded-md shadow-[5px_5px_20px_rgba(0,0,0,.4)]">
        <div id="xyposition" class="">&nbsp;</div>
        <div id="mousepad" class="h-[170px] w-full border border-white"></div>
      </div>
    </div>
  </div>
  <script type="module" src="resources/js/pong/index.js"></script>
  <script type="module" src="resources/js/bootstrap.js"></script>
</body>
</html>
<?php $appPackagist['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'php' && APP_DEBUG)
  die($appPackagist['html']);
