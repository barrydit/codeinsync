<?php

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
    if (isset($_POST['cmd']) && $_POST['cmd'] != '')
        if (preg_match('/^python\s*(:?.*)/i', $_POST['cmd'], $match)) {
            /*
            if (preg_match('/^python\s+(?!(-r))/i', $_POST['cmd'])) {
                $match[1] = trim($match[1], '"');
                $output[] = eval ($match[1] . (substr($match[1], -1) != ';' ? ';' : ''));
            } else if (preg_match('/^python\s+(?:(-r))\s+(:?(.*))/i', $_POST['cmd'], $match)) {
                $match[2] = trim($match[2], '"');
                $_POST['cmd'] = 'python -r "' . $match[2] . (substr($match[2], -1) != ';' ? ';' : '') . '"';

                if (!isset($_SERVER['SOCKET']) || !$_SERVER['SOCKET'])
                    exec($_POST['cmd'], $output);
                else {
                    $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";

                    // Send a message to the server
                    $errors['server-2'] = 'Client request: ' . $message = "cmd: " . $_POST['cmd'] . "\n";

                    fwrite($_SERVER['SOCKET'], $message);
                    $output[] = $_POST['cmd'] . ': ';
                    // Read response from the server
                    while (!feof($_SERVER['SOCKET'])) {
                        $response = fgets($_SERVER['SOCKET'], 1024);
                        $errors['server-3'] = "Server responce: $response\n";
                        if (isset($output[end($output)]))
                            $output[end($output)] .= trim($response);
                        //if (!empty($response)) break;
                    }
                }
                //$output[] = $_POST['cmd'];
            } else {}*/

            // Example of running a Python script
            $output = [];
            $returnVar = 0;
            $_POST['cmd'] = 'python --version 2>&1';
            // exec("python2.7 -c 'import sys; print(sys.version)'", $output, $returnVar);
            //exec("python3 /path/to/your_script.py arg1 arg2", $output, $returnVar);

            // Example of a one-liner Python command
            //exec("python3 -c 'print(\"Hello from Python\")'", $output, $returnVar);

            //exec("python2.7 -c 'import sys; print(sys.version)'", $output, $returnVar);

            exec($_POST['cmd'], $output, $returnVar);

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
        } elseif (preg_match('/^(g[cc|\+\+]+)\s*(:?.*)/i', $_POST['cmd'], $match)) {
            $output = [];
            $returnVar = 0;
            $_POST['cmd'] = "$match[1] --version";

            exec($_POST['cmd'], $output, $returnVar);

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