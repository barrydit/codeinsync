<?php


if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
    if (isset($_POST['cmd']) && $_POST['cmd'] != '')
        if (preg_match('/^perl\s*(:?.*)/i', $_POST['cmd'], $match)) {/*
if (preg_match('/^perl\s+(?!(-r))/i', $_POST['cmd'])) {
$match[1] = trim($match[1], '"');
$output[] = eval ($match[1] . (substr($match[1], -1) != ';' ? ';' : ''));
} else if (preg_match('/^perl\s+(?:(-r))\s+(:?(.*))/i', $_POST['cmd'], $match)) {
$match[2] = trim($match[2], '"');
$_POST['cmd'] = 'perl -r "' . $match[2] . (substr($match[2], -1) != ';' ? ';' : '') . '"';

if (!isset($GLOBALS['runtime']['socket']) || !$GLOBALS['runtime']['socket'])
exec($_POST['cmd'], $output);
else {
$errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";

// Send a message to the server
$errors['server-2'] = 'Client request: ' . $message = "cmd: " . $_POST['cmd'] . "\n";

fwrite($GLOBALS['runtime']['socket'], $message);
$output[] = $_POST['cmd'] . ': ';
// Read response from the server
while (!feof($GLOBALS['runtime']['socket'])) {
$response = fgets($GLOBALS['runtime']['socket'], 1024);
$errors['server-3'] = "Server responce: $response\n";
if (isset($output[end($output)]))
$output[end($output)] .= trim($response);
//if (!empty($response)) break;
}
}
//$output[] = $_POST['cmd'];
}*/

            // Example of running a Python script
            $output = [];
            //$returnVar = 0;
            $_POST['cmd'] = 'perl --version  2>&1';
            // exec("python2.7 -c 'import sys; print(sys.version)'", $output, $returnVar);
//exec("python3 /path/to/your_script.py arg1 arg2", $output, $returnVar);

            // Example of a one-liner Python command
//exec("python3 -c 'print(\"Hello from Python\")'", $output, $returnVar);

            exec($_POST['cmd'], $output);

            if (isset($output) && is_array($output)) {
                switch (count($output)) {
                    case 1:
                        echo /*(isset($match[1]) ? $match[1] : 'PHP') . ' >>> ' .*/ join("\n... <<< ", $output);
                        break;
                    default:
                        echo join("\n", $output);
                        break;
                }
            }

            Shutdown::setEnabled(true)->setShutdownMessage(function () { })->shutdown();

        }