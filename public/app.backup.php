<?php

if (__FILE__ == get_required_files()[0]) //die(getcwd());
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '') require_once $path;
  else die(var_dump("$path path was not found. file=config.php"));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_GET['app']) && $_GET['app'] == 'backup')
    if (isset($_POST['path']) && isset($_GET['filename']) && $path = realpath($_POST['path'] . $_GET['filename']))
      file_put_contents($path, $_POST['editor']);

}

const APP_BACKUP_PATH = '/var/www/backup/'; // symlink(/mnt/d)

ob_start();
?>

/* Styles for the absolute div */
#app_backup-container {
position: absolute;
display: none;
top: 5%;
//bottom: 60px;
right: 0;
background-color: rgba(255, 255, 255, 0.9);
color: black;
text-align: center;
padding: 10px;
z-index: 1;
}

input {
  color: black;
}


.splitter {
  width: 100%;
  height: 250px;  
  position: relative;
}

#separator {
  cursor: row-resize;
  background-color: #aaa;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='3'><path d='M2 30 0 M5 0 v30 M8 0 v30' fill='none' stroke='black'/></svg>");
  background-repeat: no-repeat;
  background-position: center;
  width: 100%;
  height: 15px;
  z-index: 2;
  transform: translateZ(0);
  /* Prevent the browser's built-in drag from interfering */
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

#ace-editor {
  margin: 0;
  position: relative;
  resize: both;
  overflow: auto;
  white-space: pre-wrap;
  //width: 100%;
  //height: 100%;
}


<?php $appBackup['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

  <div id="app_backup-container" class="<?= (APP_SELF == __FILE__ || isset($_GET['client']) && $_GET['client'] || (isset($_GET['app']) && $_GET['app'] == 'backup')  ? 'selected' : '') ?>" style="position: absolute; <?= (isset($_GET['client']) && $_GET['client'] ? 'display: block;':'' ) ?> border: 1px solid #000; right: 0; top: 0; z-index: 1;">

<?php
if (isset($_GET['client']) && $_GET['client']) {

$_GET['client'] = urldecode($_GET['client']);

  $dirs = array_filter(glob(dirname(__DIR__) . '/../../clientele/' . $_GET['client'] . '/*'), 'is_dir');

//if (count($dirs) >= 1) $_GET['domain'] = basename($dirs[array_key_first($dirs)]);

?>

<form action method="GET">
<!-- input type="hidden" name="domain" value="" / -->
<div style="display: inline-block;">
<label for="client">Client:</label>
<select id="client" name="client" onchange="this.form.submit();">
  <option value="">---</option>
<?php
  $dirs = array_filter(glob(dirname(__DIR__) . '/../../clientele/*'), 'is_dir');

  foreach ($dirs as $dir) { ?>
  <option <?= (isset($_GET['client']) && $_GET['client'] == basename($dir) ? 'selected' : '') ?>><?= basename($dir); ?></option>
<?php } ?>
</select>
</div>

<?php if (!empty($_GET['client'])) { 
    $dirs = array_filter(glob(dirname(__DIR__) . '/../../clientele/' . $_GET['client'] . '/*'), 'is_dir'); ?>
<div style="display: inline-block;">
<label for="domain">Domain:</label>
<select id="domain" name="domain" onchange="this.form.submit();">
  <option value="">---</option>
<?php foreach ($dirs as $dir) { ?>
  <option <?= (isset($_GET['domain']) && $_GET['domain'] == basename($dir) ? 'selected' : '') ?>><?= basename($dir); ?></option>
<?php } ?>
</select>
</div>
</form>
<div style="clear: both;"></div>
<?php
 if (!empty($_GET['domain'])) { 

/*
die(var_dump($dirs));
(!str_ends_with(APP_PATH, '/clientele/' . $_GET['client'] . '/' . $_GET['domain'] . '/') ?: APP_PATH)
*/

?>
<form action method="POST">
<?= (!isset($_GET['client'])?: '<input type="hidden" name="client" value="' . $_GET['client'] . '" />') ?>
<?= (!isset($_GET['domain'])?: '<input type="hidden" name="domain" value="' . $_GET['domain'] . '" />') ?>
<div style="padding-left: 50px; text-align: right;">
<?php
  $dirs = array_filter(glob(dirname(__DIR__) . '/../../clientele/' . $_GET['client'] . '/' . $_GET['domain'] . '/*'), 'is_dir');
  foreach ($dirs as $key => $dir) {
    $dir_key = $key;
  ?>

  <table style="width: 75%; font-family: 'Avenir', Verdana, sans-serif; font-size: 10px; color: #000; text-align: left; margin-left: 10px;">
    <tr>
      <td><input id="folder-<?= $key; ?>" type="checkbox" name="folder[<?= basename($dir) ?>]" value="<?= basename($dir); ?>" checked="" <?= (in_array(basename($dir), ['database', 'vendor', 'var']) ? 'disabled' : '')  ?> />&nbsp;<label for="folder-<?= $key; ?>" style="color: blue; text-transform: uppercase;" title="<?= $dir; ?>"><?= basename($dir); ?>/</label></td>
    </tr>

<?php
  $dir = basename($dir);
//dd($dir);
  $files = glob(dirname(__DIR__) . '/../../clientele/' . $_GET['client'] . '/' . $_GET['domain'] . '/' . $dir . '/*.php');
  foreach ($files as $key => $file) {
    $b_file = APP_BACKUP_PATH . 'clientele/' . $_GET['client'] . '/' . $_GET['domain'] . '/' . $dir . '/'. basename($file);
    $hash = md5_file($file);
 ?>
    <tr>
      <td >
        <fieldset name="">
          <input id="files-<?= $key; ?>" type="checkbox" name="folder[<?= $dir ?>][]" value="<?= basename($file) ?>" <?= (!is_file($b_file) ? 'checked=""' : (md5_file($b_file) == $hash ?: 'checked=""')) ?> />&nbsp;<a href="<?= '?client=' . $_GET['client'] . '&domain=' . $_GET['domain']  . '&file=' . $dir . '/' . basename($file) ?>" style="<?=(md5_file($b_file) == $hash ?: 'color: red;')?>" title="<?= (!is_file($b_file) ? '&lt;NO FILE FOUND&gt;' : md5_file($file)) ?>"><?= basename($file) ?></a>
        </fieldset>
      </td>
      <td style="color: <?= (!is_file($b_file)? 'black' : (md5_file($b_file) == $hash ? 'green' : 'red')) ?>;"><?= (!is_file($b_file)? '&lt;NO FILE FOUND&gt;' : '' /*md5_file($file)*/) ?></td>
    </tr>
<?php } ?>
  </table>
<?php } ?>
</div>
<div style="display: inline-block;">
<input type="checkbox" id="backup" name="backup" value="<?= APP_BACKUP_PATH ?>" checked="" />&nbsp;<label for="backup"><?= APP_BACKUP_PATH ?> (Backup)</label>
</div>
<div style="display: inline-block; float: right;">
<button type="submit" class="btn">Commit</button>
</div>
</form>

<?php } } else { ?>

</form>

<?php } ?>

<?php } ?>

  </div>

<?php $appBackup['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
// A function is used for dragging and moving
function dragElement(element, direction) {
  var md; // remember mouse down info
  const first = document.getElementById("first");
  const second = document.getElementById("second");

  element.onmousedown = onMouseDown;

  function onMouseDown(e) {
    //console.log("mouse down: " + e.clientX);
    md = {
      e,
      offsetLeft: element.offsetLeft,
      offsetTop: element.offsetTop,
      firstHeight: first.offsetHeight,
      secondHeight: second.offsetHeight
    };

    document.onmousemove = onMouseMove;
    document.onmouseup = () => {
      //console.log("mouse up");
      document.onmousemove = document.onmouseup = null;
    }
  }

  function onMouseMove(e) {
    //console.log("mouse move: " + e.clientX);
    var delta = {
      x: e.clientX - md.e.clientX,
      y: e.clientY - md.e.clientY
    };

    if (direction === "V") // Vertical
    {        
      // Prevent negative-sized elements
      delta.x = Math.min(Math.max(delta.y, -md.firstHeight),
        md.secondHeight);

      element.style.top = md.offsetTop + delta.x + "px";
      first.style.height = (md.firstHeight + delta.x) + "px";
      second.style.height = (md.secondHeight - delta.x) + "px";
    }
  }
}

dragElement(document.getElementById("separator"), "V");

<?php $appBackup['script'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<?php $appBackup['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'backup' && APP_DEBUG)
  Shutdown::setEnabled(false)->setShutdownMessage(function() {
      return '<!DOCTYPE html>'; // -wow */
    })->shutdown(); // die();ob_start(); ?>