<?php

// Check if the user has requested logout
if (filter_input(INPUT_GET, 'logout')) { // ?logout=true
  // Set headers to force browser to drop Basic Auth credentials
  header('WWW-Authenticate: Basic realm="Logged Out"');
  header('HTTP/1.0 401 Unauthorized');
    
  // Add cache control headers to prevent caching of the authorization details
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  header("Pragma: no-cache");
    
  // Unset the authentication details in the server environment
  unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    
  // Optional: Clear any existing headers related to authorization
  if (function_exists('header_remove')) {
    header_remove('HTTP_AUTHORIZATION');
  }

  // Provide feedback to the user and exit the script
  //header('Location: http://test:123@localhost/');
  exit('You have been logged out.');
}

//die(var_dump($_SERVER));
if (PHP_SAPI !== 'cli') {
  // Ensure the HTTP_AUTHORIZATION header exists
  if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    // Decode the HTTP Authorization header
    $authHeader = base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6));
    if ($authHeader) {
      // Split the decoded authorization string into user and password
      [$user, $password] = explode(':', $authHeader);

      // Set the PHP_AUTH_USER and PHP_AUTH_PW if available
      $_SERVER['PHP_AUTH_USER'] = $user ?? '';
      $_SERVER['PHP_AUTH_PW'] = $password ?? '';
    }
  }

  // Check if user credentials are provided
  if (empty($_SERVER['PHP_AUTH_USER'])) {
    // Prompt for Basic Authentication if credentials are missing
    header('WWW-Authenticate: Basic realm="Dashboard"');
    header('HTTP/1.0 401 Unauthorized');
  
    // Stop further script execution
    exit('Authentication required.');
  } else {
    // Display the authenticated user's details
    //echo "<p>Hello, {$_SERVER['PHP_AUTH_USER']}.</p>";
    //echo "<p>You entered '{$_SERVER['PHP_AUTH_PW']}' as your password.</p>";
    //echo "<p>Authorization header: {$_SERVER['HTTP_AUTHORIZATION']}</p>";
  }
}
/*
if (isset($_GET['debug'])) 
  require_once 'public/index.php';
else
  die(header('Location: public/index.php'));
*/