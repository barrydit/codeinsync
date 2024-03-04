<?php
/**/
// https://techglimpse.com/git-push-github-token-based-passwordless/
// git push https://<GITHUB_ACCESS_TOKEN>@github.com/<GITHUB_USERNAME>/<REPOSITORY_NAME>.git

if ($path = realpath((basename(__DIR__) != 'config' ? NULL : __DIR__ . DIRECTORY_SEPARATOR) . 'constants.php')) // is_file('config/constants.php')) 
  require_once($path);

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
  define('GIT_EXEC', 'git.exe');
else
  define('GIT_EXEC', '/usr/bin/git');

define('GIT_VERSION', (preg_match("/(?:version|v)\s*((?:[0-9]+\.?)+)/i", exec('git --version'), $match) ? rtrim($match[1], '.') : ''));

// file has to exists first
is_dir(APP_PATH . APP_BASE['var']) or mkdir(APP_PATH . APP_BASE['var'], 0755);
if (is_file(APP_PATH . APP_BASE['var'] . 'git-scm.com.html')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime(APP_PATH . APP_BASE['var'] . 'git-scm.com.html'))))) / 86400)) <= 0 ) {
    $url = 'https://git-scm.com/downloads';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($html = curl_exec($handle))) 
      file_put_contents(APP_PATH . APP_BASE['var'] . 'git-scm.com.html', $html) or $errors['GIT_LATEST'] = $url . ' returned empty.';
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
    require_once($path);

if (APP_SELF == __FILE__ || defined(APP_DEBUG) && isset($_GET['app']) && $_GET['app'] == 'git')
  die($appGit['html']);
