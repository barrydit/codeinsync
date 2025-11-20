<?php
// bootstrap/coverage-report.php

final class CoverageReport
{
    public static function generate(array $files = null): array
    {
        // Use get_required_files() by default
        $files = $files ?: get_required_files();

        // Normalize & de-dupe
        $files = array_values(array_unique(array_map('strval', $files)));

        // 1) LOC counters
        $totals = [
            'files' => 0,
            'physical_loc' => 0,   // all lines
            'logical_loc' => 0,   // non-blank, non-comment
            'executable_lines' => 0,   // coverage-known executable lines
            'executed_lines' => 0,   // actually executed
            'percent_executed' => 0.0,
            'driver' => defined('APP_COVERAGE_DRIVER') ? APP_COVERAGE_DRIVER : 'none',
        ];

        $perFile = [];

        // 2) Pull coverage map if available
        $coverageMap = []; // [file => [line => status]]
        if (defined('APP_COVERAGE_DRIVER')) {
            if (APP_COVERAGE_DRIVER === 'xdebug' && function_exists('xdebug_get_code_coverage')) {
                $coverageMap = xdebug_get_code_coverage();
            } elseif (APP_COVERAGE_DRIVER === 'pcov' && function_exists('pcov\collect')) {
                // PCOV returns a different structure; normalize to xdebug-like
                $raw = \pcov\collect(\pcov\inclusive, $files);
                foreach ($raw as $file => $lines) {
                    // $lines: [lineNumber => hits]
                    $coverageMap[$file] = $lines;
                }
            }
        }

        foreach ($files as $file) {
            if (!is_file($file) || !is_readable($file)) {
                continue;
            }

            $physical = self::countPhysicalLoc($file);
            $logical = self::countLogicalLoc($file);

            // Executable/executed from coverage
            $execLines = 0;
            $hitLines = 0;

            if (!empty($coverageMap[$file]) && is_array($coverageMap[$file])) {
                foreach ($coverageMap[$file] as $lineNo => $hitsOrStatus) {
                    // Xdebug: status is -1 (not executable), 0 (not executed), >0 (executed count)
                    // PCOV:   value is hits (0 = not executed, >0 executed). Non-executable lines typically absent.
                    if ($hitsOrStatus === -1) {
                        continue; // not executable
                    }
                    $execLines++;
                    if ($hitsOrStatus > 0) {
                        $hitLines++;
                    }
                }
            }

            $perFile[$file] = [
                'physical_loc' => $physical,
                'logical_loc' => $logical,
                'executable_lines' => $execLines,
                'executed_lines' => $hitLines,
                'percent_executed' => $execLines > 0 ? round(($hitLines / $execLines) * 100, 2) : null,
            ];

            $totals['files']++;
            $totals['physical_loc'] += $physical;
            $totals['logical_loc'] += $logical;
            $totals['executable_lines'] += $execLines;
            $totals['executed_lines'] += $hitLines;
        }

        $totals['percent_executed'] = $totals['executable_lines'] > 0
            ? round(($totals['executed_lines'] / $totals['executable_lines']) * 100, 2)
            : null;

        return ['totals' => $totals, 'files' => $perFile];
    }

    public static function print(array $report): void
    {
        $t = $report['totals'];
        $driver = strtoupper((string) $t['driver']);
        echo "\n=== Coverage Report ({$driver}) ===\n";
        echo "Files:                {$t['files']}\n";
        echo "Physical LOC:         {$t['physical_loc']}\n";
        echo "Logical LOC:          {$t['logical_loc']}\n";
        echo "Executable lines:     {$t['executable_lines']}\n";
        echo "Executed lines:       {$t['executed_lines']}\n";
        echo "Executed %:           " . ($t['percent_executed'] !== null ? $t['percent_executed'] . '%' : 'n/a (no driver)') . "\n";

        echo "\n-- Per file --\n";
        foreach ($report['files'] as $file => $d) {
            $pct = $d['percent_executed'] !== null ? $d['percent_executed'] . '%' : 'n/a';
            echo basename($file) . "\n";
            echo "  physical: {$d['physical_loc']}  logical: {$d['logical_loc']}  exec: {$d['executable_lines']}  hit: {$d['executed_lines']}  {$pct}\n";
        }
        echo "=============================\n";
    }

    private static function countPhysicalLoc(string $file): int
    {
        $lines = @file($file, FILE_IGNORE_NEW_LINES);
        return is_array($lines) ? count($lines) : 0;
    }

    private static function countLogicalLoc(string $file): int
    {
        // Count non-blank, non-comment lines via tokenization for better signal
        $code = @file_get_contents($file);
        if ($code === false)
            return 0;

        $tokens = token_get_all($code);
        $lineHasCode = [];

        $line = 1;
        foreach ($tokens as $tok) {
            if (is_array($tok)) {
                [$id, $text, $tokLine] = $tok;
                $line = $tokLine;

                // Skip comments & whitespace
                if (in_array($id, [T_COMMENT, T_DOC_COMMENT, T_WHITESPACE], true)) {
                    continue;
                }
                // Anything else counts as “logical” content on that line
                $lineHasCode[$line] = true;
            } else {
                // Single-char tokens like ; { } = … : still count toward code on current $line
                $lineHasCode[$line] = true;
            }
        }

        return count($lineHasCode);
    }
}