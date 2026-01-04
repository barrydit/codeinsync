<?php
declare(strict_types=1);

namespace CodeInSync\Infrastructure\Runtime;

/**
 * A runtime is a command handler for a tool family (git, composer, npm, php, etc.)
 * It decides if it can handle a command and returns a normalized response array.
 */
interface RuntimeInterface
{
    /**
     * Short unique name, e.g. "git"
     */
    public function name(): string;

    /**
     * Can this runtime handle the raw command string?
     * Commonly checks first token: "git ..."
     */
    public function supports(string $cmd): bool;

    /**
     * Execute the command and return an API-style response array.
     * Keep this compatible with your dispatcher output contract.
     *
     * @return array<string,mixed>
     */
    public function run(string $cmd, array $ctx = []): array;
}
