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
#app_packagist-container {
position: absolute;
display: none;
top: 5%;
//bottom: 60px;
left: 50%;
transform: translateX(-50%);
width: auto;
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

#ace-editor {
  margin: 0;
  position: relative;
  resize: both;
  overflow: auto;
  white-space: pre-wrap;
  //width: 100%;
  //height: 100%;
}

<?php $appPackagist['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
  <div id="app_packagist-container" class="<?= (APP_SELF == __FILE__ || (isset($_GET['app']) && $_GET['app'] == 'packagist') ? 'selected' : '') ?>" style="border: 1px solid #000;">
    <div class="header ui-widget-header">
      <div style="display: inline-block;">Packagist.org Package (Search)</div>
      <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_packagist-container').style.display='none';">X</a>]</div> 
    </div>

      <div style="display: inline-block; width: auto; padding-left: 10px;">
        <iframe src="<?= /* basename(__FILE__) */ NULL; ?>" style="height: 550px; width: 775px;"></iframe>
      </div>

      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->

  </div>
<!-- </div> -->

<?php $appPackagist['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<?php $appPackagist['script'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<?php $appPackagist['html'] = ob_get_contents(); 
ob_end_clean();


is_dir(APP_PATH . APP_BASE['var']) or mkdir(APP_PATH . APP_BASE['var'], 0755);
if (is_file(APP_PATH . APP_BASE['var'] . 'packagist.org.html')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime(APP_PATH . APP_BASE['var'] . '/packagist.org.html'))))) / 86400)) <= 0 ) {
    $url = 'https://packagist.org/';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($html = curl_exec($handle))) 
      file_put_contents(APP_PATH . APP_BASE['var'] . 'packagist.org.html', $html) or $errors['COMPOSER_LATEST'] = $url . ' returned empty.';
  }
} else {
  $url = 'https://packagist.org/';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($html = curl_exec($handle))) 
    file_put_contents(APP_PATH . APP_BASE['var'] . 'packagist.org.html', $html) or $errors['COMPOSER_LATEST'] = $url . ' returned empty.';
}

libxml_use_internal_errors(true); // Prevent HTML errors from displaying
$dom = new DOMDocument(1.0, 'utf-8');
$dom->loadHTML(file_get_contents(APP_PATH . APP_BASE['var'] . 'packagist.org.html'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );   
$xpath = new DOMXPath($dom);

$destination = $xpath->query('//head/meta');
$template = $dom->createDocumentFragment();
$template->appendXML('<base href="https://packagist.org/" />');
$destination[0]->parentNode->insertBefore($template, $destination[0]->nextSibling);

/*
$dom = new DOMDocument(1.0, 'utf-8');
$dom->loadHTML(file_get_contents(APP_PATH . APP_BASE['var'] . 'packagist.org.html'));

$divs = $dom->getElementsByTagName('head');

$element = $dom->createElement('test', 'This is the root element!');

$elm = createElement($dom, 'foo', 'bar', array('attr_name'=>'attr_value'));

$dom->appendChild($elm);
*/

//dd($divs);

//$content_node=$dom->getElementById("main");
//$node=getElementsByClass($content_node, 'p', 'latest');

//$dom->saveHTML($dom->documentElement);
 
//echo file_get_contents("https://packagist.org/");

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'php' && APP_DEBUG)
  Shutdown::setEnabled(false)->setShutdownMessage(function() use($dom) {
      return $dom->saveHTML(); /* eval('?>' . $project_code); // -wow */
    })->shutdown(); // die();