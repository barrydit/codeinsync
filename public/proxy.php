<?php

//$url = ($_POST['url']) ? 'https://' . $_POST['url'] : "https://php.net/manual/en/function.file-get-contents.php";
//header("Content-Type: text/html");
//echo file_get_contents($url);

//$url = 'https://' . ($_GET['url'] ?? $_POST['url'] ?? "php.net/manual/en/function.file-get-contents.php");
$query = $_SERVER['QUERY_STRING']; // Get everything after "?"
$url = "https://www.php.net/cached.php?$query"; // Forward request to php.net

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    die("Invalid URL");
}

// Fetch headers
$headers = @get_headers($url, 1);
if ($headers === false) {
    http_response_code(500);
    die("Failed to retrieve headers");
}

// Handle redirects
if (isset($headers["Location"])) {
    $url = is_array($headers["Location"]) ? end($headers["Location"]) : $headers["Location"];
}

// Detect MIME type
$contentType = $headers["Content-Type"] ?? "text/html";
$contentType = is_array($contentType) ? end($contentType) : $contentType;
$contentType = explode(";", $contentType)[0];

// Ensure only allowed types are served
$allowedTypes = ["text/html", "text/css", "application/javascript"];
if (!in_array($contentType, $allowedTypes)) {
    http_response_code(403);
    die("Forbidden MIME type: " . htmlspecialchars($contentType));
}

// Fetch content
$context = stream_context_create([
    "http" => ["header" => "User-Agent: Mozilla/5.0\r\n"]
]);

$content = @file_get_contents($url, false, $context);
if ($content === false) {
    http_response_code(500);
    die("Failed to fetch content");
}

// Fix relative stylesheet URLs in HTML
if ($contentType === "text/html") {
    $content = str_replace('href="/cached.php?', 'href="https://www.php.net/cached.php?', $content);
}

// Fix relative URLs inside CSS files
if ($contentType === "text/css") {
    $content = preg_replace('/url\(["\']?(\/[^)]+)["\']?\)/', 'url("https://www.php.net$1")', $content);
}

// Output content with correct MIME type
header("Content-Type: " . $contentType);
echo $content;
