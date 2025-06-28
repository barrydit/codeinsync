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
    error_log("Shutdown constructor called.");
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

    // Separate root (non-section) keys and sections
    $globalRoot = array_filter($globalEnv, 'is_scalar');
    $clientRoot = array_filter($clientEnv, 'is_scalar');
    $globalSections = array_filter($globalEnv, 'is_array');
    $clientSections = array_filter($clientEnv, 'is_array');

    // Start with the global root keys
    $mergedEnv = $globalRoot;

    // Merge sections
    foreach ($clientSections as $section => $values) {
      $mergedEnv[$section] = isset($globalSections[$section]) ? array_replace($globalSections[$section], $values) : $values; // array_merge
    }

    // Add remaining global sections that are not in the client
    foreach ($globalSections as $section => $values) {
      if (!isset($clientSections[$section])) {
        $mergedEnv[$section] = $values;
      }
    }

    // Merge client root keys (client root keys overwrite global)
    $mergedEnv = array_merge($mergedEnv, $clientRoot);

    return $mergedEnv;
  }

  /*
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
  */
  private function initializeEnv(): void
  {
    $globalPath = APP_PATH . '.env';
    $clientPath = APP_PATH . APP_ROOT . '.env';

    $mergedEnv = self::loadEnvFiles($globalPath, $clientPath);

    if ($mergedEnv === false || empty($mergedEnv))
      dd($mergedEnv); //$errors['malformed'] = 'Malformed .env file.';

    $_ENV = array_replace($mergedEnv, $_ENV); // array_merge

    if (self::isNonEmptyFile($globalPath)) {
      self::backupEnvFile($globalPath);
    }
  }

  public static function parseIniFileWithSections(string $filePath): array
  {
    return file_exists($filePath) ? (parse_ini_file($filePath, true, INI_SCANNER_TYPED) ?: []) : []; // INI_SCANNER_RAW 
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
    file_put_contents("$filePath.bck", $backupContent);
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

  /**
   * Summary of handleError
   * @param mixed $errno
   * @param mixed $errstr
   * @param mixed $errfile
   * @param mixed $errline
   * @return bool
   */
  public static function handleError($errno, $errstr, $errfile, $errline)
  {
    //echo 'Does this work? handleError()';
    self::triggerShutdown("Error: [$errno] $errstr - $errfile:$errline");
    return true; // To prevent PHP's internal error handler from running
  }

  /**
   * Summary of handleException
   * @param mixed $exception
   * @return void
   */
  public static function handleException($exception)
  {
    //echo "Does this work? handleException()";
    $message = "Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    self::triggerShutdown($message);
  }

  /**
   * Summary of handleParseError
   * @return void
   */

  public static function handleParseError()
  {
    $error = error_get_last();
    //echo 'Does this work? handleParseError()';
    if ($error !== null) {
      $message = "Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}";
      self::triggerShutdown($message);
    }

  }
  public static function hasEnvChanged(): bool
  {
    return hash('sha256', json_encode($_ENV, JSON_UNESCAPED_SLASHES)) !== ENV_CHECKSUM;
  }

  public static function env_checksum(): ?string
  {
    return defined('ENV_CHECKSUM') ? ENV_CHECKSUM : null;
  }

  /**
   * Saves the current state of $_ENV to a .env file.
   *
   * Note: This is a simple implementation. If your .env file includes comments,
   * blank lines, or complex variable formats, you may need a more robust solution.
   */
  public static function saveEnvToFile()
  {
    //if ($hash = !self::hasEnvChanged()) {
    //  return; // No changes, skip saving
    //}
    if (!defined('ENV_CHECKSUM')) {
      // Optionally log, ignore, or set a fallback
      return;
    }

    $hash = hash('sha256', json_encode($_ENV, JSON_UNESCAPED_SLASHES));

    if ($hash === self::env_checksum()) {
      return; // No changes, skip saving
    }

    //dd(ENV_CHECKSUM . ' (ENV_CHECKSUM) == ' . $hash . ' (hash)', false);

    $envFilePath = APP_PATH . APP_ROOT . '.env';
    $sections = [];
    $lines = [];

    foreach ($_ENV as $key => $value) {
      if (is_array($value)) {
        $sections[$key] = $value;
      } else {
        $lines[] = "$key=" . self::formatValue($value);
      }
    }

    foreach ($sections as $section => $values) {
      $lines[] = "[$section]";
      foreach ($values as $key => $value) {
        $lines[] = "$key=" . self::formatValue($value);
      }
    }

    $contents = implode(PHP_EOL, $lines) . PHP_EOL;

    //dd($_ENV);

    if (file_put_contents($envFilePath, $contents) === false) {
      error_log("Failed to write the current environment to $envFilePath");
    }
  }


  public static function unlinkEnvjson(): void
  {
    $envJsonPath = APP_PATH . APP_ROOT . '.env.json';
    if (file_exists($envJsonPath)) {
      if (!unlink($envJsonPath)) {
        error_log("Failed to delete the file: $envJsonPath");
      }
    } else {
      error_log("File does not exist: $envJsonPath");
    }
  }

  /**
   * Formats values:
   * - Wraps directory paths in double quotes
   * - Wraps JSON strings in double quotes
   * - Leaves numbers untouched
   *
   * @param mixed $value
   * @return string
   */


  private static function formatValue($value): string
  {
    // Handle empty values explicitly
    if ($value === '' || $value === null) {
      return '';
    }

    // Handle booleans explicitly (true/false should remain unquoted)
    if (is_bool($value)) {
      return $value ? 'true' : 'false';
    }

    // Keep numbers unquoted
    if (is_numeric($value)) {
      return $value;
    }

    if (preg_match('/^\/.*\/[a-z]*$/i', $value) && !preg_match('/^(\/|[A-Za-z]:\\\\).*$/', $value)) {
      return "'$value'"; // Use single quotes for regex
    }

    // Ensure regex patterns are correctly wrapped in single quotes, but not paths
    if (preg_match('/^(?!\/[A-Za-z0-9]).*\/[a-z]*$/i', $value)) {
      return "'$value'"; // Use single quotes for regex
    }

    if (preg_match('/^\/.*\/[a-z]*$|^\/.*[^\/]$/i', $value)) {
      return "'$value'"; // Use single quotes for regex
    }

    // Keep paths quoted (Linux `/path/to/dir` or Windows `C:\path\to\dir`)
    if (preg_match('/^(\/|[A-Za-z]:\\\\).*$/', $value)) {
      return "\"$value\""; // Always use double quotes for paths
    }

    // Handle strings with spaces
    if (preg_match('/ /i', $value)) {
      return "\"$value\""; // Use double quotes for strings with spaces
    }

    // Special handling for array-like strings (e.g., APP_BASE)
    if (preg_match('/^\[.*\]$/', $value)) {
      $decoded = json_decode($value, true);
      if (is_array($decoded)) {
        return "{['" . implode("','", $decoded) . "']}";
      }
    }

    // Return unquoted strings as is
    return $value; // Return as-is for all other values
  }

  /**
   * Determines if a string is valid JSON (excluding numbers)
   *
   * @param string $value
   * @return bool
   */
  private static function isJson($value): bool
  {
    if (!is_string($value) || is_numeric($value)) {
      return false; // Ignore numbers
    }

    $trimmed = trim($value);

    // Ensure it's enclosed in {} or []
    if (
      (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) ||
      (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))
    ) {

      // Ensure proper double quotes for JSON keys and values
      $tempValue = preg_replace("/'([^']+)'/", '\'$1\'', $trimmed);

      $tempValue = "\"$tempValue\"";

      json_decode($tempValue);
      return json_last_error() === JSON_ERROR_NONE;
    }

    return false;
  }
}
// Register custom error and exception handlers
set_error_handler([Shutdown::class, 'handleError']);
set_exception_handler([Shutdown::class, 'handleException']);
register_shutdown_function(function () {
  //Shutdown::triggerShutdown('');  // 
  if (!empty($_ENV))
    Shutdown::saveEnvToFile();
  Shutdown::unlinkEnvjson();

  //Shutdown::handleParseError();
});


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
    $connection = @stream_socket_client("tcp://$host:$port", $errno, $errstr, $timeout);
    if ($connection) {
      fclose($connection);
      $status = true;
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

  $headers = @get_headers($url, 1); // 1 = associative array for headers
  if ($headers === false || !isset($headers[0])) {
    return false; // No response or DNS failure
  }

  preg_match('/\d{3}/', $headers[0], $matches);
  $actualStatus = $matches[0] ?? null;

  return ((int) $actualStatus === (int) $expectedStatus);
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
function scanDirectories($directories, $baseDir, &$organizedFiles)
{
  foreach ($directories as $directory) {
    $files = glob($baseDir . $directory . '/{*.php,.htaccess}', GLOB_BRACE); // Adjusted to scan only .php files at the top level of the directory
    foreach ($files as $file) {
      if (is_file($file)) {
        $relativePath = str_replace($baseDir, '', $file);
        // Add the relative path to the array if it is a .php file and not already present
        if (pathinfo($relativePath, PATHINFO_EXTENSION) == 'php' && !in_array($relativePath, $organizedFiles)) {
          if ($relativePath == 'public/project.php' && !in_array(APP_BASE['projects'] . 'index.php', $organizedFiles))
            $organizedFiles[] = APP_BASE['projects'] . 'index.php';
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

function run_code(?string $runtime, string $code, array $options = []): string
{
  $runtime ??= 'php';

  // Check if the runtime exists and has a valid 'run' callable
  if (isset($GLOBALS['runtimes'][$runtime]) && is_callable($GLOBALS['runtimes'][$runtime]['run'])) {
    $runner = $GLOBALS['runtimes'][$runtime];
    $result = $runner['run']($code, $options);
    return (string) ($result ?? '');
  }

}

