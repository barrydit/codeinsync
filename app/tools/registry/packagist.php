<?php
/**
 * Packagist.org Package Search
 *
 * @package App
 * @author Your Name
 * @version 1.0
 */
$packagistUrl = 'https://packagist.org/';
$cacheFile = APP_BASE['var'] . 'packagist.org.html';
$cacheDir = APP_BASE['var'];

// Ensure cache directory exists
if (!is_dir($cacheDir)) {
  mkdir($cacheDir, 0755);
}

// Determine whether to refresh cache
$refreshCache = true;

if (is_file($cacheFile)) {
  $expiresInDays = 5;
  $modifiedTime = filemtime($cacheFile);
  $expiryDate = strtotime("+$expiresInDays days", $modifiedTime);
  $currentDate = strtotime(date('Y-m-d'));

  $refreshCache = $currentDate >= $expiryDate;
}

// Fetch fresh content if needed
if ($refreshCache) {
  $handle = curl_init($packagistUrl);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  $html = curl_exec($handle);

  if (!empty($html)) {
    if (!file_put_contents($cacheFile, $html)) {
      $errors['COMPOSER_LATEST'] = "$packagistUrl returned empty.";
    }
  } else {
    $errors['COMPOSER_LATEST'] = curl_error($handle);
  }

  if (is_resource($handle)) {
    curl_close($handle);
  }
}

// Serve the HTML snapshot if `packagist` is requested
if (array_key_first($_GET) === 'packagist') {
  libxml_use_internal_errors(true);

  $dom = new DOMDocument('1.0', 'utf-8');
  $dom->loadHTML(file_get_contents($cacheFile), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

  $xpath = new DOMXPath($dom);
  $metaTags = $xpath->query('//head/meta');

  if ($metaTags->length > 0) {
    $baseTag = $dom->createDocumentFragment();
    $baseTag->appendXML('<base href="https://packagist.org/" />');

    $metaTags[0]->parentNode->insertBefore($baseTag, $metaTags[0]->nextSibling);
  }

  echo $dom->saveHTML();
  exit();
}
/*
switch ($_GET['app']) {
  case 'packagist':

    break;
  default:
    //$app['title'] = APP_NAME;
    //$app['description'] = APP_DESCRIPTION;
    //$app['keywords'] = APP_KEYWORDS;
    break;
}*/



if (__FILE__ == get_required_files()[0]) //die(getcwd());
  if (
    $path = (basename(getcwd()) == 'public')
    ? (is_file('config.php') ? 'config.php' : '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php') : ''
  )
    require_once $path;
  else
    die(var_dump("$path path was not found. file=config.php"));
/*
if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../console_app.php') ? '../console_app.php' : (is_file('../config/console_app.php') ? '../config/console_app.php' : 'console_app.php'))
    : (is_file('console_app.php') ? 'console_app.php' : (is_file('public/console_app.php') ? 'public/console_app.php' : null))) require_once $path; 
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
  [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
  $output[] = 'Composer: ' . (!isset($stdout) ? NULL : $stdout . (!isset($stderr) ? NULL : ' Error: ' . $stderr) . (!isset($exitCode) ? NULL : ' Exit Code: ' . $exitCode));
  $output[] = $_POST['cmd'];

          } else if (preg_match('/^git(:?(.*))/i', $_POST['cmd'], $match)) {
          $output[] = APP_SUDO . GIT_EXEC . ' ' . $match[1];
  $proc=proc_open(APP_SUDO . GIT_EXEC . ' ' . $match[1],
    array(
      array("pipe","r"),
      array("pipe","w"),
      array("pipe","w")
    ),
    $pipes);
  [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
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

if (preg_match('/^app\.([\w\-.]+)\.php$/', basename(__FILE__), $matches))
  ${$matches[1]} = $matches[1];

if (false) { ?>
  <style type="text/css">
  <?php }
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

  <?php $app['style'] = ob_get_contents();
  ob_end_clean();
  if (false) { ?>
  </style>
<?php }

  ob_start();

  ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
<div id="app_packagist-container"
  class="<?= APP_SELF == __FILE__ || (isset($_GET['app']) && $_GET['app'] == 'packagist') ? 'selected' : '' ?>"
  style="border: 1px solid #000;">
  <div class="header ui-widget-header">
    <div style="display: inline-block;">Packagist.org Package (Search)</div>
    <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;"
        onclick="document.getElementById('app_packagist-container').style.display='none';">X</a>]</div>
  </div>

  <div style="display: inline-block; width: auto; padding-left: 10px;">
    <iframe src="<?= /* basename(__FILE__) */ '?packagist'; ?>" style="height: 550px; width: 775px;"></iframe>
  </div>

  <!-- <pre id="ace-editor" class="ace_editor"></pre> -->

</div>
<!-- </div> -->

<?php $app['body'] = ob_get_contents();
ob_end_clean();

if (false) { ?>
  <script type="text/javascript">
  <?php }
ob_start(); ?>
  // Javascript comment
  <?php $app['script'] = ob_get_contents();
  ob_end_clean();

  if (false) { ?></script> <?php }

  ob_start(); ?>

<?php $app['html'] = ob_get_contents();
ob_end_clean();

/*
$dom = new DOMDocument(1.0, 'utf-8');
$dom->loadHTML(file_get_contents(APP_BASE['var'] . 'packagist.org.html'));

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
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && array_key_first($_GET) == 'packagist' && APP_DEBUG)
  Shutdown::setEnabled(false)->setShutdownMessage(function () use ($dom) {
    return $dom->saveHTML(); /* eval('?>' . $project_code); // -wow */
  })->shutdown(); // exit;


