<?php
declare(strict_types=1);

use CodeInSync\Application\Runtime\Shutdown;

// minimal web entry
//if (is_file(dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'classes/class.shutdown.php'))
//  require_once __DIR__ . DIRECTORY_SEPARATOR . '../classes/class.shutdown.php';

// Helpers first — MUST be vendor-free 
//require_once dirname(__DIR__) . '/classes/class.pathutils.php';
//require_once dirname(__DIR__) . '/classes/class.queryurl.php';

/**/
function get_str(string $k): ?string
{
  return isset($_GET[$k]) ? trim((string) $_GET[$k]) : null;
}

function base_val(string $key): string
{
  $v = APP_BASE[$key] ?? '';
  return rtrim($v, '/') . '/';
}

function norm_path(string $p): string
{
  // collapse duplicate slashes; keep leading / if present
  $p = preg_replace('#/+#', '/', $p);
  return $p;
}


// Sanitizers you use:
function clean_client($s)
{
  return preg_replace('~[^a-z0-9._,\- ]~i', '', (string) $s);
}
function clean_domain($s)
{
  return preg_replace('~[^a-z0-9.\- ]~i', '', (string) $s);
}
function clean_project($s)
{
  return preg_replace('~[^a-z0-9._\- ]~i', '', (string) $s);
}
function clean_path($s)
{
  return preg_replace('~[^a-z0-9._\- \/]~i', '', (string) $s);
}

if (!function_exists('ctx')) {
  function ctx(string $k, $default = null)
  {
    return $GLOBALS['__ctx'][$k] ?? $default;
  }
}

if (!function_exists('load_if_file')) {
  function load_if_file(string $path): void
  {
    if (is_file($path))
      require_once $path;
  }
}

if (!function_exists('app_error_handler')) {
  // Custom error handler
  /**
   * Summary of app_error_handler
   * @param mixed $errno
   * @param mixed $errstr
   * @param mixed $errfile
   * @param mixed $errline
   * @return bool
   */
  function app_error_handler(int $errno, string $errstr, ?string $errfile = null, ?int $errline = null): bool
  {
    /*
        global $errors;
        !defined('APP_ERROR') and define('APP_ERROR', true); // $hasErrors = true;
        $errors['FUNCTIONS'] = 'functions.php failed to load. Therefore function dd() does not exist (yet).';

        foreach ([E_ERROR => 'Error', E_WARNING => 'Warning', E_PARSE => 'Parse Error', E_NOTICE => 'Notice', E_CORE_ERROR => 'Core Error', E_CORE_WARNING => 'Core Warning', E_COMPILE_ERROR => 'Compile Error', E_COMPILE_WARNING => 'Compile Warning', E_USER_ERROR => 'User Error', E_USER_WARNING => 'User Warning', E_USER_NOTICE => 'User Notice', E_STRICT => 'Strict Notice', E_RECOVERABLE_ERROR => 'Recoverable Error', E_DEPRECATED => 'Deprecated', E_USER_DEPRECATED => 'User Deprecated',] as $key => $value) {
          if ($errno == $key) {
            $errors[$key] = "$key => $value\n";
            $errors[] = "$value: $errstr in $errfile on line $errline\n";
            break;
          }
        }
        var_dump($errors);
        return false;
    */

    if (!(error_reporting() & $errno))
      return false;
    $msg = sprintf('[%s] %s in %s:%d', $errno, $errstr, $errfile ?? '?', $errline ?? 0);
    if (defined('APP_DEBUG') && APP_DEBUG) {
      if (PHP_SAPI === 'cli')
        fwrite(STDERR, $msg . PHP_EOL);
      else
        echo '<pre style="color:#f66">' . $msg . '</pre>';
    }
    return true; // handled
  }
}

function app_context(): string
{
  return defined('APP_CONTEXT') ? APP_CONTEXT : (PHP_SAPI === 'cli' ? 'cli' : 'web');
}

function highlightVersionDiff($installed, $latest)
{
  $installedParts = explode('.', $installed);
  $latestParts = explode('.', $latest);
  $result = '';

  $diffFound = false;
  for ($i = 0; $i < max(count($installedParts), count($latestParts)); $i++) {
    $installedPart = $installedParts[$i] ?? '';
    $latestPart = $latestParts[$i] ?? '';

    if (!$diffFound && $installedPart === $latestPart) {
      $result .= $latestPart;
    } else {
      if (!$diffFound) {
        $diffFound = true;
        $result .= '<span class="update" style="color: green; cursor: pointer;">';
      }
      $result .= $latestPart;
    }

    if ($i < max(count($installedParts), count($latestParts)) - 1) {
      $result .= '.';
    }
  }

  if ($diffFound) {
    $result .= '</span>';
  }

  return $result;
}

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



/**
 * Dumps a variable with formatting and optionally stops execution.
 *
 * @param mixed $param The variable to be dumped. Default is null.
 * @param bool $die Whether to stop execution after dumping the variable. Default is true.
 * @param bool $debug Whether to include debug information in the output. Default is true.
 *
 * @return void Returns void if execution is stopped; otherwise, returns the result of var_dump().



function dump(mixed ...$vars): mixed
    {
        if (!$vars) {
            VarDumper::dump(new ScalarStub('??'));

            return null;
        }

        if (array_key_exists(0, $vars) && 1 === count($vars)) {
            VarDumper::dump($vars[0]);
            $k = 0;
        } else {
            foreach ($vars as $k => $v) {
                VarDumper::dump($v, is_int($k) ? 1 + $k : $k);
            }
        }

        if (1 < count($vars)) {
            return $vars;
        }

        return $vars[$k];
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        if (!\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) && !headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        if (!$vars) {
            VarDumper::dump(new ScalarStub('??'));

            exit(1);
        }

        if (array_key_exists(0, $vars) && 1 === count($vars)) {
            VarDumper::dump($vars[0]);
        } else {
            foreach ($vars as $k => $v) {
                VarDumper::dump($v, is_int($k) ? 1 + $k : $k);
            }
        }

        exit(1);
    }
}
 */

function dd(mixed $param = null, bool $die = true, bool $debug = true): void
{
  require_once __DIR__ . DIRECTORY_SEPARATOR . '../config/constants.env.php';
  // Define start time if not defined
  !defined('APP_START') and define('APP_START', $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));

  // Calculate execution time
  $execTime = round((defined('APP_END') ? APP_END : microtime(true)) - APP_START, 3);
  $memoryUsage = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
  $output = "Execution time: <b>{$execTime}</b> secs | Memory usage: <b>{$memoryUsage}</b> MB" . PHP_EOL;

  // Format the dump output
  $isCLI = PHP_SAPI === 'cli';
  $dump = is_array($param) || is_object($param)
    ? json_encode($param, JSON_PRETTY_PRINT)
    : (is_resource($param) ? 'Resource' : var_export($param, true));

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
    if (($_ENV['APP_DEBUG'] ?? true) && $debug) {
      error_log($formattedOutput);
    }
  }
}

if (!function_exists('dd')) {
  function dd(...$vars): never
  {
    foreach ($vars as $v) {
      if (PHP_SAPI === 'cli') {
        fwrite(STDERR, print_r($v, true) . PHP_EOL);
      } else {
        echo '<pre style="padding:8px;background:#111;color:#eee;border-radius:8px;">'
          . htmlspecialchars(var_export($v, true))
          . '</pre>';
      }
    }
    exit(1);
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
  error_log($logMessage);
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
 * Quiet, cached connectivity check.
 * - Preserves your original logic & commented TCP probe.
 * - Always returns bool.
 */
function check_internet_connection(?string $host = null, int $timeout = 2, bool $quiet = true, int $ttl = 5): bool
{
  static $cache = ['ts' => 0, 'ok' => false];

  // Use cached value within TTL
  if (time() - $cache['ts'] < $ttl) {
    return $cache['ok'];
  }

  $host = $host ?: 'www.google.com';

  // Normalize if URL
  if (filter_var($host, FILTER_VALIDATE_URL)) {
    $parsed = parse_url($host, PHP_URL_HOST);
    if ($parsed) {
      $host = $parsed;
    }
  }

  $status = false;
  $prevHandler = null;

  // Local silencer (helps even if a global error handler ignores @)
  if ($quiet) {
    $prevHandler = set_error_handler(static function () {
      return true; // swallow warnings/notices here
    });
  }

  try {
    // Windows: use ping (your original logic)
    if (stripos(PHP_OS, 'WIN') === 0) {
      $output = [];
      $exitCode = 1;
      exec("ping -n 1 -w 1000 " . escapeshellarg($host), $output, $exitCode);
      $status = ($exitCode === 0);
    } else {
      // Unix-like: (kept your TCP probe commented as in your code)
      /*
      $connection = @stream_socket_client(
          "tcp://$host:80",
          $errno,
          $errstr,
          $timeout,
          STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT
      );
      if ($connection !== false) {
          stream_set_blocking($connection, false);
          fclose($connection);
          $status = true;
      }
      */
    }

    // Fallback attempts (your sequence, with small guards)
    if (!$status) {
      // Try resolving DNS first (use your resolver if present, else gethostbyname)
      if (function_exists('resolve_host_to_ip')) {
        $resolved = @resolve_host_to_ip('google.com');
        if ($resolved !== false && !empty($resolved)) {
          $cache = ['ts' => time(), 'ok' => true];
          return true;
        }
      } else {
        $ip = @gethostbyname('google.com');
        if ($ip && $ip !== 'google.com') {
          $cache = ['ts' => time(), 'ok' => true];
          return true;
        }
      }

      // UDP DNS-style probe (Google DNS)
      $dnsConnection = @stream_socket_client("udp://8.8.8.8:53", $errno, $errstr, $timeout);
      if ($dnsConnection) {
        fclose($dnsConnection);
        $cache = ['ts' => time(), 'ok' => true];
        return true;
      }

      // Final TCP fallback (your original)
      $fallback = @stream_socket_client("tcp://www.google.com:80", $errno, $errstr, 3);
      if ($fallback) {
        fclose($fallback);
        $cache = ['ts' => time(), 'ok' => true];
        return true;
      }
    }

    // If we got here, no luck
    $cache = ['ts' => time(), 'ok' => false];
    return false;

  } finally {
    if ($prevHandler !== null) {
      set_error_handler($prevHandler); // restore previous handler
    }
  }
}

/**
 * Checks internet connection by pinging a specified IP address.
 *
 * @param string $ip The IP address to ping. Defaults to Google's DNS server.
 * @return bool True if the connection is successful, false otherwise.
 */
/*
function check_internet_connection(?string $host = null): bool
{
  // Default to a reliable web host
  $host = $host ?: 'www.google.com';

  // Normalize if URL
  if (filter_var($host, FILTER_VALIDATE_URL)) {
    $host = parse_url($host, PHP_URL_HOST);
  }

  $port = 80;
  $timeout = 2;
  $status = false;

  // Windows: use ping
  if (stripos(PHP_OS, 'WIN') === 0) {
    exec("ping -n 1 -w 1000 " . escapeshellarg($host), $output, $exitCode);
    $status = ($exitCode === 0);
  } else {
    // Unix-like: use stream_socket_client on TCP port 80

    $connection = @stream_socket_client(
      "tcp://$host:$port",
      $errno,
      $errstr,
      $timeout,
      STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT
    );

    if ($connection !== false) {
      stream_set_blocking($connection, false); // don't block
      fclose($connection);
      $status = true; // connection started, not guaranteed complete
    }
  }

  // Fallback attempts
  if (!$status) {
    // Try resolving DNS first
    if (resolve_host_to_ip('google.com') !== false) {
      return true;
    }

    // Try UDP DNS-style probe (Google DNS)
    $dnsConnection = @stream_socket_client("udp://8.8.8.8:53", $errno, $errstr, $timeout);
    if ($dnsConnection) {
      fclose($dnsConnection);
      return true;
    }

    // Final TCP fallback
    $fallback = @stream_socket_client("tcp://www.google.com:80", $errno, $errstr, 3);
    if ($fallback) {
      fclose($fallback);
      $status = true;
    }
  }

  return $status;
}
*/
// die(var_dump(check_internet_connection()) ? true : false);

/**
 * Check if a URL returns a specific HTTP status code.
 * 
 * @param string $url The URL to check. Default is 'http://8.8.8.8'.
 * @param int $statusCode The HTTP status code to check for. Default is 200.
 * @return bool True if the URL does not return the specified status, false otherwise.
 */

function check_http_status($url = 'http://8.8.8.8', $expectedStatus = [0 => 200]): bool
{
  if (!preg_match('/^https?:\/\//i', $url)) {
    $url = "http://$url";
  }

  $headers = @get_headers($url, true); // 1 = associative array for headers
  if ($headers === false || !isset($headers[0])) {
    return false; // No response or DNS failure
  }

  preg_match('/\d{3}/', $headers[0], $matches);
  $actualStatus = $matches[0] ?? null;

  return ((int) $actualStatus === (int) $expectedStatus);
}

function build_url_from_parts(array $parts): string
{
  $url = '';

  // Scheme
  if (!empty($parts['scheme'])) {
    $url .= $parts['scheme'] . '://';
  }

  // User and pass
  if (!empty($parts['user'])) {
    $url .= $parts['user'];
    if (!empty($parts['pass'])) {
      $url .= ':' . $parts['pass'];
    }
    $url .= '@';
  }

  // Host
  if (!empty($parts['host'])) {
    $url .= $parts['host'];
  }

  // Port
  if (!empty($parts['port']) && $parts['port'] != 80 && $parts['port'] != 443) {
    $url .= ':' . $parts['port'];
  }

  // Path
  if (!empty($parts['path'])) {
    $url .= '/' . ltrim($parts['path'], '/');
  }

  // Query string
  if (!empty($parts['query'])) {
    $url .= '?' . $parts['query'];
  }

  // Fragment
  if (!empty($parts['fragment'])) {
    $url .= '#' . $parts['fragment'];
  }

  return $url;
}

function check_http_status_curl(string $url, int $expectedStatus = 200, array $headers = []): bool
{
  if (!preg_match('/^https?:\/\//i', $url)) {
    $url = "http://$url";
  }

  $ch = curl_init($url);

  // Set default headers and merge with any custom ones
  $defaultHeaders = [
    'User-Agent: CodeInSync/1.0 (https://github.com/barrydit/codeinsync)'
  ];
  curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));

  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_NOBODY => true,              // We only care about the headers
    CURLOPT_TIMEOUT => 5,                // Fail fast if no response
    CURLOPT_FOLLOWLOCATION => true,      // Follow redirects (301/302)
    CURLOPT_HEADER => true,              // Include headers in the output
    CURLOPT_SSL_VERIFYPEER => true,      // Use proper SSL checks
  ]);

  curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  curl_close($ch);

  return ($httpCode === $expectedStatus);
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
/**
 * Scan top-level files in the given directories under $baseDir.
 * - Indexes only *.php and .htaccess
 * - Avoids duplicates
 * - Safe if inputs are null/strings
 */
function scanDirectories($directories, string $baseDir, array &$organizedFiles): void
{
  // --- Safety: normalize inputs
  if (!is_array($directories)) {
    $directories = $directories ? [$directories] : [];
  }
  if (!isset($organizedFiles) || !is_array($organizedFiles)) {
    $organizedFiles = [];
  }

  // Ensure base ends with a separator and points at /mnt/c/www/
  $baseDir = rtrim($baseDir, "/\\") . DIRECTORY_SEPARATOR;

  foreach ($directories as $directory) {
    if ($directory === null || $directory === '') {
      continue;
    }

    // Normalize dir path (allow "../projects" style entries)
    $dir = rtrim($baseDir . $directory, "/\\");
    // Glob can return false; coalesce to []
    $files = glob($dir . '/*.{php}', GLOB_BRACE) ?: [];

    // Add .htaccess explicitly (pathinfo can't detect "htaccess" extension for dotfiles)
    $htaccessPath = $dir . DIRECTORY_SEPARATOR . '.htaccess';
    if (is_file($htaccessPath)) {
      $relative = ltrim(str_replace($baseDir, '', $htaccessPath), "/\\");
      if (!in_array($relative, $organizedFiles, true)) {
        $organizedFiles[] = $relative;
      }
    }

    foreach ($files as $file) {
      if (!is_file($file)) {
        continue;
      }

      $relativePath = ltrim(str_replace($baseDir, '', $file), "/\\");
      // Only .php at top level (glob already filtered)
      if (substr($relativePath, -4) === '.php' && !in_array($relativePath, $organizedFiles, true)) {
        // Special case: if we see public/project.php, also include projects/index.php once
        if (
          $relativePath === 'public/project.php'
          && defined('APP_BASE')
          && is_array(APP_BASE)
          && !empty(APP_BASE['projects'])
        ) {
          $projIndex = rtrim(APP_BASE['projects'], "/\\") . '/index.php';
          if (!in_array($projIndex, $organizedFiles, true)) {
            $organizedFiles[] = $projIndex;
          }
        }

        $organizedFiles[] = $relativePath;
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

function run_code(?string $runtime, string $code, array $options = []): string
{
  $runtime ??= 'php';

  // Check if the runtime exists and has a valid 'run' callable
  if (isset($GLOBALS['runtimes'][$runtime]) && is_callable($GLOBALS['runtimes'][$runtime]['run'])) {
    $runner = $GLOBALS['runtimes'][$runtime];
    $result = $runner['run']($code, $options);
  }
  return (string) ($result ?? '');
}

