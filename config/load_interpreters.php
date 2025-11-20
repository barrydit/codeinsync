<?php

// use CodeInSync\Core\Registry;
if (!class_exists(\CodeInSync\Core\Registry::class)) {
    require APP_PATH . 'src/Core/Registry.php';
    @class_alias(\CodeInSync\Core\Registry::class, 'Registry');
}

$names = ['php', 'python3', 'perl', 'ruby', 'node'];
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

foreach ($names as $name) {
    $bin = null;

    // 1. Locate binary cross-platform
    $bin = $isWindows ? trim(shell_exec("where $name") ?? '') : trim(shell_exec("command -v $name") ?? '');

    // Normalize first result only (if multiple paths are returned)
    $bin = strtok($bin, "\r\n");

    // 2. Attempt to get version
    $version = null;
    if (!empty($bin)) {
        $version = match ($name) {
            'php' => trim(shell_exec("$bin -r \"echo PHP_VERSION;\"")),
            'python3' => trim(shell_exec("$bin --version 2>&1")),
            'perl' => trim(shell_exec("$bin -e \"print \$^V\"")),
            'ruby' => trim(shell_exec("$bin -v")),
            'node' => trim(shell_exec("$bin -v")),
        };
    }

    // 3. Register to Registry
    Registry::set("interpreter.meta.$name", [
        'bin' => $bin ?: null,
        'version' => $version ?: null,
        'available' => !empty($bin),
    ]);
}

$php = Registry::get('interpreter.meta.php');
//dd($php);
if (!$php['available'] || version_compare($php['version'], '8.0.0', '<')) {
    echo "Your PHP version is too old.";
}
