<?php
namespace App\Socket\Commands;

class StatusCommand implements CommandInterface {
    public function match(string $input): array|false {
        return preg_match('/^cmd:\s*status\b/i', $input, $matches) ? $matches : false;
    }

    public function execute(string $input, array $matches = []): string {
        return "Server is running (PID: " . getmypid() . ")";
    }
}
