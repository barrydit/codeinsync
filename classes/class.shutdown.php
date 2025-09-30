<?php

class Shutdown
{
    private static $instance = false;
    private static array $functions = [];
    private static bool $enabled = true;
    private static $shutdownMessage = null;

    private function __construct()
    {
        if (($_ENV['APP_DEBUG'] ?? true))
            error_log("Shutdown constructor called.");

        defined('APP_END') or define('APP_END', microtime(true));
        $this->initializeEnv(); // defines ENV_CHECKSUM inside
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
        if (!empty($_ENV))
            self::saveEnvToFile();
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

        // Merge sections (client overrides keys inside sections)
        foreach ($clientSections as $section => $values) {
            $mergedEnv[$section] = isset($globalSections[$section])
                ? array_replace($globalSections[$section], $values)
                : $values;
        }

        // Add remaining global sections that are not in the client
        foreach ($globalSections as $section => $values) {
            if (!isset($clientSections[$section])) {
                $mergedEnv[$section] = $values;
            }
        }

        // Merge client root keys (client root keys overwrite global)
        $mergedEnv = array_replace($mergedEnv, $clientRoot);

        return $mergedEnv;
    }

    private function initializeEnv(): void
    {
        defined('APP_ROOT') or define('APP_ROOT', ''); // Ensure APP_ROOT exists

        $globalPath = APP_PATH . '.env';
        $clientPath = APP_PATH . APP_ROOT . '.env';

        // ── NEW: define ENV_CHECKSUM (for APP_PATH . '.env') on construct ─────────
        if (!defined('ENV_CHECKSUM')) {
            $cs = self::checksumFile($globalPath);
            if ($cs !== null) {
                define('ENV_CHECKSUM', $cs);
            }
        }
        // (Optional) If you also want a client checksum available:
        // if (!defined('ENV_CHECKSUM_CLIENT')) {
        //     $csc = self::checksumFile($clientPath);
        //     if ($csc !== null) define('ENV_CHECKSUM_CLIENT', $csc);
        // }

        $mergedEnv = self::loadEnvFiles($globalPath, $clientPath);

        if ($mergedEnv === false || empty($mergedEnv)) {
            die(var_dump($mergedEnv)); // Malformed or empty .env
        }

        $_ENV = array_replace($mergedEnv, $_ENV);

        if (self::isNonEmptyFile($globalPath)) {
            self::backupEnvFile($globalPath);
        }
    }

    // ── NEW: small checksum helper ────────────────────────────────────────────────
    private static function checksumFile(string $filePath): ?string
    {
        return is_file($filePath) ? hash_file('sha256', $filePath) : null;
    }

    public static function parseIniFileWithSections(string $filePath): array
    {
        return file_exists($filePath)
            ? (parse_ini_file($filePath, true, INI_SCANNER_TYPED) ?: [])
            : [];
    }

    private static function isNonEmptyFile(string $filePath): bool
    {
        return is_file($filePath) && filesize($filePath) > 0;
    }

    private static function backupEnvFile(string $filePath): void
    {
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
        if (is_bool($value))
            return $value ? 'true' : 'false';
        if (is_null($value))
            return 'null';
        return (string) $value;
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (error_reporting() === 0) {
            return false; // silenced with @
        }
        self::triggerShutdown("(Custom) Error: [$errno] $errstr - $errfile:$errline");
        return true;
    }

    public static function handleException($exception)
    {
        $message = "(Custom) Exception: " . $exception->getMessage()
            . " in " . $exception->getFile()
            . " on line " . $exception->getLine();
        self::triggerShutdown($message);
    }

    public static function handleParseError()
    {
        $error = error_get_last();
        if ($error !== null) {
            $message = "(Custom) Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}";
            self::triggerShutdown($message);
        }
    }

    /**
     * NOTE: This now compares file checksums. If you still want to compare
     * $_ENV snapshots, keep your original method under a different name.
     */
    public static function hasEnvChanged(string $filePath = null): bool
    {
        $filePath = $filePath ?: (APP_PATH . '.env'); // default: global .env
        $original = ($filePath === APP_PATH . '.env' && defined('ENV_CHECKSUM'))
            ? ENV_CHECKSUM
            : self::checksumFile($filePath);

        $current = self::checksumFile($filePath);

        // If either is null (no file), treat as no change detectable
        if ($original === null || $current === null) {
            return false;
        }
        return $current !== $original;
    }

    public static function env_checksum(): ?string
    {
        return defined('ENV_CHECKSUM') ? ENV_CHECKSUM : null;
    }

    public static function saveEnvToFile()
    {
        $envFilePath = APP_PATH . APP_ROOT . '.env';

        $sections = [];
        $lines = [];

        // --- helper: list-array detection (PHP 7.4 compatible) ---
        $isListArray = static function ($a): bool {
            if (!is_array($a))
                return false;
            $i = 0;
            foreach ($a as $k => $_) {
                if ($k !== $i++)
                    return false;
            }
            return true;
        };

        // 1) Root scope: scalars and list-arrays (e.g., APP_BASE[])
        foreach ($_ENV as $key => $value) {
            if (is_array($value)) {
                if ($isListArray($value)) {
                    // e.g., APP_BASE[]="/mnt/c/www/app"
                    $lines[] = "";
                    $lines[] = "; Prefer INI array syntax for paths (replaces custom {[...]})";

                    //die(var_dump(preg_quote(APP_PATH)));
                    foreach ($value as $item) {
                        // IMPORTANT: still run through formatValue() to quote paths, etc.
                        $lines[] = "{$key}[]=" . self::formatValue(preg_replace('/^' . preg_quote(APP_PATH, '/') . '/', '', $item)); // substr($item, strlen(APP_PATH)
                    }
                } else {
                    // associative arrays become [SECTION] later
                    $sections[$key] = $value;
                }
            } else {
                // scalars at root

                if ($key == 'APP_UNAME')
                    $lines[] = "";

                if ($key == 'DEFAULT_UPDATE_NOTICE')
                    $lines[] = "";

                $lines[] = "{$key}=" . self::formatValue($value);

                if ($key == 'APP_PWORD')
                    $lines[] = "";

                //if ($key == 'APP_PUBLIC') $lines[] = "";

            }
        }

        // Add a blank line between root keys and sections if both exist
        if (!empty($lines) && !empty($sections)) {
            $lines[] = '';
        }

        // 2) Sections: support scalars and list-arrays within sections
        foreach ($sections as $section => $values) {
            $lines[] = "[{$section}]";
            foreach ($values as $k => $v) {
                if (is_array($v) && $isListArray($v)) {
                    foreach ($v as $item) {
                        $lines[] = "{$k}[]=" . self::formatValue($item);
                    }
                } else {
                    $lines[] = "{$k}=" . self::formatValue($v);
                }
            }
            // optional visual spacer between sections
            $lines[] = '';
        }

        // Trim the very last blank line only (do NOT trim other spaces)
        if (end($lines) === '') {
            array_pop($lines);
        }

        $newContents = implode(PHP_EOL, $lines) . PHP_EOL;

        $existing = is_file($envFilePath) ? file_get_contents($envFilePath) : null;
        if ($existing !== null && hash('sha256', $existing) === hash('sha256', $newContents)) {
            return; // no material change
        }

        if (file_put_contents($envFilePath, $newContents) === false) {
            error_log("Failed to write the current environment to $envFilePath");
        }
    }

    public static function getEnvJsonPath(): string
    {
        return APP_PATH . '.env.json';
    }

    public static function unlinkEnvjson(): void
    {
        die(var_dump(get_required_files()));
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

    private static function formatValue($value): string
    {
        if ($value === '' || $value === null)
            return '';
        if (is_bool($value))
            return $value ? 'true' : 'false';
        if (is_numeric($value))
            return $value;

        if (preg_match('/^\/.*\/[a-z]*$/i', $value) && !preg_match('/^(\/|[A-Za-z]:\\\\).*$/', $value)) {
            return "'$value'"; // regex
        }
        if (preg_match('/^(?!\/[A-Za-z0-9]).*\/[a-z]*$/i', $value)) {
            return "'$value'"; // regex
        }
        if (preg_match('/^\/.*\/[a-z]*$|^\/.*[^\/]$/i', $value)) {
            return "'$value'"; // regex-ish
        }
        if (preg_match('/^(\/|[A-Za-z]:\\\\).*$/', $value)) {
            return "\"$value\""; // paths
        }
        if (preg_match('/ /', $value)) {
            return "\"$value\""; // spaces
        }
        if (preg_match('/^\[.*\]$/', $value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return "{['" . implode("','", $decoded) . "']}";
            }
        }
        return $value;
    }

    private static function isJson($value): bool
    {
        if (!is_string($value) || is_numeric($value))
            return false;

        $trimmed = trim($value);
        if (
            (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) ||
            (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))
        ) {
            $tempValue = preg_replace("/'([^']+)'/", '\'$1\'', $trimmed);
            $tempValue = "\"$tempValue\"";
            json_decode($tempValue);
            return json_last_error() === JSON_ERROR_NONE;
        }
        return false;
    }
}