<?php
declare(strict_types=1);

namespace Bioage_App\Infrastructure\Runtime;

final class BootstrapTracker
{
    private static array $stages = [];

    public static function initStages(array $stages = []): void
    {
        self::$stages = $stages ?: self::defaultStages();
    }

    public static function add(
        int $stage,
        string $name,
        string $status = 'ok',
        string $summary = '',
        array $debug = [],
        array $documentation = []
    ): void {
        self::ensureStage($stage);

        $currentStatus = self::$stages[$stage]['status'] ?? 'pending';

        if ($status === 'error') {
            self::$stages[$stage]['status'] = 'error';
        } elseif ($status === 'warning') {
            self::$stages[$stage]['status'] =
                ($currentStatus === 'error')
                ? 'error'
                : 'warning';
        } elseif ($status === 'ok') {
            self::$stages[$stage]['status'] = 'ok';
        } else {
            self::$stages[$stage]['status'] = $currentStatus;
        }
/*
        self::$stages[$stage]['status'] = match ($status) {
            'ok' => 'ok',
            'warning' => self::$stages[$stage]['status'] === 'error' ? 'error' : 'warning',
            'error' => 'error',
            default => self::$stages[$stage]['status'] ?? 'pending',
        };
*/
        self::$stages[$stage]['items'][] = [
            'stage' => $stage,
            'stage_name' => self::$stages[$stage]['name'] ?? 'Stage ' . $stage,
            'name' => $name,
            'status' => $status,
            'summary' => $summary,
            'debug' => $debug,
            'documentation' => $documentation,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public static function success(
        int $stage,
        string $name,
        string $summary = '',
        array $debug = [],
        array $documentation = []
    ): void {
        self::add($stage, $name, 'ok', $summary, $debug, $documentation);
    }

    public static function warning(
        int $stage,
        string $name,
        string $summary = '',
        array $debug = [],
        array $documentation = []
    ): void {
        self::add($stage, $name, 'warning', $summary, $debug, $documentation);
    }

    public static function error(
        int $stage,
        string $name,
        string $summary = '',
        array $debug = [],
        array $documentation = []
    ): void {
        self::add($stage, $name, 'error', $summary, $debug, $documentation);
    }

    public static function requireOnce(
        int $stage,
        string $name,
        string $file,
        array $documentation = []
    ): bool {
        $debug = [
            'file' => $file,
            'exists' => is_file($file),
            'readable' => is_readable($file),
        ];

        if (!is_file($file)) {
            self::error(
                $stage,
                $name,
                'Required file does not exist.',
                $debug,
                $documentation
            );

            return false;
        }

        if (!is_readable($file)) {
            self::error(
                $stage,
                $name,
                'Required file is not readable.',
                $debug,
                $documentation
            );

            return false;
        }

        self::success(
            $stage,
            $name,
            'Required file loaded successfully.',
            $debug,
            $documentation
        );

        require_once $file;

        return true;
    }

    public static function stages(): array
    {
        if (self::$stages === []) {
            self::initStages();
        }

        ksort(self::$stages);

        return self::$stages;
    }

    public static function reset(): void
    {
        self::$stages = self::defaultStages();
    }

    private static function ensureStage(int $stage): void
    {
        if (self::$stages === []) {
            self::initStages();
        }

        if (!isset(self::$stages[$stage])) {
            self::$stages[$stage] = [
                'name' => 'Stage ' . $stage,
                'items' => [],
            ];
        }

        self::$stages[$stage]['items'] ??= [];
    }

    private static function defaultStages(): array
    {
        return [
            1 => [
                'stage' => 1,
                'name' => 'PHP Runtime',
                'status' => 'pending',
                'summary' => 'Checks the PHP version, extensions, ini settings, memory, timezone, and error reporting.',
                'debug' => [],
                'documentation' => [
                    'This stage confirms that PHP is configured correctly before the application continues.',
                    'Runtime checks should happen before database, session, routing, or application services are loaded.',
                ],
                'items' => [],
            ],

            2 => [
                'stage' => 2,
                'name' => 'Request Context',
                'status' => 'pending',
                'summary' => 'Inspects request superglobals such as $_SERVER, $_GET, $_POST, $_FILES, and $_COOKIE.',
                'debug' => [],
                'documentation' => [
                    'Request context is available before session_start().',
                    'This stage helps identify the request method, URI, content type, cookies, and submitted values.',
                ],
                'items' => [],
            ],

            3 => [
                'stage' => 3,
                'name' => 'Session Context',
                'status' => 'pending',
                'summary' => 'Tracks session configuration, session_start(), cookie settings, and session rehydration.',
                'debug' => [],
                'documentation' => [
                    'Sessions depend on the configured session name, cookie path, save path, and incoming session cookie.',
                    'This stage helps diagnose missing cookies, regenerated sessions, and session persistence issues.',
                ],
                'items' => [],
            ],

            4 => [
                'stage' => 4,
                'name' => 'Database',
                'status' => 'pending',
                'summary' => 'Checks PDO, database configuration, connection status, and migration readiness.',
                'debug' => [],
                'documentation' => [
                    'Database checks confirm that the application can connect to the required schemas.',
                    'This stage should verify PDO, PDO MySQL, credentials, and migration state.',
                ],
                'items' => [],
            ],

            5 => [
                'stage' => 5,
                'name' => 'Application Bootstrap',
                'status' => 'pending',
                'summary' => 'Loads constants, autoloaders, environment files, configuration, runtime files, routes, and guards.',
                'debug' => [],
                'documentation' => [
                    'This stage documents which files are required during application startup.',
                    'Files may load earlier than their stage order, but they are still grouped here for documentation.',
                ],
                'items' => [],
            ],

            6 => [
                'stage' => 6,
                'name' => 'Security',
                'status' => 'pending',
                'summary' => 'Checks authentication, authorization, CSRF, HTTPS, headers, passwords, and cookie security.',
                'debug' => [],
                'documentation' => [
                    'Security checks confirm that requests are protected before sensitive routes or actions execute.',
                    'This stage should include session hardening, login state, CSRF validation, and secure cookie settings.',
                ],
                'items' => [],
            ],

            7 => [
                'stage' => 7,
                'name' => 'Application Services',
                'status' => 'pending',
                'summary' => 'Checks supporting services such as mail, logging, cache, file storage, queues, and device services.',
                'debug' => [],
                'documentation' => [
                    'Application services are optional or supporting systems used by the main application.',
                    'This stage helps identify whether external or internal services are available and working.',
                ],
                'items' => [],
            ],

            8 => [
                'stage' => 8,
                'name' => 'Debugging & Diagnostics',
                'status' => 'pending',
                'summary' => 'Collects runtime diagnostics including warnings, errors, headers, memory usage, and fatal shutdown data.',
                'debug' => [],
                'documentation' => [
                    'Diagnostics help explain what happened during bootstrap and request handling.',
                    'This stage should collect debug notices, warnings, headers, memory usage, and execution timing.',
                ],
                'items' => [],
            ],
        ];
    }
}
