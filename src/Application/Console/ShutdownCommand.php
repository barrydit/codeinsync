<?php

namespace CodeInSync\Application\Console;

use CodeInSync\Application\Console\Contracts\CommandInterface;

class ShutdownCommand implements CommandInterface {
    public function match(string $input): array|false {
        return preg_match('/^cmd:\s*shutdown\b/i', $input, $matches) ? $matches : false;
    }

    public function execute(string $input, array $matches = []): string {
        global $running;
        $running = false;
        return "Server is shutting down (PID: " . getmypid() . ")";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string {
        return 'Shuts down the server.';
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return 'shutdown';
    }

    /**
     * @inheritDoc
     */
    public function run(array $args = []): int {
        // Implement the logic to shut down the server here.
        return 0;
    }
}
