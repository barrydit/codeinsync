<?php
/*
 *
 */
class Shutdown {
    private static $instance = false;
    private $functions;
    private static $enabled = true;
    private $shutdownMessage;

    public function __construct() {
        $this->functions = [];
        defined('APP_END') or define('APP_END', microtime(true));

        $iniString = '';
        if (is_file($file = APP_PATH . APP_ROOT . '.env')){
          if (isset($_ENV) && !empty($_ENV))
            foreach ($_ENV as $key => $value) {
                // Convert boolean values to strings
                if ($value === true || $value === false || is_bool($value)) {
                    $value = (string) $value ? 'true' : 'false';

                    //die(var_dump($_ENV));
                }
        
                if (is_array($value)) {
                    // Process nested arrays
                    $iniString .= "[$key]\n";
                    foreach ($value as $nestedKey => $nestedValue) {
                        $iniString .= "$nestedKey = $nestedValue\n";
                    }
                } else {
                    // Append the key-value pair as a string to $iniString
                    $iniString .= "$key = $value\n";
                }
            }
          //file_put_contents(APP_PATH . 'env_writes.log', var_export($_ENV));
          //die();
          //dd($iniString);
          if ($iniString !== '') file_put_contents($file, $iniString);
        } else file_put_contents($file, $iniString);
        //
/*  
        $file = fopen(APP_PATH . APP_ROOT . '.env', 'w');
        if (isset($_ENV) && !empty($_ENV))
          foreach($_ENV as $key => $env_var) {
            fwrite($file, "$key=$env_var\n");
        }
        fclose($file);
*/

        register_shutdown_function([$this, 'onShutdown']);
    }

    public static function instance() {
        if (self::$instance == false) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function onShutdown() {
        if (!self::$enabled) { // $this->enabled
            // global $pdo, $session_save;
            //if (defined('APP_INSTALL') && APP_INSTALL && $path = APP_PATH . 'install.php') // is_file('config/constants.php')) 
            //    require_once($path);

            //include('checksum_md5.php'); // your_logger(get_included_files());
            //unset($pdo);
            return;
        }

        foreach ($this->functions as $fnc) {
            $fnc($this->shutdownMessage);
        }
        self::shutdown();
    }

    public function registerFunction(callable $fnc) {
        $this->functions[] = $fnc;
        return $this; // Return $this to allow method chaining
    }

    public static function setShutdownMessage(callable $messageCallback) { // $message
        self::instance()->shutdownMessage = $messageCallback; // $this->shutdownMessage = $messageCallback;
        return self::instance(); // $this; // Return $this to allow method chaining
    }

    public function shutdown($die = true) {
        if (!self::$enabled) {
            foreach ($this->functions as $fnc) {
                $fnc($this->shutdownMessage);
            }
        }
        $message = (is_callable($this->shutdownMessage)) ? call_user_func($this->shutdownMessage) : $this->shutdownMessage;
        if ($die == true)
            exit($message);
    }

    public static function create() {
        return new self();
    }

    /**
     * @param mixed $instance 
     */
    public static function setInstance($instance) {
        self::$instance = $instance;
        return;
    }

    /**
     * @param mixed $functions 
     * @return self
     */
    public function setFunctions($functions): self {
        $this->functions = $functions;
        return $this;
    }

    public static function getEnabled() {
        return self::$enabled;
    }

    /**
     * @param mixed $enabled 
     */
    public static function setEnabled($enabled) {
        self::$enabled = $enabled;
        return isset(self::$instance) ? static::instance() : self::instance();
    }
}

/* Shutdown::setEnabled(false)->setShutdownMessage(function() {
      global $pdo, $session_save;
      //if (defined('APP_INSTALL') && APP_INSTALL && $path = APP_PATH . 'install.php') // is_file('config/constants.php')) 
      //    require_once($path);

      defined('APP_END') or define('APP_END', microtime(true));
      //include('checksum_md5.php'); // your_logger(get_included_files());
      //unset($pdo);
    
      //echo "Executing shutdown function...\n";
    })->shutdown(); */

//Shutdown::create()->setEnabled(true)->shutdown();

//$shutdown = new Shutdown();
//$shutdown->setEnabled(true)->shutdown();



/*
class ShutdownHandler {
    private static $callbacks = [];
    private static $enabled = true;

    public static function create() {
        return new self();
    }

    public function registerCallback(callable $callback) {
        self::$callbacks[] = $callback;
        return $this; // Allow method chaining
    }

    public function executeCallbacks() {
        if (self::$enabled) {
            foreach (self::$callbacks as $callback) {
                call_user_func($callback);
            }
        }
    }

    public function setEnabled($enabled) {
        self::$enabled = (bool) $enabled;
        return $this; // Allow method chaining
    }
}

ShutdownHandler::create()
    ->registerCallback(function() {
      global $pdo, $session_save;
      //if (defined('APP_INSTALL') && APP_INSTALL && $path = APP_PATH . 'install.php') // is_file('config/constants.php')) 
      //    require_once($path);

      defined('APP_END') or define('APP_END', microtime(true));
      //include('checksum_md5.php'); // your_logger(get_included_files());
      //unset($pdo);
    
      echo "Executing shutdown function...\n";
    })
    ->setEnabled(true)
    ->executeCallbacks();

*/


/*
$shutdownHandler = new ShutdownHandler();

$shutdownHandler->addCallback(function() {
    // Actions to take on shutdown
    echo "Executing shutdown function...\n";
});

$shutdownHandler->executeCallbacks();

class ShutdownHandler {
    private $callbacks = [];

    public function __construct($callbacks = [], $executeImmediately = false) {
        $this->callbacks = $callbacks;

        if ($executeImmediately) {
            $this->executeCallbacks();
        } else {
            register_shutdown_function([$this, 'executeCallbacks']);
        }
    }

    public function addCallback($callback) {
        $this->callbacks[] = $callback;
    }

    public function executeCallbacks() {
        foreach ($this->callbacks as $callback) {
            call_user_func($callback);
        }
    }
}





class Shutdown {
    private static $enabled = true;

    public static function setEnabled($enabled) {
        self::$enabled = $enabled;
        return new self();
    }

    public static function shutdown() {
        if (self::$enabled) {
            // Your shutdown code goes here
            echo "Shutting down...\n";
        }
    }
}

function shutdown()
{
	global $pdo; //$myiconnect;
    // This is our shutdown function, in 
    // here we can do any last operations
    // before the script is complete.
	//mysqli_close($myiconnect);

  unset($pdo);
}

register_shutdown_function( // 'shutdown'
  function() {
    //global $pdo, $session_save;

    //isset($session_save) and $session_save();

    if (defined('APP_INSTALL') && APP_INSTALL && $path = APP_PATH . 'install.php')// is_file('config/constants.php')) 
        require_once($path);
    //else if (!is_file($path) && !in_array($path, get_required_files()))
    //    die(var_dump($path . ' was not found. file=install.php'));

    defined('APP_END') or define('APP_END', microtime(true));
    //include('checksum_md5.php'); // your_logger(get_included_files());
    //unset($pdo);
  }
);
 */

