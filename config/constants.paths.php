<?php
// config/constants.paths.php
/**
 * Resolve a root directory that may be relative to APP_PATH today,
 * but could be absolute in the future.
 *
 * Returns [absolutePathWithTrailingSlash, displayRelativeRoot, isInsideApp]
 *   - absolutePath:      for filesystem ops (glob, is_file, etc.)
 *   - displayRelativeRoot: "projects" or "clients" (stable label for UI)
 *   - isInsideApp:       bool flag if it's under APP_PATH
 */
function resolve_root(string $hint, string $appPath): array
{
    $appPath = rtrim($appPath, "/\\") . DIRECTORY_SEPARATOR;
    $parent = rtrim(realpath($appPath . '..') ?: $appPath . '..', "/\\") . DIRECTORY_SEPARATOR;
    $norm = str_replace('\\', '/', trim($hint));

    $isAbsolute = (bool) preg_match('#^/|^[A-Za-z]:/#', $norm);
    if ($norm === '') {
        // fallback to app root
        return [$appPath, '', true];
    }

    if ($isAbsolute) {
        $abs = rtrim(realpath($norm) ?: $norm, "/\\") . DIRECTORY_SEPARATOR;
        // label = last segment (e.g., ".../projects" => "projects")
        $label = basename(rtrim($abs, "/\\"));
        $inside = str_starts_with($abs, $appPath);
        return [$abs, $label, $inside];
    }

    // Relative to app (today's scenario)
    $abs = rtrim(realpath($appPath . $norm) ?: ($appPath . $norm), "/\\") . DIRECTORY_SEPARATOR;
    $label = trim($norm, '/'); // e.g., "projects"
    $inside = str_starts_with($abs, $appPath);
    return [$abs, $label, $inside];
}

/**
 * config/constants.paths.php
 * Base directory resolution + helpers.
 * Requires APP_PATH to be defined earlier OR will define it here.
 */

// ---- helpers ---------------------------------------------------------------

if (!function_exists('normalize_dir')) {
    function normalize_dir(string $path): string
    {
        if ($path === '')
            return '';
        $sep = DIRECTORY_SEPARATOR;
        $path = str_replace(['\\', '/'], $sep, $path);
        $path = rtrim($path, $sep) . $sep;
        return preg_replace('#' . preg_quote($sep, '#') . '+#', $sep, $path);
    }
}

if (!function_exists('join_path')) {
    function join_path(string ...$parts): string
    {
        $sep = DIRECTORY_SEPARATOR;
        $path = implode($sep, array_filter($parts, fn($p) => $p !== '' && $p !== $sep));
        $path = str_replace(['\\', '/'], $sep, $path);
        return preg_replace('#' . preg_quote($sep, '#') . '+#', $sep, $path);
    }
}

if (!function_exists('resolve_abs_path')) {
    /** Resolve absolute path from base + spec (absolute or relative). */
    function resolve_abs_path(string $base, string $spec): string
    {
        $sep = DIRECTORY_SEPARATOR;
        $spec = str_replace(['\\', '/'], $sep, $spec);

        $isAbs = str_starts_with($spec, $sep) || preg_match('#^[A-Za-z]:\\\\#', $spec);
        $candidate = $isAbs ? $spec : join_path($base, $spec);

        $real = @realpath($candidate);
        return $real !== false ? $real : $candidate;
    }
}

// ---- APP_PATH --------------------------------------------------------------

defined('APP_PATH') || define('APP_PATH', normalize_dir(realpath(dirname(__DIR__, 1))));

// ---- path spec with fallbacks ---------------------------------------------

$PATH_SPEC = [
    'app' => ['default' => 'app', 'fallbacks' => [], 'required' => true],
    'config' => ['default' => 'config', 'fallbacks' => [], 'required' => true],
    'data' => ['default' => 'data', 'fallbacks' => [], 'required' => false],
    'public' => ['default' => 'public', 'fallbacks' => [], 'required' => true],
    'resources' => ['default' => 'resources', 'fallbacks' => [], 'required' => false],
    'src' => ['default' => 'src', 'fallbacks' => [], 'required' => false],
    'var' => ['default' => 'var', 'fallbacks' => [], 'required' => false],
    'vendor' => ['default' => 'vendor', 'fallbacks' => [], 'required' => false],
    'node_modules' => ['default' => 'node_modules', 'fallbacks' => [], 'required' => false],

    // external-first, then internal fallback
    'projects' => ['default' => 'projects', 'fallbacks' => ['../projects'], 'required' => false],
    'clients' => ['default' => 'clients', 'fallbacks' => ['../clients'], 'required' => false],
];

// ---- env overrides (optional) ---------------------------------------------

foreach ($PATH_SPEC as $key => &$spec) {
    $envKey = 'APP_PATH_' . strtoupper($key);
    $override = getenv($envKey);
    if (is_string($override) && $override !== '') {
        $spec['default'] = $override;
    }
}
unset($spec);

// ---- resolve + validate ----------------------------------------------------

$errors ??= [];
$APP_BASE = [];
$APP_BASE_REL = [];
$APP_BASE_SRC = [];

foreach ($PATH_SPEC as $key => $spec) {
    $candidates = array_merge([$spec['default']], $spec['fallbacks']);

    $matches = [];
    foreach ($candidates as $i => $cand) {
        $abs = resolve_abs_path(APP_PATH, $cand);
        if (is_dir($abs)) {
            $matches[] = [
                'abs' => normalize_dir($abs),
                'rel' => normalize_dir(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $cand)),
                'src' => $i === 0 ? 'default' : ("fallback:$i"),
                'inside' => str_starts_with(realpath($abs) ?: $abs, rtrim(APP_PATH, "/\\") . DIRECTORY_SEPARATOR),
            ];
        }
    }

    if ($matches) {
        // Prefer inside-APP_PATH first; if tie, keep original order (default before fallbacks)
        usort($matches, function ($a, $b) {
            return ($b['inside'] <=> $a['inside']); // true (1) before false (0)
        });
        $pickedAbs = $matches[0]['abs'];
        $pickedRel = $matches[0]['rel'];
        $pickedSrc = $matches[0]['src'];
    } else {
        // unresolved fallback
        $abs = resolve_abs_path(APP_PATH, $spec['default']);
        $pickedAbs = normalize_dir($abs);
        $pickedRel = normalize_dir($spec['default']);
        $pickedSrc = 'unresolved';
        if (!empty($spec['required'])) {
            $errors['INVALID_PATHS'][] = "Missing required path: [$key] → {$spec['default']}";
        }
    }

    $APP_BASE[$key] = $pickedAbs;
    $APP_BASE_REL[$key] = $pickedRel;
    $APP_BASE_SRC[$key] = $pickedSrc;
}

defined('APP_BASE') || define('APP_BASE', $APP_BASE);
defined('APP_BASE_REL') || define('APP_BASE_REL', $APP_BASE_REL);
defined('APP_BASE_SRC') || define('APP_BASE_SRC', $APP_BASE_SRC);

const APP_PATH_PROJECTS = '../projects'; // (or an absolute like /mnt/c/projects)
const APP_PATH_CLIENTS = '../clients';

// var_dump('default', $PATH_SPEC['projects']['default'], 'picked', $APP_BASE['projects'], 'src', $APP_BASE_SRC['projects']);

// ---- ergonomic accessor ----------------------------------------------------

if (!function_exists('base_path')) {
    /**
     * base_path('clients', true, 'acme') → ".../clients/acme/"
     */
    function base_path(string $key, bool $trail = false, string ...$segments): string
    {
        if (!defined('APP_BASE') || empty(APP_BASE[$key])) {
            throw new RuntimeException("Unknown base path key: $key");
        }
        $abs = APP_BASE[$key];
        $full = $segments ? join_path($abs, ...$segments) : rtrim($abs, DIRECTORY_SEPARATOR);
        return $trail ? normalize_dir($full) : rtrim($full, DIRECTORY_SEPARATOR);
    }
}


// if (!empty($errors)) {} // dd($errors); or handle appropriately
