<?php
declare(strict_types=1);
// config/constants.paths.php

//defined('APP_PATH') or define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
//define('CONFIG_PATH', APP_PATH . 'config' . DIRECTORY_SEPARATOR);
//define('BOOTSTRAP_PATH', APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR);
//define('PATH_PUBLIC', APP_PATH . 'public' . DIRECTORY_SEPARATOR);

defined('APP_PATH') or die('APP_PATH must be defined before constants.paths.php');

defined('PATH_PUBLIC') || define(
    'PATH_PUBLIC',
    APP_PATH . 'public' . DIRECTORY_SEPARATOR
);

/**
 * Normalize a filesystem path:
 *  - If relative, make it relative to APP_PATH
 *  - Collapse .. and .
 *  - Ensure trailing DIRECTORY_SEPARATOR for directories
 */
$norm = static function (string $p, bool $ensureDir = true): string {
    // Expand relative to APP_PATH if needed
    if (!preg_match('~^([/\\\\]|[A-Za-z]:[/\\\\])~', $p)) {
        $p = rtrim(APP_PATH, "/\\") . DIRECTORY_SEPARATOR . $p;
    }
    $rp = realpath($p);
    if ($rp === false) {
        // Keep original if not existing yet; still normalize separators
        $rp = preg_replace('~[/\\\\]+~', DIRECTORY_SEPARATOR, $p);
    }
    if ($ensureDir && substr($rp, -1) !== DIRECTORY_SEPARATOR) {
        $rp .= DIRECTORY_SEPARATOR;
    }
    return $rp;
};

/**
 * Return the first existing match of $candidate under any $roots.
 * If none exist, return null.
 */
$firstExisting = static function (array $roots, string $candidate) use ($norm): ?string {
    foreach ($roots as $root) {
        $path = $norm($root, true) . $candidate;
        $path = $norm($path, true);
        if (is_dir($path)) {
            return $path;
        }
    }
    return null;
};

/**
 * Given a spec with default dir + fallbacks, search roots for the first that exists.
 */
$resolveDir = static function (array $allRoots, array $spec) use ($firstExisting): ?string {
    $rootsToUse = $allRoots;
    if (($spec['anchor'] ?? null) === 'app_root') {
        $rootsToUse = [APP_PATH]; // force search under app root only
    }

    $candidates = [];
    if (!empty($spec['default']))
        $candidates[] = rtrim($spec['default'], "/\\") . DIRECTORY_SEPARATOR;
    foreach ($spec['fallbacks'] ?? [] as $fb) {
        $candidates[] = rtrim($fb, "/\\") . DIRECTORY_SEPARATOR;
    }

    foreach ($candidates as $c) {
        if ($found = $firstExisting($rootsToUse, $c)) {
            return $found;
        }
    }
    return null;
};

// 1) Build the roots list from ENV + add your built-in roots
// 1) Build the roots list from ENV + add built-ins
$envRoots = [];
if (!empty($_ENV['APP_BASE']) && is_array($_ENV['APP_BASE'])) {
    foreach ($_ENV['APP_BASE'] as $r) {
        if (is_string($r) && $r !== '')
            $envRoots[] = $r;
    }
}

// Built-in roots
$builtinRoots = [
    APP_PATH . 'projects/clients',
    APP_PATH . 'projects/internal',
    APP_PATH . 'projects',
];

// IMPORTANT: APP_PATH must be first
$roots = [];
$seen = [];

// Prepend APP_PATH explicitly
foreach ([APP_PATH, ...$envRoots, ...$builtinRoots] as $r) {
    $key = rtrim(strtolower($r), "/\\");
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $roots[] = $r;
    }
}

// 2) Define your directory spec (logical keys -> default+fallback relative names)
$PATH_SPEC = [
    'app' => ['default' => 'app', 'fallbacks' => [], 'anchor' => 'app_root'],
    'config' => ['default' => 'config', 'fallbacks' => [], 'anchor' => 'app_root'],
    'data' => ['default' => 'data', 'fallbacks' => [], 'anchor' => 'app_root'],
    'public' => ['default' => 'public', 'fallbacks' => ['htdocs', 'www'], 'anchor' => 'app_root'],
    'resources' => ['default' => 'resources', 'fallbacks' => [], 'anchor' => 'app_root'],
    'src' => ['default' => 'src', 'fallbacks' => [], 'anchor' => 'app_root'],
    'var' => ['default' => 'var', 'fallbacks' => [], 'anchor' => 'app_root'],
    'vendor' => ['default' => 'vendor', 'fallbacks' => [], 'anchor' => 'app_root'],
    'node' => ['default' => 'node_modules', 'fallbacks' => [], 'anchor' => 'app_root'],

    // Projects:
    'clients' => ['default' => '../clients', 'fallbacks' => ['clients']],
    'projects' => ['default' => '../projects', 'fallbacks' => ['projects']],
];

// 3) Resolve each logical directory across the roots
$APP_BASE = [];
foreach ($PATH_SPEC as $key => $spec) {
    $found = $resolveDir($roots, $spec);
    if ($found !== null) {
        $APP_BASE[$key] = $found; // absolute path with trailing slash
    }
}

// 4) Define PATH_* convenience constants where available
foreach ($APP_BASE as $key => $abs) {
    $const = 'PATH_' . strtoupper($key);
    if (!defined($const)) {
        define($const, $abs);
    }
}

// 5) Optionally expose the resolved map (if you prefer using constants, skip this)
// 5) Expose resolved maps (absolute + relative) and value-only lists
$APP_PATH_NORM = rtrim(str_replace('\\', '/', APP_PATH), '/') . '/';
$APP_BASE_REL = [];

foreach ($APP_BASE as $k => $abs) {
    $absNorm = str_replace('\\', '/', $abs);
    if (stripos($absNorm, $APP_PATH_NORM) === 0) {
        $APP_BASE_REL[$k] = substr($absNorm, strlen($APP_PATH_NORM)); // keep trailing slash
    } else {
        $APP_BASE_REL[$k] = $abs; // or set null if you want only-rel
    }
}

defined('APP_BASE_MAP_ABS') || define('APP_BASE_MAP_ABS', json_encode($APP_BASE, JSON_UNESCAPED_SLASHES));
defined('APP_BASE_MAP_REL') || define('APP_BASE_MAP_REL', json_encode($APP_BASE_REL, JSON_UNESCAPED_SLASHES));

$OUTPUT_ORDER = ['app', 'config', 'data', 'public', 'resources', 'src', 'var', 'vendor', 'node', 'clients', 'projects'];

$dirsOrdered = [];
foreach ($OUTPUT_ORDER as $k) {
    if (isset($APP_BASE[$k]))
        $dirsOrdered[] = $APP_BASE[$k];
}

defined('APP_DIRS_ABS') || define('APP_DIRS_ABS', json_encode($dirsOrdered, JSON_UNESCAPED_SLASHES));
defined('APP_DIRS_REL') || define('APP_DIRS_REL', json_encode(array_values($APP_BASE_REL), JSON_UNESCAPED_SLASHES));

// 6) (Optional) Add select dirs to include_path for legacy require() code
$includePaths = [];
foreach (['app', 'config', 'src', 'vendor'] as $k) {
    if (isset($APP_BASE[$k]))
        $includePaths[] = rtrim($APP_BASE[$k], "/\\");
}
if ($includePaths) {
    set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $includePaths));
}

// 7) If you want a helper to fetch later:
if (!function_exists('app_base')) {
    /**
     * @param 'abs'|'rel' $mode
     */
    function app_base(string $key, ?string $suffix = null, string $mode = 'abs'): ?string
    {
        static $maps = ['abs' => null, 'rel' => null];

        if ($maps['abs'] === null)
            $maps['abs'] = json_decode(APP_BASE_MAP_ABS ?? '[]', true) ?: [];
        if ($maps['rel'] === null)
            $maps['rel'] = json_decode(APP_BASE_MAP_REL ?? '[]', true) ?: [];

        $map = $maps[$mode] ?? $maps['abs'];
        if (!isset($map[$key]) || $map[$key] === null)
            return null;

        return $suffix ? rtrim($map[$key], "/\\") . '/' . ltrim($suffix, "/\\") : $map[$key];
    }
}

if (!function_exists('app_dirs')) {
    /**
     * Get the list of directories (values only).
     * @param 'abs'|'rel' $mode
     * @return array<int,string>
     */
    function app_dirs(string $mode = 'abs'): array
    {
        if ($mode === 'rel') {
            return json_decode(APP_DIRS_REL ?? '[]', true) ?: [];
        }
        return json_decode(APP_DIRS_ABS ?? '[]', true) ?: [];
    }
}