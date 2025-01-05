<?php

function downloadFile(string $url, string $path, string $filename, array &$errors): void
{
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  $content = curl_exec($handle);
  curl_close($handle);

  if (!empty($content)) {
    if (!file_put_contents("{$path}{$filename}", $content))
      $errors['JS-LIBRARY'] = "$url could not be written to {$path}{$filename}.";
  } else
    $errors['JS-LIBRARY'] = "$url returned empty content.";
}

function shouldUpdateFile(string $filepath, int $expiryDays = 5): bool
{
  if (!is_file($filepath))
    return true; // File doesn't exist; needs to be downloaded.

  $fileModifiedTime = filemtime($filepath);
  $expiryTime = strtotime("+{$expiryDays} days", $fileModifiedTime);

  return time() > $expiryTime; // Check if the file is older than expiry days.
}


/* function parse_ini_file_multi($file) {
  // parse_ini_string(file_get_contents($file), true, INI_SCANNER_NORMAL))) 
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED); 
  $output = [];
  foreach($data as $key => $value) {
    $keys = explode('.', $key);
    $temp = &$output;
    foreach($keys as $key) {
      $temp = &$temp[$key];
    }
    $temp = $value;
  }
  return $output;
}
function parse_ini_file_multi($file) {
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
  $output = [];

  foreach($data as $section => $values) {
    if (is_array($values)) {
      foreach($values as $key => $value) {
        $keys = explode('.', $key);
        $temp = &$output[$section];
        foreach($keys as $key_part) {
          $temp = &$temp[$key_part];
        }
        $temp = $value;
      }
    } else {
      $keys = explode('.', $section);
      $temp = &$output;
      foreach($keys as $key_part) {
        $temp = &$temp[$key_part];
      }
      $temp = $values;
    }
  }
  
  return $output;
}
function parse_ini_file_multi($file) {
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
  $output = [];

  foreach ($data as $section => $values) {
    if (is_array($values)) {
      $output[$section] = [];
      foreach ($values as $key => $value) {
        $output[$section][$key] = str_replace(['\'', '"'], '', var_export($value, true));
      }
    } else {
      $output[$section] = str_replace(['\'', '"'], '', var_export($values, true));
    }
  }

  return $output;
}

function parse_ini_file_multi($file) {
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
  $output = [];

  foreach ($data as $section => $values) {
      if (is_array($values)) {
          $output[$section] = [];
          foreach ($values as $key => $value) {
              // Check if the value is a regular expression
              if (preg_match('/^\/.*\/$/', $value)) {
                  $output[$section][$key] = $value;
              } else {
                  // If not a regular expression, add slashes to escape special characters
                  $output[$section][$key] = addcslashes($value, '"\\');
              }
          }
      } else {
          // Add slashes to non-array values
          $output[$section] = addcslashes($values, '"\\');
      }
  }

  return $output;
}

function parse_ini_file_multi($file) {
  $data = parse_ini_file($file, true, INI_SCANNER_TYPED);
  $output = [];

  foreach ($data as $section => $values) {
      if (is_array($values)) {
          $output[$section] = [];
          foreach ($values as $key => $value) {
              // Do not escape regular expressions
              if (preg_match('/^\/.*\/[a-z]*$/i', $value)) {
                  $output[$section][$key] = $value;
              } else {
                  // Escape only non-regular expression values
                  $output[$section][$key] = addcslashes($value, '"\\');
              }
          }
      } else {
          // Escape only non-regular expression values
          if (preg_match('/^\/.*\/[a-z]*$/i', $values)) {
              $output[$section] = $values;
          } else {
              $output[$section] = addcslashes($values, '"\\');
          }
      }
  }

  return $output;
}

function parse_ini_file_multi($file): array
{
  $data = (array) parse_ini_file($file, true, INI_SCANNER_TYPED);
  $output = [];

  foreach ($data as $section => $values) {
    if (is_array($values)) {
      $output[$section] = [];
      foreach ($values as $key => $value) {
        // Do not escape regular expressions
        $output[$section][$key] = preg_match('/^\/.*\/[a-z]*$/i', $value) ? $value : (is_bool($value) ? $value : addcslashes($value, '"\\'));
      }
    } else {
      // Do not escape regular expressions
      // Unparenthesized `a ? b : c ? d : e` is not supported.
      // $output[$section] = (preg_match('/^\/.*\/[a-z]*$/i', $values)) ? $values : (is_bool($values)) ? $values ? 'true' : 'false' : addcslashes($values, '"\\');
      $output[$section] = (preg_match('/^\/.*\/[a-z]*$/i', $values)) ? $values : ((is_bool($values)) ? ($values ? true : false) : addcslashes($values, '"\\'));

    }
  }
  return $output;
} */



// Usage Example
//$globalPath = APP_PATH . '.env'; // Global .env
//$clientPath = APP_PATH . APP_ROOT . '.env'; // Client-specific .env
//$envData = EnvLoader::loadEnvFiles($globalPath, $clientPath);

// Populate $_ENV with the merged values
//$_ENV = array_merge($_ENV, $envData);

// Debugging Output
// print_r($_ENV);

/**
 * Summary of Shutdown
 */
class Shutdown
{
  private static $instance = false;
  private static array $functions = [];
  private static bool $enabled = true;
  private static $shutdownMessage = null;

  private function __construct()
  {
    error_log("EnvManager initialized.");
    defined('APP_END') or define('APP_END', microtime(true));
    $this->initializeEnv();
  }

  public static function instance(): self
  {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public static function triggerShutdown($message): void
  {
    self::$shutdownMessage = $message;
    register_shutdown_function([self::class, 'onShutdown']);
  }

  public static function onShutdown(): void
  {
    if (self::$enabled && self::$shutdownMessage !== null) {
      echo is_callable(self::$shutdownMessage)
        ? call_user_func(self::$shutdownMessage)
        : self::$shutdownMessage;
    } elseif (!self::$enabled) {
      foreach (self::$functions as $function) {
        $function(self::$shutdownMessage);
      }
    }
  }

  public static function setShutdownMessage($message): self
  {
    self::$shutdownMessage = $message;
    return self::instance();
  }

  public function shutdown(bool $die = true): void
  {
    if (!self::$enabled) {
      foreach (self::$functions as $function) {
        $function(self::$shutdownMessage);
      }
    }

    $message = is_callable(self::$shutdownMessage)
      ? call_user_func(self::$shutdownMessage)
      : self::$shutdownMessage;

    if ($die) {
      exit($message);
    }
  }

  public static function setEnabled(bool $enabled): self
  {
    self::$enabled = $enabled;
    return self::instance();
  }

  public static function loadEnvFiles(string $globalPath, string $clientPath): array
  {
    $globalEnv = self::parseIniFileWithSections($globalPath);
    $clientEnv = self::parseIniFileWithSections($clientPath);

    $globalRoot = array_filter($globalEnv, 'is_scalar');
    $clientRoot = array_filter($clientEnv, 'is_scalar');
    $globalSections = array_filter($globalEnv, 'is_array');
    $clientSections = array_filter($clientEnv, 'is_array');

    $mergedEnv = $globalRoot;

    foreach ($clientSections as $section => $values) {
      $mergedEnv[$section] = $globalSections[$section] ?? $values;
    }

    foreach ($globalSections as $section => $values) {
      if (!isset($clientSections[$section])) {
        $mergedEnv[$section] = $values;
      }
    }

    return $mergedEnv;
  }

  private function initializeEnv(): void
  {
    $globalPath = APP_PATH . '.env';
    $clientPath = APP_PATH . APP_ROOT . '.env';

    $mergedEnv = self::loadEnvFiles($globalPath, $clientPath);
    $_ENV = array_merge($_ENV, $mergedEnv);

    if (self::isNonEmptyFile($globalPath)) {
      self::backupEnvFile($globalPath);
    }
  }

  private static function parseIniFileWithSections(string $filePath): array
  {
    return file_exists($filePath) ? (parse_ini_file($filePath, true, INI_SCANNER_TYPED) ?: []) : [];
  }

  private static function isNonEmptyFile(string $filePath): bool
  {
    return is_file($filePath) && filesize($filePath) > 0;
  }

  private static function backupEnvFile(string $filePath): void
  {
    $envContent = file_get_contents($filePath);
    $parsedEnv = self::parseIniFileWithSections($filePath);

    foreach ($parsedEnv as $section => $values) {
      if (is_array($values) && isset($values['OAUTH_TOKEN'])) {
        $parsedEnv[$section]['OAUTH_TOKEN'] = null;
      }
    }

    $backupContent = self::buildEnvContent($parsedEnv);
    file_put_contents($filePath . '.bck', $backupContent);
  }

  private static function buildEnvContent(array $envData): string
  {
    $iniString = '';
    foreach ($envData as $key => $value) {
      if (is_array($value)) {
        $iniString .= "[$key]\n";
        foreach ($value as $nestedKey => $nestedValue) {
          $nestedValue = self::convertValue($nestedValue);
          $iniString .= "$nestedKey = $nestedValue\n";
        }
      } else {
        $value = self::convertValue($value);
        $iniString .= "$key = $value\n";
      }
    }
    return $iniString;
  }

  private static function convertValue($value): string
  {
    if (is_bool($value)) {
      return $value ? 'true' : 'false';
    }
    if (is_null($value)) {
      return 'null';
    }
    return (string) $value;
  }
}


/**
 * Summary of handleError
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @return bool
 */

/*
  public static function handleError($errno, $errstr, $errfile, $errline)
  {
    //echo 'Does this work? handleError()';
    self::triggerShutdown("Error: [$errno] $errstr - $errfile:$errline");
    return true; // To prevent PHP's internal error handler from running
  }
*/
/**
 * Summary of handleException
 * @param mixed $exception
 * @return void
 */

/*

  public static function handleException($exception)
  {
    //echo "Does this work? handleException()";
    $message = "Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    self::triggerShutdown($message);
  }
*/
/**
 * Summary of handleParseError
 * @return void
 */

/*
  public static function handleParseError()
  {
    $error = error_get_last();
    //echo 'Does this work? handleParseError()';
    if ($error !== null) {
      $message = "Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}";
      self::triggerShutdown($message);
    }

  }
*/


// Register custom error and exception handlers
//set_error_handler([Shutdown::class, 'handleError']);
//set_exception_handler([Shutdown::class, 'handleException']);
//register_shutdown_function(function () {
//  Shutdown::handleParseError();
//});


/**
 * Dumps a variable with formatting and optionally stops execution.
 *
 * @param mixed $param The variable to be dumped. Default is null.
 * @param bool $die Whether to stop execution after dumping the variable. Default is true.
 * @param bool $debug Whether to include debug information in the output. Default is true.
 *
 * @return void Returns void if execution is stopped; otherwise, returns the result of var_dump().
 */

function dd(mixed $param = null, bool $die = true, bool $debug = true): void
{
  // Define start time if not defined
  defined('APP_START') || define('APP_START', $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));

  // Calculate execution time
  $execTime = round((defined('APP_END') ? APP_END : microtime(true)) - APP_START, 3);
  $memoryUsage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
  $output = "Execution time: <b>{$execTime}</b> secs | Memory usage: <b>{$memoryUsage}</b> MB" . PHP_EOL;

  // Format the dump output
  $isCLI = PHP_SAPI === 'cli';
  $dump = is_array($param) || is_object($param)
    ? json_encode($param, JSON_PRETTY_PRINT)
    : var_export($param, true);

  $formattedOutput = $isCLI
    ? '<pre><code>' . htmlspecialchars($dump) . '</code></pre>' . $output
    : $dump . PHP_EOL . $output;

  if ($die)
    // Graceful shutdown with error handling
    try {
      Shutdown::setEnabled(false)
        ->setShutdownMessage(fn() => $formattedOutput)
        ->shutdown();
    } catch (Throwable $e) {
      echo "Error handling shutdown: ", $e->getMessage();
      echo $formattedOutput;
    } else {
    // Immediate output
    echo $formattedOutput;
    if ($debug) {
      error_log($formattedOutput);
    }
  }
}

/*
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        return ($http_status == 200 ? true : false);
*/

/**
 * Summary of buildUri
 * @param array $urlComponents
 * @param mixed $queryParams
 * @return string
 */
function buildUri(array $urlComponents, $queryParams = [])
{
  $uri = $urlComponents['scheme'] . '://' . $urlComponents['host'];
  if (isset($urlComponents['port']) && $urlComponents['port'] !== '80') {
    $uri .= ':' . $urlComponents['port'];
  }
  $uri .= $urlComponents['path'];
  if (!empty($queryParams)) {
    $uri .= '?' . http_build_query($queryParams);
  }
  return $uri;
}

/**
 * Parses command line args and returns array of args and their values
 *
 * @param array $args   The array from $argv
 * @return array
 */
function parseargs($args = [])
{
  global $argv;
  $parsed_args = [];

  $args = array_slice($args ?? $argv, 1);
  for ($i = 0; $i < count($args); $i++) {

    switch (substr_count($args[$i], "-", 0, 2)) {
      case 1:
        foreach (str_split(ltrim($args[$i], "-")) as $a) {
          $parsed_args[$a] = isset($parsed_args[$a]) ? $parsed_args[$a] + 1 : 1;
        }
        break;

      case 2:
        $parsed_args[ltrim(preg_replace("/=.*/", '', $args[$i]), '-')] = strpos($args[$i], '=') !== false ? substr($args[$i], strpos($args[$i], '=') + 1) : 1;
        break;

      default:
        $parsed_args['positional'][] = $args[$i];
    }
  }

  return $parsed_args;
}

/**
 * Logs a custom message to the error log.
 *
 * @param string $message The message to log.
 */
function custom_log($message)
{
  $timestamp = date('Y-m-d H:i:s');
  $logMessage = sprintf("%s - %s%s", $timestamp, $message, PHP_EOL);
  file_put_contents(ini_get('error_log'), $logMessage, FILE_APPEND);
}

/**
 * Validates an IP address.
 *
 * @param string $ip The IP address to validate.
 * @return bool True if the IP address is valid, false otherwise.
 */
function check_ip($ip = '')
{
  return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

/**
 * Resolves a hostname to an IP address.
 *
 * @param string $host The hostname to resolve.
 * @return string|false The resolved IP address or false on failure.
 */
function resolve_host_to_ip($host)
{
  $ip = gethostbyname($host);
  return $ip !== $host ? $ip : false;
}

/**
 * Checks internet connection by pinging a specified IP address.
 *
 * @param string $ip The IP address to ping. Defaults to Google's DNS server.
 * @return bool True if the connection is successful, false otherwise.
 */
function check_internet_connection($ip = '8.8.8.8')
{
  $status = null;

  // Ping the host to check network connectivity
  if (stripos(PHP_OS, 'WIN') === 0)
    exec("ping -n 1 -w 1 " . /*-W 20 */ escapeshellarg($ip), $output, $status);  // parse_url($ip, PHP_URL_HOST)
  else
    exec(APP_SUDO . (!is_file('/usr/bin/ping') ? '' : '/usr/bin/') . "ping -c 1 -W 1 " . escapeshellarg($ip), $output, $status); // var_dump(\$status)

  // If ping fails, try fsockopen as a fallback
  if ($status !== 0 && defined('APP_IS_ONLINE')) {
    $connection = @fsockopen('www.google.com', 80, $errno, $errstr, 10);
    if (!$connection) {
      $errors['APP_NO_INTERNET_CONNECTION'] = 'No internet connection.';
    } else {
      fclose($connection);
    }
  }

  return $status === 0;
}

// die(var_dump(check_internet_connection()) ? true : false);

/**
 * Check if a URL returns a specific HTTP status code.
 * 
 * @param string $url The URL to check. Default is 'http://8.8.8.8'.
 * @param int $statusCode The HTTP status code to check for. Default is 200.
 * @return bool True if the URL does not return the specified status, false otherwise.
 */
function check_http_status($url = 'http://8.8.8.8', $statusCode = 200)
{
  if (defined('APP_IS_ONLINE')) {
    if ($url !== 'http://8.8.8.8' && !preg_match('/^https?:\/\//', $url))
      $url = "http://$url";
    (!defined('APP_NO_INTERNET_CONNECTION'))
      or $headers = get_headers($url);
    return !empty($headers) && strpos($headers[0], (string) $statusCode) === false;
  } else
    return false;
  //return true; // Special case for the default URL or if not connected
}

/**
 * Retrieves the source URL for a given package from Packagist.
 *
 * @param string $vendor The vendor name for the package.
 * @param string $package The package name.
 *
 * @return string The source URL if it points to a GitHub repository; otherwise, the initial URL.
 */
function packagist_return_source($vendor, $package)
{
  $url = "https://packagist.org/packages/$vendor/$package";
  $initial_url = '';

  libxml_use_internal_errors(true); // Prevent HTML errors from displaying
  $dom = new DOMDocument(1.0, 'utf-8');
  if (check_http_status($url)) {
    $dom->loadHTML(file_get_contents($url));

    $anchors = $dom->getElementsByTagName('a');

    foreach ($anchors as $anchor) {
      if ($anchor->getAttribute('rel') == 'nofollow noopener external noindex ugc') {
        //echo $anchor->nodeValue;
        if ($anchor->nodeValue == 'Source')
          $initial_url = $anchor->getAttribute('href'); //  '/composer.json'
      }
    }
  }
  // $initial_url = "https://github.com/php-fig/log/tree/3.0.0/";

  if (preg_match('/^https:\/\/(?:www\.)?github.com\//', $initial_url)) {

    // Extract username, repository, and version from the initial URL
    $parts = explode("/", rtrim($initial_url, "/"));
    //dd($initial_url);
    //dd($parts);

    //$username = {$parts[3]};
    //$repository = $parts[4];
    //$version = $parts[6];

    //$blob_url = "https://github.com/$username/$repository/blob/$version/composer.json";
    return "https://raw.githubusercontent.com/{$parts[3]}/{$parts[4]}/{$parts[6]}/composer.json";

  }
  return $initial_url;
}

/**
 * Sanitizes the input by converting HTML entities, removing invalid UTF-8 characters, and encoding special characters.
 *
 * @param mixed $input The input to sanitize. Can be a string, array, or null. Default is an empty string.
 *
 * @return string|null The sanitized string. Returns null if the input is null.
 */
function htmlsanitize(mixed $input = '')
{
  if (is_array($input))
    $input = var_export($input, true);

  if (is_null($input))
    return;

  // Convert HTML entities to their corresponding characters
  $decoded = html_entity_decode($input, ENT_QUOTES, 'UTF-8');

  // Remove any invalid UTF-8 characters
  $cleaned = mb_convert_encoding($decoded, 'UTF-8', 'UTF-8');

  // Convert special characters to HTML entities
  $sanitized = htmlspecialchars($cleaned, ENT_QUOTES, 'UTF-8');

  return $sanitized;
}

//function htmlsanitize($argv = '') {
//  return html_entity_decode(mb_convert_encoding(htmlspecialchars($argv, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8' ), 'HTML-ENTITIES', 'utf-8');
//}


/**
 * This function takes in two parameters: $base and $path, which represent the base path and the path to be made relative, respectively.
 * It first detects the directory separator used in the base path, then splits both paths into arrays using that separator. It then removes the common base elements from the beginning of the path array, leaving only the difference.
 * Finally, it returns the relative path by joining the remaining elements in the path array using the separator detected earlier, with the separator prepended to the resulting string.

 * Return a relative path to a file or directory using base directory. 
 * When you set $base to /website and $path to /website/store/library.php
 * this function will return /store/library.php
 * @param mixed $base   A base path used to construct relative path. For example /website
 * @param mixed $path   A full path to file or directory used to construct relative path. For example /website/store/library.php
 * @return string
 */
function getRelativePath($base, $path)
{
  // Detect directory separator
  $separator = substr((string) $base, 0, 1);
  $base = array_slice(explode($separator, rtrim((string) $base, $separator)), 1);
  $path = array_slice(explode($separator, rtrim((string) $path, $separator)), 1);

  return $separator . (string) implode($separator, array_slice($path, count($base)));
}

/**
 * Resolves a symbolic link to its final target path.
 *
 * This function follows symbolic links until it reaches a non-link file or directory.
 *
 * @param string $linkFilename The path to the symbolic link to resolve.
 *
 * @return string The resolved path of the final target, which is not a symbolic link.
 */
function readlinkToEnd(string $linkFilename): string
{
  // Check if the provided path is a symbolic link
  if (!is_link($linkFilename)) {
    // If it's not a symbolic link, return the path as is
    return $linkFilename;
  }

  $final = $linkFilename;

  while (true) {
    // Read the target path of the current symbolic link
    $target = readlink($final);

    // Construct the new path from the target
    $final = (substr($target, 0, 1) == '/') ? $target : dirname($final) . '/' . $target;

    // Remove leading './' if present
    if (substr($final, 0, 2) == './') {
      $final = substr($final, 2);
    }

    // If the new path is not a symbolic link, return it
    if (!is_link($final)) {
      return $final;
    }
  }
}



// Function to scan specified directories without recursing
function scanDirectories($directories, $baseDir, &$organizedFiles)
{
  foreach ($directories as $directory) {
    $files = glob($baseDir . $directory . '/{*.php,.htaccess}', GLOB_BRACE); // Adjusted to scan only .php files at the top level of the directory
    foreach ($files as $file) {
      if (is_file($file)) {
        $relativePath = str_replace($baseDir, '', $file);
        // Add the relative path to the array if it is a .php file and not already present
        if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles)) {
          if ($relativePath == 'public/project.php' && !in_array('projects/index.php', $organizedFiles))
            $organizedFiles[] = 'projects/index.php';
          $organizedFiles[] = $relativePath;
        } else if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'htaccess' && !in_array($relativePath, $organizedFiles)) {
          $organizedFiles[] = $relativePath;
        }
      }
    }
  }
}

function customSort($array)
{
  // Separate files and directories
  $files = [];
  $directories = [];

  foreach ($array as $item) {
    if (preg_match('/^.\/(.*)/', $item))
      continue;
    if (preg_match('/^public\/(example|test|ui\_complete)(.*)/', $item))
      continue;
    // Check if the item contains directories
    if (strpos($item, '/') !== false) {
      $directories[] = $item;
    } else {
      $files[] = $item;
    }
  }

  // Sort directories and files
  sort($files); // Sort files in descending order
  sort($directories); // Sort directories in descending order

  // Merge files and directories
  $sortedArray = array_merge($files, $directories);

  return $sortedArray;
}

/*
if (!empty($env_arr = parse_ini_file($file, true, INI_SCANNER_TYPED ))) {
foreach ($env_arr as $key => $value) {

  if (is_array($value)) {
      foreach ($value as $k => $v) {
        $env_arr[$key][$k] = str_replace(['\'', '"'], '', var_export($v, true));
      }
  } else {
      // Check if the value is boolean true, and replace it with the string 'true'
      if ($value === true) {
        $env_arr[$key] = 'true';
      } else {
        $env_arr[$key] = str_replace(['\'', '"'], '', var_export($value, true));
      }
  }
}
*/

/**
 * This function is to replace PHP's extremely buggy realpath().
 * @param string The original path, can be relative etc.
 * @return string The resolved path, it might not exist.
 */
function truepath($path)
{
  // whether $path is unix or not
  $unipath = strlen($path) == 0 || $path[0] != '/';
  // attempts to detect if path is relative in which case, add cwd
  if (strpos($path, PATH_SEPARATOR) === false && $unipath)
    $path = getcwd() . DIRECTORY_SEPARATOR . $path;
  // resolve path parts (single dot, double dot and double delimiters)
  $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
  $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
  $absolutes = [];
  foreach ($parts as $part) {
    if ('.' == $part)
      continue;
    switch ('..') {
      case $part:
        array_pop($absolutes);
        break;
      default:
        $absolutes[] = $part;
        break;
    }
  }
  $path = implode(DIRECTORY_SEPARATOR, $absolutes);
  // resolve any symlinks
  if (file_exists($path) && linkinfo($path) > 0)
    $path = readlink($path);
  // put initial separator that could have been lost
  $path = !$unipath ? "/$path" : $path;
  return $path;
}


// Snippet from PHP Share: http://www.phpshare.org

function formatSizeUnits($bytes)
{
  if ($bytes >= 1073741824)
    $bytes = number_format($bytes / 1073741824, 2) . ' GB';
  elseif ($bytes >= 1048576)
    $bytes = number_format($bytes / 1048576, 2) . ' MB';
  elseif ($bytes >= 1024)
    $bytes = number_format($bytes / 1024, 2) . ' KB';
  elseif ($bytes > 1)
    $bytes = "$bytes bytes";
  elseif ($bytes == 1)
    $bytes .= ' byte';
  else
    $bytes = '0 bytes';
  return $bytes;
}

function convertToBytes($value)
{
  $unit = strtolower(substr($value, -1, 1));
  return (int) $value * pow(1024, array_search($unit, [1 => 'k', 'm', 'g']));
}

/**
 * Converts bytes into human readable file size.
 *
 * @param string $bytes
 * @return string human readable file size (2,87 ??)
 * @author Mogilev Arseny
 * @bug Does not handle 0 bytes
 */
function FileSizeConvert($bytes)
{
  $bytes = floatval($bytes);
  $arBytes = [
    0 => [
      "UNIT" => "TB",
      "VALUE" => pow(1024, 4)
    ],
    1 => [
      "UNIT" => "GB",
      "VALUE" => pow(1024, 3)
    ],
    2 => [
      "UNIT" => "MB",
      "VALUE" => pow(1024, 2)
    ],
    3 => [
      "UNIT" => "KB",
      "VALUE" => 1024
    ],
    4 => [
      "UNIT" => "B",
      "VALUE" => 1
    ],
  ];

  foreach ($arBytes as $arItem) {
    if ($bytes >= $arItem["VALUE"]) {
      $result = $bytes / $arItem["VALUE"];
      $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
      break;
    }
  }
  return $result;
}

function getElementsByClass(&$parentNode, $tagName, $className)
{
  $nodes = [];

  $childNodeList = $parentNode->getElementsByTagName($tagName);
  for ($i = 0; $i < $childNodeList->length; $i++) {
    $temp = $childNodeList->item($i);
    if (stripos($temp->getAttribute('class'), $className) !== false) {
      $nodes[] = $temp;
    }
  }

  return $nodes;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'php.php'; // 'constants.php';