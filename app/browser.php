<?php

if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"])) {
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

if (preg_match('/^app\.([\w\-.]+)\.php$/', basename(__FILE__), $matches))
  ${$matches[1]} = $matches[1];


// dd(APP_PATH . '  ' . APP_ROOT);
// PHP_URL_SCHEME, PHP_URL_HOST, PHP_URL_PORT, PHP_URL_USER, PHP_URL_PASS, PHP_URL_PATH, PHP_URL_QUERY or PHP_URL_FRAGMENT




// dd(parse_url($_SERVER['REQUEST_URI'], PHP_URL_HOST));

/*
// Define the GitHub API URL
//$githubApiUrl = 'https://api.github.com/barrydit';

// Make the request to GitHub API
//$response = file_get_contents($githubApiUrl);

$accessToken = COMPOSER_AUTH['token']; // Replace with your actual access token
$apiUrl = 'https://api.github.com/barrydit';

$options = [
    'http' => [
        'header' => "Authorization: token $accessToken",
        'method' => 'GET'
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);

if ($response === false) {
    echo 'Error fetching data from GitHub API';
} else {
    $data = json_decode($response, true);
    print_r($data);
}

// Forward the response to the client
dd($response);
*/
/*
if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../console_app.php') ? '../console_app.php' : (is_file('../config/console_app.php') ? '../config/console_app.php' : 'console_app.php'))
    : (is_file('console_app.php') ? 'console_app.php' : (is_file('public/console_app.php') ? 'public/console_app.php' : null))) require_once $path; 
else die(var_dump($path . ' path was not found. file=console_app.php'));
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  //header('Location: http://' . $_POST['url']);

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

ob_start(); ?>
/* Styles for the absolute div */
#app_browser-container {
position : absolute;
display : none;
top : 5%;
//bottom: 60px;
left : 50%;
transform : translateX(-50%);
width : auto;
height : 100vh;
background-color : rgba(255, 255, 255, 0.9);
color : black;
text-align : center;
padding : 10px;
z-index : 1;
}
input {
color : black;
}
<?php $app['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
<div id="app_browser-container"
  class="<?= __FILE__ == get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'browser') ? 'selected' : '' ?>"
  style="border: 1px solid #000;">
  <div class="header ui-widget-header">
    <div style="display: inline-block;">Browser (Google.ca)</div>
    <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;"
        onclick="document.getElementById('app_browser-container').style.display='none';">X</a>]</div>
  </div>

  <div style="display: inline-block; width: auto; height: 100%; padding-left: 10px;">
    <div style="position: absolute; background-color: white; left: 0; right: 0; width: auto;">WWW Test: <form
        method="POST" action="<?= 'proxy.php' ?>"><input type="text" name="url" onselect="go_to_url();" /><input
          type="submit" style="visibility: hidden;" /></form>
    </div>
    <iframe
      src="<?= /* (is_dir($path = APP_PATH . APP_BASE['public']) && getcwd() == realpath($path) ? APP_BASE['public'] : '') . basename(__FILE__) */ NULL; ?>"
      style="height: 100vh; width: 100%;"></iframe>
  </div>

  <!-- <pre id="ace-editor" class="ace_editor"></pre> -->

</div>
<!-- </div> -->

<?php $app['body'] = ob_get_contents();
ob_end_clean();

if (false) { ?>
  <script type="text/javascript"><?php }
ob_start(); ?>
  // Javascript comment
  <?php $app['script'] = ob_get_contents();
  ob_end_clean();

  if (false) { ?></script><?php }
  ob_start(); ?>

<?php $app['html'] = ob_get_contents();
ob_end_clean();

is_dir(APP_PATH . APP_BASE['var']) or mkdir(APP_PATH . APP_BASE['var'], 0755);
if (is_file(APP_PATH . APP_BASE['var'] . 'github.com.html')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime(APP_PATH . APP_BASE['var'] . '/github.com.html'))))) / 86400)) <= 0) {
    $url = 'https://github.com/barrydit/codeinsync';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($html = curl_exec($handle)))
      file_put_contents(APP_PATH . APP_BASE['var'] . 'github.com.html', $html) or $errors['COMPOSER_LATEST'] = "$url returned empty.";
  }
} else {
  $url = 'https://github.com/barrydit/codeinsync';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($html = curl_exec($handle)))
    file_put_contents(APP_PATH . APP_BASE['var'] . 'github.com.html', $html) or $errors['COMPOSER_LATEST'] = "$url returned empty.";
}

/*
libxml_use_internal_errors(true); // Prevent HTML errors from displaying
$dom = new DOMDocument(1.0, 'utf-8');
$dom->loadHTML(file_get_contents(APP_IS_ONLINE && check_http_status('https://github.com/barrydit/codeinsync') ? 'https://github.com/barrydit/codeinsync' : APP_PATH . APP_BASE['var'] . 'github.com.html'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );   
$xpath = new DOMXPath($dom);

//header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
//header('Content-Type: application/json');
//header('Access-Control-Allow-Credentials: true');


$destination = $xpath->query('//head/meta');
$template = $dom->createDocumentFragment();
$template->appendXML('<base href="https://github.com/" />');
$destination[0]->parentNode->insertBefore($template, $destination[0]->nextSibling);
*/

/*
$dom = new DOMDocument(1.0, 'utf-8');
$dom->loadHTML(file_get_contents(APP_PATH . APP_BASE['var'] . 'github.com.html'));

$divs = $dom->getElementsByTagName('head');

$element = $dom->createElement('test', 'This is the root element!');

$elm = createElement($dom, 'foo', 'bar', array('attr_name'=>'attr_value'));

$dom->appendChild($elm);
*/

//dd($divs);

//$content_node=$dom->getElementById("main");
//$node=getElementsByClass($content_node, 'p', 'latest');

//$dom->saveHTML($dom->documentElement);

//echo 

ob_start(); ?>
<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Browser</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="resources/css/app.css" />
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

  <?php
  // (APP_IS_ONLINE && check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/tailwindcss-3.3.5.js')?
  is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/') or mkdir($path, 0755, true);
  if (is_file("{$path}tailwindcss-3.3.5.js")) {
    if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path . 'tailwindcss-3.3.5.js'))))) / 86400)) <= 0) {
      $url = 'https://cdn.tailwindcss.com';
      $handle = curl_init($url);
      curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

      if (!empty($js = curl_exec($handle)))
        file_put_contents("{$path}tailwindcss-3.3.5.js", $js) or $errors['JS-TAILWIND'] = "$url returned empty.";
    }
  } else {
    $url = 'https://cdn.tailwindcss.com';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($js = curl_exec($handle)))
      file_put_contents("{$path}tailwindcss-3.3.5.js", $js) or $errors['JS-TAILWIND'] = "$url returned empty.";
  }
  ?>

  <script src="<?= 'resources/js/tailwindcss-3.3.5.js' ?? $url ?>"></script>

  <style type="text/tailwindcss">
    <?= /*$appWhiteboard['style'];*/ NULL; ?>
* { margin: 0; padding: 0; } /* to remove the top and left whitespace */

html, body { width: 100%; height: 100%; <?= $_SERVER['SCRIPT_FILENAME'] == __FILE__ ? 'overflow:hidden;' : '' ?> } /* just to be sure these are full screen*/
</style>
</head>

<body class="bg-gray-300">
  <?= $app['body']; ?>
</body>
<script type="text/javascript" charset="utf-8">

  function go_to_url() {
    let url = 'http://' + document.getElementsByName('url')[0].value;
    document.getElementsByTagName('iframe')[0].src = url;
  };
</script>

</html>
<?php $app['html'] = ob_get_contents();
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'browser' && APP_DEBUG)
  die($app['html']);

/*
Shutdown::setEnabled(false)->setShutdownMessage(function () { // use($dom)
  return '<!DOCTYPE html>'; //  $dom->saveHTML() ?? file_get_contents("https://github.com/barrydit/codeinsync"); // $dom->saveHTML(); /* eval('? >' . $project_code); // -wow
})->shutdown(); // exit; */