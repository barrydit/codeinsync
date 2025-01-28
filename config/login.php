<?php


// Handle logout requests
if (filter_input(INPUT_GET, 'logout')) {
    logoutUser();

    $logged_out = <<<END
<div style="position: absolute; left: 50%; right: 50%; width: 200px; border: 1px solid #ffb;">You have been logged out.</div>
END;

    exit($logged_out);
}

// Ensure authentication for non-CLI environments
if (PHP_SAPI !== 'cli') {
    authenticateUser();
}

/**
 * Logs out the user by forcing the browser to clear Basic Auth credentials.
 */
function logoutUser(): void
{  // Remove authorization headers if supported
    if (function_exists('header_remove')) {
        header_remove('HTTP_AUTHORIZATION');
    }
    // Send headers to clear Basic Auth credentials
    header('WWW-Authenticate: Basic realm="Logged Out"');
    header('HTTP/1.0 401 Unauthorized');

    // Prevent caching of authorization details
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Pragma: no-cache');

    // Clear authentication details from the server environment
    unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);


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

    // Prompt for credentials if missing
    if (empty($_SERVER['PHP_AUTH_USER'])) {
        sendAuthPrompt();
    } else {
        // Optional: Display user details (for debugging or logging)
        // echo "<p>Hello, {$_SERVER['PHP_AUTH_USER']}.</p>";
        // echo "<p>You entered '{$_SERVER['PHP_AUTH_PW']}' as your password.</p>";
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
    header('WWW-Authenticate: Basic realm="Dashboard"');
    header('HTTP/1.0 401 Unauthorized');

    $auth_require = <<<END
<div style="position: absolute; left: 50%; right: 50%; width: 200px; border: 1px solid #ffb;">Authentication Required</div>
END;

    exit($auth_require);
}