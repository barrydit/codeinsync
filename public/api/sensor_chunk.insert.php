<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// --- DB CONNECTION (adjust to your config)
$dsn = 'mysql:host=localhost;dbname=abate;charset=utf8mb4';
$user = 'root';
$pass = 'password';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $stmt = $pdo->query("SELECT payload_blob FROM sensor_chunk WHERE session_id = 1");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {  
        $decompressed = gzdecode($row['payload_blob']);
        $data[] = json_decode($decompressed, true) ?? null;
    }

    print_r($data); 
}

// --- READ RAW JSON INPUT
$raw = file_get_contents('php://input');

if (!$raw) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Empty body']);
    exit;
}

// --- DECODE JSON
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

function mysql_datetime_ms(string $iso): string
{
    $dt = new DateTimeImmutable($iso);
    return $dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s.v');
}

// --- VALIDATION (basic)
$sessionId = $data['session_id'] ?? null; // not used in insert, but could be validated
$chunkStart = mysql_datetime_ms((string) ($data['chunk_start'] ?? ''));
$chunkEnd   = mysql_datetime_ms((string) ($data['chunk_end'] ?? ''));
$sampleRateHz = $data['sample_rate_hz'] ?? null;
$pointCount = $data['point_count'] ?? null;
$values = $data['values'] ?? null;

if (!$sessionId || !$chunkStart || !$chunkEnd || !$sampleRateHz || !$pointCount || !is_array($values)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Missing required fields']);
    exit;
}

// --- PREPARE PAYLOAD
//$payload = [
//    'values' => $values
//];

$payload = $values;

// --- COMPRESS (IMPORTANT)
$jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
$compressed = gzencode($jsonPayload, 6);

// --- INSERT
$sql = "
    INSERT INTO sensor_chunk (
        session_id,
        chunk_start,
        chunk_end,
        sample_rate_hz,
        point_count,
        channel_count,
        channel_map_json,
        payload_format,
        payload_blob
    ) VALUES (
        :session_id,
        :chunk_start,
        :chunk_end,
        :sample_rate_hz,
        :point_count,
        :channel_count,
        :channel_map_json,
        :payload_format,
        :payload_blob
    )
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':session_id' => $sessionId, // hardcoded for demo
    ':chunk_start' => $chunkStart,
    ':chunk_end' => $chunkEnd,
    ':sample_rate_hz' => (int) $sampleRateHz,
    ':point_count' => (int) $pointCount,
    ':channel_count' => (int) 8,
    ':channel_map_json' => '{
  "channels": [
    { "index": 0, "key": "temperature", "label": "Temperature Sensor", "unit": "celsius", "active": true },
    { "index": 1, "key": "sensor_2", "label": "Sensor 2", "unit": "mv", "active": false },
    { "index": 2, "key": "sensor_3", "label": "Sensor 3", "unit": "mv", "active": false },
    { "index": 3, "key": "sensor_4", "label": "Sensor 4", "unit": "mv", "active": false },
    { "index": 4, "key": "sensor_5", "label": "Sensor 5", "unit": "mv", "active": false },
    { "index": 5, "key": "sensor_6", "label": "Sensor 6", "unit": "mv", "active": false },
    { "index": 6, "key": "sensor_7", "label": "Sensor 7", "unit": "mv", "active": false },
    { "index": 7, "key": "sensor_8", "label": "Sensor 8", "unit": "mv", "active": false }
  ]
}',
    ':payload_format' => 'json_columns_gzip_v1',
    ':payload_blob' => $compressed
]);

echo json_encode([
    'ok' => true,
    'insert_id' => $pdo->lastInsertId()
]);