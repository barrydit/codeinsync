<?php

require_once __DIR__ . '/../src/Infrastructure/Runtime/BootstrapTracker.php';

use Bioage_App\Infrastructure\Runtime\BootstrapTracker;

$notices = $notices ?? [];
$warnings = $warnings ?? [];

BootstrapTracker::requireOnce(
    5,
    'Application Bootstrap',
    ROOT_PATH . '/bootstrap/bootstrap.php',
    [
        'Bootstrap File' => 'bootstrap/bootstrap.php',
    ]
);

BootstrapTracker::success(
    5,
    'Application Bootstrap',
    'Defined constants after loading bootstrap file.',
    [
        'Defined Constants' => get_defined_constants(true)['user'] ?? [],
    ],
    [
        'After loading the bootstrap file, we check the user-defined constants to ensure that necessary configuration values and application constants are properly defined. This can help identify any missing or misconfigured constants that may affect the application\'s functionality.',
    ]
);

defined('DISPATCH_FILE_PATH') || define('DISPATCH_FILE_PATH', 'app/dispatch.php');

if (!defined('DISPATCH_FILE_PATH') || !is_file(DISPATCH_FILE_PATH)) {
    error_log($warnings[array_key_last($notices)] = 'Dispatcher file not found: ' . DISPATCH_FILE_PATH);
    exit;
}

defined('GUARD_FILE_PATH') || define('GUARD_FILE_PATH', 'app/auth/guard.php');

BootstrapTracker::requireOnce(
    5,
    'Application Bootstrap',
    GUARD_FILE_PATH,
    [
        'Loads the guard file to handle access control and security checks.',
    ]
);

BootstrapTracker::success(
    6,
    'Security',
    'Defined GUARD_FILE_PATH constant for authentication and security checks.',
    [
        'GUARD_FILE_PATH' => GUARD_FILE_PATH,
    ],
    [
        'The guard file is responsible for handling authentication, authorization, and other security-related checks. By defining the GUARD_FILE_PATH constant and ensuring that the guard file exists, we can maintain a centralized location for managing access control and security measures across the application.',
    ]
);

$headers = function_exists('headers_list')
    ? headers_list()
    : [];

$securityHeaders = [
    'Content-Security-Policy' => [
        'value' => null,
        'summary' => 'Content-Security-Policy header',
        'warning' => 'Content-Security-Policy header is not set.',
        'success' => 'Content-Security-Policy header is set.',
        'documentation' => 'A Content-Security-Policy header helps reduce cross-site scripting and code injection risks by defining which content sources the browser may load.',
    ],
    'X-Frame-Options' => [
        'value' => null,
        'summary' => 'X-Frame-Options header',
        'warning' => 'X-Frame-Options header is not set.',
        'success' => 'X-Frame-Options header is set.',
        'documentation' => 'The X-Frame-Options header helps protect against clickjacking by controlling whether the application can be embedded in a frame.',
    ],
    'X-Content-Type-Options' => [
        'value' => null,
        'summary' => 'X-Content-Type-Options header',
        'warning' => 'X-Content-Type-Options header is not set.',
        'success' => 'X-Content-Type-Options header is set.',
        'documentation' => 'The X-Content-Type-Options header should usually be set to nosniff to help prevent MIME type sniffing.',
    ],
    'Strict-Transport-Security' => [
        'value' => null,
        'summary' => 'Strict-Transport-Security header',
        'warning' => 'Strict-Transport-Security header is not set.',
        'success' => 'Strict-Transport-Security header is set.',
        'documentation' => 'The Strict-Transport-Security header helps enforce HTTPS connections for supported browsers.',
    ],
    'Referrer-Policy' => [
        'value' => null,
        'summary' => 'Referrer-Policy header',
        'warning' => 'Referrer-Policy header is not set.',
        'success' => 'Referrer-Policy header is set.',
        'documentation' => 'The Referrer-Policy header controls how much referrer information is sent with requests.',
    ],
];

foreach ($headers as $headerLine) {
    foreach ($securityHeaders as $headerName => &$config) {
        if (stripos($headerLine, $headerName . ':') === 0) {
            $config['value'] = trim(substr($headerLine, strlen($headerName) + 1));
        }
    }
}
unset($config);

foreach ($securityHeaders as $headerName => $config) {
    if ($config['value'] === null || $config['value'] === '') {
        BootstrapTracker::warning(
            6,
            'Security',
            $config['warning'],
            [
                $headerName => 'not set',
            ],
            [
                $config['documentation'],
            ]
        );

        continue;
    }

    BootstrapTracker::success(
        6,
        'Security',
        $config['success'],
        [
            $headerName => $config['value'],
        ],
        [
            $config['documentation'],
        ]
    );
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'] ?? null;

if (!empty($csrfToken)) {
    BootstrapTracker::success(
        6,
        'CSRF Protection',
        'CSRF token generated.',
        [
            'token_name' => 'csrf_token',
            'token_present' => true,
            'token_length' => strlen($csrfToken),
        ],
        [
            'A CSRF token has been generated and stored in the current session.',
        ]
    );
} else {
    BootstrapTracker::warning(
        6,
        'CSRF Protection',
        'CSRF token not generated.',
        [
            'token_name' => 'csrf_token',
            'token_present' => false,
            'token_length' => 0,
        ],
        [
            'A CSRF token should be generated and stored in the current session to help protect against cross-site request forgery attacks.',
        ]
    );
}

BootstrapTracker::success(
    8,
    'Security',
    'HTTP headers checked for security best practices.',
    [
        'headers_sent' => headers_sent(),
        'headers_list' => $headers,
    ],
    [
        'headers_sent() returns whether HTTP headers have already been sent, which can affect the ability to set security headers.',
        'headers_list() returns a list of headers that have been set, allowing us to verify the presence of important security headers and their values.',
    ]
);
/*
                [
                    'id' => 'csrf_protection',
                    'stage' => 'Security',
                    'status' => 'ok',
                    'message' => 'CSRF protection is active.',
                    'doc' => 'The application implements CSRF protection measures to safeguard against cross-site request forgery attacks.',
                ],
*/

$notices[] = 'GUARD_FILE_PATH: ' . (\defined('ROOT_PATH')
    ? str_replace(ROOT_PATH . DIRECTORY_SEPARATOR, '', GUARD_FILE_PATH)
    : GUARD_FILE_PATH);

if (defined('APP_MODE') && in_array(APP_MODE, ['web', 'dispatcher'])) {
    BootstrapTracker::requireOnce(
        5,
        'Application Bootstrap',
        DISPATCH_FILE_PATH,
        [
            'Loads the dispatcher after constants, autoloading, environment configuration, sessions, and routes are prepared.',
        ]
    );

} else {
    error_log($warnings[array_key_last($notices)] = 'APP_MODE: ' . APP_MODE);
    exit('Unsupported application mode');
}
