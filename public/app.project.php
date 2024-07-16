<?php

if (__FILE__ == get_required_files()[0] && __FILE__ == $_SERVER["SCRIPT_FILENAME"]) {
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

if (!$path = realpath(APP_PATH . 'projects/index.php')) {
  // file_put_contents($path, $_POST['contents']);
  $errors['project.php'] = 'projects/index.php was missing. Using template.' . "\n";
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_GET['app']) && $_GET['app'] == 'project')
    if (isset($_POST['path']) && isset($_GET['file']) && $path = realpath($_POST['path'] . $_GET['file'])) {
      file_put_contents($path, $_POST['contents']);
      die(); //header('Location: ' . APP_WWW)
    }
}

ob_start(); ?>

#app_project-container { position: absolute; height: auto; display: none; top: 40%; left: 50%; transform: translate(-50%, -50%); }
#app_project-container.selected { display: block; z-index: 1; 
  /* Add your desired styling for the selected container */
  /*
  // background-color: rgb(240, 224, 198); //  240, 224, 198, .75    #FBF7F1; // rgba(240, 224, 198, .25);
  
  bg-[#FBF7F1];
  bg-opacity-75;

  font-weight: bold;
  #top { background-color: rgba(240, 224, 198, .75); }
  */
}

img { display: inline; }

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
  <div id="app_project-container" class="absolute <?= (get_included_files()[0] == __FILE__ || (isset($_GET['project']) && !$_GET['project']) && !isset($_GET['path']) ? 'selected' : '') ?>" style="z-index: 1; width: 724px; background-color: rgba(255,255,255,0.8); padding: 10px;">
    <div style="position: relative; margin: 0 auto; width: 704px; height: 506px; border: 3px dashed #DD0000; background-color: #FBF7F1;">
      <div class="absolute ui-widget-header" style="position: absolute; display: inline-block; width: 100%; height: 25px; margin: -50px 0 25px 0; padding: 24px 0; border-bottom: 1px solid #000; z-index: 3;">
        <label class="npm-home" style="cursor: pointer;">
          <div class="" style="position: relative; display: inline-block; top: 0; left: 0; margin-top: -5px;">
            <img src="resources/images/code_icon.png" width="40" height="40" />
          </div>
        </label>
        <div style="display: inline;">
          <span style="background-color: white; color: #DD0000;">Project <?= /* (version_compare(NPM_LATEST, NPM_VERSION, '>') != 0 ? 'v'.substr(NPM_LATEST, 0, similar_text(NPM_LATEST, NPM_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(NPM_LATEST, similar_text(NPM_LATEST, NPM_VERSION)) . '</span>' : 'v'.NPM_VERSION ); */ NULL; ?></span> <span style="background-color: #0078D7; color: white;"><code class="text-sm" style="background-color: white; color: #0078D7;">$ <?= (defined('PHP_EXEC') ? 'projects/index.php' : null); ?></code></span>
        </div>
        
        <div style="display: inline; float: right; text-align: center; color: blue;"><code style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_project-container').style.display='none';">[X]</a></code></div> 
      </div>
      
      <div class=" ui-widget-content" style="position: relative; display: block; width: 698px; background-color: rgba(251,247,241); z-index: 2; height: 500px; overflow: scroll;">

        <div class="splitter">
          <div id="first">
            <iframe id="app_project-iframe" src="<?= basename(/*'?project=show'*/ 'project.php') ?>" style="height: 100%; width: 100%;"></iframe>
          </div>
          <form id="app_project-saveForm" method="POST">
          <div id="separator" style="height: 25px; text-align: center;">
            <pre style="display: inline;">---Drag Bar---</pre>

            <div style="display: inline; float: right; z-index: 1000;">

        <button type="submit" style="background-color: white; cursor: pointer; border: 1px solid #000;">&nbsp;&nbsp;Save&nbsp;&nbsp;</button>

<!-- input type="submit" name="save-submit" value="&nbsp;&nbsp;Save&nbsp;&nbsp;" style="background-color: white; cursor: pointer;" onclick="document.getElementsByClassName('ace_text-input')[0].value = globalEditor.getSession().getValue(); document.getElementsByClassName('ace_text-input')[0].name = 'editor';" / -->

            </div>

          </div>
    
          <div id="second">

<?php $path = realpath(getcwd() . (isset($_GET['path']) ? DIRECTORY_SEPARATOR . $_GET['path'] : '')) . DIRECTORY_SEPARATOR; ?>
            <div id="app_project_editor" class="editor" style="display: <?= (isset($_GET['file']) && isset($_GET['path']) && is_file($_GET['path'] . $_GET['file']) ? 'block': 'block'); ?>; width: 778px; height: 287px; z-index: 2;"><textarea name="contents" class="ace_text-input" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" style="opacity: 0; font-size: 1px; height: 1px; width: 1px; top: 28px; left: 86px;" wrap="off"><?= htmlsanitize((is_file('projects/index.php') ? file_get_contents('projects/index.php') : '') ) /*   'clientele/' . $_GET['client'] . '/' . $_GET['domain'] . '/' .  */ ?></textarea></div>
          </div>
        </form>
      </div>

      <!-- 
      <div style="display: inline-block; width: auto; padding-left: 10px;">
        <iframe src="<?= basename(__FILE__) ?>" style="height: 550px; width: 775px;"></iframe>
      </div>
<pre id="ace-editor" class="ace_editor"></pre> -->
      </div>
    </div>
  </div>
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

document.getElementById('app_project-saveForm').addEventListener('submit', function(event) {
  // Prevent the default form submission
  event.preventDefault();

  //document.getElementsByClassName('ace_text-input')[0].value = globalEditor.getSession().getValue();
  //document.getElementsByClassName('ace_text-input')[0].name = 'editor';
  //var globalEditor = editor2;
  console.log(globalEditor.getSession().getValue());

      $.ajax({
        url: '<?= basename(__FILE__); ?>?app=project&file=projects/index.php',
        type: 'POST',
        data: { path: '', contents: globalEditor.getSession().getValue() },
        //dataType: 'json',
        success: function (msg) {
          //console.log(msg);
          document.getElementById('app_project-iframe').contentWindow.location.reload();
          //iframe refresh
          console.log('window was reloaded');
        },
        error: function (jqXHR, textStatus) {
          console.log(jqXHR.responseText);
          //let responseText = jQuery.parseJSON(jqXHR.responseText);
          //console.log(responseText);
        }
      });

});

<?php $appProject['script'] = ob_get_contents();
ob_end_clean();

//header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
//header("Pragma: no-cache"); 
ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" /-->

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
<?= $appProject['style']; ?>
</style>
</head>
<body>
<?= $appProject['body']; ?>

  <script src="<?= (check_http_200('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : $path . 'jquery-3.7.1.min.js') ?>"></script>
  <!-- You need to include jQueryUI for the extended easing options. -->
<?php /* https://stackoverflow.com/questions/12592279/typeerror-p-easingthis-easing-is-not-a-function */ ?>
  <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script> <!-- Uncaught ReferenceError: jQuery is not defined -->


  <script src="resources/js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script> 
    <script src="resources/js/ace/src/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var editor = ace.edit("app_project_editor");
        editor.setTheme("ace/theme/dracula");
        // (file_ext .js = javascript, .php = php)
        editor.session.setMode("ace/mode/php");
        editor.setAutoScrollEditorIntoView(true);
        editor.setShowPrintMargin(false);
        editor.setOptions({
            //  resize: "both"
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            enableSnippets: true
        });

    });

<?= $appProject['script']; ?>
</script>
</body>
</html>
<?php $appProject['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'project' && APP_DEBUG)
  Shutdown::setEnabled(false)->setShutdownMessage(function() {
      return eval('?>' . file_get_contents('projects/index.php')); // -wow */
    })->shutdown(); // die();ob_start(); ?>