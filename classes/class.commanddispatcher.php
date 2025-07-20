<?php
namespace App\Socket;

use App\Socket\Commands\CommandInterface;

class CommandDispatcher
{
    /** @var CommandInterface[] */
    protected array $commands = [];

    public function addCommand(CommandInterface $command): void
    {
        $this->commands[] = $command;
    }

    public function dispatch(string $input): string
    {
        foreach ($this->commands as $command) {
            if ($matches = $command->match($input)) {
                return $command->execute($input, $matches);
            }
        }
        return "Unknown command.";
    }
}
