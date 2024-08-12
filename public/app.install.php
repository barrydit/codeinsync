<?php

if (preg_match('/^app\.([\w\-.]+)\.php$/', basename(__FILE__), $matches))
  ${$matches[1]} = $matches[1];

ob_start(); ?>
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

<?php $app[$install]['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
<div style="position: relative; margin: 0 auto; border: 1px solid #000;">
<div style="position: absolute; top: 0; left: 50%; transform: translate(-50%, 10%); text-align: center; width: 570px; height: 600px; background-position: center; background-size: cover; background-repeat: no-repeat; background-image: url('/resources/images/install-scenario-small.gif'); opacity: 0.8; z-index: 1; border: 1px solid #000;">

<div style="position: absolute; top: 225px; left: 129px; width: 230px; height: 200px; border: 1px dashed #000;">
<form>
<div style="position: absolute; top: 30px; left: 28px;"><input type="radio" name="scenario" value="1" checked /></div>

<div style="position: absolute; top: 30px; right: 20px;"><input type="radio" name="scenario" value="2" /></div>

<div style="position: absolute; bottom: 34px; right: 20px;"><input type="radio" name="scenario" value="3" /></div>
</form>
</div>

</div>
</div>
<?php $app[$install]['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<?php $app[$install]['script'] = ob_get_contents();
ob_end_clean();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");


ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="pragma" content="no-cache">
  <meta http-equiv="expires" content="0">

<style>
 <?= $app[$install]['style']; ?>
</style>
</head>
<body>
  <?= $app[$install]['body']; ?>
</body>
</html>
<?php $app[$install]['html'] = ob_get_contents();
ob_end_clean();

//if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'backup' && APP_DEBUG)
//  Shutdown::setEnabled(false)->setShutdownMessage(function() {
//      return $app[$install]['html'];  // -wow */
//    })->shutdown(); // die();ob_start();
?>