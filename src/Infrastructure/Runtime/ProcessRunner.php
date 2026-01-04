<?php
// src/Infrastructure/Runtime/ProcessRunner.php
declare(strict_types=1);

namespace CodeInSync\Infrastructure\Runtime;

final class ProcessRunner
{
    /**
     * @param list<string> $argv
     * @param array{cwd?:string, env?:array<string,string>, timeout?:int} $opts
     * @return array{ok:bool, exit:int, cmd:string, out:string, err:string}
     */
    public static function run(array $argv, array $opts = []): array
    {
        $cwd = $opts['cwd'] ?? null;

        $timeout = (int) ($opts['timeout'] ?? 120);

        $desc = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        // ✅ EXECUTION: pass raw argv (NO quotes)
        $cmdForExec = $argv;

        // ✅ DISPLAY ONLY: quote for readability/logging
        $cmdForDisplay = implode(' ', array_map(
            static fn($t) => escapeshellarg((string) $t),
            $argv
        ));

        $pipes = [];
        $env = $opts['env'] ?? null;
        $proc = @proc_open($cmdForExec, $desc, $pipes, $cwd, $env);

        if (!\is_resource($proc)) {
            $err = error_get_last();
            return [
                'exit' => -1,
                'out' => '',
                'err' => $err['message'] ?? 'proc_open failed',
                'cmd' => $cmdForDisplay,
            ];
        }

        fclose($pipes[0]);

        $out = stream_get_contents($pipes[1]) ?: '';
        $err = stream_get_contents($pipes[2]) ?: '';

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exit = proc_close($proc);

        return [
            'ok' => ((int) $exit === 0),
            'exit' => (int) $exit,
            'out' => $out,
            'err' => $err,
            'cmd' => $cmdForDisplay,
        ];
    }
}