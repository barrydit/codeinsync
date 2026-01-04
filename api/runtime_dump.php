<?php
declare(strict_types=1);

if (!defined('APP_BOOTSTRAPPED')) {
    require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$type = (string) ($_GET['type'] ?? 'constants');
$pretty = !isset($_GET['pretty']) || $_GET['pretty'] !== '0';

function mask_secrets(array $constants): array
{
    $masked = [];
    foreach ($constants as $k => $v) {
        $key = (string) $k;

        // Mask likely secret values (tweak list as needed)
        if (preg_match('/(PASS|PASSWORD|SECRET|TOKEN|API[_-]?KEY|PRIVATE|OAUTH|DSN|AUTH)/i', $key)) {
            $masked[$key] = is_string($v) ? '***masked***' : '***masked***';
            continue;
        }

        $masked[$key] = $v;
    }
    return $masked;
}

try {
    switch ($type) {
        case 'constants': {
            $all = get_defined_constants(true);

            // Prefer showing only user constants by default
            $group = (string) ($_GET['group'] ?? 'user');
            $data = $all[$group] ?? $all;

            if (($group === 'user' || $group === 'all') && (($_GET['unsafe'] ?? '0') !== '1')) {
                $data = is_array($data) ? mask_secrets($data) : $data;
            }

            if (is_array($data)) {
                ksort($data);
            }

            $payload = [
                'type' => 'constants',
                'group' => $group,
                'count' => is_array($data) ? count($data) : 0,
                'data' => $data,
            ];
            break;
        }

        case 'functions': {
            $funcs = get_defined_functions(true);
            $data = $funcs['user'] ?? [];

            sort($data);

            $payload = [
                'type' => 'functions',
                'count' => count($data),
                'data' => $data,
            ];
            break;
        }

        case 'classes': {
            $data = get_declared_classes();
            sort($data);

            // Optional filter: ?prefix=CodeInSync\\
            if (!empty($_GET['prefix'])) {
                $prefix = (string) $_GET['prefix'];
                $data = array_values(array_filter($data, fn($c) => str_starts_with($c, $prefix)));
            }

            $payload = [
                'type' => 'classes',
                'count' => count($data),
                'data' => $data,
            ];
            break;
        }

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'INVALID_TYPE'], JSON_UNESCAPED_SLASHES);
            exit;
    }

    $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    if ($pretty)
        $flags |= JSON_PRETTY_PRINT;

    echo json_encode(['ok' => true] + $payload, $flags);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'RUNTIME_DUMP_FAILED',
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_SLASHES);
}
