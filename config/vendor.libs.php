<?php
// config/vendor.libs.php

return [
    'jquery' => [
        'version' => '3.7.1',
        'strategy' => 'local',
        'required_files' => [
            'js/jquery.min.js',
            'js/jquery-migrate.min.js', // if you have it
        ],
    ],

    'jquery-ui' => [
        'version' => '1.13.2',
        'strategy' => 'local',
        'required_files' => [
            'js/jquery-ui.min.js',
            'css/jquery-ui.min.css',
        ],
    ],

    'bootstrap' => [
        'version' => '5.3.3',
        'strategy' => 'local',
        'required_files' => [
            'css/bootstrap.min.css',
            'js/bootstrap.bundle.min.js',
        ],
    ],

    'tinymce' => [
        'version' => '5.10.9',
        'strategy' => 'local',
        'required_files' => [
            'tinymce.min.js',
            'themes/silver/theme.min.js',
            'skins/ui/oxide/skin.min.css',
            'skins/content/default/content.min.css',
        ],
    ],
    /*
        'tinymce' => [
            'version' => '8.2.2',
            'strategy' => 'local',
            'required_files' => [
                'tinymce.min.js',

                // skin + theme
                'skins/ui/oxide/skin.min.css',
                'skins/content/default/content.min.css',
                'themes/silver/theme.min.js',

                // icons
                'icons/default/icons.min.js',

                // minimum plugin check (optional)
                'plugins/link/plugin.min.js',
                'plugins/table/plugin.min.js',
            ],
        ],
    */
    'tailwindcss' => [
        'version' => '3.3.5',
        'strategy' => 'local',
        'required_files' => [
            'js/tailwindcss-3.3.5.js',
        ],
    ],

    'modernizr' => [
        'version' => '3.6.0',
        'strategy' => 'local',
        'required_files' => [
            'js/modernizr-custom.js', // or whatever file you actually have
        ],
    ],

    'prototype' => [
        'version' => '1.7.3',
        'strategy' => 'local',
        'required_files' => [
            'js/prototype.js',
        ],
    ],

    'ace' => [
        'version' => '1.32.9',
        'strategy' => 'git', // or 'local' if you’ve copied a build here
        'repo' => 'https://github.com/ajaxorg/ace-builds.git',
        // 'required_files' => ['src-min-noconflict/ace.js'], // optional
    ],

    'webshim' => [
        'version' => '1.16.0',
        'strategy' => 'local',
        'required_files' => [
            'polyfiller.js', // inside webshim/1.16.0/
        ],
    ],

    'polyfills' => [
        'strategy' => 'local',
        'version' => '1.0.0', // purely logical, not used in path
        'required_files' => [
            // NOTE: asset resolver must special-case polyfills (no version subdir)
            'css/html5-simple-date-input-polyfill.css',
            'js/html5-simple-date-input-polyfill.min.js',
        ],
    ],
];
