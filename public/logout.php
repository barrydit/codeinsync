<?php
// Mark that the next visit to a protected page must re-prompt
setcookie('REAUTH_REQUIRED', '1', 0, '/', '', false, true);

// Make sure nothing is cached
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
?>
<!doctype html>
<meta charset="utf-8">
<title>Logged out</title>
<p>You’ve been logged out.</p>
<p><a href="/index2.php">Return to the app</a></p>