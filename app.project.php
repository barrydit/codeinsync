<?php

if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null))) require_once($path);
else die(var_dump($path . ' path was not found. file=config.php'));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_GET['app']) && $_GET['app'] == 'php')
    if (isset($_POST['path']) && isset($_GET['filename']) && $path = realpath($_POST['path'] . $_GET['filename']))
      file_put_contents($path, $_POST['editor']);

}

ob_start();
?>

/* Styles for the absolute div */
#app_project-container {
position: absolute;
display: none;
top: 5%;
//bottom: 60px;
left: 50%;
transform: translateX(-50%);
width: 800px;
height: 600px;
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


<?php $appProject['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
  <div id="app_project-container" class="<?= (APP_SELF == __FILE__ || (isset($_GET['app']) && $_GET['app'] == 'packagist') || (defined('COMPOSER') && !is_object(COMPOSER)) || count((array)COMPOSER) === 0 || version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? 'selected' : '') ?>" style="border: 1px solid #000;">
    <div class="header ui-widget-header">
      <div style="display: inline-block;">Project</div>
      <div style="display: inline; float: right; text-align: center; padding: -4px 5px 0px 0px;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_project-container').style.display='none';">X</a>]</div> 
    </div>
<?php /* https://stackoverflow.com/questions/70107579/how-can-i-split-the-resizable-panel-vertically-using-javascript */ ?>
  <div class="splitter">
    <div id="first">
      <iframe src="<?= basename('?project=show') ?>" style="height: 100%; width: 100%;"></iframe>
    </div>
<form action method="POST">
    <div id="separator" style="height: 25px; text-align: center;"><pre style="display: inline;">---Drag Bar---</pre>

    <div style="display: inline; float: right; z-index: 1000;">

<input type="submit" name="save-submit" value="&nbsp;&nbsp;Save&nbsp;&nbsp;" style="background-color: white; cursor: pointer;" onclick="document.getElementsByClassName('ace_text-input')[0].value = editor.getSession().getValue(); document.getElementsByClassName('ace_text-input')[0].name = 'editor';" />

</div>

    </div>
    
    <div id="second">
<input type="hidden" name="test" value="123" />

<textarea name="another_test">1234</textarea>

<?php $path = realpath(getcwd() . (isset($_GET['path']) ? DIRECTORY_SEPARATOR . $_GET['path'] : '')) . DIRECTORY_SEPARATOR; ?>
<div id="ace-editor" class="ace_editor" style="display: <?= (isset($_GET['file']) && isset($_GET['path']) && is_file($_GET['path'] . $_GET['file']) ? 'block': 'block')?>; width: 778px; height: 287px; z-index: 1;"><textarea name="contents" class="ace_text-input" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" style="opacity: 0; font-size: 1px; height: 1px; width: 1px; top: 28px; left: 86px;" wrap="off"><?= htmlsanitize(file_get_contents('project.php')) /*   'clientele/' . $_GET['client'] . '/' . $_GET['domain'] . '/' .  */ ?></textarea></div>
    </div>
  </form>
  </div>



      <!-- 
      <div style="display: inline-block; width: auto; padding-left: 10px;">
        <iframe src="<?= basename(__FILE__) ?>" style="height: 550px; width: 775px;"></iframe>
      </div>
<pre id="ace-editor" class="ace_editor"></pre> -->

  </div>
<!-- </div> -->

<?php $appProject['body'] = ob_get_contents();
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

<?php $appProject['script'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<?php $appProject['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'project' && APP_DEBUG)
  Shutdown::setEnabled(false)->setShutdownMessage(function() {
      return eval('?>' . file_get_contents('project.php')); // -wow */
    })->shutdown(); // die();ob_start(); ?>