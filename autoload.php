<?php

// --- Optional: App-level PSR-4 for your own code (if not using Composer for it)
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $len = strlen($prefix);
    if (strncmp($class, $prefix, $len) !== 0)
        return;

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $len)) . '.php';
    $file = APP_PATH . 'src/' . $relative;
    if (is_file($file))
        require $file;
}, true, true);

// --- Optional: register error/exception handlers, DI container, etc.
// Example (only if you have these already):
// App\Core\Shutdown::register();
// App\Core\Logger::boot();
