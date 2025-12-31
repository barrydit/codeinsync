<?php
// api/console.php

global $shell_prompt, $errors;
$output[] = 'TEST 123';

if (!function_exists('cis_run_process')) {
    $candidate = APP_PATH . 'bootstrap/process.php';
    if (is_file($candidate)) {
        require_once $candidate;
    }
}

if (!function_exists('cis_run_process')) {
    // Fail fast with a clear error instead of fatal
    return [
        'ok' => false,
        'error' => 'MISSING_DEPENDENCY',
        'message' => 'cis_run_process() is not loaded. Ensure api/console.php (or its defining file) is required.',
    ];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // $auto_clear = isset($_POST['auto_clear']) && $_POST['auto_clear'] == 'on' ? true : false;
    if (isset($_POST['group_type'])) {
        switch ($_POST['group_type']) {
            case 'file':
                touch($_POST['file_name']);
                break;
            case 'dir':
                if (isset($_POST['dir']) && $_POST['dir'] != '') {
                    if (!is_dir($dir = APP_PATH . APP_ROOT . $_GET['path'] . $_POST['name'])) {
                        //mkdir($dir, 0777, true);
                    } else
                        $output[] = "Directory already exists: $dir";
                }
                break;
        }
    } elseif (isset($_POST['cmd'])) {

        chdir(APP_PATH . APP_ROOT);

        $output = [];


        // get_current_user();
        // posix_getpwuid(posix_geteuid())['name'];

        //$output[] = $shell_prompt = 'www-data@localhost:' . getcwd() . '# ' . $_POST['cmd'];
        //$socketInstance = Sockets::getInstance();
        //$GLOBALS['runtime']['socket'] = $socketInstance->getSocket();

        //$output[] = var_export(is_resource($GLOBALS['runtime']['socket']), true);

        //$GLOBALS['runtime']['socket'] = fsockopen(SERVER_HOST, SERVER_PORT, $errno, $errstr, 5);

        if ($_POST['cmd'] && $_POST['cmd'] != '')
            if (preg_match('/^help/i', $_POST['cmd']))
                $output[] = implode(', ', ['install', 'php', 'composer', 'git', 'npm', 'whoami', 'wget', 'tail', 'cat', 'echo', 'env', 'sudo']);
            elseif (preg_match('/^test/i', $_POST['cmd'])) {
                $output[] = $_SERVER['HTTP_REFERER']; // UrlContext::getBaseHref() . '/?' .  $_SERVER['QUERY_STRING'];
            } elseif (preg_match('/^path/i', $_POST['cmd'])) {
                $output[] = realpath(APP_PATH . APP_ROOT . APP_ROOT_DIR) . DIRECTORY_SEPARATOR;
            } elseif (preg_match('/^files/i', $_POST['cmd'])) {
                $output[] = implode(", \n", get_required_files());
            } elseif (preg_match('/^defined/i', $_POST['cmd'])) {
                $output[] = APP_PATH . APP_ROOT . APP_ROOT_DIR; // implode(', ', TESTING);
            } elseif (preg_match('/^123testing/i', $_POST['cmd'])) {
                $output[] = rtrim(UrlContext::getBaseHref(), '/') . $_SERVER['REQUEST_URI'] . '  123';
            } elseif (preg_match('/^whoami(:?(.*))/i', $_POST['cmd'], $match)) {

                // Show the command that was run (your usual style)
                //$output[] = "{$shell_prompt}";

                // Run whoami and capture output into a temp array
                $tmp = [];
                exec('whoami 2>&1', $tmp, $exitCode);

                // Append the result of whoami
                if (!empty($tmp)) {
                    foreach ($tmp as $line) {
                        count($tmp) === 1
                            ? $output[] = $shell_prompt . trim($line)
                            : $output[] = $line;
                    }
                }

                // Exit code if not zero
                if ($exitCode !== 0) {
                    $output[] = "Exit Code: $exitCode";
                }
            } else if (preg_match('/^wget(:?(.*))/i', $_POST['cmd'], $match)) {
                /* https://stackoverflow.com/questions/9691367/how-do-i-request-a-file-but-not-save-it-with-wget */
                // exec("wget -qO- {$match[1]} &> /dev/null", $output);
                // exec("curl -O {$match[1]}", $output);

                $url = $match[1];
                $file = basename(parse_url($url, PHP_URL_PATH));

                $fp = fopen($file, 'wb');

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_FILE => $fp,
                    CURLOPT_FOLLOWLOCATION => true,   // like wget
                    CURLOPT_FAILONERROR => true,   // fail on 4xx/5xx
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_USERAGENT => 'PHP-cURL',
                ]);

                $ok = curl_exec($ch);

                if ($ok === false) {
                    $error = curl_error($ch);
                    curl_close($ch);
                    fclose($fp);
                    unlink($file);
                    throw new RuntimeException("Download failed: $error");
                }

                curl_close($ch);
                fclose($fp);

                $output[] = "Saved as $file\n";

            } elseif (preg_match('/^git\s+/i', $_POST['cmd'])) {
                require_once app_base('api', null, 'abs') . 'git.php';

                $res = handle_git_command($_POST['cmd']);

                // Show prompt
                if (!empty($res['prompt'])) {
                    $output[] = $shell_prompt . $res['prompt'];
                }

                foreach ($res['output'] as $line) {
                    if ($line === '' || $line === null)
                        continue;
                    foreach (explode("\n", rtrim((string) $line)) as $ln) {
                        $output[] = $ln;
                    }
                }

                if (!empty($res['errors'])) {
                    foreach ($res['errors'] as $err) {
                        foreach (explode("\n", rtrim((string) $err)) as $ln) {
                            $output[] = 'Error: ' . $ln;
                        }
                    }
                }
            } elseif (preg_match('/^server\s*start$/i', $_POST['cmd'])) {
                //require_once APP_PATH . 'server.php';

                $GLOBALS['runtime']['socket']->initializeSocket();

                /*
                          if (file_exists($pidFile = APP_PATH . 'server.pid')) {
                            $output[] = 'Server already running ...';
                          } else {
                            handleLinuxSocketConnection(true);
                            $output[] = 'Sockets started ...';
                          }
                */
            } elseif (preg_match('/^sudo\s*(.*)(?=\r?\n$)?/si', $_POST['cmd'], $sudoMatches)) {

                // Arguments after "sudo "
                $sudoArgs = trim($sudoMatches[1]);

                if ($sudoArgs === '') {
                    $output[] = 'Error: sudo requires a command.';
                    return;
                }

                // Build full command
                // We do NOT prefix with APP_SUDO here, because the user explicitly typed "sudo ..."
                // If you want to restrict this (prevent elevation), add a whitelist check here.
                $execCmd = 'sudo ' . $sudoArgs;

                // Show prompt
                $output[] = "{$shell_prompt}" . $execCmd;

                // Execute with proc_open (safe + captures stdout/stderr)
                $descriptorSpec = [
                    0 => ['pipe', 'r'], // stdin
                    1 => ['pipe', 'w'], // stdout
                    2 => ['pipe', 'w'], // stderr
                ];

                $proc = proc_open($execCmd, $descriptorSpec, $pipes);

                if (!is_resource($proc)) {
                    $output[] = 'Failed to open process.';
                    return;
                }

                // We don't need stdin
                fclose($pipes[0]);

                // Capture stdout, stderr
                $stdout = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);

                // Exit code
                $exitCode = proc_close($proc);

                // Output formatting
                if ($stdout !== '' && $stdout !== false) {
                    foreach (explode("\n", rtrim($stdout)) as $line) {
                        $output[] = $line;
                    }
                }

                if ($stderr !== '' && $stderr !== false) {
                    foreach (explode("\n", rtrim($stderr)) as $line) {
                        $output[] = 'Error: ' . $line;
                    }
                }

                if ($exitCode !== 0) {
                    $output[] = "Exit Code: $exitCode";
                }
            }  //else if (preg_match('/^install/i', $_POST['cmd']))
        //include 'templates/' . preg_split("/^install (\s*+)/i", $_POST['cmd'])[1] . '.php';


        /*
        Error: To https://github.com/barrydit/codeinsync.git
         5fbad5b..29f689e  main -> main

        ^To\s(?:[a-z]+\:\/\/)?(?:[a-z0-9\\-]+\.)+[a-z]{2,6}(?:\/\S*)?


        */
        // 


    } else {

        if (!isset($GLOBALS['runtime']['socket']) || !$GLOBALS['runtime']['socket']) {
            //exec($_POST['cmd'], $output);
            if (preg_match('/^(\w+)\s+(:?(.*))/i', $_POST['cmd'], $match))
                if (isset($match[1]) && in_array($match[1], $help = ['tail', 'cat', 'unlink', 'echo', 'env', 'sudo', 'whoami'])) {
                    //exec(APP_SUDO . $match[1] . ' ' . $match[2], $output); // $output[] = var_dump($match);

                    $output[] = APP_SUDO . "$match[1] $match[2]";
                    $proc = proc_open(
                        (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . "$match[1] $match[2]",
                        [
                            ["pipe", "r"],
                            ["pipe", "w"],
                            ["pipe", "w"]
                        ],
                        $pipes
                    );
                    [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
                    $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");

                } else {
                    $output[] = 'Server is not connected. Command not found: ' . $_POST['cmd'];
                    exit;
                }
        } else {
            $errors['server-1'] = "Connected to " . SERVER_HOST . " on port " . SERVER_PORT . "\n";

            if (preg_match('/^composer\s+(:?(.*))/i', $_POST['cmd'], $match))
                $errors['server-2'] = 'Client request: ' . $message = "cmd: composer " . $match[1] . ' --working-dir="' . APP_PATH . APP_ROOT . '"' . "\n";
            elseif (preg_match('/^git\s+(:?(.*))/i', $_POST['cmd'], $match))
                $errors['server-2'] = 'Client request: ' . $message = "cmd: git " . $match[1] . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '"' . "\n";
            else
                $errors['server-2'] = 'Client request: ' . $message = "cmd: " . $_POST['cmd'] . "\n";



            //$socketInstance = Sockets::getInstance(); // new Sockets();

            //$GLOBALS['runtime']['socket'] = $socketInstance->getSocket();

            $output = []; //$_POST['cmd'] . ' test3: ';

            fwrite($GLOBALS['runtime']['socket'], $message);

            $buffer = '';

            $response = '';

            // Read response from the server
            while (!feof($GLOBALS['runtime']['socket'])) {
                $chunk = fgets($GLOBALS['runtime']['socket'], 1024); // Read chunks of 1024 bytes
                echo ' test 123';
                if ($chunk === false) {
                    // Handle any reading error
                    echo '$chunk is false';
                    break;
                }

                // Append the chunk to the response
                $response .= $chunk;

                // Optional: If the server is sending a known termination sequence like \r\n, stop reading when detected
                if (strpos($chunk, "\r\n") !== false) {
                    break;
                }
            }

            $response = trim($response); // Remove any extra whitespace

            if ($response === '') // Handle empty response

                echo 'Empty response 123';
            else // Handle response
                $decodedResponse = json_decode($response, true); // Decode JSON response

            if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) // Handle JSON decoding error
                echo $errors['server-3'] = "Error decoding JSON: " . json_last_error_msg();
            else
                $errors['server-3'] = "Server response: $decodedResponse\n";


            // Append response to the output array
            if (isset($output[count($output) - 1])) { // end()
                $output[count($output) - 1] .= $decodedResponse;
            }

            //$buffer = $decodedResponse;



            // Read response from the server
/*
              while (!feof($GLOBALS['runtime']['socket'])) {
                $response = fgets($GLOBALS['runtime']['socket'], 1024); // Read in chunks of 1024 bytes
                if ($response !== false) {
                    $buffer .= $response; // Accumulate the response
                }

                  $response = fgets($GLOBALS['runtime']['socket'], 1024); // Reading the response 1024 bytes at a time
                  $errors['server-3'] = "Server response: $response\n";
          
                  // Append or add the response to the output array
                  if (end($output) !== false) {
                      $output[key($output)] .= trim($response);
                  } else {
                      $output[] = trim($response);
                  }
          
                  // Check for an empty response or end-of-message (optional)
                  if (!empty($response)) {
                      break; // Exit loop on receiving a non-empty response, or continue based on your logic
                  }
              }
*/
            //die(var_dump($GLOBALS['runtime']['socket']));

            fclose($GLOBALS['runtime']['socket']);
        }


        //

    }
    //else var_dump(NULL); // eval('echo $repo->status();')
    //return var_dump($output);
    if (isset($output) && is_array($output)) {
        switch (count($output)) {
            case 1:
                return /*(isset($match[1]) ? $match[1] : 'PHP') . ' >>> ' . */ join("\n... <<< ", $output);
                break;
            default:
                return join("\n", $output);
            //break;
        } // . "\n"
        //$output[] = 'post: ' . var_dump($_POST);
        //else var_dump(get_class_methods($repo));
    }
    //echo $buffer;
    //Shutdown::setEnabled(true)->setShutdownMessage(function () { })->shutdown();
    //exit();
}
//return 'hello world';