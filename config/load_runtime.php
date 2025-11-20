<?php
// config/load_runtime.php

// use CodeInSync\Core\Registry;
if (!class_exists(\CodeInSync\Core\Registry::class)) {
    require APP_PATH . 'src/Core/Registry.php';
    @class_alias(\CodeInSync\Core\Registry::class, 'Registry');
}

// Each "run_*" could be used in `app/console.php` or anywhere exec is needed
Registry::set('interpreter.run.php', function ($code) {
    return shell_exec("php -r " . escapeshellarg($code));
});

Registry::set('interpreter.run.python3', function ($code) {
    return shell_exec("python3 -c " . escapeshellarg($code));
});

Registry::set('interpreter.run.perl', function ($code) {
    return shell_exec("perl -e " . escapeshellarg($code));
});

Registry::set('interpreter.run.ruby', function ($code) {
    return shell_exec("ruby -e " . escapeshellarg($code));
});

Registry::set('interpreter.run.node', function ($code) {
    return shell_exec("node -e " . escapeshellarg($code));
});
