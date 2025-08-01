<?php

/**
 * Logs out the user by forcing the browser to clear Basic Auth credentials.
 */
function logoutUser(): void
{
    // Clear browser cache to prevent auto-login
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Pragma: no-cache');

    // Send headers to clear Basic Auth credentials
    header('WWW-Authenticate: Basic realm="Logged Out"');
    header('HTTP/1.0 401 Unauthorized');

    // Remove stored authentication details
    unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

    // Redirect to the homepage (public page)
    echo '<div style="position: absolute; left: 50%; right: 50%; width: 200px; border: 1px solid #ffb;">You have been logged out.</div>';
    header('Refresh: 2; URL=/'); // Redirect after 2 seconds
    exit;
}

/**
 * Authenticates the user using Basic Auth.
 */
function authenticateUser(): void
{
    // Decode HTTP_AUTHORIZATION if present
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        decodeAuthHeader();
    }

    // Check credentials or prompt if missing
    if (empty($_SERVER['PHP_AUTH_USER'])) {
        sendAuthPrompt();
    }
}

/**
 * Decodes the HTTP Authorization header into user and password.
 */
function decodeAuthHeader(): void
{
    $authHeader = base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6));

    if ($authHeader) {
        [$user, $password] = explode(':', $authHeader);
        $_SERVER['PHP_AUTH_USER'] = $user ?? '';
        $_SERVER['PHP_AUTH_PW'] = $password ?? '';
    }
}

/**
 * Sends a Basic Auth prompt to the client.
 */
function sendAuthPrompt(): void
{
    // Send a 401 Unauthorized header with Basic Auth prompt
    header('WWW-Authenticate: Basic realm="Dashboard"');
    header('HTTP/1.0 401 Unauthorized');

    // Prevent caching to avoid re-using stale credentials
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Pragma: no-cache');

    // Exit with a prompt message
    $auth_require = <<<END
<div style="position: absolute; left: 50%; right: 50%; width: 200px; border: 1px solid #ffb;">Authentication Required</div>
END;

    exit($auth_require);
}

// Handle logout requests
if (filter_input(INPUT_GET, 'logout')) {
    logoutUser();
    exit;
}

// Redirect if no credentials are present
if (empty($_SERVER['PHP_AUTH_USER'])) {
    sendAuthPrompt();
    exit;
}

// Ensure authentication for non-CLI environments
if (PHP_SAPI !== 'cli') {
    authenticateUser();
}