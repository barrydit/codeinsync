<?php
/**/
// https://techglimpse.com/git-push-github-token-based-passwordless/
// git push https://<GITHUB_ACCESS_TOKEN>@github.com/<GITHUB_USERNAME>/<REPOSITORY_NAME>.git

if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'constants.php')) // is_file('config/constants.php')) 
  require_once $path;

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
  define('GIT_EXEC', 'git.exe');
else
  define('GIT_EXEC', '/usr/local/bin/git');

define('GIT_VERSION', preg_match("/(?:version|v)\s*((?:[0-9]+\.?)+)/i", exec(GIT_EXEC . ' --version'), $match) ? rtrim($match[1], '.') : '');

/* $latest_remote_commit_response = file_get_contents($latest_remote_commit_url);
$latest_remote_commit_data = json_decode($latest_remote_commit_response, true);
$latest_remote_commit_sha = $latest_remote_commit_data['object']['sha']; */

function git_origin_sha_update() {
  global $errors;
  $latest_local_commit_sha = exec(GIT_EXEC . ' --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . dirname(__DIR__) . '" rev-parse main');
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

    $latest_remote_commit_url = 'https://api.github.com/repos/barrydit/' . $_GET['domain'] . '/git/refs/heads/main';

  //if (isset($_GET['path']) && is_dir(APP_PATH . $path . $_GET['path'])) $path .= $_GET['path'];
    //else 
      //exit(header('Location: http://localhost/clientele/' . $_GET['client']));    
    //$path = '?path=' . $path;

  } elseif (!empty($_GET['project'])) {
    $path = 'projects' . DIRECTORY_SEPARATOR . $_GET['project'] . DIRECTORY_SEPARATOR;   
  //$dirs = array_filter(glob(dirname(__DIR__) . '/projects/' . $_GET['project'] . '/*'), 'is_dir');
  
  if (is_dir(APP_PATH . $path)) {
    define('APP_PROJECT', new clientOrProj($path));

    $latest_remote_commit_url = 'https://api.github.com/repos/barrydit/' . $_GET['project'] . '/git/refs/heads/main';
  }
  //if (isset($_GET['path']) && is_dir(APP_PATH . $path . $_GET['path'])) $path .= $_GET['path'];

} else {
  if (isset($_ENV['COMPOSER']) && !empty($_ENV['COMPOSER'])) {
    $latest_remote_commit_url = 'https://api.github.com/repos/barrydit/' . $_ENV['COMPOSER']['PACKAGE'] . '/git/refs/heads/main'; // commits/main | sha
  }
}

$response = (defined('APP_CONNECTED') || check_http_200($_ENV['GITHUB']['ORIGIN_URL']) ? file_get_contents($latest_remote_commit_url, false, $context): '{}' );
  $errorDetails = error_get_last();
  if (isset($http_response_header) && strpos($http_response_header[0], '401') !== false) {
    $errors['git-unauthorized'] = 'You are not authorized. The token may have expired.';
  } elseif (isset($errorDetails['message'])) {
    $errors['other'] = 'An error occurred: ' . $errorDetails['message'];
  }
  //throw new Exception('Error fetching data from the URL.');

  // Make the request
  //$response = defined('APP_CONNECTED') || check_http_200($_ENV['GITHUB']['ORIGIN_URL']) ? file_get_contents($latest_remote_commit_url, false, $context) : '{}';
  $data = json_decode($response, true);

  //$errors['GIT_STATUS'] = var_dump($data);
  if ($data && isset($data['object']['sha'])) {
    $latest_remote_commit_sha = $data['object']['sha'];

    // Compare the two commit SHAs
    if ($latest_local_commit_sha !== $latest_remote_commit_sha) {
      $errors['GIT_UPDATE'] = $errors['GIT_UPDATE'] . $latest_local_commit_sha . '  ' . $latest_remote_commit_sha  . "\n"; 
    } else {
      // $_ENV['HIDE_UPDATE_NOTICE'] = var_export(false, true);
      $errors[] = 'Remote SHA ($_ENV[\'GITHUB\'][\'REMOTE_SHA\']) was updated.' . "\n" . $errors['GIT_UPDATE'] . "\n";
      $_ENV['GITHUB']['REMOTE_SHA'] = $latest_remote_commit_sha;
      unset($errors['GIT_UPDATE']);
    }
  } else {
    $errors['GIT_UPDATE'] .= "Failed to retrieve commit information.\n";
  }
  return ($_ENV['GITHUB']['REMOTE_SHA'] = $latest_local_commit_sha);
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
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime(APP_PATH . APP_BASE['var'] . 'git-scm.com.html'))))) / 86400)) <= 0 ) {
    $url = 'https://git-scm.com/downloads';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($html = curl_exec($handle))) 
      file_put_contents(APP_PATH . APP_BASE['var'] . 'git-scm.com.html', $html) or $errors['GIT_LATEST'] = "$url returned empty.";
  }
} else {
  $url = 'https://git-scm.com/downloads';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($html = curl_exec($handle))) 
    file_put_contents(APP_PATH . APP_BASE['var'] . 'git-scm.com.html', $html) or $errors['GIT_LATEST'] = $url . ' returned empty.';
}
libxml_use_internal_errors(true); // Prevent HTML errors from displaying
$doc = new DOMDocument(1.0, 'utf-8');
$doc->loadHTML(file_get_contents(APP_PATH . APP_BASE['var'] . 'git-scm.com.html'));

$content_node=$doc->getElementById("main");

$node=getElementsByClass($content_node, 'span', 'version');

//$xpath = new DOMXpath ( $doc ); //$xpath->query ( '//p [contains (@class, "latest")]' );
//dd($xpath);

$pattern = '/(\d+\.\d+\.\d+)/';

if (preg_match($pattern, $node[0]->nodeValue, $matches)) {
  $version = $matches[1];

  define('GIT_LATEST', $version);
  //echo "New Version: " . GIT_LATEST . "\n";
} else $errors['GIT_LATEST'] = $node[0]->nodeValue . ' did not match $version';


if (basename(dirname(APP_SELF)) == __DIR__ . DIRECTORY_SEPARATOR . 'public')
  if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'ui.git.php')) // is_file('public/git_app.php')) 
    require_once $path;

if (APP_SELF == __FILE__ || defined(APP_DEBUG) && isset($_GET['app']) && $_GET['app'] == 'git')
  die($appGit['html']);
