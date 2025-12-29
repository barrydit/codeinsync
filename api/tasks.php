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
                $dir = rtrim(app_base('public', null, 'abs') . 'assets/vendor/jquery', '/\\') . DIRECTORY_SEPARATOR;
                if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                    $errors['JQUERY-DIR'] = "Failed to create directory: {$dir}";
                    return "Job 2: Downloading Tailwind CSS library... Failed (dir).\n";
                }

                $jqueryJS = @file_get_contents('https://code.jquery.com/jquery-3.7.1.min.js'); // jquery-3.7.1.min.js
                //https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js -> jquery-easing.min.js
                //https://code.jquery.com/ui/1.12.1/jquery-ui.min.js -> jquery-ui-1.12.1.js
                //https://d3js.org/d3.v4.min.js
                if ($jqueryJS === false) {
                    return "Job 1: Failed to download jQuery library.\n";
                } else {
                    file_put_contents(app_base('public', null, 'abs') . 'assets/vendor/jquery/3.7.1/jquery-3.7.1.min.js', $jqueryJS);
                }
                sleep(1);
                return "Job 1: Downloading jQuery library... Done.\n";
            },
            static function (): string {
                $dir = rtrim(app_base('public', null, 'abs') . 'assets/vendor/tailwindcss', '/\\') . DIRECTORY_SEPARATOR;

                $baseUrl = 'https://cdn.tailwindcss.com';

                // Fetch CDN content and also learn final redirect URL (contains version)
                $ch = curl_init($baseUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_USERAGENT => 'CodeInSync/1.0 (tailwind asset fetch)',
                ]);

                $js = curl_exec($ch);
                $curlErr = curl_error($ch);
                $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $baseUrl;
                $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                curl_close($ch);

                if ($js === false || $httpCode < 200 || $httpCode >= 300 || trim((string) $js) === '') {
                    $errors['JS-TAILWIND'] = $curlErr ?: "HTTP {$httpCode} from {$effectiveUrl}";
                    return "Job 2: Downloading Tailwind CSS library... Failed.\n";
                }

                // Extract version from effective URL like .../3.4.17
                $version = null;
                if (preg_match('~/(\\d+\\.\\d+\\.\\d+)(?:/)?$~', (string) $effectiveUrl, $m)) {
                    $version = $m[1];
                }

                $dir = $dir . ($version ?: 'latest') . DIRECTORY_SEPARATOR;

                if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                    $errors['TAILWIND-DIR'] = "Failed to create directory: {$dir}";
                    return "Job 2: Downloading Tailwind CSS library... Failed (dir).\n";
                }

                // Fallback name if we can't detect version for some reason
                $file = $dir . 'tailwindcss-' . ($version ?: 'latest') . '.min.js';

                // Refresh window (days)
                $refreshDays = 5;

                // If file exists and is still fresh, just return it
                if (is_file($file)) {
                    $ageSeconds = time() - (int) @filemtime($file);
                    if ($ageSeconds >= 0 && $ageSeconds < ($refreshDays * 86400)) {
                        return $file;
                    }
                }

                // Write/update file
                if (file_put_contents($file, $js) === false) {
                    $errors['JS-TAILWIND'] = "Failed to write: {$file}";
                    return "Job 2: Downloading Tailwind CSS library... Failed (write).\n";
                }

                // Optional: keep only the newest version file (delete older ones)
                foreach (glob("{$dir}tailwindcss-*.min.js") ?: [] as $old) {
                    if ($old !== $file) {
                        // @unlink($old);
                    }
                }

                sleep(1);
                return "Job 2: Downloading Tailwind CSS library... Done ({$effectiveUrl}).\n";
            },
            static function (): string {

                if (defined('GIT_EXEC')) {
                    // Filesystem path to the ACE vendor directory
                    $path = APP_BASE['public'] . 'assets/vendor/ace';

                    // 1) Ensure the directory exists (or can be created)
                    if (!is_dir($path)) {
                        if (!mkdir($path, 0755, true) && !is_dir($path)) {
                            // Failed to create target directory – don't try to clone
                            $errors['GIT-CLONE-ACE'] = 'public/assets/vendor/ace does not exist and could not be created.';
                            // return; // "Job 1: Failed to download ACE library.\n";
                        }
                    }

                    // 2) Only clone if the directory is empty
                    //    (glob() needs a pattern, so include "/*")
                    $isEmpty = empty(glob($path . DIRECTORY_SEPARATOR . '*'));

                    if ($isEmpty) {
                        $cmd = (stripos(PHP_OS, 'WIN') === 0 ? '' : (defined('APP_SUDO') ? APP_SUDO : ''));
                        $cmd .= GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git public/assets/vendor/ace';

                        $output = [];
                        $returnCode = 0;

                        exec($cmd, $output, $returnCode);

                        if ($returnCode !== 0) {
                            // Save the output so you can inspect what went wrong
                            $errors['GIT-CLONE-ACE'] = $output;
                        }
                    }
                } else
                    return 'Git is not installed on the server. Skipping ACE editor download.'; // Skip if Git is not available
            
                return "Job 3: Downloading Ace editor library...\nDone.\n";
            },
            static function (): string {
                return "Job 4: Downloading CSS/utility assets...\nDone.\n";
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