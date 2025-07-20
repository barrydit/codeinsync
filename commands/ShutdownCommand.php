<?php
namespace App\Socket\Commands;

class ShutdownCommand implements CommandInterface {
    public function match(string $input): array|false {
        return preg_match('/^cmd:\s*shutdown\b/i', $input, $matches) ? $matches : false;
    }

    public function execute(string $input, array $matches = []): string {
        global $running;
        $running = false;
        return "Server is shutting down (PID: " . getmypid() . ")";
    }
}
