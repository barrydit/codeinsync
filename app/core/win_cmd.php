<?php

$batPath = dirname(__DIR__) . '/tools/server.bat';

if (PHP_OS_FAMILY === 'Windows') {
    $batPath = str_replace('/', '\\', $batPath);

    // Wrap in cmd /c start so it opens a new window
    $cmd = 'start "" cmd /c "' . $batPath . '"';
    pclose(popen("start /B " . $cmd, "r")); // or shell_exec($cmd);

    //shell_exec($cmd);

} else {

    $batWin = rtrim(shell_exec('wslpath -w ' . escapeshellarg($batPath)));

    exec('/mnt/c/Windows/System32/schtasks.exe /Run /TN "CIS-RunBat" 2>&1', $out, $rc);

    dd($out);
    //pclose(popen('/mnt/c/Windows/System32/cmd.exe /C start /B "" "' . $batWin . '"', 'r'));

    $taskName = 'CIS-RunBat';

    echo sprintf('schtasks.exe /create /tn %s /tr %s /sc ONLOGON', escapeshellarg($taskName), escapeshellarg($batWin)) . "\n";
    /*
        // Run the scheduled task (no UI required)
        $exe = '"/mnt/c/Windows/System32/schtasks.exe"';
        $task = 'CIS-RunBat'; // you created this earlier in Windows

        exec("$exe /Run /TN " . escapeshellarg($task) . " 2>&1", $out, $rc);

        if ($rc === 0) {
            echo "Scheduled task started\n";
        } else {
            echo "Failed to start task, rc=$rc\n" . implode("\n", $out) . "\n";
        }
    */

    //dd('test');
    // Create the scheduled task (if not exists)
    // exec(sprintf('schtasks.exe /create /tn %s /tr %s /sc ONLOGON', escapeshellarg($taskName), escapeshellarg($batWin)), $out, $rc);

    //if ($rc === 0) {
    //    echo "Scheduled task created successfully.\n";
    //} else {
    //    echo "Failed to create task, rc=$rc\n";
    //    echo implode("\n", $out) . "\n";
    //}
/*
    // Run the scheduled task (no UI required)
    exec(sprintf('schtasks.exe /Run /TN %s 2>&1', escapeshellarg($taskName)), $out, $rc);

    if ($rc === 0) {
        echo "Scheduled task started.\n";
    } else {
        echo "Failed to start task, rc=$rc\n";
        echo implode("\n", $out) . "\n";
    }
    dd('test');
*/
    /*
    // Opens a *new* Windows CMD window that runs the .bat then closes
    $cmd = 'cmd.exe /C start "" cmd /c "' . $batWin . '"';
    //shell_exec($cmd);

    pclose(popen('cmd.exe /C start /B "" "' . $batWin . '"', 'r'));

    system($cmd);*/


    //shell_exec('/mnt/c/Windows/System32/cmd.exe /c "C:\\www\\tools\\server.bat"');
    //$ps = 'powershell.exe -Command "Start-Process cmd -ArgumentList ' .
    //    "'/c \"{$batWin}\"' -Verb RunAs\"";
    //shell_exec($ps);
}