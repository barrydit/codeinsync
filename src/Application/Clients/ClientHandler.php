<?php

namespace CodeInSync\Application\Clients;

class ClientHandler
{
    protected SocketServer $server;
    protected Logger $logger;

    public function __construct(SocketServer $server, Logger $logger)
    {
        $this->server = $server;
        $this->logger = $logger;
    }

    public function handle(string $input): string
    {
        // move logic here from clientInputHandler() and cleanly replace globals
        // $this->server->getSocket(), $this->logger->log(), etc.

        $this->logger->info("Handling input: $input");

        if ($input == '')
            return null;
        error_log('Client [Input]: ' . trim($input));
        echo 'Client [Input]: ' . trim($input) . "\n";
        //$input = trim($input);
        $output = '';

        if (is_file($pid_file = PID_FILE ?? APP_PATH . 'server.pid') && $pid = (int) file_get_contents($pid_file)) {
            if (stripos(PHP_OS, 'WIN') === 0) {
                exec("tasklist /FI \"PID eq $pid\" 2>NUL | find /I \"$pid\" >NUL", $output, $status);
                if ($status === 0) {
                    $output = "Server is already running with PID $pid\n";
                    echo $output;
                    return $output;
                }
            } else {
                if (extension_loaded('pcntl') && function_exists('posix_kill') && posix_kill($pid, 0)) {
                    $output = "Server is already running with PID $pid\n";
                    echo $output;
                    return $output;
                }
            }
        } else if (isset($_SERVER['SUPERVISOR_ENABLED']) && $_SERVER['SUPERVISOR_ENABLED'] == '1') {
            file_put_contents($pid_file, $pid = getmypid());
            return $output;
        }

        if (preg_match('/^cmd:\s*(shutdown|server\s*(shutdown))\s*(?:(-f))?(?=\r?\n$)?/si', $input, $matches)) {
            //signalHandler(SIGTERM); // $running = false;
            //$output .= "Shutting down... \n"; // Written by Barry Dick (2024)";
            $output .= str_replace('{{STATUS}}', 'Shutting down... PID=' . getmypid() . str_pad('', 15, " "), APP_DASHBOARD) . PHP_EOL;
            $running = false;
            //$output .= var_export($matches, true);
            if ($matches[3] == '-f') {
                signalHandler(SIGTERM) and unlink(PID_FILE); /* exit(1); */
            }
            return $output;
        } elseif (preg_match('/^cmd:\s*server\s*restart(?=\r?\n$)?/si', $input, $matches)) {
            signalHandler(SIGHUB); // SIGCHLD
            $output = str_replace('{{STATUS}}', 'Server Restarting... PID=' . getmypid() . str_pad('', 12, " "), APP_DASHBOARD) . PHP_EOL;
        } elseif (preg_match('/^cmd:\s*server\s*status(?=\r?\n$)?/si', $input)) {
            $output .= str_replace('{{STATUS}}', 'Server is running... PID=' . getmypid() . str_pad('', 12, " "), APP_DASHBOARD) . PHP_EOL;
        } elseif (preg_match('/^cmd:\s*chdir\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
            ini_set('log_errors', 'false');
            $output = "Changing directory to " . ($path = APP_PATH . APP_ROOT . trim($matches[1]) . '/');
            if ($path = realpath($path)) {
                $output = "Changing step 2 directory to $matches[1]";
                $resultValue = (function () use ($path) {
                    $basePath = rtrim(APP_PATH . APP_ROOT, DIRECTORY_SEPARATOR); // Normalize base path
                    $_GET['path'] = preg_replace(
                        '#' . preg_quote($basePath, '#') . '#',
                        '',
                        $path
                    );

                    // Replace the escaped APP_PATH and APP_ROOT with the actual directory path
                    if (realpath($_GET['path']) == realpath($basePath))
                        $_GET['path'] = '';
                    $tableValue = '';
                    require_once APP_BASE['app'] . 'directory.php';
                    //dd('Path: ' . $_GET['path'] . "\n", false);
                    //dd(getcwd());
                    ob_start();
                    if (($tableValue = $tableGen()) != null) {
                        ob_end_clean();
                        return $tableValue; // $app['directory']['body'];
                    }
                })();
                $output = $resultValue;
            }
            ini_set('log_errors', 'true');
        } elseif (preg_match('/^cmd:\s*edit\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
            ini_set('log_errors', 'false');
            //$output = ($file = rtrim(realpath(APP_PATH . ($_GET['path'] ?? '')), '/') . '/' . trim($matches[1])) ? file_get_contents($file) : "File not found: $file";

            $file = realpath(APP_PATH . ($_GET['path'] ?? '')) . DIRECTORY_SEPARATOR . trim($matches[1]);

            if (file_exists($file) && is_file($file)) {
                $output = file_get_contents($file);
            } else {
                $output = "File not found test: $file";
                ini_set('log_errors', 'true'); // Ensure logging is enabled
                error_log($output);
            }

        } elseif (preg_match('/^cmd:\s*server\s*backup(?=\r?\n$)?/si', $input, $matches)) {
            //$input = trim($input);
            $output = '';

            $file = APP_BASE['data'] . 'source_code.json';

            // Retrieve file metadata using stat()
            $fileStats = stat($file);

            // Extract the last modification time from the stat results
            $statMtime = $fileStats['mtime']; // filemtime(__FILE__);

            // Clear the file status cache
            clearstatcache(true, $file);

            // Clear OPcache if enabled
            //if (function_exists('opcache_invalidate')) opcache_invalidate(__FILE__, true);

            // Get the current date (without time)
            $currentDate = date('Y-m-d');

            // Get the modification date of the file (without time)
            $fileModDate = date('Y-m-d', $statMtime);

            // Compare the dates
            if ($fileModDate !== $currentDate) {
                // Run your code only if the file was not modified today
                $output = "File was modified on: " . date('F d Y H:i:s', $statMtime) . "\n";
                require_once 'public' . DIRECTORY_SEPARATOR . 'idx.product.php';

                $files = get_required_files();
                $baseDir = APP_PATH;
                $organizedFiles = [];
                $directoriesToScan = [];

                // Collect directories from the list of files
                foreach ($files as $file) {
                    $relativePath = str_replace($baseDir, '', $file);
                    $directory = dirname($relativePath);
                    if (!in_array($directory, $directoriesToScan))
                        $directoriesToScan[] = $directory;

                    // Add the relative path to the organizedFiles array if it is a .php file and not already present
                    if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles))
                        $organizedFiles[] = $relativePath;
                    elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'htaccess' && !in_array($relativePath, $organizedFiles))
                        $organizedFiles[] = $relativePath;
                }

                // Add non-recursive scanning for the root baseDir for *.php files
                $rootPhpFiles = glob("{$baseDir}{*.php,.env.example,.gitignore,.htaccess,*.md,LICENSE,*.js,composer.json,package.json,settings.json}", GLOB_BRACE);
                foreach ($rootPhpFiles as $file) {
                    if (is_file($file)) {
                        $relativePath = str_replace($baseDir, '', $file);
                        // Add the relative path to the array if it is a .php file and not already present
                        if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles)) {
                            if ($relativePath == 'composer-setup.php')
                                continue;
                            $organizedFiles[] = $relativePath;
                        } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'example' && !in_array($relativePath, $organizedFiles)) {
                            if (preg_match('/^\.env\.example/', $relativePath))
                                $organizedFiles[] = $relativePath;
                        } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'gitignore' && !in_array($relativePath, $organizedFiles)) {
                            $organizedFiles[] = $relativePath;
                        } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'htaccess' && !in_array($relativePath, $organizedFiles)) {
                            $organizedFiles[] = $relativePath;
                        } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'md' && !in_array($relativePath, $organizedFiles)) {
                            $organizedFiles[] = $relativePath;
                        } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'LICENSE' && !in_array($relativePath, $organizedFiles)) {
                            $organizedFiles[] = $relativePath;
                        } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'js' && !in_array($relativePath, $organizedFiles)) {
                            $organizedFiles[] = $relativePath;
                        } elseif (pathinfo($relativePath, PATHINFO_EXTENSION) == 'json' && !in_array($relativePath, $organizedFiles)) {
                            $organizedFiles[] = $relativePath;
                        }
                    }
                }

                // Scan the specified directories
                scanDirectories($directoriesToScan, $baseDir, $organizedFiles);

                // Display the results
                $sortedArray = customSort($organizedFiles);

                $output = var_export($sortedArray, true);

                $json = "{\n";
                while ($path = array_shift($sortedArray)) {
                    $json .= match ($path) {
                        '.env.example' => (function () use ($path, $sortedArray) {
                                return '".env" : ' . json_encode(file_get_contents($path)) . (end($sortedArray) != $path ? ',' : '') . "\n";
                            })(),
                        default => '"' . $path . '" : ' . json_encode(file_get_contents($path)) . (end($sortedArray) != $path && !empty($sortedArray) ? ',' : '') . "\n",
                    };
                }
                $json .= "}\n";

                /*

                      $json = "{\n";  // Display the sorted array

                      //dd($path);

                      while ($path = array_shift($sortedArray)) {
                        $json .= match ($path) {
                          '.env.example' => '".env" : ' . json_encode(file_get_contents($path)) . (end($sortedArray) != $path ? ',' : '') . "\n",
                          default => '"' . $path . '" : ' . json_encode(file_get_contents($path)) . (end($sortedArray) != $path && !empty($sortedArray) ? ',' : '') . "\n",
                        };
                      }
                      $json .= "}\n";
                */

                //die($path);
                // Read and sanitize the `.env` file contents
                $envContents = $json;
                $sanitizedContents = preg_replace(
                    '/' . $_ENV['GITHUB']['OAUTH_TOKEN'] . '/m',
                    '***REDACTED***',
                    $envContents
                );

                file_put_contents(APP_BASE['data'] . 'source_code.json', $sanitizedContents, LOCK_EX);

                signalHandler(SIGTERM);
                // Update the file's modification time if necessary (or other actions)
                // touch($file); // Optional, to update the modification time to current time
            } else {
                $output = "File was already modified today.\n";
            }

            /**/
        } elseif (preg_match('/^cmd:\s*app(\s*connected)?(?=\r?\n$)?/si', $input)) {
            $output = var_export(APP_IS_ONLINE, true);
        } elseif (preg_match('/^cmd:\s*composer\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
            //$output = shell_exec($matches[1]);

            require_once APP_PATH . 'api' . DIRECTORY_SEPARATOR . 'composer.php';

            $sudo_prefix = '';
            if (defined('APP_SUDO') && trim(APP_SUDO) !== '') {
                $sudo_prefix = APP_SUDO . ' -u www-data ';
            }

            $proc = proc_open(
                'env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . $sudo_prefix . "composer $matches[1]",
                [
                    ["pipe", "r"],
                    ["pipe", "w"],
                    ["pipe", "w"]
                ],
                $pipes
            );

            [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
            $output = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");

            $output .= "\n" . $sudo_prefix . "composer $matches[1]";
            /**/
        } elseif (preg_match('/^cmd:\s*(composer(\s+.*|))(?=\r?\n$)?/si', $input, $matches)) { // ?(?=\r?\n$) // ?
            $cmd = $matches[1]; // $input
            $output = var_export($matches, true);
            //$output = 'test ' . trim(shell_exec($cmd));
            //$output .= " $input";
        } elseif (preg_match('/^cmd:\s*git\s*(.*)(?=\r?\n$)?/si', $input, $gitMatches)) {
            //$output = shell_exec($matches[1]);

            require_once APP_BASE['config'] . 'git.php';

            $parsedUrl = parse_url($_ENV['GIT']['ORIGIN_URL']);

            //$output[] = $command = $_POST['cmd'] . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" https://' . $_ENV['GITHUB']['OAUTH_TOKEN'] . '@' . $parsedUrl['host'] . $parsedUrl['path'] . '.git';
            
            $sudo_prefix = '';
            if (defined('APP_SUDO') && trim(APP_SUDO) !== '') {
                $sudo_prefix = APP_SUDO . ' -u www-data ';
            }

            $output = 'www-data@localhost:' . getcwd() . '# ' . $command = $sudo_prefix . (defined('GIT_EXEC') ? GIT_EXEC : 'git') . ' ' . trim($gitMatches[1]) . ' https://' . $_ENV['GITHUB']['OAUTH_TOKEN'] . '@' . $parsedUrl['host'] . $parsedUrl['path'] . '.git';

            // (is_dir($path = APP_PATH . APP_ROOT . '.git') || APP_PATH . APP_ROOT != APP_PATH ? ' --git-dir="' . $path . '" --work-tree="' . dirname($path) . '"': '' ) 

            /*//$err =  */

            $proc = proc_open(
                $command,
                [
                    ["pipe", "r"],
                    ["pipe", "w"],
                    ["pipe", "w"]
                ],
                $pipes
            );

            if (is_resource($proc)) {
                // Read stdout and stderr
                $stdout = stream_get_contents($pipes[1]);
                fclose($pipes[1]);


                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);

                // Close the process and get the exit code
                $exitCode = proc_close($proc);

                // Construct the output string
                //$output = '';

                if (!empty($stdout)) {
                    $output .= "\n$stdout";
                }

                if (!empty($stderr)) {
                    $output .= " Error: $stderr";
                }

                if ($exitCode !== 0) {
                    $output .= " Exit Code: $exitCode";
                }

                // Debugging info for the final executed command
                //$output .= "\n" . /*(stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ') . */ "git $gitMatches[1]";
                /*
                        socket_write($clientSocket, $stdout);
                        return $output;
                */
            } else {
                $output .= "Failed to open process.";
            }


            //$output = var_export(shell_exec('ls'), true); 

            //[$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
            //$output = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");
            //$output .= "\n" . /*(stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ')*/ . "git $gitMatches[1]";
            /**/
        } elseif (preg_match('/^cmd:\s*server\s*(variables|)(?=\r?\n$)?/si', $input)) {
            $output = var_export($_SERVER, true);
        } elseif (preg_match('/^cmd:\s*(add\s*notification)(?=\r?\n$)?/si', $input)) {
            $output = 'Added notification...';
            $manager->addNotification(new Notification('notifyUser2', true, 20));
        } elseif (preg_match('/^cmd:\s*(include|require)\s*(.*)(?=\r?\n$)?/si', $input, $matches)) {
            //var_dump(getcwd() . "/$matches[2]");
            require_once trim($matches[2]);
            $output = "Including/Requiring... $matches[2]";
            var_dump(get_required_files());
        } elseif (preg_match('/^cmd:\s*(notifications)(?=\r?\n$)?/si', $input)) {
            $output = 'Notification(s)...';
            $output .= $manager->getNotifications();
        } elseif (preg_match('/^cmd:\s*(date(\?|)|what\s+is\s+the\s+date(\?|))(?=\r?\n$)?/si', $input)) {
            $output = 'The date is: ' . date('Y-m-d');
        } elseif (preg_match('/^cmd:\s*(get\s+defined\s+constants)(?=\r?\n$)?/si', $input)) {
            $output = var_export(get_defined_constants(true)['user'], true);
        } elseif (preg_match('/^cmd:\s*(get\s+(required|included)\s+files)(?=\r?\n$)?/si', $input)) {
            $output = var_export(get_required_files(), true);
        } elseif (preg_match('/cmd:\s+(.*)(?=\r?\n$)?/s', $input, $matches)) { // cmd: composer update
            $cmd = $matches[1];
            $output = 'input: ' . json_encode($input) . " cmd: $cmd";
            //$output = trim(shell_exec(/*$cmd*/ 'echo $PWD'));
            //$output .= 'input: ' . json_encode($input) . " cmd: $cmd";
        } else {
            // Process the request and send a response
            $output = "Hello, client! Your input was: $input";

            global $manager;

            // Append notification output to the response
            $output .= "\n{$manager->getNotificationsOutput()}";
        }
        /*
            if ($cmd = $matches[1] ?? NULL) {
              $proc=proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ')  $cmd,
              [
                ["pipe", "r"],
                ["pipe", "w"],
                ["pipe", "w"]
              ],
              $pipes);
              [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
              $output = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (!isset($exitCode) && $exitCode == 0 ? NULL : " Exit Code: $exitCode");

              //$output = shell_exec($cmd);
            }
        */
        //$_POST['cmd'] = $cmd;

        //ob_start(); // Start output buffering    ob_end_flush();

        error_log("Client [Output]: " . (strlen($output) > 50 ? substr($output, 0, 20) . '...' : $output));
        echo "Client [Output]: $output"; /*(strlen($output) > 50 ? substr($output, 0, 20) . "...\n" : $output)*/

        return $output;
    }
}