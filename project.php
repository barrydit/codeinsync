<?php
if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? '' : (is_file('config.php') ? 'config.php' : '')) require_once($path); 
else die(var_dump($path . ' path was not found. file=config.php'));

ob_start();
// Dump the variable

echo 'Hello World!';

// Capture the output into a variable
$output = ob_get_clean();
ob_end_clean();

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
