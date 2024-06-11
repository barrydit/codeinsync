<?php

//dd(get_required_files());

//if (isset($_GET['path']) && isset($_GET['file']) && $path = realpath($_GET['path'] . $_GET['file']))

//$errors->{'TEXT_MANAGER'} = $path . "\n" . 'File Modified:    Rights:    Date of creation: ';

if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '') require_once $path;
  else die(var_dump("$path path was not found. file=config.php"));

//dd($_GET);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_GET['app']) && $_GET['app'] == 'ace_editor')
    if (isset($_POST['path']) && isset($_GET['file']) && $path = realpath($_POST['path'] . $_GET['file'])) {
      file_put_contents($path, $_POST['contents']);
      die(header('Location: ' . APP_WWW));
    }
  //dd($_POST);

//  if (isset($_GET['file'])) {
//    file_put_contents($projectRoot.(!$_POST['path'] ? '' : DIRECTORY_SEPARATOR.$_POST['path']).DIRECTORY_SEPARATOR.$_POST['file'], $_POST['editor']);
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

/*
$proc=proc_open('sudo ' . GIT_EXEC . ' ' . $match[1],
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
          list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
          $output[] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
*/

if (defined('GIT_EXEC'))
if (is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/ace') && empty(glob($path)))
    exec((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
elseif (!is_dir($path)) {
    if (!mkdir($path, 0755, true))
        $errors['GIT-CLONE-ACE'] = ' resources/js/ace does not exist.';
    exec((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
}

ob_start(); ?>

#app_ace_editor-container { position: absolute; display: none; top: 60px; margin: 0 auto; left: 50%; right: 50%; }
#app_ace_editor-container.selected { display: block; z-index: 1; 
  /* Add your desired styling for the selected container */
  /*
  // background-color: rgb(240, 224, 198); //  240, 224, 198, .75    #FBF7F1; // rgba(240, 224, 198, .25);
  
  bg-[#FBF7F1];
  bg-opacity-75;

  font-weight: bold;
  #top { background-color: rgba(240, 224, 198, .75); }
  */
}

input {
  color: black;
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
    border: 1px solid black;
    padding: 8px;
}

img { display: inline; }


<?php $app['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
  <div id="app_ace_editor-container" class="absolute <?= (__FILE__ ==  get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'ace_editor') && !isset($_GET['path']) ? 'selected' : '') ?>" style="z-index: 1; width: 424px; background-color: rgba(255,255,255,0.8); padding: 10px;">
    <div style="position: relative; margin: 0 auto; width: 404px; height: 306px; border: 3px dashed #38B1FF; background-color: rgba(56,177,255,0.6);">
      <div class="absolute ui-widget-header" style="position: absolute; display: inline-block; width: 100%; height: 25px; margin: -50px 0 25px 0; padding: 24px 0; border-bottom: 1px solid #000; z-index: 3;">
        <label class="ace_editor-home" style="cursor: pointer;">
          <div class="" style="position: relative; display: inline-block; top: 0; left: 0; margin-top: -5px;">
            <img src="resources/images/ace_editor_icon.png" width="32" height="32" />
          </div>
        </label>
        <div style="display: inline;">
          <span style="background-color: #38B1FF; color: #FFF;">Ace Editor <?= /* (version_compare(NPM_LATEST, NPM_VERSION, '>') != 0 ? 'v'.substr(NPM_LATEST, 0, similar_text(NPM_LATEST, NPM_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(NPM_LATEST, similar_text(NPM_LATEST, NPM_VERSION)) . '</span>' : 'v'.NPM_VERSION ); */ NULL; ?></span> <span style="background-color: #0078D7; color: white;"><code id="AceEditorVersionBox" class="text-sm" style="background-color: white; color: #0078D7;"></code></span>
        </div>
        
        <div style="display: inline; float: right; text-align: center; color: blue;"><code style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_ace_editor-container').style.display='none';">[X]</a></code></div> 
      </div>
      
      <div class=" ui-widget-content" style="position: relative; display: block; width: 398px; background-color: rgba(251,247,241); z-index: 2;">

        <div style="display: inline-block; text-align: left; width: 125px;">
          <div class="npm-menu text-sm" style="cursor: pointer; font-weight: bold; padding-left: 25px; border: 1px solid #000;">Main Menu</div>
          <div class="text-xs" style="display: inline-block; border: 1px solid #000;">
            <a class="text-sm" id="app_ace_editor-frameMenuPrev" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') ?>"> &lt; Menu</a> | <a class="text-sm" id="app_ace_editor-frameMenuNext" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') ?>">Init &gt;</a>
          </div>
        </div>
        <div class="absolute" style="position: absolute; display: inline-block; top: 4px; text-align: right; width: 272px; ">
          <div class="text-xs" style="display: inline-block;">
          + 478 <a href="https://github.com/ajaxorg/ace/graphs/contributors">contributors</a>
          <br /><a href="https://github.com/ajaxorg"><img src="resources/images/node.js.png" title="https://github.com/nodejs" width="18" height="18" /></a>
          <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;" href="https://ace.c9.io/" title="https://ace.c9.io/">https://ace.c9.io/</a>
          </div>
        </div>
        <div style="clear: both;"></div>
      </div>
<!--
      <div class="" style="position: absolute; top: 0; left: 0; right: 0; margin: 10px auto; opacity: 1.0; text-align: center; cursor: pointer; z-index: 1;">
        <img class="npm-menu" src="resources/images/node_npm.fw.png" style="margin-top: 45px;" width="150" height="198" />
      </div>
-->

<div style="position: relative; overflow: hidden; width: 398px; height: 256px;">
<!--
      <div id="app_ace_editor-frameMenu" class="app_ace_editor-frame-container absolute selected" style="background-color: rgb(225,196,151,.75); margin-top: 8px; height: 100%;">
-->

    <div style="position: relative; display: inline-block; width: auto; padding-left: 10px;">
 <!--
      <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL_BASE . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ array( 'app' => 'ace_editor')*/) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
        <input type="hidden" name="app" value="ace_editor" />
      <?php $path = realpath(getcwd() . (isset($_GET['path']) ? DIRECTORY_SEPARATOR . $_GET['path'] : '')) . DIRECTORY_SEPARATOR;
      if (isset($_GET['path'])) { ?>
       <input type="hidden" name="path" value="<?= $_GET['path']; ?>" /> 
      <?php } echo '<span title="' . $path . '">' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '</span>'; /* $path; */ ?>
        <select name="path" onchange="this.form.submit();">
          <option value>.</option>
          <option value>..</option>
<?php
if ($path)
  foreach (array_filter( glob( $path . DIRECTORY_SEPARATOR . '*'), 'is_dir') as $dir) {
    echo '<option value="' . (isset($_GET['path']) ?  $_GET['path'] . DIRECTORY_SEPARATOR : '') . basename($dir) . '"' . (isset($_GET['path']) && $_GET['path'] == basename($dir) ? ' selected' : '' )  . '>' . basename($dir) . '</option>' . "\n";
  }
?>
        </select>
      </form>
 / <input type="text" name="file" value="index.php" /> 

      <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL_BASE . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ array( 'app' => 'ace_editor')*/) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
      <input type="hidden" name="app" value="ace_editor" />

      <input type="hidden" name="path" value="<?= (isset($_GET['path']) ? $_GET['path'] : '') ?>" />

        <select name="file" onchange="this.form.submit();">
          <option value="">---</option>
<?php
if ($path)
foreach (array_filter( glob($path . DIRECTORY_SEPARATOR . '*.php'), 'is_file') as $file) {
  echo '<option value="' . basename($file) . '"' . (isset($_GET['file']) && $_GET['file'] == basename($file) ? ' selected' : '' )  . '>' . basename($file) . '</option>' . "\n";
}
?>
        </select>
      </form>
      </div> -->


      <form style="position: relative; display: inline;" action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'ace_editor')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="POST">
        <input type="hidden" name="path" value="<?= APP_PATH /*. APP_BASE['public'];*/ ?>" />
        <div style="display: inline-block; width: auto; text-align: right; float: right;">
          <input type="submit" value="Save" class="btn" style="margin: -5px 5px 5px 0;" onclick="document.getElementsByClassName('ace_text-input')[0].value = globalEditor.getSession().getValue(); document.getElementsByClassName('ace_text-input')[0].name = 'editor';"/>
        </div>
<!--   A (<?= $path ?>) future note: keep ace-editor nice and tight ... no spaces, as it interferes with the content window.
 https://scribbled.space/ace-editor-setup-usage/-->

        <div id="ui_ace_editor" class="ace_editor" style="display: <?= (isset($_GET['file']) && isset($_GET['path']) && is_file($_GET['path'] . $_GET['file']) ? 'block': 'block')?>; width: 700px; height: 400px; z-index: 1;"><textarea name="contents" class="ace_text-input" autocorrect="off" autocapitalize="none" spellcheck="false" style="opacity: 0; font-size: 1px; height: 1px; width: 1px; top: 28px; left: 86px;" wrap="off"><?= (isset($_GET['file']) && is_file($path . $_GET['file']) ? htmlsanitize(file_get_contents($path . $_GET['file'])) : /* (isset($_GET['project']) ? htmlsanitize(file_get_contents($path . 'projects/project.php')) : '')*/ '' ); /*   'clientele/' . $_GET['client'] . '/' . $_GET['domain'] . '/' .  */ ?><?= htmlsanitize("<?php

/* This is an example of ACE Editor working */

require(__DIR__ . 'config/config.php');

"); ?></textarea></div>
<?= /*
        <div class="containerTbl" style="background-ground: #fff; border: 1px solid #000; display: <?= (isset($_GET['file']) && isset($_GET['path']) && is_file($_GET['path'] . $_GET['file']) ? 'none': 'block' ) ?>;">
<table width="" style="border: 1px solid #000;">
<tr>
<?php
$paths = glob($path . '/*');
//dd(urldecode($_GET['path']));
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

$count = 1;
if (!empty($paths))
  foreach($paths as $key => $path) {
      echo '<td style="border: 1px solid #000;" class="text-xs">' . "\n";
      if (is_dir($path))
        echo '<a href="?app=ace_editor&path=' . basename($path) . '">'
        . '<img src="../../resources/images/directory.png" width="50" height="32" /><br />' . basename($path) . '</a>' . "\n";
      elseif (is_file($path))
        echo '<a href="?app=ace_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '">'
        . '<img src="../../resources/images/php_file.png" width="40" height="50" /><br />' . basename($path) . '</a>' . "\n";
      echo '</td>' . "\n";
      if ($count >= 6 || $path == end($paths)) echo '</tr>';
      if (isset($count) && $count >= 6) $count = 1;
      else $count++;
  } 
?>
</tr>
</table>
        </div>
*/ NULL; ?>
      </form>
-->
      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->

      </div>

      <div id="app_ace_editor-frameInit" class="app_ace_editor-frame-container absolute" style="overflow: hidden; height: 270px;">
<!--
    <form autocomplete="off" spellcheck="false" action="?app=git#!" method="POST">
      <div style="position: absolute; right: 0; float: right; text-align: center;">
        <input id="gitInitSubmit" class="btn" type="submit" value="Init/Run">
      </div> 
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitInitLabel" for="gitInit" style="background-color: #6781B2; color: white;">? <code>Init</code></label>
        </div>
      </div>
      <div id="gitInitForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="2" name="git[init]">git init</textarea>
      </div>
    </form>
-->
      </div>



      </div>
    </div>
  </div>
<?php $app['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<?php //if (isset($_GET['client']) && $_GET['client'] != '') { 
//if (isset($_GET['domain']) && $_GET['domain'] != '') {
if (is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/ace')) {
?>

//var ace = require("resources/js/ace/src/ace.js"); // ext/language_tools
var appEditor = ace.edit("app-ace-editor");
appEditor.setTheme("ace/theme/dracula");

//var JavaScriptMode = ace.require("ace/mode/javascript").Mode;
appEditor.session.setMode("ace/mode/php");
appEditor.setAutoScrollEditorIntoView(true);
appEditor.setShowPrintMargin(false);
appEditor.setOptions({
    //  resize: "both"
  enableBasicAutocompletion: true,
  enableLiveAutocompletion: true,
  enableSnippets: true
});

<?php } //}
?>

<?= /* $(document).ready(function() {}); */ ''; ?>

<?php $app['script'] = ob_get_contents();
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'ace_editor' && APP_DEBUG) {
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Pragma: no-cache");
  ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />
  
<?php

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
unset($path);
?>

  <script src="<?= 'resources/js/tailwindcss-3.3.5.js' ?? $url ?>"></script>

<style type="text/tailwindcss">
<?= $app['style']; ?>
</style>
</head>
<body>
<?= $app['body']; ?>

  <script src="resources/js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script>
  <script src="resources/js/ace/src/ext-language_tools.js" type="text/javascript" charset="utf-8"></script> 
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>

  <script src="resources/js/ace/src/mode-php.js" type="text/javascript" charset="utf-8"></script> -->
  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="../resources/js/jquery/jquery.min.js"></script> -->
<script>
<?= $app['script']; ?>
</script>
</body>
</html>
<?php 
  $return_contents = ob_get_contents(); 
  ob_end_clean();
  return $return_contents;
} else { 
  return $app;
}