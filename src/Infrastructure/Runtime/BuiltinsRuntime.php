<?php
declare(strict_types=1);

namespace CodeInSync\Infrastructure\Runtime;

final class BuiltinsRuntime implements RuntimeInterface
{
    public function name(): string
    {
        return 'builtins';
    }

    public function supports(string $cmd): bool
    {
        $cmd = ltrim($cmd);
        return $cmd !== '' && preg_match('/^(help|whoami|path|files|defined|test)\b/i', $cmd) === 1;
    }

    public function run(string $cmd, array $ctx = []): array
    {
        $cmd = trim($cmd);
        $prompt = '$ ' . $cmd;

        $out = '';
        $err = '';
        $exit = 0;

        if (preg_match('/^help\b/i', $cmd)) {
            $out = implode(', ', ['help', 'git', 'composer', 'npm', 'php', 'whoami', 'path', 'files', 'defined', 'test']);
        } elseif (preg_match('/^test\b/i', $cmd)) {
            $out = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        } elseif (preg_match('/^path\b/i', $cmd)) {
            $out = rtrim((string) realpath(getcwd()), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        } elseif (preg_match('/^files\b/i', $cmd)) {
            $out = implode("\n", get_required_files());
        } elseif (preg_match('/^defined\b/i', $cmd)) {
            $out = defined('APP_ROOT') ? (string) APP_ROOT : 'APP_ROOT not defined';
        } elseif (preg_match('/^whoami\b/i', $cmd)) {
            // Simple + safe
            $tmp = [];
            @exec('whoami 2>&1', $tmp, $code);
            $out = trim(implode("\n", $tmp));
            $exit = (int) $code;
            if ($exit !== 0 && $out === '')
                $err = 'whoami failed';
        }

        return [
            'ok' => ($exit === 0),
            'runtime' => $this->name(),
            'command' => $cmd,
            'prompt' => $prompt,
            'exit' => $exit,
            'stdout' => $out,
            'stderr' => $err,
            'meta' => null,
        ];
    }
}