<?php

class Shutdown
{
    private static $instance = false;
    private $functions;
    private static $enabled = true;
    private $shutdownMessage = null;

    public function __construct()
    {
        error_log("Shutdown constructor called."); // Log message to error log
        $this->functions = [];
        defined('APP_END') or define('APP_END', microtime(true));
        $this->initializeEnv();
    }

    private function initializeEnv()
    {
        $iniString = '';

        if (is_file($file = (defined('APP_PATH') ? APP_PATH : '') . (defined('APP_ROOT') ? APP_ROOT : '') . '.env')) {
            $_ENV = array_intersect_key_recursive($_ENV, parse_ini_file_multi($file));
            if (isset($_ENV) && !empty($_ENV)) {
                foreach ($_ENV as $key => $value) {
                    // Convert boolean values to strings
                    $value = $this->convertBooleanToString($value);
                    if (is_array($value)) {
                        $iniString .= "[$key]\n";
                        foreach ($value as $nestedKey => $nestedValue) {
                            $nestedValue = $this->processNestedValue($nestedValue);
                            $iniString .= "$nestedKey = $nestedValue\n";
                        }
                    } else {
                        $value = $this->processNestedValue($value);
                        $iniString .= "$key = $value\n";
                    }
                }
            }
        }
    }

    private function convertBooleanToString($value)
    {
        if ($value === true || $value === false || is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return $value;
    }

    private function processNestedValue($value)
    {
        if (is_string($value) && preg_match('/^\/.*\/[a-z]*$/i', $value)) {
            return "\"$value\"";
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return addslashes($value);
    }
    public static function triggerShutdown($message)
    {
        self::$shutdownMessage = $message;
        register_shutdown_function([self::class, 'onShutdown']);
    }

    public static function instance() {
        if (self::$instance == false) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function onShutdown()
    {
        if (!self::$enabled) {
            foreach ($this->functions as $fnc) {
                $fnc($this->shutdownMessage);
            }
        }
        $message = is_callable($this->shutdownMessage) ? call_user_func($this->shutdownMessage) : $this->shutdownMessage;
        exit($message);
    }

    public static function create()
    {
        return new self();
    }

    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

    public function setFunctions($functions): self
    {
        $this->functions = $functions;
        return $this;
    }

    public static function getEnabled()
    {
        return self::$enabled;
    }

    public static function setEnabled($enabled)
    {
        self::$enabled = $enabled;
        return isset(self::$instance) ? static::instance() : self::instance();
    }

    public function setShutdownMessage($message): self
    {
        $this->shutdownMessage = $message;
        return $this;
    }
    
    public function shutdown($die = true) {
        if (!self::$enabled) {
            foreach ($this->functions as $fnc) {
                $fnc($this->shutdownMessage);
            }
        }
        $message = (is_callable($this->shutdownMessage)) ? call_user_func($this->shutdownMessage) : $this->shutdownMessage;
        if ($die == true) {
            exit($message);
        }
    }
}

register_shutdown_function([new Shutdown(), 'onShutdown']);

function dd(mixed $param = null, $die = true, $debug = true)
{
    $output = ($debug == true && !defined('APP_END') ? 
              'Execution time: <b>'  . round(microtime(true) - APP_START, 3) . '</b> secs' : 
              'Execution time: <b>'  . round(APP_END - APP_START, 3) . '</b> secs'
              ) . "<br />\n";

    if ($die) {
        Shutdown::setEnabled(false)->setShutdownMessage(function() use ($param, $output) {
            return '<pre><code>' . str_replace(['\'', '"'], '', var_export($param, true)) . '</code></pre>' . $output;
        })->shutdown();
    } else {
        return var_dump('<pre><code>' . str_replace(['\'', '"'], '', var_export($param, true)) . '</code></pre>' . $output);
    }
}