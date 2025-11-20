<?php

namespace CodeInSync\Application\Console;

use CodeInSync\Application\Console\Contracts\CommandInterface;

final class StatusCommand implements CommandInterface {
    public function match(string $input): array|false {
        return preg_match('/^cmd:\s*status\b/i', $input, $matches) ? $matches : false;
    }

    public function execute(string $input, array $matches = []): string {
        return "Server is running (PID: " . getmypid() . ")";
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string {
        return "Returns the current status of the server.";
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return "status";
    }

    /**
     * @inheritDoc
     */
    public function run(array $args = []): int {
        echo $this->execute('cmd: status') . PHP_EOL;
        return 0;
    }
}
