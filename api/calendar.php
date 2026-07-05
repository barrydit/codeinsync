<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap/bootstrap.php'; // if needed

// Only handle POST requests for this app
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    return;
}

if (($_POST['app'] ?? '') !== 'medication_log') {
    return;
}

header('X-Content-Type-Options: nosniff');

$jsonFile = APP_PATH . APP_BASE['data'] . 'medication_log.json';

/**
 * Load JSON as array, or return [].
 */
function cis_load_json_array(string $file): array
{
    if (!is_file($file)) {
        return [];
    }

    $raw = (string) @file_get_contents($file);
    if ($raw === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Persist last N entries (keep newest N by date order as stored).
 */
function cis_save_json_last_n(string $file, array $data, int $max = 50): void
{
    // keep only last $max entries (preserve order)
    $data = array_reverse(array_slice(array_reverse($data), 0, $max));

    @file_put_contents(
        $file,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
    );
}

/**
 * Normalize a posted time like "10:00 AM" / "22:00" / etc. into "hh:mm AM/PM".
 */
function cis_normalize_time_slot(string $timeSlotRaw): ?string
{
    $timeSlotRaw = trim($timeSlotRaw);
    if ($timeSlotRaw === '') {
        return null;
    }

    // Let strtotime do the heavy lifting
    $ts = strtotime($timeSlotRaw);
    if ($ts === false) {
        return null;
    }

    return date('h:i A', $ts);
}

/**
 * Sort doses by time ascending.
 */
function cis_sort_doses(array &$doses): void
{
    usort($doses, static function (array $a, array $b): int {
        return strtotime((string) ($a['time'] ?? '')) <=> strtotime((string) ($b['time'] ?? ''));
    });
}

/**
 * Find an entry by date and return its index, or -1.
 */
function cis_find_entry_index_by_date(array $data, string $date): int
{
    foreach ($data as $i => $entry) {
        if (($entry['date'] ?? null) === $date) {
            return $i;
        }
    }
    return -1;
}

// --------------------
// Validate input
// --------------------
$date = trim((string) ($_POST['date'] ?? ''));
$timeSlotRaw = (string) ($_POST['time_slot'] ?? '');
$status = $_POST['status'] ?? null;
$note = (string) ($_POST['note'] ?? '');

if ($date === '' || $status === null) {
    // silently ignore / or you can http_response_code(400)
    return;
}

$timeSlot = cis_normalize_time_slot($timeSlotRaw);
if ($timeSlot === null) {
    return;
}

$dose = [
    'time' => $timeSlot,
    'status' => (string) $status,
    'note' => $note,
];

// --------------------
// Load + update
// --------------------
$data = cis_load_json_array($jsonFile);

$idx = cis_find_entry_index_by_date($data, $date);

if ($idx === -1) {
    $data[] = [
        'date' => $date,
        'doses' => [$dose],
    ];
} else {
    if (!isset($data[$idx]['doses']) || !is_array($data[$idx]['doses'])) {
        $data[$idx]['doses'] = [];
    }

    $data[$idx]['doses'][] = $dose;
    cis_sort_doses($data[$idx]['doses']);
}

// Save (keep last 50 records)
cis_save_json_last_n($jsonFile, $data, 50);

// Redirect back
header('Location: http://' . APP_DOMAIN . '/?' . http_build_query(['path' => '', 'app' => 'calendar']));
exit;
