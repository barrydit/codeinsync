<?php

//require_once('session.php');

//dd( dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');

if (__FILE__ == get_required_files()[0]) { //die(getcwd());
  if (
    $path = (basename(getcwd()) == 'public')
    ? (is_file('config.php') ? 'config.php' : '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php') : ''
  )
    require_once $path;
  else
    die(var_dump("$path path was not found. file=config.php"));
} elseif (!in_array($path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php', get_required_files()))
  require_once $path;

if (!empty($_SERVER['REQUEST_URI']))
  $path = $_SERVER['DOCUMENT_ROOT'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


/*
require_once realpath('index.php');
*/
//dd( realpath($path) == realpath(APP_PATH) );

//dd(APP_ERRORS);

//dd($_ENV);
if (defined('APP_ENV'))
  if (APP_ENV == 'development' && defined('APP_ERRORS') && APP_ERRORS && defined('APP_DEBUG') && APP_DEBUG == $report_errors = true)
    NULL;
//elseif (APP_ENV == 'development' && APP_DEBUG == false)
//require_once APP_PATH . 'public' . DIRECTORY_SEPARATOR . 'idx.develop.php';
//  die(header('Location: ' . (!defined('APP_URL_BASE') and 'http://' . APP_DOMAIN . APP_URL_PATH) . '?debug'));
// is_array($ob_content)
//$report_errors = true; 

// realpath($path) == "/mnt/c/www/public/composer" ... $path == "[/var/www/public]/composer/" == $_SERVER['DOCUMENT_ROOT'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)


// Initialize an associative array with the desired keys
//$files = array( /* 0 => array('path', 'filesize') */
//    'config' => glob(APP_PATH . 'config' . DIRECTORY_SEPARATOR . '*.php'),
//    'data' => glob(APP_PATH . 'data' . DIRECTORY_SEPARATOR . '*.sql'),
//    'public' => glob(APP_PATH . 'public' . DIRECTORY_SEPARATOR . '*.php'),
//    'src' => glob(APP_PATH . 'src' . DIRECTORY_SEPARATOR . '*.php')
//);

//foreach (array_merge(glob(APP_PATH . '*.php'), glob(APP_PATH . '**/*.php', GLOB_BRACE | GLOB_NOSORT)) as $filename) {
//    if ($filename == APP_SELF) continue;
//    if ($filename == APP_PATH . 'composer-setup.php') continue;
//    $files[] = ['path' => $filename, 'filesize' => filesize($filename), 'filemtime' => filemtime($filename)];
//}

if (preg_match('/^([\w\-.]+)\.php$/', basename(__FILE__), $matches))
  ${$matches[1]} = $matches[1];


ob_start(); ?>
html, body {
height: 100%;
margin: 0;
padding: 0;
}
<?php $app[$debug]['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
<div></div>
<?php $app[$debug]['body'] = ob_get_contents();
ob_end_clean();

if (false) { ?>
  <script type="text/javascript">
  <?php }
ob_start(); ?>
  // Javascript comment
  <?php $app[$debug]['script'] = ob_get_contents();
  ob_end_clean();

  if (false) { ?></script><?php }

  $files = get_required_files();
  $baseDir = APP_PATH;
  $organizedFiles = [];
  $directoriesToScan = [];

  // Collect directories from the list of files
  foreach ($files as $key => $file) {
    $relativePath = str_replace($baseDir, '', $file);
    $directory = dirname($relativePath);
    //var_dump($directory);
    if (preg_match('/^vendor(\/.*$|)/', $directory)) {
      unset($files[$key]);
      continue;
    }
    if (!in_array($directory, $directoriesToScan)) {
      $directoriesToScan[] = $directory;
    }
    // Add the relative path to the organizedFiles array if it is a .php file and not already present
    if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles)) {
      $organizedFiles[] = $relativePath;
    }
  }

  // Add non-recursive scanning for the root baseDir for *.php files
  $rootPhpFiles = glob($baseDir . '{*.php}', GLOB_BRACE);
  foreach ($rootPhpFiles as $file) {
    if (is_file($file)) {
      $relativePath = str_replace($baseDir, '', $file);
      // Add the relative path to the array if it is a .php file and not already present
      if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles)) {
        if ($relativePath == 'composer-setup.php')
          continue;
        $organizedFiles[] = $relativePath;
      }
    }
  }

  // Scan the specified directories
  scanDirectories($directoriesToScan, $baseDir, $organizedFiles);

  // Display the results
  $sortedArray = customSort($organizedFiles);

  $total_include_files = count($files);
  $total_include_lines = 0;
  $total_filesize = 0;
  $total_files = count($sortedArray);
  $total_lines = 0;

  //dd($files);
  
  foreach ($sortedArray as $key => $path) {
    $sortedArray[$key] = ['path' => APP_PATH . $path, 'filesize' => filesize(APP_PATH . $path), 'filemtime' => filemtime(APP_PATH . $path)];
    //$total_files++;
    in_array($sortedArray[$key]['path'], $files) and $total_include_lines += count(file($sortedArray[$key]['path'])); //or $total_include_files++;
    $total_filesize += $sortedArray[$key]['filesize'];
    $total_lines += count(file($sortedArray[$key]['path']));
  }


  //die(include 'example-nodes.php');
  

  if (isset($_GET['debug']) && $_GET['debug'] == 'stats') {
    //define('APP_END',     microtime(true));
  
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<style>
body { background-color: #fff; }
</style>
HTML;
    echo <<<SCRIPT
<script>
window.onload = function() {

var chart = new CanvasJS.Chart("chartContainer", {
	animationEnabled: true,
	title: {
		text: "Used and vs Unused Memory"
	},
	data: [{
		type: "pie",
		startAngle: 300,
		yValueFormatString: "##0.00\"%\"",
		indexLabel: "{label} {y}",
		dataPoints: [

SCRIPT;

    echo '			{y: ' . abs(round((1 - memory_get_usage() / convertToBytes(ini_get('memory_limit'))) * 100, 2)) . ', label: "[avail. memory]"},' . "\n";

    echo '			{y: ' . round((1 - (convertToBytes(ini_get('memory_limit')) - memory_get_usage()) / convertToBytes(ini_get('memory_limit'))) * 100, 2) . ', label: "[used memory]"}' . "\n";

    //echo '			{y: 7.31, label: "public/index"},' . "\n";
//echo '			{y: 4.00, label: "config"},' . "\n";
//echo '			{y: 7.06, label: "database"},' . "\n";
//echo '			{y: 1.26, label: "debug"},' . "\n";
//echo '			{y: 4.91, label: "functions"}' . "\n";
  
    echo <<<SCRIPT
		]
	}]
});
chart.render();

var chart2 = new CanvasJS.Chart("chartContainer2", {
	animationEnabled: true,
	title: {
		text: "Source code vs (Un)Executed code"
	},
	data: [{
		type: "pie",
		startAngle: 0,
		yValueFormatString: "##0.00\"%\"",
		indexLabel: "{label} {y}",
		dataPoints: [

SCRIPT;

    ob_start();
    $sql_lines = 0;
    foreach (glob("database/*.sql") as $filename) {
      $lines = count(file($filename));
      $sql_lines += $lines;
      //echo '			{y: ' . abs(round((1 - $lines / $total_lines) * 100 - 100, 2)) . ', label: "' . (basename(dirname($requireFile, 1)) == 'public' ? 'public/' : '') . pathinfo(basename($requireFile), PATHINFO_FILENAME) . '"},' . "\n";
    }
    ob_end_clean();

    ob_start();
    $loaded_lines = 0;

    $percentage = ($total_lines != 0) ? abs(round((1 - $sql_lines / $total_lines) * 100 - 100, 2)) : 0;

    echo '			{y: ' . $percentage . ', label: "[sql schema]"},' . "\n";

    $total_lines -= $sql_lines;
    foreach (get_required_files() as $requireFile) {
      if (basename(dirname($requireFile, 2)) == 'vendor')
        continue; // $lines = count(file($requireFile));
      if (basename(dirname($requireFile, 3)) == 'vendor')
        continue; // $lines = count(file($requireFile));
      elseif (basename(dirname($requireFile, 4)) == 'vendor')
        continue;
      elseif (basename(dirname($requireFile, 5)) == 'vendor')
        continue;
      elseif (basename(dirname($requireFile, 6)) == 'vendor')
        continue;
      else
        $lines = count(file($requireFile));
      $loaded_lines += $lines;

      $percentage = ($total_lines != 0) ? abs(round((1 - $lines / $total_lines) * 100 - 100, 2)) : 0;

      echo '			{y: ' . $percentage . ', label: "' . (basename(dirname($requireFile, 1)) == 'public' ? pathinfo(basename($requireFile), PATHINFO_FILENAME) : pathinfo(basename($requireFile), PATHINFO_FILENAME)) . '"},' . "\n";
    }
    $output = ob_get_contents();
    ob_end_clean();


    $percentage = ($total_lines != 0) ? abs(round((1 - $lines / $total_lines) * 100 - 100, 2)) : 0;

    echo '			{y: ' . $percentage . ', label: "[source]"},' . "\n";

    echo $output;
    //echo '			{y: 7.31, label: "public/index"},' . "\n";
//echo '			{y: 4.00, label: "config"},' . "\n";
//echo '			{y: 7.06, label: "database"},' . "\n";
//echo '			{y: 1.26, label: "debug"},' . "\n";
//echo '			{y: 4.91, label: "functions"}' . "\n";
  
    echo <<<SCRIPT
		]
	}]
});
chart2.render();

}
</script>
SCRIPT;
    echo <<<HTML
</head>




<body>

  <form style="display: inline;" action="" method="POST">
      Environemnt: <select name="environment" onchange="this.form.submit();">
        <option value="develop">Development</option>
        <option value="product">Production</option>
        <option value="math" selected="">Math</option>
      </select>
  </form>
<br />
HTML;
    defined('APP_END') or define('APP_END', microtime(true));
    echo '<div style="display: block;" title="APP_START - APP_END"><em>Execution time: <b>' . round(APP_END - APP_START, 6) . '</b> secs ' . "<br />\n" . 'Mem: ' . formatSizeUnits(memory_get_usage()) . "<br />\n" . ' Max: ' . formatSizeUnits(convertToBytes(ini_get('memory_limit'))) . '</em></div>' . "\n";
    echo '<div style="display: inline-block; float: right; text-align: right;">'
      . '  <em style="font-size: 13px;">Source (code): [<b>' . formatSizeUnits($total_filesize) . '</b>]  [<b>' . $total_files . '</b> files]  [<b>' . ($total_lines + $sql_lines) . '</b> lines]</em>'
      . '</div>';
    echo '<div id="chartContainer" style="height: 195px; display: inline-block; width: 46%;"></div>' . "\n";
    echo '<div id="chartContainer2" style="height: 195px; display: inline-block; width: 46%;"></div>' . "\n";
    echo '<pre style="font-size: 12px;">' . "\n";
    // print_r(get_defined_constants(true)['user']);
    echo '</pre>' . "\n";
    echo '<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>' . "\n";
    echo '</body>' . "\n";
    echo '</html>' . "\n";
    exit; // include APP_PATH . '/src/debug.php'; 
  
    /*
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) { // ($_SERVER["SCRIPT_NAME"] != APP_ROOT_URL . 'index.php') 
      require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'session.php';
      header('Location: ' . APP_URL_BASE . '?' . http_build_query(['file' => 'debug']));
      exit();
    }
    */
    ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Patient Clinic Files -- Debug Window</title>

    <base href="<?= !is_array(APP_URL) ? APP_URL : APP_URL_BASE ?>">

    <link rel="shortcut icon" href="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH ?>favicon.ico" />

    <!-- BOOTSTRAP STYLES-->
    <link rel="stylesheet" type="text/css"
      href="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH ?>assets/css/bootstrap/bootstrap.min.css" />

    <link rel="stylesheet" type="text/css"
      href="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH ?>assets/css/styles.css" />

    <style></style>

    <script></script>

  </head>

  <body>
    <div id="page-inner">
      <div class="row">
        <div class="col-md-12">
          <div class="panel panel-primary">
            <div class="panel-heading">
              <div style="float: left; padding-top: 5px;">
                <a href="<?= APP_BASE_URI ?>" style="color: white; text-decoration:none;"><img
                    src="<?= APP_BASE_URI ?>assets/images/favicon.png" width="32" height="32"> Debug Window</a>
              </div>
              <div style="float: right;">
                <a class="btn btn-primary"
                  href="<?= APP_BASE_URI . '?file=entry_form&amp;patient_id=' . $_SESSION['patient_id'] ?>" style="">New
                  Patient Form</a>
                <a class="btn btn-primary" href="<?= APP_BASE_URI . '?file=database' ?>" style="">Database</a>
                <a class="btn btn-primary" href="<?= APP_BASE_URI . '?auth=logout' ?>" style="">Logout</a>
              </div>
              <div class="clearfix"></div>
            </div>
            <div class="panel-body">
              <div class="col-md-12 h-100">
                <!-- <div class="form-group">
                    <label for="display_name">Display Name</label>
                    <input type="input" name="display_name" class="form-control" value="">
          </div> -->

                <div class="form-group" style="height: 85vh;">
                  <?php
                  echo '<pre>';
                  echo 'Session_id: ' . session_id() . ' ' . '(' . strlen(session_id()) . ')' . "\n\n";

                  echo 'Defined Constants [User]: ';
                  print_r(get_defined_constants(true)['user']); // [Core] | [pcre]
                
                  echo '$_SERVER["HTTPS"]: ' . ($_SERVER['HTTPS'] ?? 'off') . "\n";
                  echo '$_SERVER["REQUEST_URI"]: ' . $_SERVER['REQUEST_URI'] . "\n";
                  echo '$_SERVER["HTTP_REFERER"]: ' . ($_SERVER['HTTP_REFERER'] ?? '&lt;none&gt;') . "\n\n";

                  echo 'Included files: ';
                  print_r(get_included_files());
                  echo 'Required files: ';
                  print_r(get_required_files());

                  echo 'REQUEST: ';
                  print_r($_REQUEST);
                  echo 'GET: ';
                  print_r($_GET);
                  echo 'POST: ';
                  print_r($_POST);
                  echo 'FILES: ';
                  print_r($_FILES);
                  echo 'ENV: ';
                  print_r($_ENV);
                  echo 'Cookies: ';
                  print_r($_COOKIE);
                  echo 'Sessions: ';
                  print_r($_SESSION);

                  echo '</pre>';

                  /*
                      echo 'APP_URL_BASE: ' . APP_URL_BASE . "<br />\n";
                      echo 'REQUEST_URI: ' . ltrim($_SERVER['REQUEST_URI'], '/') . "<br />\n";
                      echo 'Location: ' . preg_replace("/^http:/i", "https:", APP_URL_BASE . $_SERVER['REQUEST_URI']) . "<br />\n";
                    // echo '__FILE__: ' . __FILE__;
                    echo 'Basename ' . basename($_SERVER['REQUEST_URI']) . "<br />\n";
                      die('PHP_SELF: ' . $_SERVER['PHP_SELF']);
                  */

                  $indicesServer = [
                    'PHP_SELF',
                    'argv',
                    'argc',
                    'GATEWAY_INTERFACE',
                    'SERVER_ADDR',
                    'SERVER_NAME',
                    'SERVER_SOFTWARE',
                    'SERVER_PROTOCOL',
                    'REQUEST_METHOD',
                    'REQUEST_TIME',
                    'REQUEST_TIME_FLOAT',
                    'QUERY_STRING',
                    'DOCUMENT_ROOT', // /var/www/public
                    'HTTP_ACCEPT',
                    'HTTP_ACCEPT_CHARSET',
                    'HTTP_ACCEPT_ENCODING',
                    'HTTP_ACCEPT_LANGUAGE',
                    'HTTP_CONNECTION',
                    'HTTP_HOST',
                    'HTTP_REFERER',
                    'HTTP_USER_AGENT',
                    'HTTPS',
                    'REMOTE_ADDR',
                    'REMOTE_HOST',
                    'REMOTE_PORT',
                    'REMOTE_USER',
                    'REDIRECT_REMOTE_USER',
                    'SCRIPT_FILENAME',
                    'SERVER_ADMIN',
                    'SERVER_PORT',
                    'SERVER_SIGNATURE',
                    'PATH_TRANSLATED',
                    'SCRIPT_NAME',
                    'REQUEST_URI',
                    'PHP_AUTH_DIGEST',
                    'PHP_AUTH_USER',
                    'PHP_AUTH_PW',
                    'AUTH_TYPE',
                    'PATH_INFO',
                    'ORIG_PATH_INFO'
                  ];

                  echo '<table cellpadding="10">';
                  foreach ($indicesServer as $arg) {
                    if (isset($_SERVER[$arg])) {
                      echo '<tr><td>' . $arg . '</td><td>' . $_SERVER[$arg] . '</td></tr>';
                    } else {
                      echo "<tr><td>$arg</td><td>-</td></tr>";
                    }
                  }
                  foreach (array_keys($_SESSION) as $arg) {
                    if (isset($_SESSION[$arg])) {
                      echo '<tr><td>' . $arg . '</td><td>' . $_SESSION[$arg] . '</td></tr>';
                    } else {
                      echo "<tr><td>$arg</td><td>-</td></tr>";
                    }
                  }
                  foreach (array_keys($_REQUEST) as $arg) {
                    if (isset($_REQUEST[$arg])) {
                      echo '<tr><td>' . $arg . '</td><td>' . "{$_REQUEST[$arg]}" . '</td></tr>';
                    } else {
                      echo "<tr><td>$arg</td><td>-</td></tr>";
                    }
                  }
                  foreach (array_keys($_FILES) as $arg) {
                    if (isset($_FILES[$arg])) {
                      echo '<tr><td>' . $arg . '</td><td>' . $_FILES[$arg] . '</td></tr>';
                    } else {
                      echo "<tr><td>$arg</td><td>-</td></tr>";
                    }
                  }
                  echo '</table>';
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- JQUERY SCRIPTS -->
    <script
      src="<?= !defined('APP_URL_BASE') and '//' . APP_DOMAIN . APP_URL_PATH . APP_BASE['resources'] ?>js/jquery/jquery-3.5.1.min.js"></script>
  </body>

  </html>
<?php } ?>