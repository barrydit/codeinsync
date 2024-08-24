<?php

/**
 * Summary of Shutdown
 */
class Shutdown
{
    private static $instance = false;
    /**
     * Summary of functions
     * @var 
     */
    private static $functions;
    private static $enabled = true;
    private static $shutdownMessage = null;

    /**
     * Summary of __construct
     */
    private function __construct()
    {
        error_log("Shutdown constructor called."); // Log message to error log
        self::$functions = [];
        defined('APP_END') or define('APP_END', microtime(true));
        $this->initializeEnv();
    }

    /**
     * Summary of initializeEnv
     * @return void
     */
    private static function initializeEnv()
    {
        $iniString = '';
    
        // Backup the current .env file if it's not empty
        if (filesize('.env') > 0) {
            $envContent = file_get_contents('.env');
    
            // Parse the .env content and filter out GITHUB OAUTH_TOKEN
            $parsedEnv = parse_ini_string($envContent, true);
            if (isset($parsedEnv['GITHUB']) && isset($parsedEnv['GITHUB']['OAUTH_TOKEN'])) {
                $parsedEnv['GITHUB']['OAUTH_TOKEN'] = null;
            }
    
            $backupEnvContent = '';
            foreach ($parsedEnv as $section => $data) {
                if (is_array($data)) {
                    $backupEnvContent .= "[$section]\n";
                    foreach ($data as $key => $value) {
                        $value = self::convertBooleanToString($value);
                        $value = self::processNestedValue($value);
                        $backupEnvContent .= "$key = $value\n";
                    }
                } else {
                    $data = self::convertBooleanToString($data);
                    $data = self::processNestedValue($data);
                    $backupEnvContent .= "$section = $data\n";
                }
            }
    
            file_put_contents('.env.bck', $backupEnvContent);
        }
    
        // Process the main .env file
        $file = APP_PATH . APP_ROOT . '.env';
    
        if (is_file($file)) {
            $_ENV = array_intersect_key_recursive($_ENV, parse_ini_file_multi($file));
    
            if (!empty($_ENV)) {
                foreach ($_ENV as $key => $value) {
                    // Convert boolean values to strings
                    $value = self::convertBooleanToString($value);
                    if (is_array($value)) {
                        $iniString .= "[$key]\n";
                        foreach ($value as $nestedKey => $nestedValue) {
                            $nestedValue = self::processNestedValue($nestedValue);
                            $iniString .= "$nestedKey = $nestedValue\n";
                        }
                    } else {
                        $value = self::processNestedValue($value);
                        $iniString .= "$key = $value\n";
                    }
                }
            }
    
            file_put_contents($file, $iniString);
        } else {
            //file_put_contents($file, $envContent);
        }
    }

    /**
     * Summary of convertBooleanToString
     * @param mixed $value
     * @return mixed
     */
    private static function convertBooleanToString($value)
    {
        if ($value === true || $value === false || is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return $value;
    }

    /**
     * Summary of processNestedValue
     * @param mixed $value
     * @return string
     */
    private static function processNestedValue($value)
    {
        if (is_string($value) && !is_numeric($value) && preg_match('/^\/.*\/[a-z]*$/i', $value)) {
            return "\"$value\"";
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return addslashes($value);
    }

    /**
     * Summary of triggerShutdown
     * @param mixed $message
     * @return void
     */
    public static function triggerShutdown($message)
    {
        self::$shutdownMessage = $message;
        register_shutdown_function([self::class, 'onShutdown']);
    }

    /**
     * Summary of instance
     * @return bool|Shutdown
     */
    public static function instance()
    {
        if (self::$instance == false) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Summary of onShutdown
     * @return void
     */
    public static function onShutdown()
    {
        if (self::$enabled && self::$shutdownMessage !== null) {
            if (is_callable(self::$shutdownMessage)) {
                echo call_user_func(self::$shutdownMessage);
            } else {
                echo self::$shutdownMessage;
            }
        } elseif (!self::$enabled) {
            foreach (self::$functions as $fnc) {
                $fnc(self::$shutdownMessage);
            }
        }
    }

    /**
     * Summary of create
     * @return Shutdown
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Summary of setInstance
     * @param mixed $instance
     * @return void
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

    /**
     * Summary of setFunctions
     * @param mixed $functions
     * @return Shutdown
     */
    public function setFunctions($functions): self
    {
        self::$functions = $functions;
        return $this;
    }

    /**
     * Summary of getEnabled
     * @return mixed
     */
    public static function getEnabled()
    {
        return self::$enabled;
    }

    /**
     * Summary of setEnabled
     * @param mixed $enabled
     * @return bool|Shutdown
     */
    public static function setEnabled($enabled)
    {
        self::$enabled = $enabled;
        return isset(self::$instance) ? static::instance() : self::instance();
    }

    public static function setShutdownMessage($message): self
    {
        self::$shutdownMessage = $message;
        return isset(self::$instance) ? static::instance() : self::instance();
    }

    public function shutdown($die = true)
    {
        if (!self::$enabled) {
            foreach (self::$functions as $fnc) {
                $fnc(self::$shutdownMessage);
            }
        }
        $message = is_callable(self::$shutdownMessage) ? call_user_func(self::$shutdownMessage) : self::$shutdownMessage;
        if ($die == true) {
            exit($message);
        }
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        self::triggerShutdown("Error: [$errno] $errstr - $errfile:$errline");
        return true; // To prevent PHP's internal error handler from running
    }

    public static function handleException($exception)
    {
        self::triggerShutdown("Exception: " . $exception->getMessage());
    }

    public static function handleParseError()
    {
        $error = error_get_last();
        if ($error !== null) {
            self::triggerShutdown("Fatal error: " . $error['message']);
        }
    }
}

// Register custom error and exception handlers
set_error_handler([Shutdown::class, 'handleError']);
set_exception_handler([Shutdown::class, 'handleException']);
register_shutdown_function([Shutdown::class, 'handleParseError']);

/**
 * Dumps a variable with formatting and optionally stops execution.
 *
 * @param mixed $param The variable to be dumped. Default is null.
 * @param bool $die Whether to stop execution after dumping the variable. Default is true.
 * @param bool $debug Whether to include debug information in the output. Default is true.
 *
 * @return void Returns void if execution is stopped; otherwise, returns the result of var_dump().
 */
function dd($param = null, bool $die = true, bool $debug = true): void{
    // Prepare the output with execution time information
    $output = ($debug == true && !defined('APP_END') ? 
              'Execution time: <b>'  . round(microtime(true) - APP_START, 3) . '</b> secs' : 
              'Execution time: <b>'  . round(APP_END - APP_START, 3) . '</b> secs'
              ) . "<br />\n";

    if ($die) {
        // If $die is true, set a shutdown function to output the dump and stop execution
        Shutdown::setEnabled(false)->setShutdownMessage(function() use ($param, $output) {
            return '<pre><code>' . str_replace(['\'', '"'], '', var_export($param, true)) . '</code></pre>' . $output;
        })->shutdown();
    } else {
        // If $die is false, output the dump and continue execution
        var_dump('<pre><code>' . str_replace(['\'', '"'], '', var_export($param, true)) . '</code></pre>' . $output);
    }
}