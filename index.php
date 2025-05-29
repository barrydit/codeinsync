<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'constants.php';

if (/*APP_SELF === APP_PATH_PUBLIC*/ dirname(APP_SELF) === dirname(APP_PATH_PUBLIC)) { ///   /mnt/www
    function getSortedUiAndAppPaths(): array
    {
        $paths = [];

        // Get app paths
        $appPaths = array_filter(glob(APP_BASE['public'] . 'app.*.php'), 'is_file');

        usort($appPaths, function ($a, $b) {
            $priority = [
                'app.install.php',
                'app.debug.php',
                'app.directory.php',
                'app.project.php',
                'app.timesheet.php',
                'app.browser.php',
                'app.github.php',
                'app.packagist.php',
                'app.whiteboard.php',
                'app.notes.php',
                'app.pong.php',
                'app.console.php',
            ];

            $aBase = basename($a);
            $bBase = basename($b);
            $aIndex = array_search($aBase, $priority);
            $bIndex = array_search($bBase, $priority);

            $aIndex = $aIndex === false ? PHP_INT_MAX : $aIndex;
            $bIndex = $bIndex === false ? PHP_INT_MAX : $bIndex;

            return $aIndex <=> $bIndex ?: strcmp($aBase, $bBase);
        });

        // Remove app.install.php if present
        foreach ($appPaths as $key => $file) {
            if (basename($file) === 'app.install.php') {
                unset($appPaths[$key]);
            }
        }

        // Get ui paths
        $uiPaths = array_filter(glob(APP_BASE['public'] . 'ui.*.php', GLOB_BRACE));

        usort($uiPaths, function ($a, $b) {
            $order = [
                'ui.calendar.php' => -10,
                'ui.nodes.php' => -9,
                'ui.php.php' => -8,
                'ui.composer.php' => 10,
                'ui.npm.php' => 11,
                'ui.ace_editor.php' => 12,
                'ui.git.php' => 13,
                'ui.notes.php' => 14,
            ];

            $aBase = basename($a);
            $bBase = basename($b);

            $aRank = $order[$aBase] ?? 0;
            $bRank = $order[$bBase] ?? 0;

            return $aRank <=> $bRank ?: strcmp($aBase, $bBase);
        });

        $paths = array_values(array_unique(array_merge($uiPaths, $appPaths)));


        return $paths;
    }

    function loadAppsFromPaths(array $paths): array
    {
        $paths = array_filter($paths, fn($path) => is_file($path) && preg_match('/^(ui|app)\.([\w\-]+)\.php$/', basename($path)));

        $apps = [];

        foreach ($paths as $path) {
            if ($realpath = realpath($path)) {
                // Clean out $app before loading
                //$GLOBALS['app'] = [];

                require_once $realpath;

                // ob_start(); $output = ob_get_clean(); ob_end_flush();

                $filename = basename($realpath);
                if (preg_match('/^(ui|app)\.([\w\-]+)\.php$/', $filename, $matches)) {
                    //dd($matches, false);
                    $type = $matches[1]; // 'ui' or 'app'
                    $name = $matches[2]; // e.g., 'notes'

                    $apps[$name] = [
                        'type' => $type,
                        'style' => $app['style'] ?? '',
                        'body' => $app['body'] ?? '',
                        'script' => $app['script'] ?? '',
                    ];
                }
            }
            $app = []; // Clear the app variable to avoid conflicts
        }

        return $apps;
    }

    define('UI_APPS', loadAppsFromPaths(getSortedUiAndAppPaths()));

}