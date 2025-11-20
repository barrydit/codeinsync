<?php

namespace CodeInSync\Application\Console;

use CodeInSync\Application\Console\Contracts\CommandInterface;

class GitCommand implements CommandInterface {
    public function execute(string $input, array $matches = []): string {
        global $running;
        $running = false;
        $output = "[GIT] Received input: $input\n";
        return $output;
    }
    public function match(string $input): array|false {
        return preg_match('/^cmd:\s*git\b/i', $input, $matches) ? $matches : false;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string {
        return 'Runs Git commands.';
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return 'git';
    }

    /**
     * @inheritDoc
     */
    public function run(array $args = []): int {
        // Implement the logic to run Git commands here.
        return 0;
    }
}