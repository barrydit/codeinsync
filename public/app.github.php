<?php

/*

$accessToken = 'YOUR_ACCESS_TOKEN'; // Replace with your actual access token
$apiUrl = 'https://api.github.com/brrydit';

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
----------------

$accessToken = 'YOUR_ACCESS_TOKEN'; // Replace with your actual access token
$apiUrl = 'https://api.github.com/user';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: token ' . $accessToken]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    echo 'Error fetching data from GitHub API';
} else {
    $data = json_decode($response, true);
    print_r($data);
}

curl_close($ch);
?>


// Set the CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

// Check if this is a preflight request (OPTIONS method), and if so, exit early
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

// Define the GitHub API endpoints
$endpoints = array(
    'used_by_list' => 'https://api.github.com/repos/barrydit/composer_app/community/profile',
    'overview_actions' => 'https://api.github.com/repos/barrydit/composer_app/actions',
    'commit_rollup' => 'https://api.github.com/repos/barrydit/composer_app/commits/e5bc5fed931d9e81105cab97ad0957e61ab4691b/rollup',
    'citation_hovercards' => 'https://api.github.com/repos/barrydit/composer_app/contents/hovercards/citation/sidebar_partial?ref=main',
    'security_overall_count' => 'https://api.github.com/repos/barrydit/composer_app/community/profile',
    'spoofed_commit_check' => 'https://api.github.com/repos/barrydit/composer_app/compare/e5bc5fed931d9e81105cab97ad0957e61ab4691b...HEAD'
);

// Get the requested endpoint from the query string
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : null;

// Check if the requested endpoint is valid
if ($endpoint && isset($endpoints[$endpoint])) {
    // Make the request to the GitHub API
    $url = $endpoints[$endpoint];
    $response = file_get_contents($url);

    // Forward the GitHub API response to the client
    header('Content-Type: application/json');
    die($response);
} else {
    // Invalid endpoint requested
//    header('HTTP/1.1 400 Bad Request');
//    die(json_encode(array('error' => 'Invalid endpoint')));
}

*/

if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=config.php'));


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
    : (is_file('console_app.php') ? 'console_app.php' : (is_file('public/console_app.php') ? 'public/console_app.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=console_app.php'));
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

      
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
#app_github-container {
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


<?php $appGithub['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
  <div id="app_github-container" class="<?= (APP_SELF == __FILE__ || (isset($_GET['app']) && $_GET['app'] == 'github') ? 'selected' : '') ?>" style="border: 1px solid #000;">
    <div class="header ui-widget-header">
      <div style="display: inline-block;">Github.com VCS (Source Code)</div>
      <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_github-container').style.display='none';">X</a>]</div> 
    </div>

      <div style="display: inline-block; width: auto; padding-left: 10px;">
        <iframe src="<?= /* basename(__FILE__) */ NULL; ?>" style="height: 550px; width: 775px;"></iframe>
      </div>

      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->

  </div>
<!-- </div> -->

<?php $appGithub['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<?php $appGithub['script'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<?php $appGithub['html'] = ob_get_contents(); 
ob_end_clean();

is_dir(APP_PATH . APP_BASE['var']) or mkdir(APP_PATH . APP_BASE['var'], 0755);
if (is_file(APP_PATH . APP_BASE['var'] . 'github.com.html')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime(APP_PATH . APP_BASE['var'] . '/github.com.html'))))) / 86400)) <= 0 ) {
    $url = 'https://github.com/barrydit/composer_app';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($html = curl_exec($handle))) 
      file_put_contents(APP_PATH . APP_BASE['var'] . 'github.com.html', $html) or $errors['COMPOSER_LATEST'] = $url . ' returned empty.';
  }
} else {
  $url = 'https://github.com/barrydit/composer_app';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($html = curl_exec($handle))) 
    file_put_contents(APP_PATH . APP_BASE['var'] . 'github.com.html', $html) or $errors['COMPOSER_LATEST'] = $url . ' returned empty.';
}
/*
libxml_use_internal_errors(true); // Prevent HTML errors from displaying
$dom = new DOMDocument(1.0, 'utf-8');
$dom->loadHTML(file_get_contents(check_http_200('https://github.com/barrydit/composer_app') ? 'https://github.com/barrydit/composer_app' : APP_PATH . APP_BASE['var'] . 'github.com.html'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );   
$xpath = new DOMXPath($dom);

// https://stackoverflow.com/questions/27358966/how-can-i-set-x-frame-options-on-an-iframe

//header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
//header('Content-Type: application/json');
//header('Access-Control-Allow-Credentials: true');


$destination = $xpath->query('//head/meta');
$template = $dom->createDocumentFragment();
$template->appendXML('<base href="https://github.githubassets.com/" />');
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

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'php' && APP_DEBUG)
  Shutdown::setEnabled(false)->setShutdownMessage(function() use($dom) {

      return $dom->saveHTML() ?? file_get_contents("https://github.com/barrydit/composer_app"); // $dom->saveHTML(); /* eval('? >' . $project_code); // -wow */
    })->shutdown(); // die();