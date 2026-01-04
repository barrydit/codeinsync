<?php
// api/git.php
use CodeInSync\Infrastructure\Runtime\Shutdown;
use CodeInSync\Infrastructure\Git\GitManager;
//use CodeInSync\Infrastructure\Runtime\ProcessRunner;

if (!class_exists(\CodeInSync\Infrastructure\Git\GitManager::class)) {
    require APP_PATH . 'src/Infrastructure/Git/GitManager.php';
    @class_alias(\CodeInSync\Infrastructure\Git\GitManager::class, 'GitManager');
}

/*
if (!class_exists(\CodeInSync\Infrastructure\Runtime\ProcessRunner::class)) {
    require APP_PATH . 'src/Infrastructure/Runtime/ProcessRunner.php';
    @class_alias(\CodeInSync\Infrastructure\Runtime\ProcessRunner::class, 'ProcessRunner');
}*/

/**
 * Handle a git command string like "git status", "git commit -am 'msg'" etc.
 * Assumes cis_run_process() is defined elsewhere (e.g. in api/console.php).
 */
/*
function handle_git_command(string $cmd): array
{
    $output = [];
    $errors = [];

    if (!preg_match('/^git\s+(.*)/i', $cmd, $match)) {
        return [
            'status' => 'error',
            'message' => 'Invalid or missing git command',
        ];
    }

    $gitCmd = trim($match[1]);
    $sudo = (defined('APP_SUDO') && trim(APP_SUDO) !== '') ? APP_SUDO . ' -u www-data ' : '';
    $gitExec = defined('GIT_EXEC') ? GIT_EXEC : 'git';

    $gitDir = realpath(APP_PATH . APP_ROOT . '.git');
    $workTree = realpath(APP_PATH . APP_ROOT) . DIRECTORY_SEPARATOR;

    $fullCommand = sprintf(
        '%s%s --git-dir="%s" --work-tree="%s" %s',
        $sudo,
        $gitExec,
        $gitDir,
        $workTree,
        $gitCmd
    );

    $shellPrompt = "\$ $fullCommand";

    // ── Special: help ───────────────────────────
    if (preg_match('/^help\b/i', $gitCmd)) {
        $output[] = <<<END
git reset filename   (unstage a specific file)

git branch
  -m   oldBranch newBranch   (Rename branch)
  -d   Safe delete
  -D   Force delete

git commit -am "Message"
git checkout -b newBranch
END;
    }

    // ── Special: update ─────────────────────────
    elseif (preg_match('/^update\b/i', $gitCmd)) {
        $output[] = function_exists('git_origin_sha_update')
            ? git_origin_sha_update()
            : 'git_origin_sha_update() not available.';
    }

    // ── Special: clone ──────────────────────────
    elseif (preg_match('/^clone\b/i', $gitCmd)) {
        if (
            preg_match('/^git\s+clone\s+(http(?:s)?:\/\/([^@\s]+)@github\.com\/[\w.-]+\/[\w.-]+\.git)(?:\s*([\w.-]+))?/i', $cmd, $github_repo)
            || preg_match('/^git\s+clone\s+(http(?:s)?:\/\/github\.com\/[\w.-]+\/[\w.-]+\.git)(?:\s*([\w.-]+))?/i', $cmd, $github_repo)
        ) {
            $parsedUrl = parse_url($_ENV['GIT']['ORIGIN_URL']);
            $token = $_ENV['GITHUB']['OAUTH_TOKEN'] ?? '';

            $remoteUrl = "https://{$token}@{$parsedUrl['host']}{$parsedUrl['path']}.git";

            $fullCommand = "$cmd $remoteUrl";

            if (
                !isset($GLOBALS['runtime']['socket'])
                || !is_resource($GLOBALS['runtime']['socket'])
            ) {
                $res = cis_run_process($fullCommand, $workTree);

                if ($res['stdout'] !== '') {
                    $output[] = $res['stdout'];
                }
                if ($res['stderr'] !== '') {
                    $errors[] = $res['stderr'];
                }
            } else {
                // socket handling unchanged
                $message = "cmd: $fullCommand\n";
                fwrite($GLOBALS['runtime']['socket'], $message);
                $response = '';
                while (!feof($GLOBALS['runtime']['socket'])) {
                    $response .= fgets($GLOBALS['runtime']['socket'], 1024);
                }
                fclose($GLOBALS['runtime']['socket']);
                $output[] = trim($response);
            }
        }
    }

    // ── Default git command ─────────────────────
    else {
        $res = cis_run_process($fullCommand, $workTree);

        if ($res['exitCode'] !== 0 && $res['stdout'] === '') {
            if ($res['stderr'] !== '') {
                $errors["GIT-$cmd"] = $res['stderr'];
                error_log($res['stderr']);
            }
        } else {
            if ($res['stdout'] !== '') {
                $output[] = $res['stdout'];
            }
            if ($res['stderr'] !== '') {
                $output[] = 'stderr: ' . $res['stderr'];
            }
        }

        // $output[] = var_export(get_required_files(), true);
    }

    // Build a printable result string
    $resultText = '';
    if (!empty($output)) {
        $resultText .= implode("\n", array_map('strval', $output));
    }
    if (!empty($errors)) {
        $errText = implode("\n", array_map('strval', is_array($errors) ? $errors : [$errors]));
        $resultText .= ($resultText !== '' ? "\n\n" : '') . $errText;
    }

    return [
        'ok' => empty($errors),
        'api' => 'git',
        'command' => $cmd,
        'prompt' => $shellPrompt,
        'result' => $resultText === '' ? '' : $resultText, // always string
        'output' => $output,
        'errors' => $errors,
    ];
}*/

ob_start();


// === Execution point (direct HTTP access) ===
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['cmd']) && is_string($_POST['cmd']) && $_POST['cmd'] !== '')
    // If INCLUDED by another PHP file (dispatcher)
    if (cis_is_included_file(__FILE__)) {
        $cmd = (string) ($_POST['cmd'] ?? '');
        $manager = GitManager::fromGlobals();
        return $manager->handleCommand($cmd);
    } elseif (cis_is_direct_http_file(__FILE__)) {
        $manager = GitManager::fromGlobals();
        $response = $manager->handleCommand($_POST['cmd']);

        $wantText = isset($_SERVER['HTTP_ACCEPT'])
            && str_contains($_SERVER['HTTP_ACCEPT'], 'text/plain');

        if ($wantText) {
            header('Content-Type: text/plain; charset=utf-8');
            echo $response['prompt'] . "\n" . $response['result'] . "\n";
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode(
            $response,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        if (class_exists(Shutdown::class)) {
            Shutdown::setEnabled(true)->shutdown();
        }

        exit;
    }

// Optional: if directly hit without cmd
if (cis_is_direct_http_file(__FILE__)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Missing cmd']);
    exit;
}
/*
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST')

    if (isset($_POST['cmd']) && $_POST['cmd'] != '')
        if (preg_match('/^git\s*(:?.*)/i', $_POST['cmd'], $match)) {
            //(function() use ($path) {
            //  ob_start();
            //require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'git.php';

            $sudo_prefix = '';
            if (defined('APP_SUDO') && trim(APP_SUDO) !== '') {
                $sudo_prefix = APP_SUDO . ' -u www-data ';
            }

            $command = $sudo_prefix . 'git' . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" commit --allow-empty --dry-run';

            // Append `; echo $?` to capture the exit code in the output
            $shellOutput = shell_exec("$command ; echo $?");

            // Split the output to separate the actual output from the exit code
            $outputLines = explode("\n", trim($shellOutput));
            $exitCode = array_pop($outputLines);  // The last line will be the exit code

            // Reconstruct the output without the exit code
            $commandOutput = implode("\n", $outputLines);

            // Display the result
            //echo "Command Output:\n$commandOutput\n";
            //echo "Exit Code: $exitCode\n";

            if ($exitCode == 128) {
                $proc = proc_open($command, [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

                [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

                if ($exitCode !== 0) {
                    if (empty($stdout)) {
                        if (!empty($stderr)) {
                            $errors["GIT-" . $_POST['cmd']] = $stderr;
                            error_log($stderr);
                        }
                    } else {
                        $errors["GIT-" . $_POST['cmd']] = $stdout;
                    }
                }
                $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $command")); // $exitCode
            }


            //  ob_end_clean();
            //  return '';
            //})();

            if (preg_match('/^git\s+(help)(:?\s+)?/i', $_POST['cmd'])) {
                $output[] = <<<END
  git reset filename   (unstage a specific file)

  git branch
  -m   oldBranch newBranch   (Renaming a git branch)
  -d   Safe deletion
  -D   Forceful deletion

  git commit -am "Default message"

  git checkout -b branchName
END;

                $sudo_prefix = '';
                if (defined('APP_SUDO') && trim(APP_SUDO) !== '') {
                    $sudo_prefix = APP_SUDO . ' -u www-data ';
                }

                $output[] = $command = $sudo_prefix . (defined('GIT_EXEC') ? GIT_EXEC : 'git') . (is_dir($path = APP_PATH . APP_ROOT . '.git') || APP_PATH . APP_ROOT != APP_PATH ? '' : '') . ' ' . $match[1];

                $proc = proc_open(
                    $command,
                    [
                        ["pipe", "r"],
                        ["pipe", "w"],
                        ["pipe", "w"]
                    ],
                    $pipes
                );

                [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
                preg_match('/\/(.*)\//', DOMAIN_EXPR, $matches);
                $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : (preg_match("/^To\\s$matches[1]/", $stderr) ? $stderr : "Error: $stderr")) . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");

            } else if (preg_match('/^git\s+(update)(:?\s+)?/i', $_POST['cmd'])) {
                $output[] = git_origin_sha_update();
            } else if (preg_match('/^git\s+(clone)(:?\s+)?/i', $_POST['cmd'])) {

                //$output[] = dd($_POST['cmd']);
                $output[] = 'This works ... ';

                if (preg_match('/^git\s+clone\s+(http(?:s)?:\/\/([^@\s]+)@github\.com\/[\w.-]+\/[\w.-]+\.git)(?:\s*([\w.-]+))?/', $_POST['cmd'], $github_repo)) { // matches with token

                    // (?:(?=(.*?[^@\s]+))[^@\s]+@)?

                } else if (preg_match('/^git\s+clone\s+(http(?:s)?:\/\/github\.com\/[\w.-]+\/[\w.-]+\.git)(?:\s*([\w.-]+))?/', $_POST['cmd'], $github_repo)) { // matches without token

                    if (realpath($github_repo[3]))
                        $output[] = realpath($github_repo[3]);

                    //$output[] = dd($github_repo);
                    if (!is_dir('.git'))
                        exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'git init', $output);

                    exec('git branch -m master main', $output);

                    //exec('git remote add origin ' . $github_repo[2], $output);
                    //...git remote set-url origin http://...@github.com/barrydit/

                    exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'git config core.sparseCheckout true', $output);

                    //touch('.git/info/sparse-checkout');

                    file_put_contents('.git/info/sparse-checkout', '*'); /// exec('echo "*" >> .git/info/sparse-checkout', $output);

                    exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'git pull origin main', $output);

                    //exec(APP_SUDO . ' git init', $output);
                    //$output[] = dd($output);
                    $output[] = 'This works ... ';

                }

                $parsedUrl = parse_url($_ENV['GIT']['ORIGIN_URL']);

                $output[] = $command = $_POST['cmd'] . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" https://' . $_ENV['GITHUB']['OAUTH_TOKEN'] . '@' . $parsedUrl['host'] . $parsedUrl['path'] . '.git';


                if (isset($github_repo) && !empty($github_repo)) {

                    if (!isset($GLOBALS['runtime']['socket']) || !is_resource($GLOBALS['runtime']['socket']) || empty($GLOBALS['runtime']['socket'])) {
                        $sudo_prefix = '';
                        if (defined('APP_SUDO') && trim(APP_SUDO) !== '') {
                            $sudo_prefix = APP_SUDO . ' -u www-data ';
                        }
                        $errors['server-1'] = 'no socket was detected.';
                        $proc = proc_open($sudo_prefix . $command, [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

                        [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

                        if ($exitCode !== 0) {
                            if (empty($stdout)) {
                                if (!empty($stderr)) {
                                    $errors["GIT-" . $_POST['cmd']] = $stderr;
                                    error_log($stderr);
                                }
                            } else {
                                $errors["GIT-" . $_POST['cmd']] = $stdout;
                            }
                        }

                        $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode"));

                    } else {
                        // Connect to the server
                        $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";

                        // Send a message to the server
                        $errors['server-2'] = 'Client request: ' . $message = "cmd: $command\n";
                        // Known socket  Error / Bug is mis-handled and An established connection was aborted by the software in your host machine 

                        fwrite($GLOBALS['runtime']['socket'], $message);

                        //$output[] = trim($message) . ': ';
                        // Read response from the server
                        while (!feof($GLOBALS['runtime']['socket'])) {
                            $response = fgets($GLOBALS['runtime']['socket'], 1024);
                            $errors['server-3'] = "Server responce: $response\n";
                            if (isset($output[end($output)]))
                                $output[end($output)] .= $response = trim((string) $response);
                            //if (!empty($response)) break;
                        }

                        // Close and reopen socket
                        fclose($GLOBALS['runtime']['socket']);

                    }

                }

                // exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO)  . 'git --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" remote add origin ' . $github_repo[2], $output);

            }

            $sudo_prefix = '';
            if (defined('APP_SUDO') && trim(APP_SUDO) !== '') {
                $sudo_prefix = APP_SUDO . ' -u www-data ';
            }

            $command = $sudo_prefix . 'git' . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" ' . $match[1];

            $output[] = "$shell_prompt$command";

            $output[] = shell_exec("$command ; echo $?");

            if (preg_match('/Your branch is up to date with \'origin\/main\'\./', end($output))) { //  nothing to commit, working tree clean/s'
                //echo "Repository is up-to-date.";
            } else {
                echo "Repository has changes.\n";
            }

            if (isset($output) && is_array($output)) {
                switch (count($output) > 0) {
                    case true:
                        echo join("\n" , $output); //  (isset($match[1]) ? $match[1] : 'PHP') . ' >>> ' . ... <<<
                        break;
                    default:
                        echo join("\n", $output);
                        break;
                }

                Shutdown::setEnabled(true)->setShutdownMessage(function () { })->shutdown();
                //dd('test');
                // . "\n"
                //$output[] = 'post: ' . var_dump($_POST);
                //else var_dump(get_class_methods($repo));
            }
        }

        */