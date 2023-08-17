<?php

define('COMPOSER_NAME_EXPR', '/^[a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]|-{1,2})?[a-z0-9]+)*$/'); // name
define('COMPOSER_VER_EXPR', '/^v?\d+(\.\d+){0,3}|^dev-/'); // version

/*

require 'vendor/autoload.php'; // Include Composer's autoloader

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
  public $require_dev;
  public $autoload;
}


if ($path = (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'constants.php') ? __DIR__ . DIRECTORY_SEPARATOR . 'constants.php' : (is_file('config/constants.php') ? 'config/constants.php' : NULL))) // is_file('config/composer.php')) 
  require_once($path);
else die(var_dump($path));

// isset($$c_or_p) and dd($$c_or_p);

//cd /usr/local/bin
//curl -sS https://getcomposer.org/installer | php /* -- --filename=composer */
//chmod a+x composer.phar
//sudo mv composer /usr/local/bin/composer
//Change into a project directory cd /path/to/my/project


//composer config --global --auth --unset github-oauth.github.com
//composer config --global github-oauth.github.com __TOKEN__
//putenv('COMPOSER_use-github-api=true');
//putenv('COMPOSER_github-oauth.github.com=BAM');

define('COMPOSER_ALLOW_SUPERUSER', true);

putenv('COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER);

//defined('PHP_WINDOWS_VERSION_MAJOR') ? 'APPDATA' : 'HOME';

/*
  Must be defined before the composer-setup.php can be preformed.
*/

$composerUser = 'barrydit';
$componetPkg = basename(dirname(__DIR__));
$composerHome = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ?
  'C:/Users/Barry Dick/AppData/Roaming/Composer/' : 
  '/home/' . (getenv('USERNAME') ?: getenv('USER')) . '/.composer/'
);

if (!realpath($composerHome)) {
  if (!mkdir($composerHome, 0755))
    $errors['COMPOSER_HOME'] = $composerHome . ' does not exist';
} else define('COMPOSER_HOME', $composerHome);

putenv('COMPOSER_HOME=' . $composerHome ?? '/var/www/.composer/');

if (!file_exists(APP_PATH . 'composer.phar')) {
  if (!file_exists(APP_PATH . 'composer-setup.php'))
    copy('https://getcomposer.org/installer', 'composer-setup.php');
  
  $error = exec('php composer-setup.php'); // php -d register_argc_argv=1

  $errors['COMPOSER-PHAR'] = 'Composer setup was executed and ' . (file_exists(APP_PATH.'composer.phar') ? 'does' : 'does not') . ' exist. version='.exec('php composer.phar -V') . '  error=' . $error;
}

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { // DO NOT REMOVE! { .. }
    if (file_exists('C:\ProgramData\ComposerSetup\bin\composer.phar')) {
        define('COMPOSER_PHAR', 'C:\ProgramData\ComposerSetup\bin\composer.phar');
        define('COMPOSER_BIN', /*'composer.exe'*/ NULL);
    }
} else {
/*
  if (file_exists('/usr/bin/composer')) {
    define('COMPOSER_PHAR', (file_exists(APP_PATH . 'composer.phar') ? APP_PATH . 'composer.phar' : '/usr/bin/composer'));
    define('COMPOSER_BIN', '/usr/bin/composer');
  } elseif (file_exists('/usr/local/bin/composer')) {
    define('COMPOSER_PHAR', (file_exists(APP_PATH . 'composer.phar') ? APP_PATH . 'composer.phar' : '/usr/local/bin/composer'));
    define('COMPOSER_BIN', '/usr/local/bin/composer');
  }
*/
    foreach(array('/usr/local/bin/composer', 'php composer.phar', '/usr/bin/composer') as $key => $bin) {
        !isset($composer) and $composer = array();

        $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . $bin . ' --version;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

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
                    $errors['COMPOSER_VERSION'] = '$stdout is empty. $stderr = ' . $stderr;
            } // else $errors['COMPOSER_VERSION'] = $stdout . ' does not match $version'; }
        }
    }

    usort($composer, function($a, $b) {
        return version_compare($b['version'], $a['version']); // Sort in descending order based on version
    });

    if (empty($composer)) $errors['COMPOSER-BIN'] = 'There are no composer binaries.';
    else {
        define('COMPOSER_VERSION', $composer[0]['version']);
        define('COMPOSER_BIN', (isset($composer[0]['bin']) ? $composer[0]['bin'] : '') ?? (isset($composer[1]['bin']) ? $composer[1]['bin'] : ''));
        define('COMPOSER_PHAR', (file_exists(APP_PATH . 'composer.phar') ? 'php composer.phar' : (isset($composer[1]['bin']) ? $composer[1]['bin'] : COMPOSER_BIN)));
    }
}
    
defined('COMPOSER_EXEC')
  or define('COMPOSER_EXEC', (isset($_GET['exec']) ? ($_GET['exec'] == 'phar' ? COMPOSER_PHAR : COMPOSER_BIN) : COMPOSER_BIN ?? COMPOSER_PHAR));


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

  

if (APP_ENV == 'development') {
  $composer_exec = (COMPOSER_EXEC == COMPOSER_PHAR ? COMPOSER_PHAR : basename(COMPOSER_EXEC));

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
    defined('COMPOSER_INIT_PARAMS')
      or define('COMPOSER_INIT_PARAMS', <<<TEXT
{$composer_exec} init --quiet --no-interaction --working-dir="{$$c_or_p->path}" --name="{$composerUser}/{$$c_or_p->name}" --description="General Description" --author="Barry Dick <barryd.it@gmail.com>" --type="project" --homepage="https://github.com/{$composerUser}/{$$c_or_p->name}" --require="php:^7.4||^8.0" --require-dev="pds/skeleton:^1.0" --require-dev="composer/composer:^1.0" --stability="dev" --license="WTFPL"
TEXT
);

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
  $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_INIT_PARAMS, array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

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
  
  defined('COMPOSER_INIT_PARAMS')
      or define('COMPOSER_INIT_PARAMS', <<<TEXT
{$composer_exec} init --quiet --no-interaction --working-dir="" --name="<vendor id>/{$componetPkg}" --description="General Description" --author="<name> <email@url.ext>" --type="project" --homepage="https://github.com/<vendor id>/{$componetPkg}" --require="php:^7.4||^8.0" --require-dev="pds/skeleton:^1.0" --require-dev="composer/composer:^1.0" --stability="dev" --license="WTFPL"
TEXT
);

  if (!defined('COMPOSER_JSON')) 
    define('COMPOSER_JSON', ['json' => (is_file($$c_or_p->path . 'composer.json') ? file_get_contents($$c_or_p->path . 'composer.json') : '{}'), 'path' => $$c_or_p->path . 'composer.json']);




  //$errors['COMPOSER_JSON'] = 'COMPOSER_JSON constant/object is not defined.';

/*
if (file_exists($$c_or_p->path . '/composer.json'))
(defined(strtoupper($c_or_p)) ??
  defined('COMPOSER_JSON')
    or define('COMPOSER_JSON', $$c_or_p->path . '/composer.json')
);
else (@!touch($$c_or_p->path . '/composer.json')? define('COMPOSER_JSON', $$c_or_p->path . '/composer.json') : $erros['COMPOSER-JSON'] = 'composer.json was unable to be created.');
*/
}
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

if (defined('COMPOSER_JSON'))
  $composer_obj = json_decode(COMPOSER_JSON['json']);

if (defined('COMPOSER_VERSION') && defined('COMPOSER_LATEST') && defined('APP_DEBUG')) {
  if (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0) {
    $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC . ' self-update;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

/*
//fwrite($pipes[0], "yes");
//fclose($pipes[0]);

$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);

fclose($pipes[1]);
fclose($pipes[2]);
*/

    list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    
    if (empty($stdout)) {
      if (!empty($stderr))
        $errors['COMPOSER_UPDATE'] = '$stdout is empty. $stderr = ' . $stderr;
    } else $errors['COMPOSER_UPDATE'] = $stdout;

  }
  
  if (!is_dir('vendor') || !is_file('vendor/autoload.php'))
    exec('sudo composer dump-autoload', $output, $returnCode) or $errors['COMPOSER-DUMP-AUTOLOAD'] = $output;
  else
    if (!empty($composer_obj->{'require'}))
      foreach ($composer_obj->{'require'} as $package => $version) {
        //dd(COMPOSER_NAME_EXPR);
      
        if (!preg_match(COMPOSER_NAME_EXPR . 'i', $package)) continue;  // $package == 'php'
        else {
        //echo $package . ' => ' . $version . "\n" ;
          $command = "composer show $package";
          $output = [];
          $returnCode = 0;
          exec($command, $output, $returnCode);

          if ($returnCode !== 0) {
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
              if (is_dir($repository->url)) 
                $composer_obj->repositories[] = $repository;
            }
          } // else { }
        }
      }
//dd($composer_obj);
  //if (!$composer_obj->{'repositories'}) $composer_obj->{'repositories'} = [];  
  if (isset($composer_obj->{'version'}) && !preg_match(COMPOSER_VER_EXPR, $composer_obj->{'version'}))
    unset($composer_obj->{'version'});
  
  file_put_contents(COMPOSER_JSON['path'], json_encode($composer_obj, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

  if (is_file(APP_PATH . 'composer.lock')) unlink(APP_PATH . 'composer.lock');

  $proc = proc_open('sudo ' . COMPOSER_EXEC . ' update', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

  list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

  if ($exitCode !== 0)
    if (empty($stdout)) {
      if (!empty($stderr))
        $errors['COMPOSER-UPDATE'] = '$stdout is empty. $stderr = ' . $stderr;
    } else $errors['COMPOSER-UPDATE'] = $stdout;
  //else $debug['COMPOSER-UPDATE'] = '$stdout=' $stdout . "\n".  '$stderr = ' . $stderr;

  $proc = proc_open('sudo ' . COMPOSER_EXEC . ' install -o', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

  list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

  if ($exitCode !== 0)
    if (empty($stdout)) {
      if (!empty($stderr))
        $errors['COMPOSER-INSTALL'] = '$stdout is empty. $stderr = ' . $stderr;
    } else $errors['COMPOSER-INSTALL'] = $stdout;
  //else $debug['COMPOSER-INSTALL'] = '$stdout=' $stdout . "\n".  '$stderr = ' . $stderr;

  //exec('sudo ' . COMPOSER_EXEC . ' install -o', $output, $returnCode) or $errors['COMPOSER-INSTALL'] = $output;
}

// 

//$output = shell_exec("cd " . escapeshellarg(dirname(COMPOSER_JSON['path'])) . " && " . 'sudo ' . COMPOSER_EXEC . ' validate --no-check-all --no-check-publish --no-check-version');
//dd($output);

  $proc = proc_open('sudo ' . COMPOSER_EXEC . ' validate --no-check-all --no-check-publish --no-check-version', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

  list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

  if ($exitCode !== 0)
    if (empty($stdout)) {
      if (!empty($stderr))
        $errors['COMPOSER-VALIDATE'] = '$stdout is empty. $stderr = ' . $stderr;
    } else $errors['COMPOSER-VALIDATE'] = $stdout;

//if (strpos($output, 'No errors or warnings detected') !== false)
//Deprecated:  strpos(): Passing null to parameter #1 ($haystack) of type string is deprecated


  if (defined('COMPOSER_JSON'))
    define('COMPOSER', json_decode(file_get_contents(COMPOSER_JSON['path'])));
  //else $errors['COMPOSER-VALIDATE'] = $output;

//dd(COMPOSER);


//dd(get_defined_constants(true)['user']);
//dd(COMPOSER_EXEC . '  ' . COMPOSER_VERSION);

if (basename(dirname(APP_SELF)) == __DIR__ . DIRECTORY_SEPARATOR . 'public')
  if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'composer_app.php')) // is_file('config/composer_app.php')) 
    require_once($path);

if (APP_SELF == __FILE__ || defined(APP_DEBUG) && isset($_GET['app']) && $_GET['app'] == 'composer') die($appComposer['html']);