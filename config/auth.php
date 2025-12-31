<?php
// config/auth.php
// ==== Congig ===============================================================

const AUTH_REALM_BASE = 'CodeInSync';   // base realm name
define('AUTH_LOGOUT_LANDING', (string) UrlContext::getBaseHref()); // PUBLIC page (see #2 below)

// NEW: cookie to track whether we've already shown the welcome page
const AUTH_WELCOME_COOKIE = 'AUTH_WELCOME_SHOWN';

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
    global $_GET;
    auth_no_cache();
    header('WWW-Authenticate: Basic realm="' . auth_realm() . '", charset="UTF-8"');
    header('HTTP/1.0 401 Unauthorized');
    if (isset($_GET['authprobe'])) {
        $_GET['logout'] = '1';
        return;
    }
    exit('<div style="margin:2rem auto;max-width:360px;padding:1rem;border:1px solid #ffb;">Authentication Required 1</div>');
}

function auth_send_prompt(): void
{
    auth_no_cache();
    header('WWW-Authenticate: Basic realm="' . auth_realm() . '", charset="UTF-8"');
    header('HTTP/1.0 401 Unauthorized');
    exit('<div style="margin:2rem auto;max-width:360px;padding:1rem;border:1px solid #ffb;">Authentication Required 2</div>');
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
    auth_prompt();
    header("WWW-Authenticate: Basic realm=\"$realm\", charset=\"UTF-8\"");
    header('HTTP/1.0 401 Unauthorized');
    //header("Location: " . AUTH_LOGOUT_LANDING);
    exit('<div style="margin:2rem auto;max-width:360px;padding:1rem;border:1px solid #ffb;">Authentication Required 3 <a href="' . AUTH_LOGOUT_LANDING . '">Continue</a></div>');
}

$logout = array_key_exists('logout', $_GET);     // true for ?logout or ?logout=1/true/…
$authprobe = array_key_exists('authprobe', $_GET);  // true for ?authprobe[...]

// ---- Logout: rotate realm + silent invalidate, then land on public page
if ($logout && $_SERVER['REQUEST_METHOD'] === 'GET') {
    session_start();
    session_destroy();
    auth_no_cache();
    $oldRealm = auth_realm();
    auth_rotate_realm();

    // NEW: clear the welcome cookie so next login shows Hello World again
    if (isset($_COOKIE[AUTH_WELCOME_COOKIE])) {
        setcookie(AUTH_WELCOME_COOKIE, '', time() - 3600, '/', '', false, true);
        unset($_COOKIE[AUTH_WELCOME_COOKIE]);
    }

    //setcookie('REAUTH_REQUIRED', '1', 0, '/', '', false, true);

    unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

    // Make sure nothing is cached
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
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
                    await fetch('<?= htmlspecialchars(UrlContext::getBaseHref()) ?>?authprobe=1&r=<?= rawurlencode($oldRealm) ?>', {
                        headers: { Authorization: 'Basic ' + btoa('x:x') },
                        cache: 'no-store',
                        credentials: 'omit'
                    });
                } catch (e) { /* ignore */ }
                // Redirect to your PUBLIC landing page (must not include auth.php)
                location.replace('<?= AUTH_LOGOUT_LANDING ?>?authprobe=1&r=<?= rawurlencode($oldRealm) ?>');
            })();
        </script>

        <noscript>
            <meta http-equiv="refresh"
                content="1;url=<?= AUTH_LOGOUT_LANDING; ?>?authprobe=1&r=<?= rawurlencode($oldRealm); ?>">
            <p><a href="<?= AUTH_LOGOUT_LANDING; ?>?authprobe=1&r=<?= rawurlencode($oldRealm); ?>">Continue</a></p>
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

    // NEW: Hello World page on first successful login in this browser session
    if (empty($_COOKIE[AUTH_WELCOME_COOKIE])) {
        // Mark that we've shown the welcome page
        setcookie(AUTH_WELCOME_COOKIE, '1', 0, '/', '', false, true);

        header('Content-Type: text/html; charset=UTF-8');
        ?>
        <!doctype html>
        <html lang="en">

        <head>
            <meta charset="utf-8">
            <title>CodeInSync – Hello World</title>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
            <meta http-equiv="Pragma" content="no-cache">
            <meta http-equiv="Expires" content="0">
            <style>
                body {
                    margin: 0;
                    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                    background: #0f172a;
                    color: #e5e7eb;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                }

                .card {
                    background: #020617;
                    border: 1px solid #1e293b;
                    border-radius: 0.75rem;
                    padding: 1.75rem 2rem;
                    max-width: 540px;
                    width: 100%;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.45);
                }

                h1 {
                    margin: 0 0 0.5rem;
                    font-size: 1.5rem;
                }

                .sub {
                    margin: 0 0 1.25rem;
                    color: #9ca3af;
                    font-size: 0.9rem;
                }

                code.console {
                    display: block;
                    background: #020617;
                    border-radius: 0.5rem;
                    padding: 0.75rem 0.85rem;
                    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                    font-size: 0.82rem;
                    border: 1px solid #1e293b;
                    white-space: pre;
                    overflow-x: auto;
                    height: 10rem;
                }

                .actions {
                    margin-top: 1.25rem;
                    display: flex;
                    gap: 0.75rem;
                    flex-wrap: wrap;
                }

                .btn {
                    border-radius: 999px;
                    padding: 0.45rem 0.95rem;
                    font-size: 0.85rem;
                    border: 1px solid #22c55e;
                    color: #22c55e;
                    background: transparent;
                    cursor: pointer;
                }

                .btn-secondary {
                    border-color: #4b5563;
                    color: #9ca3af;
                }

                .btn:active {
                    transform: translateY(1px);
                }
            </style>
        </head>

        <body>
            <div class="card">
                <h1>CodeInSync – Hello world 👋</h1>
                <p class="sub">
                    You are successfully authenticated via HTTP Basic for this realm:<br>
                    <strong><?= htmlspecialchars(auth_realm(), ENT_QUOTES, 'UTF-8') ?></strong>
                </p>

                <code class="console" id="console-log"><?php
                echo <<<EOT
\$ whoami
  $u
\$ echo "Ready for console integration…"
  ✅ Console stub is ready. Next step: wire commands + client-side refresh.
EOT; ?></code>
                <div class="actions">
                    <button class="btn" type="button" id="btn-refresh">Refresh Window</button>
                    <a class="btn btn-secondary" href="?logout=1">Logout</a>
                </div>
            </div>

            <script>
                // Stub for your future console + refresh behavior
                document.getElementById('btn-refresh')?.addEventListener('click', () => {
                    const el = document.getElementById('console-log');
                    if (!el) return;
                    const stamp = new Date().toISOString();
                    el.textContent += "\n$ refresh\n  ↻ Client-side refresh triggered @ " + stamp;
                    window.location.reload();
                });


                function runTaskSequence(taskName, step) {
                    step = step || 0;

                    const params = new URLSearchParams();
                    params.set('task', taskName);
                    params.set('step', step);
                    params.set('format', 'json');

                    const el = document.getElementById('console-log');
                    if (!el) return;

                    return fetch('/?api=tasks', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: params
                    })
                        .then(function (res) {
                            return res.json();
                        })
                        .then(function (data) {
                            if (!data || !data.ok) {
                                //$console.val(
                                //    'Task ' + taskName + ' step ' + step + ' failed.' + "\n" +
                                //    $console.val()
                                //);
                                el.textContent = "\nTask " + taskName + " step " + step + " failed.".el.textContent;
                                return;
                            }

                            const humanStep = data.step + 1; // 1-based
                            const total = data.total_steps;

                            // 1) Show job name/output now
                            const lines = [];
                            lines.push(
                                'Job ' + humanStep + ' / ' + total +
                                ' [' + (data.done ? 'done' : 'ok') + '] (' + data.duration_ms + ' ms)'
                            );

                            if (data.output) {
                                lines.push(String(data.output).replace(/\n+$/, ''));
                            }

                            el.textContent = "\n" + lines.join("\n") + el.textContent;

                            // prepend just the job lines first
                            //$console.val(lines.join("\n") + "\n" + $console.val());

                            // 2) If this was the last step, prepend the completion line separately
                            if (data.done) {
                                //$console.val(
                                //    '=== Task ' + data.task + ' completed. ===' + "\n" +
                                //    $console.val()
                                //);
                                el.textContent = "\n=== Task " + data.task + " completed. ===" + el.textContent;
                                return; // no next step
                            }

                            // 3) If there is another step, start it AFTER this one
                            if (data.next_step != null) {
                                return window.runTaskSequence(taskName, data.next_step);
                            }
                        })
                        .catch(function (err) {
                            //const $console = $('#responseConsole');
                            //$console.val(
                            //    'Error running task ' + taskName + ' step ' + step + ': ' + err + "\n" +
                            //    $console.val()
                            //);
                            el.textContent = "\nError running task " + taskName + " step " + step + ": " + err + el.textContent;
                        });
                };
                document.addEventListener('DOMContentLoaded', () => {
                    const el = document.getElementById('console-log');
                    if (!el) return;
                    el.textContent = "\n" + el.textContent;
                    window.runTaskSequence('download-assets');
                    window.setTimeout(() => {
                        //window.runTaskSequence('startup');

                        //new Promise(resolve => setTimeout(resolve, 1000)).then(() => {
                        window.location.reload();
                        //});
                    }, 2500);

                });

            </script>
        </body>

        </html>
        <?php
        exit;
    }

    // If the welcome cookie is already set:
    // do nothing more here; index.php continues exactly as before.
}