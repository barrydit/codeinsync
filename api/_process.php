<?php

if (!function_exists('cis_run_process')) {
    function cis_run_process(string $command, ?string $cwd = null, ?array $env = null): array
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = proc_open($command, $descriptorSpec, $pipes, $cwd ?: null, $env ?: null);

        if (!is_resource($proc)) {
            return [
                'command' => $command,
                'stdout' => '',
                'stderr' => 'Failed to open process.',
                'exitCode' => 1,
            ];
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($proc);

        return [
            'command' => $command,
            'stdout' => $stdout === false ? '' : $stdout,
            'stderr' => $stderr === false ? '' : $stderr,
            'exitCode' => $exitCode,
        ];
    }
}
