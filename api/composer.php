<?php


require_once dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'constants.composer.php';

//require_once APP_PATH . 'bootstrap/api_init.php';
//require_once APP_PATH . 'api/handlers/composer_cmd.php';

// use Registry;



//require_once 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.php';
//require_once 'config' . DIRECTORY_SEPARATOR . 'config.php';

//die(var_dump(get_defined_constants(true)['user'], false)); // get_defined_constants(true) or die('get_defined_constants() failed.');

//dd(PHP_EXEC);




use PHPUnit\Event\Code\Throwable;
/*
{
  "autoload": {
      "psr-4": {
          "App\\": "src/"
      } ...
      "classmap": [
            "src/"
      ] ...
      "files": [
            "src/helpers.php",
            "src/constants.php"
      ]
  }
}
*/

$a = $b = 'string';
//echo is_bool($b === 0);

//ini_set('assert.exception', 1);

//ini_get('assert.exception') or ini_set('assert.exception', 1);

//dd(ini_get('assert.exception') or die('assert.exception is not set: ' . (bool) ini_get('assert.exception')));

/* PHP Assertion Exception Handling */

global $errors;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.env.php';
    require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.url.php';
    require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php';

    //dd(get_required_files());
    if (isset($_POST['composer']['autoload'])) {
        $_ENV['COMPOSER']['AUTOLOAD'] = $_POST['composer']['autoload'] === 'on' ? true : false;
        Shutdown::setEnabled(false)->setShutdownMessage(fn() => header("Location: http://localhost{$_SERVER['PHP_SELF']}"))->shutdown();
    }

    //die(var_dump($_POST));


    dd($_POST);
    //dd($_ENV['COMPOSER']['AUTOLOAD']);

    //dd($_ENV);

    // consider creating a visual aspect for the lock file

    //dd($_POST);

    chdir(APP_PATH . APP_ROOT);

    if (isset($_POST['composer']['create-project']) && preg_match(COMPOSER_EXPR_NAME, $_POST['composer']['package'], $matches)) {
        if (!is_dir($path = APP_PATH . 'project'))
            (@!mkdir($path, 0755, true) ?: $errors['COMPOSER-PROJECT'] = 'project/ could not be created.');
        if ($matches[1] == 'laravel' && $matches[2] == 'laravel')
            exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'composer create-project ' . $_POST['composer']['package'] . ' project/laravel', $output, $returnCode) or $errors['COMPOSER-PROJECT-LARAVEL'] = $output;
        elseif ($matches[1] == 'symfony' && $matches[2] == 'skeleton')
            exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'composer create-project ' . $_POST['composer']['package'] . ' project/symfony', $output, $returnCode) or $errors['COMPOSER-PROJECT-SYMFONY'] = $output;

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

        if (!is_file('var' . DIRECTORY_SEPARATOR . 'package-' . $vendor . '-' . $package . '.php')) {

            $source_blob = check_http_status($raw_url) ? file_get_contents($raw_url) : '';

            //dd('url: ' . $raw_url);
            $raw_url = addslashes($raw_url);

            $source_blob = addslashes(COMPOSER_JSON['json']); // $source_blob
            file_put_contents(
                'var' . DIRECTORY_SEPARATOR . 'package-' . $vendor . '-' . $package . '.php',
                '<?php' . "\n" . (check_http_status($raw_url) ? "\$source = \"$raw_url\";" : '') . "\n" .
                <<<END
\$composer_json = "{$source_blob}";
return '<form action method="POST">'
. '...'
. '</form>';
END
            );

            if (isset($_POST['composer']['install'])) {
                exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'composer require ' . $_POST['composer']['package'], $output, $returnCode) or $errors['COMPOSER-REQUIRE'] = $output;
                exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'composer update ' . $_POST['composer']['package'], $output, $returnCode) or $errors['COMPOSER-UPDATE'] = $output;
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
        else
            unset($composer->{'version'});
        $composer->{'type'} = $_POST['composer']['config']['type'];
        $composer->{'keywords'} = $_POST['composer']['config']['keywords'] ?? [];
        $composer->{'homepage'} = 'https://github.com/' . $_POST['composer']['config']['name']['vendor'] . '/' . $_POST['composer']['config']['name']['package'];
        $composer->{'readme'} = 'README.md';
        $composer->{'time'} = date('Y-m-d H:i:s');
        $composer->{'license'} = $_POST['composer']['config']['license'];
        $composer->{'authors'} = []; //$_POST['composer']['authors'];

        if (!empty($_POST['composer']['config']['authors']))
            foreach ($_POST['composer']['config']['authors'] as $key => $author) {
                if ($author['name'] != '' || $author['email'] != '') {
                    $object = new stdClass();
                    $object->name = $author['name'] ?? 'John Doe';
                    $object->email = $author['email'] ?? 'jdoe@example.com';
                    $object->role = $author['role'] ?? 'Developer';

                    $composer->{'authors'}[] = $object;
                }
            } else
            $composer->{'authors'} = [];

        // $composer->{'support'}
        // $composer->{'funding'}

        // $composer->{'repositories'}

        if (!$composer->{'repositories'} || empty($composer->{'repositories'}))
            $composer->{'repositories'} = [];

        $composer->{'require'} = new stdClass(); //$_POST['composer']['require'];

        //dd($composer->{'require'});

        if (!empty($_POST['composer']['config']['require'])) {
            //if (!in_array($require_0, $_POST['composer']['require'])) { continue; }
            foreach ($_POST['composer']['config']['require'] as $require) {   //   

                if (preg_match('/^(.+?)[:=](.+)$/', $require, $match))
                    $composer->{'require'}->{$match[1]} = $match[2] ?? '^';
            }
        } else
            $composer->{'require'} = new StdClass;

        $composer->{'require-dev'} = new stdClass();

        if (!empty($_POST['composer']['config']['require-dev'])) {
            //if (!in_array($require_0, $_POST['composer']['require'])) { continue; }
            foreach ($_POST['composer']['config']['require-dev'] as $require) {   //   
                if (preg_match('/^(.+?)[:=](.+)$/', $require, $match))
                    $composer->{'require-dev'}->{$match[1]} = $match[2] ?? '^';
            }
        } else
            $composer->{'require-dev'} = new StdClass;

        $composer->{'autoload'} = new StdClass; // $_POST['composer']['autoload'];
        //$composer->{'autoload'}->{'psr-4'} = new StdClass; 
        //$composer->{'autoload'}->{'psr-4'}->{'HtmlToRtf\\'} = "src/HtmlToRtf";
        //$composer->{'autoload'}->{'psr-4'}->{'ProgressNotes\\'} = "src/HtmlToRtf";

        //$composer->{'autoload-dev'} = $_POST['composer']['autoload-dev'];

        $composer->{'minimum-stability'} = $_POST['composer']['config']['minimum-stability'];

        $composer->{'prefer-stable'} = true;

        dd($composer);
        /*
                $composer->{'config'}->{'preferred-install'} = 'dist';
                $composer->{'config'}->{'sort-packages'} = true;
                $composer->{'config'}->{'optimize-autoloader'} = true;
                $composer->{'config'}->{'classmap-authoritative'} = true;
                $composer->{'config'}->{'apcu-autoloader'} = true;
                $composer->{'config'}->{'apcu-autoloader-prefix'} = 'composer';
                $composer->{'config'}->{'platform'} = new stdClass();
                $composer->{'config'}->{'platform'}->{'php'} = PHP_VERSION;
                $composer->{'config'}->{'platform'}->{'ext-openssl'} = true;
                $composer->{'config'}->{'platform'}->{'ext-curl'} = true;
                $composer->{'config'}->{'platform'}->{'ext-json'} = true;
                $composer->{'config'}->{'platform'}->{'ext-mbstring'} = true;
                $composer->{'config'}->{'platform'}->{'ext-xml'} = true;
                $composer->{'config'}->{'platform'}->{'ext-zip'} = true;
                $composer->{'config'}->{'platform'}->{'ext-pdo'} = true;
                $composer->{'config'}->{'platform'}->{'ext-pdo_mysql'} = true;
                $composer->{'config'}->{'platform'}->{'ext-pdo_sqlite'} = true;
                $composer->{'config'}->{'platform'}->{'ext-pdo_pgsql'} = true;
                $composer->{'config'}->{'platform'}->{'ext-pdo_sqlsrv'} = true;
                $composer->{'config'}->{'platform'}->{'ext-ctype'} = true;
                $composer->{'config'}->{'platform'}->{'ext-iconv'} = true;
                $composer->{'config'}->{'platform'}->{'ext-fileinfo'} = true;
                $composer->{'config'}->{'platform'}->{'ext-tokenizer'} = true;
                $composer->{'config'}->{'platform'}->{'ext-xmlreader'} = true;
                $composer->{'config'}->{'platform'}->{'ext-xmlwriter'} = true;
                $composer->{'config'}->{'platform'}->{'ext-dom'} = true;
                $composer->{'config'}->{'platform'}->{'ext-simplexml'} = true;
                $composer->{'config'}->{'platform'}->{'ext-bcmath'} = true;
                $composer->{'config'}->{'platform'}->{'ext-gd'} = true;
                $composer->{'config'}->{'platform'}->{'ext-curl'} = true;
                $composer->{'config'}->{'platform'}->{'ext-openssl'} = true;
        */
        if (COMPOSER_AUTH['token'] != $_POST['auth']['github_oauth']) {
            $tmp_auth = json_decode(COMPOSER_AUTH['json']);
            $tmp_auth->{'github-oauth'}->{'github.com'} = $_POST['auth']['github_oauth'];

            file_put_contents(COMPOSER_AUTH['path'], json_encode($tmp_auth, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); // COMPOSER_AUTH['json']
        }

        file_put_contents(COMPOSER_JSON['path'], json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    }

    if (isset($_POST['composer']['init']) && !empty($_POST['composer']['init'])) {
        $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . str_replace(array("\r\n", "\r", "\n"), ' ', $_POST['composer']['init']) . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' update;', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

        [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

        if (empty($stdout)) {
            if (!empty($stderr))
                $errors['COMPOSER_INIT'] = '$stderr = ' . $stderr;
        } else
            $errors['COMPOSER_INIT'] = $stdout;

        //dd($errors);
    }
    //dd('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC . ' install ' . (isset($_POST['composer']['config']) ? '-o' : (isset($_POST['composer']['optimize-classes']) ? '-o': '')) . ';');

    isset($_POST['composer']['lock'])
        and unlink(APP_PATH . 'composer.lock');

    // https://stackoverflow.com/questions/33052195/what-are-the-differences-between-composer-update-and-composer-install

    if (isset($_POST['composer']['install'])) {

        $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' install ' . (isset($_POST['composer']['config']) ? '-o' : (isset($_POST['composer']['optimize-classes']) ? '-o' : '')) . ';', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

        [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

        if (empty($stdout)) {
            if (!empty($stderr))
                $errors['COMPOSER_INSTALL'] = "\$stderr = $stderr";
        } else
            $errors['COMPOSER_INSTALL'] = $stdout;

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
        $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' update', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

        [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

        if (empty($stdout)) {
            if (!empty($stderr))
                $errors['COMPOSER_UPDATE'] = "\$stderr = $stderr";
        } else
            $errors['COMPOSER_UPDATE'] = $stdout;

        if (defined('COMPOSER_VERSION') && defined('COMPOSER_LATEST'))
            if (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0) {
                $proc = proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . COMPOSER_EXEC['bin'] . ' self-update;', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

                [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

                if (empty($stdout)) {
                    if (!empty($stderr))
                        $errors['COMPOSER_UPDATE'] = "\$stderr = $stderr";
                } else
                    $errors['COMPOSER_UPDATE'] = $stdout;
            }
        // $_POST['composer']['cmd'];
    }

    //die(APP_URL);

    //exit(header('Location: ' . (is_array(APP_URL) ? APP_URL['scheme'] . '://' . APP_DOMAIN . '/' : APP_URL ) . '?' . http_build_query(APP_QUERY)));
    //dd($_POST);
}

//dd();

if (isset($_POST['cmd']) && $_POST['cmd'] != '' && preg_match('/^composer\s*(:?.*)/i', $_POST['cmd'], $match)) {

    if (!isset($GLOBALS['runtime']['socket']) || !$GLOBALS['runtime']['socket']) {

        $sudo_prefix = '';
        if (defined('APP_SUDO') && trim(APP_SUDO) !== '') {
            $sudo_prefix = APP_SUDO . ' -u www-data ';
        }

        //$output[] = dd(COMPOSER_EXEC);
        $output[] = 'Cmd: ' . $sudo_prefix . COMPOSER_EXEC['bin'] . ' ' . $match[1];
        $proc = proc_open(
            $sudo_prefix . COMPOSER_EXEC['bin'] . ' ' . $match[1] . ' --working-dir="' . APP_PATH . APP_ROOT . '"',
            [
                ["pipe", "r"],
                ["pipe", "w"],
                ["pipe", "w"]
            ],
            $pipes
        );
        [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
        $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (!isset($exitCode) && $exitCode == 0 ? NULL : " Exit Code: $exitCode");
        //$output[] = $_POST['cmd'];        
        //exec($_POST['cmd'], $output);
        //die(var_dump($output));
    }

    if (isset($output) && is_array($output)) {
        switch (count($output)) {
            case 1:
                echo /*(isset($match[1]) ? $match[1] : 'PHP') . ' >>> ' . */ join("\n... <<< ", $output);
                break;
            default:
                echo join("\n", $output);
                break;
        }

    }
    Shutdown::setEnabled(true)->setShutdownMessage(function () { })->shutdown();
}

unset($output);


if (basename(dirname(APP_SELF)) == __DIR__ . DIRECTORY_SEPARATOR . 'public') {
    if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'ui.composer.php')) {
        $app['html'] = require_once $path;
    }
}


