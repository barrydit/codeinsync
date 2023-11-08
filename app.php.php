<?php

if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=config.php'));
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
#app_php-container {
position: absolute;
display: none;
top: 60px;
//bottom: 60px;
left: 50%;
transform: translateX(-50%);
width: auto;
height: 400px;
background-color: rgba(255, 255, 255, 0.9);
color: black;
text-align: center;
padding: 10px;
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

<?php $appPHP['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
  <div id="app_php-container" style="border: 1px solid #000; width: 400px;">
    <div class="header ui-widget-header">
      <div style="display: inline-block;">PHP <?= 'v' . PHP_VERSION; ?> Configuration/Settings</div>
      <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_php-container').style.display='none';">X</a>]</div> 
    </div>

      <div style="display: inline-block; width: auto; padding-left: 10px;">

      </div>

      <form style="display: inline;" action="<?= APP_URL_BASE . basename(APP_SELF) . '?' . http_build_query(APP_QUERY + array( 'app' => 'php')) . (APP_ENV == 'development' ? '#!' : '') /*  $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">

      <div>
        <div style="display: inline; width: 46%;">
          <?php if (isset($_GET['debug'])) { ?> <input type="hidden" name="debug" value="" /> <?php } ?>
          <select name="const" onchange="this.form.submit()">
<?php foreach(get_defined_constants(true)['user'] as $key => $user_const) { ?>
            <option <?= (isset($_GET['const']) && $_GET['const'] == $key ? ' selected' : '' )?>><?= $key; ?></option>
<?php } ?>
          </select>
        </div>
        <div style="background-color: #000; color: #000;"><textarea rows="10" cols="40"><?= (isset($_GET['const']) ? var_dump(htmlsanitize(constant($_GET['const']))) : '') ?></textarea></div>
      </div>

      </form>
      
      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->

  </div>
<!-- </div> -->

<?php $appPHP['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<?php //if (isset($_GET['client']) && $_GET['client'] != '') { 
//if (isset($_GET['domain']) && $_GET['domain'] != '') {
?>
ace.require("ace/ext/language_tools");
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

$(document).ready(function() {});
<?php $appPHP['script'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

<script src="https://cdn.tailwindcss.com"></script>

<style type="text/tailwindcss">
<?= $appPHP['style']; ?>
</style>
</head>
<body>
<?= $appPHP['body']; ?>

  <script src="../../resources/js/ace/ace.js" type="text/javascript" charset="utf-8"></script>
<!--  <script src="resources/js/ace/ext-language_tools.js" type="text/javascript" charset="utf-8"></script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>

  <script src="../../resources/js/ace/mode-php.js" type="text/javascript" charset="utf-8"></script>
  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="../resources/js/jquery/jquery.min.js"></script> -->
<script>
<?= /*$appPHP['script'];*/ NULL; ?>
</script>
</body>
</html>
<?php $appPHP['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'php' && APP_DEBUG)
  die($appPHP['html']);