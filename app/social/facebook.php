<?php

/*
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
*/
session_start();

$url = 'https://www.facebook.com/'; // change to your target

$cookieJar = $_SESSION['cookie_jar'] ?? (sys_get_temp_dir() . '/fb_cookies_' . uniqid() . '.txt');
$_SESSION['cookie_jar'] = $cookieJar;

$_SESSION['ua'] ??= 'Mozilla/5.0 (compatible; DebugBot/1.0)' ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/117.0 Safari/537.36';
$_SESSION['accept'] ??= 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
$_SESSION['accept_lang'] ??= 'en-US,en;q=0.9';

/*
$ch = curl_init($url);

$headers = [
    //'Content-Security-Policy: default-src \'self\' ; frame-src https://www.facebook.com/;',
    'Content-Security-Policy: frame-ancestors \'self\' https://www.facebook.com;',
    'X-Frame-Options: ALLOW-FROM https://www.facebook.com'
];

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0 Safari/537.36',
    CURLOPT_COOKIEJAR      => $cookieJar,
    CURLOPT_COOKIEFILE     => $cookieJar,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$response = curl_exec($ch);
if ($response === false) {
    echo "cURL error: " . curl_error($ch);
    curl_close($ch);
    exit;
}
*/

$headerSize = $info['header_size'] ?? 0;
$headers = substr($response, 0, $headerSize);
$htmlContent = substr($response, $headerSize);
// echo "<h3>HTTP headers</h3><pre>" . htmlspecialchars($headers) . "</pre>";
// echo $htmlContent;
//'Content-Security-Policy: default-src \'self\' ; frame-src https://www.facebook.com/;',

//$addHeaders[] = 'Content-Security-Policy: frame-ancestors \'self\' http://www.facebook.com;';
//$addHeaders[] = 'X-Frame-Options: ALLOW-FROM http://www.facebook.com';

$addHeaders = [
    "content-security-policy: default-src blob: 'self' https://*.fbsbx.com *.facebook.com *.fbcdn.net;script-src *.facebook.com *.fbcdn.net *.facebook.net 127.0.0.1:* 'nonce-dMpCnZNA' blob: 'self' connect.facebook.net 'unsafe-eval' https://*.google-analytics.com *.google.com;style-src *.fbcdn.net data: *.facebook.com 'unsafe-inline' https://fonts.googleapis.com;connect-src *.facebook.com facebook.com *.fbcdn.net *.facebook.net wss://*.facebook.com:* wss://*.whatsapp.com:* wss://*.fbcdn.net attachment.fbsbx.com ws://localhost:* blob: *.cdninstagram.com 'self' http://localhost:3103 wss://gateway.facebook.com wss://edge-chat.facebook.com wss://snaptu-d.facebook.com wss://kaios-d.facebook.com/ v.whatsapp.net *.fbsbx.com *.fb.com https://*.google-analytics.com;font-src data: *.facebook.com *.fbcdn.net *.fbsbx.com https://fonts.gstatic.com;img-src *.fbcdn.net *.facebook.com data: https://*.fbsbx.com facebook.com *.cdninstagram.com fbsbx.com fbcdn.net connect.facebook.net *.carriersignal.info blob: android-webview-video-poster: *.whatsapp.net *.fb.com *.oculuscdn.com *.tenor.co *.tenor.com *.giphy.com https://trustly.one/ https://*.trustly.one/ https://paywithmybank.com/ https://*.paywithmybank.com/ https://www.googleadservices.com https://googleads.g.doubleclick.net https://*.google-analytics.com;media-src *.cdninstagram.com blob: *.fbcdn.net *.fbsbx.com www.facebook.com *.facebook.com data: *.tenor.co *.tenor.com https://*.giphy.com;child-src data: blob: 'self' https://*.fbsbx.com *.facebook.com *.fbcdn.net;frame-src *.facebook.com *.fbsbx.com fbsbx.com data: www.instagram.com *.fbcdn.net accounts.meta.com *.accounts.meta.com https://trustly.one/ https://*.trustly.one/ https://paywithmybank.com/ https://*.paywithmybank.com/ https://www.googleadservices.com https://googleads.g.doubleclick.net https://www.google.com https://td.doubleclick.net *.google.com *.doubleclick.net;manifest-src data: blob: 'self' https://*.fbsbx.com *.facebook.com *.fbcdn.net;object-src data: blob: 'self' https://*.fbsbx.com *.facebook.com *.fbcdn.net;worker-src blob: *.facebook.com data:;block-all-mixed-content;upgrade-insecure-requests;",
    "x-frame-options: ALLOW-FROM http://www.facebook.com"
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 30,
    //CURLOPT_HTTPHEADER => $addHeaders,
    CURLOPT_USERAGENT => $_SESSION['ua'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (compatible; DebugBot/1.0)',
    CURLOPT_HTTPHEADER => [
        'Accept: ' . $_SESSION['accept'],
        'Accept-Language: ' . $_SESSION['accept_lang'],
    ],
    CURLOPT_COOKIEJAR => $cookieJar,
    CURLOPT_COOKIEFILE => $cookieJar,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);
/*
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
//curl_setopt($ch, CURLOPT_HTTPHEADER , $addHeaders);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; DebugBot/1.0)'); // Works
//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0 Safari/537.36');
*/
$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

$_SESSION['last_response_info'] = $info;
$_SESSION['last_response_time'] = time();
$_SESSION['last_effective_url'] = $info['effective_url'] ?? null;

$httpCode = $info['http_code'];
$effectiveUrl = $info['effective_url'];
$headerSize = $info['header_size'];
// var_dump('<pre>' . var_export($info, true) . '</pre>');

$rawHeaders = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

//var_dump('<pre>' . var_export($rawHeaders, true) . '</pre>');

// show diagnostics
//echo "<h3>HTTP Status</h3><pre>{$httpCode}</pre>";
//echo "<h3>Final URL (effective_url)</h3><pre>" . htmlspecialchars($effectiveUrl) . "</pre>";
//echo "<h3>Response headers (last response)</h3><pre>" . htmlspecialchars($rawHeaders) . "</pre>";

// extract set-cookie lines
if (preg_match_all('/^set-cookie:\s*(.+)$/mi', $rawHeaders, $m)) {
    //echo "<h3>Set-Cookie (last response)</h3><ul>";
    foreach ($m[1] as $c) {
        list($name, $value) = array_map('trim', explode('=', $c, 2));
        $cookies[$name] = $value;
    }
    //echo "</ul>";
}

$_SESSION['last_set_cookies'] = $cookies;

// detect login/consent gate
if (stripos($effectiveUrl, '/login') !== false || stripos($effectiveUrl, 'privacy_mutation_token') !== false) {
    echo "<p><strong>Detected:</strong> final URL looks like a login/consent flow. The content is gated and requires browser-based authentication.</p>";
}

// $nonce = base64_encode(random_bytes(16));

$script = "console.log('hello');";

$hash = base64_encode(hash('sha256', $script, true));


//header('X-Frame-Options: ALLOW-FROM https://www.facebook.com');
//header('Referrer-Policy: strict-origin-when-cross-origin');
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self' 'sha256-{$hash}' https://connect.facebook.net https://facebook.com https://*.facebook.com https://*.fbcdn.net; " .
    "style-src 'self' 'unsafe-inline' https://connect.facebook.net https://facebook.com https://*.facebook.com https://*.fbcdn.net; " .
    "img-src 'self' data: https://facebook.com https://*.facebook.com https://*.fbcdn.net; " .
    "connect-src 'self' https://facebook.com https://*.facebook.com https://*.fbcdn.net; " .
    "frame-src https://facebook.com https://*.facebook.com; " .
    "base-uri 'self'; frame-ancestors 'self' https://www.facebook.com; " .
    "upgrade-insecure-requests"
);
/*
header(
  "Content-Security-Policy: " .
  "default-src 'self'; " .
  "script-src 'self' https://connect.facebook.net https://*.facebook.com https://*.fbcdn.net; " .
  "style-src 'self' 'unsafe-inline' https://*.facebook.com https://*.fbcdn.net; " .
  "img-src 'self' data: https://*.facebook.com https://*.fbcdn.net; " .
  "connect-src 'self' https://*.facebook.com https://*.fbcdn.net; " .
  "frame-src https://*.facebook.com; " .
  "base-uri 'self'; frame-ancestors 'self'; " .
  "upgrade-insecure-requests"
);
*/

/*
header("Content-Security-Policy: ".
  "default-src 'self'; ".
  "script-src 'self' connect.facebook.net *.facebook.com *.fbcdn.net; ".
  "style-src 'self' 'unsafe-inline' *.facebook.com *.fbcdn.net; ".
  "img-src 'self' data: *.facebook.com *.fbcdn.net; ".
  "frame-src *.facebook.com; ".
  "connect-src 'self' *.facebook.com *.fbcdn.net;");
*/

ob_start();

if (empty($html = $body))
    die('Page Could not be Loaded.');

if (preg_match('/name="fb_dtsg"\s+value="([^"]+)"/i', $body, $m)) {
    $_SESSION['fb_dtsg'] = $m[1];
} elseif (preg_match('/"fb_dtsg"\s*:\s*"([^"]+)"/i', $body, $m2)) {
    $_SESSION['fb_dtsg'] = $m2[1];
}

if (preg_match('/name="jazoest"\s+value="([^"]+)"/i', $body, $m3)) {
    $_SESSION['jazoest'] = $m3[1];
}

libxml_use_internal_errors(true);
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

$node = $dom->getElementById('meta_referrer');
if ($node && $node->parentNode) {
    $node->parentNode->removeChild($node);
}

echo $clean = $dom->saveHTML();
libxml_clear_errors();

$html = ob_get_clean();

$inject = <<<HTML
<div id="fb-root"></div>
<script async crossorigin="anonymous"
  src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v17.0"></script>
<script nonce="{$hash}">
{$script}
</script>
HTML;

// insert just before </body>, case-insensitive
if (preg_match('~</body>~i', $html)) {
    $html = preg_replace('~</body>~i', $inject . "\n</body>", $html, 1);
} else {
    $html .= "\n" . $inject; // fallback
}

echo $html;

/*
// Create a new DOMDocument instance
$dom = new DOMDocument();

// Load the HTML content
libxml_use_internal_errors(true); // Suppress warnings for invalid HTML
$dom->loadHTML($body);
libxml_clear_errors();

// Create a new <base> element
$base = $dom->createElement('base');
$base->setAttribute('href', '//www.facebook.com/');
// $base->setAttribute('target', '_blank');

// Find the <head> section
$head = $dom->getElementsByTagName('head')->item(0);

// Insert the <base> tag into the <head>
$head->insertBefore($base, $head->firstChild);

// Output the modified HTML
echo $dom->saveHTML();
*/

// http://localhost/login/?privacy_mutation_token=eyJ0eXBlIjowLCJjcmVhdGlvbl90aW1lIjoxNzYwNjcxNzA4LCJjYWxsc2l0ZV9pZCI6MzgxMjI5MDc5NTc1OTQ2fQ%3D%3D&next

//echo '<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">';
//echo $data;
//}
