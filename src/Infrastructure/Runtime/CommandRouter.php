<?php
declare(strict_types=1);

namespace CodeInSync\Infrastructure\Runtime;

final class CommandRouter
{
    /** @var list<RuntimeInterface> */
    private array $runtimes;

    /**
     * @param list<RuntimeInterface> $runtimes
     */
    public function __construct(array $runtimes)
    {
        $this->runtimes = $runtimes;
    }

    public function dispatch(string $cmd, array $ctx = []): array
    {
        $cmd = trim($cmd);

        if ($cmd === '') {
            return [
                'ok' => false,
                'runtime' => 'router',
                'command' => '',
                'prompt' => '$',
                'exit' => 400,
                'stdout' => '',
                'stderr' => 'Missing cmd',
                'meta' => ['code' => 'MISSING_CMD'],
            ];
        }

        foreach ($this->runtimes as $rt) {
            if ($rt->supports($cmd)) {
                $res = $rt->run($cmd, $ctx);

                // Ensure minimal normalized keys exist (transition safety)
                if (\is_array($res)) {
                    $res['runtime'] ??= $rt->name();
                    $res['command'] ??= $cmd;
                    $res['prompt'] ??= '$ ' . $cmd;
                    $res['exit'] ??= ((empty($res['ok'])) ? 1 : 0);
                    $res['stdout'] ??= '';
                    $res['stderr'] ??= '';
                    $res['meta'] ??= null;
                    $res['ok'] ??= ((int) $res['exit'] === 0);
                }

                return $res;
            }
        }

        return [
            'ok' => false,
            'runtime' => 'router',
            'command' => $cmd,
            'prompt' => '$ ' . $cmd,
            'exit' => 404,
            'stdout' => '',
            'stderr' => 'Unknown command/runtime',
            'meta' => ['code' => 'UNKNOWN_RUNTIME'],
        ];
    }
}
