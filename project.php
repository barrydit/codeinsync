<?php


//use Pds\Skeleton;
if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null))) require_once($path);
else die(var_dump($path . ' path was not found. file=config.php'));

/*
function nl2p(string $string, $line_breaks = true, $xml = true)
{
    $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);
    if ($line_breaks == true) {
        return '<p>' . preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '$1<br' . ($xml == true ? ' /' : '') . '>$2'), trim($string)) . '</p>';
    } else {
        return '<p>' . preg_replace(
                array("/([\n]{2,})/i", "/([\r\n]{3,})/i", "/([^>])\n([^<])/i"),
                array("</p>\n<p>", "</p>\n<p>", '$1<br' . ($xml == true ? ' /' : '') . '>$2'),

                trim($string)) . '</p>';
    }
}

dd(nl2p("\n\n" . 'testing'));
*/


require_once 'vendor/autoload.php';

use Psr\Log\LogLevel;

use Arua\Session;

$session_factory = new \Aura\Session\SessionFactory;
$session = $session_factory->newInstance($_COOKIE);

$session->setCookieParams(array('lifetime' => APP_TIMEOUT ?? '1209600')); 

//(null !== LogLevel::DEBUG) // isset() || defined('LogLevel') class_exists('LogLevel')
//  and $msg = 'Now let\'s use LogLevel... ' . LogLevel::DEBUG . "\n";

// ob_start(); // $test = ob_get_contents();   ob_end_clean();

ob_start();
// Dump the variable
var_dump($session);

// Capture the output into a variable
$output = ob_get_clean();


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
