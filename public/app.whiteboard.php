<?php

/*

https://stackoverflow.com/questions/2368784/draw-on-html5-canvas-using-a-mouse

https://leimao.github.io/blog/HTML-Canvas-Mouse-Touch-Drawing/

*/


if (__FILE__ != get_required_files()[0]) {
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    if (is_file($path = realpath('../config/config.php'))) {
      require_once $path;
    }
  } elseif (is_file($path = realpath('config/config.php'))) {
    require_once $path;
  } else {
    die(var_dump("Path was not found. file=$path"));
  }
}

/*
if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../console_app.php') ? '../console_app.php' : (is_file('../config/console_app.php') ? '../config/console_app.php' : 'console_app.php'))
    : (is_file('console_app.php') ? 'console_app.php' : (is_file('public/console_app.php') ? 'public/console_app.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=console_app.php'));
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_GET['app']) && $_GET['app'] == 'php')
    if (isset($_POST['path']) && isset($_GET['filename']) && $path = realpath($_POST['path'] . $_GET['filename']))
      file_put_contents($path, $_POST['editor']);
      
  //dd($_POST);

//  if (isset($_GET['filename'])) {
//    file_put_contents($projectRoot.(!$_POST['path'] ? '' : DIRECTORY_SEPARATOR.$_POST['path']).DIRECTORY_SEPARATOR.$_POST['filename'], $_POST['editor']);
//  }

/*
    if (isset($_POST['cmd'])) {
      if ($_POST['cmd'] && $_POST['cmd'] != '') 
        if (preg_match('/^install/i', $_POST['cmd']))
          include('templates/' . preg_split("/^install (\s*+)/i", $_POST['cmd'])[1] . '.php');
        else if (preg_match('/^php(:?(.*))/i', $_POST['cmd'], $match))
          exec($_POST['cmd'], $output);
        else if (preg_match('/^composer(:?(.*))/i', $_POST['cmd'], $match)) {
        $output[] = 'env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC . ' ' . $match[1];
$proc=proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC . ' ' . $match[1],
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
$output[] = 'Composer: ' . (!isset($stdout) ? NULL : $stdout . (!isset($stderr) ? NULL : ' Error: ' . $stderr) . (!isset($exitCode) ? NULL : ' Exit Code: ' . $exitCode));
$output[] = $_POST['cmd'];

        } else if (preg_match('/^git(:?(.*))/i', $_POST['cmd'], $match)) {
        $output[] = 'sudo ' . GIT_EXEC . ' ' . $match[1];
$proc=proc_open('sudo ' . GIT_EXEC . ' ' . $match[1],
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
$output[] = 'Composer: ' . (!isset($stdout) ? NULL : $stdout . (!isset($stderr) ? NULL : ' Error: ' . $stderr) . (!isset($exitCode) ? NULL : ' Exit Code: ' . $exitCode));
$output[] = $_POST['cmd'];

        }

          //exec($_POST['cmd'], $output);
        else echo $_POST['cmd'] . "\n";
      //else var_dump(NULL); // eval('echo $repo->status();')
      if (!empty($output)) echo 'PHP >>> ' . join("\n... <<< ", $output) . "\n"; // var_dump($output);
      //else var_dump(get_class_methods($repo));
      exit();
    }
*/
}

ob_start(); ?>

/* Styles for the absolute div */
#app_whiteboard-container {
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

<?php $appWhiteboard['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
  <div id="app_whiteboard-container" class="<?= (APP_SELF == __FILE__ || (isset($_GET['app']) && $_GET['app'] == 'whiteboard') ? 'selected' : '') ?>" style="border: 1px solid #000;">
    <div class="header ui-widget-header">
      <div style="display: inline-block;">Whiteboard</div>
      <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_whiteboard-container').style.display='none';">X</a>]</div> 
    </div>

      <div style="display: inline-block; width: auto;">
        <iframe src="<?= (is_dir($path = APP_PATH . APP_BASE['public']) && getcwd() == realpath($path) ? APP_BASE['public'] : '' ) . basename(__FILE__) ?>" style="height: 460px; width: 800px;"></iframe>
      </div>

      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->

  </div>
<!-- </div> -->

<?php $appWhiteboard['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

//[[x,y],[x,y],[x,y],[x,y],[x,y],[x,y],[x,y]]

$(document).ready(function () {
  initialize();
});

var prevent = false;

var postArr = new Array();
// works out the X, Y position of the click INSIDE the canvas from the X, Y position on the page
function getPosition(mouseEvent, element) {
  var x, y;
  if (mouseEvent.pageX != undefined && mouseEvent.pageY != undefined) {
    x = mouseEvent.pageX;
    y = mouseEvent.pageY;
  } else {
    x = mouseEvent.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
    y = mouseEvent.clientY + document.body.scrollTop + document.documentElement.scrollTop;
  }
  x = x - element.offsetLeft;
    return { X: x, Y: y - element.offsetTop };
  }
   
  function requestData() {
    if ($('#stop:checked').length > 0) return;
 
    $.ajax({
      url: 'http://localhost/composer-src/?update',
      success: function(json) {
		
		console.log(json);
		  
        $.each(json, function(key,value) { });

        setTimeout(requestData, 2000); 

      }, 
      cache: false
    });
  }

   function initialize() {
         // get references to the canvas element as well as the 2D drawing context
         var element = document.getElementById("canvas");
         var context = element.getContext("2d");

         // start drawing when the mousedown event fires, and attach handlers to 
         // draw a line to wherever the mouse moves to
         $("#canvas").mousedown(function (mouseEvent) {
            var position = getPosition(mouseEvent, element);

            context.moveTo(position.X, position.Y);
            context.beginPath();
            prevent = true;

            // attach event handlers
            $(this).mousemove(function (mouseEvent) {
               drawLine(mouseEvent, element, context);
            }).mouseup(function (mouseEvent) {
               finishDrawing(mouseEvent, element, context);
            }).mouseout(function (mouseEvent) {
               finishDrawing(mouseEvent, element, context);
            });
         });

         // Handle each resize
         $(window).resize(function () {
            var can = document.getElementById('canvas');
			can.width = window.innerWidth;
            can.height = window.innerHeight;
         });

         // Handle initial load size
         var can = document.getElementById('canvas');
		 can.width = window.innerWidth;
         can.height = window.innerHeight;

         // clear the content of the canvas by resizing the element
         $("#btnClear").click(function () {
            // remember the current line width
            var currentWidth = context.lineWidth;

            element.width = element.width;
            context.lineWidth = currentWidth;
         });

         document.addEventListener('touchmove', function (event) {
            if (prevent) {
               event.preventDefault();
            }

            return event;
         }, false);
      }

   // draws a line to the x and y coordinates of the mouse event inside
   // the specified element using the specified context
   function drawLine(mouseEvent, element, context) {

      var position = getPosition(mouseEvent, element);
	  	  
	  //console.log(position.X + ',' + position.Y);
	  postArr.push({X: position.X, Y: position.Y});

      context.lineTo(position.X, position.Y);

      context.stroke();
   }

   // draws a line from the last coordiantes in the path to the finishing
   // coordinates and unbind any event handlers which need to be preceded
   // by the mouse down event
   function finishDrawing(mouseEvent, element, context) {
      // draw the line to the finishing coordinates
	  //var data = null;
	  
	  console.log(postArr);
      //data += '{[';
	  //$.each(postArr, function(key, pos) { data += pos + (key < postArr.length - 1 ? "," : false); });
      //data += ']}';
	  $.ajax({
        type: "POST",
        url: 'http://loclahost/composer-src/app.whiteboard.php',
		contentType: "application/json",
        dataType: 'json',
        data: JSON.stringify(postArr),
        success: function() {
          console.log('test'); 
        },
		cache: false
      });
	  
	  postArr = new Array();

      drawLine(mouseEvent, element, context);

      context.closePath();

      // unbind any events which could draw
      $(element).unbind("mousemove")
                .unbind("mouseup")
                .unbind("mouseout");
      prevent = false;
   }

<?php $appWhiteboard['script'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />


<?php
// (check_http_200('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_WWW . 'resources/js/tailwindcss-3.3.5.js')?
is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/') or mkdir($path, 0755, true);
if (is_file($path . 'tailwindcss-3.3.5.js')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path . 'tailwindcss-3.3.5.js'))))) / 86400)) <= 0 ) {
    $url = 'https://cdn.tailwindcss.com';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($js = curl_exec($handle))) 
      file_put_contents($path . 'tailwindcss-3.3.5.js', $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
  }
} else {
  $url = 'https://cdn.tailwindcss.com';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($js = curl_exec($handle))) 
    file_put_contents($path . 'tailwindcss-3.3.5.js', $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
}
?>

  <script src="<?= 'resources/js/tailwindcss-3.3.5.js' ?? $url ?>"></script>

<style type="text/tailwindcss">
<?= /*$appWhiteboard['style'];*/ NULL; ?>
* { margin: 0; padding: 0; } /* to remove the top and left whitespace */

html, body { width: 100%; height: 100%; overflow:hidden; } /* just to be sure these are full screen*/

#canvasDiv {
	position: absolute;
	background-color: #E8E8E8;
	top: 5px;
	left: 5px;
	width: 175px;
	height: 75px;
	border: 1px solid Black;
}

canvas
{
   display: block;
   background-color: #FFFFFF;
   border: 1px solid Black;
   cursor: crosshair;
   width: auto;
   height: auto;
}
</style>
</head>
<body>
<?= /* $appWhiteboard['body'];*/ ''; ?>

<?php
if (isset($HTTP_RAW_POST_DATA) && !is_null($HTTP_RAW_POST_DATA)) {
/*
	$query = "INSERT INTO `abatepai_abate`.`whiteboard` ( `id` , `board` , `date` , `php_sess` , `data` ) VALUES ( NULL , '" . $_SESSION['board'] . "', NULL, '" . session_id() . "', '" . mysql_real_escape_string(stripcslashes($HTTP_RAW_POST_DATA)) . "');";

	if (!mysql_query($query)) {
      die('Invalid query: ' . mysql_error());
    };
    echo $query;
	die();

*/

var_dump(stripcslashes($HTTP_RAW_POST_DATA));
}
?>
<div id="canvasDiv" style="padding: 5px; border: #000000 thin dashed;">
  <p style="font-family: Tahoma, Geneva, sans-serif; font-weight: bold;">WhiteBoard 0.1b</p>
  <p>(C) Barry Dick</p>
  <p>Participant: <?php echo session_id(); ?></p>
  <p style="font-weight: bold;">Board: <?= (isset($_SESSION['board']) ? $_SESSION['board'] : '&lt;blank&gt;') ?></p>
</div>
<canvas id="canvas"></canvas>

  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="../resources/js/jquery/jquery.min.js"></script> -->
<script>
<?= $appWhiteboard['script']; ?>
</script>
</body>
</html>
<?php $appPackagist['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'php' && APP_DEBUG)
  die($appPackagist['html']);

