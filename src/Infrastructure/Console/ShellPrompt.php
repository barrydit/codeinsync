<?php
// src/Infrastructure/Console/ShellPrompt.php
declare(strict_types=1);

namespace CodeInSync\Infrastructure\Console;

final class ShellPrompt
{
    /**
     * Build a shell-like prompt string:
     *   user@server:/abs/path/relative/path# 
     */
    public static function build(?string $relativePath = null): string
    {
        $user = self::detectUser();
        $server = self::detectServer();
        $cwd = self::detectCwdOrAppPath();

        $rel = self::normalizeRelativePath(
            $relativePath . /*rtrim((string) (\defined('APP_ROOT') ? APP_ROOT : ''), DIRECTORY_SEPARATOR) .*/ (DIRECTORY_SEPARATOR . ($_GET['path'] ?? null))
        );

        return \sprintf('%s@%s:%s%s# ', $user, $server, $cwd, $rel);
    }

    private static function detectUser(): string
    {
        $isWindows = (stripos(PHP_OS, 'WIN') === 0);

        $user = $isWindows
            ? (string) get_current_user()
            : trim((string) shell_exec('whoami 2>&1'));

        if ($user === '') {
            $user = (string) ($_ENV['APACHE']['USER'] ?? 'www-data');
        }

        return $user;
    }

    private static function detectServer(): string
    {
        // Prefer your env pattern, but fall back safely.
        $server = (string) ($_ENV['APACHE']['SERVER'] ?? '');

        if ($server === '') {
            $server = (string) ($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost');
        }

        return $server;
    }

    private static function detectCwdOrAppPath(): string
    {
        $cwd = realpath(getcwd() . (\defined('APP_ROOT') ? '/' . APP_ROOT : '') . (\defined('APP_ROOT_PATH') ? '/' . APP_ROOT_PATH : '')) . DIRECTORY_SEPARATOR;
        if ($cwd !== false && $cwd !== '') {
            return $cwd;
        }

        // Match your existing fallback behavior
        return rtrim((string) (\defined('APP_PATH') ? APP_PATH : ''), DIRECTORY_SEPARATOR);
    }

    private static function normalizeRelativePath(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }

        $path = trim((string) $path, '/');

        // Match your existing behavior: append as a relative tail with no leading slash.
        return $path === '' ? '' : $path;
    }
}