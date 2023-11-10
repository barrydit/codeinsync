<?php
// Set the CORS headers
header('Access-Control-Allow-Origin: https://*.github.com');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');

// Check the request method
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // This is a preflight request, so just send a 200 response
    header('HTTP/1.1 200 OK');
    exit();
}

// Make the request to the GitHub API
$url = 'https://github.com/barrydit/composer_app/security/overall-count';
$response = file_get_contents($url);

// Forward the GitHub API response to the client
header('Content-Type: application/json');
echo $response;

?>