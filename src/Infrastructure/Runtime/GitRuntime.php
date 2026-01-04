<?php

declare(strict_types=1);

namespace CodeInSync\Infrastructure\Runtime;

use CodeInSync\Infrastructure\Git\GitManager;

final class GitRuntime implements RuntimeInterface
{
    public function name(): string
    {
        return 'git';
    }

    public function supports(string $cmd): bool
    {
        $cmd = ltrim($cmd);
        return $cmd !== '' && \preg_match('/^git(?:\s|$)/i', $cmd) === 1;
    }

    /**
     * @param array{cwd?:string, env?:array<string,string>, timeout?:int} $ctx
     * @return array<string,mixed>
     */
    public function run(string $cmd, array $ctx = []): array
    {
        // Keep runtime thin; GitManager owns the behavior.
        $manager = GitManager::fromGlobals();

        // Future hook: if you later add something like:
        // $manager->withCwd($ctx['cwd'] ?? null)->withEnv($ctx['env'] ?? null)
        // you can wire it here without changing your router/API.

        $res = $manager->handleCommand($cmd);

        // Optional: enforce runtime name if older code returns 'api' => 'git'
        if (\is_array($res) && empty($res['runtime'])) {
            $res['runtime'] = 'git';
        }

        return $res;
    }
}
