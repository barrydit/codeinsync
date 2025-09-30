<?php
// /app/devtools/directory.php
// A simple directory listing and navigation tool for development environments.
// Usage: http://localhost/?path=clients/123-domain.com

global $errors;

// ---- helpers --------------------------------------------------------------

/**
 * Small utility class for path normalization / query building.
 */
final class PathUtils
{
  public static function norm(?string $p): string
  {
    $p = $p ?? '';
    $p = str_replace('\\', '/', $p);
    $p = preg_replace('#/+#', '/', $p);       // collapse multiple slashes
    $p = preg_replace('#(^|/)\./#', '$1', $p); // remove "./"
    return trim($p, '/');
  }

  /** Get parent path, with trailing slash (or empty string if no parent) */
  public static function parentPath(?string $p): string
  {
    $p = self::norm($p);
    if ($p === '')
      return '';
    // dirname trick with leading slash so dirname() behaves
    $d = dirname('/' . $p);
    return $d === '/' ? '' : ltrim($d, '/') . '/';
  }

  /** Extract best-guess [client, domain] from APP_ROOT like "projects/clients/000-Doe,John/example.com" */
  public static function clientDomainFromRoot(string $root): array
  {
    $parts = array_values(array_filter(explode('/', self::norm($root)), 'strlen'));
    // Heuristic: find "clients" or "projects" and take next two as client/domain
    $idx = array_search('clients', $parts, true);
    if ($idx === false)
      $idx = array_search('projects', $parts, true);
    $client = $parts[$idx + 1] ?? '';
    $domain = $parts[$idx + 2] ?? '';
    return [$client, $domain];
  }

  public static function stripLeading(string $p, string $prefix): string
  {
    $p = self::norm($p);
    $prefix = self::norm($prefix);
    if ($prefix !== '' && strpos($p . '/', $prefix . '/') === 0) {
      return ltrim(substr($p, strlen($prefix)), '/');
    }
    return $p;
  }

  public static function onlyOneScope(array $GET): array
  {
    if (!empty($GET['project'])) {
      return ['project' => $GET['project']];
    }
    if (!empty($GET['client'])) {
      return ['client' => $GET['client'], 'domain' => '']; // drop domain
    }
    if (!empty($GET['domain'])) {
      return ['domain' => $GET['domain']];
    }
    return [];
  }

  public static function buildChildPath(string $base, string $child): string
  {
    $base = self::norm($base);
    $child = self::norm($child);
    return ($base === '' ? $child : $base . '/' . $child) . '/';
  }
}

/* ---------- URL query builder that honors your scenarios ---------- */
final class QueryUrl
{
  /**
   * Build href by preserving current scope from $_GET:
   * - If project is present => only project (exclusive)
   * - Else keep client (if present) and domain (if present)
   * - Always set path to $nextPath (can be '' to mean context root)
   */
  public static function build(array $GET, string $nextPath): string
  {
    $q = self::scope($GET);

    // If you'd like to retain additional harmless params, whitelist here:
    // foreach (['view','mode'] as $k) if (isset($GET[$k])) $q[$k] = $GET[$k];

    $q['path'] = $nextPath;

    // Optional: avoid redundant path when it equals the scope string
    foreach (['project', 'domain'] as $k) {
      if (!empty($q[$k]) && rtrim($q[$k], '/') === rtrim($nextPath, '/')) {
        // keep explicit path anyway? If not, uncomment next line to drop it.
        // unset($q['path']);
      }
    }

    // Filter out empties and build
    $q = array_filter($q, static fn($v) => $v !== '' && $v !== null);
    return '?' . http_build_query($q);
  }

  /** Scope rules: project is exclusive; client & domain may co-exist. */
  private static function scope(array $GET): array
  {
    $out = [];
    if (!empty($GET['project'])) {
      $out['project'] = $GET['project'];
      return $out; // exclusive
    }
    if (!empty($GET['client']))
      $out['client'] = $GET['client'];
    if (!empty($GET['domain']))
      $out['domain'] = $GET['domain'];
    return $out;
  }
}

function is_top_marker_at(string $absPath, array $names, string $rootDir): bool
{
  $root = rtrim(str_replace('\\', '/', $rootDir), '/');
  $rp = str_replace('\\', '/', @realpath($absPath) ?: $absPath);

  // must be inside the browse root
  $prefix = $root . '/';
  if ($rp !== $root && strncmp($rp, $prefix, strlen($prefix)) !== 0)
    return false;

  // relative path from root must be a single segment
  $rel = ltrim(substr($rp, strlen($root)), '/');
  if ($rel === '' || strpos($rel, '/') !== false)
    return false;

  return in_array($rel, $names, true);
}

function base_val(string $key): string
{
  $v = APP_BASE[$key] ?? '';
  return rtrim($v, '/') . '/';
}

function norm_path(string $p): string
{
  // collapse duplicate slashes; keep leading / if present
  $p = preg_replace('#/+#', '/', $p);
  return $p;
}

function get_str(string $k): ?string
{
  return isset($_GET[$k]) ? trim((string) $_GET[$k]) : null;
}

// defined('APP_BASE') or require_once APP_PATH . 'config/constants.paths.php';
// defined('APP_URL_BASE') or require_once APP_PATH . 'config/constants.url.php';

// require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php';

//require_once APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php';
//require_once APP_PATH . APP_ROOT . 'app' . DIRECTORY_SEPARATOR . 'composer.php';

// !isset($_GET['path']) and $_GET['path'] = '';

//namespace App\Directory;

if (preg_match('/^([\w\-.]+)\.php$/', basename(__FILE__), $matches))
  ${$matches[1]} = $matches[1];

// ---- read inputs ----------------------------------------------------------

// Read raw
$clientRaw = get_str('client');
$domainRaw = get_str('domain');
$projectRaw = get_str('project');
$pathRaw = get_str('path');

// Per-field sanitizers:
// - client: allow letters/digits, dot, underscore, hyphen, space, and COMMA (NO slash)
// - domain: allow domain chars only (no comma, no spaces)
// - project: typical slug: letters/digits, dot, underscore, hyphen
// - path: allow safe path chars + slash (but NOT ".." traversal)
$cleanClient = fn($s) => preg_replace('~[^a-z0-9._,\- ]~i', '', (string) $s);
$cleanDomain = fn($s) => preg_replace('~[^a-z0-9.\-]~i', '', (string) $s);
$cleanProject = fn($s) => preg_replace('~[^a-z0-9._\-]~i', '', (string) $s);
$cleanPath = fn($s) => preg_replace('~[^a-z0-9._\-\/]~i', '', (string) $s);

// Apply
$client = $cleanClient($clientRaw);     // ex: 000-clientname
$domain = $cleanDomain($domainRaw);     // ex: example.com
$project = $cleanProject($projectRaw);    // ex: 123project
$path = $cleanPath($pathRaw);       // ex: sub-directory/ (may be '')

// Optional: hard-block traversal in path
if (strpos($path, '..') !== false)
  $path = '';

// Prefer APP_PATH constant; fallback to $_ENV
$APP_PATH = defined('APP_PATH') ? APP_PATH : ($_ENV['APP_PATH'] ?? '/');
// Normalized copy used ONLY for APP_ROOT stripping
$APP_PATH_N = rtrim($APP_PATH, '/\\') . '/';

// ---- Precompute bases -----------------------------------------------------
$BASE_CLIENTS = base_val('clients');
$BASE_PROJECTS = base_val('projects');

$absDir = null;
$ctxRoot = null;    // NEW: context root (for APP_ROOT)
$context = null;

// $nullish = fn($v) => $v === null || $v === '';

$populated = [
  'client' => ($client !== null && $client !== ''),
  'domain' => ($domain !== null && $domain !== ''),
  'project' => ($project !== null && $project !== ''),
  'path' => ($path !== null && $path !== ''),
];

$hasClient = array_key_exists('client', $_GET);
$hasDomain = array_key_exists('domain', $_GET);
$hasProject = array_key_exists('project', $_GET);
$hasPath = array_key_exists('path', $_GET);

// Consider empty path as not present for the 3a base-cases:
$hasNonEmptyPath = $hasPath && $path !== '';

// 3a) ONLY empty client/project → base

// 3a) ONLY empty base listings (presence-aware)
// no params -> app
if (!$hasClient && !$hasDomain && !$hasProject && !$hasNonEmptyPath) {
  $ctxRoot = $APP_PATH_N;
  $absDir = $APP_PATH;
  $context = 'app';
} elseif ($hasClient && $client === '' && $hasDomain && $domain === '' && !$hasProject && !$hasNonEmptyPath) {
  $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
  $absDir = $ctxRoot;
  $context = 'clients-base';
} elseif ($hasProject && $project === '' && !$hasClient && !$hasDomain && !$hasNonEmptyPath) {
  $ctxRoot = $APP_PATH_N . $BASE_PROJECTS;
  $absDir = $ctxRoot;
  $context = 'projects-base';
} elseif ($hasClient && $client === '' && !$hasProject && !$hasDomain && !$hasNonEmptyPath) {
  $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
  $absDir = $ctxRoot;
  $context = 'clients-base';
} elseif ($hasDomain && $domain === '' && !$hasClient && !$hasProject && !$hasNonEmptyPath) {
  $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
  $absDir = $ctxRoot;
  $context = 'clients-base';
}

// 3b) ONLY path → redirect (run only if still undecided)
if ($absDir === null && $populated['path'] && !$populated['client'] && !$populated['domain'] && !$populated['project']) {
  $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
  $isJson = isset($_GET['json']) || stripos($accept, 'application/json') !== false;

  if (!$isJson) {
    $target = ($_SERVER['SCRIPT_NAME'] ?? '/') . '?' . http_build_query([
      'app' => 'devtools/directory',
      'path' => $path,
    ]);

    $curPath = $_SERVER['SCRIPT_NAME'] ?? '/';
    parse_str($_SERVER['QUERY_STRING'] ?? '', $curQS);
    ksort($curQS);
    $current = $curPath . (empty($curQS) ? '' : '?' . http_build_query($curQS));

    if ($current !== $target && !headers_sent()) {
      header("Location: $target", true, 302);
      exit;
    }
  }
}

/*
if (($populated['path'] ?? false) && count($populated) === 1) {
  $redir = '/?app=' . urlencode('devtools/directory') . '&path=' . urlencode($path);
  if (($_SERVER['REQUEST_URI'] ?? '') !== $redir) {
    header('Location: ' . $redir, true, 302);
    exit;
  }
}*/

// ---- Decide the effective directory (only if not decided yet) ------------
// 3c) Main decision tree (only if still undecided)
if ($absDir === null) {
  // ---- decide the effective directory --------------------------------------
//
// Priority by your rules:
//
// 1) client + domain + optional path:
//    APP_PATH . APP_BASE['clients'] . client . '/' . domain . '/' . path
// 2) domain + path (no client):
//    APP_PATH . APP_BASE['clients'] . domain . '/' . path
// 3) project + path:
//    APP_PATH . APP_BASE['projects'] . project . '/' . path
// 4) only path:
//    APP_PATH . path
//
// Empty fallbacks you specified:
// - client=='' OR domain=='' OR path=='clients/'  => show base clients dir: APP_PATH . APP_BASE['clients']
// - project==''                                    => show base projects dir: APP_PATH . APP_BASE['projects']
//
  if ($hasClient && $client !== '' && $hasDomain && $domain !== '') {
    // client + domain (+ optional path)
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/' . $domain . '/';
    $absDir = $ctxRoot . ($hasPath && $path !== '' ? rtrim($path, '/') . '/' : '');
    $context = 'clients';

  } elseif (
    $hasClient && $client !== ''
    && (!$hasDomain || ($hasDomain && $domain === ''))
    && $hasPath && $path !== ''
  ) {
    // client + path (no domain)
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/';
    $absDir = $ctxRoot . rtrim($path, '/') . '/';
    $context = 'clients';

  } elseif (
    $hasClient && $client !== ''
    && (!$hasDomain || ($hasDomain && $domain === ''))
    && (!$hasPath || ($hasPath && $path === ''))
  ) {
    // client only (no domain, no path)
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $client . '/';
    $absDir = $ctxRoot;
    $context = 'clients';

  } elseif ($hasDomain && $domain !== '') {
    // domain (+ optional path)
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS . $domain . '/';
    $absDir = $ctxRoot . ($hasPath && $path !== '' ? rtrim($path, '/') . '/' : '');
    $context = 'clients';

  } elseif ($hasProject && $project !== '') {
    // project (+ optional path)
    $ctxRoot = $APP_PATH_N . $BASE_PROJECTS . $project . '/';
    $absDir = $ctxRoot . ($hasPath && $path !== '' ? rtrim($path, '/') . '/' : '');
    $context = 'projects';

  } elseif (
    ($hasClient && $client === '') ||
    ($hasDomain && $domain === '') ||
    ($hasPath && $path !== '' && preg_match('~^clients(?:/|$)~', $path))
  ) {
    // clients fallbacks
    $ctxRoot = $APP_PATH_N . $BASE_CLIENTS;
    $absDir = $ctxRoot;
    $context = 'clients-base';

  } elseif ($hasPath && $path !== '') {
    // only path → app
    $ctxRoot = $APP_PATH_N; // app root context
    $absDir = $APP_PATH_N . rtrim($path, '/') . '/';
    $context = 'app';

  } else {
    // default: app root
    $ctxRoot = $APP_PATH_N;
    $absDir = $APP_PATH_N;
    $context = 'app';
  }
}
/*
    // ---- APP_ROOT + normalize + existence ------------------------------------
    $ctxRoot = rtrim($ctxRoot, '/\\') . '/';
    $absDir = rtrim($absDir, '/\\') . '/'; // norm_path($absDir);

    // ---- APP_ROOT from CONTEXT ROOT ONLY (no path)
    $APP_ROOT_REL = preg_replace('#^' . preg_quote($APP_PATH_N, '#') . '#', '', $ctxRoot);
    $APP_ROOT_REL = rtrim($APP_ROOT_REL, '/'); // '' | 'clients/Client/Domain' | 'projects/Proj'

    if (!defined('APP_ROOT')) {
      define('APP_ROOT', $APP_ROOT_REL);
    }

    if (!defined('APP_ROOT_DIR')) {
      define('APP_ROOT_DIR', $ctxRoot);
    }
    // ---- existence check ------------------------------------------------------
    $absDir = rtrim($absDir ?? '', '/\\') . '/';
*/

// helpers
$norm = static fn($s) => trim(str_replace('\\', '/', $s ?? ''), '/');
$trail = static fn($s) => $s === '' ? '' : rtrim($s, '/\\') . '/';

// Labels from APP_BASE (handles "../clients/")
$clientsLabel = basename($norm(APP_BASE['clients'] ?? 'clients/'));   // "clients"
$projectsLabel = basename($norm(APP_BASE['projects'] ?? 'projects/'));  // "projects"

// Sanitized subpath from URL
$PATH_SUB = $norm($_GET['path'] ?? '');

// De-dup leading base when context is already that base
if ($context === 'clients-base') {
  if ($PATH_SUB === $clientsLabel)
    $PATH_SUB = '';
  elseif (strpos($PATH_SUB, $clientsLabel . '/') === 0)
    $PATH_SUB = substr($PATH_SUB, strlen($clientsLabel . '/'));
}
if ($context === 'projects-base') {
  if ($PATH_SUB === $projectsLabel)
    $PATH_SUB = '';
  elseif (strpos($PATH_SUB, $projectsLabel . '/') === 0)
    $PATH_SUB = substr($PATH_SUB, strlen($projectsLabel . '/'));
}

// ---- browse subpath (already de-duped earlier) ----
$PATH_SUB_BROWSE = $trail($PATH_SUB);                 // '' or 'example.com/' etc.
$BROWSE_ROOT = $trail($ctxRoot);                  // context root from 3c
$absDir = $trail($BROWSE_ROOT . $PATH_SUB_BROWSE);
$exists = is_dir($absDir);

// ---- APP_ROOT: install context relative to APP_PATH ----
// '' | '../clients/client1/domain.com/' | '../clients/domain.com/' | 'projects/proj_1/'
$APP_ROOT_REL = ($APP_ROOT_REL === '') ? '' : $trail($APP_ROOT_REL);

// ---- APP_ROOT_DIR: install subpath INSIDE context (raw from query, no dedupe) ----
$INSTALL_SUB = isset($_GET['path']) ? (string) $_GET['path'] : '';
$INSTALL_SUB = preg_replace('~[^a-z0-9._\-/]~i', '', $INSTALL_SUB); // allow a–z 0–9 . _ - /
if (strpos($INSTALL_SUB, '..') !== false)
  $INSTALL_SUB = '';        // block traversal
$INSTALL_SUB = ($INSTALL_SUB === '') ? '' : $trail($INSTALL_SUB);

// ---- define constants once ----
if (!defined('APP_ROOT'))
  define('APP_ROOT', $APP_ROOT_REL);
if (!defined('APP_ROOT_DIR'))
  define('APP_ROOT_DIR', $INSTALL_SUB);

// (optional) full install target
$COMPLETE_PATH = rtrim(APP_PATH, '/\\') . '/' . APP_ROOT . APP_ROOT_DIR;

/*
// ── Browsing roots (UI listing) ───────────────────────────────────────────────
// Always browse from the context root you computed earlier in 3c
$BROWSE_ROOT = rtrim($ctxRoot, '/\\') . '/';

// Long-term correct browse dir:
$absDir = rtrim($BROWSE_ROOT . $PATH_SUB, '/\\') . '/';

// TEMPORARY root-only fallback (your A) quick fix). Uncomment if you need it:
// $absDir = $BROWSE_ROOT;

// Existence check for UI
$exists = is_dir($absDir);

// Optional: complete installer path when you need it
$COMPLETE_PATH_INSTALL = rtrim(APP_PATH, '/\\') . '/' . APP_ROOT . APP_ROOT_DIR;
// Examples:
//   $vendorDir = $COMPLETE_PATH_INSTALL;              // if APP_ROOT_DIR == 'vendor/'
//   $nodeDir   = rtrim(APP_PATH, '/\\') . '/' . APP_ROOT . 'node_modules/';

// Optional label for display:
$label = ($context === 'clients-base') ? 'clients'
  : (($context === 'projects-base') ? 'projects' : $context);
*/

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

  //if (isset($_POST['cmd']) && $_POST['cmd'] != '')
  // require_once 'app.console.php';

  if (isset($_GET['app']) && $_GET['app'] == 'ace_editor')
    require_once 'ui.ace_editor.php';

  if (isset($_POST['cmd'])) {

    chdir(APP_PATH . APP_ROOT);

    $output = [];

    //$GLOBALS['runtime']['socket'] = fsockopen(SERVER_HOST, SERVER_PORT, $errno, $errstr, 5);

    if ($_POST['cmd'] && $_POST['cmd'] != '')
      if (preg_match('/^chdir\s+(:?(.*))/i', $_POST['cmd'], $match)) {
        //exec($_POST['cmd'], $output);
        //die(header('Location: ' . APP_URL_BASE . '?app=text_editor&filename='.$_POST['cmd']));
        //$output[] = "Changing directory to " . $path;

        //$output[] = var_dump(get_required_files()); //'Location: ' . APP_PATH . APP_ROOT . rtrim(trim(preg_match('#(?:\.\./)+#', $match[1]) ? '/' : $match[1]), DIRECTORY_SEPARATOR);

        /**/
        error_log("Path: $match[1]");

        if (realpath($path = APP_PATH . APP_ROOT . rtrim(trim(preg_match('#(?:\.\./)+#', $match[1]) ? '/' : $match[1]), DIRECTORY_SEPARATOR))) {
          // Define the root directory you don't want to go past

          // Regular expression to normalize the path
          $match[1] = preg_replace('#(?:\.\./)+#', '../', $match[1]);

          // Optional: Ensure path does not start with excessive "../"
          //$maxUpLevels = 1; // Adjust as needed
          //$match[1] = preg_replace('#^((?:\.\./){' . ($maxUpLevels + 1) . ',})#', str_repeat('../', $maxUpLevels), $filteredPath);
          $rootDir = realpath(APP_PATH . APP_ROOT . ($match[1] ?? '../'));

          // Check if the resolved path is within the allowed root
          if (strpos($path, $rootDir) === 0 && strlen($path) >= strlen($rootDir)) {
            // Proceed with your existing logic if the path is valid
            /* $resultValue = (function () use ($path, $tableGen): string{
              defined('APP_CLIENT') ? '' : define('APP_CLIENT', $path);

              $basePath = rtrim(APP_PATH . APP_ROOT, DIRECTORY_SEPARATOR);

              $path = preg_replace('#^' . preg_quote($basePath, '#') . '/?#', '', $path);

              // Replace the escaped APP_PATH and APP_ROOT with the actual directory path
              if (realpath(preg_replace('#^' . preg_quote($basePath, '#') . '/?#', '', $path)) == realpath($basePath)) {
                $_GET['path'] = '';
              } elseif (
                realpath(
                  $newPath = preg_replace(
                    '#^' . preg_quote(rtrim(APP_PATH . APP_ROOT . ($_GET['domain'] ?? ''), DIRECTORY_SEPARATOR), '#') . '/?#',
                    '',
                    $path
                  )
                )
              ) {
                ($newPath === '../' ? $_GET['path'] = '' : $_GET['path'] = "$newPath/");  ///  preg_replace('/' . preg_quote(APP_PATH . APP_ROOT, DIRECTORY_SEPARATOR) . '/', '', $path)
              } else {
                $_GET['path'] = '';
              }
              //$_GET['path'] =  . '>>' . APP_CLIENT . ($_GET['domain'] ?? '');
              //dd(get_required_files(), false);
              ob_start();
              //if (is_file($include = APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php'))
              //if (isset($_ENV['COMPOSER']['AUTOLOAD']) && (bool) $_ENV['COMPOSER']['AUTOLOAD'] === TRUE)
              //  require_once $include;

              isset($tableGen) and $tableValue = $tableGen();
              ob_end_clean();
              return $tableValue ?? ''; // $app['directory']['body'];
            })(); */
            $output[] = (string) null; // $resultValue;
            //die();
          } else {
            // Handle the case where the path is trying to go past the root directory
            $output[] = "Cannot go past the root directory: $rootDir >> $path";
          }
        }
        /*
            if ($path = realpath(APP_PATH . APP_ROOT . rtrim(trim($match[1]), '/'))) {
              // Define the root directory you don't want to go past
              $root_dir = realpath(APP_PATH . APP_ROOT);

              // Resolve the parent directory path
              $parent_dir = realpath("$path/../");

              // Check if the parent directory is within the allowed root
              if (strpos($parent_dir, $root_dir) === 0 && strlen($parent_dir) >= strlen($root_dir)) {
                // Proceed with your existing logic if the path is valid
                $resultValue = (function() use ($path): string {
                  // Replace the escaped APP_PATH and APP_ROOT with the actual directory path
                  if (realpath($_GET['path'] = preg_replace('/' . preg_quote(APP_PATH . APP_ROOT, '/') . '/', '', $path)) == realpath(APP_PATH . APP_ROOT))
                    $_GET['path'] = '';
                  ob_start();
                  require 'app/directory.php';
                  $tableValue = $tableGen();
                  ob_end_clean();
                  return $tableValue; // $app['directory']['body'];
                })();
                $output[] = (string) $resultValue;
              } else {
                // Handle the case where the path is trying to go past the root directory
                $output[] = "Cannot go past the root directory: $root_dir";
              }
            }
        */
      } else if (preg_match('/^edit\s+(:?(.*))/i', $_POST['cmd'], $match)) {
        //exec($_POST['cmd'], $output);
        //die(header('Location: ' . APP_URL_BASE . '?app=text_editor&filename='.$_POST['cmd']));

        //$output[] = 'This works ... ' . dd(get_required_files(), false) . dd($_POST, false) . APP_PATH . APP_ROOT; // APP_ROOT; 

        // . DIRECTORY_SEPARATOR . ($_GET['domain'] ?? '')
        // . DIRECTORY_SEPARATOR . ($_GET['path'] ?? '')

        //(isset($_GET['path']) ? (empty($_GET['client']) ? '' : APP_BASE['clients'] . (isset($_GET['client']) || isset($_GET['domain']) ? /* APP_BASE['clients'] */ (!isset($_GET['client']) ? '' : $_GET['client']) . DIRECTORY_SEPARATOR : '') . (!isset($_GET['domain']) ? '' : $_GET['domain'])) /*APP_BASE[''] . $_GET['client'] . '/'*/ : APP_ROOT  ) . 

        $rootFilter = '';
        $filePath = APP_PATH;

        // Determine the root filter based on client and domain
        if (!empty($_GET['client'])) {
          $rootFilter = app_base('clients', null, 'rel') . $_GET['client'] . DIRECTORY_SEPARATOR;
          if (isset($_GET['domain'])) {
            $rootFilter .= $_GET['domain'] . DIRECTORY_SEPARATOR;
          }
        } elseif (isset($_GET['domain'])) {
          $rootFilter = app_base('clients', null, 'rel') . $_GET['domain'] . DIRECTORY_SEPARATOR;
        }

        // Add project-specific root filter if applicable
        if (isset($_GET['project'])) {
          $rootFilter = app_base('projects', null, 'rel') . $_GET['project'] . DIRECTORY_SEPARATOR;
        }

        // Add path to the file path
        if (isset($_GET['path'])) {
          $filePath .= $rootFilter . $_GET['path'] . DIRECTORY_SEPARATOR;
        } else {
          $filePath .= $rootFilter;
        }

        // Trim and clean the match path
        $matchPath = preg_replace('#^' . preg_quote($rootFilter, '#') . '/?#', '', $match[1] ?? '');
        $filePath .= trim($matchPath);

        // Check if the file exists and read its content
        $output[] = (is_file($filePath)) ? file_get_contents($filePath) : "File not found: $filePath";



        //$root_filter = '';
        //$output[] = is_file($file = APP_PATH . (empty($_GET['client']) ? '' : $root_filter = APP_BASE['clients'] . (isset($_GET['client']) || isset($_GET['domain']) ? /* APP_BASE['clients'] */ (!isset($_GET['client']) ? '' : $_GET['client']) . DIRECTORY_SEPARATOR : '') . (!isset($_GET['domain']) ? '' : $_GET['domain'])) . DIRECTORY_SEPARATOR . (!isset($_GET['path']) ? (!isset($_GET['project']) ? '' : $root_filter = APP_BASE['projects'] . $_GET['project']) : $_GET['path']) . DIRECTORY_SEPARATOR . trim(preg_replace('#^' . preg_quote($root_filter, '#') . '/?#', '', $match[1]))) ? file_get_contents($file) : "File not found: $file";
      }
    if (isset($output) && is_array($output)) {
      switch (count($output)) {
        case 1:
          echo /*(isset($match[1]) ? $match[1] : 'PHP') . ' >>> ' . */ join("\n... <<< ", $output);
          break;
        default:
          echo join("\n", $output);
          break;
      } // . "\n"
      //$output[] = 'post: ' . var_dump($_POST);
      //else var_dump(get_class_methods($repo));
    }
    $output = [];
    //echo $buffer;
    unset($match);
    //require_once /*APP_BASE['app'] .*/ 'console.php';
  }

  if (!isset($_POST['group_type']))
    Shutdown::setEnabled(true)->setShutdownMessage(function () {
      return ''; // 'The application has been terminated.';  
    })->shutdown();
}



//dd($directory, true);

// $style = file_get_contents(__DIR__ . '/devtools.directory.css'); // put your CSS here
if (false) { ?>
  <style>
  <?php }
ob_start(); ?>
  #app_directory-container {
    position: absolute;
    height: auto;
    width: 100%;
    /* display: none; */
    left: 15px;
    top: 15px;
    /* z-index: 99; */
  }

  .directory-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 10px;
  }

  .directory-entry {
    flex: 1 0 10%;
    /* roughly 7 items per row at full width */
    max-width: 115px;
    text-align: center;
    border: 0;
    padding: 4px;
    box-sizing: border-box;
  }

  .directory-entry img {
    display: block;
    margin: 0 auto 4px auto;
    width: 40px;
    height: auto;
  }

  <?php $UI_APP['style'] = ob_get_contents();
  ob_end_clean();
  if (false) { ?>
  </style><?php }

  // $style = preg_replace('~^\s*<style[^>]*>|</style>\s*$~i', '', ob_get_clean());
  /**
   * Generates a table.
   *
   * @return string
   */
  $group_type = $_POST['group_type'] ?? null;
  //$tableGen = function () use ($group_type): string {
  
  // ---- render UI -------------------------------------------------------------
/*

if (false) { ?>

  <body>
  <?php }
ob_start();

// If missing, you can show your "Missing directory" notice as before:
if (!$exists) {
  echo '<br><br>Missing directory: ' . htmlspecialchars($absDir);
} else
  echo $context . ' : ' . htmlspecialchars(APP_PATH . APP_ROOT) . '<br><br>';
//  var_dump([
//    'has' => compact('hasClient', 'hasDomain', 'hasProject', 'hasPath', 'hasNonEmptyPath'),
//    'vals' => compact('client', 'domain', 'project', 'path')
//  ]);

//dd($_GET, false); // { "app": "devtools\/directory", "json": "1" } 

$UI_APP['body'] = ob_get_contents();
ob_end_clean();
if (false) { ?>
  </body><?php }
// From here, use $absDir to render your directory grid.
// You can also branch on $context to decide which “listing” (clients, projects, etc.) UI to show.

return $UI_APP; */

  //ob_start();
  
  //$returnValue = ob_get_contents();
  //ob_end_clean();
  //return $returnValue;
  //  };
  
  /*ob_start(); ?>

  <?php $app['style'] = ob_get_contents();
  ob_end_clean();  */

  ob_start();
  //dd(get_required_files(), false); ?>
<div style="position: fixed; z-index: 50; left: 0px; top: 0px;">
  <div
    style="position: absolute; top: -10px; left: 0px; width: 395px; z-index: 50; background-color: rgb(250, 250, 250); border: 1px solid black; box-shadow: rgba(0, 0, 0, 0.5) 0px 0px 10px; border-radius: 5px; padding: 3px;">
    <form action="" method="GET" style="display: inline; margin: 0;">
      <input type="hidden" name="path" value="" />
      <button id="displayDirectoryBtn" style="margin: 2px 5px 0 0; border: 3px dashed red;" type=""
        onclick="this.form.submit();"><img src="resources/images/directory-www.fw.png" width="18" height="10"
          style="vertical-align: middle;">&nbsp;&#9650;</button>
    </form>
    <div style="display: inline; margin-top: -3px;"><a style="font-size: 18pt; font-weight: bold; padding: 0 3px 0 0 ;"
        href="/">&#8962;</a></div>
    <form style="display: inline;" autocomplete="off" spellcheck="false" action="" method="GET">/
      <select name="category" onchange="this.form.submit();">
        <option value=""></option>
        <option value="application">applications</option>
        <option value="client">clients</option>
        <option value="projects">projects</option>
        <option value="node_module">./node_modules</option>
        <option value="resources">./resources</option>
        <option value="project">./project</option>
        <option value="vendor">./vendor</option>
      </select>
    </form>
    <form style="display: inline;" action="" method="GET">
      <span title="/mnt/c/www/" style="margin: 2px 5px 0 0; cursor: pointer;" onclick=""> /
        <select name="path" style="" onchange="this.form.submit(); return false;">
          <option value="">.</option>
          <option value="">..</option>
          <option value="/applications">applications/</option>
          <option value="/bin">bin/</option>
          <option value="/clients">clients/</option>
          <option value="/config">config/</option>
          <option value="/data">data/</option>
          <option value="/dist">dist/</option>
          <option value="/docs">docs/</option>
          <option value="/node_modules">node_modules/</option>
          <option value="/projects">projects/</option>
          <option value="/public">public/</option>
          <option value="/resources">resources/</option>
          <option value="/src">src/</option>
          <option value="/tests">tests/</option>
          <option value="/var">var/</option>
          <option value="/vendor">vendor/</option>
        </select> / <a href="#" onclick="document.getElementById('info').style.display = 'block';">+</a>
      </span>
    </form>

  </div>
</div>
<?php /* <div id="app_directory-container" style="
position: absolute;
display: <?= isset($_GET['debug']) || isset($_GET['project']) || isset($_GET['path']) ? 'block' : 'block'; ?>;
top: 10px;
left: 0;
right: 0;
margin: 0 auto;
background-color: rgba(255, 255, 255, 0.1);
// height: auto;
width: 100%;
max-height: 80vh;
overflow-y: auto;
overflow-x: hidden;
resize: vertical;
">...</div>*/ ?>

<div id="info"
  style="position: fixed; display: none; width: 570px; height: 500px; top: calc(50% - 300px); /* 500 / 2 */
left: calc(50% - 265px); /* 1207 / 2 */ /*transform: translate(-50%, -50%);*/ border: 5px solid #000; background-repeat: no-repeat; background-color: #FFFFFF; z-index:99;">
  <div
    style="position: absolute; display: block; background-color: #FFFFFF; z-index: 1; right: 0px; margin-top: -20px;">
    [<a href="#" onclick="document.getElementById('info').style.display = 'none';">x</a>]</div>
  <form method="post" action="/?path" enctype="multipart/form-data">
    <div class="directory-grid" data-app-path="devtools/directory">
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/new_file.png" width="58" height="69" />
            New File</a>
        </div>
      </div>
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/git_clone.png" width="69" height="69" />
            Git<br>(clone)</a>
        </div>
      </div>
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/ftp_conn.png" width="82" height="71" />
            FTP</a>
        </div>
      </div>
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/www_curl.png" width="75" height="81" />
            www<br>(curl)</a>
        </div>
      </div>
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/clients.png" width="74" height="79" />
            Clients</a>
        </div>
      </div>
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/projects.png" width="74" height="79" />
            Projects</a>
        </div>
      </div>
    </div>

  </form>
</div>

<?php
//$path = APP_PATH . APP_ROOT . ($_GET['path'] ?? '');
//dd($_GET);
// $context = $context ?? 'app'; // fallback
/* ---------- inputs ---------- */
$base = rtrim(APP_PATH, '/'); // e.g. /mnt/c/www
$root = defined('APP_ROOT') && APP_ROOT == '' ? trim(APP_ROOT, '/') : '';
$client = $_GET['client'] ?? '';
$domain = $_GET['domain'] ?? '';
$project = $_GET['project'] ?? '';
$path = $_GET['path'] ?? '';
$visiblePath = PathUtils::norm($path); // path shown in UI
$segments = [];
$parent = PathUtils::parentPath($visiblePath);

/* ---------- segment: APP_PATH (always) ---------- */
$segments[] = (APP_ROOT === '') ? sprintf(
  '[ ' . ($visiblePath !== '' ? '<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s/</a><a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">%s/</a>'
    : '<a href="/">%s/</a>') . ' ]',
  htmlspecialchars($base),
  htmlspecialchars($parent, ENT_QUOTES),
  htmlspecialchars(rtrim($visiblePath, '/'))
)
  : sprintf(
    '[ <a href="/">%s/</a> ]',
    htmlspecialchars($base)
  );

/* ---------- segment: Context (APP_ROOT or explicit client/project/domain) ---------- */
if ($root !== '') {
  [$cFromRoot, $dFromRoot] = PathUtils::clientDomainFromRoot($root);
  $ctxLabel = trim(($cFromRoot ? $cFromRoot . '/' : '') . ($dFromRoot ? $dFromRoot . '/' : ''), '/');
  if ($ctxLabel === '') {
    $parts = explode('/', PathUtils::norm($root));
    $ctxLabel = implode('/', array_slice($parts, max(0, count($parts) - 2))) . '/';
  } else {
    $ctxLabel .= '/';
  }
  // click => context root (empty path)
  $segments[] = sprintf(
    ' [ <a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>' . ($visiblePath !== '' ? '<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">%s/</a>' : '') . ' ]',
    htmlspecialchars($ctxLabel),
    htmlspecialchars($parent, ENT_QUOTES),
    htmlspecialchars(rtrim($visiblePath, '/'))
  );
} else {
  if ($project) {
    $segments[] = sprintf(
      ' [ <a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s/</a>' . ($visiblePath !== '' ? '<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">%s/</a>' : '') . ' ]',
      htmlspecialchars(rtrim((string) (APP_BASE['projects'] ?? 'projects/'), '/')),
      htmlspecialchars($parent, ENT_QUOTES),
      htmlspecialchars(rtrim($visiblePath, '/'))
    );
  } elseif ($client || $domain) {
    /* $segments[] = sprintf(
      ' [ ' . ($domain && !$client ? '<a href="?domain">Domain:</a> ' : '<a href="?client">Client:</a> <a href="?client=%s" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>' . (!$domain ? '' : ' Domain: ')) . '<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>' . ($visiblePath !== '' ? '/<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">%s/</a>' : '') . ' ]',
      $domain && !$client ? htmlspecialchars($domain) : htmlspecialchars($client),
      $domain && !$client ? htmlspecialchars($domain) : htmlspecialchars($client), //APP_BASE['clients'] ?? 'clients/'
      htmlspecialchars($visiblePath),
      htmlspecialchars($parent, ENT_QUOTES),
      htmlspecialchars(rtrim($visiblePath, '/'))
    ); */

    $fmtParts = [];
    $args = [];

    $fmtParts[] = ' [ ';

    /* Left chunk: "Domain:" OR "Client:" (+ optional Domain:) */
    if ($domain !== '' && $client === '') {
      // Domain-only
      $fmtParts[] = '<a href="?domain">Domain:</a> ';
      $fmtParts[] = '<a href="?domain=%s" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>';
      $args[] = rawurlencode($domain);                 // query value
      $args[] = htmlspecialchars(rtrim($domain, '/'));             // link label

    } else {
      // Client first (if present)
      $fmtParts[] = '<a href="?client">Client:</a> ';
      if ($client !== '') {
        $fmtParts[] = '<a href="?client=%s" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>';
        $args[] = rawurlencode($client);
        $args[] = htmlspecialchars(rtrim($client, '/'));
      }

      // Optional Domain after client
      if ($domain !== '') {
        $fmtParts[] = ' <a href="?domain">Domain:</a> ';
        $fmtParts[] = '<a href="?domain=%s" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>';
        $args[] = rawurlencode($domain);
        $args[] = htmlspecialchars(rtrim($domain, '/'));
      }
    }

    /* Tail: current path + up (only if visiblePath set) */
    if ($visiblePath !== '') {
      $fmtParts[] = '/<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">%s/</a>';
      $args[] = htmlspecialchars($parent, ENT_QUOTES);               // onclick('…')
      $args[] = htmlspecialchars(rtrim($visiblePath, '/'));          // label
    }

    $fmtParts[] = ' ]';

    $segments[] = vsprintf(implode('', $fmtParts), $args);
  }
}

/* ---------- segment: current path with quick "up" ---------- */
if ($visiblePath !== '') {
  $parent = PathUtils::parentPath($visiblePath);
  $segments[] = sprintf(' <a href="#!" title="Up one level" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">&#9664; up</a> ', htmlspecialchars($parent, ENT_QUOTES));
}

/* ---------- render ---------- */
echo '<div id="breadcrumb" style="height:25px;display:inline;"><br><br>'
  . implode('', $segments)
  . '</div>';

/* ---------- existence check (kept) ---------- */
$exists = (bool) realpath($absDir ?? '');
if (!$exists) {
  echo 'Missing directory: ' . htmlspecialchars($absDir ?? '');
}

// dd("APP_PATH = " . APP_PATH . '  APP_ROOT = ' . APP_ROOT . '  APP_ROOT_DIR = ' . APP_ROOT_DIR, false);

// ---- existence check ------------------------------------------------------
switch ($context) {
  case 'clients-base':
    // dd("$context : $absDir | APP_ROOT=" . APP_ROOT, false);
    ?>
    <h3>&#9660; Domains: </h3>
    <table style="border:none;">
      <tr style="border:none;">
        <?php
        $count = 1;
        $links = array_filter(
          glob(APP_PATH . APP_BASE['clients'] . '*', GLOB_ONLYDIR),
          fn($link) => preg_match('/^(?!\d{3}-)[a-z0-9\-]+\.[a-z]{2,6}$/i', basename($link))
        );
        $old_links = $links;
        while ($link = array_shift($links)) {
          $old_link = $link;
          $link = basename($link);
          echo "<td style=\"text-align:center;border:none;\" class=\"text-xs\">
                  <a class=\"pkg_dir\" href=\"?" . (isset($_ENV['DEFAULT_CLIENT']) && $_ENV['DEFAULT_CLIENT'] == $link ? '' : "domain=$link") . "\">
                  <img src=\"resources/images/directory.png\" width=\"50\" height=\"32\" />
                  <br />$link/</a><br />
                </td>";
          if ($count >= 6)
            echo '</tr><tr>';
          elseif ($old_link == end($old_links))
            echo '</tr>';
          $count = ($count >= 6) ? 1 : $count + 1;
        }
        ?>
    </table>
    <?php
    foreach (['000', '100', '200', '300', '400'] as $key => $status) {
      if ($key != 0)
        echo "</table>\n\n\n";
      $links = array_filter(glob(APP_PATH . APP_BASE['clients'] . $status . '*'), 'is_dir');
      $statusCode = $status;
      $status = ($status == 000) ? "On-call" :
        (($status == 100) ? "Working" :
          (($status == 200) ? "Planning" :
            (($status == 300) ? "Previous" :
              (($status == 400) ? "Future" : "Unknown")))); ?>
      <h3>&#9660; Stage: <?= $status ?> (<?= $statusCode ?>)</h3>
      <table style="border:none;">
        <tr style="border:none;">
          <?php
          $count = 1;
          $old_links = $links;
          while ($link = array_shift($links)) {
            $old_link = $link;
            $link = basename($link);
            echo "<td style=\"text-align:center;border:none;\" class=\"text-xs\">
                    <a class=\"pkg_dir\" href=\"?client=$link\">
                    <img src=\"resources/images/directory.png\" width=\"50\" height=\"32\" />
                    <br />$statusCode-Client$count/</a><br />
                  </td>";
            if ($count >= 6)
              echo '</tr><tr>';
            elseif ($old_link == end($old_links))
              echo '</tr>';
            $count = ($count >= 6) ? 1 : $count + 1;
          } ?>
      </table>
      <?php
    }
    break;

  case 'projects-base':
    // dd("$context : $absDir | APP_ROOT=" . APP_ROOT, false);
    ?>
    <div style="text-align:center;border:none;" class="text-xs">
      <a class="pkg_dir" href="#" onclick="document.getElementById('app_project-container').style.display='block';">
        <img src="resources/images/project-icon.png" width="50" height="32" />
      </a><br />
      <a href="?project">./project/</a>
    </div>
    <table style="border:none;">
      <tr style="border:none;">
        <?php
        $links = array_filter(glob(APP_PATH . app_base('projects', null, 'rel') . '*'), 'is_dir');
        $count = 1;
        if (empty($links))
          echo "<hr />\n";
        $old_links = $links;
        while ($link = array_shift($links)) {
          $old_link = $link;
          $link = basename($link);
          echo "<td style=\"text-align:center;border:none;\" class=\"text-xs\">
                  <a class=\"pkg_dir\" href=\"?project=$link\">
                  <img src=\"resources/images/directory.png\" width=\"50\" height=\"32\" />
                  <br />$link</a><br />
                </td>";
          if ($count >= 7)
            echo '</tr><tr>';
          elseif ($old_link == end($old_links))
            echo '</tr>';
          $count = ($count >= 7) ? 1 : $count + 1;
        } ?>
    </table>
    <?php
    break;

  case 'clients':
  case 'projects':
  case 'app':
  default:
    // Your generic directory grid for $absDir
    //render_directory_grid($absDir);
    //dd("$context : $absDir", false);  // dd(APP_ROOT, false);

    // dd(APP_ROOT, false);

    $path = rtrim($absDir, '/\\') . '/';
    /*
          if (defined('APP_ROOT') && APP_ROOT) {
            $path .= APP_ROOT;


          } elseif (APP_ROOT === '') {
            // Handle client-specific path
            if (isset($_GET['client'])) {
              $path = APP_BASE['clients'] . $_GET['client'] . DIRECTORY_SEPARATOR;
            }

            // Add domain to the path if applicable
            if (isset($_GET['domain'])) {
              $path = APP_PATH . APP_BASE['clients'] . $_GET['domain'] . DIRECTORY_SEPARATOR;
            } elseif (!isset($_GET['client']) && isset($_GET['path']) && $_GET['path'] == 'vendor') {
              // Default to vendor path if no domain or client is set
              $path .= 'vendor' . DIRECTORY_SEPARATOR;
              if (isset($_GET['client'])) {
                $path .= $_GET['client'] . DIRECTORY_SEPARATOR;
              }
            }
          } else {
            $path .= APP_ROOT;
          }
    */
    // dd("$COMPLETE_PATH_INSTALL", false);

    if (isset($_GET['path']) && preg_match('/^vendor\/?/', $_GET['path'])) {

      //if ($_ENV['COMPOSER']['AUTOLOAD'] == true)
      //require_once APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
      //require_once APP_PATH . 'api' . DIRECTORY_SEPARATOR . 'composer.php'; ?>
      <!-- iframe src="composer_pkg.php" style="height: 500px; width: 700px;"></iframe -->
      <div style="width: 700px; ">
        <div style="display: inline-block; width: 350px;"><a href="#!"
            onclick="handleClick(event, 'vendor/'); openApp(\'tools/registry/composer\');">Composers</a>
          Vendor Packages [Installed] List</div>
        <div style="display: inline-block; text-align: right; width: 300px;">
          <form
            action="<?= !defined('APP_URL') ? '//' . APP_DOMAIN . APP_URL_PATH . '?' . http_build_query(APP_QUERY, '', '&amp;') : APP_URL . '?' . http_build_query(APP_QUERY, '', '&amp;') ?>"
            method="POST">
            <input id="RequirePkg" type="text" title="Enter Text and onSelect" list="RequirePkgs"
              placeholder="[vendor]/[package]" name="composer[package]" value onselect="get_package(this);" autocomplete="off"
              style=" margin-top: 4px;">
            <button type="submit" style="border: 1px solid #000; margin-top: 4px;"> Add </button>
            <div style="display: inline-block; float: right; text-align: left; margin-left: 10px;" class="text-xs">
              <input type="checkbox" name="composer[install]" value="" /> Install<br />
              <input type="checkbox" name="composer[update]" value="" /> Update
            </div>
            <datalist id="RequirePkgs">
              <option value=""></option>
            </datalist>
          </form>
        </div>
      </div>
    <?php }
    /* var_dump(COMPOSER_VENDORS); null; */ //dd($_GET, false); 
    ?>
    <div class="directory-grid" data-app-path="devtools/directory">
      <?php

      if (isset($_REQUEST['path']) && $_REQUEST['path'] !== '') {
        /*
                  echo <<<END
        <div class="directory-entry">
        <div
          style="position: relative; display: block;">
        END;

          echo '<a href="?' . (isset($_REQUEST['client']) ? 'client=test' : 'testing') . '&domain={$_REQUEST['domain']}&path={$_REQUEST['path']}" onclick="handleClick(event, '../{$_REQUEST['path']}');">

        echo <<<END
            <img src="resources/images/directory.png" width="50" height="32" />
            ../</a>
        </div>
        </div>
        END;
        */
      }

      //dd(APP_CLIENT, false);
  
      //$path = (defined('APP_CLIENT')) ? APP_CLIENT : APP_PATH . (!isset($_GET['domain']) && isset($_GET['client']) ? APP_ROOT : APP_ROOT);
  
      //echo dirname($pathAvail) . DIRECTORY_SEPARATOR . ($_GET['path'] ?? '');
  
      //$paths = ['thgsgfhfgh.php']; // dirname(APP_PATH . APP_ROOT) . DIRECTORY_SEPARATOR
      $paths = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '{.[!.]*,*}', GLOB_BRACE | GLOB_MARK);

      unset($path);
      //dd(urldecode($_GET['path']));
  
      usort($paths, function ($a, $b) {
        $aIsDir = is_dir($a);
        $bIsDir = is_dir($b);

        // Check if either $a or $b is the "project.php" file
        $aIsProjectFile = !$aIsDir && basename($a) === 'project.php';
        $bIsProjectFile = !$bIsDir && basename($b) === 'project.php';

        // Handle the case when either $a or $b is the "project.php" file
        if ($aIsProjectFile || $bIsProjectFile) {
          if ($aIsProjectFile && $bIsProjectFile) {   // -1 0 1
            return -1; // Both are "project.php" files, no change in order
          } elseif ($aIsProjectFile) {
            return 0; // $a is "project.php", move it down
          } else {
            return 1; // $b is "project.php", move it up
          }
        }

        // Directories go first, then files
        if ($aIsDir && !$bIsDir) {
          return -1;
        } elseif (!$aIsDir && $bIsDir) {
          return 1;
        }

        // If both are directories or both are files, sort alphabetically
        return strcasecmp($a, $b);
      });
      /*
      usort($paths, function ($a, $b) {
          $aIsDir = is_dir($a);
          $bIsDir = is_dir($b);

          // Directories go first, then files
          if ($aIsDir && !$bIsDir) {
              return -1;
          } elseif (!$aIsDir && $bIsDir) {
              return 1;
          }

          // If both are directories or both are files, sort alphabetically
          return strcasecmp($a, $b);
      });
      */

      $count = 1;
      $lastKey = array_key_last($paths);

      if (!empty($paths))
        foreach ($paths as $key => $path) {
          // Adjust the path to be relative to the current directory
          $relativePath = str_replace(APP_PATH . APP_ROOT, '', rtrim($path, DIRECTORY_SEPARATOR));

          echo '<div class="directory-entry">' . "\n";
          if (is_dir($path)) {
            if (substr(PHP_OS, 0, 3) == 'WIN') {
              $relativePath = rtrim(str_replace('\\', '/', $relativePath), DIRECTORY_SEPARATOR); //
            } elseif (stripos(PHP_OS, 'LIN') == 0) {
              $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath) . DIRECTORY_SEPARATOR;
            }

            //dd($relativePath);
  

            //function buildQueryString($queryParams, $relativePath) {
/*
              $client = isset($_GET['client']) ? 'client=' . urlencode($_GET['client']) . '&' : '';
              $domain = isset($_GET['domain']) && $_GET['domain'] !== ''
                ? 'domain=' . urlencode($_GET['domain']) . '&'
                : '';
              $project = isset($_GET['project']) ? 'project=' . urlencode($_GET['project']) . '&' : '';

              // return null;
              $url = $client . $domain . $project . 'path=' . urlencode($relativePath); */
            //}
  
            /**
             * Inputs:
             *  - $path: absolute or mixed path of the current item (file/dir entry you are rendering)
             *  - $relativePath: path relative to your current context (can be same as $_GET['path'] or derived)
             *
             * Contract:
             *  - We never mutate $_GET
             *  - We only show at most ONE of project|client|domain in the query
             *  - We always carry a normalized "path" that preserves the parent and appends the child "/"
             */

            /* -------------- main logic (unchanged except using PathUtils::) -------------- */

            $GET = [
              'project' => $_GET['project'] ?? '',
              'client' => $_GET['client'] ?? '',
              'domain' => $_GET['domain'] ?? '',
              'path' => $_GET['path'] ?? '',
            ];

            /* ---------- usage inside your link render ---------- */
            $GET = $_GET; // read-only use
            $visibleBase = PathUtils::norm($GET['path'] ?? '');
            $childName = basename(rtrim((string) $path, "/\\"));
            $nextPath = PathUtils::buildChildPath($visibleBase, $childName);

            $href = QueryUrl::build($GET, $nextPath);
            $onclickAttr = " onclick=\"return App['devtools/directory'].handleClick('" . htmlspecialchars($nextPath, ENT_QUOTES) . "')\"";
            $dataDir = htmlspecialchars($nextPath, ENT_QUOTES);
            /* ---------- end usage ---------- */
            switch (true) {
              case is_top_marker_at($path, ['.composer'], $BROWSE_ROOT):
                echo '<div style="position: relative; border: 4px dashed #6B4329;">'
                  . '<a href="#!" data-open-app="tools/registry/composer"><img src="resources/images/directory-composer.png" width="50" height="32" alt=""></a>'
                  . '<a href="#!" data-dir="' . $relativePath . '"
   data-open-app="tools/registry/composer"
   data-open-after-dir="1">' . basename($path) . '/</a>'

                  //. '<a href="#!" onclick="return handleClick(event, \'' . $relativePath . '\'); openApp(\'tools/registry/composer\');">' . '<img src="resources/images/directory-composer.png" width="50" height="32" /></a>'
                  //. '<a href="' . /* basename(__FILE__) .*/ '#!' /* . $url */ . '" data-path="vendor/" onclick="return App[\'devtools/directory\'].handleClick(\'' . $relativePath . '\')">' . basename($path) . '/</a>  // "?path=' . basename($path) . '"   
                  . '</div>' . "\n";
                break;

              // add others if you like
              case is_top_marker_at($path, ['.git', '.github', 'bootstrap'], $BROWSE_ROOT):
                echo '<div style="position: relative; border: 4px dashed #F05033;">'
                  . '<a href="#!"
   data-open-app="tools/code/git"><img src="resources/images/directory-git.png" width="50" height="32" alt=""></a>'
                  . '<a href="#!" data-dir="' . $relativePath . '"
   data-open-app="tools/code/git"
   data-open-after-dir="1">' . basename($path) . '/</a>'
                  //. '<a href="#!" onclick="openApp(\'tools/code/git\');">' // "?path=' . basename($path) . '" 
                  //. '<img src="resources/images/directory-git.png" width="50" height="32" /></a>'
                  //. '<a href="' . /* basename(__FILE__) .*/ '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\'); openApp(\'tools/code/git\');">' . basename($path) . '/</a>' .
                  . '</div>' . "\n";
                break;

              default:
                // Render the folder link
  
                echo '<a href="' . $href . '"' . $onclickAttr . ' data-dir="' . $dataDir . '">
  <img src="resources/images/directory.png" width="50" height="32" alt=""></a>'
                  . '<a href="' . $href . '"' . $onclickAttr . ' data-dir="' . $dataDir . '">'
                  . htmlspecialchars($childName) . '/</a>';
                break;
            }
          } elseif (is_file($path)) {
            $relativePath = str_replace(APP_PATH . APP_ROOT, '', rtrim($path, DIRECTORY_SEPARATOR));
            // Ensure the path excludes the domain if present in the folder structure
            $relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);

            if (!empty($_GET['domain'])) {
              // Remove the domain from the relative path if it exists at the start
              $relativePath = preg_replace(
                '#^' . preg_quote($_GET['domain'], '#') . '/?#',
                '',
                $relativePath
              );
            }

            // Initialize query parameters
            $queryParams = [];

            if (!isset($_GET['path']))
              $_GET['path'] = '';

            $_GET['app'] = 'ace_editor';

            // Determine the parameters to use based on the conditions
            if (isset($_GET['client']) && isset($_GET['domain'])) {
              // Case 1: Both client and domain are set
              $queryParams = [
                'client' => $_GET['client'],
                'domain' => $_GET['domain'],
                'path' => $_GET['path'] ?? dirname(rtrim($path, '/')) . '/', // Add the path parameter
                'app' => $_GET['app'],
                'file' => basename($path),
              ];
            } elseif (isset($_GET['client'])) {
              // Case 2: Only client is set
              $queryParams = [
                'client' => $_GET['client'],
                'domain' => $_GET['domain'] ?? '' /*rtrim($relativePath, '/')*/ , // Default domain if not explicitly provided
                'path' => $_GET['path'] ?? dirname(rtrim($path, '/')) . '/', // Add the path parameter
                'app' => $_GET['app'],
                'file' => basename($path),
              ];
            } elseif (isset($_GET['domain'])) {
              // Case 2: Only client is set
              $queryParams = [
                'domain' => $_GET['domain'] ?? '' /*rtrim($relativePath, '/')*/ , // Default domain if not explicitly provided
                'path' => $_GET['path'] ?? dirname(rtrim($path, '/')) . '/', // Add the path parameter
                'app' => $_GET['app'],
                'file' => basename($path),
              ];
            } elseif (isset($_GET['project'])) {
              $queryParams = [
                //'path' => rtrim($relativePath, '/') . '/', // Use the path parameter
                'project' => $_GET['project'] ?? '', // Add the path parameter
                'path' => $_GET['path'] ?? '',
                'app' => $_GET['app'],
                'file' => basename($path),
              ];
            } elseif (isset($_GET['path'])) {
              // Case 3: Only path is set
              $queryParams = [
                //'path' => rtrim($relativePath, '/') . '/', // Use the path parameter
                'path' => $_GET['path'] ?? '', // Add the path parameter
                'app' => $_GET['app'],
                'file' => basename($path),
              ];
            }

            // Filter out empty parameters to avoid unnecessary query string entries
//$queryParams = array_filter($queryParams);
  
            // Build the query string
            $queryString = http_build_query($queryParams);

            // Final URL
            $url = /*basename(__FILE__) .*/ "?$queryString";

            if (preg_match('/^\..*/', basename($path))) {

              //$relativePath = str_replace('\\', '\\\\', $relativePath );
  
              switch (basename($path)) {
                case '.htaccess':
                  echo '<div style="position: relative; border: 4px dashed #A50F5E;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : "path={$_GET['path']}&") . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/htaccess_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
                  break;
                case '.babelrc':
                  echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : "path={$_GET['path']}&") . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/babelrc_file.gif" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
                  break;
                case '.gitignore':
                  echo '<div style="position: relative; border: 4px dashed #F05033;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . 'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/gitignore_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
                  break;
                case '.env.bck':
                case '.env':
                  echo '<div style="position: relative;"><a onclick="openNewEditorWindow(\'' . basename($path) . '\', \'Hello123\');"><img src="resources/images/env_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
                  break;
                default:
                  echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/env_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
              }
            } elseif (preg_match('/^package(?:-lock)?\.(json)/', basename($path))) {
              echo '<div style="position: relative; border: 4px dashed #E14747;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\'); document.getElementById(\'app_node_js-container\').style.display=\'block\';">';

              switch (basename($path)) {
                case 'package.json':
                  echo '<img src="resources/images/package_json_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                    . '</div>' . "\n";
                  break;
                case 'package-lock.json':
                  echo '<img src="resources/images/package-lock_json_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                    . '</div>' . "\n";
                  break;
              }

            } elseif (preg_match('/^composer(?:-setup)?\.(json|lock|php|phar)/', basename($path))) {
              echo '<div style="position: relative;"><div style="position: relative; border: 4px dashed #6B4329;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\'); openApp(\'tools/registry/composer\');">';

              switch (basename($path)) {
                case 'composer.json':
                  echo '<img src="resources/images/composer_json_file.gif" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\');">' . basename($path) . '</a>'
                    . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                    . '</div></div>' . "\n";
                  break;
                case 'composer.lock':
                  echo '<img src="resources/images/composer_lock_file.gif" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    . (isset($errors['COMPOSER-VALIDATE-LOCK']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div></div>' . "\n";
                  break;
                case 'composer.phar':
                  echo '<img src="resources/images/phar_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div></div>' . "\n";
                  break;
                default:
                  echo '<img src="resources/images/composer_php_file.gif" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                    . '</a></div></div>' . "\n";
                  break;
              }
            } elseif (preg_match('/^.*\.js$/', basename($path))) {
              switch (basename($path)) {
                case 'webpack.config.js':
                  echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '"><img src="resources/images/webpack_config_js_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                  break;
                default:
                  echo '<a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/js_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                  break;
              }

            } elseif (preg_match('/^.*\.md$/', basename($path))) {
              echo '<div style="position: relative; border: 4px dashed #8BBB4B;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/md_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';

            } elseif (preg_match('/^.*\.php$/', basename($path))) {
              if (preg_match('/^project\.php/', basename($path)))
                echo '<div style="position: relative; border: 4px dashed #2C88DA;"><a style="position: relative;" href="' . (isset($_GET['project']) ? 'project#!' : '#') . '" onclick="document.getElementById(\'app_project-container\').style.display=\'block\';"><div style="position: absolute; left: -60px; top: -20px; color: red; font-weight: bold;">' . (isset($_GET['project']) ? '' : '') . '</div><img src="resources/images/project-icon.png" width="40" height="50" /></a><a href="' . /*basename(__FILE__) .*/ '?' . (isset($_GET['project']) ? 'project#!' : (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'app=ace_editor' . '&file=' . basename($path)) . '" ' . (isset($_GET['project']) ? 'onclick="document.getElementById(\'app_ace_editor-container\').style.display=\'block\';"' : 'onclick="handleClick(event, \'' . basename($relativePath) . '\')"') . '>' . basename($path) . '</a></div>';
              elseif (basename($path) == 'phpunit.php')
                echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . 'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/phpunit_php_file.png" width="40" height="50" /></a>' . '<a href="' . /*basename(__FILE__) .*/ '?file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
              elseif (basename($path) == 'bootstrap.php')
                echo '<div style="position: relative; border: 4px dashed #897AE3;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . '&path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';
              elseif (basename($path) == 'server.php')
                echo '<div style="position: relative; border: 4px dashed #897AE3;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . '&path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';
              else
                echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . 'path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';

            } elseif (basename($path) == 'LICENSE' && preg_match('/^' . preg_quote(APP_PATH, '/') . 'LICENSE$/', $path)) {
              /* https://github.com/unlicense */
              echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path']) . '&app=ace_editor' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/license_file.png" width="40" height="50" /></a>un' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                . '.org</a>'
                /* . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                . '</div>' . "\n";
            } elseif (basename($path) == basename(ini_get('error_log')))
              echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path']) . '&app=ace_editor' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">'
                . '<img src="resources/images/error_log.png" width="40" height="50" /></a><a id="app_php-error-log" href="' . (APP_URL_BASE['query'] != '' ? '?' . APP_URL_BASE['query'] : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') . /* '?' . basename(ini_get('error_log')) . '=unlink' */ '" style="text-decoration: line-through; background-color: red; color: white;"></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '</a><div style="position: absolute; top: -8px; left: 8px; color: red; font-weight: bold;"><a href="#" onclick="$(\'#requestInput\').val(\'unlink error_log\'); $(\'#requestSubmit\').click();">[X]</a></div>' : '')
                . '</div>' . "\n";
            elseif (preg_match('/^.*\.exe$/', basename($path))) {
              echo '<div style="position: relative; border: 4px dashed #8BBB4B;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . 'download&' . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '"><img src="resources/images/exe_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '">' . basename($path) . '</a></div>';

            } else
              echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
          }
          echo "</div>\n";
        } ?>
    </div>
    <?php
    break;
}

/*
  $basePath = rtrim(APP_PATH, DIRECTORY_SEPARATOR);
  $clientPath = $_GET['client'] ?? null;
  $domainPath = $_GET['domain'] ?? null;
  $projectPath = $_GET['project'] ?? null;
  $relativePath = isset($_GET['path']) ? trim($_GET['path'], DIRECTORY_SEPARATOR) : null;

  // Base link
  $navigation = '<div style="height: 25px; display: inline;"><br /><br />[[ <a href="#" onclick="handleClick(event, \'./\');">' . APP_PATH . '</a> ]' . (APP_ROOT !== '' ? '[ <a href="#" onclick="handleClick(event, \'../\');">' . APP_ROOT . '</a> ]' : '') . (isset($_REQUEST['path']) && $_REQUEST['path'] !== '' ? '[ <a href="#" onclick="handleClick(event, \'../\');">' . (APP_BASE[rtrim($_REQUEST['path'], DIRECTORY_SEPARATOR)] ?? $_REQUEST['path']) . '</a> ]' : '') . '] ' . '</div><div style="display: inline;">' . '</div>';
  echo $navigation;
*/
/*  $cwd = getcwd();
(str_starts_with($cwd, APP_PATH)
  ? substr($cwd, strlen(APP_PATH))
  : '')
dd($_GET, false);

dd(APP_PATH . APP_ROOT, false);
  dd(get_required_files(), false);
dd($_POST, false);*/
//dd(APP_CLIENT, false);

if (isset($_GET['path']) && preg_match('/^project\/?/', $_GET['path']) || isset($_GET['project']) && empty($_GET['project'])) {

} elseif (isset($_GET['application'])) {
  if (readlinkToEnd($_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'applications') == '/mnt/c/www/applications') {
    if ($_GET['application']) {

      $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'applications/' . $_GET['application']), 'is_file');

      echo '<h3>Application: 7-Zip</h3>';

      echo '<br /><div style="text-align: center; margin: 0 auto;"><a href="https://www.7-zip.org/download.html"><img width="110" height="63" src="http://www.7-zip.org/7ziplogo.png" alt="7-Zip" border="0" /><br />' . basename($links[0]) . ' =&gt; <a href="https://www.7-zip.org/a/7z2301-x64.exe">7z2301-x64.exe</a></a></div>' . "<br />";
    } else {
      $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'applications/*'), 'is_file'); ?>
      <h3>Applications:</h3>
      <table width="" style="border: none;">
        <tr style=" border: none;">
          <?php
          //if (empty($links)) {
          //  echo '<option value="" selected>---</option>' . "\n"; // label="     "
          //} else  //dd($links);
          $count = 1;
          $old_links = $links;
          while ($link = array_shift($links)) {
            $old_link = $link;
            $link = basename($link);

            echo '<td style="text-align: center; border: none;" class="text-xs">' . "\n";

            echo '<a class="pkg_dir" href="?application=' . $link . '">'
              . '<img src="resources/images/app_file.png" width="50" height="32" style="" /><br />' . $link . '</a><br />'
              . '</td>' . "\n";

            if ($count >= 3)
              echo '</tr><tr>';
            elseif ($old_link == end($old_links))
              echo '</tr>';

            if (isset($count) && $count >= 3)
              $count = 1;
            else
              $count++;
          } ?>
      </table>
    <?php }
  }
} elseif (isset($_GET['node_module']) && empty($_GET['node_module'])) {
  //if (readlinkToEnd('/var/www/applications') == '/mnt/c/www/applications') { }
  $links = array_filter(glob(APP_PATH . 'node_modules/*'), 'is_dir'); ?>
  <div style="display: inline-block; width: 350px;">Node Modules [Installed] List</div>
  <div style="display: inline-block; text-align: right; width: 300px; ">
    <form action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + ['app' => 'composer', 'path' => 'vendor']) ?>"
      method="POST">
      <input id="RequirePkg" type="text" title="Enter Text and onSelect" list="RequirePkgs"
        placeholder="[vendor]/[package]" name="composer[package]" value="" onselect="get_package(this);"
        autocomplete="off" style=" margin-top: 4px;">
      <button type="submit" style="border: 1px solid #000; margin-top: 4px;"> Add </button>
      <div style="display: inline-block; float: right; text-align: left; margin-left: 10px;" class="text-xs">
        <input type="checkbox" name="composer[install]" value=""> Install<br>
        <input type="checkbox" name="composer[update]" value=""> Update
      </div>
      <datalist id="RequirePkgs">
        <option value=""></option>
      </datalist>
    </form>
  </div>
  <table width="" style="border: none;">
    <?php
    //if (empty($links)) {
    //  echo '<option value="" selected>---</option>' . "\n"; // label="     "
    //} else  //dd($links);
    $count = 1;
    $old_links = $links;
    while ($link = array_shift($links)) {
      $old_link = $link;
      $link = basename($link);
      echo '<tr style=" border: none;">' . "\n";
      echo '<td style="text-align: center; border: none;" class="text-xs">' . "\n";
      echo '<a class="pkg_dir" href="?application=' . $link . '">'
        . '<img src="resources/images/directory.png" width="50" height="32" style="" /><br />' . $link . '</a><br />'
        . '</td>' . "\n";

      if ($count >= 3)
        echo '</tr><tr>';
      elseif ($old_link == end($old_links))
        echo '</tr>';

      if (isset($count) && $count >= 3)
        $count = 1;
      else
        $count++;
    }

    ?>
  </table>
<?php } elseif (isset($_GET['path']) && preg_match('/^client(?:s|ele)?\/?/', $_GET['path']) || isset($_GET['client']) && empty($_GET['client'])) {

} else {

}
?>
</div>
<?php $UI_APP['body'] = ob_get_contents();
ob_end_clean();


if (false) { ?>
  <script>
  <?php }
ob_start(); ?>
    // devtools/directory module script
    (() => {
      const APP_ID = 'devtools/directory';
      const CONTAINER_ID = 'app_devtools_directory-container';

      const container = document.getElementById(CONTAINER_ID);
      if (!container) return;

      // avoid double-binding if this app reloads
      if (container.dataset.bound === '1') return;
      container.dataset.bound = '1';

      // ensure registries exist (keeps inline access patterns working)
      window.AppMods = window.AppMods || {};
      window.App = window.App || new Proxy({}, {
        get(_t, k) { return window.AppMods[k]; },
        has(_t, k) { return k in window.AppMods; }
      });

      // --- URL + reload helper (fixed app param) ---
      async function handleClick(path) {
        const url = new URL(location.href);

        if (path) url.searchParams.set('path', path);
        else url.searchParams.delete('path');

        // ensure ?app is never shown in the URL
        url.searchParams.delete('app');

        history.pushState({ path: path || '' }, '', url.toString());

        await window.openApp?.('devtools/directory', {
          params: path ? { path } : {},
          forceReload: true,
          from: 'dir-click'
        });
        return false;
      }

      // Public API (kept for other modules / inline)
      const api = { init() { }, handleClick };
      window.AppMods[APP_ID] = Object.assign(window.AppMods[APP_ID] || {}, api);
      window.App[APP_ID] = window.AppMods[APP_ID];

      // --- Delegated clicks inside the directory container ---
      container.addEventListener('click', async (e) => {
        const el = e.target.closest('[data-dir],[data-path],[data-open-app]');
        if (!el) return;

        const path = el.getAttribute('data-dir') ?? el.getAttribute('data-path');
        const app = el.getAttribute('data-open-app');
        const openAfter = el.getAttribute('data-open-after-dir') === '1';

        // Case 1: directory click (we own this; prevent bubbling to global delegate)
        if (path) {
          e.preventDefault();
          e.stopPropagation();

          // If also flagged to open an app afterwards, do it in sequence
          if (openAfter && app) {
            await handleClick(path);
            // pass path along if your other app uses it; otherwise call without params
            window.openApp?.(app, { params: { path }, from: 'dir-tile' });
            return;
          }

          // Just a plain directory change
          await handleClick(path);
          return;
        }

        // Case 2: pure app tile (no path) — let the global document delegate handle it
        // (Do NOT call preventDefault/stopPropagation here.)
      });

      // --- Back/forward support for path changes ---
      window.addEventListener('popstate', (ev) => {
        // Only react if this module is currently present
        if (!document.getElementById('app_devtools_directory-container')) return;

        const sp = new URLSearchParams(location.search);
        const path = (ev.state && 'path' in ev.state) ? ev.state.path : (sp.get('path') || '');

        window.openApp?.('devtools/directory', {
          params: path ? { path } : {},
          forceReload: true,
          from: 'popstate'
        });
      });

      // Respect ?path=… on initial load (optional: trigger a reload if you want)
      // const urlPath = new URLSearchParams(location.search).get('path');
      // if (urlPath) { handleClick(urlPath); }
    })();

  // register on a global so index.php can find it
  //window.AppModules ??= {};
  //window.AppModules['devtools/directory'] = { init, handleClick };

  // tell the host page we’re ready
  //window.__registerAppModule('devtools/directory', { init, handleClick });

  // example returned by dispatcher when Accept: text/javascript

  <?php
  $UI_APP['script'] = ob_get_contents();
  ob_end_clean();

  if (false) { ?>
  </script>
<?php }

  ob_start(); ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

  <?php
  // (APP_IS_ONLINE && check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/tailwindcss-3.3.5.js')?
// Path to the JavaScript file
  $path = app_base('resources', null, 'abs') . 'js/tailwindcss-3.3.5.js';

  // Create the directory if it doesn't exist
  is_dir(dirname($path)) or mkdir(dirname($path), 0755, true);

  // URL for the CDN
  $url = 'https://cdn.tailwindcss.com';

  // Check if the file exists and if it needs to be updated
  if (defined('APP_IS_ONLINE') && APP_IS_ONLINE)
    if (!is_file($path) || (time() - filemtime($path)) > 5 * 24 * 60 * 60) { // ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path . 'tailwindcss-3.3.5.js'))))) / 86400)) <= 0 
      // Download the file from the CDN
      $handle = curl_init($url);
      curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
      $js = curl_exec($handle);

      // Check if the download was successful
      if (!empty($js)) {
        // Save the file
        file_put_contents($path, $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
      }
    } ?>

  <script
    src="<?= defined('APP_IS_ONLINE') && APP_IS_ONLINE && check_http_status($url) ? substr($url, strpos($url, parse_url($url)['host']) + strlen(parse_url($url)['host'])) : substr($path, strpos($path, dirname(app_base('resources', null, 'rel') . 'js'))) ?>"></script>

  <style type="text/tailwindcss">
    <?= $UI_APP['style']; ?>
  </style>
</head>

<body>
  <?= $UI_APP['body']; ?>

  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="resources/js/jquery/jquery.min.js"></script> -->
  <script>
    <?= $UI_APP['script']; ?>
  </script>
</body>

</html>
<?php $UI_APP['html'] = ob_get_contents();
ob_end_clean();

// bootstrap if directly accessed
if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"]))
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    chdir('../');
    if ($path = realpath(/*'config' . DIRECTORY_SEPARATOR . */ 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.php')) // is_file('bootstrap.php')
      require_once $path;

    //die(var_dump(APP_PATH));
  } else
    die(var_dump("Path was not found. file=$path"));

//check if file is included or accessed directly
if (defined('APP_RUNNING') && isset($_GET['app']) && $_GET['app'] == 'directory' && APP_DEBUG)
  exit($UI_APP['html']);
else
  return $UI_APP;
