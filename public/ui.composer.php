<?php

if (in_array(__FILE__, get_required_files())) {
  //echo "File $target_file was included.";

  // die(var_dump(dirname(getcwd())));
  if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"])) {
    if (is_file($path = dirname(getcwd()) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php')) require_once $path; 
    else die(var_dump("$path path was not found. file=" . basename($path)));
  }
  
  if (is_file($path = APP_PATH . APP_BASE['config'] . 'composer.php')) require_once $path; 
  else die(var_dump("$path path was not found. file=" . basename($path)));

  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    if (is_file($path = realpath('index.php'))) require_once $path;
  } else die(var_dump("Path was not found. file=$path"));

/*
if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '') require_once $path;
  else die(var_dump("$path path was not found. file=config.php"));
*/
  /*
else {
  $path = APP_PATH . APP_BASE['config'] . 'composer.php';
dd($path);
  if (is_file($path))
dd(get_included_files(), false);
    require_once $path; 
  else
    die(var_dump("$path path was not found. file=composer.php"));
}*/

// dd(get_required_files());
// require APP_BASE['vendor'] . 'autoload.php'; // Include Composer's autoloader

// dd(get_required_files());

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
die();

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

*/



/** Loading Time: 5.1s **/
  
  //dd(get_required_files(), true);
/*
if (!in_array(APP_PATH . APP_BASE['config'] . 'composer.php', get_required_files())) {
  require_once APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php';
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../composer.php') ? '../composer.php' : (is_file('../config/composer.php') ? '../config/composer.php' : null))
    : (is_file('composer.php') ? 'composer.php' : (is_file('config/composer.php') ? 'config/composer.php' : null))) require_once $path; 
  else die(var_dump("$path path was not found. file=composer.php"));
}
*/
/*
if (!in_array(APP_PATH . APP_BASE['public'] . 'app.console.php', get_required_files()))
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  // consider creating a visual aspect for the lock file
  
  dd($_POST);
  
  chdir(APP_PATH . APP_ROOT);

  //if (isset($_POST['composer']['autoload']))
  //  Shutdown::setEnabled(false)->setShutdownMessage(function() {
  //    $_ENV['COMPOSER']['AUTOLOAD'] = $_POST['composer']['autoload'] == 'on' ? true : false;
  //    return header('Location: ' . APP_URL); // -wow
  //  })->shutdown();
  if (isset($_POST['composer']['create-project']) && preg_match(COMPOSER_EXPR_NAME, $_POST['composer']['package'], $matches)) {
    if (!is_dir($path = APP_PATH . 'project'))
      (@!mkdir($path, 0755, true) ?: $errors['COMPOSER-PROJECT'] = 'project/ could not be created.' );
    if ($matches[1] == 'laravel' && $matches[2] == 'laravel')  
      exec((stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ') . 'composer create-project ' . $_POST['composer']['package'] . ' project/laravel', $output, $returnCode) or $errors['COMPOSER-PROJECT-LARAVEL'] = $output;
    elseif ($matches[1] == 'symfony' && $matches[2] == 'skeleton')  
      exec((stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ') . 'composer create-project ' . $_POST['composer']['package'] . ' project/symfony', $output, $returnCode) or $errors['COMPOSER-PROJECT-SYMFONY'] = $output;

    unset($_POST['composer']['package']);
    unset($_POST['composer']['create-project']);
  } elseif (isset($_POST['composer']['package']) && preg_match(COMPOSER_EXPR_NAME, $_POST['composer']['package'])) {
    [$vendor, $package] = explode('/', $_POST['composer']['package']);

    if (empty($vendor))
      $errors['COMPOSER_PKG'] = "vendor is missing its value. vendor=$vendor";
    if (empty($package))
      $errors['COMPOSER_PKG'] = "package is missing its value. package=$package";

      //if ($vendor == 'nesbot' && $package == 'carbon') {

    $raw_url = (preg_match(DOMAIN_EXPR, packagist_return_source($vendor, $package), $matches)) ? $initial_url = $matches[0] : '';
    
    if (!is_file(APP_BASE['var'].'package-' . $vendor . '-' . $package . '.php')) {

      $source_blob = check_http_status($raw_url) ? file_get_contents($raw_url) : '';
    
      //dd('url: ' . $raw_url);
      $raw_url = addslashes($raw_url);

      $source_blob = addslashes(COMPOSER_JSON['json']); // $source_blob
      file_put_contents(APP_BASE['var'].'package-' . $vendor . '-' . $package . '.php', '<?php' . "\n" . ( check_http_status($raw_url) ? "\$source = \"$raw_url\";" : '' ) . "\n" . 
<<<END
\$composer_json = "{$source_blob}";
return '<form action method="POST">'
. '...'
. '</form>';
END
);

      if (isset($_POST['composer']['install'])) {
        exec((stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ') . 'composer require ' . $_POST['composer']['package'], $output, $returnCode) or $errors['COMPOSER-REQUIRE'] = $output;
        exec((stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ') . 'composer update ' . $_POST['composer']['package'], $output, $returnCode) or $errors['COMPOSER-UPDATE'] = $output;
      }

      exit(header('Location: ' . APP_URL_BASE . '?' . http_build_query(APP_QUERY))); // , '', '&amp;'

    }

  } elseif (isset($_POST['composer']['config']) && !empty($_POST['composer']['config'])) {

    $composer = new composerConfig();

/*
      if (isset($_POST['composer']['config']['version']) && preg_match(COMPOSER_EXPR_VER, $_POST['composer']['config']['version']))
        $composer->{'version'} = $_POST['composer']['config']['version'];   // $_POST['composer']['config']['version'] !== ''
      else unset($composer->{'version'});
*/
    $composer->{'name'} = $_POST['composer']['config']['name']['vendor'] . '/' . $_POST['composer']['config']['name']['package'];
    $composer->{'description'} = $_POST['composer']['config']['description'];
    if (isset($_POST['composer']['config']['version']) && preg_match(COMPOSER_EXPR_VER, $_POST['composer']['config']['version']))
      $composer->{'version'} = $_POST['composer']['config']['version'];   // $_POST['composer']['config']['version'] !== ''
    else unset($composer->{'version'});
    $composer->{'type'} = $_POST['composer']['config']['type'];
    $composer->{'keywords'} = (isset($_POST['composer']['config']['keywords']) ? $_POST['composer']['config']['keywords'] : []);
    $composer->{'homepage'} = 'https://github.com/' . $_POST['composer']['config']['name']['vendor'] . '/' . $_POST['composer']['config']['name']['package'];
    $composer->{'readme'} = 'README.md';
    $composer->{'time'} = date('Y-m-d H:i:s');
    $composer->{'license'} = $_POST['composer']['config']['license'];
    $composer->{'authors'} = []; //$_POST['composer']['authors'];

    if (!empty($_POST['composer']['config']['authors'])) foreach ($_POST['composer']['config']['authors'] as $key => $author) {

      if ($author['name'] != '' || $author['email'] != '') {
        $object = new stdClass();
        $object->name = $author['name'] ?? 'John Doe';
        $object->email = $author['email'] ?? 'jdoe@example.com';
        $object->role = $author['role'] ?? 'Developer';

        $composer->{'authors'}[] = $object;
      }
    } else $composer->{'authors'} = [];
  
      // $composer->{'support'}
      // $composer->{'funding'}
      
      // $composer->{'repositories'}
 
    if (!$composer->{'repositories'} || empty($composer->{'repositories'})) $composer->{'repositories'} = [];
  
    $composer->{'require'} = new stdClass(); //$_POST['composer']['require'];
      
  //dd($composer->{'require'});
  
    if (!empty($_POST['composer']['config']['require'])) {
        //if (!in_array($require_0, $_POST['composer']['require'])) { continue; }
      foreach ($_POST['composer']['config']['require'] as $require) {   //   

        if (preg_match('/(.*):(.*)/', $require, $match)) $composer->{'require'}->{$match[1]} = $match[2] ?? '^';
      }
    } else $composer->{'require'} = new StdClass;
    
    $composer->{'require-dev'} = new stdClass();
      
    if (!empty($_POST['composer']['config']['require-dev'])) {
        //if (!in_array($require_0, $_POST['composer']['require'])) { continue; }
      foreach ($_POST['composer']['config']['require-dev'] as $require) {   //   
        if (preg_match('/(.*):(.*)/', $require, $match)) $composer->{'require-dev'}->{$match[1]} = $match[2] ?? '^';
      }
    } else $composer->{'require-dev'} = new StdClass;

    $composer->{'autoload'} = new StdClass; // $_POST['composer']['autoload'];
  //$composer->{'autoload'}->{'psr-4'} = new StdClass; 
  //$composer->{'autoload'}->{'psr-4'}->{'HtmlToRtf\\'} = "src/HtmlToRtf";
  //$composer->{'autoload'}->{'psr-4'}->{'ProgressNotes\\'} = "src/HtmlToRtf";

  //$composer->{'autoload-dev'} = $_POST['composer']['autoload-dev'];

    $composer->{'minimum-stability'} = $_POST['composer']['config']['minimum-stability'];
      
    $composer->{'prefer-stable'} = true;

      //dd();

    if (COMPOSER_AUTH['token'] != $_POST['auth']['github_oauth']) {
      $tmp_auth = json_decode(COMPOSER_AUTH['json']);
      $tmp_auth->{'github-oauth'}->{'github.com'} = $_POST['auth']['github_oauth'];

      file_put_contents(COMPOSER_AUTH['path'], json_encode($tmp_auth, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)); // COMPOSER_AUTH['json']
    }

    file_put_contents(COMPOSER_JSON['path'], json_encode($composer, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

  }

  if (isset($_POST['composer']['init']) && !empty($_POST['composer']['init'])) {
    $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ')  . str_replace(array("\r\n", "\r", "\n"), ' ', $_POST['composer']['init']) . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' update;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

    [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

    if (empty($stdout)) {
      if (!empty($stderr))
        $errors['COMPOSER_INIT'] = '$stderr = ' . $stderr;
    } else $errors['COMPOSER_INIT'] = $stdout;

      //dd($errors);
  }
    //dd('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ') . COMPOSER_EXEC . ' install ' . (isset($_POST['composer']['config']) ? '-o' : (isset($_POST['composer']['optimize-classes']) ? '-o': '')) . ';');
    
  isset($_POST['composer']['lock'])
    and unlink(APP_PATH . 'composer.lock');

// https://stackoverflow.com/questions/33052195/what-are-the-differences-between-composer-update-and-composer-install
    
  if (isset($_POST['composer']['install'])) {

    $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' install ' . (isset($_POST['composer']['config']) ? '-o' : (isset($_POST['composer']['optimize-classes']) ? '-o': '')) . ';', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

    [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

    if (empty($stdout)) {
      if (!empty($stderr))
        $errors['COMPOSER_INSTALL'] = '$stderr = ' . $stderr;
    } else $errors['COMPOSER_INSTALL'] = $stdout;

      //$composer = json_decode(COMPOSER_JSON['json'], true);   dd($composer);
      
      /* php composer.phar remove phpmd/phpmd    php composer.phar update*/
      /* composer update phpmd/phpmd */
  }
    
  if (isset($_POST['composer']['update'])) {

    /* Update won't work if the repositry has any upper/lower case letters differn't ... */
/*  
update [--with WITH] [--prefer-source] [--prefer-dist] [--prefer-install PREFER-INSTALL] [--dry-run] [--dev] [--no-dev] [--lock] [--no-install] [--no-audit] [--audit-format AUDIT-FORMAT] [--no-autoloader] [--no-suggest] [--no-progress] [-w|--with-dependencies] [-W|--with-all-dependencies] [-v|vv|vvv|--verbose] [-o|--optimize-autoloader] [-a|--classmap-authoritative] [--apcu-autoloader] [--apcu-autoloader-prefix APCU-AUTOLOADER-PREFIX] [--ignore-platform-req IGNORE-PLATFORM-REQ] [--ignore-platform-reqs] [--prefer-stable] [--prefer-lowest] [-i|--interactive] [--root-reqs] [--] [<packages>...]
*/

    if (isset($_POST['composer']['self-update']) || file_exists(APP_PATH . 'composer.phar')) {
      if (!file_exists(APP_PATH . 'composer-setup.php'))
        copy('https://getcomposer.org/installer', 'composer-setup.php');
      exec('php composer-setup.php');
    }
      // If this process isn't working, its because you have an invalid composer.json file
    $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' update'  , array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

    [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

    if (empty($stdout)) {
      if (!empty($stderr))
        $errors['COMPOSER_UPDATE'] = "\$stderr = $stderr";
    } else $errors['COMPOSER_UPDATE'] = $stdout;

    if (defined('COMPOSER_VERSION') && defined('COMPOSER_LATEST'))
      if (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0) {
        $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' self-update;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

        [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    
        if (empty($stdout)) {
          if (!empty($stderr))
            $errors['COMPOSER_UPDATE'] = "\$stderr = $stderr";
        } else $errors['COMPOSER_UPDATE'] = $stdout;
      }
    // $_POST['composer']['cmd'];
  }

  //die(APP_URL);

  //exit(header('Location: ' . (is_array(APP_URL) ? APP_URL['scheme'] . '://' . APP_DOMAIN . '/' : APP_URL ) . '?' . http_build_query(APP_QUERY)));
  //dd($_POST);
}

}

/** Loading Time: 4.99s **/
  
  //dd(get_required_files(), true);


/*
<?php ob_start(); ?>
<HTML ...>
<?php $app['css'] = ob_get_contents(); ?>
*/ 

ob_start(); ?>

#app_composer-container { position: absolute; display: none; left: 832px; top: 96px; margin: 0 auto; z-index: 1; }
#app_composer-container.selected { display: block; z-index: 1; 
  /* Add your desired styling for the selected container */
  /*
  // background-color: rgb(240, 224, 198); //  240, 224, 198, .75  #FBF7F1; // rgba(240, 224, 198, .25);
  
  bg-[#FBF7F1];
  bg-opacity-75;

  font-weight: bold;
  #top { background-color: rgba(240, 224, 198, .75); }
  */
}

.app_composer-frame-container { position: absolute; display: none; top:0; left: 0; width: 400px; }
.app_composer-frame-container.selected { display: block; z-index: 1; }

/* #app_composer-frameName == ['menu', 'conf', 'install', 'init', 'update'] */

#app_composer-frameMenu {}
#app_composer-frameMenuPrev {} /* composerMenuPrev */
#app_composer-frameMenuNext {} /* composerMenuNext */

#app_composer-frameMenuConf {}
#app_composer-frameMenuInstall {}
#app_composer-frameMenuInit {}
#app_composer-frameMenuUpdate {}

#app_composer-frameConf {}
#app_composer-frameInstall {}
#app_composer-frameInit {}
#app_composer-frameUpdate {}

#update { backgropund-color: rgba(240, 224, 198, .75); }
#middle { backgropund-color: rgba(240, 224, 198, .75); }
#bottom { backgropund-color: rgba(240, 224, 198, .75); }

.btn {
  @apply rounded-md px-2 py-1 text-center font-medium text-slate-900 shadow-sm ring-1 ring-slate-900/10 hover:bg-slate-50
}

.composer-menu {
  cursor: pointer;
}

.dropbtn {
  background-color: #3498DB;
  color: white;
  padding: 2px 7px;
  font-size: 14px;
  border: none;
  cursor: pointer;
}

.dropbtn:hover, .dropbtn:focus {
  background-color: #2980B9;
}

.dropdown {
  position: relative;
  display: inline-block;
  float: right;
  z-index: 1;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: #f1f1f1;
  min-width: 160px;
  margin: -100px;
  overflow: auto;
}

.dropdown-content a {
  color: black;
  padding: 8px 12px;
  text-decoration: none;
  display: block;
}

.dropdown a:hover {background-color: #ddd;}

.show { display: block; }

img { display: inline; }

<?php $app['style'] = ob_get_contents();
ob_end_clean();


// dd(glob('*')); dd(getcwd());

//(APP_SELF == __FILE__ || isset($_GET['app']) && $_GET['app'] == 'composer' ? 'selected' : (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? (isset($_GET['app']) && $_GET['app'] != 'composer' ? '' : 'selected') :  '')) 
ob_start(); ?>

<div id="app_composer-container" class="absolute <?= __FILE__ == get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'composer') || (defined('COMPOSER') && !is_object(COMPOSER['json']) && count((array) COMPOSER) === 0 ) || version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? 'selected' : '' ?>" style="z-index: 1; width: 424px; background-color: rgba(255,255,255,0.8); padding: 10px;">

<div style="position: relative; margin: 0 auto; width: 404px; height: 324px; border: 3px dashed #6B4329; background-color: #FBF7F1;">

<div class="absolute ui-widget-header" id="" style="position: absolute; display: inline-block; width: 100%; height: 25px; margin: -50px 0 25px 0; padding: 24px 0; border-bottom: 1px solid #000; z-index: 3;">
  <label class="composer-home" style="cursor: pointer;">
    <div class="absolute" style="position: relative; display: inline-block; top: 0; left: 0; margin-top: -5px;">
      <img src="resources/images/composer_icon.png" width="32" height="40" />
    </div>
  </label>
  <div style="display: inline;">
    <span style="background-color: #B0B0B0; color: white;">Composer <?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? 'v'.substr(COMPOSER_LATEST, 0, similar_text(COMPOSER_LATEST, COMPOSER_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(COMPOSER_LATEST, similar_text(COMPOSER_LATEST, COMPOSER_VERSION)) . '</span>' : 'v'.COMPOSER_VERSION ); ?> </span>


    <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL . '?' . http_build_query(APP_QUERY + array( 'app' => 'composer')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
      <?php if (isset($_GET['debug'])) { ?> <input type="hidden" name="debug" value="" /> <?php } ?>

      <code class="text-sm" style="background-color: #fff; color: #0078D7;">$ 
        <input type="hidden" name="app" value="composer" />
        <select name="exec" onchange="this.form.submit();">
          <option <?= (COMPOSER_EXEC == COMPOSER_BIN ? 'selected' : '') ?> value="bin"><?= COMPOSER_BIN['bin']; ?></option>
          <option <?= (COMPOSER_EXEC == COMPOSER_PHAR ? 'selected' : '') ?> value="phar"><?= 'php composer.phar' /*COMPOSER_PHAR['bin']*/; ?></option>
        </select>
      </code>
    </form>

  </div>
  <div style="display: inline; float: right; text-align: center; "><code style=" background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_composer-container').style.display='none';">[X]</a></code></div> 
</div>

<div class="ui-widget-content" style="position: relative; display: block; width: 398px; background-color: rgba(251,247,241); z-index: 2;">
  <div style="display: inline-block; text-align: left; width: 225px;">
    <div class="composer-menu text-sm" style="cursor: pointer; font-weight: bold; padding-left: 40px;">
      <div style=" border: 1px solid #000; width: 150px;">Main Menu</div>
    </div>
    <div class="text-xs" style="display: inline-block; border: 1px solid #000;">
      <a class="text-sm" id="app_composer-frameMenuPrev" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '#') ?>"> &lt; Menu</a> | <a class="text-sm" id="app_composer-frameMenuNext" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '#') ?>">Init &gt;</a>
    </div>
    <form style="display: inline-block;" action="<?= basename(__FILE__); ?>" method="POST"><div class="text-sm" ><input type="checkbox" <?= $_ENV['COMPOSER']['AUTOLOAD'] ?: 'checked="checked"' ?> name="composer[autoload]" onchange="this.form.submit();" /> AUTOLOAD</div></form>
  </div>
  <div class="absolute" style="position: absolute; display: inline-block; top: 4px; text-align: right; width: 175px; ">
    <div class="text-xs" style="display: inline-block;">
    + 987 <a href="https://github.com/composer/composer/graphs/contributors">contributors</a>
    <br />
    <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;" href="http://getcomposer.org/" target="_blank">http://getcomposer.org/</a>
    </div>
<!--
    <select id="frameSelector">
      <option value="0" selected>---</option>
      <option value="1">Update</option>
      <option value="2">Config</option>
      <option value="3">Initial</option>
      <option value="4">Install</option>
    </select>
-->
  </div>
  <div style="clear: both;"></div>
</div>
<div class="absolute" style="position: absolute; bottom: 60px; right: 0; margin: 0 auto; width: 225px; text-align: right;">
  <form action="?" method="POST" class="text-sm">
    <input type="hidden" name="update" value="" />composer.lock requires an <button type="submit" style="border: 1px solid #000; z-index: 3;">Update</button>
  </form>
</div>
<div class="absolute" style="position: absolute; margin: 0px auto; text-align: center; height: 275px; width: 100%; background-repeat: no-repeat; <?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? "background-image: url('https://editablegifs.com/gifs/gifs/fireworks-1/output.gif?egv=3258')" : '') ?> ;">
</div>

<div class="absolute" style="position: absolute; top: 0; left: 0; right: 0; margin: 10px auto; opacity: 1.0; text-align: center; cursor: pointer; z-index: 1;">
  <img class="<?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? 'composer-update' : 'composer-menu') ?>" src="resources/images/composer.fw.png" style="margin-top: 45px;" width="150" height="198" />
</div>

<div class="absolute" style="position: absolute; bottom: 40px; left: 0; right: 0; width: 100%; text-align: center; z-index: 1; ">
<form action="#!" method="POST">
  <input type="hidden" name="composer[create-project]" value="" />
  <span style="pdding-left: 125px"></span>
  <select name="composer[package]" onchange="this.form.submit();">
    <option value="" selected>create-project</option>
    <option value="laravel/laravel">laravel/laravel</option>
    <option value="symfony/skeleton">symfony/skeleton</option>
  </select>
  <span>/project/*</span>
</form>
</div>

<div class="absolute" style="position: absolute; bottom: 24px; left: 0; right: 0; width: 100%; text-align: center;">
  <span style="text-decoration-line: underline; text-decoration-style: solid;">A Dependency Manager for PHP</span>
</div>

<div style="position: absolute; bottom: 0; left: 0; padding: 2px; z-index: 1;">
  <a href="https://github.com/composer/composer"><img src="resources/images/github-composer.fw.png" /></a>
</div>

<div class="absolute text-sm" style="position: absolute; bottom: 0; right: 0; padding: 2px; z-index: 1; "><?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? '<code>Latest: </code><span class="update" style="color: green; cursor: pointer;">' . 'v'.substr(COMPOSER_LATEST, 0, similar_text(COMPOSER_LATEST, COMPOSER_VERSION)). substr(COMPOSER_LATEST, similar_text(COMPOSER_LATEST, COMPOSER_VERSION))  . '</span>': 'Installed: v' . COMPOSER_VERSION ); ?></div>
<div style="position: relative; overflow: hidden; width: 398px; height: 250px;">
<?php

$count = 0;
if (defined('COMPOSER') && isset(COMPOSER['json']->require))
foreach (COMPOSER['json']->require as $key => $require) {
if (preg_match('/.*\/.*:.*/', $key . ':' . $require)) 
if (preg_match('/(.*)\/.*/', $key, $match))
  if (!empty($match) && !is_dir(APP_PATH . APP_BASE['vendor'] . $match[1].'/')) $count++;
}
?>      
<div id="app_composer-frameMenu" class="app_composer-frame-container <?=($count >= 1 ? '' : 'selected' ); ?> absolute" style="background-color: rgb(225,196,151,.75); margin-top: 8px;">
  <!--<h3>Main Menu</h3> <h4>Update - Edit Config - Initalize - Install</h4> -->

  <div style="display: block; margin: 5px auto;">
    <div class="drop-shadow-2xl font-bold" style="display: inline-block; width: 192px; margin: 10px auto; text-align: right; cursor: pointer;">
      <div id="app_composer-frameMenuInit" style="text-align: center; padding-left: 18px;"><img style="display: block; margin: auto;" src="resources/images/initial_icon.fw.png" width="70" height="57" />Init</div>
    </div>
  
    <div class="config drop-shadow-2xl font-bold" style="display: inline-block; width: 192px; margin: 0px auto; text-align: center; cursor: pointer;">
      <div id="app_composer-frameMenuConf"  class="" style="text-align: center;"><img style="display: block; margin: auto;" src="resources/images/folder.fw.png" width="70" height="58" />Config</div>
    </div>
  </div>
  <div style="display: block; margin: 4px auto;">
    <div class="install drop-shadow-2xl font-bold" style="display: inline-block; width: 192px; margin: 0 auto; text-align: right; cursor: pointer;">
      <div id="app_composer-frameMenuInstall"  style="position: relative; text-align: center; padding-left: 15px;">
        <div style="position: absolute; top: -10px; left: 130px; color: red;"><?=($count >= 1 ? $count : '' ); ?></div>
        <img style="display: block; margin: auto;" src="resources/images/install_icon.fw.png" width="54" height="54" />Install</div>
    </div>
    <div class="drop-shadow-2xl font-bold" style="display: inline-block; width: 192px; margin: 0 auto; text-align: center; cursor: pointer;">
      <div id="app_composer-frameMenuUpdate" style="text-align: center; "><img style="display: block; margin: auto;" src="resources/images/update_icon.fw.png" width="54" height="54" /><a href="#!">Update<?=/*Now!*/NULL; ?></a></div>
    </div>
  </div>
  <div style="height: 10px;"></div>
</div>
<?php ob_start(); ?>
<div id="app_composer-frameUpdate" class="app_composer-frame-container absolute" style="overflow: scroll; background-color: rgb(225,196,151,.75);">
<form autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" action="<?= APP_URL . '?' . http_build_query(APP_QUERY + array( 'app' => 'composer')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="POST">
<input type="hidden" name="composer[update]" value="" />
<div style="position: absolute; right: 0; float: right; text-align: center;">
  <input class="btn" id="composerSetupSubmit" type="submit" value="self-update">
</div>
<div style="display: inline-block; width: 100%; margin: 0 auto;">
  <div class="text-sm" style="display: inline;">
    <label id="composerSetupLabel" for="composerSetup" style="background-color:hsl(89, 100%, 42%); color: white; text-decoration: underline; cursor: pointer; font-weight: bold;">&#9650; <code>Setup / Update</code></label>
  </div>
  <span style="background-color: white;">
  <span class="text-sm" style="display: inline-block;">was <?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') == 0 ? '<span style="font-weight: bold;">not</span>' : '')?> found: </span>
  </span>
</div>

<div id="composerSetupForm" style="display: inline-block; padding: 5px; background-color: rgba(0,0,0,.03); border: 1px dashed #0078D7;">
  <div>
  <span class="text-xs" style="background-color: #0078D7; color: white;"><code>Version: (Installed) <?= COMPOSER_VERSION ?> -> (Latest) <?= COMPOSER_LATEST ?></code></span>
  </div>
  <label>Composer Command</label>
  <textarea style="width: 100%" cols="40" rows="5" name="composer[cmd]">php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar -v
<?= stripos(PHP_OS, 'WIN') === 0 ? '' : 'sudo ' ?>composer self-update</textarea>
</div>
</form>
</div>
<?php
$frameUpdateContents = ob_get_contents();
ob_end_clean(); ?>

<?= (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') == 0 ? NULL : $frameUpdateContents); ?>

<div id="app_composer-frameInit" class="app_composer-frame-container absolute <?= (realpath(COMPOSER_JSON['path']) ? '' : (defined('COMPOSER')  && is_object(COMPOSER) && count((array)COMPOSER) !== 0 ? '' : 'selected')); ?>" style="overflow: hidden; height: 270px;">
<?php if (!defined('CONSOLE') && CONSOLE != true) { ?>
<form autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'composer')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="POST">
<?php } ?>
<div style="position: absolute; right: 0; float: right; text-align: center;">
    <button id="app_composer-init-submit" class="btn" type="submit" value>Init/Run</button>
</div>
<div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
  <div class="text-sm" style="display: inline;">
    <label id="composerInitLabel" for="composerInit" style="background-color: #6781B2; color: white;">&#9650; <code>Init</code></label>
  </div>
</div>
<div id="composerInitForm" style="display: inline-block; padding: 10px; background-color: rgba(235, 216, 186, 0.8); border: 1px dashed #0078D7;">
  <label>Composer Command</label>
  <textarea id="app_composer-init-input" style="width: 100%" cols="40" rows="6" name="composer[init]" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?= preg_replace('/\s--/', "\n--", COMPOSER_INIT_PARAMS); ?></textarea>
</div>
<?php if (!defined('CONSOLE') && CONSOLE != true) { ?>
</form>
<?php } ?>
</div>

<div id="app_composer-frameConf" class="app_composer-frame-container absolute <?= (!defined('COMPOSER') && is_file(APP_PATH . 'composer.json') ? 'selected' : ''); ?>" style="overflow-x: hidden; overflow-y: auto; height: 230px;">
<form autocomplete="off" spellcheck="false" action="<?= (!defined('APP_URL_BASE') ? '//' . APP_DOMAIN . APP_URL_PATH . '?' . http_build_query(APP_QUERY, '', '&amp;') : APP_URL . '?' . http_build_query(APP_QUERY + array('app' => 'composer'), '', '&amp;')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') ?>" method="POST">
<input type="hidden" name="composer[config]" value="" />

<div style="position: absolute; right: 0; float: right; text-align: center; z-index: 2;">
  <button class="btn absolute" id="composerJsonSubmit" type="submit" style="position: absolute; top: 0; right: 0;" value=""><?= (defined('COMPOSER_AUTH') && realpath(COMPOSER_AUTH['path']) ? 'Modify' : 'Create' ); ?></button>
</div> 
<div style="position: relative; display: inline-block; width: 100%; background-color: rgb(225,196,151,.25); z-index: 1;">
  <div class="text-sm" style="display: inline;">
    <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="composerJson" value="true" checked=""> -->
    <label for="composerJson" id="appComposerAuthLabel" title="<?= (defined('COMPOSER_AUTH') && realpath(COMPOSER_AUTH['path']) ? COMPOSER_AUTH['path'] : COMPOSER_AUTH['path']) /*NULL*/;?>" style="background-color: #6B4329; <?= (defined('COMPOSER_JSON') && realpath(COMPOSER_AUTH['path']) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color: red; text-decoration: underline; text-decoration: line-through;') ?> cursor: pointer; font-weight: bold;">&#9660; <code>COMPOSER_HOME/auth.json</code></label>
  </div>
</div>
<div id="appComposerAuthJsonForm" style="display: none; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
  <a class="text-sm" style="color: blue; text-decoration: underline;" href="https://github.com/settings/tokens?page=1">GitHub OAuth Token</a>:
  <span class="text-sm" style="float: right;"><?= ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+30 days',filemtime(COMPOSER_AUTH['path']))))) / 86400)) ?> (Days left)</span>
  <div style="float: right;">
    <input type="text" size="40" name="auth[github_oauth]" value="<?= COMPOSER_AUTH['token'] ?>" />
  </div>
  <div style="clear: both;"></div>
</div>

<div style="position: relative; display: inline-block; width: 100%; background-color: rgb(225,196,151,.25); z-index: 1;">
  <div class="text-sm" style="display: inline;">
    <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="composerJson" value="true" checked=""> -->
    <label for="composerJson" id="appComposerConfigLabel" title="<?= (defined('COMPOSER_CONFIG') && realpath(COMPOSER_CONFIG['path']) ? COMPOSER_CONFIG['path'] : COMPOSER_CONFIG['path']) /*NULL*/;?>" style="background-color: #6B4329; <?= (defined('COMPOSER_CONFIG') && realpath(COMPOSER_CONFIG['path']) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color: red; text-decoration: underline; text-decoration: line-through;') ?> cursor: pointer; font-weight: bold;" >&#9660; <code>COMPOSER_HOME/config.json</code></label>
  </div>
</div>
<div id="appComposerConfigJsonForm" style="display: none; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
  <a class="text-sm" style="color: blue; text-decoration: underline;" href="https://github.com/settings/tokens?page=1">GitHub OAuth Token</a>:
  <span class="text-sm" style="float: right;"></span>
  <div style="float: right;">
    <input type="text" size="40" name="config[github_oauth]" value="<?= COMPOSER_AUTH['token'] ?>" disabled />
  </div>
  <div style="clear: both;"></div>
  <a class="text-sm" style="color: blue; text-decoration: underline;" href="">Platform</a>:
  <span class="text-sm" style="float: right;"></span>
  <div style="float: right;">
    <input type="text" size="40" name="config[platform]" value="php:^7.4||^8.1" disabled />
  </div>
  <div style="clear: both;"></div>
</div>
<!--  -->
<div style="position: relative; display: inline-block; background-color: rgb(225,196,151,.25); width: 100%; z-index: 1;">
<?php //if (defined('COMPOSER_JSON')) $composer = json_decode(COMPOSER_JSON['json']); ?>
  <div class="text-sm" style="display: inline;">
    <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="composerJson" value="true" checked=""> -->
    <label for="composerJson" id="appComposerJsonLabel" class="text-sm" style="background-color: #6B4329; <?= (defined('COMPOSER_JSON') && realpath(COMPOSER_JSON['path']) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color:red; text-decoration: underline; text-decoration: line-through;') ?> cursor: pointer; font-weight: bold;" title="<?= (defined('COMPOSER_JSON') && realpath(COMPOSER_JSON['path']) ? COMPOSER_JSON['path'] : COMPOSER_JSON['path']) /*NULL*/; ?>">&#9650; <code>COMPOSER_PATH/composer.json</code></label>
    <div class="text-xs" style="display: <?= (!is_file(APP_PATH . 'composer.lock') ? 'none' : 'inline-block' )?>; padding-top: 5px; padding-right: 10px; float: right;"><input type="checkbox" name="composer[lock]" value="" /> <span style="background-color: white; color: red; text-decoration: line-through;">composer.lock</span></div>
  </div>
</div>
<div id="appComposerJsonForm" style="position: relative; display: inline-block; overflow-x: hidden; overflow-y: auto; height: auto; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
<?php if (defined('COMPOSER_JSON') && realpath(COMPOSER_JSON['path'])) { ?>
<div style="display: inline-block; width: 100%; margin-bottom: 10px;">
  <div class="text-xs" style="display: inline-block; float: left; background-color: #0078D7; color: white;">Last Update: <span <?= (isset(COMPOSER['json']->time) && COMPOSER['json']->time === '' ? 'style="background-color: white; color: red;"' : 'style="background-color: white; color: #0078D7;"') ?>><?= (isset(COMPOSER['json']->time) && COMPOSER['json']->time !== '' ? COMPOSER['json']->{'time'} : date('Y-m-d H:i:s')) ?></span></div>
  
  
  <div class="text-xs" style="display: inline-block; float: right;">
    <input type="checkbox" name="composer[update]" value="" checked /> <span style="background-color: #0078D7; color: white;">Update</span>
    <input type="checkbox" name="composer[install]" value="" checked /> <span style="background-color: #0078D7; color: white;">Install</span>
  </div>
</div>
<?php } ?>
<div style="display: inline-block; width: 100%;"><span <?= (isset(COMPOSER['json']->{'name'}) && COMPOSER['json']->{'name'} !== '' ? '' : 'style="background-color: #fff; color: red;" title="Either Vendor or Package is missing"') ?>>Name:</span>
  <div style="position: relative; float: right;">
    <div class="absolute font-bold" style="position: absolute; top: -8px; left: 5px; font-size: 10px; z-index: 1;">Vendor</div>
    <input type="text" id="tst" name="composer[config][name][vendor]" placeholder="vendor" value="<?= (defined('COMPOSER') && isset(COMPOSER['json']->name) ? explode('/', COMPOSER['json']->name)[0] : ''); ?>" size="13"> / <div class="absolute font-bold" style="position: absolute; top: -8px; right: 82px; font-size: 10px; z-index: 1;">Package</div> <input type="text" id="tst" name="composer[config][name][package]" placeholder="package" value="<?= (defined('COMPOSER') && isset(COMPOSER['json']->name)? explode('/', COMPOSER['json']->name)[1] : ''); ?>" size="13" />   
  </div>
</div>
<div style="display: inline-block; width: 100%;"><label for="composer-description" <?= (isset(COMPOSER['json']->{'description'}) && COMPOSER['json']->{'description'} !== '' ? '' : 'style="background-color: #fff; color: red; cursor: pointer;" title="Description is missing"') ?>>Description:</label>
  <div style="float: right;">
    <input id="composer-description" type="text" name="composer[config][description]" placeholder="Details" value="<?= (defined('COMPOSER') && isset(COMPOSER['json']->description)? COMPOSER['json']->description : ''); ?>">
  </div>
</div>

<!-- version -->
<div style="display: inline-block; width: 100%;"><label for="composer-version" <?= (isset(COMPOSER['json']->{'version'}) && preg_match(COMPOSER_EXPR_VER, COMPOSER['json']->{'version'}) ? '' : 'style="background-color: #fff; color: red; cursor: pointer;" title="Version must follow this format: ' . COMPOSER_EXPR_VER . '"') ?>>Version:</label>
  <div style="float: right;">
    <input id="composer-version" type="text" name="composer[config][version]" size="10" placeholder="(Version) 1.2.3" style="text-align: right;" pattern="(\d+\.\d+(?:\.\d+)?)" value="<?= (defined('COMPOSER') && isset(COMPOSER['json']->version) ? COMPOSER['json']->version : ''); ?>">
  </div>
</div>
<!-- type -->
<div style="display: inline-block; width: 100%;">Type:
  <div style="float: right;">
    <select name="composer[config][type]">
      <option label="" <?= (defined('COMPOSER') && isset(COMPOSER['json']->license) ? '' : 'selected=""');?>></option>
<?php foreach (['library', 'project', 'metapackage', 'composer-plugin'] as $type) { ?>
      <option<?= (defined('COMPOSER') && isset(COMPOSER['json']->type) && COMPOSER['json']->type == $type ? ' selected=""' : '' ); ?>><?= $type; ?></option>
<?php } ?>
    </select>
  </div>
</div>
<div style="display: inline-block; width: 100%;">Keywords:
  <div style="float: right;">
    <input id="composerKeywordAdd" type="text" placeholder="Keywords" value="" onselect="add_keyword()">
  </div>
  <div class="clearfix"></div>
  <div id="composerAppendKeyword" style="padding: 10px 0 10px 0; display: <?= (defined('COMPOSER') && isset(COMPOSER['json']->keywords) && !empty(COMPOSER['json']->keywords) ? 'block' : 'none') ?>; width: 100%;">
<?php if (defined('COMPOSER') && isset(COMPOSER['json']->keywords)) foreach (COMPOSER['json']->keywords as $key => $keyword) { ?>
    <label for="keyword_<?= $key; ?>"><sup onclick="rm_keyword(\'keyword_<?= $key; ?>\');">[x]</sup><?= $keyword; ?></label><input type="hidden" id="keyword_<?= $key; ?>" name="composer[config][keywords][]" value="<?= $keyword; ?>" />&nbsp;
<?php } ?>
  </div>
</div>
<!-- homepage -->
<!-- readme -->
<!-- time -->
<!-- version_normalized -->
<div style="display: inline-block; width: 100%;">License:
  <div style="float: right;">
    <select name="composer[config][license]">
      <option label=""<?= (defined('COMPOSER') && isset(COMPOSER['json']->license) ? '' : ' selected=""' );?>></option>
<?php foreach (['WTFPL', 'GPL-3.0', 'MIT'] as $license) { ?>
      <option<?= (defined('COMPOSER') && isset(COMPOSER['json']->license) && COMPOSER['json']->license == $license ? ' selected=""' : '' ); ?>><?= $license; ?></option>
<?php } ?>
    </select>
  </div>
</div>
<!-- authors -->
<div style="display: inline-block; width: 100%;">Authors:<br />

<?php if (defined('COMPOSER') && isset(COMPOSER['json']->authors)) foreach (COMPOSER['json']->authors as $key => $author) { ?>
  <div style="position: relative; float: left;">
    <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name</div>
    <input type="text" id="tst" name="composer[config][authors][<?= $key ?>][name]" placeholder="name" value="<?= $author->{'name'} ?>" size="10"> /
    <div class="absolute font-bold" style="position: absolute; top: -8px; right: 134px; font-size: 10px;">Email</div>
    <input type="text" id="tst" name="composer[config][authors][<?= $key ?>][email]" placeholder="email" value="<?= $author->{'email'} ?>" size="18" />   
  </div>
  <div class="dropdown">
    <div id="myDropdown" class="dropdown-content">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key2 => $role) { ?>
      <a href="#!"><img style="float: left;" width="30" height="33" src="resources/images/role<?=$key2?>.fw.png"><?= $role; ?> <input type="radio" id="<?=$key2?>" style="float: right; cursor: pointer;" name="composer[config][authors][<?= $key ?>][role]" value="<?= $role; ?>" <?= (isset($author->{'role'}) && $author->{'role'} == $role ? ' checked=""' : '' ) ?> /></a>
<?php } ?>
    </div>
    <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
  </div>

<?php } else { ?>

  <div style="position: relative; float: left;">
    <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name</div>
    <input type="text" id="tst" name="composer[config][authors][0][name]" placeholder="name" value="Barry Dick" size="10"> / 
    <div class="absolute font-bold" style="position: absolute; top: -8px; right: 140px; font-size: 10px;">Email</div>
    <input type="text" id="tst" name="composer[config][authors][0][email]" placeholder="email" value="barryd.it@gmail.com" size="18" />   
  </div>&nbsp;

  <div class="dropdown">
    <div id="myDropdown" class="dropdown-content">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key => $role) { ?>
      <a href="#!"><img style="float: left;" width="30" height="33" src="resources/images/role<?=$key?>.fw.png"><?= $role; ?> <input type="radio" id="<?=$key?>" style="float: right; cursor: pointer;" name="composer[config][authors][0][role]" value="<?= $role; ?>" /></a>
<?php } ?>
    </div>
    <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
  </div>

<!--
    <select name="composerAuthorRole">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $role) { ?>
      <option<?= (defined('COMPOSER') && isset(COMPOSER['json']->authors) && COMPOSER['json']->authors->role ? 'value="' . $role . '"' : '') && (defined('COMPOSER') && isset(COMPOSER['json']->authors) && COMPOSER['json']->authors->role == $role ? ' selected=""' : '' ); ?>><?= $role; ?></option>
<?php } ?>
    </select>
-->
  
<!--        <label for="author_<?= $key; ?>"><sup onclick="rm_author(\'author_<?= $key; ?>\');">[x]</sup>' + event.target.value + '</label><input type="hidden" id="author_<?= $key; ?>" name="composerAuthors[]" value="' + event.target.value + '" />&nbsp; -->
<?php } ?>

</div>

<!-- source -->
<!-- dist -->

<!-- funding -->


<!--
"require": {
  "php": ">=5.3.0"
},
"autoload": {
  "psr-4": {
      "ResponseClass\\":"src/"
  }
},
"config":{
  "optimize-autoloader": true
}
-->

<div style="display: inline-block; width: 100%;"><hr />Require:
  <div style="float: right;">
    <input id="composerReqPkg" type="text" title="Enter Text and onSelect" list="composerReqPkgs" placeholder="" value="" onselect="get_package(this);">
    <datalist id="composerReqPkgs">
      <option value=""></option>
    </datalist>
  </div>
  <div id="composerAppendRequire" style="padding: 10px; display: <?= (defined('COMPOSER') && !isset(COMPOSER['json']->{'require'}) ? 'none' : 'block') ?>;">
    <datalist id="composerReqVersResults">
      <option value=""></option>
    </datalist>
<?php $i = 0; if (defined('COMPOSER') && isset(COMPOSER['json']->{'require'})) {
if (!isset(COMPOSER['json']->{'require'}->{'php'})) { ?>
    <input type="checkbox" checked="" onchange="this.indeterminate = !this.checked; document.getElementById('pkg_<?= $i; ?>').disabled = !this.checked">
    <input type="text" id="pkg_<?= $i; ?>" name="composer[config][require][]" value="<?= 'php:^' . PHP_VERSION ?>" list="composerReqVersResults" size="30" onselect="get_version('pkg_<?= $i; ?>')">
    <label for="pkg_<?= $i; ?>"></label><br />
<?php $i++; } foreach (COMPOSER['json']->{'require'} as $key => $require) { ?>
    <input type="checkbox" checked="" onchange="this.indeterminate = !this.checked; document.getElementById('pkg_<?= $i; ?>').disabled = !this.checked">
    <input type="text" id="pkg_<?= $i; ?>" name="composer[config][require][]" value="<?= $key . ':' . $require ?>" list="composerReqVersResults" size="30" onselect="get_version('pkg_<?= $i; ?>')">
    <label for="pkg_<?= $i; ?>"></label><br />
<?php $i++; } } else { ?>
    <input type="checkbox" checked="" onchange="this.indeterminate = !this.checked; document.getElementById('pkg_<?= $i; ?>').disabled = !this.checked">
    <input type="text" id="pkg_<?= $i; ?>" name="composer[config][require][]" value="<?= 'php:^' . PHP_VERSION ?>" list="composerReqVersResults" size="30" onselect="get_version('pkg_<?= $i; ?>')">
    <label for="pkg_<?= $i; ?>"></label><br />
<?php } ?>
  </div>
</div>
<div style="display: inline-block; width: 100%;">Require-dev:
  <div style="float: right;">
    <input id="composerRequireDevPkg" type="text" placeholder="" value="" list="composerReqDevPackages" onselect="get_dev_package()">
    <datalist id="composerReqDevPackages">
      <option value=""></option>
    </datalist>
  </div>
  <div id="composerAppendRequire-dev" style="padding: 10px; display: <?= (defined('COMPOSER') && !isset(COMPOSER['json']->{'require-dev'}) ? 'none' : 'block') ?>;">
    <datalist id="composerReq-devVersResults">
      <option value=""></option>
    </datalist>
<?php $i = 0; if (defined('COMPOSER') && isset(COMPOSER['json']->{'require-dev'})) foreach (COMPOSER['json']->{'require-dev'} as $key => $require) { ?>
    <input type="checkbox" checked="" onchange="this.indeterminate = !this.checked; document.getElementById('pkg-dev_<?= $i; ?>').disabled = !this.checked">
    <input type="text" id="pkg-dev_<?= $i; ?>" name="composer[config][require-dev][]" value="<?= $key . ':' . $require ?>" list="composerReqVersResults" size="30" onselect="get_version('pkg-dev_<?= $i; ?>')">
    <label for="pkg-dev_<?= $i; ?>"></label><br />
<?php $i++; } ?>
  </div>
</div>

<div style="display: inline-block; width: 100%;">Autoload:
  <div style="float: right;">
    <input type="text" name="composer[config][autoload]" placeholder="Autoload" value="">
  </div>
</div>
<div style="display: inline-block; width: 100%;">Autoload-dev:
  <div style="float: right;">
    <input type="text" name="composer[config][autoload-dev]" placeholder="Autoload-dev" value="">
  </div>
</div>

<div style="display: inline-block; width: 100%;">Minimum-Stability:
  <div style="float: right;">
    <select name="composer[config][minimum-stability]">
<?php if (defined('COMPOSER')) foreach (['stable', 'rc', 'beta', 'alpha', 'dev'] as $ms) { ?>
      <option value="<?= $ms ?>"<?= (isset(COMPOSER['json']->{'minimum-stability'}) && COMPOSER['json']->{'minimum-stability'} == $ms ? ' selected=""' : '' )?>><?= $ms ?></option>
<?php } ?>
    </select>
  </div>
</div>
</div>
<div style="position: relative; display: inline-block; background-color: rgb(225,196,151,.25); width: 100%; z-index: 1;">
<?php //if (defined('COMPOSER_JSON')) $composer = json_decode(COMPOSER_JSON['json']); ?>
  <div class="text-sm" style="display: inline;">
    <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="" value="true" checked=""> -->

    <label for="composerJson" id="appComposerVendorJsonLabel" class="text-sm" style="background-color: #6B4329; <?= (defined('VENDOR_JSON') && realpath(VENDOR_JSON['path']) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color:red; text-decoration: underline; text-decoration: line-through;') ?> cursor: pointer; font-weight: bold;" title="<?= (defined('VENDOR_JSON') && realpath(VENDOR_JSON['path']) ? VENDOR_JSON['path'] : '') /*NULL*/; ?>">&#9650; <code>COMPOSER_PATH/[vendor/*].json</code></label>
    <div class="text-xs" style="display: <?= (!is_file(APP_PATH . 'composer.lock') ? 'none' : 'inline-block' )?>; padding-top: 5px; padding-right: 10px; float: right;"></div>
  </div>
</div>

<div id="appComposerVendorJsonForm" style="position: relative; display: inline-block; overflow-x: hidden; overflow-y: auto; height: auto; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7; width: 100%;">
<?php if (defined('VENDOR')) { ?>


<?php if (defined('VENDOR_JSON') && realpath(VENDOR_JSON['path'])) { ?>
<div style="display: block; width: 100%; margin-bottom: 10px;">
  <div class="text-xs" style="display: inline-block; float: left; background-color: #0078D7; color: white;">Last Update: <span <?= (isset(VENDOR->time) && VENDOR->time === '' ? 'style="background-color: white; color: red;"' : 'style="background-color: white; color: #0078D7;"') ?>><?= (isset(VENDOR->time) && VENDOR->time !== '' ? VENDOR->{'time'} : date('Y-m-d H:i:s')) ?></span></div>

</div>
<?php } ?>


<div style="display: inline-block; width: 100%;"><span <?= (isset(VENDOR->{'name'}) && VENDOR->{'name'} !== '' ? '' : 'style="background-color: #fff; color: red;" title="Either Vendor or Package is missing"') ?>>Vendor/Package:</span>
  <div style="position: relative; float: right;"><?php

$keys = array_keys(get_object_vars(COMPOSER['json']->{'require'}));
if (defined('COMPOSER') && isset(COMPOSER['json']->{'require-dev'}) && !empty(get_object_vars(COMPOSER['json']->{'require-dev'})))
$keys = array_merge($keys, array_keys(get_object_vars(COMPOSER['json']->{'require-dev'})));

?>
    <select onselect="selectPackage()">
      <option>---</option>
<?php
foreach($keys as $package) {
if ($package == 'php') continue;
elseif (isset(COMPOSER['json']->{'require'}->{$package}))
echo "<option selected>$package</option>";
else echo "<option>$package</option>";
}
?>
    </select>
  </div>
</div>

<div style="display: inline-block; width: 100%;"><label for="description" <?= (isset(VENDOR->{'description'}) && VENDOR->{'description'} !== '' ? '' : 'style="background-color: #fff; color: red; cursor: pointer;" title="Description is missing"') ?>>Description:</label>
  <div style="float: right;">
    <input id="description" type="text" name="" placeholder="Details" value="<?= (defined('VENDOR') && isset(VENDOR->description)? VENDOR->description : ''); ?>">
  </div>
</div>

<!-- version -->
<div style="display: inline-block; width: 100%;"><label for="version" <?= (isset(VENDOR->{'version'}) && preg_match(COMPOSER_EXPR_VER, VENDOR->{'version'}) ? '' : 'style="background-color: #fff; color: red; cursor: pointer;" title="Version must follow this format: ' . COMPOSER_EXPR_VER . '"') ?>>Version:</label>
  <div style="float: right;">
    <input id="version" type="text" name="" size="10" placeholder="(Version) 1.2.3" style="text-align: right;" pattern="(\d+\.\d+(?:\.\d+)?)" value="<?= (defined('VENDOR') && isset(VENDOR->version) ? VENDOR->version : ''); ?>">
  </div>
</div>
<!-- type -->
<div style="display: inline-block; width: 100%;">Type:
  <div style="float: right;">
    <select name="">
      <option label="" <?= defined('VENDOR') && isset(VENDOR->license) ? '' : 'selected=""';?>></option>
<?php foreach (['library', 'project', 'metapackage', 'composer-plugin'] as $type) { ?>
      <option<?= defined('VENDOR') && isset(VENDOR->type) && VENDOR->type == $type ? ' selected=""' : ''; ?>><?= $type; ?></option>
<?php } ?>
    </select>
  </div>
</div>
<div style="display: inline-block; width: 100%;">Keywords:
  <div style="float: right;">
    <input type="text" placeholder="Keywords" value="">
  </div>
  <div class="clearfix"></div>
  <div id="composerAppendKeyword" style="padding: 10px 0 10px 0; display: <?= defined('VENDOR') && isset(VENDOR->keywords) && !empty(VENDOR->keywords) ? 'block' : 'none' ?>; width: 100%;">
<?php if (defined('VENDOR') && isset(VENDOR->keywords)) foreach (VENDOR->keywords as $key => $keyword) { ?>
    <label for="keyword_<?= $key; ?>"><sup onclick="rm_keyword(\'keyword_<?= $key; ?>\');">[x]</sup><?= $keyword; ?></label>&nbsp;
<?php } ?>
  </div>
</div>
<!-- homepage -->
<!-- readme -->
<!-- time -->
<!-- version_normalized -->
<div style="display: inline-block; width: 100%;">License:
  <div style="float: right;">
    <select name="">
      <option label=""<?= defined('VENDOR') && isset(VENDOR->license) ? '' : ' selected=""';?>></option>
<?php foreach (['WTFPL', 'GPL-3.0', 'MIT'] as $license) { ?>
      <option <?= defined('VENDOR') && isset(VENDOR->license) && VENDOR->license == $license ? 'selected=""' : ''; ?>><?= $license; ?></option>
<?php } ?>
    </select>
  </div>
</div>
<!-- authors -->
<div style="display: inline-block; width: 100%;">Authors:<br />

<?php if (defined('VENDOR') && isset(VENDOR->authors)) foreach (VENDOR->authors as $key => $author) { ?>
  <div style="position: relative; float: left;">
    <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name</div>
    <input type="text" id="tst" name="" placeholder="name" value="<?= $author->{'name'} ?>" size="10"> /
    <div class="absolute font-bold" style="position: absolute; top: -8px; right: 134px; font-size: 10px;">Email</div>
    <input type="text" id="tst" name="" placeholder="email" value="<?= $author->{'email'} ?>" size="18" />   
  </div>
  <div class="dropdown">
    <div id="myDropdown" class="dropdown-content">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key2 => $role) { ?>
      <a href="#!"><img style="float: left;" width="30" height="33" src="resources/images/role<?=$key2?>.fw.png"><?= $role; ?> <input type="radio" id="<?=$key2?>" style="float: right; cursor: pointer;" name="" value="<?= $role; ?>" <?= (isset($author->{'role'}) && $author->{'role'} == $role ? ' checked=""' : '' ) ?> /></a>
<?php } ?>
    </div>
    <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
  </div>

<?php } else { ?>

  <div style="position: relative; float: left;">
    <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name</div>
    <input type="text" id="tst" name="" placeholder="name" value="Barry Dick" size="10"> / 
    <div class="absolute font-bold" style="position: absolute; top: -8px; right: 140px; font-size: 10px;">Email</div>
    <input type="text" id="tst" name="" placeholder="email" value="barryd.it@gmail.com" size="18" />   
  </div>&nbsp;

  <div class="dropdown">
    <div id="myDropdown" class="dropdown-content">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key => $role) { ?>
      <a href="#!"><img style="float: left;" width="30" height="33" src="resources/images/role<?=$key?>.fw.png"><?= $role; ?> <input type="radio" id="<?=$key?>" style="float: right; cursor: pointer;" name="" value="<?= $role; ?>" /></a>
<?php } ?>
    </div>
    <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
  </div>

<!--
    <select name="">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $role) { ?>
      <option<?= (defined('COMPOSER') && isset(COMPOSER->{'authors'}) && COMPOSER->{'authors'}->role ? "value=\"$role\"" : '') && (defined('COMPOSER') && isset(COMPOSER->authors) && COMPOSER->authors->role == $role ? ' selected=""' : '' ); ?>><?= $role; ?></option>
<?php } ?>
    </select>
-->
  
<!--        <label for="author_<?= $key; ?>"><sup onclick="rm_author(\'author_<?= $key; ?>\');">[x]</sup>' + event.target.value + '</label><input type="hidden" id="author_<?= $key; ?>" name="" value="' + event.target.value + '" />&nbsp; -->
<?php } ?>

</div>

<!-- source -->
<!-- dist -->

<!-- funding -->


<!--
"require": {
  "php": ">=5.3.0"
},
"autoload": {
  "psr-4": {
      "ResponseClass\\":"src/"
  }
},
"config":{
  "optimize-autoloader": true
}
-->

<div style="display: inline-block; width: 100%;"><hr />Require:
  <div style="float: right;">
    <input type="text" title="Enter Text and onSelect" placeholder="" value="">
  </div>
  <div style="padding: 10px; display: <?= (defined('VENDOR') && !isset(VENDOR->{'require'}) ? 'none' : 'block') ?>;">
<?php $i = 0; if (defined('VENDOR') && isset(VENDOR->{'require'})) {
if (!isset(VENDOR->{'require'}->{'php'})) { ?>
    <input type="checkbox" checked="" />
    <input type="text" value="<?= 'php:^' . PHP_VERSION ?>" size="30" />
    <label for="pkg_<?= $i; ?>"></label><br />
<?php $i++; } foreach (VENDOR->{'require'} as $key => $require) { ?>
    <input type="checkbox" checked="" />
    <input type="text" name="" value="<?= $key . ':' . $require ?>" size="30" />
    <label for="pkg_<?= $i; ?>"></label><br />
<?php $i++; } } else { ?>
    <input type="checkbox" checked="" />
    <input type="text" id="pkg_<?= $i; ?>" name="" value="<?= 'php:^' . PHP_VERSION ?>" size="30" />
    <label for="pkg_<?= $i; ?>"></label><br />
<?php } ?>
  </div>
</div>
<div style="display: inline-block; width: 100%;">Require-dev:
  <div style="float: right;">
    <input type="text" placeholder="" name="" value="" />
  </div>
  <div style="padding: 10px; display: <?= (defined('VENDOR') && !isset(VENDOR->{'require-dev'}) ? 'none' : 'block') ?>;">
<?php $i = 0; if (defined('VENDOR') && isset(VENDOR->{'require-dev'})) foreach (VENDOR->{'require-dev'} as $key => $require) { ?>
    <input type="checkbox" checked="" />
    <input type="text" id="pkg-dev_<?= $i; ?>" name="" value="<?= $key . ':' . $require ?>" size="30" />
    <label for="pkg-dev_<?= $i; ?>"></label><br />
<?php $i++; } ?>
  </div>
</div>

<div style="display: inline-block; width: 100%;">Autoload:
  <div style="float: right;">
    <input type="text" name="" placeholder="Autoload" value="">
  </div>
</div>
<div style="display: inline-block; width: 100%;">Autoload-dev:
  <div style="float: right;">
    <input type="text" name="" placeholder="Autoload-dev" value="">
  </div>
</div>

<div style="display: inline-block; width: 100%;">Minimum-Stability:
  <div style="float: right;">
    <select name="">
<?php if (defined('VENDOR')) foreach (['stable', 'rc', 'beta', 'alpha', 'dev'] as $ms) { ?>
      <option value="<?= $ms ?>"<?= (isset(VENDOR->{'minimum-stability'}) && VENDOR->{'minimum-stability'} == $ms ? ' selected=""' : '' )?>><?= $ms ?></option>
<?php } ?>
    </select>
  </div>
</div>
  <div style="padding: 10px; width: 100%;">

  </div>

<?php } ?>

</div>

<div style="height: 15px;"></div>

</form>

</div>

<?php
$count = 0;
if (defined('COMPOSER') && isset(COMPOSER['json']->require))
foreach (COMPOSER['json']->require as $key => $require)
if (preg_match('/.*\/.*:.*/', $key . ':' . $require)) 
if (preg_match('/(.*\/.*)/', $key, $match))
  if (!empty($match) && !is_dir(APP_BASE['vendor'] . $match[1].'/')) $count++;

?>
<div id="app_composer-frameInstall" class="app_composer-frame-container absolute <?= $count > 0 ? 'selected' : ''; ?>" style="overflow: scroll; width: 400px; height: 270px;">
<form autocomplete="off" spellcheck="false" action="<?= APP_URL . '?' . http_build_query(APP_QUERY + array( 'app' => 'composer')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '')  /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="POST">  
<div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
  <input type="hidden" name="composer[install]" value="" />
  <div style="position: absolute; right: 0; float: right; text-align: center; z-index: 1;">

    <button id="composerInstallSubmit" class="btn" type="submit" style="<?= ($count > 0 ? 'color: red;' : '' ); ?>" value>Install (<?= ($count > 0 ? $count : '' ); ?>)</button>
  </div> 
  <div class="text-sm" style="display: inline;">
    <label id="composerInstallLabel" for="composerInstall" style="background-color: hsl(343, 100%, 42%); color: white; cursor: pointer;">&#9650; <code>Install</code></label>
  </div>

</div>
<?php if ($count > 0) { ?>
<div id="" style="display: inline-block; padding: 10px; margin-bottom: 5px; width: 100%; background-color: rgba(235, 216, 186, 0.8);  border: 1px dashed #0078D7;">

  Install (vendor/package): 
  <span >
  <ul style="padding-left: 10px;">
<?php
foreach (COMPOSER['json']->require as $key => $require) {
if (preg_match('/.*\/.*:.*/', $key . ':' . $require)) 
if (preg_match('/(.*\/.*)/', $key, $match))
if (!empty($match) && !is_dir(APP_BASE['vendor'] . $match[1].'/')) echo '<li style="color: red;"><code class="text-sm">' . $match[1] . ':' . '<span style="float: right">' . $require . '</span>' . "</code></li>\n";
}
?>
  </ul>
  </span>
</div>
<?php } ?>
<div id="composerInstallForm" style="display: inline-block; padding: 10px; margin-bottom: 5px; height: 250px; width: 100%; background-color: rgb(225,196,151,.25);  border: 1px dashed #0078D7;">
<div style="display: inline-block; width: 100%;">
  <label>Self-update <!--(C:\ProgramData\ComposerSetup\bin\composer.phar)--></label>
  <div style="float: right;">
    <input type="checkbox" name="composer[self-update]" value="true" <?= (!file_exists(APP_PATH . 'composer.phar') ? '' : 'checked=""') ?>/>
  </div>
</div>
<div style="display: inline-block; width: 100%;">
  <label>Optimize Classes</label>
  <div style="float: right;">
    <input type="checkbox" name="composer[optimize-classes]" checked="">
  </div>
</div>
<div style="display: inline-block; width: 100%;">
  <label>Update Packages</label>
  <div style="float: right;">
    <input type="checkbox" name="composer[update]" checked="">
  </div>
</div>
</div>
</form>
</div>

<?php if (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') == 0 ) echo $frameUpdateContents; ?>

</div>

</div>
<!-- future feature: convert div from absolute to fixed. make screen bigger. <div style="position: relative; text-align: right; cursor: pointer; width: 400px; margin: 0 auto; border: 1px solid #000;"> &#9660;</div> -->
</div>

<?php $app['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
var keyword_i = 0;

/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
function myFunction() {
  document.getElementById("myDropdown").classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}

function rm_keyword(argv_id) {
  var el = document.querySelector('label[for=' + argv_id + ']');
  var input = document.getElementById(argv_id);
  if (el) el.remove();
  if (input) input.remove();
  //console.log(document.getElementById('composerAppendKeyword').childNodes.length);
  if (document.getElementById('composerAppendKeyword').childNodes.length == 4) document.getElementById('composerAppendKeyword').style.display = "none";
}

function add_keyword() {
    if (event.target.value == '') return;
    var filledInputs = $('#composerAppendKeyword').find(':input[type=text]').filter(function() {return !!this.value;}).length;
    document.getElementById('composerAppendKeyword').style.display = "inline-block";
    keywordOption = '<label class="text-sm" for="keyword_' + keyword_i + '" ><sup onclick="rm_keyword(\'keyword_' + keyword_i + '\');">[x]</sup>' + event.target.value + '</label><input type="hidden" id="keyword_' + keyword_i + '" name="composer[config][keywords][]" value="' + event.target.value + '" />&nbsp;';
    keyword_i++;
    document.getElementById('composerAppendKeyword').insertAdjacentHTML('beforeend', keywordOption); // innerHTML += keywordOption
    document.getElementById('composerKeywordAdd').value = '';
}

//document.getElementById("composerAppendKeyword").childElementCount
//var x = $('#composerAppendKeyword').find(':input[type=hidden]').filter(function() {return !!this.value;}).length; 
//alert(x);


document.getElementById("composerReqPkg").addEventListener("input", function(event){
  if(event.inputType == "insertReplacementText" || event.inputType == null) {
    var filledInputs = $('#composerAppendRequire').find(':input[type=text]').filter(function() {return !!this.value;}).length;
    document.getElementById('composerAppendRequire').style.display = "inline-block";
    packageOption = '<input type="checkbox" checked onchange="this.indeterminate = !this.checked; document.getElementById(\'pkg_' + filledInputs + '\').disabled = !this.checked"/> <input type="text" id="pkg_' + filledInputs + '" name="composer[config][require][]" value="' + event.target.value + '" list="composerReqVersResults" size="30" onSelect="get_version(\'pkg_' + filledInputs + '\')" /><label for="pkg_' + filledInputs + '"></label><br />';
    document.getElementById('composerAppendRequire').insertAdjacentHTML('beforeend', packageOption); // innerHTML += packageOption
    event.target.value = "";
  }
});


function selectPackage() { 

}

function get_package(element) { // onSelect="get_package()"
  var val = element.value; // document.getElementById("composerReqPkg")
  console.log(element.id+ 's');
  var url, packagesOption;
  url = 'https://packagist.org/search.json?q=' + val;
  document.getElementById(element.id + 's').innerHTML = '';
  $.getJSON(url, function(data) {
  //populate the packages datalist
    $(data.results).each(function() {
      packagesOption = '<option value="' + this.name + '" />';
      $('#' + element.id + 's').append(packagesOption);
      //console.log(this.favers);
    });
  });
}

function get_version(argv_id) { // onSelect="get_version()"
  var val = document.getElementById(argv_id).value;
  var url, packagesOption;
  //var vendorPkg = val.split("/");

  url = 'https://repo.packagist.org/p2/' + val + '.json'; 
  document.getElementById('composerReqVersResults').innerHTML = '';
  $.getJSON(url, function(data) {
  //populate the packages datalist
    packagesOption = '<option value="' + val + ':dev-master" />';
    $('#composerReqVersResults').append(packagesOption);
    var vers = $(data.packages[val])[0].version.split(/(\d+\.\d+(?:\.\d+)?)/);
    packagesOption = '<option value="' + val + ':^' + vers[1] + '" />';
    $('#composerReqVersResults').append(packagesOption);
/*  
    $(data.packages[val]).each(function() {
      packagesOption = '<option value="' + val + ':^' + this.version + '" />';
      $('#composerReqVersResults').append(packagesOption);
      //console.log(this.version);
    });
*/
  });
}

document.getElementById("composerRequireDevPkg").addEventListener("input", function(event){
  if(event.inputType == "insertReplacementText" || event.inputType == null) {
    var filledInputs = $('#composerAppendRequire-dev').find(':input[type=text]').filter(function() {return !!this.value;}).length;
    document.getElementById('composerAppendRequire-dev').style.display = "inline-block";
    packageOption = '<input type="checkbox" checked onchange="this.indeterminate = !this.checked; document.getElementById(\'pkg-dev_' + filledInputs + '\').disabled = !this.checked"/><input type="text" id="pkg-dev_' + filledInputs + '" name="composerRequireDevPkgs[]" value="' + event.target.value + '" list="composerReq-devVersResults" size="30" onSelect="get_dev_version(\'pkg-dev_' + filledInputs + '\')" /><label for="pkg-dev_' + filledInputs + '"></label><br />';
    document.getElementById('composerAppendRequire-dev').insertAdjacentHTML('beforeend', packageOption); // innerHTML += packageOption
    event.target.value = "";
  }
});

function get_dev_package() { // onSelect="get_dev_package()"
  var val = document.getElementById("composerRequireDevPkg").value;
  var url, packagesOption;
  url = 'https://packagist.org/search.json?q=' + val;
  document.getElementById('composerReqDevPackages').innerHTML = '';
  $.getJSON(url, function(data) {
  //populate the packages datalist
    $(data.results).each(function() {
      packagesOption = "<option value=\"" + this.name + "\" />";
      $('#composerReqDevPackages').append(packagesOption);
      //console.log(this.favers);
    });
  });
}

function get_dev_version(argv_id) { // onSelect="get_version()"
  var val = document.getElementById(argv_id).value;
  var url, packagesOption;
  //var vendorPkg = val.split("/");
  url = 'https://repo.packagist.org/p2/' + val + '.json'; 
  document.getElementById('composerReq-devVersResults').innerHTML = '';
  $.getJSON(url, function(data) {
  //populate the packages datalist
    packagesOption = '<option value="' + val + ':dev-master" />';
    $('#composerReq-devVersResults').append(packagesOption);
    var vers= $(data.packages[val])[0].version.split(/(\d+\.\d+(?:\.\d+)?)/);
    packagesOption = '<option value="' + val + ':^' + vers[1] + '" />';
    $('#composerReq-devVersResults').append(packagesOption);
/*  
    $(data.packages[val]).each(function() {
      packagesOption = '<option value="' + val + ':^' + this.version + '" />';
      $('#composerReq-devVersResults').append(packagesOption);
      //console.log(this.version);
    });
*/
  });
}

//document.getElementById("bottom").style.zIndex = "1";

$(document).ready(function() {
  var composer_frame_containers = $(".app_composer-frame-container");
  var totalFrames = composer_frame_containers.length;
  var currentIndex = 0;
  
  console.log(totalFrames + ' - total frames');

  $("#appComposerAuthLabel").click(function() {
    if ($('#appComposerAuthJsonForm').css('display') == 'none') {
      $('#appComposerAuthLabel').html("&#9650; <code>COMPOSER_HOME/auth.json");
      $('#appComposerAuthJsonForm').slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('#appComposerAuthLabel').html("&#9660; <code>COMPOSER_HOME/auth.json</code>");
      $('#appComposerAuthJsonForm').slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });
  


  $("#appComposerVendorJsonLabel").click(function() {
    if ($('#appComposerVendorJsonForm').css('display') == 'none') {
      $('#appComposerVendorJsonLabel').html("&#9650; <code>COMPOSER_PATH/[vendor/*].json</code>");
      $('#appComposerVendorJsonForm').slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('#appComposerVendorJsonLabel').html("&#9660; <code>COMPOSER_PATH/[vendor/*].json</code>");
      $('#appComposerVendorJsonForm').slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });

  $("#appComposerJsonLabel").click(function() {
    if ($('#appComposerJsonForm').css('display') == 'none') {
      $('#appComposerJsonLabel').html("&#9650; <code>COMPOSER_PATH/composer.json</code>");
      $('#appComposerJsonForm').slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('#appComposerJsonLabel').html("&#9660; <code>COMPOSER_PATH/composer.json</code>");
      $('#appComposerJsonForm').slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });

  $("#app_composer-frameMenuInit").click(function() {
    currentIndex = 1;
    $("#app_composer-frameMenuPrev").html('&lt; Menu');
    $("#app_composer-frameMenuNext").html('Conf &gt;');
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
  });

  $("#app_composer-frameMenuConf").click(function() {
    currentIndex = 2;
    $("#app_composer-frameMenuPrev").html('&lt; Init');
    $("#app_composer-frameMenuNext").html('Install &gt;');
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
  });   
  
  $("#app_composer-frameMenuInstall").click(function() {
    currentIndex = 3;
    $("#app_composer-frameMenuPrev").html('&lt; Conf');
    $("#app_composer-frameMenuNext").html('Update &gt;');
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
  });

  $("#app_composer-frameMenuUpdate").click(function() {
    currentIndex = 4;
    $("#app_composer-frameMenuPrev").html('&lt; Install');
    $("#app_composer-frameMenuNext").html('Menu &gt;');
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
  });   
 
  $(".composer-home").click(function() {
    currentIndex = -1; 
    composer_frame_containers.removeClass("selected");
    //composer_frame_containers.eq(currentIndex).addClass('selected');
  });

  $(".composer-menu").click(function() {
    currentIndex = 0; 
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
  });

  $("#app_composer-frameMenuPrev").click(function() {
    if (currentIndex <= 0) currentIndex = 5;
    console.log(currentIndex + '!=' + totalFrames);
    currentIndex--;
    if (currentIndex >= totalFrames) {
      currentIndex = 0;
    }
    if (currentIndex == 0) {
      $("#app_composer-frameMenuPrev").html('&lt; Update');
      $("#app_composer-frameMenuNext").html('Init &gt;');
    } else if (currentIndex == 1) {
      $("#app_composer-frameMenuPrev").html('&lt; Menu');
      $("#app_composer-frameMenuNext").html('Conf &gt;');
    } else if (currentIndex == 2) {
      $("#app_composer-frameMenuPrev").html('&lt; Init');
      $("#app_composer-frameMenuNext").html('Install &gt;');
    } else if (currentIndex == 3) {
      $("#app_composer-frameMenuPrev").html('&lt; Conf');
      $("#app_composer-frameMenuNext").html('Update &gt;');
    } else if (currentIndex == 4) {
      $("#app_composer-frameMenuPrev").html('&lt; Install');
      $("#app_composer-frameMenuNext").html('Menu &gt;');
    }

    //else 
    console.log('decided: ' + currentIndex);
    composer_frame_containers.removeClass("selected");
    composer_frame_containers.eq(currentIndex).addClass('selected');
    
    //currentIndex--;    
    console.log(currentIndex);
  });

  $("#app_composer-frameMenuNext").click(function() {
    currentIndex++;
    console.log(currentIndex + '!=' + totalFrames);
    if (currentIndex >= totalFrames) {
      currentIndex = 0;
    }
    if (currentIndex == 0) {
      $("#app_composer-frameMenuPrev").html('&lt; Update');
      $("#app_composer-frameMenuNext").html('Init &gt;');
    } else if (currentIndex == 1) {
      $("#app_composer-frameMenuPrev").html('&lt; Menu');
      $("#app_composer-frameMenuNext").html('Conf &gt;');
    } else if (currentIndex == 2) {
      $("#app_composer-frameMenuPrev").html('&lt; Init');
      $("#app_composer-frameMenuNext").html('Install &gt;');
    } else if (currentIndex == 3) {
      $("#app_composer-frameMenuPrev").html('&lt; Conf');
      $("#app_composer-frameMenuNext").html('Update &gt;');
    } else if (currentIndex == 4) {
      $("#app_composer-frameMenuPrev").html('&lt; Install');
      $("#app_composer-frameMenuNext").html('Menu &gt;');
    }
    if (currentIndex < 0) currentIndex++;
    //else 
    console.log('decided: ' + currentIndex);
    composer_frame_containers.removeClass("selected"); // composer_frame_containers.css("z-index", 0); // Reset z-index for all elements
    composer_frame_containers.eq(currentIndex).addClass('selected'); // css("z-index", totalFrames); // Set top layer z-index
  });
  
  $("#frameSelector").change(function() {
    var selectedIndex = parseInt($(this).val(), 10);
    currentIndex = selectedIndex;
    $(".app_composer-frame-container").removeClass("selected"); // Remove selected class from all containers
    $(".app_composer-frame-container").eq(currentIndex).addClass("selected"); // Apply selected class to the chosen container
  });
/*
  $('select').on('change', function (e) {
    var optionSelected = $("option:selected", this);
    var valueSelected = this.value;
  });
*/
});
<?php $app['script'] = ob_get_contents(); 
ob_end_clean();



/** Loading Time: 6.73s **/
  
  //dd(get_required_files(), true);

  ob_start(); ?>
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
    <!-- link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" /-->
  
  <?php
  // (check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/tailwindcss-3.3.5.js')?
  is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/') or mkdir($path, 0755, true);
  if (is_file($path . 'tailwindcss-3.3.5.js')) {
    if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime("{$path}tailwindcss-3.3.5.js"))))) / 86400)) <= 0 ) {
      $url = 'https://cdn.tailwindcss.com';
      $handle = curl_init($url);
      curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  
      if (!empty($js = curl_exec($handle))) 
        file_put_contents("{$path}tailwindcss-3.3.5.js", $js) or $errors['JS-TAILWIND'] = "$url returned empty.";
    }
  } else {
    $url = 'https://cdn.tailwindcss.com';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  
    if (!empty($js = curl_exec($handle))) 
      file_put_contents("{$path}tailwindcss-3.3.5.js", $js) or $errors['JS-TAILWIND'] = "$url returned empty.";
  }
  ?>
  
    <script src="<?= 'resources/js/tailwindcss-3.3.5.js' ?? $url ?>"></script>
  
  <style type="text/tailwindcss">
  <?= $app['style']; ?>
  </style>
  </head>
  <body>
  <?= $app['body']; ?>
  
    <script src="<?= check_http_status('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : "{$path}jquery-3.7.1.min.js" ?>"></script>
    <!-- You need to include jQueryUI for the extended easing options. -->
  <?php /* https://stackoverflow.com/questions/12592279/typeerror-p-easingthis-easing-is-not-a-function */ ?>
    <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script> <!-- Uncaught ReferenceError: jQuery is not defined -->
  
  <script>
  <?= $app['script']; ?>
  </script>
  </body>
  </html>
  <?php
  $app['html'] = ob_get_contents(); 
  ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ ==  get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'composer' && APP_DEBUG) {
  return $app['html'];
} else {
  return $app;
}

/** Loading Time: 7.0s **/
  
  //dd(get_required_files(), true);