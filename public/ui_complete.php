<?php


//dd(get_required_files());

//if (isset($_GET['path']) && isset($_GET['file']) && $path = realpath($_GET['path'] . $_GET['file']))

//$errors->{'TEXT_MANAGER'} = $path . "\n" . 'File Modified:    Rights:    Date of creation: ';



if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=config.php'));

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


if (is_dir('resources/js/ace') && empty(glob('resources/js/ace')))
    exec('sudo ' . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
elseif (!is_dir('resources/js/ace')) {
    if (!mkdir('resources/js/ace', 0755, true))
        $errors['GIT-CLONE-ACE'] = ' resources/js/ace does not exist.';
    exec('sudo ' . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
}

ob_start(); ?>

/* Styles for the absolute div */
#app_ace_editor-container {
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

<?php $appAceEditor['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
  <div id="app_ace_editor-container" style="display: <?= (isset($_GET['app']) && $_GET['app'] == 'ace_editor' ? 'block' : (isset($_GET['file']) ? 'block' : 'none')) ?>; width: auto; border: 1px solid #000;">

    <div class="header ui-widget-header" style="margin: 10px;">

      <div style="display: inline-block;">
          <div class="absolute" style="position: absolute; top: 5px; left: 10px;">
            <img src="resources/images/ace_editor_icon.png" width="32" height="32">
          </div>
          ACE Editor</div>
      <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_ace_editor-container').style.display='none';">X</a>]</div> 
    </div>

    <div style="position: relative; display: inline-block; width: auto; padding-left: 10px;">
      <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL_BASE . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ array( 'app' => 'ace_editor')*/) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
        <input type="hidden" name="app" value="ace_editor" />
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
      <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL_BASE . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ array( 'app' => 'ace_editor')*/) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
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
      </div>

      <form style="position: relative; display: inline;" action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'ace_editor')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="POST">
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
      
      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->

  </div>
<!-- </div> -->

<?php $appAceEditor['body'] = ob_get_contents();
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
<?php $appAceEditor['script'] = ob_get_contents();
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
<?= $appAceEditor['style']; ?>
</style>
</head>
<body>
<?= $appAceEditor['body']; ?>

  <script src="resources/js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script>
  <script src="resources/js/ace/src/ext-language_tools.js" type="text/javascript" charset="utf-8"></script> 
<!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>

  <script src="resources/js/ace/src/mode-php.js" type="text/javascript" charset="utf-8"></script>-->
  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="../resources/js/jquery/jquery.min.js"></script> -->
<script>
<?= $appAceEditor['script']; ?>
</script>
</body>
</html>
<?php $appAceEditor['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'ace_editor' && APP_DEBUG)
  die($appAceEditor['html']);
  
// 4.826 @ 4.802
dd('ace init time: ', false);
/****   END of ui.ace_editor.php   ****/




// require 'vendor/autoload.php'; // Include Composer's autoloader

/*
use Composer\Factory;
use Composer\Repository\InstalledRepositoryInterface;

// Initialize Composer
$composer = Factory::create();

// Get the installed packages repository
$installedRepo = $composer->getRepositoryManager()->getLocalRepository();

// Get a list of installed packages
$installedPackages = $installedRepo->getPackages();

// Print the list of installed packages
foreach ($installedPackages as $package) {
    echo $package->getName() . ' (' . $package->getVersion() . ')' . PHP_EOL;
}
die();

*/


// composer create-project [PACKAGE] [DESTINATION PATH] [--FLAGS]
//composer create-project laravel/laravel example-app


//cd example-app

//php artisan serve

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

/*

Workflows and Projects

PHP   .github / workflows / php.yml
Build and test a PHP application using Composer

SLSA Generic generator
Generate SLSA3 provenance for your existing release workflows

Jekyll using Docker image
Package a Jekyll site using the jekyll/builder Docker image.

Laravel
Test a Laravel project.

Symfony
Test a Symfony project.

Publish Node.js Package
Publishes a Node.js package to npm.

Publish Node.js Package to GitHub Packages
Publishes a Node.js package to GitHub Packages.

*/
}


/** Loading Time: 5.1s **/
  
  //dd(get_required_files(), true);

if (!in_array(APP_PATH . APP_BASE['config'] . 'composer.php', get_required_files()))
if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../composer.php') ? '../composer.php' : (is_file('../config/composer.php') ? '../config/composer.php' : null))
    : (is_file('composer.php') ? 'composer.php' : (is_file('config/composer.php') ? 'config/composer.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=composer.php'));

if (!in_array(APP_PATH . APP_BASE['public'] . 'app.console.php', get_required_files()))
if ($path = (basename(getcwd()) == 'public')
    ? (is_file('app.console.php') ? 'app.console.php' : (is_file('../config/app.console.php') ? '../config/app.console.php' : null))
    : (is_file('app.console.php') ? 'app.console.php' : (is_file('public/app.console.php') ? 'public/app.console.php' : 'app.console.php'))) require_once($path); 
else die(var_dump($path . ' path was not found. file=app.console.php'));

/*  ...
    "autoload": {
        "psr-4": {
            "HtmlToRtf\\": "src/HtmlToRtf",
            "ProgressNotes\\": "src/ProgressNotes"
        }
    }
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  // consider creating a visual aspect for the lock file

  if (isset($_POST['composer']['create-project']) && preg_match(COMPOSER_EXPR_NAME, $_POST['composer']['package'], $matches)) {
    if (!is_dir($path = APP_PATH . 'project'))
      (@!mkdir($path, 0755, true) ?: $errors['COMPOSER-PROJECT'] = 'project/ could not be created.' );
    if ($matches[1] == 'laravel' && $matches[2] == 'laravel')  
      exec('sudo composer create-project ' . $_POST['composer']['package'] . ' project/laravel', $output, $returnCode) or $errors['COMPOSER-PROJECT-LARAVEL'] = $output;
    elseif ($matches[1] == 'symfony' && $matches[2] == 'skeleton')  
      exec('sudo composer create-project ' . $_POST['composer']['package'] . ' project/symfony', $output, $returnCode) or $errors['COMPOSER-PROJECT-SYMFONY'] = $output;

    unset($_POST['composer']['package']);
    unset($_POST['composer']['create-project']);
  } elseif (isset($_POST['composer']['package']) && preg_match(COMPOSER_EXPR_NAME, $_POST['composer']['package'])) {
        list($vendor, $package) = explode('/', $_POST['composer']['package']);

        if (empty($vendor))
          $errors['COMPOSER_PKG'] = 'vendor is missing its value. vendor=' . $vendor;
        if (empty($package))
          $errors['COMPOSER_PKG'] = 'package is missing its value. package=' . $package;

        //if ($vendor == 'nesbot' && $package == 'carbon') {

        if (preg_match(DOMAIN_EXPR, packagist_return_source($vendor, $package), $matches))
$raw_url = $initial_url = $matches[0];
        else $raw_url = '';
    
        if (!is_file(APP_BASE['var'].'package-' . $vendor . '-' . $package . '.php')) {

          $source_blob = (check_http_200($raw_url) ? file_get_contents($raw_url) : '' );
    
          //dd('url: ' . $raw_url);
          $raw_url = addslashes($raw_url);

          $source_blob = addslashes(COMPOSER_JSON['json']); // $source_blob
          file_put_contents(APP_BASE['var'].'package-' . $vendor . '-' . $package . '.php', '<?php' . "\n" . ( check_http_200($raw_url) ? '$source = "' . $raw_url . '";' : '' ) . "\n" . 
<<<END
\$composer_json = "{$source_blob}";
return '<form action method="POST">'
. '...'
. '</form>';
END
);

          if (isset($_POST['composer']['install'])) {
            exec('sudo composer require ' . $_POST['composer']['package'], $output, $returnCode) or $errors['COMPOSER-REQUIRE'] = $output;
            exec('sudo composer update ' . $_POST['composer']['package'], $output, $returnCode) or $errors['COMPOSER-UPDATE'] = $output;
          }

      exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query(APP_QUERY))); // , '', '&amp;'
      //}

    } elseif (isset($_POST['composer']['config']) && !empty($_POST['composer']['config'])) {
      $composer = new composerSchema;

/*
      if (isset($_POST['composer']['config']['version']) && preg_match(COMPOSER_EXPR_VER, $_POST['composer']['config']['version']))
        $composer->{'version'} = $_POST['composer']['config']['version'];   // $_POST['composer']['config']['version'] !== ''
      else unset($composer->{'version'});
*/
      $composer->{'name'} = $_POST['composer']['config']['name']['vendor'] . '/' . $_POST['composer']['config']['name']['package'];
      $composer->{'description'} = $_POST['composer']['config']['description'];
      if (isset($_POST['composer']['config']['version']) && preg_match(COMPOSER_EXPR_VER, $_POST['composer']['config']['version']))
        $composer->{'version'} = $_POST['composer']['config']['version'];   // $_POST['composer']['config']['version'] !== ''
      else unset($composer->{'version'});
      $composer->{'type'} = $_POST['composer']['config']['type'];
      $composer->{'keywords'} = (isset($_POST['composer']['config']['keywords']) ? $_POST['composer']['config']['keywords'] : []);
      $composer->{'homepage'} = 'https://github.com/' . $_POST['composer']['config']['name']['vendor'] . '/' . $_POST['composer']['config']['name']['package'];
      $composer->{'readme'} = 'README.md';
      $composer->{'time'} = date('Y-m-d H:i:s');
      $composer->{'license'} = $_POST['composer']['config']['license'];
      $composer->{'authors'} = []; //$_POST['composer']['authors'];

      if (!empty($_POST['composer']['config']['authors'])) foreach ($_POST['composer']['config']['authors'] as $key => $author) {

        if ($author['name'] != '' || $author['email'] != '') {
          $object = new stdClass();
          $object->name = $author['name'] ?? 'John Doe';
          $object->email = $author['email'] ?? 'jdoe@example.com';
          $object->role = $author['role'] ?? 'Developer';

          $composer->{'authors'}[] = $object;
        }
      } else $composer->{'authors'} = [];
  
      // $composer->{'support'}
      // $composer->{'funding'}
      
      // $composer->{'repositories'}
      
      if (!$composer->{'repositories'} || empty($composer->{'repositories'})) $composer->{'repositories'} = [];
  
      $composer->{'require'} = new stdClass(); //$_POST['composer']['require'];
      
  //dd($composer->{'require'});
  
      if (!empty($_POST['composer']['config']['require'])) {
        //if (!in_array($require_0, $_POST['composer']['require'])) { continue; }
        foreach ($_POST['composer']['config']['require'] as $require) {   //   

          if (preg_match('/(.*):(.*)/', $require, $match)) $composer->{'require'}->{$match[1]} = $match[2] ?? '^';
        }
      } else $composer->{'require'} = new StdClass;
    
      $composer->{'require-dev'} = new stdClass();
      
      if (!empty($_POST['composer']['config']['require-dev'])) {
        //if (!in_array($require_0, $_POST['composer']['require'])) { continue; }
        foreach ($_POST['composer']['config']['require-dev'] as $require) {   //   
          if (preg_match('/(.*):(.*)/', $require, $match)) $composer->{'require-dev'}->{$match[1]} = $match[2] ?? '^';
        }
      } else $composer->{'require-dev'} = new StdClass;

      $composer->{'autoload'} = new StdClass; // $_POST['composer']['autoload'];
  //$composer->{'autoload'}->{'psr-4'} = new StdClass; 
  //$composer->{'autoload'}->{'psr-4'}->{'HtmlToRtf\\'} = "src/HtmlToRtf";
  //$composer->{'autoload'}->{'psr-4'}->{'ProgressNotes\\'} = "src/HtmlToRtf";

  //$composer->{'autoload-dev'} = $_POST['composer']['autoload-dev'];

      $composer->{'minimum-stability'} = $_POST['composer']['config']['minimum-stability'];
      
      $composer->{'prefer-stable'} = true;

      //dd();

      if (COMPOSER_AUTH['token'] != $_POST['auth']['github_oauth']) {
        $tmp_auth = json_decode(COMPOSER_AUTH['json']);
        $tmp_auth->{'github-oauth'}->{'github.com'} = $_POST['auth']['github_oauth'];

        file_put_contents(COMPOSER_AUTH['path'], json_encode($tmp_auth, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)); // COMPOSER_AUTH['json']
      }

      file_put_contents(COMPOSER_JSON['path'], json_encode($composer, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

    }

    if (isset($_POST['composer']['init']) && !empty($_POST['composer']['init'])) {
      $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . str_replace(array("\r\n", "\r", "\n"), ' ', $_POST['composer']['init']) . '; sudo ' . COMPOSER_EXEC['bin'] . ' update;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

      list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

      if (empty($stdout)) {
        if (!empty($stderr))
          $errors['COMPOSER_INIT'] = '$stdout is empty. $stderr = ' . $stderr;
      } else $errors['COMPOSER_INIT'] = $stdout;

      //dd($errors);
    }
    //dd('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC . ' install ' . (isset($_POST['composer']['config']) ? '-o' : (isset($_POST['composer']['optimize-classes']) ? '-o': '')) . ';');
    
    isset($_POST['composer']['lock'])
      and unlink(APP_PATH . 'composer.lock');

// https://stackoverflow.com/questions/33052195/what-are-the-differences-between-composer-update-and-composer-install
    
    if (isset($_POST['composer']['install'])) {
      $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC['bin'] . ' install ' . (isset($_POST['composer']['config']) ? '-o' : (isset($_POST['composer']['optimize-classes']) ? '-o': '')) . ';', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

      list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

      if (empty($stdout)) {
        if (!empty($stderr))
          $errors['COMPOSER_INSTALL'] = '$stdout is empty. $stderr = ' . $stderr;
      } else $errors['COMPOSER_INSTALL'] = $stdout;

      //$composer = json_decode(COMPOSER_JSON['json'], true);   dd($composer);
      
      /* php composer.phar remove phpmd/phpmd    php composer.phar update*/
      /* composer update phpmd/phpmd */
    }
    
    if (isset($_POST['composer']['update'])) {
    
    /* Update won't work if the repositry has any upper/lower case letters differn't ... */
/*  
update [--with WITH] [--prefer-source] [--prefer-dist] [--prefer-install PREFER-INSTALL] [--dry-run] [--dev] [--no-dev] [--lock] [--no-install] [--no-audit] [--audit-format AUDIT-FORMAT] [--no-autoloader] [--no-suggest] [--no-progress] [-w|--with-dependencies] [-W|--with-all-dependencies] [-v|vv|vvv|--verbose] [-o|--optimize-autoloader] [-a|--classmap-authoritative] [--apcu-autoloader] [--apcu-autoloader-prefix APCU-AUTOLOADER-PREFIX] [--ignore-platform-req IGNORE-PLATFORM-REQ] [--ignore-platform-reqs] [--prefer-stable] [--prefer-lowest] [-i|--interactive] [--root-reqs] [--] [<packages>...]
*/

      if (isset($_POST['composer']['self-update']) || file_exists(APP_PATH . 'composer.phar')) {
        if (!file_exists(APP_PATH . 'composer-setup.php'))
          copy('https://getcomposer.org/installer', 'composer-setup.php');
        exec('php composer-setup.php');
      }
      // If this process isn't working, its because you have an invalid composer.json file
      $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC['bin'] . ' update'  , array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

      list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

      if (empty($stdout)) {
        if (!empty($stderr))
          $errors['COMPOSER_UPDATE'] = '$stdout is empty. $stderr = ' . $stderr;
      } else $errors['COMPOSER_UPDATE'] = $stdout;

      if (defined('COMPOSER_VERSION') && defined('COMPOSER_LATEST'))
        if (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0) {
          $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC['bin'] . ' self-update;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);
    
          list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    
          if (empty($stdout)) {
            if (!empty($stderr))
              $errors['COMPOSER_UPDATE'] = '$stdout is empty. $stderr = ' . $stderr;
          } else $errors['COMPOSER_UPDATE'] = $stdout;
        }
      // $_POST['composer']['cmd'];
    }

    exit(header('Location: ' . APP_URL_BASE));
  }
}

// dd(get_required_files(), true);

/*
<?php ob_start(); ?>
<HTML ...>
<?php $appComposer['css'] = ob_get_contents(); ?>
*/ 

ob_start(); ?>


#app_composer-container { position: absolute; display: none; top: 60px; left: 50%; right: 50%; margin: 0 auto; }
#app_composer-container.selected { display: block; z-index: 1; 
  /* Add your desired styling for the selected container */
  /*
  // background-color: rgb(240, 224, 198); //  240, 224, 198, .75  #FBF7F1; // rgba(240, 224, 198, .25);
  
  bg-[#FBF7F1];
  bg-opacity-75;

  font-weight: bold;
  #top { background-color: rgba(240, 224, 198, .75); }
  */
}

.app_composer-frame-container { position: absolute; display: none; top:0; left: 0; width: 400px; }
.app_composer-frame-container.selected { display: block; z-index: 1; }

/* #app_composer-frameName == ['menu', 'conf', 'install', 'init', 'update'] */

#app_composer-frameMenu {}
#app_composer-frameMenuPrev {} /* composerMenuPrev */
#app_composer-frameMenuNext {} /* composerMenuNext */

#app_composer-frameMenuConf {}
#app_composer-frameMenuInstall {}
#app_composer-frameMenuInit {}
#app_composer-frameMenuUpdate {}

#app_composer-frameConf {}
#app_composer-frameInstall {}
#app_composer-frameInit {}
#app_composer-frameUpdate {}

#update { backgropund-color: rgba(240, 224, 198, .75); }
#middle { backgropund-color: rgba(240, 224, 198, .75); }
#bottom { backgropund-color: rgba(240, 224, 198, .75); }

.btn {
  @apply rounded-md px-2 py-1 text-center font-medium text-slate-900 shadow-sm ring-1 ring-slate-900/10 hover:bg-slate-50
}

.composer-menu {
  cursor: pointer;
}

.dropbtn {
  background-color: #3498DB;
  color: white;
  padding: 2px 7px;
  font-size: 14px;
  border: none;
  cursor: pointer;
}

.dropbtn:hover, .dropbtn:focus {
  background-color: #2980B9;
}

.dropdown {
  position: relative;
  display: inline-block;
  float: right;
  z-index: 1;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: #f1f1f1;
  min-width: 160px;
  margin: -100px;
  overflow: auto;
}

.dropdown-content a {
  color: black;
  padding: 8px 12px;
  text-decoration: none;
  display: block;
}

.dropdown a:hover {background-color: #ddd;}

.show { display: block; }

img { display: inline; }

<?php $appComposer['style'] = ob_get_contents();
ob_end_clean();

// dd(glob('*')); dd(getcwd());

//(APP_SELF == __FILE__ || isset($_GET['app']) && $_GET['app'] == 'composer' ? 'selected' : (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? (isset($_GET['app']) && $_GET['app'] != 'composer' ? '' : 'selected') :  '')) 

ob_start(); ?>

  <div id="app_composer-container" class="absolute <?= (APP_SELF == __FILE__ || (isset($_GET['app']) && $_GET['app'] == 'composer') && !isset($_GET['path']) || (defined('COMPOSER') && !is_object(COMPOSER)) || count((array)COMPOSER) === 0 || version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? 'selected' : '') ?>" style="z-index: 1; width: 424px; background-color: rgba(255,255,255,0.8); padding: 10px;">

    <div style="position: relative; margin: 0 auto; width: 404px; height: 324px; border: 3px dashed #6B4329; background-color: #FBF7F1;">

      <div class="absolute ui-widget-header" id="" style="position: absolute; display: inline-block; width: 100%; margin: -25px 0 10px 0; border-bottom: 1px solid #000; z-index: 3;">
        <label class="composer-home" style="cursor: pointer;">
          <div class="absolute" style="position: absolute; top: 0px; left: 3px;">
            <img src="resources/images/composer_icon.png" width="32" height="40" />
          </div>
        </label>
        <div style="display: inline; padding-left: 40px;">
          <span style="background-color: #B0B0B0; color: white;">Composer <?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? 'v'.substr(COMPOSER_LATEST, 0, similar_text(COMPOSER_LATEST, COMPOSER_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(COMPOSER_LATEST, similar_text(COMPOSER_LATEST, COMPOSER_VERSION)) . '</span>' : 'v'.COMPOSER_VERSION ); ?></span>
          <span>

          <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'composer')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
            <?php if (isset($_GET['debug'])) { ?> <input type="hidden" name="debug" value="" /> <?php } ?>

            <code class="text-sm" style="background-color: #fff; color: #0078D7;">$ 
              <input type="hidden" name="app" value="composer" />
              <select name="exec" onchange="this.form.submit();">
                <option <?= (COMPOSER_EXEC == COMPOSER_BIN ? 'selected' : '') ?> value="bin"><?= COMPOSER_BIN['bin']; ?></option>
                <option <?= (COMPOSER_EXEC == COMPOSER_PHAR ? 'selected' : '') ?> value="phar"><?= 'php composer.phar' /*COMPOSER_PHAR['bin']*/; ?></option>
              </select>

            </code>
          </form>
          </span>
        </div>
        
        <div style="display: inline; float: right; text-align: center; "><code style=" background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_composer-container').style.display='none';">[X]</a></code></div> 
      </div>
      
      <div class="ui-widget-content" style="position: relative; display: block; width: 398px; background-color: rgba(251,247,241); z-index: 2;">
        <div style="display: inline-block; text-align: left; width: 130px;">
          <div class="composer-menu text-sm" style="cursor: pointer; font-weight: bold; padding-left: 40px; border: 1px solid #000;">Main Menu</div>
          <div class="text-xs" style="display: inline-block; border: 1px solid #000;">
            <a class="text-sm" id="app_composer-frameMenuPrev" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (APP_ENV == 'development' ? '#!' : '') ?>"> &lt; Menu</a> | <a class="text-sm" id="app_composer-frameMenuNext" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (APP_ENV == 'development' ? '#!' : '') ?>">Init &gt;</a>
          </div>
        </div>
        <div class="absolute" style="position: absolute; display: inline-block; top: 4px; text-align: right; width: 264px; ">
          <div class="text-xs" style="display: inline-block;">
          + 960 <a href="https://github.com/composer/composer/graphs/contributors">contributors</a>
          <br />
          <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;" href="http://getcomposer.org/">http://getcomposer.org/</a>
          </div>
<!--
          <select id="frameSelector">
            <option value="0" selected>---</option>
            <option value="1">Update</option>
            <option value="2">Config</option>
            <option value="3">Initial</option>
            <option value="4">Install</option>
          </select>
-->
        </div>
        <div style="clear: both;"></div>
      </div>
      <div class="absolute" style="position: absolute; bottom: 60px; right: 0; margin: 0 auto; width: 225px; text-align: right;">
        <form action="?" method="POST" class="text-sm">
          <input type="hidden" name="update" value="" />composer.lock requires an <button type="submit" style="border: 1px solid #000; z-index: 3;">Update</button>
        </form>
      </div>
      <div class="absolute" style="position: absolute; margin: 0px auto; text-align: center; height: 275px; width: 100%; background-repeat: no-repeat; <?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? "background-image: url('https://editablegifs.com/gifs/gifs/fireworks-1/output.gif?egv=3258')" : '') ?> ;">
      </div>

      <div class="absolute" style="position: absolute; top: 0; left: 0; right: 0; margin: 10px auto; opacity: 1.0; text-align: center; cursor: pointer; z-index: 1;">
        <img class="<?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? 'composer-update' : 'composer-menu') ?>" src="resources/images/composer.fw.png" style="margin-top: 45px;" width="150" height="198" />
      </div>

      <div class="absolute" style="position: absolute; bottom: 40px; left: 0; right: 0; width: 100%; text-align: center; z-index: 1; ">
      <form action="#!" method="POST">
        <input type="hidden" name="composer[create-project]" value="" />
        <span style="pdding-left: 125px"></span>
        <select name="composer[package]" onchange="this.form.submit();">
          <option value="" selected>create-project</option>
          <option value="laravel/laravel">laravel/laravel</option>
          <option value="symfony/skeleton">symfony/skeleton</option>
        </select>
        <span>/project/*</span>
      </form>
      </div>

      <div class="absolute" style="position: absolute; bottom: 24px; left: 0; right: 0; width: 100%; text-align: center;">
        <span style="text-decoration-line: underline; text-decoration-style: solid;">A Dependency Manager for PHP</span>
      </div>

      <div style="position: absolute; bottom: 0; left: 0; padding: 2px; z-index: 1;">
        <a href="https://github.com/composer/composer"><img src="resources/images/github-composer.fw.png" /></a>
      </div>
      
      <div class="absolute text-sm" style="position: absolute; bottom: 0; right: 0; padding: 2px; z-index: 1; "><?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? '<code>Latest: </code><span class="update" style="color: green; cursor: pointer;">' . 'v'.substr(COMPOSER_LATEST, 0, similar_text(COMPOSER_LATEST, COMPOSER_VERSION)). substr(COMPOSER_LATEST, similar_text(COMPOSER_LATEST, COMPOSER_VERSION))  . '</span>': 'Installed: v' . COMPOSER_VERSION ); ?></div>
      <div style="position: relative; overflow: hidden; width: 398px; height: 250px;">
<?php

$count = 0;
if (defined('COMPOSER') && isset(COMPOSER->require))
  foreach (COMPOSER->require as $key => $require) {
    if (preg_match('/.*\/.*:.*/', $key . ':' . $require)) 
      if (preg_match('/(.*)\/.*/', $key, $match))
        if (!empty($match) && !is_dir('vendor/'.$match[1].'/')) $count++;
  }
?>      
      <div id="app_composer-frameMenu" class="app_composer-frame-container <?=($count >= 1 ? '' : 'selected' ); ?> absolute" style="background-color: rgb(225,196,151,.75); margin-top: 8px;">
        <!--<h3>Main Menu</h3> <h4>Update - Edit Config - Initalize - Install</h4> -->

        <div style="display: block; margin: 5px auto;">
          <div class="drop-shadow-2xl font-bold" style="display: inline-block; width: 192px; margin: 10px auto; text-align: right; cursor: pointer;">
            <div id="app_composer-frameMenuInit" style="text-align: center; padding-left: 18px;"><img style="display: block; margin: auto;" src="resources/images/initial_icon.fw.png" width="70" height="57" />Init</div>
          </div>
        
          <div class="config drop-shadow-2xl font-bold" style="display: inline-block; width: 192px; margin: 0px auto; text-align: center; cursor: pointer;">
            <div id="app_composer-frameMenuConf"  class="" style="text-align: center;"><img style="display: block; margin: auto;" src="resources/images/folder.fw.png" width="70" height="58" />Config</div>
          </div>
        </div>
        <div style="display: block; margin: 4px auto;">
          <div class="install drop-shadow-2xl font-bold" style="display: inline-block; width: 192px; margin: 0 auto; text-align: right; cursor: pointer;">
            <div id="app_composer-frameMenuInstall"  style="position: relative; text-align: center; padding-left: 15px;">
              <div style="position: absolute; top: -10px; left: 130px; color: red;"><?=($count >= 1 ? $count : '' ); ?></div>
              <img style="display: block; margin: auto;" src="resources/images/install_icon.fw.png" width="54" height="54" />Install</div>
          </div>
          <div class="drop-shadow-2xl font-bold" style="display: inline-block; width: 192px; margin: 0 auto; text-align: center; cursor: pointer;">
            <div id="app_composer-frameMenuUpdate" style="text-align: center; "><img style="display: block; margin: auto;" src="resources/images/update_icon.fw.png" width="54" height="54" /><a href="#!">Update<?=/*Now!*/NULL; ?></a></div>
          </div>
        </div>
        <div style="height: 10px;"></div>
      </div>
<?php ob_start(); ?>
      <div id="app_composer-frameUpdate" class="app_composer-frame-container absolute" style="overflow: scroll; background-color: rgb(225,196,151,.75);">
    <form autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'composer')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="POST">
      <input type="hidden" name="composer[update]" value="" />
      <div style="position: absolute; right: 0; float: right; text-align: center;">
        <input class="btn" id="composerSetupSubmit" type="submit" value="self-update">
      </div>
      <div style="display: inline-block; width: 100%; margin: 0 auto;">
        <div class="text-sm" style="display: inline;">
          <label id="composerSetupLabel" for="composerSetup" style="background-color:hsl(89, 100%, 42%); color: white; text-decoration: underline; cursor: pointer; font-weight: bold;">&#9650; <code>Setup / Update</code></label>
        </div>
        <span style="background-color: white;">
        <span class="text-sm" style="display: inline-block;">was <?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') == 0 ? '<span style="font-weight: bold;">not</span>' : '')?> found: </span>
        </span>
      </div>

      <div id="composerSetupForm" style="display: inline-block; padding: 5px; background-color: rgba(0,0,0,.03); border: 1px dashed #0078D7;">
        <div>
        <span class="text-xs" style="background-color: #0078D7; color: white;"><code>Version: (Installed) <?= COMPOSER_VERSION ?> -> (Latest) <?= COMPOSER_LATEST ?></code></span>
        </div>
        <label>Composer Command</label>
        <textarea style="width: 100%" cols="40" rows="5" name="composer[cmd]">php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar -v
sudo composer self-update</textarea>
      </div>
    </form>
    </div>
<?php
$frameUpdateContents = ob_get_contents();
ob_end_clean(); ?>

      <?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') == 0 ? NULL : $frameUpdateContents); ?>

      <div id="app_composer-frameInit" class="app_composer-frame-container absolute <?= (realpath(COMPOSER_JSON['path']) ? '' : (defined('COMPOSER')  && is_object(COMPOSER) && count((array)COMPOSER) !== 0 ? '' : 'selected')); ?>" style="overflow: hidden; height: 270px;">
<?php if (!defined('CONSOLE') && CONSOLE != true) { ?>
    <form autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'composer')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="POST">
<?php } ?>
      <div style="position: absolute; right: 0; float: right; text-align: center;">
          <button id="app_composer-init-submit" class="btn" type="submit" value>Init/Run</button>
      </div>
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="composerInitLabel" for="composerInit" style="background-color: #6781B2; color: white;">&#9650; <code>Init</code></label>
        </div>
      </div>
      <div id="composerInitForm" style="display: inline-block; padding: 10px; background-color: rgba(235, 216, 186, 0.8); border: 1px dashed #0078D7;">
        <label>Composer Command</label>
        <textarea id="app_composer-init-input" style="width: 100%" cols="40" rows="6" name="composer[init]" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?= preg_replace('/\s--/', "\n--", COMPOSER_INIT_PARAMS); ?></textarea>
      </div>
<?php if (!defined('CONSOLE') && CONSOLE != true) { ?>
    </form>
<?php } ?>
      </div>

      <div id="app_composer-frameConf" class="app_composer-frame-container absolute <?= (!defined('COMPOSER') && is_file(APP_PATH . 'composer.json') ? 'selected' : ''); ?>" style="overflow-x: hidden; overflow-y: auto; height: 230px;">
    <form autocomplete="off" spellcheck="false" action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + array('app' => 'composer')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="POST">
      <input type="hidden" name="composer[config]" value="" />

      <div style="position: absolute; right: 0; float: right; text-align: center; z-index: 2;">
        <button class="btn absolute" id="composerJsonSubmit" type="submit" style="position: absolute; top: 0; right: 0;" value=""><?= (defined('COMPOSER_AUTH') && realpath(COMPOSER_AUTH['path']) ? 'Modify' : 'Create' ); ?></button>
      </div> 
      <div style="position: relative; display: inline-block; width: 100%; background-color: rgb(225,196,151,.25); z-index: 1;">
        <div class="text-sm" style="display: inline;">
          <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="composerJson" value="true" checked=""> -->
          <label for="composerJson" id="appComposerAuthLabel" title="<?= (defined('COMPOSER_AUTH') && realpath(COMPOSER_AUTH['path']) ? COMPOSER_AUTH['path'] : COMPOSER_AUTH['path']) /*NULL*/;?>" style="background-color: #6B4329; <?= (defined('COMPOSER_JSON') && realpath(COMPOSER_AUTH['path']) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color: red; text-decoration: underline; text-decoration: line-through;') ?> cursor: pointer; font-weight: bold;">&#9660; <code>COMPOSER_HOME/auth.json</code></label>
        </div>
      </div>
      <div id="appComposerAuthJsonForm" style="display: none; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
        <a class="text-sm" style="color: blue; text-decoration: underline;" href="https://github.com/settings/tokens?page=1">GitHub OAuth Token</a>:
        <span class="text-sm" style="float: right;"><?= ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+30 days',filemtime(COMPOSER_AUTH['path']))))) / 86400)) ?> (Days left)</span>
        <div style="float: right;">
          <input type="text" size="40" name="auth[github_oauth]" value="<?= COMPOSER_AUTH['token'] ?>" />
        </div>
        <div style="clear: both;"></div>
      </div>

      <div style="position: relative; display: inline-block; width: 100%; background-color: rgb(225,196,151,.25); z-index: 1;">
        <div class="text-sm" style="display: inline;">
          <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="composerJson" value="true" checked=""> -->
          <label for="composerJson" id="appComposerConfigLabel" title="<?= (defined('COMPOSER_CONFIG') && realpath(COMPOSER_CONFIG['path']) ? COMPOSER_CONFIG['path'] : COMPOSER_CONFIG['path']) /*NULL*/;?>" style="background-color: #6B4329; <?= (defined('COMPOSER_CONFIG') && realpath(COMPOSER_CONFIG['path']) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color: red; text-decoration: underline; text-decoration: line-through;') ?> cursor: pointer; font-weight: bold;" >&#9660; <code>COMPOSER_HOME/config.json</code></label>
        </div>
      </div>
      <div id="appComposerConfigJsonForm" style="display: none; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
        <a class="text-sm" style="color: blue; text-decoration: underline;" href="https://github.com/settings/tokens?page=1">GitHub OAuth Token</a>:
        <span class="text-sm" style="float: right;"></span>
        <div style="float: right;">
          <input type="text" size="40" name="config[github_oauth]" value="<?= COMPOSER_AUTH['token'] ?>" disabled />
        </div>
        <div style="clear: both;"></div>
        <a class="text-sm" style="color: blue; text-decoration: underline;" href="">Platform</a>:
        <span class="text-sm" style="float: right;"></span>
        <div style="float: right;">
          <input type="text" size="40" name="config[platform]" value="php:^7.4||^8.1" disabled />
        </div>
        <div style="clear: both;"></div>
      </div>

      <div style="position: relative; display: inline-block; background-color: rgb(225,196,151,.25); width: 100%; z-index: 1;">
<?php //if (defined('COMPOSER_JSON')) $composer = json_decode(COMPOSER_JSON['json']); ?>
        <div class="text-sm" style="display: inline;">
          <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="" value="true" checked=""> -->
          <label for="composerJson" id="appComposerVendorJsonLabel" class="text-sm" style="background-color: #6B4329; <?= (defined('VENDOR_JSON') && realpath(VENDOR_JSON['path']) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color:red; text-decoration: underline; text-decoration: line-through;') ?> cursor: pointer; font-weight: bold;" title="<?= (defined('VENDOR_JSON') && realpath(VENDOR_JSON['path']) ? VENDOR_JSON['path'] : '') /*NULL*/; ?>">&#9650; <code>COMPOSER_PATH/[vendor/*].json</code></label>
          <div class="text-xs" style="display: <?= (!is_file(APP_PATH . 'composer.lock') ? 'none' : 'inline-block' )?>; padding-top: 5px; padding-right: 10px; float: right;"></div>
        </div>
      </div>

      <div id="appComposerVendorJsonForm" style="position: relative; display: inline-block; overflow-x: hidden; overflow-y: auto; height: auto; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7; width: 100%;">
<?php if (defined('VENDOR')) { ?>

      
<?php if (defined('VENDOR_JSON') && realpath(VENDOR_JSON['path'])) { ?>
      <div style="display: block; width: 100%; margin-bottom: 10px;">
        <div class="text-xs" style="display: inline-block; float: left; background-color: #0078D7; color: white;">Last Update: <span <?= (isset(VENDOR->time) && VENDOR->time === '' ? 'style="background-color: white; color: red;"' : 'style="background-color: white; color: #0078D7;"') ?>><?= (isset(VENDOR->time) && VENDOR->time !== '' ? VENDOR->{'time'} : date('Y-m-d H:i:s')) ?></span></div>

      </div>
<?php } ?>


      <div style="display: inline-block; width: 100%;"><span <?= (isset(VENDOR->{'name'}) && VENDOR->{'name'} !== '' ? '' : 'style="background-color: #fff; color: red;" title="Either Vendor or Package is missing"') ?>>Vendor/Package:</span>
        <div style="position: relative; float: right;"><?php

$keys = array_keys(get_object_vars(COMPOSER->{'require'}));

if (!empty(get_object_vars(COMPOSER->{'require-dev'})))
  $keys = array_merge($keys, array_keys(get_object_vars(COMPOSER->{'require-dev'})));

?>
          <select onselect="selectPackage()">
            <option>---</option>
<?php 
  foreach($keys as $package) {
    if ($package == 'php') continue;
    elseif (isset(COMPOSER->{'require'}->{$package}))
      echo '<option selected>' . $package . '</option>';
    else echo '<option>' . $package . '</option>';
  }
?>
          </select>
        </div>
      </div>

      <div style="display: inline-block; width: 100%;"><label for="description" <?= (isset(VENDOR->{'description'}) && VENDOR->{'description'} !== '' ? '' : 'style="background-color: #fff; color: red; cursor: pointer;" title="Description is missing"') ?>>Description:</label>
        <div style="float: right;">
          <input id="description" type="text" name="" placeholder="Details" value="<?= (defined('VENDOR') && isset(VENDOR->description)? VENDOR->description : ''); ?>">
        </div>
      </div>

      <!-- version -->
      <div style="display: inline-block; width: 100%;"><label for="version" <?= (isset(VENDOR->{'version'}) && preg_match(COMPOSER_EXPR_VER, VENDOR->{'version'}) ? '' : 'style="background-color: #fff; color: red; cursor: pointer;" title="Version must follow this format: ' . COMPOSER_EXPR_VER . '"') ?>>Version:</label>
        <div style="float: right;">
          <input id="version" type="text" name="" size="10" placeholder="(Version) 1.2.3" style="text-align: right;" pattern="(\d+\.\d+(?:\.\d+)?)" value="<?= (defined('VENDOR') && isset(VENDOR->version) ? VENDOR->version : ''); ?>">
        </div>
      </div>
      <!-- type -->
      <div style="display: inline-block; width: 100%;">Type:
        <div style="float: right;">
          <select name="">
            <option label="" <?= (defined('VENDOR') && isset(VENDOR->license) ? '' : 'selected=""');?>></option>
<?php foreach (['library', 'project', 'metapackage', 'composer-plugin'] as $type) { ?>
            <option<?= (defined('VENDOR') && isset(VENDOR->type) && VENDOR->type == $type ? ' selected=""' : '' ); ?>><?= $type; ?></option>
<?php } ?>
          </select>
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">Keywords:
        <div style="float: right;">
          <input type="text" placeholder="Keywords" value="">
        </div>
        <div class="clearfix"></div>
        <div id="composerAppendKeyword" style="padding: 10px 0 10px 0; display: <?= (defined('VENDOR') && isset(VENDOR->keywords) && !empty(VENDOR->keywords) ? 'block' : 'none') ?>; width: 100%;">
<?php if (defined('VENDOR') && isset(VENDOR->keywords)) foreach (VENDOR->keywords as $key => $keyword) { ?>
          <label for="keyword_<?= $key; ?>"><sup onclick="rm_keyword(\'keyword_<?= $key; ?>\');">[x]</sup><?= $keyword; ?></label>&nbsp;
<?php } ?>
        </div>
      </div>
      <!-- homepage -->
      <!-- readme -->
      <!-- time -->
      <!-- version_normalized -->
      <div style="display: inline-block; width: 100%;">License:
        <div style="float: right;">
          <select name="">
            <option label=""<?= (defined('VENDOR') && isset(VENDOR->license) ? '' : ' selected=""' );?>></option>
<?php foreach (['WTFPL', 'GPL-3.0', 'MIT'] as $license) { ?>
            <option <?= (defined('VENDOR') && isset(VENDOR->license) && VENDOR->license == $license ? 'selected=""' : '' ); ?>><?= $license; ?></option>
<?php } ?>
          </select>
        </div>
      </div>
      <!-- authors -->
      <div style="display: inline-block; width: 100%;">Authors:<br />

<?php if (defined('VENDOR') && isset(VENDOR->authors)) foreach (VENDOR->authors as $key => $author) { ?>
        <div style="position: relative; float: left;">
          <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name</div>
          <input type="text" id="tst" name="" placeholder="name" value="<?= $author->{'name'} ?>" size="10"> /
          <div class="absolute font-bold" style="position: absolute; top: -8px; right: 134px; font-size: 10px;">Email</div>
          <input type="text" id="tst" name="" placeholder="email" value="<?= $author->{'email'} ?>" size="18" />   
        </div>
        <div class="dropdown">
          <div id="myDropdown" class="dropdown-content">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key2 => $role) { ?>
            <a href="#!"><img style="float: left;" width="30" height="33" src="resources/images/role<?=$key2?>.fw.png"><?= $role; ?> <input type="radio" id="<?=$key2?>" style="float: right; cursor: pointer;" name="" value="<?= $role; ?>" <?= (isset($author->{'role'}) && $author->{'role'} == $role ? ' checked=""' : '' ) ?> /></a>
<?php } ?>
          </div>
          <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
        </div>

<?php } else { ?>

        <div style="position: relative; float: left;">
          <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name</div>
          <input type="text" id="tst" name="" placeholder="name" value="Barry Dick" size="10"> / 
          <div class="absolute font-bold" style="position: absolute; top: -8px; right: 140px; font-size: 10px;">Email</div>
          <input type="text" id="tst" name="" placeholder="email" value="barryd.it@gmail.com" size="18" />   
        </div>&nbsp;

        <div class="dropdown">
          <div id="myDropdown" class="dropdown-content">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key => $role) { ?>
            <a href="#!"><img style="float: left;" width="30" height="33" src="resources/images/role<?=$key?>.fw.png"><?= $role; ?> <input type="radio" id="<?=$key?>" style="float: right; cursor: pointer;" name="" value="<?= $role; ?>" /></a>
<?php } ?>
          </div>
          <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
        </div>

<!--
          <select name="">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $role) { ?>
            <option<?= (defined('COMPOSER') && isset(COMPOSER->authors) && COMPOSER->authors->role ? 'value="' . $role . '"' : '') && (defined('COMPOSER') && isset(COMPOSER->authors) && COMPOSER->authors->role == $role ? ' selected=""' : '' ); ?>><?= $role; ?></option>
<?php } ?>
          </select>
-->
        
<!--        <label for="author_<?= $key; ?>"><sup onclick="rm_author(\'author_<?= $key; ?>\');">[x]</sup>' + event.target.value + '</label><input type="hidden" id="author_<?= $key; ?>" name="" value="' + event.target.value + '" />&nbsp; -->
<?php } ?>

      </div>
      
      <!-- source -->
      <!-- dist -->

      <!-- funding -->


<!--
    "require": {
        "php": ">=5.3.0"
    },
    "autoload": {
        "psr-4": {
            "ResponseClass\\":"src/"
        }
    },
    "config":{
        "optimize-autoloader": true
    }
-->
      
      <div style="display: inline-block; width: 100%;"><hr />Require:
        <div style="float: right;">
          <input type="text" title="Enter Text and onSelect" placeholder="" value="">
        </div>
        <div style="padding: 10px; display: <?= (defined('VENDOR') && !isset(VENDOR->{'require'}) ? 'none' : 'block') ?>;">
<?php $i = 0; if (defined('VENDOR') && isset(VENDOR->{'require'})) {
  if (!isset(VENDOR->{'require'}->{'php'})) { ?>
          <input type="checkbox" checked="" />
          <input type="text" value="<?= 'php:^' . PHP_VERSION ?>" size="30" />
          <label for="pkg_<?= $i; ?>"></label><br />
<?php $i++; } foreach (VENDOR->{'require'} as $key => $require) { ?>
          <input type="checkbox" checked="" />
          <input type="text" name="" value="<?= $key . ':' . $require ?>" size="30" />
          <label for="pkg_<?= $i; ?>"></label><br />
<?php $i++; } } else { ?>
          <input type="checkbox" checked="" />
          <input type="text" id="pkg_<?= $i; ?>" name="" value="<?= 'php:^' . PHP_VERSION ?>" size="30" />
          <label for="pkg_<?= $i; ?>"></label><br />
<?php } ?>
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">Require-dev:
        <div style="float: right;">
          <input type="text" placeholder="" name="" value="" />
        </div>
        <div style="padding: 10px; display: <?= (defined('VENDOR') && !isset(VENDOR->{'require-dev'}) ? 'none' : 'block') ?>;">
<?php $i = 0; if (defined('VENDOR') && isset(VENDOR->{'require-dev'})) foreach (VENDOR->{'require-dev'} as $key => $require) { ?>
          <input type="checkbox" checked="" />
          <input type="text" id="pkg-dev_<?= $i; ?>" name="" value="<?= $key . ':' . $require ?>" size="30" />
          <label for="pkg-dev_<?= $i; ?>"></label><br />
<?php $i++; } ?>
        </div>
      </div>

      <div style="display: inline-block; width: 100%;">Autoload:
        <div style="float: right;">
          <input type="text" name="" placeholder="Autoload" value="">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">Autoload-dev:
        <div style="float: right;">
          <input type="text" name="" placeholder="Autoload-dev" value="">
        </div>
      </div>
      
      <div style="display: inline-block; width: 100%;">Minimum-Stability:
        <div style="float: right;">
          <select name="">
<?php if (defined('VENDOR')) foreach (['stable', 'rc', 'beta', 'alpha', 'dev'] as $ms) { ?>
            <option value="<?= $ms ?>"<?= (isset(VENDOR->{'minimum-stability'}) && VENDOR->{'minimum-stability'} == $ms ? ' selected=""' : '' )?>><?= $ms ?></option>
<?php } ?>
          </select>
        </div>
      </div>
        <div style="padding: 10px; width: 100%;">

        </div>

<?php } ?>

      </div>


      <div style="position: relative; display: inline-block; background-color: rgb(225,196,151,.25); width: 100%; z-index: 1;">
<?php //if (defined('COMPOSER_JSON')) $composer = json_decode(COMPOSER_JSON['json']); ?>
        <div class="text-sm" style="display: inline;">
          <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="composerJson" value="true" checked=""> -->
          <label for="composerJson" id="appComposerJsonLabel" class="text-sm" style="background-color: #6B4329; <?= (defined('COMPOSER_JSON') && realpath(COMPOSER_JSON['path']) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color:red; text-decoration: underline; text-decoration: line-through;') ?> cursor: pointer; font-weight: bold;" title="<?= (defined('COMPOSER_JSON') && realpath(COMPOSER_JSON['path']) ? COMPOSER_JSON['path'] : COMPOSER_JSON['path']) /*NULL*/; ?>">&#9650; <code>COMPOSER_PATH/composer.json</code></label>
          <div class="text-xs" style="display: <?= (!is_file(APP_PATH . 'composer.lock') ? 'none' : 'inline-block' )?>; padding-top: 5px; padding-right: 10px; float: right;"><input type="checkbox" name="composer[lock]" value="" /> <span style="background-color: white; color: red; text-decoration: line-through;">composer.lock</span></div>
        </div>
      </div>
      <div id="appComposerJsonForm" style="position: relative; display: inline-block; overflow-x: hidden; overflow-y: auto; height: auto; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
<?php if (defined('COMPOSER_JSON') && realpath(COMPOSER_JSON['path'])) { ?>
      <div style="display: inline-block; width: 100%; margin-bottom: 10px;">
        <div class="text-xs" style="display: inline-block; float: left; background-color: #0078D7; color: white;">Last Update: <span <?= (isset(COMPOSER->time) && COMPOSER->time === '' ? 'style="background-color: white; color: red;"' : 'style="background-color: white; color: #0078D7;"') ?>><?= (isset(COMPOSER->time) && COMPOSER->time !== '' ? COMPOSER->{'time'} : date('Y-m-d H:i:s')) ?></span></div>
        
        
        <div class="text-xs" style="display: inline-block; float: right;">
          <input type="checkbox" name="composer[update]" value="" checked /> <span style="background-color: #0078D7; color: white;">Update</span>
          <input type="checkbox" name="composer[install]" value="" checked /> <span style="background-color: #0078D7; color: white;">Install</span>
        </div>
      </div>
<?php } ?>
      <div style="display: inline-block; width: 100%;"><span <?= (isset(COMPOSER->{'name'}) && COMPOSER->{'name'} !== '' ? '' : 'style="background-color: #fff; color: red;" title="Either Vendor or Package is missing"') ?>>Name:</span>
        <div style="position: relative; float: right;">
          <div class="absolute font-bold" style="position: absolute; top: -8px; left: 5px; font-size: 10px; z-index: 1;">Vendor</div>
          <input type="text" id="tst" name="composer[config][name][vendor]" placeholder="vendor" value="<?= (defined('COMPOSER') && isset(COMPOSER->name) ? explode('/', COMPOSER->name)[0] : ''); ?>" size="13"> / <div class="absolute font-bold" style="position: absolute; top: -8px; right: 82px; font-size: 10px; z-index: 1;">Package</div> <input type="text" id="tst" name="composer[config][name][package]" placeholder="package" value="<?= (defined('COMPOSER') && isset(COMPOSER->name)? explode('/', COMPOSER->name)[1] : ''); ?>" size="13" />   
        </div>
      </div>
      <div style="display: inline-block; width: 100%;"><label for="composer-description" <?= (isset(COMPOSER->{'description'}) && COMPOSER->{'description'} !== '' ? '' : 'style="background-color: #fff; color: red; cursor: pointer;" title="Description is missing"') ?>>Description:</label>
        <div style="float: right;">
          <input id="composer-description" type="text" name="composer[config][description]" placeholder="Details" value="<?= (defined('COMPOSER') && isset(COMPOSER->description)? COMPOSER->description : ''); ?>">
        </div>
      </div>
      
      <!-- version -->
      <div style="display: inline-block; width: 100%;"><label for="composer-version" <?= (isset(COMPOSER->{'version'}) && preg_match(COMPOSER_EXPR_VER, COMPOSER->{'version'}) ? '' : 'style="background-color: #fff; color: red; cursor: pointer;" title="Version must follow this format: ' . COMPOSER_EXPR_VER . '"') ?>>Version:</label>
        <div style="float: right;">
          <input id="composer-version" type="text" name="composer[config][version]" size="10" placeholder="(Version) 1.2.3" style="text-align: right;" pattern="(\d+\.\d+(?:\.\d+)?)" value="<?= (defined('COMPOSER') && isset(COMPOSER->version) ? COMPOSER->version : ''); ?>">
        </div>
      </div>
      <!-- type -->
      <div style="display: inline-block; width: 100%;">Type:
        <div style="float: right;">
          <select name="composer[config][type]">
            <option label="" <?= (defined('COMPOSER') && isset(COMPOSER->license) ? '' : 'selected=""');?>></option>
<?php foreach (['library', 'project', 'metapackage', 'composer-plugin'] as $type) { ?>
            <option<?= (defined('COMPOSER') && isset(COMPOSER->type) && COMPOSER->type == $type ? ' selected=""' : '' ); ?>><?= $type; ?></option>
<?php } ?>
          </select>
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">Keywords:
        <div style="float: right;">
          <input id="composerKeywordAdd" type="text" placeholder="Keywords" value="" onselect="add_keyword()">
        </div>
        <div class="clearfix"></div>
        <div id="composerAppendKeyword" style="padding: 10px 0 10px 0; display: <?= (defined('COMPOSER') && isset(COMPOSER->keywords) && !empty(COMPOSER->keywords) ? 'block' : 'none') ?>; width: 100%;">
<?php if (defined('COMPOSER') && isset(COMPOSER->keywords)) foreach (COMPOSER->keywords as $key => $keyword) { ?>
          <label for="keyword_<?= $key; ?>"><sup onclick="rm_keyword(\'keyword_<?= $key; ?>\');">[x]</sup><?= $keyword; ?></label><input type="hidden" id="keyword_<?= $key; ?>" name="composer[config][keywords][]" value="<?= $keyword; ?>" />&nbsp;
<?php } ?>
        </div>
      </div>
      <!-- homepage -->
      <!-- readme -->
      <!-- time -->
      <!-- version_normalized -->
      <div style="display: inline-block; width: 100%;">License:
        <div style="float: right;">
          <select name="composer[config][license]">
            <option label=""<?= (defined('COMPOSER') && isset(COMPOSER->license) ? '' : ' selected=""' );?>></option>
<?php foreach (['WTFPL', 'GPL-3.0', 'MIT'] as $license) { ?>
            <option<?= (defined('COMPOSER') && isset(COMPOSER->license) && COMPOSER->license == $license ? ' selected=""' : '' ); ?>><?= $license; ?></option>
<?php } ?>
          </select>
        </div>
      </div>
      <!-- authors -->
      <div style="display: inline-block; width: 100%;">Authors:<br />

<?php if (defined('COMPOSER') && isset(COMPOSER->authors)) foreach (COMPOSER->authors as $key => $author) { ?>
        <div style="position: relative; float: left;">
          <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name</div>
          <input type="text" id="tst" name="composer[config][authors][<?= $key ?>][name]" placeholder="name" value="<?= $author->{'name'} ?>" size="10"> /
          <div class="absolute font-bold" style="position: absolute; top: -8px; right: 134px; font-size: 10px;">Email</div>
          <input type="text" id="tst" name="composer[config][authors][<?= $key ?>][email]" placeholder="email" value="<?= $author->{'email'} ?>" size="18" />   
        </div>
        <div class="dropdown">
          <div id="myDropdown" class="dropdown-content">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key2 => $role) { ?>
            <a href="#!"><img style="float: left;" width="30" height="33" src="resources/images/role<?=$key2?>.fw.png"><?= $role; ?> <input type="radio" id="<?=$key2?>" style="float: right; cursor: pointer;" name="composer[config][authors][<?= $key ?>][role]" value="<?= $role; ?>" <?= (isset($author->{'role'}) && $author->{'role'} == $role ? ' checked=""' : '' ) ?> /></a>
<?php } ?>
          </div>
          <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
        </div>

<?php } else { ?>

        <div style="position: relative; float: left;">
          <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name</div>
          <input type="text" id="tst" name="composer[config][authors][0][name]" placeholder="name" value="Barry Dick" size="10"> / 
          <div class="absolute font-bold" style="position: absolute; top: -8px; right: 140px; font-size: 10px;">Email</div>
          <input type="text" id="tst" name="composer[config][authors][0][email]" placeholder="email" value="barryd.it@gmail.com" size="18" />   
        </div>&nbsp;

        <div class="dropdown">
          <div id="myDropdown" class="dropdown-content">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key => $role) { ?>
            <a href="#!"><img style="float: left;" width="30" height="33" src="resources/images/role<?=$key?>.fw.png"><?= $role; ?> <input type="radio" id="<?=$key?>" style="float: right; cursor: pointer;" name="composer[config][authors][0][role]" value="<?= $role; ?>" /></a>
<?php } ?>
          </div>
          <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
        </div>

<!--
          <select name="composerAuthorRole">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $role) { ?>
            <option<?= (defined('COMPOSER') && isset(COMPOSER->authors) && COMPOSER->authors->role ? 'value="' . $role . '"' : '') && (defined('COMPOSER') && isset(COMPOSER->authors) && COMPOSER->authors->role == $role ? ' selected=""' : '' ); ?>><?= $role; ?></option>
<?php } ?>
          </select>
-->
        
<!--        <label for="author_<?= $key; ?>"><sup onclick="rm_author(\'author_<?= $key; ?>\');">[x]</sup>' + event.target.value + '</label><input type="hidden" id="author_<?= $key; ?>" name="composerAuthors[]" value="' + event.target.value + '" />&nbsp; -->
<?php } ?>

      </div>
      
      <!-- source -->
      <!-- dist -->

      <!-- funding -->


<!--
    "require": {
        "php": ">=5.3.0"
    },
    "autoload": {
        "psr-4": {
            "ResponseClass\\":"src/"
        }
    },
    "config":{
        "optimize-autoloader": true
    }
-->
      
      <div style="display: inline-block; width: 100%;"><hr />Require:
        <div style="float: right;">
          <input id="composerReqPkg" type="text" title="Enter Text and onSelect" list="composerReqPkgs" placeholder="" value="" onselect="get_package(this);">
          <datalist id="composerReqPkgs">
            <option value=""></option>
          </datalist>
        </div>
        <div id="composerAppendRequire" style="padding: 10px; display: <?= (defined('COMPOSER') && !isset(COMPOSER->{'require'}) ? 'none' : 'block') ?>;">
          <datalist id="composerReqVersResults">
            <option value=""></option>
          </datalist>
<?php $i = 0; if (defined('COMPOSER') && isset(COMPOSER->{'require'})) {
  if (!isset(COMPOSER->{'require'}->{'php'})) { ?>
          <input type="checkbox" checked="" onchange="this.indeterminate = !this.checked; document.getElementById('pkg_<?= $i; ?>').disabled = !this.checked">
          <input type="text" id="pkg_<?= $i; ?>" name="composer[config][require][]" value="<?= 'php:^' . PHP_VERSION ?>" list="composerReqVersResults" size="30" onselect="get_version('pkg_<?= $i; ?>')">
          <label for="pkg_<?= $i; ?>"></label><br />
<?php $i++; } foreach (COMPOSER->{'require'} as $key => $require) { ?>
          <input type="checkbox" checked="" onchange="this.indeterminate = !this.checked; document.getElementById('pkg_<?= $i; ?>').disabled = !this.checked">
          <input type="text" id="pkg_<?= $i; ?>" name="composer[config][require][]" value="<?= $key . ':' . $require ?>" list="composerReqVersResults" size="30" onselect="get_version('pkg_<?= $i; ?>')">
          <label for="pkg_<?= $i; ?>"></label><br />
<?php $i++; } } else { ?>
          <input type="checkbox" checked="" onchange="this.indeterminate = !this.checked; document.getElementById('pkg_<?= $i; ?>').disabled = !this.checked">
          <input type="text" id="pkg_<?= $i; ?>" name="composer[config][require][]" value="<?= 'php:^' . PHP_VERSION ?>" list="composerReqVersResults" size="30" onselect="get_version('pkg_<?= $i; ?>')">
          <label for="pkg_<?= $i; ?>"></label><br />
<?php } ?>
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">Require-dev:
        <div style="float: right;">
          <input id="composerRequireDevPkg" type="text" placeholder="" value="" list="composerReqDevPackages" onselect="get_dev_package()">
          <datalist id="composerReqDevPackages">
            <option value=""></option>
          </datalist>
        </div>
        <div id="composerAppendRequire-dev" style="padding: 10px; display: <?= (defined('COMPOSER') && !isset(COMPOSER->{'require-dev'}) ? 'none' : 'block') ?>;">
          <datalist id="composerReq-devVersResults">
            <option value=""></option>
          </datalist>
<?php $i = 0; if (defined('COMPOSER') && isset(COMPOSER->{'require-dev'})) foreach (COMPOSER->{'require-dev'} as $key => $require) { ?>
          <input type="checkbox" checked="" onchange="this.indeterminate = !this.checked; document.getElementById('pkg-dev_<?= $i; ?>').disabled = !this.checked">
          <input type="text" id="pkg-dev_<?= $i; ?>" name="composer[config][require-dev][]" value="<?= $key . ':' . $require ?>" list="composerReqVersResults" size="30" onselect="get_version('pkg-dev_<?= $i; ?>')">
          <label for="pkg-dev_<?= $i; ?>"></label><br />
<?php $i++; } ?>
        </div>
      </div>

      <div style="display: inline-block; width: 100%;">Autoload:
        <div style="float: right;">
          <input type="text" name="composer[config][autoload]" placeholder="Autoload" value="">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">Autoload-dev:
        <div style="float: right;">
          <input type="text" name="composer[config][autoload-dev]" placeholder="Autoload-dev" value="">
        </div>
      </div>
      
      <div style="display: inline-block; width: 100%;">Minimum-Stability:
        <div style="float: right;">
          <select name="composer[config][minimum-stability]">
<?php if (defined('COMPOSER')) foreach (['stable', 'rc', 'beta', 'alpha', 'dev'] as $ms) { ?>
            <option value="<?= $ms ?>"<?= (isset(COMPOSER->{'minimum-stability'}) && COMPOSER->{'minimum-stability'} == $ms ? ' selected=""' : '' )?>><?= $ms ?></option>
<?php } ?>
          </select>
        </div>
      </div>
      </div>

      <div style="height: 15px;"></div>

    </form>

      </div>

<?php
$count = 0;
if (defined('COMPOSER') && isset(COMPOSER->require))
  foreach (COMPOSER->require as $key => $require)
    if (preg_match('/.*\/.*:.*/', $key . ':' . $require)) 
      if (preg_match('/(.*\/.*)/', $key, $match))
        if (!empty($match) && !is_dir('vendor/'.$match[1].'/')) $count++;

?>
      <div id="app_composer-frameInstall" class="app_composer-frame-container absolute <?= ($count > 0 ? 'selected' : ''); ?>" style="overflow: scroll; width: 400px; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="<?=APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'composer')) . (APP_ENV == 'development' ? '#!' : '')  /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="POST">  
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <input type="hidden" name="composer[install]" value="" />
        <div style="position: absolute; right: 0; float: right; text-align: center; z-index: 1;">

          <button id="composerInstallSubmit" class="btn" type="submit" style="<?= ($count > 0 ? 'color: red;' : '' ); ?>" value>Install (<?= ($count > 0 ? $count : '' ); ?>)</button>
        </div> 
        <div class="text-sm" style="display: inline;">
          <label id="composerInstallLabel" for="composerInstall" style="background-color: hsl(343, 100%, 42%); color: white; cursor: pointer;">&#9650; <code>Install</code></label>
        </div>

      </div>
<?php if ($count > 0) { ?>
      <div id="" style="display: inline-block; padding: 10px; margin-bottom: 5px; width: 100%; background-color: rgba(235, 216, 186, 0.8);  border: 1px dashed #0078D7;">
      
        Install (vendor/package): 
        <span >
        <ul style="padding-left: 10px;">
<?php
foreach (COMPOSER->require as $key => $require) {
  if (preg_match('/.*\/.*:.*/', $key . ':' . $require)) 
    if (preg_match('/(.*\/.*)/', $key, $match))
      if (!empty($match) && !is_dir('vendor/'.$match[1].'/')) echo '<li style="color: red;"><code class="text-sm">' . $match[1] . ':' . '<span style="float: right">' . $require . '</span>' . "</code></li>\n";
}
?>
        </ul>
        </span>
      </div>
<?php } ?>
      <div id="composerInstallForm" style="display: inline-block; padding: 10px; margin-bottom: 5px; height: 250px; width: 100%; background-color: rgb(225,196,151,.25);  border: 1px dashed #0078D7;">
      <div style="display: inline-block; width: 100%;">
        <label>Self-update <!--(C:\ProgramData\ComposerSetup\bin\composer.phar)--></label>
        <div style="float: right;">
          <input type="checkbox" name="composer[self-update]" value="true" <?= (!file_exists(APP_PATH . 'composer.phar') ? '' : 'checked=""') ?>/>
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Optimize Classes</label>
        <div style="float: right;">
          <input type="checkbox" name="composer[optimize-classes]" checked="">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Update Packages</label>
        <div style="float: right;">
          <input type="checkbox" name="composer[update]" checked="">
        </div>
      </div>
      </div>
    </form>
      </div>

<?php if (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') == 0 ) echo $frameUpdateContents; ?>

      </div>

    </div>
    <!-- future feature: convert div from absolute to fixed. make screen bigger. <div style="position: relative; text-align: right; cursor: pointer; width: 400px; margin: 0 auto; border: 1px solid #000;"> &#9660;</div> -->
  </div>
<?php $appComposer['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
var keyword_i = 0;

/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
function myFunction() {
  document.getElementById("myDropdown").classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}

function rm_keyword(argv_id) {
  var el = document.querySelector('label[for=' + argv_id + ']');
  var input = document.getElementById(argv_id);
  if (el) el.remove();
  if (input) input.remove();
  //console.log(document.getElementById('composerAppendKeyword').childNodes.length);
  if (document.getElementById('composerAppendKeyword').childNodes.length == 4) document.getElementById('composerAppendKeyword').style.display = "none";
}

function add_keyword() {
    if (event.target.value == '') return;
    var filledInputs = $('#composerAppendKeyword').find(':input[type=text]').filter(function() {return !!this.value;}).length;
    document.getElementById('composerAppendKeyword').style.display = "inline-block";
    keywordOption = '<label class="text-sm" for="keyword_' + keyword_i + '" ><sup onclick="rm_keyword(\'keyword_' + keyword_i + '\');">[x]</sup>' + event.target.value + '</label><input type="hidden" id="keyword_' + keyword_i + '" name="composer[config][keywords][]" value="' + event.target.value + '" />&nbsp;';
    keyword_i++;
    document.getElementById('composerAppendKeyword').insertAdjacentHTML('beforeend', keywordOption); // innerHTML += keywordOption
    document.getElementById('composerKeywordAdd').value = '';
}

//document.getElementById("composerAppendKeyword").childElementCount
//var x = $('#composerAppendKeyword').find(':input[type=hidden]').filter(function() {return !!this.value;}).length; 
//alert(x);


document.getElementById("composerReqPkg").addEventListener("input", function(event){
  if(event.inputType == "insertReplacementText" || event.inputType == null) {
    var filledInputs = $('#composerAppendRequire').find(':input[type=text]').filter(function() {return !!this.value;}).length;
    document.getElementById('composerAppendRequire').style.display = "inline-block";
    packageOption = '<input type="checkbox" checked onchange="this.indeterminate = !this.checked; document.getElementById(\'pkg_' + filledInputs + '\').disabled = !this.checked"/> <input type="text" id="pkg_' + filledInputs + '" name="composer[config][require][]" value="' + event.target.value + '" list="composerReqVersResults" size="30" onSelect="get_version(\'pkg_' + filledInputs + '\')" /><label for="pkg_' + filledInputs + '"></label><br />';
    document.getElementById('composerAppendRequire').insertAdjacentHTML('beforeend', packageOption); // innerHTML += packageOption
    event.target.value = "";
  }
});


function selectPackage() { 

}

function get_package(element) { // onSelect="get_package()"
  var val = element.value; // document.getElementById("composerReqPkg")
  console.log(element.id+ 's');
  var url, packagesOption;
  url = 'https://packagist.org/search.json?q=' + val;
  document.getElementById(element.id + 's').innerHTML = '';
  $.getJSON(url, function(data) {
  //populate the packages datalist
    $(data.results).each(function() {
      packagesOption = '<option value="' + this.name + '" />';
      $('#' + element.id + 's').append(packagesOption);
      //console.log(this.favers);
    });
  });
}

function get_version(argv_id) { // onSelect="get_version()"
  var val = document.getElementById(argv_id).value;
  var url, packagesOption;
  //var vendorPkg = val.split("/");

  url = 'https://repo.packagist.org/p2/' + val + '.json'; 
  document.getElementById('composerReqVersResults').innerHTML = '';
  $.getJSON(url, function(data) {
  //populate the packages datalist
    packagesOption = '<option value="' + val + ':dev-master" />';
    $('#composerReqVersResults').append(packagesOption);
    var vers = $(data.packages[val])[0].version.split(/(\d+\.\d+(?:\.\d+)?)/);
    packagesOption = '<option value="' + val + ':^' + vers[1] + '" />';
    $('#composerReqVersResults').append(packagesOption);
/*  
    $(data.packages[val]).each(function() {
      packagesOption = '<option value="' + val + ':^' + this.version + '" />';
      $('#composerReqVersResults').append(packagesOption);
      //console.log(this.version);
    });
*/
  });
}

document.getElementById("composerRequireDevPkg").addEventListener("input", function(event){
  if(event.inputType == "insertReplacementText" || event.inputType == null) {
    var filledInputs = $('#composerAppendRequire-dev').find(':input[type=text]').filter(function() {return !!this.value;}).length;
    document.getElementById('composerAppendRequire-dev').style.display = "inline-block";
    packageOption = '<input type="checkbox" checked onchange="this.indeterminate = !this.checked; document.getElementById(\'pkg-dev_' + filledInputs + '\').disabled = !this.checked"/><input type="text" id="pkg-dev_' + filledInputs + '" name="composerRequireDevPkgs[]" value="' + event.target.value + '" list="composerReq-devVersResults" size="30" onSelect="get_dev_version(\'pkg-dev_' + filledInputs + '\')" /><label for="pkg-dev_' + filledInputs + '"></label><br />';
    document.getElementById('composerAppendRequire-dev').insertAdjacentHTML('beforeend', packageOption); // innerHTML += packageOption
    event.target.value = "";
  }
});

function get_dev_package() { // onSelect="get_dev_package()"
  var val = document.getElementById("composerRequireDevPkg").value;
  var url, packagesOption;
  url = 'https://packagist.org/search.json?q=' + val;
  document.getElementById('composerReqDevPackages').innerHTML = '';
  $.getJSON(url, function(data) {
  //populate the packages datalist
    $(data.results).each(function() {
      packagesOption = "<option value=\"" + this.name + "\" />";
      $('#composerReqDevPackages').append(packagesOption);
      //console.log(this.favers);
    });
  });
}

function get_dev_version(argv_id) { // onSelect="get_version()"
  var val = document.getElementById(argv_id).value;
  var url, packagesOption;
  //var vendorPkg = val.split("/");
  url = 'https://repo.packagist.org/p2/' + val + '.json'; 
  document.getElementById('composerReq-devVersResults').innerHTML = '';
  $.getJSON(url, function(data) {
  //populate the packages datalist
    packagesOption = '<option value="' + val + ':dev-master" />';
    $('#composerReq-devVersResults').append(packagesOption);
    var vers= $(data.packages[val])[0].version.split(/(\d+\.\d+(?:\.\d+)?)/);
    packagesOption = '<option value="' + val + ':^' + vers[1] + '" />';
    $('#composerReq-devVersResults').append(packagesOption);
/*  
    $(data.packages[val]).each(function() {
      packagesOption = '<option value="' + val + ':^' + this.version + '" />';
      $('#composerReq-devVersResults').append(packagesOption);
      //console.log(this.version);
    });
*/
  });
}

//document.getElementById("bottom").style.zIndex = "1";

$(document).ready(function() {
  var composer_frame_containers = $(".app_composer-frame-container");
  var totalFrames = composer_frame_containers.length;
  var currentIndex = 0;
  
  console.log(totalFrames + ' - total frames');

  $("#appComposerAuthLabel").click(function() {
    if ($('#appComposerAuthJsonForm').css('display') == 'none') {
      $('#appComposerAuthLabel').html("&#9650; <code>COMPOSER_HOME/auth.json");
      $('#appComposerAuthJsonForm').slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('#appComposerAuthLabel').html("&#9660; <code>COMPOSER_HOME/auth.json</code>");
      $('#appComposerAuthJsonForm').slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });
  


  $("#appComposerVendorJsonLabel").click(function() {
    if ($('#appComposerVendorJsonForm').css('display') == 'none') {
      $('#appComposerVendorJsonLabel').html("&#9650; <code>COMPOSER_PATH/[vendor/*].json</code>");
      $('#appComposerVendorJsonForm').slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('#appComposerVendorJsonLabel').html("&#9660; <code>COMPOSER_PATH/[vendor/*].json</code>");
      $('#appComposerVendorJsonForm').slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });

  $("#appComposerJsonLabel").click(function() {
    if ($('#appComposerJsonForm').css('display') == 'none') {
      $('#appComposerJsonLabel').html("&#9650; <code>COMPOSER_PATH/composer.json</code>");
      $('#appComposerJsonForm').slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('#appComposerJsonLabel').html("&#9660; <code>COMPOSER_PATH/composer.json</code>");
      $('#appComposerJsonForm').slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });

  $("#app_composer-frameMenuInit").click(function() {
    currentIndex = 1;
    $("#app_composer-frameMenuPrev").html('&lt; Menu');
    $("#app_composer-frameMenuNext").html('Conf &gt;');
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
  });

  $("#app_composer-frameMenuConf").click(function() {
    currentIndex = 2;
    $("#app_composer-frameMenuPrev").html('&lt; Init');
    $("#app_composer-frameMenuNext").html('Install &gt;');
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
  });   
  
  $("#app_composer-frameMenuInstall").click(function() {
    currentIndex = 3;
    $("#app_composer-frameMenuPrev").html('&lt; Conf');
    $("#app_composer-frameMenuNext").html('Update &gt;');
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
  });

  $("#app_composer-frameMenuUpdate").click(function() {
    currentIndex = 4;
    $("#app_composer-frameMenuPrev").html('&lt; Install');
    $("#app_composer-frameMenuNext").html('Menu &gt;');
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
  });   
 
  $(".composer-home").click(function() {
    currentIndex = -1; 
    composer_frame_containers.removeClass("selected");
    //composer_frame_containers.eq(currentIndex).addClass('selected');
  });

  $(".composer-menu").click(function() {
    currentIndex = 0; 
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
  });

  $("#app_composer-frameMenuPrev").click(function() {
    if (currentIndex <= 0) currentIndex = 5;
    console.log(currentIndex + '!=' + totalFrames);
    currentIndex--;
    if (currentIndex >= totalFrames) {
      currentIndex = 0;
    }
    if (currentIndex == 0) {
      $("#app_composer-frameMenuPrev").html('&lt; Update');
      $("#app_composer-frameMenuNext").html('Init &gt;');
    } else if (currentIndex == 1) {
      $("#app_composer-frameMenuPrev").html('&lt; Menu');
      $("#app_composer-frameMenuNext").html('Conf &gt;');
    } else if (currentIndex == 2) {
      $("#app_composer-frameMenuPrev").html('&lt; Init');
      $("#app_composer-frameMenuNext").html('Install &gt;');
    } else if (currentIndex == 3) {
      $("#app_composer-frameMenuPrev").html('&lt; Conf');
      $("#app_composer-frameMenuNext").html('Update &gt;');
    } else if (currentIndex == 4) {
      $("#app_composer-frameMenuPrev").html('&lt; Install');
      $("#app_composer-frameMenuNext").html('Menu &gt;');
    }

    //else 
    console.log('decided: ' + currentIndex);
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
    
    //currentIndex--;    
    console.log(currentIndex);
  });

  $("#app_composer-frameMenuNext").click(function() {
    currentIndex++;
    console.log(currentIndex + '!=' + totalFrames);
    if (currentIndex >= totalFrames) {
      currentIndex = 0;
    }
    if (currentIndex == 0) {
      $("#app_composer-frameMenuPrev").html('&lt; Update');
      $("#app_composer-frameMenuNext").html('Init &gt;');
    } else if (currentIndex == 1) {
      $("#app_composer-frameMenuPrev").html('&lt; Menu');
      $("#app_composer-frameMenuNext").html('Conf &gt;');
    } else if (currentIndex == 2) {
      $("#app_composer-frameMenuPrev").html('&lt; Init');
      $("#app_composer-frameMenuNext").html('Install &gt;');
    } else if (currentIndex == 3) {
      $("#app_composer-frameMenuPrev").html('&lt; Conf');
      $("#app_composer-frameMenuNext").html('Update &gt;');
    } else if (currentIndex == 4) {
      $("#app_composer-frameMenuPrev").html('&lt; Install');
      $("#app_composer-frameMenuNext").html('Menu &gt;');
    }
    if (currentIndex < 0) currentIndex++;
    //else 
    console.log('decided: ' + currentIndex);
    composer_frame_containers.removeClass("selected"); // composer_frame_containers.css("z-index", 0); // Reset z-index for all elements
    composer_frame_containers.eq(currentIndex).addClass('selected'); // css("z-index", totalFrames); // Set top layer z-index
  });
  
  $("#frameSelector").change(function() {
    var selectedIndex = parseInt($(this).val(), 10);
    currentIndex = selectedIndex;
    $(".app_composer-frame-container").removeClass("selected"); // Remove selected class from all containers
    $(".app_composer-frame-container").eq(currentIndex).addClass("selected"); // Apply selected class to the chosen container
  });
/*
  $('select').on('change', function (e) {
    var optionSelected = $("option:selected", this);
    var valueSelected = this.value;
  });
*/
});
<?php $appComposer['script'] = ob_get_contents(); 
ob_end_clean();

/** Loading Time: 5.03s **/
  
  //dd(get_required_files(), true);

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
<?= $appComposer['style']; ?>
</style>
</head>
<body>
<?= $appComposer['body']; ?>

  <script src="<?= (check_http_200('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : $path . 'jquery-3.7.1.min.js') ?>"></script>
  <!-- You need to include jQueryUI for the extended easing options. -->
<?php /* https://stackoverflow.com/questions/12592279/typeerror-p-easingthis-easing-is-not-a-function */ ?>
  <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script> <!-- Uncaught ReferenceError: jQuery is not defined -->

<script>
<?= $appComposer['script']; ?>
</script>
</body>
</html>
<?php $appComposer['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == APP_SELF || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'composer' && APP_DEBUG)
  die($appComposer['html']);

/** Loading Time: 7.0s **/
  
  //dd(get_required_files(), true);
// 5.025 @ 4.992
dd('composer init time: ', false);
/****   END of ui.composer.php   ****/

/* https://stackoverflow.com/questions/73026623/how-to-ignore-or-permanently-block-the-files-which-contain-date-or-datetime-in */


if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../composer.php') ? '../composer.php' : (is_file('../config/composer.php') ? '../config/composer.php' : null))
    : (is_file('composer.php') ? 'composer.php' : (is_file('config/composer.php') ? 'config/composer.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=composer.php'));


/*
if ($path = realpath((!is_dir(dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR . 'config' ? dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' : (!is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'config') ? (basename(__DIR__) != 'config' ? NULL : '.') : __DIR__ . DIRECTORY_SEPARATOR . 'config'))  . DIRECTORY_SEPARATOR . 'git.php')) // realpath() | is_file('config/git.php')) 
  require_once($path);
else die(var_dump($path . ' path was not found. file=git.php'));
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


/*
git reset filename   (unstage a specific file)

git branch
  -m   oldBranch newBranch   (Renaming a git branch)
  -d   Safe deletion
  -D   Forceful deletion

git commit -am "Default message"

git checkout -b branchName
*/

/*
function testGit()
	{
		$descriptorspec = [
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$pipes = [];
		$resource = proc_open(Git::getBin(), $descriptorspec, $pipes);

		foreach ($pipes as $pipe) {
			fclose($pipe);
		}

		$status = trim(proc_close($resource));

		return ($status != 127);
	}

$repo = Git::open('/path/to/repo');  // -or- Git::create('/path/to/repo')

$repo->add('.');

    if (is_array($files))
		$files = '"'.implode('" "', $files).'"';
    GIT_EXEC . " add $files -v";

$repo->commit('Some commit message');

    GIT_EXEC . ' commit -av -m ' . escapeshellarg($message)


$repo->push('origin', 'master');

    GIT_EXEC . " push $remote $branch"

*/


// 
}

/*
<?php ob_start(); ?>
<HTML ...>
<?php $appGit['css'] = ob_get_contents();
ob_end_clean(); ?>
*/ 

ob_start(); ?>

#app_git-container { position: absolute; display: none; top: 60px; margin: 0 auto; left: 50%; right: 50%;  }
#app_git-container.selected { display: block; z-index: 1; 
  /* Add your desired styling for the selected container */
  /*
  // background-color: rgb(240, 224, 198); //  240, 224, 198, .75    #FBF7F1; // rgba(240, 224, 198, .25);
  
  bg-[#FBF7F1];
  bg-opacity-75;

  font-weight: bold;
  #top { background-color: rgba(240, 224, 198, .75); }
  */
}

.app_git-frame-container { position: absolute; display: none; top:0; left: 0; width: 400px; }
.app_git-frame-container.selected { display: block; z-index: 1; }

/* #app_git-frameName == ['menu', 'init', 'status', 'config', 'commit', 'update'] */

#app_git-frameMenu {}
#app_git-frameMenuPrev {} /* composerMenuPrev */
#app_git-frameMenuNext {} /* composerMenuNext */
/*
#app_git-frameMenuConf {}
#app_git-frameMenuInstall {}
#app_git-frameMenuInit {}
#app_git-frameMenuUpdate {}
*/
#app_git-frameInit {} /* Either there is a .git directory, or go to config with .git_*ignore ... */
#app_git-frameStatus {} /* these maybe just for console results */
#app_git-frameConfig {} /* Frame */
#app_git-frameCommit {} /* same with this one */
#app_git-frameUpdate {} /* Frame */
/*
#update { backgropund-color: rgba(240, 224, 198, .75); }
#middle { backgropund-color: rgba(240, 224, 198, .75); }
#bottom { backgropund-color: rgba(240, 224, 198, .75); }
*/
.btn {
  @apply rounded-md px-2 py-1 text-center font-medium text-slate-900 shadow-sm ring-1 ring-slate-900/10 hover:bg-slate-50
}

.git-menu {
  cursor: pointer;
}

img { display: inline; }

.show { display: block; }


<?php $appGit['style'] = ob_get_contents();
ob_end_clean(); 

ob_start();
!defined('GIT_VERSION') and define('GIT_VERSION', '1.0.0');
!defined('GIT_LATEST') and define('GIT_LATEST', GIT_VERSION);
?>
  <div id="app_git-container" class="absolute <?= (APP_SELF == __FILE__ || isset($_GET['app']) && $_GET['app'] == 'git' ? 'selected' : (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? (isset($_GET['app']) && $_GET['app'] != 'git' ? '' : '') :  '')) ?>" style="z-index: 1; width: 424px; background-color: rgba(255,255,255,0.8); padding: 10px;">
<div style="position: relative; margin: 0 auto; width: 404px; height: 306px; border: 3px dashed #F05033; background-color: #FBF7F1;">

      <div class="absolute ui-widget-header" style="position: absolute; display: inline-block; width: 100%; margin: -25px 0 10px 0; border-bottom: 1px solid #000; z-index: 3;">
        <label class="git-home" style="cursor: pointer;">
          <div class="absolute" style="position: absolute; top: 0px; left: 3px;">
            <img src="resources/images/git_icon.fw.png" width="32" height="32" />
          </div>
        </label>
        <div style="display: inline; padding-left: 40px;">
          <span style="background-color: white; color: #F05033;">Git <?= (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? 'v'.substr(GIT_LATEST, 0, similar_text(GIT_LATEST, GIT_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(GIT_LATEST, similar_text(GIT_LATEST, GIT_VERSION)) . '</span>' : 'v'.GIT_VERSION ); ?></span> <span style="background-color: #0078D7; color: white;"><code class="text-sm" style="background-color: white; color: #0078D7;">$ <?= (defined('GIT_EXEC') ? GIT_EXEC : null); ?></code></span>
        </div>
        
        <div style="display: inline; float: right; text-align: center; color: blue;"><code style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_git-container').style.display='none';">[X]</a></code></div> 
      </div>
      
      <div class=" ui-widget-content" style="position: relative; display: block; width: 398px; background-color: rgba(251,247,241); z-index: 2;">
        <div style="display: inline-block; text-align: left; width: 125px;">
          <div class="git-menu text-sm" style="cursor: pointer; font-weight: bold; padding-left: 25px; border: 1px solid #000;">Main Menu</div>
          <div class="text-xs" style="display: inline-block; border: 1px solid #000;">
            <a class="text-sm" id="app_git-frameMenuPrev" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (APP_ENV == 'development' ? '#!' : '') ?>"> &lt; Menu</a> | <a class="text-sm" id="app_git-frameMenuNext" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (APP_ENV == 'development' ? '#!' : '') ?>">Init &gt;</a>
          </div>
        </div>
        <div class="absolute" style="position: absolute; display: inline-block; top: 4px; text-align: right; width: 272px; ">
          <div class="text-xs" style="display: inline-block;">
          + 1626 <a href="https://github.com/git/git/graphs/contributors">contributors</a>
          <br /><a href="http://github.com/git"><img src="resources/images/github.fw.png" title="http://github.com/git" width="18" height="18" /></a>
          <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;" href="http://git-scm.com/" title="http://git-scm.com/">http://git-scm.com/</a>
          </div>
        </div>
        <div style="clear: both;"></div>
      </div>

      <div class="absolute" style="position: absolute; top: 0; margin: 0px auto; text-align: center; height: 200px; width: 100%; background-repeat: no-repeat; <?= (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? "background-image: url('https://editablegifs.com/gifs/gifs/fireworks-1/output.gif?egv=3258'); opacity: 0.2;" : '' ) ?> z-index: 1;">
      </div>
      <div style="position: absolute; top: 0; left: 0; right: 0; margin: 10px auto; opacity: 1.0; text-align: center; cursor: pointer; z-index: 1; ">
        <img class="<?= (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? 'git-menu' : 'git-update') ?>" src="resources/images/git_logo.gif<?= /*.fw.png*/ NULL; ?>" style="" width="229" height="96" />
      </div>
      <div class="absolute" style="position: absolute; bottom: 24px; left: 0; right: 0; width: 100%; text-align: center;">
        <span style="text-decoration-line: underline; text-decoration-style: solid;">Git is a distributed version control system</span>
      </div>
      <div style="position: absolute; bottom: 0; left: 0; padding: 2px; z-index: 1;">
        <a href="https://github.com/git"><img src="resources/images/github-composer.fw.png" /></a>
      </div>
      <div class="absolute text-sm" style="position: absolute; bottom: 0; right: 0; padding: 2px; z-index: 1; "><?= '<code>Latest: </code>'; ?> <?= (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? '<span class="update" style="color: green; cursor: pointer;">' . 'v'.substr(GIT_LATEST, 0, similar_text(GIT_LATEST, GIT_VERSION)). substr(GIT_LATEST, similar_text(GIT_LATEST, GIT_VERSION))  . '</span>': 'Installed: v' . GIT_VERSION ); ?></div>
      <div style="position: relative; overflow: hidden; width: 398px; height: 286px;">

      <div id="app_git-frameMenu" class="app_git-frame-container selected absolute" style="background-color: rgb(225,196,151,.75); margin-top: 8px;">
        <!--<h3>Main Menu</h3> <h4>Update - Edit Config - Initalize - Install</h4> -->
        <div style="position: absolute; right: 10px; float: right; z-index: 1;">
          <div class="text-sm" style="display: inline-block; margin: 0 auto;">
            <form id="app_git-push" action="<?=APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
              <!-- <input type="hidden"  /> -->
              <button type="submit" name="cmd" value="push"><img src="resources/images/green_arrow.fw.png" width="20" height="25" style="cursor: pointer; margin-left: 6px;" /><br />Push</button>
            </form>
          </div>
          <div class="text-sm" style="display: inline-block; margin: 0 auto;">
            <form id="app_git-pull" action="<?=APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
              <!-- <input type="hidden"  /> -->
              <button type="submit" name="cmd" value="pull"><img src="resources/images/red_arrow.fw.png" width="20" height="25" style="cursor: pointer; margin-left: 4px;" /><br />Pull</button>
            </form>
          </div>
        </div>
        <div style="position: relative; height: 100px;">
          <div id="app_git-commit_msg-container" style="display: none; position: absolute; top: 80px; left: 25%; right: 25%;">
            <input id="app_git-commit_msg" type="text "/>
          </div>
        </div>
        <div style="display: block; margin: 10px auto; width: 100%; background-color: rgb(255,255,255,.75);">
        
          <div style="position: absolute; top: 25px; left: 10px;" class="text-xs">
            <span style="color: red;">[Help]</span><br />Commands<br />
            <code class="text-xs">
            <a id="app_git-add-cmd" href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="">git add .</a><br />
            <a id="app_git-remote-cmd" href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="">git remote -v</a><br />
            <a id="app_git-commit-cmd" href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>">git commit -am "&lt;detail message&gt;"</a>
            </code>
          </div>

          <div style="display: inline-block; width: 32%; text-align: right;"><img src="resources/images/git.fw.png" width="52" height="37" style=" border: 1px dashed #F05033;" /></div>
          <div style="display: inline-block; width: 32%; text-align: center; border: 1px dashed #F05033; height: 44px; padding: 7px;">
            <select id="app_git-frameSelector">
              <!-- <option value="">---</option> -->
              <option value="init" <?= (is_dir('.git') ? 'disabled' : 'selected' ); ?>>init</option>
              <option value="status">status</option>
              <option value="config">config</option>
              <option value="commit">commit</option>
            </select>
          </div>
          <div style="display: inline-block; width: 33%; padding-top: 2px;">
          <form id="app_git-cmd-selected" method="GET">
            <button type="submit"><img src="resources/images/git_icon_selected.fw.png" width="44" height="29" style="border: 1px dashed #F05033;" /></button>
          </form>
          </div>
        </div>
        <div style="height: 35px;"></div>
      </div>

      <div id="app_git-frameInit" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
      <div style="position: absolute; right: 0; float: right; text-align: center;">
        <input id="gitInitSubmit" class="btn" type="submit" value="Init/Run" />
      </div> 
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitInitLabel" for="gitInit" style="background-color: #6781B2; color: white;">&#9650; <code>Init</code></label>
        </div>
      </div>
      <div id="gitInitForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="2" name="git[init]"><?= /* GIT_INIT_PARAMS  NULL*/ 'git init'; ?></textarea>
      </div>
    </form>
      </div>

      <div id="app_git-frameStatus" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: #6781B2; color: white;">&#9650; <code>Status</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run" />
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]"><?= /* GIT_INIT_PARAMS  NULL*/ 'git status'; ?></textarea>
      </div>
    </form>
      </div>
      
      <div id="app_git-frameConfig" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
      <div style="position: absolute; right: 0; float: right; text-align: center;">
        <button id="gitConfigSubmit" class="btn absolute" style="position: absolute; top: 0; right: 0; z-index: 2;" type="submit" value>Modify</button>
      </div> 
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(29, 100%, 42%); color: white; cursor: pointer;">&#9650; <code>GIT_PATH/.gitignore</code></label>
        </div>
      </div>
      <div id="appGitIgnoreForm" style="display:<?= (file_exists('.gitignore') ? 'inline-block' : 'none') ?>; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="2" name="git[status]"><?= /* GIT_INIT_PARAMS  NULL*/ 'git status'; ?></textarea>
      </div>
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(29, 100%, 42%); color: white; cursor: pointer;">&#9650; <code>GIT_PATH/.gitconfig</code></label>
        </div>
      </div>
      <div id="appGitConfigForm" style="display: <?= (exec('git config -l') == NULL ? 'inline-block' : 'none') ?>; overflow-x: hidden; overflow-y: auto; height: 180px; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
      <div style="display: none; width: 100%;">
        <input type="checkbox" name="gitConfigList" />
        <label style="font-style: italic;">git config -l</label>
        <div style="float: right;">
          <input type="checkbox" name="gitIngoreFile" 1 /> <label style="font-style:italic;">.gitignore</label>
          <input type="checkbox" name="gitConfigFile" 1/> <label style="font-style:italic;">.gitconfig</label>
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Name:</label>
        
        <div style="float: right;">
          <input name="gitConfigName" value="<?= 'Barry Dick'; ?>" />
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Email:</label>
        <div style="float: right;">
          <input name="gitConfigEmail" value="<?= 'barryd.it@gmail.com'; ?>" />
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Editor (Core):</label>
        <div style="float: right;">
          <input name="gitConfigCoreEditor" size="40" value="\&quot;C:/Program Files (x86)/Programmer's Notepad/pn.exe\&quot; --allowmulti -w" />
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Default Branch (Init)</label> 
        <div style="float: right;">
          <input name="gitConfigInitDefaultBranch" value="master" />
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Helper (Credential)</label>
        <div style="float: right;">
          <input name="gitConfigCredentialHelper" value="manager-core" />
        </div>
      </div>
      </div>

    </form>
      </div>


      <div id="app_git-frameCommit" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(343, 100%, 42%); color: white;">&#9650; <code>Stage / Commit</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run" />
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]"><?= /* GIT_INIT_PARAMS  NULL*/ 'git commit'; ?></textarea>
      </div>
    </form>
      </div>

      <div id="app_git-frameUpdate" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color:hsl(89, 100%, 42%); color: white;">&#9650; <code>Update</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run" />
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]"><?= /* GIT_INIT_PARAMS  NULL*/ 'git update'; ?></textarea>
      </div>
    </form>
      </div>


      </div>
    </div>
  </div>
<?php $appGit['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

document.getElementById('app_git-push').addEventListener('submit', function(event) {
  // Prevent the default form submission
  event.preventDefault();

  // For example, you can show an alert to indicate that the form submission is disabled
  alert('Push request was made made.');

  document.getElementById('requestInput').value = 'git push https://<?= getenv('COMPOSER_TOKEN') ?>@github.com/barrydit/CodeHub.git';

  // Get the element with the ID "requestSubmit"
  var requestSubmit = document.getElementById('requestSubmit');

  // Create a new click event
  var clickEvent = new MouseEvent('click', {
    bubbles: true,
    cancelable: true,
    view: window
  });

  // Dispatch the click event on the element
  requestSubmit.dispatchEvent(clickEvent);
});

// git_icon_selected  app_git-cmd-selected
document.getElementById('app_git-cmd-selected').addEventListener('submit', function(event) {
  // Prevent the default form submission
  event.preventDefault();
  
  var cmdSelect = document.getElementById('app_git-frameSelector');
  
  const git_cmd = document.getElementById('requestInput');
  const commit_msg_container = document.getElementById('app_git-commit_msg-container');
  const commit_msg = document.getElementById('app_git-commit_msg');
  
  git_cmd.value = 'git ' + cmdSelect.value;


  if (cmdSelect.value == 'config') {
    console.log('Testing...');
  } else if (cmdSelect.value == 'commit') {
    commit_msg_container.style.display = 'block';
    
    if (commit_msg.value != '') {
      document.getElementById('requestInput').value = git_cmd.value + ' -a -m "' + commit_msg.value + '"';
    } else {
      document.getElementById('requestInput').value = git_cmd.value + ' -a -m "default message"';
      return false;    
    }
  }
  
  // Get the element with the ID "requestSubmit"
  var requestSubmit = document.getElementById('requestSubmit');

  // Create a new click event
  var clickEvent = new MouseEvent('click', {
    bubbles: true,
    cancelable: true,
    view: window
  });

  // Dispatch the click event on the element

  requestSubmit.dispatchEvent(clickEvent);

//  var changePositionBtn = document.getElementById('changePositionBtn');
//  const myDiv = document.getElementById('myDiv');

//  if (myDiv.style.position == 'absolute')
//    changePositionBtn.dispatchEvent(clickEvent);

  isFixed = true;
  show_console();

  // For example, you can show an alert to indicate that the form submission is disabled
  console.log(cmdSelect.value + ' was executed.');
  
});

document.getElementById('app_git-pull').addEventListener('submit', function(event) {
  // Prevent the default form submission
  event.preventDefault();

  // For example, you can show an alert to indicate that the form submission is disabled
  alert('Pull request was made made.');
  
  document.getElementById('requestInput').value = 'git pull';

  // Get the element with the ID "requestSubmit"
  var requestSubmit = document.getElementById('requestSubmit');

  // Create a new click event
  var clickEvent = new MouseEvent('click', {
    bubbles: true,
    cancelable: true,
    view: window
  });

  // Dispatch the click event on the element
  requestSubmit.dispatchEvent(clickEvent);
  
  //$("#requestSubmit").click();  
  
});

$(document).ready(function() {
  var git_frame_containers = $(".app_git-frame-container");
  var git_frame_totalFrames = git_frame_containers.length;
  var currentIndex = 0;
  
  $("#app_git-frameSelector").value = 0;
  
  console.log(git_frame_totalFrames + ' - total frames');
/*
  $("#appGitAuthLabel").click(function() {
    if ($('#appGitAuthJsonForm').css('display') == 'none') {
      $('#appGitAuthLabel').html("&#9650; <code>GIT_HOME/auth.json");
      $('#appGitAuthJsonForm').slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('#appGitAuthLabel').html("&#9660; <code>GIT_HOME/auth.json</code>");
      $('#appGitAuthJsonForm').slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });

  $("#appGitJsonLabel").click(function() {
    if ($('#appGitJsonForm').css('display') == 'none') {
      $('#appGitJsonLabel').html("&#9650; <code>GIT_PATH/git.json");
      $('#appGitJsonForm').slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('#appGitJsonLabel').html("&#9660; <code>GIT_PATH/git.json</code>");
      $('#appGitJsonForm').slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });

  $("#app_git-frameMenuConf").click(function() {
    currentIndex = 1;
    $("#app_git-frameMenuPrev").html('&lt; Menu');
    $("#app_git-frameMenuNext").html('Conf &gt;');
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
  });   

  $("#app_git-frameMenuInstall").click(function() {
    currentIndex = 2;
    $("#app_git-frameMenuPrev").html('&lt; Conf');
    $("#app_git-frameMenuNext").html('Init &gt;');
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
  });

  $("#app_git-frameMenuInit").click(function() {
    currentIndex = 3;
    $("#app_git-frameMenuPrev").html('&lt; Install');
    $("#app_git-frameMenuNext").html('Update &gt;');
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
  });
  
  $("#app_git-frameMenuUpdate").click(function() {
    currentIndex = 4;
    $("#app_git-frameMenuPrev").html('&lt; Init');
    $("#app_git-frameMenuNext").html('Menu &gt;');
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
  });   
*/

  $(".git_icon_selected").click(function() {
    currentIndex = 0;
    
    console.log('test');

    //git_frame_containers.removeClass("selected");
    //git_frame_containers.eq(currentIndex).addClass('selected');
  });

  $(".git-home").click(function() {
    currentIndex = 0; 
    git_frame_containers.removeClass("selected");
    //git_frame_containers.eq(currentIndex).addClass('selected');
  });

  $(".git-menu").click(function() {
    currentIndex = 0;
    $("#app_git-frameSelector").value = 0;
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
  });

  $("#app_git-frameMenuPrev").click(function() {
    if (currentIndex <= 0) currentIndex = 5;
    console.log(currentIndex + '!=' + git_frame_totalFrames);
    currentIndex--;
    if (currentIndex >= git_frame_totalFrames) {
      currentIndex = 0;
    }
    if (currentIndex == 0) {
      $("#app_git-frameMenuPrev").html('&lt; Update');
      $("#app_git-frameMenuNext").html('Conf &gt;');
    } if (currentIndex == 1) {
      $("#app_git-frameMenuPrev").html('&lt; Menu');
      $("#app_git-frameMenuNext").html('Install &gt;');
    } else if (currentIndex == 2) {
      $("#app_git-frameMenuPrev").html('&lt; Conf');
      $("#app_git-frameMenuNext").html('Init &gt;');
    } else if (currentIndex == 3) {
      $("#app_git-frameMenuPrev").html('&lt; Install');
      $("#app_git-frameMenuNext").html('Update &gt;');
    } else if (currentIndex == 4) {
      $("#app_git-frameMenuPrev").html('&lt; Init');
      $("#app_git-frameMenuNext").html('Menu &gt;');
    }

    //else 
    console.log('decided: ' + currentIndex);
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
    
    //currentIndex--;    
    console.log(currentIndex);
  });
    
  $("#app_git-frameMenuNext").click(function() {
    currentIndex++;
    console.log(currentIndex + '!=' + git_frame_totalFrames);
    if (currentIndex >= git_frame_totalFrames) {
      currentIndex = 0;
    }
    if (currentIndex == 0) {
      $("#app_git-frameMenuPrev").html('&lt; Update');
      $("#app_git-frameMenuNext").html('Conf &gt;');
    } else if (currentIndex == 1) {
      $("#app_git-frameMenuPrev").html('&lt; Menu');
      $("#app_git-frameMenuNext").html('Install &gt;');
    } else if (currentIndex == 2) {
      $("#app_git-frameMenuPrev").html('&lt; Conf');
      $("#app_git-frameMenuNext").html('Init &gt;');
    } else if (currentIndex == 3) {
      $("#app_git-frameMenuPrev").html('&lt; Install');
      $("#app_git-frameMenuNext").html('Update &gt;');
    } else if (currentIndex == 4) {
      $("#app_git-frameMenuPrev").html('&lt; Init');
      $("#app_git-frameMenuNext").html('Menu &gt;');
    }
    if (currentIndex < 0) currentIndex++;
    //else 
    console.log('decided: ' + currentIndex);
    git_frame_containers.removeClass("selected"); // git_frame_containers.css("z-index", 0); // Reset z-index for all elements
    git_frame_containers.eq(currentIndex).addClass('selected'); // css("z-index", git_frame_totalFrames); // Set top layer z-index
  });

  
/*
  $("#app_git-push").click(function() {
     e.preventDefault();
  });

  $("#app_git-pull").click(function() {
  
  });
  

  $("#app_git-frameSelector").change(function() {
    var selectedIndex = parseInt($(this).val(), 10);
    currentIndex = selectedIndex;

    if (currentIndex >= git_frame_totalFrames) {
      currentIndex = 0;
    }
    console.log(currentIndex + ' = currentIndex');
    $(".app_git-frame-container").removeClass("selected"); // Remove selected class from all containers
    
    if (currentIndex <= git_frame_totalFrames && currentIndex > 0) {
      $(".app_git-frame-container").eq(currentIndex).addClass("selected"); // Apply selected class to the chosen container
    }
    this.value = currentIndex;
    //
  });

  $('select').on('change', function (e) {
    var optionSelected = $("option:selected", this);
    var valueSelected = this.value;
  });
*/
});
<?php $appGit['script'] = ob_get_contents();
ob_end_clean();

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
<?= $appGit['style']; ?>
</style>
</head>
<body>
<?= $appGit['body']; ?>


  <script src="<?= (check_http_200('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : $path . 'jquery-3.7.1.min.js') ?>"></script>
  <!-- You need to include jQueryUI for the extended easing options. -->
<?php /* https://stackoverflow.com/questions/12592279/typeerror-p-easingthis-easing-is-not-a-function */ ?>
  <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script> <!-- Uncaught ReferenceError: jQuery is not defined -->



  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <!-- script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script -->
  <!-- <script src="resources/js/jquery/jquery.min.js"></script> -->
<script>
<?= $appGit['script']; ?>
</script>
</body>
</html>
<?php $appGit['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'git' && APP_DEBUG)
  die($appGit['html']);


/****   END of ui.git.php   ****/


ob_start(); ?>

#app_npm-container { position: absolute; display: none; top: 60px; margin: 0 auto; left: 50%; right: 50%;  }
#app_npm-container.selected { display: block; z-index: 1; 
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


<?php $appNpm['style'] = ob_get_contents();
ob_end_clean(); 

ob_start(); ?>
  <div id="app_npm-container" class="absolute <?= (APP_SELF == __FILE__ || (isset($_GET['app']) && $_GET['app'] == 'npm') && !isset($_GET['path']) ? 'selected' : '') ?>" style="z-index: 1; width: 424px; background-color: rgba(255,255,255,0.8); padding: 10px;">
    <div style="position: relative; margin: 0 auto; width: 404px; height: 306px; border: 3px dashed #DD0000; background-color: #FBF7F1;">
      <div class="absolute ui-widget-header" style="position: absolute; display: inline-block; width: 100%; margin: -25px 0 10px 0; border-bottom: 1px solid #000; z-index: 3;">
        <label class="npm-home" style="cursor: pointer;">
          <div class="absolute" style="position: absolute; top: 0px; left: 3px;">
            <img src="resources/images/npm_icon.png" width="32" height="32" />
          </div>
        </label>
        <div style="display: inline; padding-left: 40px;">
          <span style="background-color: white; color: #DD0000;">Node.js <?= /* (version_compare(NPM_LATEST, NPM_VERSION, '>') != 0 ? 'v'.substr(NPM_LATEST, 0, similar_text(NPM_LATEST, NPM_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(NPM_LATEST, similar_text(NPM_LATEST, NPM_VERSION)) . '</span>' : 'v'.NPM_VERSION ); */ NULL; ?></span> <span style="background-color: #0078D7; color: white;"><code class="text-sm" style="background-color: white; color: #0078D7;">$ <?= (defined('NPM_EXEC') ? NPM_EXEC : null); ?></code></span>
        </div>
        
        <div style="display: inline; float: right; text-align: center; color: blue;"><code style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_npm-container').style.display='none';">[X]</a></code></div> 
      </div>
      
      <div class=" ui-widget-content" style="position: relative; display: block; width: 398px; background-color: rgba(251,247,241); z-index: 2;">

        <div style="display: inline-block; text-align: left; width: 125px;">
          <div class="npm-menu text-sm" style="cursor: pointer; font-weight: bold; padding-left: 25px; border: 1px solid #000;">Main Menu</div>
          <div class="text-xs" style="display: inline-block; border: 1px solid #000;">
            <a class="text-sm" id="app_npm-frameMenuPrev" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (APP_ENV == 'development' ? '#!' : '') ?>"> &lt; Menu</a> | <a class="text-sm" id="app_npm-frameMenuNext" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (APP_ENV == 'development' ? '#!' : '') ?>">Init &gt;</a>
          </div>
        </div>
        <div class="absolute" style="position: absolute; display: inline-block; top: 4px; text-align: right; width: 272px; ">
          <div class="text-xs" style="display: inline-block;">
          + 3357 <a href="https://github.com/nodejs/node/graphs/contributors">contributors</a>
          <br /><a href="https://github.com/nodejs"><img src="resources/images/node.js.png" title="https://github.com/nodejs" width="18" height="18" /></a>
          <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;" href="https://nodejs.org/" title="https://nodejs.org/">https://nodejs.org/</a>
          </div>
        </div>
        <div style="clear: both;"></div>
      </div>

      <div class="" style="position: absolute; top: 0; left: 0; right: 0; margin: 10px auto; opacity: 1.0; text-align: center; cursor: pointer; z-index: 1;">
        <img class="npm-menu" src="resources/images/node_npm.fw.png" style="margin-top: 45px;" width="150" height="198" />
      </div>


<div style="position: relative; overflow: hidden; width: 398px; height: 256px;">

      <div id="app_git-frameMenu" class="app_git-frame-container absolute selected" style="background-color: rgb(225,196,151,.75); margin-top: 8px; height: 100%;">

        <div style="display: block; margin: 10px auto; width: 100%; background-color: rgb(255,255,255,.75);">

<div class="text-sm" style="display: inline-block; width: 100%;"><span>Dependencies (install):</span>
        <div style="position: relative; float: right;">
          <input id="composerReqPkg" type="text" title="Enter Text and onSelect" list="composerReqPkgs" placeholder="" value="" onselect="get_package(this);">
          <datalist id="composerReqPkgs">
            <option value=""></option>
          </datalist>
      </div>

        <div style="clear: both;"></div>
<input type="checkbox" />
<input type="text" value="jquery:^3.7.1" /><br />
<input type="checkbox" />
<input type="text" value="npm:^10.2.3" /><br /><br />
Dev. Dependencies<br />
<input type="checkbox" />
<input type="text" value="@babel/core:^7.23.2" /><br />
<input type="checkbox" />
<input type="text" value="@babel/preset-env:^7.23.2" size="30" /><br />
<input type="checkbox" />
<input type="text" value="babel-loader:^9.1.3" /><br />
<input type="checkbox" />
<input type="text" value="webpack:^5.89.0" /><br />
<input type="checkbox" />
<input type="text" value="webpack-cli:^5.1.4" />
          </div>

        </div>
        <div style="height: 35px;"></div>
      </div>

      <div id="app_git-frameInit" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
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
      </div>

      <div id="app_git-frameStatus" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?app=git#!" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: #6781B2; color: white;">? <code>Status</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run">
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]">git status</textarea>
      </div>
    </form>
      </div>
      
      <div id="app_git-frameConfig" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?app=git#!" method="POST">
      <div style="position: absolute; right: 0; float: right; text-align: center;">
        <button id="gitConfigSubmit" class="btn absolute" style="position: absolute; top: 0; right: 0; z-index: 2;" type="submit" value="">Modify</button>
      </div> 
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(29, 100%, 42%); color: white; cursor: pointer;">? <code>GIT_PATH/.gitignore</code></label>
        </div>
      </div>
      <div id="appGitIgnoreForm" style="display:inline-block; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="2" name="git[status]">git status</textarea>
      </div>
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(29, 100%, 42%); color: white; cursor: pointer;">? <code>GIT_PATH/.gitconfig</code></label>
        </div>
      </div>
      <div id="appGitConfigForm" style="display: inline-block; overflow-x: hidden; overflow-y: auto; height: 180px; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
      <div style="display: none; width: 100%;">
        <input type="checkbox" name="gitConfigList">
        <label style="font-style: italic;">git config -l</label>
        <div style="float: right;">
          <input type="checkbox" name="gitIngoreFile" 1=""> <label style="font-style:italic;">.gitignore</label>
          <input type="checkbox" name="gitConfigFile" 1=""> <label style="font-style:italic;">.gitconfig</label>
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Name:</label>
        
        <div style="float: right;">
          <input name="gitConfigName" value="Barry Dick">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Email:</label>
        <div style="float: right;">
          <input name="gitConfigEmail" value="barryd.it@gmail.com">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Editor (Core):</label>
        <div style="float: right;">
          <input name="gitConfigCoreEditor" size="40" value="\&quot;C:/Program Files (x86)/Programmer's Notepad/pn.exe\&quot; --allowmulti -w">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Default Branch (Init)</label> 
        <div style="float: right;">
          <input name="gitConfigInitDefaultBranch" value="master">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Helper (Credential)</label>
        <div style="float: right;">
          <input name="gitConfigCredentialHelper" value="manager-core">
        </div>
      </div>
      </div>

    </form>
      </div>


      <div id="app_git-frameCommit" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?app=git#!" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(343, 100%, 42%); color: white;">? <code>Stage / Commit</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run">
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]">git commit</textarea>
      </div>
    </form>
      </div>

      <div id="app_git-frameUpdate" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?app=git#!" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color:hsl(89, 100%, 42%); color: white;">? <code>Update</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run">
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]">git update</textarea>
      </div>
    </form>
      </div>


      </div>
    </div>
  </div>
<?php $appNpm['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>


<?php $appNpm['script'] = ob_get_contents();
ob_end_clean();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache"); 
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
<?= $appNpm['style']; ?>
</style>
</head>
<body>
<?= $appNpm['body']; ?>

  <script src="<?= (check_http_200('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : $path . 'jquery-3.7.1.min.js') ?>"></script>
  <!-- You need to include jQueryUI for the extended easing options. -->
<?php /* https://stackoverflow.com/questions/12592279/typeerror-p-easingthis-easing-is-not-a-function */ ?>
  <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script> <!-- Uncaught ReferenceError: jQuery is not defined -->

<script>
<?= $appNpm['script']; ?>
</script>
</body>
</html>
<?php $appNpm['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'npm' && APP_DEBUG)
  die($appNpm['html']);

/****   END of ui.npm.php   ****/

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

    <div class=" ui-widget-content" style="display: inline-block; width: auto; padding-left: 10px;">

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
</div>


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

// 4.961 @ 4.937
dd('php init time: ', false);
/****   END of ui.php.php   ****/