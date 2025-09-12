<?php
/**
 * Lazy loader for the latest Composer version.
 * - Caches getcomposer.org homepage HTML for 5 days under APP_BASE['var'].
 * - Parses "Latest: x.y.z (...)" from the page.
 * - Returns version string (e.g., "2.7.7") or null on failure.
 *
 * @param array|null $errors Collects non-fatal errors.
 * @param bool       $forceRefresh If true, ignore TTL and refresh now.
 * @return string|null
 */
function composer_latest_version(?array &$errors = null, bool $forceRefresh = false): ?string
{
    $errors ??= [];

    // 1) Ensure var/ exists
    $varDir = app_base('var', null, 'rel') ?? 'var/';
    if (!is_dir($varDir) && !@mkdir($varDir, 0755, true)) {
        $errors['COMPOSER_LATEST'][] = "Failed to create var directory: {$varDir}";
        return null;
    }

    // 2) Cache file + TTL
    $cacheFile = rtrim($varDir, '/\\') . DIRECTORY_SEPARATOR . 'getcomposer.org.html';
    $ttlDays = 5;
    $needsUpdate = $forceRefresh || !is_file($cacheFile) ||
        ((time() - @filemtime($cacheFile)) >= ($ttlDays * 86400));

    // 3) Refresh cache if needed
    if ($needsUpdate) {
        $html = fetch_getcomposer_home($fetchErr);
        if ($html !== null && $html !== '') {
            if (@file_put_contents($cacheFile, $html) === false) {
                $errors['COMPOSER_LATEST'][] = "Unable to write cache file: {$cacheFile}";
            }
        } else {
            // Non-fatal: fall back to old cache if it exists
            $msg = $fetchErr ?: 'Empty response from getcomposer.org';
            $errors['COMPOSER_LATEST'][] = $msg;
            if (!is_file($cacheFile)) {
                // No cache to fall back to
                return null;
            }
        }
    }

    // 4) Parse cached HTML
    $cached = @file_get_contents($cacheFile);
    if ($cached === false || $cached === '') {
        $errors['COMPOSER_LATEST'][] = "Cache file unreadable/empty: {$cacheFile}";
        return null;
    }

    // Suppress HTML warnings
    $prevUseErrors = libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'utf-8');
    $doc->loadHTML($cached);
    libxml_clear_errors();
    libxml_use_internal_errors($prevUseErrors);

    // Prefer XPath (no external helpers needed)
    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query('//p[contains(concat(" ", normalize-space(@class), " "), " latest ")]');

    if (!$nodes || $nodes->length === 0) {
        // Fallback: try id="main" then any <p> within
        $main = $doc->getElementById('main');
        if ($main) {
            $nodes = $xpath->query('.//p', $main);
        }
    }

    if ($nodes && $nodes->length > 0) {
        $text = trim($nodes->item(0)->textContent ?? '');
        // Match "Latest: 2.7.7 (stable)" style
        if (preg_match('/Latest:\s*([0-9]+\.[0-9]+\.[0-9]+)\s*\(/', $text, $m)) {
            return $m[1];
        }
        $errors['COMPOSER_LATEST'][] = "Pattern did not match in text: {$text}";
        return null;
    }

    $errors['COMPOSER_LATEST'][] = "Could not locate 'p.latest' in Composer homepage HTML.";
    return null;
}

/**
 * Fetches getcomposer.org homepage HTML with sane defaults.
 * Returns string HTML or null; sets $err with a short message on failure.
 *
 * @param string|null $err
 * @return string|null
 */
function fetch_getcomposer_home(?string &$err = null): ?string
{
    $url = 'https://getcomposer.org/';

    // Prefer cURL if available
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_ENCODING => '', // accept gzip/deflate
            CURLOPT_USERAGENT => 'CodeInSync/1.0 (+PHP cURL)',
        ]);
        $html = curl_exec($ch);
        if ($html === false) {
            $err = 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            return null;
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 400) {
            $err = "HTTP {$code} from getcomposer.org";
            return null;
        }
        return $html;
    }

    // Fallback: file_get_contents with timeout
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 8,
            'header' => "User-Agent: CodeInSync/1.0 (+PHP stream)\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);
    $html = @file_get_contents($url, false, $ctx);
    if ($html === false) {
        $err = 'stream error fetching getcomposer.org';
        return null;
    }
    return $html;
}

if (!function_exists('composer_json')) {
    /**
     * Get composer.json as array (default) or stdClass, with per-request cache.
     * @param bool $assoc true=array, false=object
     * @return array|object|null
     */
    function composer_json(bool $assoc = true) {
        static $cacheArr = null, $cacheObj = null;

        // Already cached?
        if ($assoc && $cacheArr !== null) return $cacheArr;
        if (!$assoc && $cacheObj !== null) return $cacheObj;

        // Source: prefer COMPOSER_JSON_RAW if defined, else read file
        $raw = defined('COMPOSER_JSON_RAW') ? COMPOSER_JSON_RAW : null;
        if ($raw === null && defined('COMPOSER_JSON') && is_file(COMPOSER_JSON)) {
            $raw = @file_get_contents(COMPOSER_JSON);
        }
        if (!is_string($raw) || $raw === '') return null;

        $decoded = json_decode($raw, $assoc);
        if (json_last_error() !== JSON_ERROR_NONE) return null;

        if ($assoc)  $cacheArr = $decoded;
        else         $cacheObj = $decoded;

        return $decoded;
    }
}

if (!function_exists('composer_json_get')) {
    /**
     * Dot-path getter, e.g. composer_json_get('require.symfony/console', '*')
     */
    function composer_json_get(string $path, $default = null) {
        $data = composer_json(true);
        if (!is_array($data)) return $default;
        $cur = $data;
        foreach (explode('.', $path) as $seg) {
            if (!is_array($cur) || !array_key_exists($seg, $cur)) return $default;
            $cur = $cur[$seg];
        }
        return $cur;
    }
}