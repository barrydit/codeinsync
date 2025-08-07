<?php
/**
 * trace.php - A minimal runtime code trace utility for debugging.
 *
 * Usage:
 *     trace_execution_block();
 */

if (!function_exists('trace_execution_block')) {
    function trace_execution_block(int $offset = 0, int $context = 5): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1 + $offset)[0];

        $file = $trace['file'] ?? '[unknown file]';
        $line = $trace['line'] ?? 0;

        echo "\n>>> Executing: {$file} @ line {$line}\n\n";

        if (!is_file($file)) {
            echo "(file not found)\n";
            return;
        }

        $lines = file($file);
        $total = count($lines);

        $start = max($line - $context - 1, 0);
        $end = min($line + $context - 1, $total - 1);

        for ($i = $start; $i <= $end; $i++) {
            $prefix = ($i + 1 === $line) ? '>>>' : '   ';
            echo sprintf("%s %5d | %s", $prefix, $i + 1, $lines[$i]);
        }

        echo "\n---\n";
    }
}
