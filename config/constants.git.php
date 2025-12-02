<?php
/**/
// https://techglimpse.com/git-push-github-token-based-passwordless/
// git push https://<GITHUB_ACCESS_TOKEN>@github.com/<GITHUB_USERNAME>/<REPOSITORY_NAME>.git
//if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'constants.paths.php'))
//  require_once $path;

global $shell_prompt, $errors;

/*
// Path to the Git binary. Can be overridden in environment or platform-specific configs.
defined('GIT_BIN') || define('GIT_BIN', 'git');

// Core Git directory structure (relative to repository root)
defined('GIT_FOLDER') || define('GIT_FOLDER', '.git');
defined('GIT_CONFIG') || define('GIT_CONFIG', GIT_FOLDER . '/config');
defined('GIT_HEAD') || define('GIT_HEAD', GIT_FOLDER . '/HEAD');
defined('GIT_INDEX') || define('GIT_INDEX', GIT_FOLDER . '/index');
defined('GIT_OBJECTS') || define('GIT_OBJECTS', GIT_FOLDER . '/objects');
defined('GIT_REFS') || define('GIT_REFS', GIT_FOLDER . '/refs');
defined('GIT_HOOKS') || define('GIT_HOOKS', GIT_FOLDER . '/hooks');
defined('GIT_LOGS') || define('GIT_LOGS', GIT_FOLDER . '/logs');
defined('GIT_INFO_EXCLUDE') || define('GIT_INFO_EXCLUDE', GIT_FOLDER . '/info/exclude'); // per-repo ignore

// Git root-level configuration files (outside of .git/)
defined('GIT_IGNORE') || define('GIT_IGNORE', '.gitignore');
defined('GIT_ATTRIBUTES') || define('GIT_ATTRIBUTES', '.gitattributes');
defined('GIT_MODULES') || define('GIT_MODULES', '.gitmodules');

// Optional: Add existence check constants if needed in runtime
// define('GIT_EXISTS', is_dir(GIT_FOLDER));
*/
class GitPaths
{
    // Path to the Git executable
    public const BIN = 'git';

    // Git internal folder and contents
    public const FOLDER = '.git';
    public const CONFIG = self::FOLDER . '/config';
    public const HEAD = self::FOLDER . '/HEAD';
    public const INDEX = self::FOLDER . '/index';
    public const OBJECTS = self::FOLDER . '/objects';
    public const REFS = self::FOLDER . '/refs';

    // Git project-level files
    public const IGNORE = '.gitignore';
    public const ATTRIBUTES = '.gitattributes';
    public const MODULES = '.gitmodules';

    // Optional helper: check if .git exists in current directory
    public static function exists(string $path = '.'): bool
    {
        return is_dir($path . DIRECTORY_SEPARATOR . self::FOLDER);
    }
}

//if (GitPaths::exists()) {
//    echo "Git repo found. HEAD is at: " . GitPaths::HEAD;
//}


$gitconfig = <<<END
[safe]
    directory = /mnt/c/www

[user]
    name = <Your Name>
    email = <your.email@example.com>

[core]
    editor = vim
    autocrlf = input

[alias]
    co = checkout
    br = branch
    ci = commit
    st = status

[color]
    ui = auto
END;

if (!is_dir($dirname = (defined('APP_PATH') ? APP_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR) . '.ssh'))
    (@!mkdir($dirname, 0755, true) ?: $errors['APP_BASE'][basename($dirname)] = "$dirname could not be created.");

defined('GIT_EXEC') or define('GIT_EXEC', stripos(PHP_OS, 'WIN') === 0 ? 'git.exe' : $_ENV['GIT']['EXEC'] ?? '/usr/bin/git');
//dd(GIT_EXEC);
if (isset($_ENV['GITHUB']['EXPR_VERSION'])) {
    (function () {
        $gitVersion = exec(GIT_EXEC . ' --version');
        // match will interferer with any included files
        if (preg_match($_ENV['GITHUB']['EXPR_VERSION'], $gitVersion, $match))
            defined('GIT_VERSION') or define('GIT_VERSION', rtrim($match[1], '.'));
    })();
}
/* $latest_remote_commit_response = file_get_contents($latest_remote_commit_url);
$latest_remote_commit_data = json_decode($latest_remote_commit_response, true);
$latest_remote_commit_sha = $latest_remote_commit_data['object']['sha']; */

/**
 * Summary of git_origin_sha_update
 * @return bool|string
 */
function git_origin_sha_update()
{
    global $errors;
    $latest_local_commit_sha = exec(GIT_EXEC . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" rev-parse main');
    $errors['GIT_UPDATE'] = "Local main branch is not up-to-date with origin/main\n";

    //dd('test');

    /*
      $arr = [
        'http' => [
          'method' => 'GET',
          'header' => 'Authorization: token ' . ($_ENV['GITHUB']['OAUTH_TOKEN'] ?? '') . "\r\n" .
            "User-Agent: My-App\r\n",
        ],
      ];

      $context = stream_context_create($arr);
      if ($context == null) {
        error_log("Failed to create stream context.");
        var_dump(error_get_last());
      } else {
        //dd($context);
      }

     file_get_contents($latest_remote_commit_url, false, $context)
    */


    if (isset($_ENV['GITHUB']) && !empty($_ENV['GITHUB']['USERNAME'])) {
        if (!empty($_GET['client']) || !empty($_GET['domain'])) {
            $latest_remote_commit_url = 'https://api.github.com/repos/' . $_ENV['GITHUB']['USERNAME'] . '/' . ($_GET['domain'] ?? $_ENV['DEFAULT_DOMAIN']) . '/git/refs/heads/main'; // commits/main
        } elseif (!empty($_GET['project'])) {
            $path = app_base('projects', null, 'rel') . $_GET['project'] . DIRECTORY_SEPARATOR;
            if (is_dir(APP_PATH . $path)) {
                //define('APP_PROJECT', new clientOrProj($path));
                $latest_remote_commit_url = 'https://api.github.com/repos/' . $_ENV['GITHUB']['USERNAME'] . '/' . $_GET['project'] . '/git/refs/heads/main';
            }
        } else if (isset($_ENV['COMPOSER']) && !empty($_ENV['COMPOSER'])) {
            $latest_remote_commit_url = 'https://api.github.com/repos/' . $_ENV['GITHUB']['USERNAME'] . '/' . $_ENV['COMPOSER']['PACKAGE'] . '/git/refs/heads/main';
        }

        if (isset($_ENV['GITHUB']['OAUTH_TOKEN']) || defined('COMPOSER_AUTH'))
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => 'Authorization: token ' . ($_ENV['GITHUB']['OAUTH_TOKEN'] ?? COMPOSER_AUTH['token']) . "\r\n" .
                        "User-Agent: My-App\r\n",
                ],
            ]);


        //dd($latest_remote_commit_url);

        //dd(check_http_status($latest_remote_commit_url, 404));

        if (APP_IS_ONLINE /*&&check_http_status($_ENV['GIT']['ORIGIN_URL']) &&*/ && !check_http_status($latest_remote_commit_url, 404) && !check_http_status($latest_remote_commit_url, 401)) {

            if ($context === false) {
                error_log("Failed to create stream context.");
                var_dump(error_get_last());
            } else {
                $response = $result = file_get_contents($latest_remote_commit_url, false, $context) ?? '{}';
                if ($result === false) {
                    error_log("Failed to get contents from url.");
                    var_dump(error_get_last());
                } else {
                    $decodedResult = json_decode($result, true);
                    if ($decodedResult === null && json_last_error() !== JSON_ERROR_NONE) {
                        error_log("Failed to decode json.");
                        var_dump(json_last_error_msg());
                    } else {
                        // Process the decoded JSON data
                        //print_r($decodedResult);
                    }
                }
            }
        }
    }

    //dd($errorDetails);

    if (isset($http_response_header) && strpos($http_response_header[0], '401') !== false) {
        $errors['git-unauthorized'] = "[git] You are not authorized. The token may have expired.\n";
    } // elseif (isset($errorDetails['message'])) $errors['other'] = 'An error occurred: ' . $errorDetails['message'];

    //dd($response);

    if (isset($response) && $response !== null) {
        $errors['GIT_UPDATE'] .= "Failed to retrieve commit information.\n";

        $data = json_decode($response, true);
    } else
        $data = null;

    if ($data && isset($data['object']['sha'])) {
        $latest_remote_commit_sha = $data['object']['sha'];

        if ($latest_local_commit_sha !== $latest_remote_commit_sha) {
            $errors[] = 'Remote SHA ($_ENV[\'GITHUB\'][\'REMOTE_SHA\']) was updated.' . "\n" . $errors['GIT_UPDATE'] . "\n";
            $_ENV['GITHUB']['REMOTE_SHA'] = $latest_remote_commit_sha;

        } else {
            $_ENV['GITHUB']['REMOTE_SHA'] = $latest_remote_commit_sha;
            $errors['GIT_UPDATE'] = $errors['GIT_UPDATE'] . $latest_local_commit_sha . '  ' . $latest_remote_commit_sha . "\n";
            $_ENV['DEFAULT_UPDATE_NOTICE'] = false;
            unset($errors['GIT_UPDATE']);
        }
    } else {
        $errors['GIT_UPDATE'] .= "Failed to retrieve commit information.\n";
    }
    $_ENV['DEFAULT_UPDATE_NOTICE'] = false;

    // dd($data);

    return $_ENV['GITHUB']['REMOTE_SHA'] = $latest_local_commit_sha;
}

//git_origin_sha_update();
/*
dd($errors['GIT_UPDATE']);
*/

//dd($latest_remote_commit_url);
if (is_file($file = APP_PATH . APP_ROOT . '.env') && date('Y-m-d', filemtime($file)) == date('Y-m-d')) {
    if (isset($_ENV['GITHUB']['REMOTE_SHA']) && git_origin_sha_update() !== $_ENV['GITHUB']['REMOTE_SHA']) {

    }
}

// file has to exists first
is_dir($path = app_base('var', null, 'abs') ) or mkdir($path, 0755);
if (is_file($path . 'git-scm.com.html')) {
    if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path . 'git-scm.com.html'))))) / 86400)) <= 0) {
        $url = 'https://git-scm.com/downloads';
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        if (!empty($html = curl_exec($handle))) {
            file_put_contents($path . 'git-scm.com.html', $html) or $errors['GIT_LATEST'] = "$url returned empty.";
        }
    }
} else {
    $url = 'https://git-scm.com/downloads';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($html = curl_exec($handle))) {
        file_put_contents($path . 'git-scm.com.html', $html) or $errors['GIT_LATEST'] = "$url returned empty.";
    }
}

libxml_use_internal_errors(true); // Prevent HTML errors from displaying
$doc = new DOMDocument(1.0, 'utf-8');
$doc->loadHTML(file_get_contents($path . 'git-scm.com.html'));

$content_node = $doc->getElementById("main");

$node = getElementsByClass($content_node, 'span', 'version');

//$xpath = new DOMXpath($doc); //$xpath->query('//p [contains (@class, "latest")]');
//dd($xpath);

$pattern = '/(\d+\.\d+\.\d+)/';

if (preg_match($pattern, $node[0]->nodeValue, $matches)) {
    $version = $matches[1];

    defined('GIT_LATEST') or define('GIT_LATEST', $version);
    //echo "New Version: " . GIT_LATEST . "\n";
} else {
    $errors['GIT_LATEST'] = $node[0]->nodeValue . ' did not match $version';
}
//dd(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public');

//if (dirname(APP_SELF) == dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public')
//  if ($path = realpath(dirname(APP_SELF) . DIRECTORY_SEPARATOR . 'ui.git.php')) $app['html'] = require_once $path;

