<?php

namespace CodeInSync\Application\Console;

use CodeInSync\Application\Console\Contracts\CommandInterface;

class ComposerCommand implements CommandInterface {
    public function execute(string $input, array $matches = []): string {
        global $running;
        $running = false;
        $output = "[COMPOSER] Received input: $input\n";
        return $output;
    }
    public function match(string $input): array|false {
        return preg_match('/^cmd:\s*composer\b/i', $input, $matches) ? $matches : false;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string {
        return 'Runs Composer commands.';
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return 'composer';
    }

    /**
     * @inheritDoc
     */
    public function run(array $args = []): int {
        // Implement the logic to run Composer commands here.
        return 0;
    }
}