<?php
namespace CodeInSync\Application\Console\Contracts;

interface CommandInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function run(array $args = []): int;
    
    public function match(string $input): array|false;
    public function execute(string $input, array $matches = []): string;
}