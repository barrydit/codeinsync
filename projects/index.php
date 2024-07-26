<?php
//if (__FILE__ != get_required_files()[0])
//die(__FILE__ . '==' . $_SERVER["SCRIPT_FILENAME"]);
//die(realpath($_SERVER["SCRIPT_FILENAME"]) . '==' . get_required_files()[0]);
//

if (realpath($_SERVER["SCRIPT_FILENAME"]) == get_required_files()[0] && in_array(__FILE__, get_required_files()) ) {
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    if (is_file($path = realpath('../config/config.php'))) {
      require_once $path;
    }
  } elseif (is_file($path = realpath('config/config.php'))) {
    require_once $path;
  } else {
    die(var_dump("Path was not found. file=$path"));
  }
  //require_once(realpath('../config/constants.php')); 
}

if (isset($_ENV['COMPOSER']['AUTOLOAD']) && (bool) $_ENV['COMPOSER']['AUTOLOAD'] === true)
  require_once APP_PATH . APP_BASE['vendor'] . 'autoload.php';

ob_start(); // dd('Break/test the dd();');
use noximo\PHPColoredAsciiLinechart\Settings;
use noximo\PHPColoredAsciiLinechart\Linechart;
use noximo\PHPColoredAsciiLinechart\Colorizers\HTMLColorizer;

$linechart = new Linechart();
$settings = new Settings();  // Settings are needed in this case
$settings->setColorizer(new HTMLColorizer());  // Here you need to set up HTMLColorizer

$lineA = [];
for ($i = 0; $i < +120; $i++) {
    $lineA[] = 5 * sin($i * ((M_PI * 4) / 120));
}

$linechart->addLine(0, ['color:purple'], Linechart::FULL_LINE);  // Use css styles instead of ascii color codes
$linechart->addMarkers($lineA, ['color: green'], ['color: red']);
$linechart->setSettings($settings);

echo $linechart->chart();
?>

<div class="table-container">
<table border="1" style="">
<tr><td>Created</td><td>a</td><td>table</td><td>columns</td></tr>
<tr><td>A</td><td>new</td><td>row</td></tr>
<tr><td>And</td><td>some</td><td>more</td><td>rows</td></tr>
</table>

<table border="1" style="">
<tr><td>Create New</td><td>a</td><td>table</td><td>columns</td></tr>
<tr><td>A</td><td>new</td><td>row</td></tr>
<tr><td>And</td><td>some</td><td>more</td><td>rows</td></tr>
</table>
</div>
<div style="clear: both;"></div>

<?php

// Capture the output into a variable
$output = ob_get_clean();
ob_end_clean();

$output = ($output == '' ? ' ' : $output);

return <<<END
<!DOCTYPE html>
<html>
<head>
  <title></title>

<style>
/* * { border: 1px dashed red; } */
pre {
    white-space: pre-wrap;
    background: hsl(220, 80%, 90%);
}
    .table-container {
      width: 100%;
      margin: 10px 0; /* Adjust margin as needed */
    }

    .table-container table {
      float: left;
      width: 45%; /* Adjust width as needed */
      margin-right: 5%; /* Adjust margin as needed */
      border-collapse: collapse;
    }

    .table-container table:last-child {
      margin-right: 0; /* Remove margin for the last table */
    }

</style>

</head>
<body style="background-color: #fff;">
<pre style="text-align: center;"><code>|||| Demonstrational Purposes ||||<br />--[Save] to Update--</code></pre>
{$output}
</body>
</html>
END
;