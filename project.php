<?php

//use Pds\Skeleton;

?>


<?php

require_once 'vendor/autoload.php';

use Psr\Log\LogLevel;

(null !== LogLevel::DEBUG) // isset() || defined('LogLevel') class_exists('LogLevel')
  and $msg = 'Now let\'s use LogLevel... ' . LogLevel::DEBUG . "\n";

// ob_start(); // $test = ob_get_contents();   ob_end_clean();

return <<<END
<html>
<head>
  <title></title>
</head>
<body style="background-color: #fff;">
<code>{$msg}</code>
</body>
</html>
END;



