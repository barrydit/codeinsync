<?php

define('COMPOSER_EXPR_NAME', '/([a-z0-9](?:[_.-]?[a-z0-9]+)*)\/([a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+)*)/'); // name
define('COMPOSER_EXPR_VER', '/v?\d+(?:\.\d+){0,3}|dev-.*/'); // version

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

define('COMPOSER_ALLOW_SUPERUSER', true);
putenv('COMPOSER_ALLOW_SUPERUSER=' . (int) COMPOSER_ALLOW_SUPERUSER);

define('COMPOSER_ALLOW_XDEBUG', false); // didn't work
putenv('COMPOSER_ALLOW_XDEBUG=' . (int) COMPOSER_ALLOW_XDEBUG);

putenv('COMPOSER_DISABLE_XDEBUG_WARN=' . (int) true);

//dd(getenv('COMPOSER_ALLOW_SUPERUSER'));

class composerSchema {
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
}

if (!function_exists('get_declared_classes')) {
  $autoloadContent = file_get_contents('vendor/autoload.php');
  if (!preg_match('/class\s+ComposerAutoloaderInit([a-f0-9]+)/', $autoloadContent, $matches))
    $errors['COMPOSER-AutoloaderInit'] = 'ComposerAutoloaderInit failed to be matched.';
} else
  if (!empty($classes = get_declared_classes()))
    foreach($classes as $key => $class) {
      if (preg_match('/(ComposerAutoloaderInit[a-f0-9]+)/', $class, $matches)) 
        break;
      if ($class == end($classes))
        $errors['COMPOSER-AutoloaderInit'] = 'ComposerAutloaderInit failed to be matched.' . "\n";
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
if (class_exists('Composer\Autoload\ClassLoader')) {
    $loadedLibraries[] = 'Composer\Autoload\ClassLoader';
}

// Check if a library is loaded
if (in_array('Composer\Autoload\ClassLoader', $loadedLibraries)) {
    // The library is loaded
//  echo 'Library found.';
    //$loadedLibraries;
    
    $installedPackages = \Composer\InstalledVersions::getInstalledPackages();
}

//dd();
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

$composerUser = 'barrydit';
$componetPkg = 'composer_app';
$composerHome = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ?
  'C:/Users/' . (getenv('USERNAME') ? getenv('USERNAME') : getenv('USER')) . '/AppData/Roaming/Composer/' : 
  (($user = (getenv('USERNAME') ? getenv('USERNAME') : getenv('USER'))) == 'root' ?
    '/' . $user . '/.composer/' :
    '/home/' . $user . '/.composer/'  
  ) 
);

if (!realpath($composerHome)) {
  if (!mkdir($composerHome, 0755, true))
    $errors['COMPOSER_HOME'] = $composerHome . ' does not exist. Path: ' . $composerHome;
} else define('COMPOSER_HOME', $composerHome);

//dd($errors);
//dd('Composer Home: ' . $composerHome, 0);

putenv('COMPOSER_HOME=' . $composerHome ?? '/var/www/.composer/');

if (!file_exists(APP_PATH . 'composer.phar')) {
  if (!file_exists(APP_PATH . 'composer-setup.php'))
    copy('https://getcomposer.org/installer', 'composer-setup.php');
  
  $error = exec('php composer-setup.php'); // php -d register_argc_argv=1

  $errors['COMPOSER-PHAR'] = 'Composer setup was executed and ' . (file_exists(APP_PATH.'composer.phar') ? 'does' : 'does not') . ' exist. version='.exec('php composer.phar -V') . '  error=' . $error;
} else {

  if (preg_match('/Composer(?: version)? (\d+\.\d+\.\d+) (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', exec(($bin = 'php composer.phar') . ' -V'), $matches))
    define('COMPOSER_PHAR', ['bin' => $bin, 'version' => $matches[1], 'date' => $matches[2]]);
}

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { // DO NOT REMOVE! { .. }
    if (file_exists('C:\ProgramData\ComposerSetup\bin\composer.phar')) {
      if (preg_match('/Composer(?: version)? (\d+\.\d+\.\d+) (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', exec($bin = 'php C:\ProgramData\ComposerSetup\bin\composer.phar' . ' -V'), $matches))
        !defined('COMPOSER_PHAR') and define('COMPOSER_PHAR', ['bin' => $bin, 'version' => $matches[1], 'date' => $matches[2]]);
      !defined('COMPOSER_BIN') and define('COMPOSER_BIN', COMPOSER_PHAR);
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
    foreach(array('/usr/local/bin/composer', 'php ' . APP_PATH . 'composer.phar', '/usr/bin/composer') as $key => $bin) {
        !isset($composer) and $composer = array();

/*//*/
        $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . $bin . ' --version;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        $exitCode = proc_close($proc);

        if (preg_match('/Composer(?: version)? (\d+\.\d+\.\d+) (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $stdout, $matches)) {
            $composer[$key]['bin'] = $bin;
            $composer[$key]['version'] = $matches[1];
            $composer[$key]['date'] = $matches[2];        
        } else {
          if (empty($stdout)) {
            if (!empty($stderr))
              $errors['COMPOSER_VERSION'] = $stderr;
          } else $errors['COMPOSER_VERSION'] = $stdout; // else $errors['COMPOSER_VERSION'] = $stdout . ' does not match $version'; }
        }
    }

    usort($composer, function($a, $b) {
        return version_compare($b['version'], $a['version']); // Sort in descending order based on version
    });

    if (empty($composer)) $errors['COMPOSER-BIN'] = 'There are no composer binaries.';
    else 
      foreach ($composer as $key => $exec) {
        if ($key == 0 || $key == 1) {

          if (preg_match('/^php.*composer\.phar$/', $exec['bin'])) !defined('COMPOSER_PHAR') and define('COMPOSER_PHAR', $exec);
          else !defined('COMPOSER_BIN') and define('COMPOSER_BIN', $exec);

          continue; // !break 2-loops
        } else break;
      }
}

// dd(COMPOSER_PHAR, 0);
// dd(COMPOSER_BIN, 0);

defined('COMPOSER_EXEC')
  or define('COMPOSER_EXEC', (isset($_GET['exec']) ? ($_GET['exec'] == 'phar' ? COMPOSER_PHAR : COMPOSER_BIN) : COMPOSER_BIN ?? COMPOSER_PHAR));

if (is_array(COMPOSER_EXEC))
  define('COMPOSER_VERSION', COMPOSER_EXEC['version']);
else
  define('COMPOSER_VERSION', COMPOSER_PHAR['version']);

$configJsonPath = COMPOSER_HOME . 'config.json';

if (!file_exists($configJsonPath)) {
    if (!touch($configJsonPath)) {
      $errors['COMPOSER_CONFIG'] = $configJsonPath . ' is unable to be created.';
    } else {
      file_put_contents($configJsonPath, '{}');
    }
}

if (realpath($configJsonPath)) {
    define('COMPOSER_CONFIG', [
      'json' => '{}',
      'path' => $configJsonPath
    ]);
}

$authJsonPath = COMPOSER_HOME . 'auth.json';

if (!file_exists($authJsonPath)) {
    if (!touch($authJsonPath)) {
      $errors['COMPOSER_AUTH'] = $authJsonPath . ' is unable to be created.';
    } else {
      file_put_contents($authJsonPath, '{"github-oauth": {"github.com": ""}}');
    }
}

if (realpath($authJsonPath)) {
  putenv('COMPOSER_AUTH=' . (filesize($authJsonPath) == 0 || trim(file_get_contents($authJsonPath)) == false ? '{"github-oauth": {"github.com": ""}}' : trim(str_replace([' ', "\r\n", "\n", "\r"], '', file_get_contents($authJsonPath)))));

  define('COMPOSER_AUTH', [
    'json' => getenv('COMPOSER_AUTH'),
    'path' => $authJsonPath,
    'token' => json_decode(getenv('COMPOSER_AUTH')/*, true */)->{'github-oauth'}->{'github.com'}
    ]);
}

putenv('COMPOSER_TOKEN=' . (COMPOSER_AUTH['token'] ?? 'static token')); // <GITHUB_ACCESS_TOKEN>

putenv('PWD=' . APP_PATH);

//dd(file_get_contents($authJsonPath)); // json_decode(getenv('COMPOSER_AUTH') ?? file_get_contents($authJsonPath) /*, true */)

/*
  This section of code will need to correspond to a project
  
    A project file will need to look for first, and then look for the applications' composer.json
    
       Can a constant be a object, or does an object need to be able to write to itself ...
     
       If !defined(COMPOSER_JSON) and define('COMPOSER_JSON', APP_PATH . '/composer.json');
*/

/* library, project, metapackage, composer-plugin ... Package type */

$composer_exec = (COMPOSER_EXEC['bin'] == COMPOSER_PHAR['bin'] ? COMPOSER_PHAR['bin'] : basename(COMPOSER_EXEC['bin']));

/*
APP_WORK[client]

APP_CLIENT / APP_PROJECT APP_ {key(APP_WORK)}
  [path]
  [user]
*/

if (defined('APP_ENV') and APP_ENV == 'development') {
  if (defined('APP_CLIENT') || defined('APP_PROJECT'))
    $$c_or_p = APP_CLIENT ?? APP_PROJECT;
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

    ob_start(); ?>
<?= $composer_exec; ?> init --quiet --no-interaction --working-dir="<?= APP_PATH . APP_ROOT; ?>" --name="<?= $composerUser . '/' . $$c_or_p->name; ?>" --description="General Description" --author="Barry Dick <barryd.it@gmail.com>" --type="project" --homepage="https://github.com/<?= $composerUser . '/' . $$c_or_p->name; ?>"" --require="php:^7.4||^8.0" --require="composer/composer:^1.0" --require-dev="pds/skeleton:^1.0" --stability="dev" --license="WTFPL"
<?php
    defined('COMPOSER_INIT_PARAMS')
      or define('COMPOSER_INIT_PARAMS', /*<<<TEXT TEXT*/ ob_get_contents());
    ob_end_clean();
    
  if (!is_dir($$c_or_p->path . 'vendor'))
    $errors['COMPOSER_INIT-VENDOR'] = 'Failed to create the vendor/ directory. If you are seeing this. An error has occured.';
    
    //@mkdir($$c_or_p->path . 'vendor');

  

// composer init --require=twig/twig:1.13.* -n   // https://webrewrite.com/create-composer-json-file-php-project/

// composer init --quiet --no-interaction --working-dir="{$$c_or_p->path}" --require=php:^7.4|^8.0

// --require-dev="phpunit/phpunit:^9.5.20"
// --autoload="src/"
    if (file_exists($$c_or_p->path . 'composer.json')) {

  // clean up json -- preg_replace('/[\x00-\x1F\x80-\xFF]/', '', str_replace('\\', '\\\\', '{...}'))

  //($err = json_decode(str_replace('\\', '\\\\', file_get_contents($$c_or_p->path . 'composer.json')), null, 512, JSON_THROW_ON_ERROR)) and $error['COMPOSER-JSON'] = 'Invalid JSON: ' . $err;

      if (!defined('COMPOSER_JSON'))
        define('COMPOSER_JSON', ['json' =>  file_get_contents($$c_or_p->path . 'composer.json'), 'path' => $$c_or_p->path . 'composer.json']);

    } else {
  // php composer.phar init

  // /usr/share/php/Symfony/Component/Console/Helper/HelperSet.php
  // Deprecated: Return type of Symfony\Component\Console\Helper\HelperSet::getIterator() should either be compatible with IteratorAggregate::getIterator()
  // Traversable, or the #[\ReturnTypeWillChange] attribute should be used to temporarily suppress the notice in /usr/share/php/Symfony/Component/Console/Helper/HelperSet.php on line 103

  // 'COMPOSER_BIN init' >> Symfony\Component\Console\Helper\...
/*  This code would be used to create 
  $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . COMPOSER_INIT_PARAMS, array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

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

defined('COMPOSER_JSON')
      or define('COMPOSER_JSON', ['json' => (is_file(APP_PATH . 'composer.json') ? file_get_contents(APP_PATH . 'composer.json') : '{}'), 'path' => APP_PATH . 'composer.json']);

ob_start(); 

$root = APP_ROOT ?? ''; ?>
<?= $composer_exec; ?> init --quiet --no-interaction --working-dir="<?= APP_PATH . $root; ?>" --name="<?= $composerUser . '/' . str_replace('.', '_', basename($root) ?? $componetPkg); ?>" --description="General Description" --author="Barry Dick <barryd.it@gmail.com>" --type="project" --homepage="https://github.com/<?= $composerUser . '/' . str_replace('.', '_', basename($root) ?? $componetPkg); ?>" --require="php:^7.4||^8.0" --require="composer/composer:^1.0" --require-dev="pds/skeleton:^1.0" --stability="dev" --license="WTFPL"
<?php
defined('COMPOSER_INIT_PARAMS')
  or define('COMPOSER_INIT_PARAMS', /*<<<TEXT TEXT*/ ob_get_contents());
ob_end_clean();




if (!realpath('vendor')) {
  exec(COMPOSER_INIT_PARAMS);
} elseif (!realpath('vendor/autoload.php')) {
    exec((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' update', $output, $returnCode) or $errors['COMPOSER-INIT-UPDATE'] = $output;
    exec((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' dump-autoload', $output, $returnCode) or $errors['COMPOSER-DUMP-AUTOLOAD'] = $output;
  }

// dd(getcwd());

/** Loading Time: 0.134s **/

  //dd(get_required_files(), true);

// moved to config.php load (last)
// is_file('vendor/autoload.php') and require('vendor/autoload.php'); // Include Composer's autoloader

//dd(get_required_files());
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

//dd(get_declared_classes());




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


//dd(APP_PATH);

//require __DIR__ . '/../vendor/Git.php/src/Git.php';
//require __DIR__ . '/../vendor/Git.php/src/GitRepo.php';

//use Kbjr\Git\Git;
//use Kbjr\Git\GitRepo;

//dd(var_dump(APP_BASE));

//dd(APP_PATH . APP_BASE['var']);

// file has to exists first

is_dir(APP_PATH . APP_BASE['var']) or mkdir(APP_PATH . APP_BASE['var'], 0755);
if (is_file(APP_PATH . APP_BASE['var'] . 'getcomposer.org.html')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime(APP_PATH . APP_BASE['var'] . '/getcomposer.org.html'))))) / 86400)) <= 0 ) {
    $url = 'https://getcomposer.org/';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($html = curl_exec($handle))) 
      file_put_contents(APP_PATH . APP_BASE['var'] . 'getcomposer.org.html', $html) or $errors['COMPOSER_LATEST'] = $url . ' returned empty.';
  }
} else {
  $url = 'https://getcomposer.org/';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($html = curl_exec($handle))) 
    file_put_contents(APP_PATH . APP_BASE['var'] . 'getcomposer.org.html', $html) or $errors['COMPOSER_LATEST'] = $url . ' returned empty.';
}
libxml_use_internal_errors(true); // Prevent HTML errors from displaying
$doc = new DOMDocument(1.0, 'utf-8');
$doc->loadHTML(file_get_contents(APP_PATH . APP_BASE['var'] . 'getcomposer.org.html'));

$content_node=$doc->getElementById("main");

$node=getElementsByClass($content_node, 'p', 'latest');

//$xpath = new DOMXpath ( $doc ); //$xpath->query ( '//p [contains (@class, "latest")]' );
//dd($xpath);

$pattern = '/Latest: (\d+\.\d+\.\d+) \(\w+\)/';

if (preg_match($pattern, $node[0]->nodeValue, $matches)) {
  $version = $matches[1];

  define('COMPOSER_LATEST', $version);
  //echo "New Version: " . COMPOSER_LATEST . "\n";
} else $errors['COMPOSER_LATEST'] = $node[0]->nodeValue . ' did not match $version';

if (defined('COMPOSER_JSON') && !empty(COMPOSER_JSON['json']))
  $composer_obj = json_decode(COMPOSER_JSON['json']);
else {
  $composer_obj = json_decode(json_encode(new composerSchema(), true)); 
  $composer_obj->{'require'} = new stdClass(); //(array) ['php' => '7.4||8.1'];
  $composer_obj->{'require'}->{'php'} = '7.4||8.1';
  $composer_obj->{'require-dev'} = new stdClass();
  $composer_obj->{'require-dev'}->{'pds/skeleton'} = '^1.0';
}



if (defined('COMPOSER_VERSION') && defined('COMPOSER_LATEST') && defined('APP_DEBUG')) {
//  if (is_file($path = APP_PATH . 'composer.lock') && is_writable($path)) 
//    unlink($path);

  if (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0) {
    $proc = proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' self-update;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

/*
//fwrite($pipes[0], "yes");
//fclose($pipes[0]);

$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);

fclose($pipes[1]);
fclose($pipes[2]);
*/
/**/
    list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    
    if (empty($stdout)) {
      if (!empty($stderr))
        $errors['COMPOSER_UPDATE'] = $stderr;
    } else $errors['COMPOSER_UPDATE'] = $stdout;

  }

  if (!is_dir('vendor') || !is_file('vendor/autoload.php'))
    exec((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' dump-autoload', $output, $returnCode) or $errors['COMPOSER-DUMP-AUTOLOAD'] = $output;
  else
    if (!empty($composer_obj->{'require'}))
      foreach ($composer_obj->{'require'} as $package => $version) {
        if (preg_match(COMPOSER_EXPR_NAME . 'i', $package)) continue;  // $package == 'php'
        elseif (in_array($package, ['php',])) continue;
        else {
        //echo $package . ' => ' . $version . "\n" ;
          $errors['COMPOSER-PACKAGE'] = $package . ' does not match the package. reg_expr=' . COMPOSER_EXPR_NAME ;
          $output = [];
          $returnCode = 0;
          exec("composer show $package", $output, $returnCode);

          if ($returnCode !== 0) {
            if (isset($composer_obj->{'require'}->{$package}) && is_dir('vendor/'.$package)) continue;
            if (!empty($composer_obj->{'repositories'}))
              foreach ($composer_obj->{'repositories'} as $key => $repo) { //unset($composer_obj->{'repositories'});
                if (!is_dir('vendor/'.$package)) continue; // future: consider type->path and/or checking locally and unsetting.
                //strcmp("git.php", basename($package) !== 0);
                if (!in_array('vendor/' . $package, array_filter(glob('vendor/' . dirname($package) . '/*'), 'is_dir')))
                  if ($oldpath = preg_grep('/^vendor\/' . preg_quote($package, '/') . '/i', glob('vendor/' . dirname($package) . '/*'))[0])
                    rename($oldpath, 'vendor/' . $package) or $errors['COMPOSER-INSTALL'] = $package . ' has a vendor/package installed, but the letter case spelling did not pass.';
                $repository = new stdClass();
                $repository->type = 'path';
                $repository->url = 'vendor/' . $package;
                if ($repository == $repo) continue;
                else if (!is_dir($repo->url)) unset($composer_obj->{'repositories'}[$key]);
                else $composer_obj->repositories[] = $repository;
              }
            else {
              $repository = new stdClass();
              $repository->type = 'path';
              $repository->url = 'vendor/' . $package;
              if (is_dir($repository->url)) $composer_obj->repositories[] = $repository;
            }
          } // else { }
        }
      }

  //if (!$composer_obj->{'repositories'}) $composer_obj->{'repositories'} = [];  
  if (isset($composer_obj->{'version'}) && !preg_match(COMPOSER_EXPR_VER, $composer_obj->{'version'}))
    unset($composer_obj->{'version'});

  //!isset($composer_obj->{'prefer-stable'})
  //  and $composer_obj->{'prefer-stable'} = true;
  
  if (!is_file(COMPOSER_JSON['path']))
    file_put_contents(COMPOSER_JSON['path'], json_encode($composer_obj, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

/* Previous unlink('composer.lock') location */

  if (check_http_200()) {

    $vendors = $dirs_diff = [];

//$dirs = array_filter( glob( 'vendor/*'), 'is_dir');
    if (defined('COMPOSER_VENDORS'))
      foreach (COMPOSER_VENDORS as $vendor => $packages) {
        if ($vendor == basename('bin')) continue;
        if ($vendor == 'barrydit') continue;
        if (in_array(APP_ROOT . 'vendor/' . $vendor, array_filter(glob(APP_ROOT . 'vendor/' . $vendor . ''), 'is_dir'))) continue;
        else $dirs_diff[] = basename($vendor);

        if (!isset($uniqueNames[$vendor])) {
          $uniqueNames[$vendor] = true;
          $vendors[] = $vendor;
        }
      }

    if (!isset($dirs_diff) && !empty($dirs_diff))
      dd($dirs_diff);
    else $dirs_diff = [];

//dd($vendors);


//dd('composer timeout', false);

    if (!empty(array_diff($vendors, $dirs_diff)) ) {
      $proc = proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' update', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

      list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

      if ($exitCode !== 0)
        if (empty($stdout)) {
          if (!empty($stderr))
            $errors['COMPOSER-UPDATE'] = $stderr;
        } else $errors['COMPOSER-UPDATE'] = $stdout;
    //else $debug['COMPOSER-UPDATE'] = '$stdout=' $stdout . "\n".  '$stderr = ' . $stderr;
    
      if (preg_match('/^.*Composer is operating significantly slower than normal because you do not have the PHP curl extension enabled./m', $stdout)) {
        $errors['composer-curl-error'] = 'PHP cURL needs to be installed and enabled.';
      }

    }

  if (!empty($errors) && isset($errors['COMPOSER-UPDATE'])) {
    if (preg_match('/^.*Problem \d*(\r?\n)*.*- Root composer\.json requires ([a-z0-9](?:[_.-]?[a-z0-9]+)*\/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+)) (\^v?\d+(?:\.\d+){0,3}|^dev-.*), it is satisfiable by (?:[a-z0-9](?:[_.-]?[a-z0-9]+)*\/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+))\[\d+(?:\.\d+){0,3}\] from composer repo \((?:[a-z]+\:\/\/)?(?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*)?\) but (?:[a-z0-9](?:[_.-]?[a-z0-9]+)*\/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+))\[(.*)\]/m', $errors['COMPOSER-UPDATE'], $matches)) {
      if (preg_match('/(v?\d+(?:\.\d+){0,3})/', $matches[4]))      
        $composer_obj->require->{$matches[2]} = '^' . $matches[4];
      elseif (preg_match('/(dev-.*)/', $matches[4]))
        $composer_obj->require->{$matches[2]} = $matches[4];

      file_put_contents(COMPOSER_JSON['path'], json_encode($composer_obj, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
      unset($errors['COMPOSER-UPDATE']);
    }

    if (preg_match('/^.*Problem \d*(?:\r?\n)*.*- Root composer\.json requires ([a-z0-9](?:[_.-]?[a-z0-9]+)*\/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+)) (v?\d+(?:\.\d+){0,3}|dev-.*)\, found ([a-z0-9](?:[_.-]?[a-z0-9]+)*\/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+))\s*\[(v?\d+(?:\.\d+){0,3}|dev-.*)(?:,|$)/m', $errors['COMPOSER-UPDATE'], $matches)) {
      // Split the fourth element by commas and extract the first part
      $constraint_parts = explode(', ', $matches[4]);
      $first_element = reset($constraint_parts);

      if (preg_match('/(v?\d+(?:\.\d+){0,3})/', $first_element))      
        $composer_obj->require->{$matches[1]} = '^' . $first_element;
      elseif (preg_match('/(dev-.*)/', $first_element))
        $composer_obj->require->{$matches[1]} = $first_element;

      file_put_contents(COMPOSER_JSON['path'], json_encode($composer_obj, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
      unset($errors['COMPOSER-UPDATE']);
    }
  }
  }

//     while() { $errors['COMPOSER-UPDATE'] } // loop for 5 attempts to fix a problem 

  if (!is_file('composer.lock')) {
/**
  Optimization
**/
    $proc = proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' install -o', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

    list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

    if ($exitCode !== 0)
      if (empty($stdout)) {
        if (!empty($stderr))
          $errors['COMPOSER-INSTALL'] = $stderr;
      } else $errors['COMPOSER-INSTALL'] = $stdout;
  //else $debug['COMPOSER-INSTALL'] = '$stdout=' $stdout . "\n".  '$stderr = ' . $stderr;

  //exec((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' install -o', $output, $returnCode) or $errors['COMPOSER-INSTALL'] = $output;
  
  }

  // https://getcomposer.org/doc/03-cli.md
//  $proc = proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' validate --no-check-all --no-check-publish --no-check-version --strict', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes); // $output = shell_exec("cd " . escapeshellarg(dirname(COMPOSER_JSON['path'])) . " && " . 'sudo ' . COMPOSER_EXEC . ' validate --no-check-all --no-check-publish --no-check-version');  dd($output);

  //  "./composer.json" does not match the expected JSON schema:  
  //  - NULL value found, but an object is required

  // poss. err './composer.json is valid but your composer.lock has some errors'   checks composer.lock
/*
  list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

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
}


//if (strpos($output, 'No errors or warnings detected') !== false)
//Deprecated:  strpos(): Passing null to parameter #1 ($haystack) of type string is deprecated

defined('COMPOSER_JSON')
    and define('COMPOSER', ['json' => json_decode(file_get_contents($path = COMPOSER_JSON['path'])), 'path' => $path]);

if (isset(COMPOSER->{'require'}) && !empty(COMPOSER->{'require'}))
foreach (COMPOSER->require as $key => $value) {
  if ($key == 'php') continue;
  else {
    if (isset(COMPOSER->require->{'composer/composer'}) && $value === COMPOSER->require->{'composer/composer'}) {
        //echo "The key is: $key";
    defined('VENDOR_JSON')
      or define('VENDOR_JSON', ['json' => (is_file(APP_PATH . 'vendor/' . $key . '/composer.json') ? file_get_contents(APP_PATH .'vendor/' . $key . '/composer.json') : '{}'), 'path' => APP_PATH . 'vendor/' . $key . '/composer.json']);
    
//dd(VENDOR_JSON);

defined('VENDOR_JSON')
    and define('VENDOR', json_decode(file_get_contents(VENDOR_JSON['path'])));
        break;
    } else {

defined('VENDOR_JSON')
      or define('VENDOR_JSON', ['json' => '{}', 'path' => '']);
      
defined('VENDOR_JSON')
    and define('VENDOR', json_decode('{}'));
    }
  }
}


if (!defined('VENDOR_JSON') && isset(COMPOSER->{'require'}[1]))
  define('VENDOR_JSON', ['json' => (is_file(APP_PATH . 'vendor/' . COMPOSER->{'require'}[1] . '/composer.json') ? file_get_contents(APP_PATH .'vendor/' . COMPOSER->{'require'}[1] . '/composer.json') : '{}'), 'path' => APP_PATH . 'vendor/' . COMPOSER->{'require'}[1] . '/composer.json']);

//dd(COMPOSER);
//dd(COMPOSER);
//dd(VENDOR_JSON['path']);

  //else $errors['COMPOSER-VALIDATE'] = $output;

//dd(get_defined_constants(true)['user']);
//dd(COMPOSER_EXEC . '  ' . COMPOSER_VERSION);

if (basename(dirname(APP_SELF)) == __DIR__ . DIRECTORY_SEPARATOR . 'public')
  if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'ui.composer.php')) // is_file('config/composer_app.php')) 
    require_once($path);

if (APP_SELF == __FILE__ || defined(APP_DEBUG) && isset($_GET['app']) && $_GET['app'] == 'composer') die($appComposer['html']);