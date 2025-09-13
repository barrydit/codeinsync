<?php
// ==== Config ===============================================================
const AUTH_REALM_BASE = 'Dashboard';   // base realm name
const AUTH_LOGOUT_LANDING = '/logout.php'; // PUBLIC page (see #2 below)

// ==== Helpers ==============================================================
function auth_no_cache(): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
}

function auth_realm(): string
{
    return AUTH_REALM_BASE . '-' . ($_COOKIE['BASIC_REALM_NONCE'] ?? 'v1');
}
function auth_rotate_realm(): void
{
    // change realm nonce so browser won't auto-send old creds next visit
    setcookie('BASIC_REALM_NONCE', bin2hex(random_bytes(4)), 0, '/', '', false, true);
}
function auth_prompt(): void
{
    auth_no_cache();
    header('WWW-Authenticate: Basic realm="' . auth_realm() . '", charset="UTF-8"');
    header('HTTP/1.0 401 Unauthorized');
    exit('<div style="margin:2rem auto;max-width:360px;padding:1rem;border:1px solid #ffb;">Authentication Required</div>');
}

function auth_send_prompt(): void
{
    auth_no_cache();
    header('WWW-Authenticate: Basic realm="Dashboard", charset="UTF-8"');
    header('HTTP/1.0 401 Unauthorized');
    exit('<div style="margin:2rem auto;max-width:360px;padding:1rem;border:1px solid #ffb;">Authentication Required</div>');
}

function auth_get_header(): ?string
{
    if (!empty($_SERVER['HTTP_AUTHORIZATION']))
        return $_SERVER['HTTP_AUTHORIZATION'];
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
        return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    return null;
}

function auth_get_creds(): array
{
    $u = $_SERVER['PHP_AUTH_USER'] ?? '';
    $p = $_SERVER['PHP_AUTH_PW'] ?? '';
    if ($u !== '')
        return [$u, $p];
    $h = auth_get_header();
    if ($h && stripos($h, 'Basic ') === 0) {
        $decoded = base64_decode(substr($h, 6), true);
        if ($decoded !== false)
            return array_pad(explode(':', $decoded, 2), 2, '');
    }
    return ['', ''];
}

function auth_verify(string $u, string $p): bool
{
    //require_once CONFIG_PATH . 'constants.env.php';
    $EU = getenv('APP_BASIC_USER') ?: $_ENV['APP_UNAME'];
    $EP = getenv('APP_BASIC_PASS') ?: $_ENV['APP_PWORD'];
    return hash_equals($EU, $u) && hash_equals($EP, $p);
}

function auth_clear_reauth_flag(): void
{
    if (isset($_COOKIE['REAUTH_REQUIRED'])) {
        setcookie('REAUTH_REQUIRED', '', time() - 3600, '/', '', false, true);
        unset($_COOKIE['REAUTH_REQUIRED']);
    }
}

/** Call this at the very top of index.php (after requiring this file) */
function auth_require(): void
{
    // If logout requested a re-auth, force a prompt regardless of Authorization header
    if (!empty($_COOKIE['REAUTH_REQUIRED'])) {
        auth_send_prompt(); // exits with 401
    }

    // Normal Basic-Auth check
    [$u, $p] = auth_get_creds();
    if ($u === '' || !auth_verify($u, $p)) {
        auth_send_prompt(); // exits with 401
    }

    // Success: prevent caching & clear re-auth flag for next time
    auth_no_cache();
    auth_clear_reauth_flag();
}

// ==== Special endpoints (must run BEFORE guard) ============================
// Silent probe: return a 401 for a specified realm (or current one) without UI
if (isset($_GET['authprobe'])) {
    auth_no_cache();
    $realm = isset($_GET['r']) ? (string) $_GET['r'] : auth_realm();
    header('WWW-Authenticate: Basic realm="' . $realm . '", charset="UTF-8"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

$logout = array_key_exists('logout', $_GET);     // true for ?logout or ?logout=1/true/…
$authprobe = array_key_exists('authprobe', $_GET);  // true for ?authprobe[...]

// ---- Logout: rotate realm + silent invalidate, then land on public page
if ($logout && $_SERVER['REQUEST_METHOD'] === 'GET') {
    auth_no_cache();
    $oldRealm = auth_realm();
    auth_rotate_realm();
    unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <title>Logged out</title>
        <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>

    <body>
        <div style="margin:2rem auto;max-width:360px;padding:1rem;border:1px solid #ffb;">
            You have been logged out.
        </div>

        <script>
            (async () => {
                try {
                    await fetch('<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?authprobe=1&r=<?= rawurlencode($oldRealm) ?>', {
                        headers: { Authorization: 'Basic ' + btoa('x:x') },
                        cache: 'no-store',
                        credentials: 'omit'
                    });
                } catch (e) { /* ignore */ }
                // Redirect to your PUBLIC landing page (must not include auth.php)
                location.replace('<?= AUTH_LOGOUT_LANDING ?>');
            })();
        </script>

        <noscript>
            <meta http-equiv="refresh" content="1;url=<?= AUTH_LOGOUT_LANDING ?>">
            <p><a href="<?= AUTH_LOGOUT_LANDING ?>">Continue</a></p>
        </noscript>
    </body>

    </html>
    <?php
    exit;
}

// ---- Guard: run for normal requests only (skip CLI and special endpoints)
if (!in_array(PHP_SAPI, ['cli', 'phpdbg'], true) && !$logout && !$authprobe) {
    [$u, $p] = auth_get_creds();
    if ($u === '' || !auth_verify($u, $p)) {
        auth_prompt();   // sends 401 with your (dynamic) realm and exits
    }
    auth_no_cache();     // avoid back/forward cached “logged-in” views
}