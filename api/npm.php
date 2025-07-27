<?php

require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'constants.composer.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
    if (isset($_POST['cmd']) && $_POST['cmd'] != '')
        if (preg_match('/^npm\s+(:?(.*))/i', $_POST['cmd'], $match)) {
            $output[] = $command = (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . NPM_EXEC . ' ' . $match[1];
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
            $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");
            //$output[] = $_POST['cmd'];

            //exec($_POST['cmd'], $output);
        }
