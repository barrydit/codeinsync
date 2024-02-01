<?php
if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? '' : (is_file('config.php') ? 'config.php' : 'config/config.php')) require_once($path); 
else die(var_dump($path . ' path was not found. file=config.php'));
ob_start(); // dd('Break/test the dd();'); ?>

                                   |||| Demonstration Purposes ||||


<table border="1">
<tr><td>Create</td><td>a</td><td>table</td><td>columns</td></tr>
<tr><td>A</td><td>new</td><td>row</td></tr>
<tr><td>And</td><td>some</td><td>more</td><td>rows</td></tr>

</table>

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
code { 
    background: hsl(220, 80%, 90%);
    display:block;
    white-space:pre-wrap;
}

pre {
    white-space: pre-wrap;
    background: hsl(30,80%,90%);
}
</style>

</head>
<body style="background-color: #fff;">
<pre><code>{$output}</code></pre>
</body>
</html>
END;
