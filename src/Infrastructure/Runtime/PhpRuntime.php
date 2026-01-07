<?php
declare(strict_types=1);

namespace CodeInSync\Infrastructure\Runtime;

final class PhpRuntime implements RuntimeInterface
{
    public function name(): string
    {
        return 'php';
    }

    public function supports(string $cmd): bool
    {
        $cmd = ltrim($cmd);
        return $cmd !== '' && \preg_match('/^php(?:\s|$)/i', $cmd) === 1;
    }

    /**
     * @param array{cwd?:string, env?:array<string,string>, timeout?:int, max_inline?:int} $ctx
     * @return array<string,mixed>
     */
    public function run(string $cmd, array $ctx = []): array
    {
        $parsed = self::parsePhpCmd($cmd);

        if ($parsed === null) {
            return $this->err(
                cmd: $cmd,
                prompt: '$ ' . $cmd,
                exit: 400,
                message: 'Unsupported command format',
                code: 'PHP_UNSUPPORTED_FORMAT',
                meta: ['expected' => ['php <code>', 'php -r "<code>"']]
            );
        }

        $phpExec = self::phpExec();
        $argv = [
            $phpExec,
            '-d',
            'display_errors=1',
            '-d',
            'html_errors=0',
            '-r',
            $parsed['code'],
        ];

        $timeout = (int) ($ctx['timeout'] ?? 10);
        $cwd = (string) ($ctx['cwd'] ?? getcwd());

        // Run through shared process pipeline
        $res = ProcessRunner::run($argv, [
            'cwd' => $cwd,
            'timeout' => $timeout,
            // 'env' => $ctx['env'] ?? null, // enable later if your ProcessRunner supports it
        ]);

        // ProcessRunner returns: exit/out/err/cmd
        $exit = (int) ($res['exit'] ?? 255);
        $stdout = (string) ($res['out'] ?? '');
        $stderr = (string) ($res['err'] ?? '');

        return [
            'ok' => ($exit === 0),
            'runtime' => 'php',
            'command' => $cmd,
            'prompt' => '$ ' . $cmd,
            'exit' => $exit,
            'stdout' => $stdout,
            'stderr' => $stderr,
            'meta' => [
                'mode' => $parsed['mode'],
                'php_exec' => $phpExec,
                'argv' => $argv,
                'cwd' => $cwd,
            ],
        ];
    }

    // ---------------- internals ----------------

    private function err(string $cmd, string $prompt, int $exit, string $message, string $code, array $meta = []): array
    {
        return [
            'ok' => false,
            'runtime' => 'php',
            'command' => $cmd,
            'prompt' => $prompt,
            'exit' => $exit,
            'stdout' => '',
            'stderr' => $message,
            'meta' => ['code' => $code] + $meta,
        ];
    }

    /**
     * Accept:
     *  - php <code>
     *  - php -r "<code>"
     *
     * @return array{mode:string, code:string}|null
     */
    private static function parsePhpCmd(string $cmd): ?array
    {
        $cmd = trim($cmd);

        // php -r "<code>"
        if (\preg_match('/^php\s+-r\s+(.+)$/is', $cmd, $m)) {
            $code = self::normalizeInlinePhp($m[1]);
            return ['mode' => 'php -r', 'code' => $code];
        }

        // php <code> (not php -r)
        if (\preg_match('/^php\s+(?!-r\b)(.+)$/is', $cmd, $m)) {
            $code = self::normalizeInlinePhp($m[1]);
            return ['mode' => 'php', 'code' => $code];
        }

        return null;
    }

    private static function normalizeInlinePhp(string $s): string
    {
        $s = trim($s);

        // 1) If input arrives as \"...\" or \'...\', strip those first
        if (\preg_match('/^\\\\(["\'])(.*)\\\\\\1$/s', $s, $m)) {
            $s = $m[2];
        }

        // 2) Strip normal wrapping quotes "..." or '...'
        if (
            (str_starts_with($s, '"') && str_ends_with($s, '"')) ||
            (str_starts_with($s, "'") && str_ends_with($s, "'"))
        ) {
            $s = substr($s, 1, -1);
        }

        // 3) Unescape remaining \" and \'
        $s = str_replace(['\\"', "\\'"], ['"', "'"], $s);

        // 4) Ensure trailing semicolon
        $s = rtrim($s);
        if ($s !== '' && substr($s, -1) !== ';') {
            $s .= ';';
        }

        return $s;
    }

    private static function phpExec(): string
    {
        // 1) explicit constant wins
        if (\defined('PHP_EXEC') && \is_string(PHP_EXEC) && PHP_EXEC !== '') {
            return PHP_EXEC;
        }

        // 2) common absolute paths
        foreach (['/usr/bin/php', '/bin/php', '/usr/local/bin/php'] as $p) {
            if (is_file($p) && is_executable($p)) {
                return $p;
            }
        }

        // 3) last resort: rely on PATH
        return 'php';
    }
}