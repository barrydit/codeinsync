<?php

if (stripos($effectiveUrl, '/login') !== false || stripos($effectiveUrl, 'privacy_mutation_token') !== false) {
    die("<p><strong>Detected:</strong> final URL looks like a login/consent flow. The content is gated and requires browser-based authentication.</p>");

}
// die(var_dump($_SERVER));
header('X-Frame-Options: ALLOW-FROM https://www.facebook.com');
header('Referrer-Policy: strict-origin-when-cross-origin');

session_start();

// die('<pre>' . var_export($_SESSION, true) . "</pre>");

// header('Location: ' . 'https://www.facebook.com' . $_SERVER['REQUEST_URI']);

$addHeaders = [
"Content-Security-Policy: " .
"default-src blob: 'self' https://*.fbsbx.com *.facebook.com *.fbcdn.net; " .
"script-src *.facebook.com *.fbcdn.net *.facebook.net 127.0.0.1:* 'nonce-dMpCnZNA' blob: 'self' connect.facebook.net 'unsafe-eval' https://*.google-analytics.com *.google.com; " .
"style-src *.fbcdn.net data: *.facebook.com 'unsafe-inline' https://fonts.googleapis.com; " .
"connect-src *.facebook.com facebook.com *.fbcdn.net *.facebook.net wss://*.facebook.com:* wss://*.whatsapp.com:* wss://*.fbcdn.net attachment.fbsbx.com ws://localhost:* blob: *.cdninstagram.com 'self' http://localhost:3103 wss://gateway.facebook.com wss://edge-chat.facebook.com wss://snaptu-d.facebook.com wss://kaios-d.facebook.com/ v.whatsapp.net *.fbsbx.com *.fb.com https://*.google-analytics.com; " .
"font-src data: *.facebook.com *.fbcdn.net *.fbsbx.com https://fonts.gstatic.com; " .
"img-src *.fbcdn.net *.facebook.com data: https://*.fbsbx.com facebook.com *.cdninstagram.com fbsbx.com fbcdn.net connect.facebook.net *.carriersignal.info blob: android-webview-video-poster: *.whatsapp.net *.fb.com *.oculuscdn.com *.tenor.co *.tenor.com *.giphy.com https://trustly.one/ https://*.trustly.one/ https://paywithmybank.com/ https://*.paywithmybank.com/ https://www.googleadservices.com https://googleads.g.doubleclick.net https://*.google-analytics.com; " .
"media-src *.cdninstagram.com blob: *.fbcdn.net *.fbsbx.com www.facebook.com *.facebook.com data: *.tenor.co *.tenor.com https://*.giphy.com; ".
"child-src data: blob: 'self' https://*.fbsbx.com *.facebook.com *.fbcdn.net; " .
"frame-src *.facebook.com *.fbsbx.com fbsbx.com data: www.instagram.com *.fbcdn.net accounts.meta.com *.accounts.meta.com https://trustly.one/ https://*.trustly.one/ https://paywithmybank.com/ https://*.paywithmybank.com/ https://www.googleadservices.com https://googleads.g.doubleclick.net https://www.google.com https://td.doubleclick.net *.google.com *.doubleclick.net; " .
"base-uri 'self'; frame-ancestors 'self' https://www.facebook.com; " .
"manifest-src data: blob: 'self' https://*.fbsbx.com *.facebook.com *.fbcdn.net; " .
"object-src data: blob: 'self' https://*.fbsbx.com *.facebook.com *.fbcdn.net; " .
"worker-src blob: *.facebook.com data:;" . 
"block-all-mixed-content; " .
"upgrade-insecure-requests; ",
"x-frame-options: ALLOW-FROM http://www.facebook.com"
];


$cookieJar = sys_get_temp_dir() . '/fb_diag_cookies_' . uniqid() . '.txt';

$ch = curl_init('https://www.facebook.com' . $_SERVER['REQUEST_URI']);

$post = [
  'fb_dtsg' => $_SESSION['fb_dtsg'] ?? '',
  'jazoest' => $_SESSION['jazoest'] ?? '',
  'other_field' => 'value',
];


/*
curl_setopt_array($ch, [
  CURLOPT_URL => 'https://www.facebook.com' . $_SERVER['REQUEST_URI'],
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query($post),
  CURLOPT_COOKIEFILE => $_SESSION['cookie_jar'],
  CURLOPT_COOKIEJAR  => $_SESSION['cookie_jar'],
  CURLOPT_USERAGENT  => $_SESSION['ua'],
  CURLOPT_HTTPHEADER => [
     'Accept: ' . $_SESSION['accept'],
     'Accept-Language: ' . $_SESSION['accept_lang'],
     'Referer: ' . ($_SESSION['last_effective_url'] ?? 'https://www.facebook.com/'),
     'X-Requested-With: XMLHttpRequest', // if endpoint expects AJAX
  ],
  CURLOPT_SSL_VERIFYPEER => true,
  CURLOPT_SSL_VERIFYHOST => 2,
]);*/

curl_setopt_array($ch, [
    //CURLOPT_RETURNTRANSFER => true,
    //CURLOPT_HEADER         => true,
    //CURLOPT_NOBODY         => false,
    //CURLOPT_FOLLOWLOCATION => true,
    //CURLOPT_MAXREDIRS      => 10,
    //CURLOPT_CONNECTTIMEOUT => 10,
    //CURLOPT_TIMEOUT        => 30,
    //CURLOPT_URL => 'https://www.facebook.com' . $_SERVER['REQUEST_URI'],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post),
    CURLOPT_COOKIEJAR      => $_SESSION['cookie_file'],
    CURLOPT_COOKIEFILE     => $_SESSION['cookie_file'],
    CURLOPT_USERAGENT      => $_SESSION['ua'],
    CURLOPT_HTTPHEADER     => [
     'Accept: ' . $_SESSION['accept'],
     'Accept-Language: ' . $_SESSION['accept_lang'],
     'Referer: ' . ($_SESSION['last_effective_url'] ?? 'https://www.facebook.com/'),
     'X-Requested-With: XMLHttpRequest', // if endpoint expects AJAX
  ],
    //CURLOPT_USERAGENT        => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0 Safari/537.36',
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

ob_start();

$response = curl_exec($ch);

$buffer = ob_get_contents();
ob_end_clean();

$info = curl_getinfo($ch);
curl_close($ch);

$httpCode     = $info['http_code'];
$effectiveUrl  = $info['effective_url'];
$headerSize   = $info['header_size'];

$rawHeaders = substr($response, 0, $headerSize);
$body       = substr($response, $headerSize);

//var_dump('<pre>' . var_export($response, true) . '</pre>');

echo $body;
