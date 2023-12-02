<?php

//dd(get_required_files());

//if (isset($_GET['path']) && isset($_GET['file']) && $path = realpath($_GET['path'] . $_GET['file']))

//$errors->{'TEXT_MANAGER'} = $path . "\n" . 'File Modified:    Rights:    Date of creation: ';


if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=config.php'));


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

//dd($_POST);

  if (isset($_GET['app']) && $_GET['app'] == 'text_editor')
    if (isset($_POST['path']) && isset($_GET['file']) && $path = realpath($_POST['path'] . $_GET['file']))
      file_put_contents($path, $_POST['editor']);
      
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


if (is_dir('resources/js/ace') && empty(glob('resources/js/ace')))
    exec('sudo ' . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
elseif (!is_dir('resources/js/ace')) {
    if (!mkdir('resources/js/ace', 0755, true))
        $errors['GIT-CLONE-ACE'] = ' resources/js/ace does not exist.';
    exec('sudo ' . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
}

ob_start(); ?>

/* Styles for the absolute div */
#app_text_editor-container {
position: absolute;
top: 10%;
//bottom: 60px;
left: 50%;
transform: translateX(-50%);
width: auto;
height: 408px;
background-color: rgba(255, 255, 255, 0.9);
color: black;
text-align: center;
z-index: 1;
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

<?php $appTextEditor['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
  <div id="app_text_editor-container" style="display: <?= (isset($_GET['app']) && $_GET['app'] == 'text_editor' ? 'block' : (isset($_GET['file']) ? 'block' : 'none')) ?>; width: auto; border: 1px solid #000;">

    <div class="header ui-widget-header" style="margin: 10px;">

      <div style="display: inline-block;">
          <div class="absolute" style="position: absolute; top: 5px; left: 10px;">
            <img src="resources/images/code_icon.png" width="32" height="32">
          </div>
          Text Editor</div>
      <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_text_editor-container').style.display='none';">X</a>]</div> 
    </div>

    <div style="position: relative; display: inline-block; width: auto; padding-left: 10px;">
      <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL_BASE . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ array( 'app' => 'text_editor')*/) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
        <input type="hidden" name="app" value="text_editor" />
      <?php $path = realpath(getcwd() . (isset($_GET['path']) ? DIRECTORY_SEPARATOR . $_GET['path'] : '')) . DIRECTORY_SEPARATOR;
      if (isset($_GET['path'])) { ?>
        <!-- <input type="hidden" name="path" value="<?= $_GET['path']; ?>" /> -->
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
 /<!--<input type="text" name="file" value="index.php" />-->
      <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL_BASE . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ array( 'app' => 'text_editor')*/) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
      <input type="hidden" name="app" value="text_editor" />

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
      </div>

      <form style="position: relative; display: inline;" action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'text_editor')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="POST">
        <input type="hidden" name="path" value="<?= APP_PATH /*. APP_BASE['public'];*/ ?>" />
        <div style="display: inline-block; width: auto; text-align: right; float: right;">
          <input type="submit" value="Save" class="btn" style="margin: -5px 5px 5px 0;" onclick="document.getElementsByClassName('ace_text-input')[0].value = globalEditor.getSession().getValue(); document.getElementsByClassName('ace_text-input')[0].name = 'editor';"/>
        </div>
        <!-- A (<?= $path ?>) future note: keep ace-editor nice and tight ... no spaces, as it interferes with the content window. -->

<!-- https://scribbled.space/ace-editor-setup-usage/ -->

        <!-- div id="ace-editor" class="ace_editor" style="display: <?= (isset($_GET['file']) && isset($_GET['path']) && is_file($_GET['path'] . $_GET['file']) ? 'block': 'block')?>; width: 700px; height: 400px; z-index: 1;"><textarea name="contents" class="ace_text-input" autocorrect="off" autocapitalize="none" spellcheck="false" style="opacity: 0; font-size: 1px; height: 1px; width: 1px; top: 28px; left: 86px;" wrap="off"><?= (isset($_GET['file']) && is_file($path . $_GET['file']) ? htmlsanitize(file_get_contents($path . $_GET['file'])) : (isset($_GET['project']) ? htmlsanitize(file_get_contents($path . 'project.php')) : '') ); /*   'clientele/' . $_GET['client'] . '/' . $_GET['domain'] . '/' .  */ ?></textarea></div -->
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
        echo '<a href="?app=text_editor&path=' . basename($path) . '">'
        . '<img src="../../resources/images/directory.png" width="50" height="32" /><br />' . basename($path) . '</a>' . "\n";
      elseif (is_file($path))
        echo '<a href="?app=text_editor&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '">'
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
      
      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->

  </div>
<!-- </div> -->

<?php $appTextEditor['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<?php //if (isset($_GET['client']) && $_GET['client'] != '') { 
//if (isset($_GET['domain']) && $_GET['domain'] != '') {
?>
//var ace = require("resources/js/ace/src/ace.js"); // ext/language_tools
var editor = ace.edit("ace-editor");
editor.setTheme("ace/theme/dracula");

//var JavaScriptMode = ace.require("ace/mode/javascript").Mode;
editor.session.setMode("ace/mode/php");
editor.setAutoScrollEditorIntoView(true);
editor.setShowPrintMargin(false);
editor.setOptions({
    //  resize: "both"
  enableBasicAutocompletion: true,
  enableLiveAutocompletion: true,
  enableSnippets: true
});
<?php //} }
?>

<?= /* $(document).ready(function() {}); */ ''; ?>
<?php $appTextEditor['script'] = ob_get_contents();
ob_end_clean();

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
?>

  <script src="<?= 'resources/js/tailwindcss-3.3.5.js' ?? $url ?>"></script>
   

<style type="text/tailwindcss">
<?= $appTextEditor['style']; ?>
</style>
</head>
<body>
<?= $appTextEditor['body']; ?>

  <script src="resources/js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script>
  <script src="resources/js/ace/src/ext-language_tools.js" type="text/javascript" charset="utf-8"></script> 
<!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>

  <script src="resources/js/ace/src/mode-php.js" type="text/javascript" charset="utf-8"></script>-->
  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="../resources/js/jquery/jquery.min.js"></script> -->
<script>
<?= $appTextEditor['script']; ?>
</script>
</body>
</html>
<?php $appTextEditor['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'text_editor' && APP_DEBUG)
  die($appTextEditor['html']);