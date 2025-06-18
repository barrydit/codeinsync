<?php

!defined('APP_PATH') and
    define('APP_PATH', realpath(__DIR__ /*. '..' . DIRECTORY_SEPARATOR*/) . DIRECTORY_SEPARATOR);

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    //if (isset($_POST['php']) || preg_match('/^php\s*(:?.*)/i', $_POST['cmd'], $match))
    require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'php.php';
    if (isset($_POST['composer']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^composer\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'composer.php';
    if (isset($_POST['git']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^git\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'git.php';
    if (isset($_POST['npm']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^npm\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'npm.php';
    if (isset($_POST['python']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^python\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'python.php';
    if (isset($_POST['perl']) || isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^perl\s*(:?.*)/i', $_POST['cmd'], $match))
        require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'perl.php';
    /* if (preg_match('/^ruby\s*(:?.*)/i', $_POST['cmd'], $match))
       require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'ruby.php';
     if (preg_match('/^go\s*(:?.*)/i', $_POST['cmd'], $match))
       require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'go.php';
     if (preg_match('/^java\s*(:?.*)/i', $_POST['cmd'], $match))
       require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'java.php';
     if (preg_match('/^csharp\s*(:?.*)/i', $_POST['cmd'], $match))
       require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'csharp.php'; */
    //require_once 'config' . DIRECTORY_SEPARATOR . 'javascript.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'ruby.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'go.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'java.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'csharp.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'rust.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'php.php'; // PHP config
    //require_once 'config' . DIRECTORY_SEPARATOR . 'nodejs.php'; // Node.js config
    //require_once 'config' . DIRECTORY_SEPARATOR . 'composer.php'; // Composer config
    //require_once 'config' . DIRECTORY_SEPARATOR . 'autoload.php'; // Autoload configuration
    //require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'; // Vendor autoload
    //require_once 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'perl.php';
    //require_once 'config' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'python.php';
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

if (/*APP_SELF === PATH_PUBLIC*/ dirname(APP_SELF) === dirname(PATH_PUBLIC)) { ///   /mnt/www
    function getSortedUiAndAppPaths(): array
    {
        $paths = [];

        // Desired load order for app logic (basename without extension)
        $priority = [
            //'install',
            //'debug',
            'directory',
            'packagist',
            'project',
            'timesheet',
            'browser',
            'github',
            'whiteboard',
            'notes',
            //'pong',
            'console',
        ];

        // Get app files (e.g., directory.php)
        $appPaths = array_filter(glob(APP_BASE['app'] . '*.php'), 'is_file');

        usort($appPaths, function ($a, $b) use ($priority) {
            $aBase = basename($a, '.php');
            $bBase = basename($b, '.php');

            $aIndex = array_search($aBase, $priority);
            $bIndex = array_search($bBase, $priority);

            $aIndex = $aIndex === false ? PHP_INT_MAX : $aIndex;
            $bIndex = $bIndex === false ? PHP_INT_MAX : $bIndex;

            return $aIndex <=> $bIndex ?: strcmp($aBase, $bBase);
        });

        // Get UI paths (e.g., ui.directory.php)
        $uiPaths = array_filter(glob(APP_BASE['app'] . 'ui.*.php'));

        usort($uiPaths, function ($a, $b) use ($priority) {
            $aKey = preg_replace('/^ui\.(.+)\.php$/', '$1', basename($a));
            $bKey = preg_replace('/^ui\.(.+)\.php$/', '$1', basename($b));

            $aIndex = array_search($aKey, $priority);
            $bIndex = array_search($bKey, $priority);

            $aIndex = $aIndex === false ? PHP_INT_MAX : $aIndex;
            $bIndex = $bIndex === false ? PHP_INT_MAX : $bIndex;

            return $aIndex <=> $bIndex ?: strcmp($aKey, $bKey);
        });

        // Merge them in order: app logic first, then UI overlays
        $paths = array_merge($appPaths, $uiPaths);

        return $paths;
    }
    function loadAppsFromPaths(array $paths): array
    {
        // Only include files like: 'notes.php' or 'ui.notes.php'
        $paths = array_filter($paths, function ($path) {
            $filename = basename($path);
            return is_file($path) && preg_match('/^(ui\.)?([\w\-]+)\.php$/', $filename);
        });

        $apps = [];

        foreach ($paths as $path) {
            if ($realpath = realpath($path)) {
                require_once $realpath;

                $filename = basename($realpath);
                if (preg_match('/^(ui\.)?([\w\-]+)\.php$/', $filename, $matches)) {
                    $type = $matches[1] === 'ui.' ? 'ui' : 'app';
                    $name = $matches[2];

                    $apps[$name] ??= ['type' => 'app', 'style' => '', 'body' => '', 'script' => ''];

                    if ($type === 'ui') {
                        $apps[$name]['type'] = 'ui'; // optional override
                    }

                    $apps[$name]['style'] .= $app['style'] ?? '';
                    $apps[$name]['body'] .= $app['body'] ?? '';
                    $apps[$name]['script'] .= $app['script'] ?? '';
                }
            }

            // Clear $app to prevent cross contamination between files
            $app = [];
        }

        return $apps;
    }

    define('UI_APPS', loadAppsFromPaths(getSortedUiAndAppPaths()));
}