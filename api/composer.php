<?php
declare(strict_types=1);
// api/composer.php (or app/tools/registry/composer.helpers.php)

global $errors;

// Ensure bootstrap has run (so constants like APP_PATH, APP_URL are defined)
if (!defined('APP_BOOTSTRAPPED')) {
    require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';
}

require_once APP_PATH . 'config/functions.composer.php'; // lazy loader lives here
require_once APP_PATH . 'config/constants.composer.php'; // your static composer paths/execs, etc.

$errors = [];
$force = !empty($_GET['refresh']) || (!empty($_POST['refresh']) && $_POST['refresh']); // optional manual refresh

$latest = composer_latest_version($errors, $force);
// Use it however you need:
if ($latest) {
    defined('COMPOSER_LATEST') || define('COMPOSER_LATEST', $latest);
}

// If your dispatcher expects JSON for XHR calls, emit JSON:
if (isset($_GET['json']) || isset($_POST['json'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'latest' => $latest,
        'errors' => $errors,
        // include any other composer API payload you already return:
        // 'style' => ..., 'body' => ..., 'script' => ...
    ]);
    return [
        'latest' => $latest,
        'errors' => $errors,
    ];
}

// Otherwise, return a PHP array so the dispatcher can use it:
//return [
//    'latest' => $latest,
//    'errors' => $errors,
//];

/*

$cmd = composer_build_command($_POST['args'] ?? 'install');

APP_COMPOSER_HOME=/var/lib/codeinsync/composer
APP_COMPOSER_BIN=/usr/local/bin/composer
APP_COMPOSER_PROJECT_ROOT=/srv/www/myapp
APP_COMPOSER_DEFAULT_ARGS=--no-interaction --prefer-dist
COMPOSER_AUTOCREATE_HOME=1
COMPOSER_AUTOCREATE_CONFIG=1 */

/* ============================ Helpers ============================= */
/**
 * Returns a prefix for sudo commands if APP_SUDO is defined and not empty.
 * This is used to run commands with elevated privileges on Unix-like systems.
 */
function sudo_prefix(): string
{
    return (defined('APP_SUDO') && APP_SUDO !== '' && stripos(PHP_OS, 'WIN') !== 0) ? (rtrim(APP_SUDO) . ' ') : '';
}

/** Run a shell command inside a specific working directory. */

function run_in_dir(string $cmd, string $cwd): array
{
    $spec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $proc = proc_open($cmd, $spec, $pipes, $cwd, $_ENV);
    if (!is_resource($proc)) {
        return [1, '', 'Failed to start process'];
    }
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $code = proc_close($proc);
    return [$code, (string) $out, (string) $err];
}

/** Ensure vendor/autoload.php exists by running `composer dump-autoload` if needed. */
function composer_ensure_autoload(string $projectDir, array &$errors = []): bool
{
    $vendor = $projectDir . DIRECTORY_SEPARATOR . 'vendor';
    $autoload = $vendor . DIRECTORY_SEPARATOR . 'autoload.php';
    if (is_dir($vendor) && is_file($autoload)) {
        return true;
    }
    $sudo = defined('APP_SUDO') && APP_SUDO !== '' ? APP_SUDO . ' ' : '';
    [$code, $out, $err] = run_in_dir("{$sudo}composer dump-autoload -n", $projectDir);
    if ($code !== 0) {
        $errors['COMPOSER_DUMP_AUTOLOAD'] = trim($err ?: $out) ?: 'dump-autoload failed';
        return false;
    }
    return is_file($autoload);
}

/**
 * Validate required packages exist; for path-like packages, add a `path` repo if missing.
 * Returns an array of notices/errors (non-fatal unless you want to enforce).
 */
function composer_validate_packages(string $projectDir, array &$errors = [], array &$notices = []): void
{
    $jsonPath = $projectDir . DIRECTORY_SEPARATOR . 'composer.json';
    if (!is_file($jsonPath)) {
        $errors['COMPOSER_JSON'] = "composer.json not found at $jsonPath";
        return;
    }

    $composer = json_decode((string) file_get_contents($jsonPath), true);
    if (!is_array($composer)) {
        $errors['COMPOSER_JSON_PARSE'] = 'composer.json is not valid JSON';
        return;
    }

    $required = $composer['require'] ?? [];
    if (!is_array($required) || !$required) {
        return; // nothing to check
    }

    // Build package-name allow regex once (from env or default constant)
    $expr = $_ENV['COMPOSER']['EXPR_NAME'] ?? (defined('COMPOSER_EXPR_NAME') ? COMPOSER_EXPR_NAME : null);
    $allowRe = null;
    if (is_string($expr) && $expr !== '') {
        // If they give 'vendor/.*', wrap w/ delimiters and flags only once.
        $allowRe = preg_match('~^/.+/[a-z]*$~i', $expr) ? $expr : '~' . $expr . '~i';
    }

    $sudo = defined('APP_SUDO') && APP_SUDO !== '' ? APP_SUDO . ' ' : '';

    foreach ($required as $package => $constraint) {
        if ($package === 'php')
            continue;

        if ($allowRe && preg_match($allowRe, $package)) {
            // Allowed by regex – skip existence check
            continue;
        }

        // Prefer composer CLI over filesystem heuristics
        [$code, $out, $err] = run_in_dir($sudo . 'composer show ' . escapeshellarg($package) . ' --no-ansi', $projectDir);

        if ($code !== 0) {
            // Try to detect a local path package under vendor/vendor/name
            $vendorDir = $projectDir . DIRECTORY_SEPARATOR . 'vendor';
            $pkgDir = $vendorDir . DIRECTORY_SEPARATOR . $package;        // exact case
            $pkgParent = $vendorDir . DIRECTORY_SEPARATOR . dirname($package);

            if (is_dir($pkgDir)) {
                $notices['COMPOSER_LOCAL_PATH'][] = "$package present under vendor/, but not recognized by composer show.";
            } elseif (is_dir($pkgParent)) {
                // Try to find a case-insensitive match and normalize
                $candidates = array_values(array_filter(glob($pkgParent . '/*') ?: [], 'is_dir'));
                $match = null;
                foreach ($candidates as $cand) {
                    if (strcasecmp(basename($cand), basename($package)) === 0) {
                        $match = $cand;
                        break;
                    }
                }
                if ($match && $match !== $pkgDir) {
                    @rename($match, $pkgDir);
                    $notices['COMPOSER_CASEFIX'][] = "Renamed " . basename($match) . " → " . basename($pkgDir);
                }
            }

            // Add/ensure a path repository for the package if a local dir exists
            if (is_dir($pkgDir)) {
                $repositories = $composer['repositories'] ?? [];
                $already = false;
                foreach ($repositories as $repo) {
                    if (($repo['type'] ?? null) === 'path' && realpath($repo['url'] ?? '') === realpath($pkgDir)) {
                        $already = true;
                        break;
                    }
                }
                if (!$already) {
                    $repositories[] = ['type' => 'path', 'url' => "vendor/$package"];
                    $composer['repositories'] = $repositories;
                    // Write back composer.json atomically
                    $tmp = $jsonPath . '.tmp';
                    file_put_contents($tmp, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
                    @rename($tmp, $jsonPath);
                    $notices['COMPOSER_REPO_ADDED'][] = "Added path repo for $package";
                }
            } else {
                $errors['COMPOSER_MISSING'][] = "$package not installed (composer show failed, exit $code).";
            }
        }
    }
}

/** Build a valid regex from env/constant, adding delimiters/flags if missing. */
function normalize_regex(?string $expr): ?string
{
    if (!$expr)
        return null;
    // if already like /.../i
    if (preg_match('~^/.+/[a-z]*$~i', $expr))
        return $expr;
    return '~' . $expr . '~i';
}

/* ============================ POST handler ============================= */

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Project dir (where composer.json lives)
    $projectDir = rtrim(APP_PATH . (defined('APP_ROOT') ? APP_ROOT : ''), '/\\');

    $errors = [];
    $notices = [];

    // 1) Toggle autoload (kept as-is, with safer redirect)
    if (isset($_POST['composer']['autoload'])) {
        $_ENV['COMPOSER']['AUTOLOAD'] = $_POST['composer']['autoload'] === 'on';

        if (class_exists('Shutdown')) {
            Shutdown::setEnabled(false)
                ->setShutdownMessage(fn() => header('Location: ' . (APP_URL ?? '/') . '?' . http_build_query(['app' => 'tools/registry/composer']))) // strtok($_SERVER['REQUEST_URI'] ?? '', '?')
                ->shutdown();
        } else {
            header('Location: ' . (APP_URL ?? '/')); // APP_URL_BASE === Array[]
            exit;
        }
    }

    // Common regexes (if defined)
    $exprName = normalize_regex($_ENV['COMPOSER']['EXPR_NAME'] ?? (defined('COMPOSER_EXPR_NAME') ? COMPOSER_EXPR_NAME : null));
    $exprVer = normalize_regex(defined('COMPOSER_EXPR_VER') ? COMPOSER_EXPR_VER : null);

    /* ---------- create-project ---------- */
    if (!empty($_POST['composer']['create-project']) && !empty($_POST['composer']['package'])) {
        $pkg = (string) $_POST['composer']['package'];
        if ($exprName && !preg_match($exprName, $pkg)) {
            $errors['COMPOSER_PACKAGE'] = "Package '$pkg' does not match expected pattern.";
        } else {
            $targetBase = $projectDir . DIRECTORY_SEPARATOR . 'project';
            if (!is_dir($targetBase) && !@mkdir($targetBase, 0755, true)) {
                $errors['COMPOSER_PROJECT'] = 'project/ could not be created.';
            } else {
                // special-cases (laravel/skeleton) preserved
                $sudo = sudo_prefix();
                $targetSub = 'generic';
                if (preg_match('~^laravel/laravel$~i', $pkg))
                    $targetSub = 'laravel';
                if (preg_match('~^symfony/skeleton$~i', $pkg))
                    $targetSub = 'symfony';

                [$code, $out, $err] = run_in_dir($sudo . 'composer create-project ' . escapeshellarg($pkg) . ' ' . escapeshellarg("project/$targetSub") . ' -n', $projectDir);
                if ($code !== 0) {
                    $errors['COMPOSER_CREATE_PROJECT'] = trim($err ?: $out);
                }
            }
        }
        // Clean POST fields
        unset($_POST['composer']['package'], $_POST['composer']['create-project']);
        if (empty($errors)) {
            header('Location: ' . (APP_URL_BASE ?? (APP_URL ?? '/')) . '?' . http_build_query(APP_QUERY ?? []));
            exit;
        }
    }
    /* ---------- require / update single package ---------- */ elseif (!empty($_POST['composer']['package'])) {
        $pkg = (string) $_POST['composer']['package'];
        if ($exprName && !preg_match($exprName, $pkg)) {
            $errors['COMPOSER_PACKAGE'] = "Package '$pkg' does not match expected pattern.";
        } else {
            // optional: your packagist scraping/caching omitted here
            if (!empty($_POST['composer']['install'])) {
                $sudo = sudo_prefix();

                // prefer "require" (installs new) then "update" on that package
                [$code1, $out1, $err1] = run_in_dir($sudo . 'composer require ' . escapeshellarg($pkg) . ' -n', $projectDir);
                if ($code1 !== 0) {
                    $errors['COMPOSER_REQUIRE'] = trim($err1 ?: $out1);
                }

                [$code2, $out2, $err2] = run_in_dir($sudo . 'composer update ' . escapeshellarg($pkg) . ' -n', $projectDir);
                if ($code2 !== 0) {
                    $errors['COMPOSER_UPDATE'] = trim($err2 ?: $out2);
                }
            }
        }
    }
    /* ---------- write composer.json (+ auth) from form ---------- */ elseif (!empty($_POST['composer']['config'])) {
        $cfg = $_POST['composer']['config'];
        $composer = new stdClass();

        // name
        $vendor = trim((string) ($cfg['name']['vendor'] ?? ''));
        $package = trim((string) ($cfg['name']['package'] ?? ''));
        $composer->name = $vendor && $package ? ($vendor . '/' . $package) : '';
        $composer->description = (string) ($cfg['description'] ?? '');

        // version
        $ver = (string) ($cfg['version'] ?? '');
        if ($ver !== '' && (!$exprVer || preg_match($exprVer, $ver))) {
            $composer->version = $ver;
        }

        $composer->type = (string) ($cfg['type'] ?? '');
        $composer->keywords = array_values(array_filter((array) ($cfg['keywords'] ?? []), 'strlen'));
        $composer->homepage = $vendor && $package ? ("https://github.com/$vendor/$package") : '';
        $composer->readme = 'README.md';
        $composer->time = date('Y-m-d H:i:s');
        $composer->license = (string) ($cfg['license'] ?? '');

        // authors
        $composer->authors = [];
        if (!empty($cfg['authors']) && is_array($cfg['authors'])) {
            foreach ($cfg['authors'] as $author) {
                $name = trim((string) ($author['name'] ?? ''));
                $email = trim((string) ($author['email'] ?? ''));
                $role = trim((string) ($author['role'] ?? 'Developer'));
                if ($name !== '' || $email !== '') {
                    $o = new stdClass();
                    $o->name = $name ?: 'John Doe';
                    $o->email = $email ?: 'jdoe@example.com';
                    $o->role = $role;
                    $composer->authors[] = $o;
                }
            }
        }

        // $composer->{'support'}
        // $composer->{'funding'}

        // repositories (start empty)
        $composer->repositories = [];

        // require / require-dev
        $composer->require = new stdClass();
        if (!empty($cfg['require']) && is_array($cfg['require'])) {
            foreach ($cfg['require'] as $line) {
                if (preg_match('/^(.+?)[:=](.+)$/', (string) $line, $m)) {
                    $composer->require->{$m[1]} = trim($m[2]) ?: '^';
                }
            }
        }

        $composer->{'require-dev'} = new stdClass();
        if (!empty($cfg['require-dev']) && is_array($cfg['require-dev'])) {
            foreach ($cfg['require-dev'] as $line) {
                if (preg_match('/^(.+?)[:=](.+)$/', (string) $line, $m)) {
                    $composer->{'require-dev'}->{$m[1]} = trim($m[2]) ?: '^';
                }
            }
        }

        // autoload + minimum-stability, prefer-stable
        $composer->autoload = new stdClass();
        //$composer->{'autoload'}->{'psr-4'} = new StdClass; 
        //$composer->{'autoload'}->{'psr-4'}->{'HtmlToRtf\\'} = "src/HtmlToRtf";
        //$composer->{'autoload'}->{'psr-4'}->{'ProgressNotes\\'} = "src/HtmlToRtf";

        //$composer->{'autoload-dev'} = $_POST['composer']['autoload-dev'];
        $composer->{'minimum-stability'} = (string) ($cfg['minimum-stability'] ?? '');
        $composer->{'prefer-stable'} = true;
        /*
                $composer->{'config'}->{'preferred-install'} = 'dist';
                $composer->{'config'}->{'sort-packages'} = true;
                $composer->{'config'}->{'optimize-autoloader'} = true;
                $composer->{'config'}->{'classmap-authoritative'} = true;
                $composer->{'config'}->{'apcu-autoloader'} = true;
                $composer->{'config'}->{'apcu-autoloader-prefix'} = 'composer';
                $composer->{'config'}->{'platform'} = new stdClass();
                $composer->{'config'}->{'platform'}->{'php'} = PHP_VERSION;
                $composer->{'config'}->{'platform'}->{'ext-openssl'} = true;
                $composer->{'config'}->{'platform'}->{'ext-curl'} = true;
                $composer->{'config'}->{'platform'}->{'ext-json'} = true;
                $composer->{'config'}->{'platform'}->{'ext-mbstring'} = true;
                $composer->{'config'}->{'platform'}->{'ext-xml'} = true;
                $composer->{'config'}->{'platform'}->{'ext-zip'} = true;
                $composer->{'config'}->{'platform'}->{'ext-pdo'} = true;
                $composer->{'config'}->{'platform'}->{'ext-pdo_mysql'} = true;
                $composer->{'config'}->{'platform'}->{'ext-pdo_sqlite'} = true;
                $composer->{'config'}->{'platform'}->{'ext-pdo_pgsql'} = true;
                $composer->{'config'}->{'platform'}->{'ext-pdo_sqlsrv'} = true;
                $composer->{'config'}->{'platform'}->{'ext-ctype'} = true;
                $composer->{'config'}->{'platform'}->{'ext-iconv'} = true;
                $composer->{'config'}->{'platform'}->{'ext-fileinfo'} = true;
                $composer->{'config'}->{'platform'}->{'ext-tokenizer'} = true;
                $composer->{'config'}->{'platform'}->{'ext-xmlreader'} = true;
                $composer->{'config'}->{'platform'}->{'ext-xmlwriter'} = true;
                $composer->{'config'}->{'platform'}->{'ext-dom'} = true;
                $composer->{'config'}->{'platform'}->{'ext-simplexml'} = true;
                $composer->{'config'}->{'platform'}->{'ext-bcmath'} = true;
                $composer->{'config'}->{'platform'}->{'ext-gd'} = true;
                $composer->{'config'}->{'platform'}->{'ext-curl'} = true;
                $composer->{'config'}->{'platform'}->{'ext-openssl'} = true;
        */
        // Write composer.json (atomic)
        $jsonPath = $projectDir . DIRECTORY_SEPARATOR . 'composer.json';
        $tmp = $jsonPath . '.tmp';
        file_put_contents($tmp, json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
        @rename($tmp, $jsonPath);

        // Update auth (GitHub token)
        if (isset($_POST['auth']['github_oauth'])) {
            $newToken = (string) $_POST['auth']['github_oauth'];
            if (defined('COMPOSER_AUTH') && is_array(COMPOSER_AUTH) && !empty(COMPOSER_AUTH['path'])) {
                $authPath = COMPOSER_AUTH['path'];
                $authObj = json_decode((string) (COMPOSER_AUTH['json'] ?? '{}'), false) ?: new stdClass();
                $authObj->{'github-oauth'} = $authObj->{'github-oauth'} ?? new stdClass();
                $authObj->{'github-oauth'}->{'github.com'} = $newToken;

                $tmpAuth = $authPath . '.tmp';
                file_put_contents($tmpAuth, json_encode($authObj, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
                @rename($tmpAuth, $authPath);
            }
        }
    }

    // Respond (minimal JSON summary)
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => empty($errors),
        'errors' => $errors,
        'notices' => $notices,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

//require_once APP_PATH . 'bootstrap/api_init.php';
//require_once APP_PATH . 'api/handlers/composer_cmd.php';

// use Registry;


//require_once 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.php';
//require_once 'config' . DIRECTORY_SEPARATOR . 'config.php';

//die(var_dump(get_defined_constants(true)['user'], false)); // get_defined_constants(true) or die('get_defined_constants() failed.');

//dd(PHP_EXEC);




use PHPUnit\Event\Code\Throwable;
/*
{
  "autoload": {
      "psr-4": {
          "App\\": "src/"
      } ...
      "classmap": [
            "src/"
      ] ...
      "files": [
            "src/helpers.php",
            "src/constants.php"
      ]
  }
}
*/

$a = $b = 'string';
//echo is_bool($b === 0);

/* PHP Assertion Exception Handling */

if (
    ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
    && isset($_POST['cmd'])
    && is_string($_POST['cmd'])
    && preg_match('/^composer\b(.*)$/is', $_POST['cmd'], $m)
) {


    $projectDir = rtrim(APP_PATH . (defined('APP_ROOT') ? APP_ROOT : ''), '/\\');

    // If you have a socket runtime, use that first (your existing contract)
    if (!empty($GLOBALS['runtime']['socket'])) {
        // send $_POST['cmd'] to your socket here, then echo response and exit
        // (left as-is since you said you’ll reuse elsewhere)
        // echo socket_send_and_receive($_POST['cmd']);
        // exit;
    }

    // Local fallback

    // Extract raw composer args (everything after "composer")
    $rawArgs = trim($m[1] ?? '');

    // Basic hardening: disallow obvious shell chaining/injection. Adjust to taste.
    if ($rawArgs !== '' && preg_match('/[;&|`$\\\()\x00]/', $rawArgs)) {
        http_response_code(400);
        echo "Rejected: unsafe characters in command arguments.";
        exit;
    }

    // Build command
    $sudo = (defined('APP_SUDO') && APP_SUDO !== '' && stripos(PHP_OS, 'WIN') !== 0) ? (rtrim(APP_SUDO) . ' ') : '';
    $bin = COMPOSER_EXEC['bin'] ?? 'composer'; // e.g., '/usr/bin/composer'
    $cmd = trim("{$sudo}{$bin} {$rawArgs}");

    // Prefer proc_open with $cwd instead of --working-dir flag
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $proc = proc_open($cmd, $descriptors, $pipes, $projectDir, $_ENV);
    if (!is_resource($proc)) {
        http_response_code(500);
        echo "Failed to start process.";
        exit;
    }
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exit = proc_close($proc);

    // Format output similar to your original
    $lines = [];
    $lines[] = 'Cmd: ' . $cmd . '  (cwd=' . $projectDir . ')';
    if ($stdout !== '' && $stdout !== null)
        $lines[] = rtrim($stdout);
    if ($stderr !== '' && $stderr !== null)
        $lines[] = 'Error: ' . rtrim($stderr);
    if ($exit !== 0)
        $lines[] = "Exit Code: $exit";

    // If you want single-line format when only one line:
    if (count($lines) === 1) {
        echo $lines[0];
    } else {
        echo implode("\n", $lines);
    }

    // If you rely on Shutdown, keep it; otherwise just exit.
    if (class_exists('Shutdown')) {
        Shutdown::setEnabled(true)->setShutdownMessage(function () { })->shutdown();
    } else {
        exit;
    }
}

/*
if (isset($_POST['composer']['html']) && $_POST['composer']['html'] == 'on') {
    load_feature_constants('composer');
    $app = [
        'name' => 'Composer',
        'slug' => 'composer',
        'icon' => 'fa fa-fw fa-composer',
        'version' => COMPOSER_VERSION,
        'latest' => COMPOSER_LATEST,
        'url' => APP_URL_BASE . '?app=composer',
        'html' => null,
    ];
} else {
    $app = [];
}*/
/*
if (basename(dirname(APP_SELF)) == __DIR__ . DIRECTORY_SEPARATOR . 'public') {
    if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'ui.composer.php')) {
        $app['html'] = require_once $path;
    }
}*/