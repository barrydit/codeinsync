<?php


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
        defined('APP_ROOT') or define('APP_ROOT', ''); // Define APP_ROOT if not already defined

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
        if (error_reporting() === 0) {
            // Silenced with @ — let PHP ignore it
            return false;
        }
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
        require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
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