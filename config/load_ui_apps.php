<?php

if (/*APP_SELF === PATH_PUBLIC*/ dirname(APP_SELF) === dirname(PATH_PUBLIC)) { //  /mnt/www
    /**
     * Get sorted paths for UI and app files.
     *
     * @return array Sorted paths.
     */
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
        $appPaths = array_filter(glob(APP_PATH . 'app' . DIRECTORY_SEPARATOR . '*.php'), 'is_file');

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
        $uiPaths = array_filter(glob(APP_PATH . 'app' . DIRECTORY_SEPARATOR . 'ui.*.php'));

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

    /**
     * Load apps from the given paths.
     *
     * @param array $paths Paths to load apps from.
     * @return array Loaded apps.
     */
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


    if (defined('APP_CONTEXT') && APP_CONTEXT === 'www') {

        define('UI_APPS', loadAppsFromPaths(getSortedUiAndAppPaths()));

    }

    //dd(loadAppsFromPaths(getSortedUiAndAppPaths())); // []
}
else {
    // If not in public context, define UI_APPS as empty array
    define('UI_APPS', []);
}
