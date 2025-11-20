<?php
// api/tasks.php
declare(strict_types=1);

header('X-Content-Type-Options: nosniff');

// Input (GET or POST)
$taskName = (string) ($_GET['task'] ?? $_POST['task'] ?? 'startup');
$step = isset($_GET['step']) || isset($_POST['step'])
    ? (int) ($_GET['step'] ?? $_POST['step'])
    : 0; // default: first job
$format = (string) ($_GET['format'] ?? $_POST['format'] ?? 'json');

// --- Define tasks as sequences of jobs ---
$tasks = [

    'onetask' => [
        'steps' => [
            static function (): string {
                sleep(1);
                return "This is one task job 1... Done.\n";
            },
        ],
    ],

    // Example startup pipeline with 5 jobs
    'startup' => [
        'steps' => [
            static function (): string {
                // e.g. check PHP version, env, etc.
                sleep(1);
                return "Startup job 1: basic checks... Done.\n";
            },
            static function (): string {
                // e.g. check composer.json, vendor, etc.
                sleep(1);
                return "Startup job 2: composer checks... Done.\n";
            },
            static function (): string {
                // e.g. check git status
                sleep(1);
                return "Startup job 3: git status... Done.\n";
            },
            static function (): string {
                // e.g. ping socket server
                sleep(1);
                return "Startup job 4: socket ping... Done.\n";
            },
            static function (): string {
                // e.g. check npm/node
                return "Testing:\n Startup job 5: npm checks... Done.\n";
            },
        ],
    ],

    // Example: downloading assets in multiple stages
    'download-assets' => [
        'steps' => [
            static function (): string {
                return "Job 1: Downloading jQuery library...\nDone.\n";
            },
            static function (): string {
                return "Job 2: Downloading Ace editor library...\nDone.\n";
            },
            static function (): string {
                return "Job 3: Downloading CSS/utility assets...\nDone.\n";
            },
        ],
    ],

];

if (!isset($tasks[$taskName])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Unknown task: {$taskName}\n";
    exit;
}

$steps = $tasks[$taskName]['steps'];
$total = count($steps);

if ($step < 0 || $step >= $total) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Invalid step {$step} for task {$taskName} (total {$total}).\n";
    exit;
}

// --- Run exactly ONE job ---
$fn = $steps[$step];
$start = microtime(true);
$output = $fn();
$durationMs = (microtime(true) - $start) * 1000.0;

$nextStep = ($step + 1 < $total) ? $step + 1 : null;
$done = ($nextStep === null);

// You can still support text, but JSON is easier for the JS to work with.
if ($format === 'text') {
    header('Content-Type: text/plain; charset=utf-8');

    echo "Job " . ($step + 1) . " / {$total}\n";
    echo rtrim($output, "\n") . "\n";
    echo "[done] step {$step} (" . round($durationMs, 2) . " ms)\n";
    if ($done) {
        echo "=== Task {$taskName} completed. ===\n";
    } else {
        echo "=== Next step: {$nextStep} / {$total} ===\n";
    }
    exit;
}

// JSON mode
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'ok' => true,
    'task' => $taskName,
    'step' => $step,
    'total_steps' => $total,
    'output' => $output,
    'duration_ms' => round($durationMs, 2),
    'done' => $done,
    'next_step' => $nextStep,
]);