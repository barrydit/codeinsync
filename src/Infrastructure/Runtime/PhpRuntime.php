<?php
declare(strict_types=1);

namespace CodeInSync\Infrastructure\Runtime;

final class PhpRuntime
{
    public const DEFAULT_MAX_INLINE = 10_000;   // 10 KB: safe for -r
    public const DEFAULT_TIMEOUT = 10;

    public static function execPath(): string
    {
        if (defined('PHP_EXEC') && is_string(PHP_EXEC) && PHP_EXEC !== '') {
            return PHP_EXEC;
        }
        foreach (['/usr/bin/php', '/bin/php', '/usr/local/bin/php'] as $p) {
            if (is_file($p) && is_executable($p))
                return $p;
        }
        return 'php';
    }

    public static function normalize(string $code): string
    {
        $code = trim($code);

        // strip escaped wrapping quotes: \"...\"
        if (preg_match('/^\\\\(["\'])(.*)\\\\\\1$/s', $code, $m)) {
            $code = $m[2];
        }

        // strip normal wrapping quotes: "..." or '...'
        if (
            (str_starts_with($code, '"') && str_ends_with($code, '"')) ||
            (str_starts_with($code, "'") && str_ends_with($code, "'"))
        ) {
            $code = substr($code, 1, -1);
        }

        // unescape quotes
        $code = str_replace(['\\"', "\\'"], ['"', "'"], $code);

        // ensure trailing ;
        $code = rtrim($code);
        if ($code !== '' && substr($code, -1) !== ';')
            $code .= ';';

        return $code;
    }

    public static function run(string $code, array $opts = []): array
    {
        $php = self::execPath();
        $timeout = (int) ($opts['timeout'] ?? self::DEFAULT_TIMEOUT);
        $maxInline = (int) ($opts['max_inline'] ?? self::DEFAULT_MAX_INLINE);

        $code = self::normalize($code);

        $iniArgs = ['-d', 'display_errors=1', '-d', 'html_errors=0'];

        // Decide inline vs file
        if (strlen($code) <= $maxInline) {
            $argv = array_merge([$php], $iniArgs, ['-r', $code]);
            return ProcessRunner::run($argv, ['cwd' => getcwd(), 'timeout' => $timeout]);
        }

        // File execution fallback
        $tmp = tempnam(sys_get_temp_dir(), 'cis_php_');
        if ($tmp === false) {
            return ['ok' => false, 'exit' => 255, 'out' => '', 'err' => 'tempnam() failed', 'cmd' => ''];
        }
        $file = $tmp . '.php';

        $template = (string) ($opts['template'] ?? "<?php\n\n%s\n");
        file_put_contents($file, sprintf($template, $code));

        $argv = array_merge([$php], $iniArgs, [$file]);
        $res = ProcessRunner::run($argv, ['cwd' => getcwd(), 'timeout' => $timeout]);

        if (empty($opts['keep_file'])) {
            @unlink($file);
        }

        return $res;
    }
}