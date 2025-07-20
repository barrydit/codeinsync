<?php

require_once 'CommandInterface.php';

class GitCommand implements CommandInterface {
    public function execute(string $input, array $matches = []): string {
        global $running;
        $running = false;
        $output .= "[GIT] Received input: $input\n";
        return $output;
    }
    public function match(string $input): array|false {
        return preg_match('/^cmd:\s*git\b/i', $input, $matches) ? $matches : false;
    }
}