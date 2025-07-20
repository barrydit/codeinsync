<?php
namespace App\Socket\Commands;

interface CommandInterface {
    public function match(string $input): array|false;
    public function execute(string $input, array $matches = []): string;
}
