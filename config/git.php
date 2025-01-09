<?php

global $errors;
/**/
// https://techglimpse.com/git-push-github-token-based-passwordless/
// git push https://<GITHUB_ACCESS_TOKEN>@github.com/<GITHUB_USERNAME>/<REPOSITORY_NAME>.git
if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'constants.php')) {
  require_once $path;
}

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

define('GIT_EXEC', stripos(PHP_OS, 'WIN') === 0 ? 'git.exe' : '/usr/local/bin/git');

if (isset($_ENV['GITHUB']['EXPR_VERSION'])) {
  (function () {
    $gitVersion = exec(GIT_EXEC . ' --version');
    // match will interferer with any included files
    if (preg_match($_ENV['GITHUB']['EXPR_VERSION'], $gitVersion, $match)) {
      define('GIT_VERSION', rtrim($match[1], '.'));
    }
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

  $options = [
    'http' => [
      'method' => 'GET',
      'header' => 'Authorization: token ' . ($_ENV['GITHUB']['OAUTH_TOKEN'] ?? '') . "\r\n" .
        "User-Agent: My-App\r\n",
    ],
  ];

  $context = stream_context_create($options);

  if (!empty($_GET['client']) || !empty($_GET['domain'])) {
    $latest_remote_commit_url = 'https://api.github.com/repos/' . $_ENV['GITHUB']['USERNAME'] . '/' . ($_GET['domain'] ?? $_ENV['DEFAULT_DOMAIN']) . '/git/refs/heads/main';
  } elseif (!empty($_GET['project'])) {
    $path = 'projects' . DIRECTORY_SEPARATOR . $_GET['project'] . DIRECTORY_SEPARATOR;
    if (is_dir(APP_PATH . $path)) {
      define('APP_PROJECT', new clientOrProj($path));
      $latest_remote_commit_url = 'https://api.github.com/repos/' . $_ENV['GITHUB']['USERNAME'] . '/' . $_GET['project'] . '/git/refs/heads/main';
    }
  } else if (isset($_ENV['COMPOSER']) && !empty($_ENV['COMPOSER'])) {
    $latest_remote_commit_url = 'https://api.github.com/repos/' . $_ENV['GITHUB']['USERNAME'] . '/' . $_ENV['COMPOSER']['PACKAGE'] . '/git/refs/heads/main';
  }

  $response = defined('APP_IS_ONLINE') && check_http_status($_ENV['GIT']['ORIGIN_URL']) === true && !check_http_status($latest_remote_commit_url, 404) ? file_get_contents($latest_remote_commit_url, false, $context) : '{}';
  $errorDetails = error_get_last();
  if (isset($http_response_header) && strpos($http_response_header[0], '401') !== false) {
    $errors['git-unauthorized'] = "[git] You are not authorized. The token may have expired.\n";
  } elseif (isset($errorDetails['message'])) {
    $errors['other'] = 'An error occurred: ' . $errorDetails['message'];
  }

  $data = json_decode($response, true);

  if ($data && isset($data['object']['sha'])) {
    $latest_remote_commit_sha = $data['object']['sha'];

    if ($latest_local_commit_sha !== $latest_remote_commit_sha) {
      $errors['GIT_UPDATE'] = $errors['GIT_UPDATE'] . $latest_local_commit_sha . '  ' . $latest_remote_commit_sha . "\n";
    } else {
      $errors[] = 'Remote SHA ($_ENV[\'GITHUB\'][\'REMOTE_SHA\']) was updated.' . "\n" . $errors['GIT_UPDATE'] . "\n";
      $_ENV['GITHUB']['REMOTE_SHA'] = $latest_remote_commit_sha;
      $_ENV['HIDE_UPDATE_NOTICE'] = '';
      unset($errors['GIT_UPDATE']);
    }
  } else {
    $errors['GIT_UPDATE'] .= "Failed to retrieve commit information.\n";
  }
  $_ENV['HIDE_UPDATE_NOTICE'] = '';
  return $_ENV['GITHUB']['REMOTE_SHA'] = $latest_local_commit_sha;
}

//dd($latest_remote_commit_url);
if (is_file($file = APP_PATH . APP_ROOT . '.env') && date('Y-m-d', filemtime($file)) != date('Y-m-d')) {
  if (isset($_ENV['GITHUB']['REMOTE_SHA']) && git_origin_sha_update() !== $_ENV['GITHUB']['REMOTE_SHA']) {
    //
  }
}

// file has to exists first
is_dir(APP_PATH . APP_BASE['var']) or mkdir(APP_PATH . APP_BASE['var'], 0755);
if (is_file(APP_PATH . APP_BASE['var'] . 'git-scm.com.html')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime(APP_PATH . APP_BASE['var'] . 'git-scm.com.html'))))) / 86400)) <= 0) {
    $url = 'https://git-scm.com/downloads';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($html = curl_exec($handle))) {
      file_put_contents(APP_PATH . APP_BASE['var'] . 'git-scm.com.html', $html) or $errors['GIT_LATEST'] = "$url returned empty.";
    }
  }
} else {
  $url = 'https://git-scm.com/downloads';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($html = curl_exec($handle))) {
    file_put_contents(APP_PATH . APP_BASE['var'] . 'git-scm.com.html', $html) or $errors['GIT_LATEST'] = "$url returned empty.";
  }
}
libxml_use_internal_errors(true); // Prevent HTML errors from displaying
$doc = new DOMDocument(1.0, 'utf-8');
$doc->loadHTML(file_get_contents(APP_PATH . APP_BASE['var'] . 'git-scm.com.html'));

$content_node = $doc->getElementById("main");

$node = getElementsByClass($content_node, 'span', 'version');

//$xpath = new DOMXpath($doc); //$xpath->query('//p [contains (@class, "latest")]');
//dd($xpath);

$pattern = '/(\d+\.\d+\.\d+)/';

if (preg_match($pattern, $node[0]->nodeValue, $matches)) {
  $version = $matches[1];

  define('GIT_LATEST', $version);
  //echo "New Version: " . GIT_LATEST . "\n";
} else {
  $errors['GIT_LATEST'] = $node[0]->nodeValue . ' did not match $version';
}
//dd(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public');

//if (dirname(APP_SELF) == dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public')
//  if ($path = realpath(dirname(APP_SELF) . DIRECTORY_SEPARATOR . 'ui.git.php')) $app['html'] = require_once $path;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
  if (isset($_POST['cmd']) && $_POST['cmd'] != '')
    if (preg_match('/^git\s+(:?(.*))/i', $_POST['cmd'], $match)) {
      //(function() use ($path) {
      //  ob_start();
      //require_once APP_PATH . 'config/git.php';

      $command = (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ') . 'git ' . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" commit --allow-empty --dry-run';

      // Append `; echo $?` to capture the exit code in the output
      $shellOutput = shell_exec("$command ; echo $?");

      // Split the output to separate the actual output from the exit code
      $outputLines = explode("\n", trim($shellOutput));
      $exitCode = array_pop($outputLines);  // The last line will be the exit code

      // Reconstruct the output without the exit code
      $commandOutput = implode("\n", $outputLines);

      // Display the result
      //echo "Command Output:\n$commandOutput\n";
      //echo "Exit Code: $exitCode\n";

      if ($exitCode == 128) {
        $proc = proc_open($command, [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

        [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

        if ($exitCode !== 0) {
          if (empty($stdout)) {
            if (!empty($stderr)) {
              $errors["GIT-" . $_POST['cmd']] = $stderr;
              error_log($stderr);
            }
          } else {
            $errors["GIT-" . $_POST['cmd']] = $stdout;
          }
        }
        $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $command")); // $exitCode
      }


      //  ob_end_clean();
      //  return '';
      //})();

      if (preg_match('/^git\s+(help)(:?\s+)?/i', $_POST['cmd'])) {
        $output[] = <<<END
  git reset filename   (unstage a specific file)
  
  git branch
  -m   oldBranch newBranch   (Renaming a git branch)
  -d   Safe deletion
  -D   Forceful deletion
  
  git commit -am "Default message"
  
  git checkout -b branchName
END;
        $output[] = $command = ((stripos(PHP_OS, 'WIN') === 0) ? '' : APP_SUDO) . (defined('GIT_EXEC') ? GIT_EXEC : 'git') . (is_dir($path = APP_PATH . APP_ROOT . '.git') || APP_PATH . APP_ROOT != APP_PATH ? '' : '') . ' ' . $match[1];



        $proc = proc_open(
          $command,
          [
            ["pipe", "r"],
            ["pipe", "w"],
            ["pipe", "w"]
          ],
          $pipes
        );

        [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
        preg_match('/\/(.*)\//', DOMAIN_EXPR, $matches);
        $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : (preg_match("/^To\\s$matches[1]/", $stderr) ? $stderr : "Error: $stderr")) . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");

      } else if (preg_match('/^git\s+(update)(:?\s+)?/i', $_POST['cmd'])) {
        $output[] = git_origin_sha_update();
      } else if (preg_match('/^git\s+(clone)(:?\s+)?/i', $_POST['cmd'])) {

        //$output[] = dd($_POST['cmd']);
        $output[] = 'This works ... ';

        if (preg_match('/^git\s+clone\s+(http(?:s)?:\/\/([^@\s]+)@github\.com\/[\w.-]+\/[\w.-]+\.git)(?:\s*([\w.-]+))?/', $_POST['cmd'], $github_repo)) { // matches with token

          // (?:(?=(.*?[^@\s]+))[^@\s]+@)?

        } else if (preg_match('/^git\s+clone\s+(http(?:s)?:\/\/github\.com\/[\w.-]+\/[\w.-]+\.git)(?:\s*([\w.-]+))?/', $_POST['cmd'], $github_repo)) { // matches without token
          /*
                      if (realpath($github_repo[3])) $output[] = realpath($github_repo[3]);
          
                      //$output[] = dd($github_repo);
                      if (!is_dir('.git')) exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'git init', $output);
          
                      exec('git branch -m master main', $output);
          
                      //exec('git remote add origin ' . $github_repo[2], $output);
                      //...git remote set-url origin http://...@github.com/barrydit/
          
                      exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO)  . 'git config core.sparseCheckout true', $output);
          
                      //touch('.git/info/sparse-checkout');
          
                      file_put_contents('.git/info/sparse-checkout', '*'); /// exec('echo "*" >> .git/info/sparse-checkout', $output);
          
                      exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'git pull origin main', $output);
          
                      //exec(APP_SUDO . ' git init', $output);
                      //$output[] = dd($output);
                    $output[] = 'This works ... ';
          */
        }

        $parsedUrl = parse_url($_ENV['GIT']['ORIGIN_URL']);

        $output[] = $command = $_POST['cmd'] . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" https://' . $_ENV['GITHUB']['OAUTH_TOKEN'] . '@' . $parsedUrl['host'] . $parsedUrl['path'] . '.git';

        /**/

        if (isset($github_repo) && !empty($github_repo)) {

          if (!isset($_SERVER['SOCKET']) || !is_resource($_SERVER['SOCKET']) || empty($_SERVER['SOCKET'])) {

            $proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ') . $command, [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

            [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

            if ($exitCode !== 0) {
              if (empty($stdout)) {
                if (!empty($stderr)) {
                  $errors["GIT-" . $_POST['cmd']] = $stderr;
                  error_log($stderr);
                }
              } else {
                $errors["GIT-" . $_POST['cmd']] = $stdout;
              }
            }

            $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode"));

          } else {
            // Connect to the server
            $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";

            // Send a message to the server
            $errors['server-2'] = 'Client request: ' . $message = "cmd: $command\n";
            /* Known socket  Error / Bug is mis-handled and An established connection was aborted by the software in your host machine */

            fwrite($_SERVER['SOCKET'], $message);

            //$output[] = trim($message) . ': ';
            // Read response from the server
            while (!feof($_SERVER['SOCKET'])) {
              $response = fgets($_SERVER['SOCKET'], 1024);
              $errors['server-3'] = "Server responce: $response\n";
              if (isset($output[end($output)]))
                $output[end($output)] .= $response = trim("$response");
              //if (!empty($response)) break;
            }

            // Close and reopen socket
            fclose($_SERVER['SOCKET']);

          }

        }

        // exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO)  . 'git --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" remote add origin ' . $github_repo[2], $output);

      }

      $command = (stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO . '-u www-data ') . 'git ' . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" ' . $match[1];

      $output[] = 'Path: ' . APP_PATH . APP_ROOT;

      $output[] = shell_exec("$command ; echo $?");
      
if (isset($output) && is_array($output)) {
  switch (count($output)) {
    case 1:
      echo /*(isset($match[1]) ? $match[1] : 'PHP') . ' >>> ' . */ join("\n... <<< ", $output);
      break;
    default:
      echo join("\n", $output);
      break;
  }

  Shutdown::setEnabled(true)->setShutdownMessage(function () { })->shutdown();
  //dd('test');
  // . "\n"
  //$output[] = 'post: ' . var_dump($_POST);
  //else var_dump(get_class_methods($repo));
}
      
    }

//dd($output);


if (APP_SELF == __FILE__ || (defined('APP_DEBUG') && isset($_GET['app']) && $_GET['app'] == 'git')) {
  // die($app['html']);
}