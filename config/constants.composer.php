<?php

use App\Core\Registry;

//if (!defined('COMPOSER_VERSION')) {
//    define('COMPOSER_VERSION', trim(shell_exec('composer --version')));
//}
global $errors;

!defined('APP_ROOT') ? define('APP_ROOT', '') : '';

!defined('APP_SELF') ? define('APP_SELF', basename($_SERVER['PHP_SELF'] ?? '')) : '';

!defined('APP_SUDO') ? define('APP_SUDO', '') : '';

if (!defined('COMPOSER_PHAR')) {
    // Define path to composer.phar, adjust this as needed
    define('COMPOSER_PHAR', []);
}

//if ($_SERVER['REQUEST_METHOD'] == 'POST') 
//  die(var_dump($_GET));

use Composer\InstalledVersions;

if (isset($_ENV['COMPOSER']['EXPR_NAME']) && !defined('COMPOSER_EXPR_NAME'))
    define('COMPOSER_EXPR_NAME', $_ENV['COMPOSER']['EXPR_NAME']); // const COMPOSER_EXPR_NAME = 'string only/non-block/ternary';
//elseif (!defined('COMPOSER_EXPR_NAME'))
//define('COMPOSER_EXPR_NAME', '/([a-z0-9](?:[_.-]?[a-z0-9]+)*)\/([a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+)*)/'); // name

if (isset($_ENV['COMPOSER']['EXPR_VER']) && !defined('COMPOSER_EXPR_VER'))
    define('COMPOSER_EXPR_VER', $_ENV['COMPOSER']['EXPR_VER']); // const COMPOSER_EXPR_VER = 'string only/non-block';
elseif (!defined('COMPOSER_EXPR_VER'))
    define('COMPOSER_EXPR_VER', '/v?\d+(?:\.\d+){0,3}|dev-.*/'); // name

//composer config --global --auth --unset github-oauth.github.com
//composer config --global github-oauth.github.com __TOKEN__
//putenv('COMPOSER_use-github-api=true');
//putenv('COMPOSER_github-oauth.github.com=BAM');

// php -d xdebug.remote_enable=0 composer
// php -d xdebug.remote_enable=0 composer <your_command_here>
// -d xdebug.remote_enable=0 \
// -d xdebug.profiler_enable=0 \
// -d xdebug.profiler_output_dir=. \
// -d xdebug.default_enable=0

// php -dxdebug.mode=debug -dxdebug.output_dir=. public/ui_complete.php

/*
foreach ($array = preg_split("/\r\n|\n|\r/", exec(APP_SUDO  . -u www-data /usr/local/bin/composer diagnose')) as $key => $diag_line) {
  dd($diag_line, false);
}
*/

// '(win) set VARIABLE / (linux/macos) export VARIABLE '

const COMPOSER_DISABLE_NETWORK = 1;
putenv('COMPOSER_DISABLE_NETWORK=' . (bool) COMPOSER_DISABLE_NETWORK);

const COMPOSER_ALLOW_SUPERUSER = true;
putenv('COMPOSER_ALLOW_SUPERUSER=' . (bool) COMPOSER_ALLOW_SUPERUSER); //dd(getenv('COMPOSER_ALLOW_SUPERUSER'));

const COMPOSER_ALLOW_XDEBUG = false; // didn't work
putenv('COMPOSER_ALLOW_XDEBUG=' . (bool) COMPOSER_ALLOW_XDEBUG);

putenv('COMPOSER_DISABLE_XDEBUG_WARN=' . (bool) true);

class ComposerConfig
{
    private $name;
    private $version;
    private $description;

    // Properties initialized with default values
    private $type = '';
    private $keywords = [];
    private $homepage = '';
    private $readme = '';
    private $time = '';
    private $license = '';
    private $authors = [];
    private $repositories = [];
    private $require;
    private $requireDev;
    private $autoload;
    private $autoloadDev;
    private $minimumStability = '';
    private $preferStable = false;
    private $config;

    public function __construct($config = ['name' => 'default', 'version' => '1.0.0', 'description' => 'Default description'])
    {
        $this->name = $config['name'] ?? 'default';
        $this->version = $config['version'] ?? '1.0.0';
        $this->description = $config['description'] ?? 'Default description';

        // Initialize all other required properties with defaults
        $this->initializeProperties();
    }

    private function initializeProperties()
    {
        // Default configurations for properties with specific types or nested structures
        $this->require = new stdClass();
        $this->requireDev = new stdClass();
        $this->autoload = new stdClass();
        $this->autoloadDev = new stdClass();
        $this->repositories = [];

        $this->authors[] = (object) [
            'name' => $_ENV['COMPOSER']['AUTHOR'] ?? 'Default Author',
            'email' => $_ENV['COMPOSER']['EMAIL'] ?? 'author@example.com'
        ];

        $this->config = (object) [
            'platform-check' => false,
            'platform' => (object) ['php' => '7.4.0']
        ];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description,
            'type' => $this->type,
            'keywords' => $this->keywords,
            'homepage' => $this->homepage,
            'readme' => $this->readme,
            'time' => $this->time,
            'license' => $this->license,
            'authors' => $this->authors,
            'repositories' => $this->repositories,
            'require' => $this->require,
            'require-dev' => $this->requireDev,
            'autoload' => $this->autoload,
            'autoload-dev' => $this->autoloadDev,
            'minimum-stability' => $this->minimumStability,
            'prefer-stable' => $this->preferStable,
            'config' => $this->config
        ];
    }

    // Add getter methods for each property
    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /*
      setProperty – Set or update a specific property in the configuration.
      addAuthor – Add an author to the authors array.
      addRepository – Add a repository to the repositories array.
      addRequirement – Add a requirement to require or require-dev.
      setAutoload – Configure autoload settings.
      validateConfig – Check if required fields are set and valid.
      toArray – Convert the config object to an array format.
      toJson – Convert the config to a JSON string.
      saveToFile – Save the JSON output to a file.
      loadFromFile – Load configuration data from a JSON file.
      mergeConfig – Merge additional configurations or override existing values.
      resetConfig – Reset properties to default values.
    */
}

class composerSchema
{
    public $name;
    public $description;
    public $version;
    public $type;
    public $keywords;
    public $homepage;
    public $readme;
    public $time; //date('Y-m-d H:i:s');
    public $license;
    public $authors;
    public $repositories;
    public $require;
    //public $require_dev;  // using {'require-dev'}
    public $autoload;
    //public $autoload_dev;  // using {'autoload-dev'}

    /**
     */
    public function __construct()
    {
    }
}

if (is_file($include = APP_PATH . !defined('APP_ROOT') ?: APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    if (isset($_ENV['COMPOSER']['AUTOLOAD']) && (bool) $_ENV['COMPOSER']['AUTOLOAD'] === TRUE) {
        require_once $include;
    } else if (!empty(APP_ROOT) || isset($_GET['app']) && $_GET['app'] === 'composer') { //require_once $include;
    }
} else
    $errors['COMPOSER_AUTOLOAD'] = "Composer autoload is disabled.\n";


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!defined('APP_PATH_CONFIG') || !in_array(APP_PATH_CONFIG, get_required_files()))
        die("const(APP_PATH_CONFIG) is missing. Presumed that this file was opened on its own.");
} else {
    /*  if ($path = realpath(APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.env.php'))
        require_once $path;
      if ($path = realpath(APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.paths.php'))
        require_once $path;

      //if ($path = realpath(APP_PATH . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.cli.php'))
      //  require_once $path;


      //if ($path = realpath(APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.runtime.php'))
      //  require_once $path;
      if ($path = realpath(APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.url.php'))
        require_once $path;
      if ($path = realpath(APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php'))
        require_once $path;
    */
}

if (!function_exists('get_declared_classes')) {
    $autoloadContent = file_get_contents($include);
    if (!preg_match('/class\s+ComposerAutoloaderInit([a-f0-9]+)/', $autoloadContent, $matches))
        $errors['COMPOSER-AutoloaderInit'] = "ComposerAutoloaderInit failed to be matched. Check for autoload.php\n";
} else if (!empty($classes = get_declared_classes())) {
    foreach ($classes as $key => $class) {
        if (preg_match('/(ComposerAutoloaderInit[a-f0-9]+)/', $class, $matches))
            break;
        if ($class == end($classes))
            $errors['COMPOSER-AutoloaderInit'] = "ComposerAutloaderInit2 failed to be matched. Check for autoload.php\n";

        // composer dump-autoload
    }
    /*
    Check oauth github
    Check vendor folder exists and/or empty
    composer/InstalledVersions.php
    */

}

if (isset($matches[1])) {
    define('COMPOSER_AUTOLOADERINIT', $matches[1]); // no dashes

    $classesFound = [];
    $foundKey = false; // Flag to indicate if key 179 has been found
    foreach ($classes as $key => $class) {
        if ($foundKey) {
            // Now $key is the key and $item is the value
            //echo "$key => $class\n"; // Print key-value pair
            $classesFound[] = $class;
        }
        if (preg_match('/' . COMPOSER_AUTOLOADERINIT . '/', $class)) {
            $classesFound[] = $class;
            $foundKey = true; // Set the flag to true once key 179 is found
        }
    }
}

$loadedLibraries = [];

// Load a library
if (class_exists(Composer\Autoload\ClassLoader::class)) {
    $loadedLibraries[] = 'Composer\Autoload\ClassLoader';
}

// Check if a library is loaded
if (in_array(Composer\Autoload\ClassLoader::class, $loadedLibraries)) {
    // The library is loaded
//  echo 'Library found.';
    //$loadedLibraries;

    if (class_exists("Composer\\InstalledVersions")) {
        $installedPackages = Composer\InstalledVersions::getInstalledPackages();
        // Process $installedPackages as needed
    } else {
        $errors['COMPOSER_INSTALLEDVERSIONS'] = "The class Composer\\InstalledVersions is not found. Please check your Composer setup.";
    }
}

$vendors = [];

// Print information about each package
if (isset($installedPackages) && !empty($installedPackages)) {
    foreach ($installedPackages as $key => $package) { //
        if (preg_match(COMPOSER_EXPR_NAME, $package, $matches))
            $vendors[$key] = $matches[1];
    }

    $uniqueVendors = array_unique($vendors);

    foreach ($installedPackages as $key => $package) { //
        if (preg_match(COMPOSER_EXPR_NAME, $package, $matches))
            $uniqueVendors[$matches[1]][] = $matches[2];
        unset($uniqueVendors[$key]);
    }

    define('COMPOSER_VENDORS', $uniqueVendors);
}

/*
  Must be defined before the composer-setup.php can be preformed.
*/

$composerUser = $_ENV['COMPOSER']['VENDOR'] ?? '';
$componetPkg = $_ENV['COMPOSER']['PACKAGE'] ?? '';
$user = getenv('USERNAME') ?? getenv('APACHE_RUN_USER') ?? getenv('USER') ?? '';

// Determine the Composer home path based on the OS and user
$composerHome = (stripos(PHP_OS, 'WIN') === 0) ? "C:/Users/$user/AppData/Roaming/Composer/" : ($user === 'root' ? '/root/.composer/' : APP_PATH . '.composer/');

if (!realpath($composerHome) && @!mkdir($composerHome, 0755, true)) {
    $errors['COMPOSER_HOME'] = "$composerHome does not exist. Path: $composerHome\n";
}

define('COMPOSER_HOME', $composerHome);

//dd('Composer Home: ' . $composerHome, 0);

putenv("COMPOSER_HOME=$composerHome" ?? $_SERVER['HOME'] . '/.composer/'); // /var/www

if (!file_exists(APP_PATH . 'composer.phar')) {
    copy('https://getcomposer.org/installer', 'composer-setup.php');

    $error = shell_exec($_ENV['PHP']['EXEC'] . ' composer-setup.php'); // php -d register_argc_argv=1

    $errors['COMPOSER-PHAR'] = 'Composer setup was executed and ' . (file_exists(APP_PATH . 'composer.phar') ? 'does' : 'does not') . ' exist.';

    //defined('PHP_EXEC') ? $errors['COMPOSER-PHAR'] .= ' version='. shell_exec(PHP_EXEC . ' composer.phar -V') . '  error=' . $error : '';
} else

    //if (preg_match('/Composer(?: version)? (\d+\.\d+\.\d+) (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', exec(($bin = 'php composer.phar') . ' -V'), $matches))
    //  define('COMPOSER_PHAR', ['bin' => $bin, 'version' => $matches[1], 'date' => $matches[2]]);

    if (stripos(PHP_OS, 'WIN') === 0) { // DO NOT REMOVE! { .. }
        // Check if PHP is in the system's PATH and executable
        $phpCheckOutput = null;
        $phpCheckResult = null;

        defined('PHP_EXEC') ? exec(PHP_EXEC . ' -v', $phpCheckOutput, $phpCheckResult) : exec('php -v', $phpCheckOutput, $phpCheckResult);

        if ($phpCheckResult !== 0) {
            $errors['PHP_PATH'] = 'PHP is not within the system\'s PATH or is not executable.';
            !defined('COMPOSER_PHAR') and define('COMPOSER_PHAR', ['bin' => PHP_EXEC . 'composer.phar', 'version' => null, 'date' => null]);
            !defined('COMPOSER_BIN') && defined('COMPOSER_PHAR') and define('COMPOSER_BIN', COMPOSER_PHAR);
        } else {
            // Check if Composer is installed and accessible
            if (file_exists('C:\ProgramData\ComposerSetup\bin\composer.phar')) {
                if (
                    preg_match(
                        '/Composer(?: version)? (\d+\.\d+\.\d+) (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/',
                        exec($bin = "php C:\\ProgramData\\ComposerSetup\\bin\\composer.phar -V"),
                        $matches
                    )
                ) {
                    !defined('COMPOSER_PHAR') and define('COMPOSER_PHAR', ['bin' => $bin, 'version' => $matches[1], 'date' => $matches[2]]);
                    !defined('COMPOSER_BIN') && defined('COMPOSER_PHAR') and define('COMPOSER_BIN', COMPOSER_PHAR);
                }
            } else {
                if (
                    preg_match(
                        '/Composer(?: version)? (\d+\.\d+\.\d+) (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/',
                        exec($bin = "php C:\\www\\composer.phar -V"),
                        $matches
                    )
                ) {
                    !defined('COMPOSER_PHAR') and define('COMPOSER_PHAR', ['bin' => 'composer.phar', 'version' => $matches[1], 'date' => $matches[2]]);
                    !defined('COMPOSER_BIN') && defined('COMPOSER_PHAR') and define('COMPOSER_BIN', COMPOSER_PHAR);
                }
            }
        }
    } else {

        //if (file_exists(APP_PATH . 'composer.phar'))
        //define('COMPOSER_PHAR', ['bin' => 'php composer.phar', version => '1.0.0']);
/*
  if (file_exists(APP_PATH . 'composer.phar')) {
    define('COMPOSER_PHAR', (file_exists(APP_PATH . 'composer.phar') ? APP_PATH . 'composer.phar' : '/usr/bin/composer'));
    define('COMPOSER_BIN', '/usr/bin/composer');
  } elseif (file_exists('/usr/local/bin/composer')) {
    define('COMPOSER_PHAR', (file_exists(APP_PATH . 'composer.phar') ? APP_PATH . 'composer.phar' : '/usr/local/bin/composer'));
    define('COMPOSER_BIN', '/usr/local/bin/composer');
  }
*/
        //defined('COMPOSER_EXEC')

        // Set $sudo_prefix properly
        $sudo_prefix = '';
        if (defined('APP_SUDO') && trim(APP_SUDO) !== '') {
            $sudo_prefix = APP_SUDO . ' -u www-data ';
        }

        // Try to find composer using `which`
        $composer_which = trim(shell_exec("{$sudo_prefix}which composer") ?? '');

        // Try to resolve real path
        $resolved_path = realpath($composer_which);

        // Fallback handling
        if (!$resolved_path) {
            $composer_which = '/usr/local/bin/composer'; // fallback
            $errors['COMPOSER-WHICH'] = "Could not locate composer using 'which'. Fallback used: {$composer_which}";
        } else {
            $composer_which = $resolved_path;
        }
        //die(var_dump($composer_which));
        foreach ([basename(PHP_BINARY ?? PHP_EXEC) . ' ' . APP_PATH . 'composer.phar', $composer_which] as $key => $bin) {

            !isset($composer) /*|| !$composer*/ ?: $composer = [];
            /*//*/
            if (isset($bin) && preg_match('/^php.*composer\.phar$/', $bin))
                !defined('COMPOSER_PHAR') and define('COMPOSER_PHAR', ['bin' => PHP_EXEC . " $bin"]);
            else {
                $proc = proc_open(/*'env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' .*/ $sudo_prefix . (defined('COMPOSER_BIN') ? COMPOSER_BIN : 'composer') . ' --version', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);

                $exitCode = proc_close($proc);

                if (preg_match('/Composer(?: version)? (\d+\.\d+\.\d+) (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $stdout, $matches)) {
                    //dd($key);
                    $composer[$key]['bin'] = $bin;
                    (!isset($matches[1]) ?: $composer[$key]['version'] = $matches[1]);
                    (!isset($matches[2]) || is_bool($matches[2]) ?: $composer[$key]['date'] = $matches[2]);

                    $errors['COMPOSER_VERSION'] = $matches[1] ?? 'Unknown version';

                } else {

                    if (empty($stdout)) {
                        if (!empty($stderr))
                            $errors['COMPOSER_VERSION'] = $stderr;
                    } else
                        $errors['COMPOSER_VERSION'] = $stdout; // else $errors['COMPOSER_VERSION'] = $stdout . ' does not match $version'; }
                }

            }
        }

        usort($composer, function ($a, $b) {
            return version_compare($b['version'], $a['version']); // Sort in descending order based on version
        });


        if (empty($composer))
            $errors['COMPOSER-BIN'] = 'There are no composer binaries.';
        else
            foreach ($composer as $key => $exec)
                if ($key == 0 || $key == 1) {

                    !defined('COMPOSER_BIN') and define('COMPOSER_BIN', $exec);

                    continue; // !break 2-loops
                } else
                    break;


    }

// dd(COMPOSER_PHAR, 0);
// dd(COMPOSER_BIN, 0);

//
//exec('whoami', $output, $returnCode); // or $errors['COMPOSER-WHOAMI'] = $output;
//if (APP_DEBUG) {

//$output = [];

$output = [ // Exception: [] operator not supported for strings
    stripos(PHP_OS, 'WIN') === 0 ? realpath('C:\\composer\\composer.bat' /*shell_exec('where composer')*/)
    : realpath(shell_exec((defined('APP_SUDO') ? APP_SUDO : '') . 'which composer') ?? ''),
    shell_exec('composer --version') ?: $errors['COMPOSER-VERSION'] = ''
];

if (isset($output[0]))
    if (stripos(PHP_OS, 'WIN') === 0) {
        !preg_match('/Composer(?: version)? (\d+\.\d+\.\d+) (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $output[1], $matches) and
            $errors['COMPOSER-VERSION'] = $output[1];
    } else {
        preg_match('/Composer(?: version)? (\d+\.\d+\.\d+) (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $output[1], $matches) or $errors['COMPOSER-VERSION'] = $output[1];
    }


if (!empty($matches))
    defined('COMPOSER_EXEC') or define('COMPOSER_EXEC', (isset($_GET['exec']) && $_GET['exec'] == 'phar' ? COMPOSER_PHAR : (defined('COMPOSER_BIN') ? ['bin' => defined('COMPOSER_BIN') ? 'composer' : COMPOSER_BIN, 'version' => ($matches[1] ?? '')] : ['bin' => 'composer', 'version' => $matches[1]])) ?? COMPOSER_PHAR);

if (defined('COMPOSER_EXEC') && is_array(COMPOSER_EXEC))
    define('COMPOSER_VERSION', COMPOSER_EXEC['version'] ?? '1.0.0');
//else
//define('COMPOSER_VERSION', COMPOSER_PHAR['version']);

$configJsonPath = COMPOSER_HOME . 'config.json';

if (realpath(COMPOSER_HOME) && !file_exists($configJsonPath)) {
    if (!touch($configJsonPath)) {
        $errors['COMPOSER_CONFIG'] = "$configJsonPath is unable to be created.";
    } else {
        file_put_contents($configJsonPath, '{}');
    }
}

if (is_file($configJsonPath)) {
    define('COMPOSER_CONFIG', [
        'json' => '{}',
        'path' => $configJsonPath
    ]);
}

$authJsonPath = COMPOSER_HOME . 'auth.json';

if (realpath(COMPOSER_HOME) && !file_exists($authJsonPath)) {
    if (!touch($authJsonPath)) {
        $errors['COMPOSER_AUTH'] = "$authJsonPath is unable to be created.";
    } else {
        file_put_contents($authJsonPath, '{"github-oauth": {"github.com": ""}}');
    }
}

if (is_file($authJsonPath)) {
    putenv('COMPOSER_AUTH=' . (filesize($authJsonPath) == 0 || trim(file_get_contents($authJsonPath)) == false ? '{"github-oauth": {"github.com": ""}}' : trim(str_replace([' ', "\r\n", "\n", "\r"], '', file_get_contents($authJsonPath)))));

    define('COMPOSER_AUTH', [
        'json' => getenv('COMPOSER_AUTH'),
        'path' => $authJsonPath,
        'token' => json_decode(getenv('COMPOSER_AUTH')/*, true */)->{'github-oauth'}->{'github.com'}
    ]);
} else
    define('COMPOSER_AUTH', [
        'json' => getenv('COMPOSER_AUTH'),
        'path' => $authJsonPath
    ]);

if (is_file($authJsonPath) && isset($_ENV['GITHUB']['OAUTH_TOKEN']) && COMPOSER_AUTH['token'] !== $_ENV['GITHUB']['OAUTH_TOKEN'] ?? 'static token') {
    $errors['COMPOSER_TOKEN'] = "COMPOSER_TOKEN does not match the GITHUB/OAUTH_TOKEN\n";
    if (isset($errors['COMPOSER_TOKEN']))
        file_put_contents($authJsonPath, '{"github-oauth": {"github.com": "' . (COMPOSER_AUTH['token'] ?? $_ENV['GITHUB']['OAUTH_TOKEN']) . '"}}');
} else
    putenv('COMPOSER_TOKEN=' . (COMPOSER_AUTH['token'] ?? 'static token')); // <GITHUB_ACCESS_TOKEN>


// dd(COMPOSER_AUTH['token'] . '   ' . $_ENV['GITHUB']['OAUTH_TOKEN']);

putenv('PWD=' . APP_PATH . !defined('APP_ROOT') ?: APP_ROOT);

//dd(file_get_contents($authJsonPath)); // json_decode(getenv('COMPOSER_AUTH') ?? file_get_contents($authJsonPath) /*, true */)

/*
  This section of code will need to correspond to a project

    A project file will need to look for first, and then look for the applications' composer.json

       Can a constant be a object, or does an object need to be able to write to itself ...

       If !defined(COMPOSER_JSON) and define('COMPOSER_JSON', APP_PATH . '/composer.json');
*/

/* library, project, metapackage, composer-plugin ... Package type */
$composer_exec = null;

if (defined('COMPOSER_EXEC') && is_array(COMPOSER_EXEC)) {
    $exec_bin = COMPOSER_EXEC['bin'] ?? null;
    $phar_bin = (defined('COMPOSER_PHAR') && is_array(COMPOSER_PHAR)) ? COMPOSER_PHAR['bin'] ?? null : null;

    if ($exec_bin) {
        $composer_exec = ($exec_bin === $phar_bin) ? $phar_bin : $exec_bin;
    }
}
/*
APP_WORK[client]

APP_CLIENT / APP_PROJECT APP_ {key(APP_WORK)}
  [path]
  [user]
*/

if (defined('APP_ENV') and APP_ENV == 'development') {
    if (defined('APP_CLIENT') && is_object(APP_CLIENT))
        $$c_or_p = APP_CLIENT;
    else {
        $c_or_p = 'client';
        $$c_or_p = new stdClass();
        $$c_or_p->path = APP_PATH;
        $$c_or_p->name = 'www';
        define('APP_CLIENT', $$c_or_p);
    }

    if (!isset($c_or_p) && !is_object($$c_or_p))
        $errors['COMPOSER_CLIENT-PROJECT'] = '$c_or_p is not set. No project or client was selected. Using APP as client.';
    else {
        //die('test');

        ob_start();
        echo $composer_exec; ?> init --quiet --no-interaction
        --working-dir="<?= APP_PATH . APP_ROOT; ?>"
        --name="<?= $composerUser . '/' . $$c_or_p->name; ?>"
        --description="General Description"
        --author="Barry Dick <barryd.it@gmail.com>"
            --type="project"
            --homepage="https://github.com/<?= $composerUser . '/' . $$c_or_p->name; ?>""
            --require="php:^7.4||^8.0"
            --require="composer/composer:^1.0"
            --require-dev="pds/skeleton:^1.0"
            --stability="dev"
            --license="WTFPL"
            <?php
            defined('COMPOSER_INIT_PARAMS')
                or define('COMPOSER_INIT_PARAMS', /*<<<TEXT TEXT*/ ob_get_contents());
            ob_end_clean();

            if (!is_dir($$c_or_p->path . 'vendor' . DIRECTORY_SEPARATOR))
                $errors['COMPOSER_INIT-VENDOR'] = 'Failed to create the vendor/ directory. If you are seeing this. An error has occured.';

            //@mkdir($$c_or_p->path . 'vendor');
    

            // composer init --require=twig/twig:1.13.* -n   // https://webrewrite.com/create-composer-json-file-php-project/
    
            // composer init --quiet --no-interaction --working-dir="{$$c_or_p->path}" --require=php:^7.4|^8.0
    
            // --require-dev="phpunit/phpunit:^9.5.20"
// --autoload="src/"
            if (file_exists(APP_PATH . APP_ROOT . 'composer.json')) {

                // clean up json -- preg_replace('/[\x00-\x1F\x80-\xFF]/', '', str_replace('\\', '\\\\', '{...}'))
    
                //($err = json_decode(str_replace('\\', '\\\\', file_get_contents($$c_or_p->path . 'composer.json')), null, 512, JSON_THROW_ON_ERROR)) and $error['COMPOSER-JSON'] = 'Invalid JSON: ' . $err;
    
                if (!defined('COMPOSER_JSON'))
                    define('COMPOSER_JSON', ['json' => file_get_contents(APP_PATH . APP_ROOT . 'composer.json'), 'path' => APP_PATH . APP_ROOT . 'composer.json']);

            } else {
                // php composer.phar init
    
                // /usr/share/php/Symfony/Component/Console/Helper/HelperSet.php
                // Deprecated: Return type of Symfony\Component\Console\Helper\HelperSet::getIterator() should either be compatible with IteratorAggregate::getIterator()
                // Traversable, or the #[\ReturnTypeWillChange] attribute should be used to temporarily suppress the notice in /usr/share/php/Symfony/Component/Console/Helper/HelperSet.php on line 103
    
                // 'COMPOSER_BIN init' >> Symfony\Component\Console\Helper\...
/*  This code would be used to create 
  $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_INIT_PARAMS, array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

  $stdout = stream_get_contents($pipes[1]);
  $stderr = stream_get_contents($pipes[2]);

  $exitCode = proc_close($proc);

  if (empty($stdout)) {
    if (!empty($stderr))
      $errors['COMPOSER_INIT'] = '$stdout is empty. $stderr = ' . $stderr;
  } //else $errors['COMPOSER_INIT'] = $stdout;

  if (file_exists($$c_or_p->path . 'composer.json'))
    define('COMPOSER_JSON', ['json' => file_get_contents($$c_or_p->path . 'composer.json'), 'path' => $$c_or_p->path . 'composer.json']);
  else 
    if (!touch($$c_or_p->path . 'composer.json'))
      $errors['COMPOSER-JSON'] = 'composer.json was unable to be created.';
    else
      file_put_contents($$c_or_p->path . 'composer.json', '{}');
*/
            }
    }

    /*
      Suggested packages to be added later:
        composer/packagist
        composer/getcomposer.org
    */

    //$errors['COMPOSER_JSON'] = 'COMPOSER_JSON constant/object is not defined.';

    /*
    if (file_exists($$c_or_p->path . '/composer.json'))
    (defined(strtoupper($c_or_p)) ??
      defined('COMPOSER_JSON')
        or define('COMPOSER_JSON', $$c_or_p->path . '/composer.json')
    );
    else (@!touch($$c_or_p->path . '/composer.json')? define('COMPOSER_JSON', $$c_or_p->path . '/composer.json') : $erros['COMPOSER-JSON'] = 'composer.json was unable to be created.');
    */
} // else { }
defined('COMPOSER_JSON') or define('COMPOSER_JSON', [
    'json' => (is_file(!defined('APP_ROOT') ?: APP_ROOT . 'composer.json') ? file_get_contents(!defined('APP_ROOT') ?: APP_ROOT . 'composer.json') : '{}'),
    'path' => APP_PATH . !defined('APP_ROOT') ?: APP_ROOT . 'composer.json'
]);

ob_start();
echo $composer_exec; ?> init --quiet --no-interaction
    --working-dir="<?= APP_PATH . APP_ROOT; ?>"
    --name="<?= $composerUser . '/' . str_replace('.', '_', basename(APP_ROOT) ?? $componetPkg); ?>"
    --description="General Description"
    --author="Barry Dick &lt;barryd.it@gmail.com&gt;"
    --type="project"
    --homepage="https://github.com/<?= $composerUser . '/' . str_replace('.', '_', basename(APP_ROOT) ?? $componetPkg); ?>"
    --require="php:^7.4||^8.0"
    --require="composer/composer:^1.0"
    --require-dev="pds/skeleton:^1.0"
    --stability="dev"
    --license="WTFPL"
    <?php
    defined('COMPOSER_INIT_PARAMS')
        or define('COMPOSER_INIT_PARAMS', /*<<<TEXT TEXT*/ trim(ob_get_contents()));
    ob_end_clean();

    if (!realpath(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR)) {
        exec(COMPOSER_INIT_PARAMS);
    } elseif (!realpath(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
        exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' update', $output, $returnCode);
        if ($returnCode !== 0)
            $errors['COMPOSER-INIT-UPDATE'] = $output;

        exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' dump-autoload', $output, $returnCode);
        if ($returnCode !== 0)
            $errors['COMPOSER-DUMP-AUTOLOAD'] = $output;
    }
    /* Consider writing a gui that would handle the composer traffic ... */


    // moved to config.php load (last)
// is_file('vendor' . DIRECTORY_SEPARATOR . 'autoload.php') and require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'; // Include Composer's autoloader
    
    /*

    use Composer\Console\Application;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Output\BufferedOutput;

    // Create a new Composer Application
    $application = new Application();

    // Create a BufferedOutput to capture the output
    $output = new BufferedOutput();

    // Create an input object with the show command
    $input = new ArrayInput(['command' => 'show']);

    // Run the show command and capture the output
    $exitCode = $application->run($input, $output);

    // Check if the command was successful
    if ($exitCode === 0) {
        // Get the captured output and print it
        $outputText = $output->fetch();
        echo $outputText;
    } else {
        // Handle the case where the command failed
        echo "Composer show command failed with exit code $exitCode";
    }
    */
    /*
    // Use the Composer\Factory to create a Composer instance
    $composer = \Composer\Factory::create();

    // Get the installed repository, which contains a list of installed packages
    $repository = $composer->getRepositoryManager()->getLocalRepository();

    // Get all installed packages
    $packages = $repository->getPackages();

    // Print information about each package
    foreach ($packages as $package) {
        echo $package->getName() . ' - ' . $package->getPrettyVersion() . PHP_EOL;
    }
    */
    /*
    // Use the Composer\Factory to create a Composer instance
    $composer = new \Composer\Semver\Semver();

    // Get the list of installed packages
    $installedPackages = $composer::getInstalledPackages();
    */
    /*
    // Use Composer's InstalledVersions class to get a list of installed packages
    $installedPackages = Composer\InstalledVersions::getAll();

    // Print information about each package
    foreach ($installedPackages as $package) {
        echo $package['name'] . ' - ' . $package['version'] . PHP_EOL;
    }
    */

    /*
    // Read the installed packages from the installed.json file
    $installedPackages = json_decode(file_get_contents('vendor/composer/installed.json'), true);

    foreach ($installedPackages['packages']  as $package) { //
        echo $package['name'] . ' - ' . $package['version'] . ' - ' . $package['description']. '<br />' . PHP_EOL; // 
    }
    */


    /* This code starts here */




    /* Ends here */


    /*

    use Composer\Composer;
    use Composer\Factory;
    use Composer\DependencyResolver\Request;
    use Composer\Package\Version\VersionSelector;
    use Composer\Repository\CompositeRepository;
    use Composer\Repository\PlatformRepository;

    // Initialize Composer
    $composer = Factory::create();

    // Create a repository representing all known packages
    $repositorySet = $composer->getRepositoryManager()->getLocalRepositorySet();

    // Create a PlatformRepository to represent the currently installed packages
    $platformRepo = new PlatformRepository();

    // Create a CompositeRepository with both the known packages and the installed packages
    $compositeRepo = new CompositeRepository([$platformRepo, $repositorySet->getCanonicalLocalRepository()]);

    // Create a Request for the package you're looking to install
    $request = new Request();
    $request->install(['package-name' => '*']);

    // Get the latest version of the package
    $versionSelector = new VersionSelector($compositeRepo);
    $latestPackage = $versionSelector->findBestCandidate('package-name');

    if ($latestPackage !== null) {
        echo 'Package is installable.';
    } else {
        echo 'Package is not installable.';
    }
    */

    // isset($$c_or_p) and dd($$c_or_p);
    
    //cd /usr/local/bin
//curl -sS https://getcomposer.org/installer | php /* -- --filename=composer */
//chmod a+x composer.phar
//sudo mv composer /usr/local/bin/composer
//Change into a project directory cd /path/to/my/project
    


    //defined('PHP_WINDOWS_VERSION_MAJOR') ? 'APPDATA' : 'HOME';
    
    //require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'Git.php/src/Git.php';
//require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'Git.php/src/GitRepo.php';
    
    //use Kbjr\Git\Git;
//use Kbjr\Git\GitRepo;
    
    // file has to exists first
    
    is_dir(APP_PATH . 'var') || mkdir(APP_PATH . 'var', 0755);

    if (is_file(APP_PATH . 'var' . DIRECTORY_SEPARATOR . 'getcomposer.org.html')) {
        if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime(APP_PATH . 'var' . DIRECTORY_SEPARATOR . '/getcomposer.org.html'))))) / 86400)) <= 0) {
            $url = 'https://getcomposer.org/';
            $handle = curl_init($url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

            if (!empty($html = curl_exec($handle))) {
                file_put_contents(APP_PATH . 'var' . DIRECTORY_SEPARATOR . 'getcomposer.org.html', $html) or $errors['COMPOSER_LATEST'] = "$url returned empty.";
            }
        }
    } else {
        $url = 'https://getcomposer.org/';
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        if (!empty($html = curl_exec($handle))) {
            file_put_contents(APP_PATH . 'var' . DIRECTORY_SEPARATOR . 'getcomposer.org.html', $html) or $errors['COMPOSER_LATEST'] = "$url returned empty.";
        }
    }
    libxml_use_internal_errors(true); // Prevent HTML errors from displaying
    $doc = new DOMDocument(1.0, 'utf-8');
    $doc->loadHTML(file_get_contents(APP_PATH . 'var' . DIRECTORY_SEPARATOR . 'getcomposer.org.html'));

    $content_node = $doc->getElementById("main");

    $node = getElementsByClass($content_node, 'p', 'latest');

    //$xpath = new DOMXpath ( $doc ); //$xpath->query ( '//p [contains (@class, "latest")]' );
//dd($xpath);
    
    $pattern = '/Latest: (\d+\.\d+\.\d+) \(\w+\)/';

    if (preg_match($pattern, $node[0]->nodeValue, $matches)) {
        $version = $matches[1];

        define('COMPOSER_LATEST', $version);

        $errors['COMPOSER_LATEST'] = /*"Latest Composer version: " .*/ COMPOSER_LATEST . "\n";
        //echo "New Version: " . COMPOSER_LATEST . "\n";
    } else {
        $errors['COMPOSER_LATEST'] = $node[0]->nodeValue . ' did not match $version';
    }

    if (defined('COMPOSER_JSON') && !empty(COMPOSER_JSON['json'])) {
        $composer_obj = json_decode(COMPOSER_JSON['json']);
    } else {
        $composer_obj = json_decode(json_encode(new composerConfig(), true));
        $composer_obj->{'require'} = new stdClass(); //(array) ['php' => '7.4||8.1'];
        $composer_obj->{'require'}->{'php'} = '7.4||8.1';
        $composer_obj->{'require-dev'} = new stdClass();
        $composer_obj->{'require-dev'}->{'pds/skeleton'} = '^1.0';
    }

    if (defined('COMPOSER_VERSION') && defined('COMPOSER_LATEST')) { // defined('APP_DEBUG') && APP_DEBUG !== false
    
        //  if (is_file($path = APP_PATH . 'composer.lock') && is_writable($path)) 
//    unlink($path);
    
        if (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0) {

            //dd(basename(COMPOSER_EXEC['bin']) . ' self-update;'); // (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 
            //(APP_SELF !== APP_PATH_SERVER) and 
    
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                Registry::set('logger', new Logger());
                //unset($socketInstance);
                if (!isset($socketInstance)) {
                    $socketInstance = Sockets::getInstance(Registry::get('logger'));
                }

                //$socketInstance->handleClientRequest("composer self-update\n");
    
                if (defined('APP_IS_ONLINE'))
                    if (!isset($GLOBALS['runtime']['socket']) || !is_resource($GLOBALS['runtime']['socket']) || empty($GLOBALS['runtime']['socket'])) {

                        //$proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : /*APP_SUDO . '-u www-data '*/ '') . basename(COMPOSER_EXEC['bin']) . ' self-update', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);
    
                        //[$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    
                        if ($exitCode !== 0) {
                            if (empty($stdout)) {
                                if (!empty($stderr)) {
                                    $errors['COMPOSER-SELF-UPDATE'] = $stderr;
                                    error_log($stderr);
                                }
                            } else {
                                $errors['COMPOSER-SELF-UPDATE'] = $stdout;
                            }
                        }
                    } else {
                        // Connect to the server
                        $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";

                        // Send a message to the server
                        $errors['server-2'] = 'Client request: ' . $message = "cmd: " . basename(COMPOSER_EXEC['bin']) . " self-update\n";
                        /* Known socket  Error / Bug is mis-handled and An established connection was aborted by the software in your host machine */

                        fwrite($GLOBALS['runtime']['socket'], $message);

                        $output[] = trim($message) . ': ';
                        // Read response from the server
                        while (!feof($GLOBALS['runtime']['socket'])) {
                            $response = fgets($GLOBALS['runtime']['socket'], 1024);
                            $errors['server-3'] = "Server responce: $response\n";
                            if (isset($output[end($output)]))
                                $output[end($output)] .= $response = trim($response);
                            //if (!empty($response)) break;
                        }

                        // Close and reopen socket
                        fclose($socketInstance->getSocket());

                    }
            }

            //$proc = proc_open(basename(COMPOSER_EXEC['bin']) . ' self-update;', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);
/*

if (isset($_POST['composer']['self-update']) || file_exists(APP_PATH . 'composer.phar')) {
  if (!file_exists(APP_PATH . 'composer-setup.php'))
    copy('https://getcomposer.org/installer', 'composer-setup.php');
  exec('php composer-setup.php');
}

//fwrite($pipes[0], "yes");
//fclose($pipes[0]);

$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);

fclose($pipes[1]);
fclose($pipes[2]);
*/
            /*
              [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

              if (empty($stdout)) {
                if (!empty($stderr))
                  $errors['COMPOSER_UPDATE'] = $stderr;
              } else $errors['COMPOSER_UPDATE'] = $stdout;
            */
        }

        //die(var_dump($GLOBALS['runtime']['socket']));
    
        if (!is_dir(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR) || !is_file(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'))
            exec((!defined('APP_SUDO') ?: APP_SUDO) . COMPOSER_EXEC['bin'] . ' dump-autoload', $output, $returnCode) or $errors['COMPOSER-DUMP-AUTOLOAD'] = $output;
        else
            if (!empty($composer_obj->{'require'}))
                foreach ($composer_obj->{'require'} as $package => $version) {
                    // $_ENV['COMPOSER']['EXPR_NAME'] is missing because the clients' env file is overriding the global
                    if (defined('COMPOSER_EXPR_NAME') && preg_match(!isset($_ENV['COMPOSER']['EXPR_NAME']) ? COMPOSER_EXPR_NAME : $_ENV['COMPOSER']['EXPR_NAME'] . 'i', $package))
                        continue;  // $package == 'php'
                    elseif (in_array($package, ['php',]))
                        continue;
                    else {
                        //echo $package . ' => ' . $version . "\n" ;
                        $errors['COMPOSER-PACKAGE'] = $package . ' does not match the package. reg_expr=' . (!isset($_ENV['COMPOSER']['EXPR_NAME']) ? (!defined('COMPOSER_EXPR_NAME') ?: COMPOSER_EXPR_NAME) : $_ENV['COMPOSER']['EXPR_NAME']) . "\n";
                        $output = [];
                        $returnCode = 0;
                        exec("composer show $package", $output, $returnCode);

                        if ($returnCode !== 0) {
                            if (isset($composer_obj->{'require'}->{$package}) && is_dir(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $package))
                                continue;
                            if (!empty($composer_obj->{'repositories'}))
                                foreach ($composer_obj->{'repositories'} as $key => $repo) { //unset($composer_obj->{'repositories'});
                                    if (!is_dir(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $package))
                                        continue; // future: consider type->path and/or checking locally and unsetting.
                                    //strcmp("git.php", basename($package) !== 0);
                                    if (!in_array(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $package, array_filter(glob(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . dirname($package) . '/*'), 'is_dir')))
                                        if ($oldpath = preg_grep('/^vendor\/' . preg_quote($package, '/') . '/i', glob(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . dirname($package) . '/*'))[0])
                                            rename($oldpath, APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $package) or $errors['COMPOSER-INSTALL'] = "$package has a vendor/package installed, but the letter case spelling did not pass.";
                                    $repository = new stdClass();
                                    $repository->type = 'path';
                                    $repository->url = 'vendor' . DIRECTORY_SEPARATOR . $package;
                                    if ($repository == $repo)
                                        continue;
                                    else if (!is_dir($repo->url))
                                        unset($composer_obj->{'repositories'}[$key]);
                                    else
                                        $composer_obj->repositories[] = $repository;
                                } else {
                                $repository = new stdClass();
                                $repository->type = 'path';
                                $repository->url = 'vendor' . DIRECTORY_SEPARATOR . $package;
                                if (is_dir($repository->url))
                                    $composer_obj->repositories[] = $repository;
                            }
                        } // else { }
                    }
                }

        //if (!$composer_obj->{'repositories'}) $composer_obj->{'repositories'} = [];  
        if (isset($composer_obj->{'version'}) && !preg_match(COMPOSER_EXPR_VER, $composer_obj->{'version'}))
            unset($composer_obj->{'version'});

        //!isset($composer_obj->{'prefer-stable'})
        //  and $composer_obj->{'prefer-stable'} = true;
    
        if (!is_file(COMPOSER_JSON['path']) && COMPOSER_JSON['path'] !== APP_PATH . APP_ROOT) {
            file_put_contents(COMPOSER_JSON['path'], json_encode($composer_obj, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

            /* Previous unlink('composer.lock') location */

            //if (check_http_status()) {
    
            $vendors = $dirs_diff = [];

            //$dirs = array_filter( glob( 'vendor' . DIRECTORY_SEPARATOR . '*'), 'is_dir');
            if (defined('COMPOSER_VENDORS'))
                foreach (COMPOSER_VENDORS as $vendor => $packages) {
                    if ($vendor == basename('bin'))
                        continue;
                    if ($vendor == 'barrydit')
                        continue;
                    if (in_array(APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $vendor, array_filter(glob(APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $vendor . ''), 'is_dir')))
                        continue;
                    else
                        $dirs_diff[] = basename($vendor);

                    if (!isset($uniqueNames[$vendor])) {
                        $uniqueNames[$vendor] = true;
                        $vendors[] = $vendor;
                    }
                }

            if (!isset($dirs_diff) || empty($dirs_diff))
                $dirs_diff = [];
            //else //dd($dirs_diff);
    
            if (!empty(array_diff($vendors, $dirs_diff))) {

                //if (!isset($GLOBALS['runtime']['socket']) || !$GLOBALS['runtime']['socket']) $GLOBALS['runtime']['socket'] = openSocket(APP_HOST, 12345); // 
    
                (APP_SELF !== APP_PATH_SERVER) and $socketInstance = Sockets::getInstance();
                //$socketInstance->handleClientRequest("composer self-update\n");
                if (!isset($GLOBALS['runtime']['socket']) || !is_resource($GLOBALS['runtime']['socket']) || empty($GLOBALS['runtime']['socket'])) {

                    $sudo_prefix = '';
                    if (defined('APP_SUDO') && trim(APP_SUDO) !== '') {
                        $sudo_prefix = APP_SUDO . ' -u www-data ';
                    }

                    $proc = proc_open($sudo_prefix . basename(COMPOSER_EXEC['bin']) . ' update', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

                    [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

                    if ($exitCode !== 0) {
                        if (empty($stdout)) {
                            if (!empty($stderr)) {
                                $errors['COMPOSER-UPDATE'] = $stderr;
                                error_log($stderr);
                            }
                        } else {
                            $errors['COMPOSER-UPDATE'] = $stdout;
                        }
                    }

                    (preg_match('/Composer is operating significantly slower than normal because you do not have the PHP curl extension enabled./m', $stdout))
                        and $errors['PHP-ext/curl'] = "PHP cURL needs to be installed and enabled.\n";

                } else {

                    $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";

                    // Send a message to the server
                    $errors['server-2'] = 'Client request: ' . $message = "cmd: " . basename(COMPOSER_EXEC['bin']) . " update\n";

                    fwrite($GLOBALS['runtime']['socket'], $message);
                    $output[] = trim($message) . ': ';
                    // Read response from the server
                    while (!feof($GLOBALS['runtime']['socket'])) {
                        $response = fgets($GLOBALS['runtime']['socket'], 1024);
                        $errors['server-3'] = "Server responce: $response\n";
                        if (isset($output[end($output)]))
                            $output[end($output)] .= $response = trim($response);
                        //if (!empty($response)) break;
                    }
                    // Close and reopen socket
                    fclose($socketInstance->getSocket());
                    /*
                    [$server, $port] = explode(PATH_SEPARATOR, SERVER_HOST . PATH_SEPARATOR . SERVER_PORT); // 127.0.0.1:12345   
                    $errors['server-1'] = "Connected to Server: " . $server . PATH_SEPARATOR . $port . "\n"; // APP_PATH_SERVER || APP_HOST

                    // Send a message to the server
                    $errors['server-2'] = 'Client request: ' . $message = "cmd: composer update " . "\n";

                    fwrite($GLOBALS['runtime']['socket'], $message);

                    // Read response from the server
                    while (!feof($GLOBALS['runtime']['socket'])) {
                        $response = fgets($GLOBALS['runtime']['socket'], 1024);
                        $errors['server-3'] = "Server response [2]: $response\n";
                        if (!empty($response)) break;
                    }

                    // Close the connection
                    //fclose($GLOBALS['runtime']['socket']);
                    */
                }
                /*
                        //$proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' update', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

                        [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

                        if ($exitCode !== 0)
                          if (empty($stdout)) {
                            if (!empty($stderr))
                              $errors['COMPOSER-UPDATE'] = $stderr;
                          } else $errors['COMPOSER-UPDATE'] = $stdout;
                      //else $debug['COMPOSER-UPDATE'] = '$stdout=' $stdout . "\n".  '$stderr = ' . $stderr;

                        (preg_match('/Composer is operating significantly slower than normal because you do not have the PHP curl extension enabled./m', $stdout))
                          and $errors['ext/curl'] = 'PHP cURL needs to be installed and enabled.';
                */
            }

        }

        if (!empty($errors) && isset($errors['COMPOSER-UPDATE'])) {

            $problems = [];
            if (preg_match_all('/Problem \d+.*?(?=\r?\nProblem \d+|\r?\n$)/s', $errors['COMPOSER-UPDATE'], $matches)) {
                foreach ($matches[0] as $problem) {
                    // Extract problem ID
                    preg_match('/Problem (\d+)/', $problem, $idMatch);
                    $problemId = $idMatch[1];

                    // Extract items under the problem, excluding paths
                    preg_match_all('/- (?!\/etc\/php\/\d+\.\d+\/cli\/.*\.ini)(.*?)(?=\r?\n(?!\s*- )|\r?\n)/s', $problem, $itemMatches); // --$
                    $items = array_map('trim', $itemMatches[1]);

                    // Store the problem ID and its items
                    $problems = $items;
                }

                // Count of problems
                $problemCount = count($problems);

                // Display the results
                $errors['COMPOSER-PROBLEMS'] = "Total Problems: $problemCount\n";
                $errors['COMPOSER-PROBARRAY'] = var_export($problems, true);
            } else {
                $errors['COMPOSER-PROBLEMS'] = "No problems found.\n";
            }

            if (empty($problems) && preg_match_all('/Problem \d+.*?(?=\r?\n)$/s', $errors['COMPOSER-UPDATE'], $matches)) {
                $problems = [];
                foreach ($matches[0] as $problem) {
                    // Extract problem ID
                    preg_match('/Problem (\d+)/', $problem, $idMatch);
                    $problemId = $idMatch[1];

                    // Extract items under the problem, excluding paths
                    preg_match_all('/- (?!\/etc\/php\/\d+\.\d+\/cli\/.*\.ini)(.*?)(?=\r?\n(?!\s*- )|\r?\n)/s', $problem, $itemMatches); // --$
                    $items = array_map('trim', $itemMatches[1]);

                    // Store the problem ID and its items
                    $problems = $items;
                }

                // Count of problems
                $problemCount = count($problems);

                // Display the results
                $errors['COMPOSER-PROBLEMS'] = "Total Problems: $problemCount\n";
                $errors['COMPOSER-PROBARRAY'] = var_export($problems, true);
            } else {
                $errors['COMPOSER-PROBLEMS'] = "No problems found.\n";
            }

            if (preg_match_all('/To enable extensions, verify that they are enabled in your \.ini files:.*?(?=\r?\n$)/s', $errors['COMPOSER-UPDATE'], $matches)) {
                $ini_files = [];
                foreach ($matches[0] as $ini_file) {

                    // Extract items under the problem, excluding paths
                    preg_match_all('/(?=\/etc\/php\/\d+\.\d+\/cli\/.*\.ini)(.*?)(?=\r?\n(?!\s*- )|\r?\n)/s', $ini_file, $itemMatches); // --$
                    $items = array_map('trim', $itemMatches[0]);

                    // Store the problem ID and its items
                    $ini_files = $items;
                }

                $base_names = array_map(function ($path) {
                    // Extract the filename without the extension
                    $filename = preg_replace('/\.ini$/', '', basename($path));
                    return preg_replace('/^\d+-/', '', $filename);
                }, $ini_files);

                // Count of problems
                $ini_files_count = count($base_names);

                // Display the results
                $errors['COMPOSER-INI-FILES'] = "Total Ini Files: $ini_files_count\n";
                $errors['COMPOSER-INIARRAY'] = var_export($base_names, true);
            } else {
                $errors['COMPOSER-INI-FILES'] = "No Ini Files found.\n";
            }


            if (preg_match('/^.*Problem \d*(\r?\n)*.*- Root composer\.json requires ([a-z0-9](?:[_.-]?[a-z0-9]+)*\/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+)) (\^v?\d+(?:\.\d+){0,3}|^dev-.*), it is satisfiable by (?:[a-z0-9](?:[_.-]?[a-z0-9]+)*\/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+))\[\d+(?:\.\d+){0,3}\] from composer repo \((?:[a-z]+\:\/\/)?(?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*)?\) but (?:[a-z0-9](?:[_.-]?[a-z0-9]+)*\/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+))\[(.*)\]/m', $errors['COMPOSER-UPDATE'], $matches)) {
                if (preg_match('/(v?\d+(?:\.\d+){0,3})/', $matches[4]))
                    $composer_obj->require->{$matches[2]} = '^' . $matches[4];
                elseif (preg_match('/(dev-.*)/', $matches[4]))
                    $composer_obj->require->{$matches[2]} = $matches[4];

                file_put_contents(COMPOSER_JSON['path'], json_encode($composer_obj, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                unset($errors['COMPOSER-UPDATE']);
            }

            if (preg_match('/^.*Problem \d*(?:\r?\n)*.*- Root composer\.json requires ([a-z0-9](?:[_.-]?[a-z0-9]+)*\/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+)) (v?\d+(?:\.\d+){0,3}|dev-.*)\, found ([a-z0-9](?:[_.-]?[a-z0-9]+)*\/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+))\s*\[(v?\d+(?:\.\d+){0,3}|dev-.*)(?:,|$)/m', $errors['COMPOSER-UPDATE'], $matches)) {
                // Split the fourth element by commas and extract the first part
                $constraint_parts = explode(', ', $matches[4]);
                $first_element = reset($constraint_parts);

                if (preg_match('/(v?\d+(?:\.\d+){0,3})/', $first_element))
                    $composer_obj->require->{$matches[1]} = "^$first_element";
                elseif (preg_match('/(dev-.*)/', $first_element))
                    $composer_obj->require->{$matches[1]} = $first_element;

                file_put_contents(COMPOSER_JSON['path'], json_encode($composer_obj, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                unset($errors['COMPOSER-UPDATE']);
            }
        }
        //}
    
        //     while() { $errors['COMPOSER-UPDATE'] } // loop for 5 attempts to fix a problem 
    
        if (!is_file('composer.lock')) {
            /**
              Optimization
            **/

            // composer clear-cache
    

            putenv('COMPOSER_HOME='); // TESTING
    
            //(APP_SELF !== APP_PATH_SERVER) and $socketInstance = Sockets::getInstance();
            //$socketInstance->handleClientRequest("composer self-update\n");
            if (!isset($GLOBALS['runtime']['socket']) || !is_resource($GLOBALS['runtime']['socket']) || empty($GLOBALS['runtime']['socket'])) {
                $proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' install -o', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

                [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

                if ($exitCode !== 0) {
                    if (empty($stdout)) {
                        if (!empty($stderr)) {
                            $errors['COMPOSER-INSTALL'] = $stderr;
                            error_log($stderr);
                        }
                    } else {
                        $errors['COMPOSER-INSTALL'] = $stdout;
                    }
                }

            } else {
                $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";

                // Send a message to the server
                $errors['server-2'] = 'Client request: ' . $message = 'cmd: ' . COMPOSER_EXEC['bin'] . " install -o\n";
                /* Known socket  Error / Bug is mis-handled and An established connection was aborted by the software in your host machine */
                fwrite($GLOBALS['runtime']['socket'], $message);
                $output[] = trim($message) . ': ';
                // Read response from the server
                while (!feof($GLOBALS['runtime']['socket'])) {
                    $response = fgets($GLOBALS['runtime']['socket'], 1024);
                    $errors['server-3'] = "Server responce: $response\n";
                    if (isset($output[end($output)]))
                        $output[end($output)] .= $response = trim($response);
                    //if (!empty($response)) break;
                }
                // Close and reopen socket
                fclose($socketInstance->getSocket());
            }



            /*
                //$proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' install -o', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

                [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

                if ($exitCode !== 0)
                  if (empty($stdout)) {
                    if (!empty($stderr))
                      $errors['COMPOSER-INSTALL'] = $stderr;
                  } else $errors['COMPOSER-INSTALL'] = $stdout;
            */
            //else $debug['COMPOSER-INSTALL'] = '$stdout=' $stdout . "\n".  '$stderr = ' . $stderr;
    
            //exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' install -o', $output, $returnCode) or $errors['COMPOSER-INSTALL'] = $output;
    
        }

        // https://getcomposer.org/doc/03-cli.md
//  $proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' validate --no-check-all --no-check-publish --no-check-version --strict', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes); // $output = shell_exec("cd " . escapeshellarg(dirname(COMPOSER_JSON['path'])) . " && " . APP_SUDO . COMPOSER_EXEC . ' validate --no-check-all --no-check-publish --no-check-version');  dd($output);
    
        //  "./composer.json" does not match the expected JSON schema:  
        //  - NULL value found, but an object is required
    
        // poss. err './composer.json is valid but your composer.lock has some errors'   checks composer.lock
/*
  [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

  if ($exitCode !== 0) {
    if (!empty($stdout)) {
      $errors['COMPOSER-VALIDATE'] = $stdout;

      if (preg_match('/(?:\s*)?\"\.\/composer\.json\" does not match the expected JSON schema:/', $stdout))
        $errors['COMPOSER-VALIDATE-JSON'] = false; //'$stdout is empty. $stderr = ' . $stderr;
      
      if (preg_match('/(?:\s*)?\.\/composer\.json is valid but your composer.lock has some errors/', $stdout)) // took off \"\"
        $errors['COMPOSER-VALIDATE-LOCK'] = false; //'$stdout is empty. $stderr = ' . $stderr;
    }

    if (!empty($stderr)) {
      $errors['COMPOSER-VALIDATE-ERR'] = $stderr;

      if (preg_match('/(?:\s*)?\"\.\/composer\.json\" does not contain valid JSON/', $stderr))
        $errors['COMPOSER-VALIDATE-JSON'] = false; //'$stdout is empty. $stderr = ' . $stderr;
    }
    //dd($errors);
  }
*/


        //if (strpos($output, 'No errors or warnings detected') !== false)
//Deprecated:  strpos(): Passing null to parameter #1 ($haystack) of type string is deprecated
    
        defined('COMPOSER_JSON') && COMPOSER_JSON['path'] !== APP_PATH . APP_ROOT
            and define('COMPOSER', ['json' => json_decode(file_get_contents($path = COMPOSER_JSON['path'])), 'path' => $path]);

        if (defined('COMPOSER') && isset(COMPOSER['json']->{'require'}) && !empty(COMPOSER['json']->{'require'}))
            foreach (COMPOSER['json']->require as $key => $value) {
                switch ($key) {
                    case 'php':
                        continue 2;
                    default:
                        if (isset(COMPOSER['json']->require->{'composer/composer'}) && $value === COMPOSER['json']->require->{'composer/composer'}) {
                            //echo "The key is: $key";
                            defined('VENDOR_JSON')
                                or define('VENDOR_JSON', ['json' => (is_file(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $key . '/composer.json') ? file_get_contents(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $key . '/composer.json') : '{}'), 'path' => APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . $key . '/composer.json']);

                            if (realpath(VENDOR_JSON['path']))
                                defined('VENDOR_JSON')
                                    and define('VENDOR', json_decode(file_get_contents(VENDOR_JSON['path'])));
                            break 2;
                        } else {
                            defined('VENDOR_JSON') or define('VENDOR_JSON', ['json' => '{}', 'path' => '']);
                        }
                        break;
                }
            }

        //dd(COMPOSER['json']->{'require'}->{'php'}); 
    
        if (!defined('VENDOR_JSON') && defined('COMPOSER') && isset(COMPOSER['json']->{'require'}->{'composer'}))
            define('VENDOR_JSON', [
                'json' => (is_file(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'composer.json') ? file_get_contents(APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'composer.json') : '{}'),
                'path' => APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'composer/composer.json'
            ]);


        //dd(VENDOR_JSON['path']);
    
        //else $errors['COMPOSER-VALIDATE'] = $output;
    
        //dd(get_defined_constants(true)['user']);
//dd(COMPOSER_EXEC . '  ' . COMPOSER_VERSION);
    

        header("Access-Control-Allow-Origin: *"); // Allow all origins
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        if (in_array(__FILE__, get_required_files())) {
            //echo "File $target_file was included.";
    
            // die(var_dump(dirname(getcwd())));
            if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"])) {
                if (is_file($path = dirname(getcwd()) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php')) {
                }
                //require_once $path;
                else
                    die(var_dump("$path path was not found. file=" . basename($path)));
            }

            if (is_file($path = APP_PATH . 'api' . DIRECTORY_SEPARATOR . 'composer.php'))
                require_once $path;
            //else
            //  die(var_dump("$path path was not found. file=" . basename($path)));
    
            //if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
            // if (is_file($path = realpath('index.php'))) require_once $path;
            //} else
            //  die(var_dump("Path was not found. file=$path"));
    
            /*
            if (__FILE__ == get_required_files()[0])
              if ($path = (basename(getcwd()) == 'public')
                ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '') require_once $path;
              else die(var_dump("$path path was not found. file=config.php"));
            */
            /*
          else {
            $path = APP_PATH . 'api' . DIRECTORY_SEPARATOR . 'composer.php';
          dd($path);
            if (is_file($path))
          dd(get_included_files(), false);
              require_once $path; 
            else
              die(var_dump("$path path was not found. file=composer.php"));
          }*/

            // require 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'; // Include Composer's autoloader
    
            /*
            use Composer\Factory;
            use Composer\Repository\InstalledRepositoryInterface;

            // Initialize Composer
            $composer = Factory::create();

            // Get the installed packages repository
            $installedRepo = $composer->getRepositoryManager()->getLocalRepository();

            // Get a list of installed packages
            $installedPackages = $installedRepo->getPackages();

            // Print the list of installed packages
            foreach ($installedPackages as $package) {
                echo $package->getName() . ' (' . $package->getVersion() . ')' . PHP_EOL;
            }
            exit;

            */


            // composer create-project [PACKAGE] [DESTINATION PATH] [--FLAGS]
//composer create-project laravel/laravel example-app
    

            //cd example-app
    
            //php artisan serve
    
            // if ($_SERVER['REQUEST_METHOD'] == 'POST') { }
    
            /*

            Workflows and Projects

            PHP   .github / workflows / php.yml
            Build and test a PHP application using Composer

            SLSA Generic generator
            Generate SLSA3 provenance for your existing release workflows

            Jekyll using Docker image
            Package a Jekyll site using the jekyll/builder Docker image.

            Laravel
            Test a Laravel project.

            Symfony
            Test a Symfony project.

            Publish Node.js Package
            Publishes a Node.js package to npm.

            Publish Node.js Package to GitHub Packages
            Publishes a Node.js package to GitHub Packages.

            if (!in_array(APP_PATH . 'api' . DIRECTORY_SEPARATOR . 'composer.php', get_required_files())) {
              require_once APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
              if ($path = (basename(getcwd()) == 'public')
                ? (is_file('../composer.php') ? '../composer.php' : (is_file('../public/api/composer.php') ? '../public/api/composer.php' : null))
                : (is_file('composer.php') ? 'composer.php' : (is_file('public/api/composer.php') ? 'public/api/composer.php' : null))) require_once $path; 
              else die(var_dump("$path path was not found. file=composer.php"));
            }
            */
            /*
            if (!in_array(APP_PATH . 'public' . DIRECTORY_SEPARATOR . 'app.console.php', get_required_files()))
              if ($path = (basename(getcwd()) == 'public')
                ? (is_file('app.console.php') ? 'app.console.php' : (is_file('../config/app.console.php') ? '../config/app.console.php' : null))
                : (is_file('app.console.php') ? 'app.console.php' : (is_file('public/app.console.php') ? 'public/app.console.php' : 'app.console.php'))) require_once $path; 
            else die(var_dump("$path path was not found. file=app.console.php"));
            */
            /*  ...
                "autoload": {
                    "psr-4": {
                        "HtmlToRtf\\": "src/HtmlToRtf",
                        "ProgressNotes\\": "src/ProgressNotes"
                    }
                }
            */
            //dd(get_required_files());
            //dd(get_defined_constants(true)['user']);
        }
    }