<?php
// /app/devtools/directory.php
// A simple directory listing and navigation tool for development environments.
// Usage: http://localhost/?path=clients/123-domain.com

global $errors;

// ---- helpers --------------------------------------------------------------

/**
 * Small utility class for path normalization / query building.
 */


// --- helpers (soft fallbacks if you already have them elsewhere)
if (!function_exists('get_str')) {
  function get_str(string $k, $default = null)
  {
    return isset($_GET[$k]) ? (string) $_GET[$k] : $default;
  }
}
if (!function_exists('base_val')) {
  // expects APP_BASE[...] available (env/constants). Fallback to defaults.
  function base_val(string $key): string
  {
    $base = defined('APP_BASE') ? APP_BASE : ($_ENV['APP_BASE'] ?? []);
    $v = $base[$key] ?? ($key === 'clients' ? 'clients/' : ($key === 'projects' ? 'projects/' : ''));
    // ensure trailing slash once, allow "../clients/"
    $v = rtrim(str_replace('\\', '/', $v), '/') . '/';
    return $v;
  }
}

function isDomainName(string $name): bool
{
  // Trim whitespace and trailing dot
  $name = trim($name, " \t\n\r\0\x0B.");

  // Reject client folders like "000-Whatever"
  if (preg_match('/^\d{3}-/', $name))
    return false;

  // RFC-ish domain (allows subdomains and punycode, TLD up to 63)
  return (bool) preg_match(
    '/^(?=.{1,253}$)(?:xn--)?[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?' .
    '(?:\.(?:xn--)?[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+$/i',
    $name
  );
}

/**
 * Build a query string by cloning $_GET, unsetting some keys, and setting others.
 */
function build_query_href(array $GET, array $set, array $unset = []): string
{
  $q = $GET;
  foreach ($unset as $k)
    unset($q[$k]);
  foreach ($set as $k => $v)
    $q[$k] = $v;
  return '?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986);
}


// defined('APP_BASE') or require_once APP_PATH . 'config/constants.paths.php';
// defined('APP_URL_BASE') or require_once APP_PATH . 'config/constants.url.php';

// require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php';

//require_once APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php';
//require_once APP_PATH . APP_ROOT . 'app' . DIRECTORY_SEPARATOR . 'composer.php';

// !isset($_GET['path']) and $_GET['path'] = '';

//namespace App\Directory;

if (preg_match('/^([\w\-.]+)\.php$/', basename(__FILE__), $matches))
  ${$matches[1]} = $matches[1];





/*
// ── Browsing roots (UI listing) ───────────────────────────────────────────────
// Always browse from the context root you computed earlier in 3c
$BROWSE_ROOT = rtrim($ctxRoot, '/\\') . '/';

// Long-term correct browse dir:
$absDir = rtrim($BROWSE_ROOT . $PATH_SUB, '/\\') . '/';

// TEMPORARY root-only fallback (your A) quick fix). Uncomment if you need it:
// $absDir = $BROWSE_ROOT;

// Existence check for UI
$exists = is_dir($absDir);

// Optional: complete installer path when you need it
$COMPLETE_PATH_INSTALL = rtrim(APP_PATH, '/\\') . '/' . APP_ROOT . APP_ROOT_DIR;
// Examples:
//   $vendorDir = $COMPLETE_PATH_INSTALL;              // if APP_ROOT_DIR == 'vendor/'
//   $nodeDir   = rtrim(APP_PATH, '/\\') . '/' . APP_ROOT . 'node_modules/';

// Optional label for display:
$label = ($context === 'clients-base') ? 'clients'
  : (($context === 'projects-base') ? 'projects' : $context);
*/

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

  //if (isset($_POST['cmd']) && $_POST['cmd'] != '')
  // require_once 'app.console.php';

  if (isset($_GET['app']) && $_GET['app'] == 'ace_editor')
    require_once 'ui.ace_editor.php';

  if (isset($_POST['cmd'])) {

    chdir(APP_PATH . APP_ROOT);

    $output = [];

    //$GLOBALS['runtime']['socket'] = fsockopen(SERVER_HOST, SERVER_PORT, $errno, $errstr, 5);

    if ($_POST['cmd'] && $_POST['cmd'] != '')
      if (preg_match('/^chdir\s+(:?(.*))/i', $_POST['cmd'], $match)) {
        //exec($_POST['cmd'], $output);
        //die(header('Location: ' . APP_URL_BASE . '?app=text_editor&filename='.$_POST['cmd']));
        //$output[] = "Changing directory to " . $path;

        //$output[] = var_dump(get_required_files()); //'Location: ' . APP_PATH . APP_ROOT . rtrim(trim(preg_match('#(?:\.\./)+#', $match[1]) ? '/' : $match[1]), DIRECTORY_SEPARATOR);

        /**/
        error_log("Path: $match[1]");

        if (realpath($path = APP_PATH . APP_ROOT . rtrim(trim(preg_match('#(?:\.\./)+#', $match[1]) ? '/' : $match[1]), DIRECTORY_SEPARATOR))) {
          // Define the root directory you don't want to go past

          // Regular expression to normalize the path
          $match[1] = preg_replace('#(?:\.\./)+#', '../', $match[1]);

          // Optional: Ensure path does not start with excessive "../"
          //$maxUpLevels = 1; // Adjust as needed
          //$match[1] = preg_replace('#^((?:\.\./){' . ($maxUpLevels + 1) . ',})#', str_repeat('../', $maxUpLevels), $filteredPath);
          $rootDir = realpath(APP_PATH . APP_ROOT . ($match[1] ?? '../'));

          // Check if the resolved path is within the allowed root
          if (strpos($path, $rootDir) === 0 && strlen($path) >= strlen($rootDir)) {
            // Proceed with your existing logic if the path is valid
            /* $resultValue = (function () use ($path, $tableGen): string{
              defined('APP_CLIENT') ? '' : define('APP_CLIENT', $path);

              $basePath = rtrim(APP_PATH . APP_ROOT, DIRECTORY_SEPARATOR);

              $path = preg_replace('#^' . preg_quote($basePath, '#') . '/?#', '', $path);

              // Replace the escaped APP_PATH and APP_ROOT with the actual directory path
              if (realpath(preg_replace('#^' . preg_quote($basePath, '#') . '/?#', '', $path)) == realpath($basePath)) {
                $_GET['path'] = '';
              } elseif (
                realpath(
                  $newPath = preg_replace(
                    '#^' . preg_quote(rtrim(APP_PATH . APP_ROOT . ($_GET['domain'] ?? ''), DIRECTORY_SEPARATOR), '#') . '/?#',
                    '',
                    $path
                  )
                )
              ) {
                ($newPath === '../' ? $_GET['path'] = '' : $_GET['path'] = "$newPath/");  ///  preg_replace('/' . preg_quote(APP_PATH . APP_ROOT, DIRECTORY_SEPARATOR) . '/', '', $path)
              } else {
                $_GET['path'] = '';
              }
              //$_GET['path'] =  . '>>' . APP_CLIENT . ($_GET['domain'] ?? '');
              //dd(get_required_files(), false);
              ob_start();
              //if (is_file($include = APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php'))
              //if (isset($_ENV['COMPOSER']['AUTOLOAD']) && (bool) $_ENV['COMPOSER']['AUTOLOAD'] === TRUE)
              //  require_once $include;

              isset($tableGen) and $tableValue = $tableGen();
              ob_end_clean();
              return $tableValue ?? ''; // $app['directory']['body'];
            })(); */
            $output[] = (string) null; // $resultValue;
            //die();
          } else {
            // Handle the case where the path is trying to go past the root directory
            $output[] = "Cannot go past the root directory: $rootDir >> $path";
          }
        }
        /*
            if ($path = realpath(APP_PATH . APP_ROOT . rtrim(trim($match[1]), '/'))) {
              // Define the root directory you don't want to go past
              $root_dir = realpath(APP_PATH . APP_ROOT);

              // Resolve the parent directory path
              $parent_dir = realpath("$path/../");

              // Check if the parent directory is within the allowed root
              if (strpos($parent_dir, $root_dir) === 0 && strlen($parent_dir) >= strlen($root_dir)) {
                // Proceed with your existing logic if the path is valid
                $resultValue = (function() use ($path): string {
                  // Replace the escaped APP_PATH and APP_ROOT with the actual directory path
                  if (realpath($_GET['path'] = preg_replace('/' . preg_quote(APP_PATH . APP_ROOT, '/') . '/', '', $path)) == realpath(APP_PATH . APP_ROOT))
                    $_GET['path'] = '';
                  ob_start();
                  require 'app/directory.php';
                  $tableValue = $tableGen();
                  ob_end_clean();
                  return $tableValue; // $app['directory']['body'];
                })();
                $output[] = (string) $resultValue;
              } else {
                // Handle the case where the path is trying to go past the root directory
                $output[] = "Cannot go past the root directory: $root_dir";
              }
            }
        */
      } else if (preg_match('/^edit\s+(:?(.*))/i', $_POST['cmd'], $match)) {
        //exec($_POST['cmd'], $output);
        //die(header('Location: ' . APP_URL_BASE . '?app=text_editor&filename='.$_POST['cmd']));

        //$output[] = 'This works ... ' . dd(get_required_files(), false) . dd($_POST, false) . APP_PATH . APP_ROOT; // APP_ROOT; 

        // . DIRECTORY_SEPARATOR . ($_GET['domain'] ?? '')
        // . DIRECTORY_SEPARATOR . ($_GET['path'] ?? '')

        //(isset($_GET['path']) ? (empty($_GET['client']) ? '' : APP_BASE['clients'] . (isset($_GET['client']) || isset($_GET['domain']) ? /* APP_BASE['clients'] */ (!isset($_GET['client']) ? '' : $_GET['client']) . DIRECTORY_SEPARATOR : '') . (!isset($_GET['domain']) ? '' : $_GET['domain'])) /*APP_BASE[''] . $_GET['client'] . '/'*/ : APP_ROOT  ) . 

        $rootFilter = '';
        $filePath = APP_PATH;

        // Determine the root filter based on client and domain
        if (!empty($_GET['client'])) {
          $rootFilter = app_base('clients', null, 'rel') . $_GET['client'] . DIRECTORY_SEPARATOR;
          if (isset($_GET['domain'])) {
            $rootFilter .= $_GET['domain'] . DIRECTORY_SEPARATOR;
          }
        } elseif (isset($_GET['domain'])) {
          $rootFilter = app_base('clients', null, 'rel') . $_GET['domain'] . DIRECTORY_SEPARATOR;
        }

        // Add project-specific root filter if applicable
        if (isset($_GET['project'])) {
          $rootFilter = app_base('projects', null, 'rel') . $_GET['project'] . DIRECTORY_SEPARATOR;
        }

        // Add path to the file path
        if (isset($_GET['path'])) {
          $filePath .= $rootFilter . $_GET['path'] . DIRECTORY_SEPARATOR;
        } else {
          $filePath .= $rootFilter;
        }

        // Trim and clean the match path
        $matchPath = preg_replace('#^' . preg_quote($rootFilter, '#') . '/?#', '', $match[1] ?? '');
        $filePath .= trim($matchPath);

        // Check if the file exists and read its content
        $output[] = (is_file($filePath)) ? file_get_contents($filePath) : "File not found: $filePath";



        //$root_filter = '';
        //$output[] = is_file($file = APP_PATH . (empty($_GET['client']) ? '' : $root_filter = APP_BASE['clients'] . (isset($_GET['client']) || isset($_GET['domain']) ? /* APP_BASE['clients'] */ (!isset($_GET['client']) ? '' : $_GET['client']) . DIRECTORY_SEPARATOR : '') . (!isset($_GET['domain']) ? '' : $_GET['domain'])) . DIRECTORY_SEPARATOR . (!isset($_GET['path']) ? (!isset($_GET['project']) ? '' : $root_filter = APP_BASE['projects'] . $_GET['project']) : $_GET['path']) . DIRECTORY_SEPARATOR . trim(preg_replace('#^' . preg_quote($root_filter, '#') . '/?#', '', $match[1]))) ? file_get_contents($file) : "File not found: $file";
      }
    if (isset($output) && is_array($output)) {
      switch (count($output)) {
        case 1:
          echo /*(isset($match[1]) ? $match[1] : 'PHP') . ' >>> ' . */ join("\n... <<< ", $output);
          break;
        default:
          echo join("\n", $output);
          break;
      } // . "\n"
      //$output[] = 'post: ' . var_dump($_POST);
      //else var_dump(get_class_methods($repo));
    }
    $output = [];
    //echo $buffer;
    unset($match);
    //require_once /*APP_BASE['app'] .*/ 'console.php';
  }

  if (!isset($_POST['group_type']))
    Shutdown::setEnabled(true)->setShutdownMessage(function () {
      return ''; // 'The application has been terminated.';  
    })->shutdown();
}



//dd($directory, true);

// $style = file_get_contents(__DIR__ . '/devtools.directory.css'); // put your CSS here
if (false) { ?>
  <style>
  <?php }
ob_start(); ?>
  #app_directory-container {
    position: absolute;
    height: auto;
    width: 100%;
    /* display: none; */
    left: 15px;
    top: 15px;
    /* z-index: 99; */
  }

  .directory-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 10px;
  }

  .directory-entry {
    flex: 1 0 10%;
    /* roughly 7 items per row at full width */
    max-width: 115px;
    text-align: center;
    border: 0;
    padding: 4px;
    box-sizing: border-box;
  }

  .directory-entry img {
    display: block;
    margin: 0 auto 4px auto;
    width: 40px;
    height: auto;
  }

  <?php $UI_APP['style'] = ob_get_contents();
  ob_end_clean();
  if (false) { ?>
  </style><?php }

  // $style = preg_replace('~^\s*<style[^>]*>|</style>\s*$~i', '', ob_get_clean());
  /**
   * Generates a table.
   *
   * @return string
   */
  $group_type = $_POST['group_type'] ?? null;
  //$tableGen = function () use ($group_type): string {
  
  // ---- render UI -------------------------------------------------------------
/*

if (false) { ?>

  <body>
  <?php }
ob_start();

// If missing, you can show your "Missing directory" notice as before:
if (!$exists) {
  echo '<br><br>Missing directory: ' . htmlspecialchars($absDir);
} else
  echo $context . ' : ' . htmlspecialchars(APP_PATH . APP_ROOT) . '<br><br>';
//  var_dump([
//    'has' => compact('hasClient', 'hasDomain', 'hasProject', 'hasPath', 'hasNonEmptyPath'),
//    'vals' => compact('client', 'domain', 'project', 'path')
//  ]);

//dd($_GET, false); // { "app": "devtools\/directory", "json": "1" } 

$UI_APP['body'] = ob_get_contents();
ob_end_clean();
if (false) { ?>
  </body><?php }
// From here, use $absDir to render your directory grid.
// You can also branch on $context to decide which “listing” (clients, projects, etc.) UI to show.

return $UI_APP; */

  //ob_start();
  
  //$returnValue = ob_get_contents();
  //ob_end_clean();
  //return $returnValue;
  //  };
  
  /*ob_start(); ?>

  <?php $app['style'] = ob_get_contents();
  ob_end_clean();  */

  ob_start();
  //dd(get_required_files(), false); ?>
<div style="position: fixed; z-index: 50; left: 0px; top: 0px;">
  <div
    style="position: absolute; top: -10px; left: 0px; width: 395px; z-index: 50; background-color: rgb(250, 250, 250); border: 1px solid black; box-shadow: rgba(0, 0, 0, 0.5) 0px 0px 10px; border-radius: 5px; padding: 3px;">
    <form action="" method="GET" style="display: inline; margin: 0;">
      <input type="hidden" name="path" value="" />
      <button id="displayDirectoryBtn" style="margin: 2px 5px 0 0; border: 3px dashed red;" type=""
        onclick="this.form.submit();"><img src="resources/images/directory-www.fw.png" width="18" height="10"
          style="vertical-align: middle;">&nbsp;&#9650;</button>
    </form>
    <div style="display: inline; margin-top: -3px;"><a style="font-size: 18pt; font-weight: bold; padding: 0 3px 0 0 ;"
        href="/">&#8962;</a></div>
    <form style="display: inline;" autocomplete="off" spellcheck="false" action="" method="GET">/
      <select name="category" onchange="this.form.submit();">
        <option value=""></option>
        <option value="application">applications</option>
        <option value="client">clients</option>
        <option value="projects">projects</option>
        <option value="node_module">./node_modules</option>
        <option value="resources">./resources</option>
        <option value="project">./project</option>
        <option value="vendor">./vendor</option>
      </select>
    </form>
    <form style="display: inline;" action="" method="GET">
      <span title="/mnt/c/www/" style="margin: 2px 5px 0 0; cursor: pointer;" onclick=""> /
        <select name="path" style="" onchange="this.form.submit(); return false;">
          <option value="">.</option>
          <option value="">..</option>
          <option value="/applications">applications/</option>
          <option value="/bin">bin/</option>
          <option value="/clients">clients/</option>
          <option value="/config">config/</option>
          <option value="/data">data/</option>
          <option value="/dist">dist/</option>
          <option value="/docs">docs/</option>
          <option value="/node_modules">node_modules/</option>
          <option value="/projects">projects/</option>
          <option value="/public">public/</option>
          <option value="/resources">resources/</option>
          <option value="/src">src/</option>
          <option value="/tests">tests/</option>
          <option value="/var">var/</option>
          <option value="/vendor">vendor/</option>
        </select> / <a href="#" onclick="document.getElementById('info').style.display = 'block';">+</a>
      </span>
    </form>

  </div>
</div>
<?php /* <div id="app_directory-container" style="
position: absolute;
display: <?= isset($_GET['debug']) || isset($_GET['project']) || isset($_GET['path']) ? 'block' : 'block'; ?>;
top: 10px;
left: 0;
right: 0;
margin: 0 auto;
background-color: rgba(255, 255, 255, 0.1);
// height: auto;
width: 100%;
max-height: 80vh;
overflow-y: auto;
overflow-x: hidden;
resize: vertical;
">...</div>*/ ?>

<div id="info"
  style="position: fixed; display: none; width: 570px; height: 500px; top: calc(50% - 300px); /* 500 / 2 */
left: calc(50% - 265px); /* 1207 / 2 */ /*transform: translate(-50%, -50%);*/ border: 5px solid #000; background-repeat: no-repeat; background-color: #FFFFFF; z-index:99;">
  <div
    style="position: absolute; display: block; background-color: #FFFFFF; z-index: 1; right: 0px; margin-top: -20px;">
    [<a href="#" onclick="document.getElementById('info').style.display = 'none';">x</a>]</div>
  <form method="post" action="/?path" enctype="multipart/form-data">
    <div class="directory-grid" data-app-path="devtools/directory">
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/new_file.png" width="58" height="69" />
            New File</a>
        </div>
      </div>
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/git_clone.png" width="69" height="69" />
            Git<br>(clone)</a>
        </div>
      </div>
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/ftp_conn.png" width="82" height="71" />
            FTP</a>
        </div>
      </div>
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/www_curl.png" width="75" height="81" />
            www<br>(curl)</a>
        </div>
      </div>
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/clients.png" width="74" height="79" />
            Clients</a>
        </div>
      </div>
      <div class="directory-entry">
        <div style="position: relative;">
          <a href="#!" onclick="handleClick(event, '../');">
            <img src="resources/images/projects.png" width="74" height="79" />
            Projects</a>
        </div>
      </div>
    </div>

  </form>
</div>

<?php
//$path = APP_PATH . APP_ROOT . ($_GET['path'] ?? '');
//dd($_GET);
// $context = $context ?? 'app'; // fallback
/* ---------- inputs ---------- */
$base = rtrim(APP_PATH, '/'); // e.g. /mnt/c/www
$root = defined('APP_ROOT') && APP_ROOT == '' ? trim(APP_ROOT, '/') : '';
$client = $_GET['client'] ?? '';
$domain = $_GET['domain'] ?? '';
$project = $_GET['project'] ?? '';
$path = $_GET['path'] ?? '';
$visiblePath = PathUtils::norm($path); // path shown in UI
$segments = [];
$parent = PathUtils::parentPath($visiblePath);

/* ---------- segment: APP_PATH (always) ---------- */
$segments[] = (APP_ROOT !== '') ? sprintf(
  '[ ' . ($visiblePath !== '' ? '<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s/</a>'
    : '<a href="/">%s/</a>') . ' ]',
  htmlspecialchars($base)
)
  : sprintf(
    '[ <a href="/">%s</a>' . ($visiblePath !== '' ? '/' . (!in_array(['client', 'domain', 'project'], $_GET) ? '<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">%s</a>' : '') : '/') . ' ]',
    htmlspecialchars($base),
    htmlspecialchars($parent, ENT_QUOTES),
    htmlspecialchars(rtrim($visiblePath, '/') . '/')
  );

/* ---------- segment: Context (APP_ROOT or explicit client/project/domain) ---------- */
if ($root !== '') {
  [$cFromRoot, $dFromRoot] = PathUtils::clientDomainFromRoot($root);
  $ctxLabel = trim(($cFromRoot ? $cFromRoot . '/' : '') . ($dFromRoot ? $dFromRoot . '/' : ''), '/');
  if ($ctxLabel === '') {
    $parts = explode('/', PathUtils::norm($root));
    $ctxLabel = implode('/', array_slice($parts, max(0, count($parts) - 2))) . '/';
  } else {
    $ctxLabel .= '/';
  }
  // click => context root (empty path)
  $segments[] = sprintf(
    ' [ <a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>' . ($visiblePath !== '' ? '<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">%s/</a>' : '') . ' ]',
    htmlspecialchars($ctxLabel),
    htmlspecialchars($parent, ENT_QUOTES),
    htmlspecialchars(rtrim($visiblePath, '/'))
  );
} //else {
if ($project && !$client && !$domain) {
  $segments[] = sprintf(
    ' [ <a href="/?project" style="font-weight:bold">Project:</a> <a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s/</a>' . ($visiblePath !== '' ? '<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">%s/</a>' : '') . ' ]',
    //htmlspecialchars(rtrim((string) (APP_BASE['projects'] ?? 'projects/'), '/')),
    htmlspecialchars(rtrim($project, '/')),
    htmlspecialchars($parent, ENT_QUOTES),
    htmlspecialchars(rtrim($visiblePath, '/'))
  );
} elseif ($client || $domain) {
  /* $segments[] = sprintf(
    ' [ ' . ($domain && !$client ? '<a href="?domain">Domain:</a> ' : '<a href="?client">Client:</a> <a href="?client=%s" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>' . (!$domain ? '' : ' Domain: ')) . '<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>' . ($visiblePath !== '' ? '/<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">%s/</a>' : '') . ' ]',
    $domain && !$client ? htmlspecialchars($domain) : htmlspecialchars($client),
    $domain && !$client ? htmlspecialchars($domain) : htmlspecialchars($client), //APP_BASE['clients'] ?? 'clients/'
    htmlspecialchars($visiblePath),
    htmlspecialchars($parent, ENT_QUOTES),
    htmlspecialchars(rtrim($visiblePath, '/'))
  ); */

  $fmtParts = [];
  $args = [];

  $fmtParts[] = ' [ ';

  /* Left chunk: "Domain:" OR "Client:" (+ optional Domain:) */
  if ($domain !== '' && $client === '') {
    // Domain-only
    $fmtParts[] = '<a href="?domain" style="font-weight:bold">Domain:</a> ';
    $fmtParts[] = '<a href="?domain=%s" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>';
    $args[] = rawurlencode($domain);                 // query value
    $args[] = htmlspecialchars(rtrim($domain, '/'));             // link label

  } else {
    // Client first (if present)
    $fmtParts[] = '<a href="?client" style="font-weight:bold">Client:</a> ';
    if ($client !== '') {
      $fmtParts[] = '<a href="?client=%s" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>';
      $args[] = rawurlencode($client);
      $args[] = htmlspecialchars(rtrim($client, '/'));
    }

    // Optional Domain after client
    if ($domain !== '') {
      $fmtParts[] = ' <a href="?' . ($client === '' ? '' : "client=$client&") . 'domain" style="font-weight:bold">Domain:</a> ';
      $fmtParts[] = '<a href="?' . ($client === '' ? 'domain' : "client=$client&" . 'domain=' . rawurlencode($domain)) . '" onclick="return App[\'devtools/directory\'].handleClick(\'\')">%s</a>';
      $args[] = htmlspecialchars(rtrim($domain, '/'));
    }
  }

  /* Tail: current path + up (only if visiblePath set) */
  if ($visiblePath !== '') {
    $fmtParts[] = '/<a href="#!" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">%s/</a>';
    $args[] = htmlspecialchars($parent, ENT_QUOTES);               // onclick('…')
    $args[] = htmlspecialchars(rtrim($visiblePath, '/'));          // label
  }

  $fmtParts[] = ' ]';

  $segments[] = vsprintf(implode('', $fmtParts), $args);
}
//}

/* ---------- segment: current path with quick "up" ---------- */
if ($visiblePath !== '') {
  $parent = PathUtils::parentPath($visiblePath);
  $segments[] = sprintf(' <a href="#!" title="Up one level" onclick="return App[\'devtools/directory\'].handleClick(\'%s\')">&#9664; up</a> ', htmlspecialchars($parent, ENT_QUOTES));
}

/* ---------- render ---------- */
echo '<div id="breadcrumb" style="height:25px;display:inline;"><br><br>'
  . implode('', $segments)
  . '</div>';

/* ---------- existence check (kept) ---------- */
$exists = (bool) realpath($absDir ?? '');
if (!$exists) {
  echo 'Missing directory: ' . htmlspecialchars($absDir ?? '');
}

// dd("APP_PATH = " . APP_PATH . '  APP_ROOT = ' . APP_ROOT . '  APP_ROOT_DIR = ' . APP_ROOT_DIR, false);

// ---- existence check ------------------------------------------------------
switch (ctx('context')) {
  case 'clients-base':
    // dd("$context : $absDir | APP_ROOT=" . APP_ROOT, false);
    ?>
    <h3>&#9660; Domains: </h3>
    <table style="border:none;">
      <tr style="border:none;">
        <?php
        $count = 1;
        $links = array_filter(
          glob(APP_PATH . APP_BASE['clients'] . '*', GLOB_ONLYDIR),
          fn($link) => preg_match('/^(?!\d{3}-)[a-z0-9\-]+\.[a-z]{2,6}$/i', basename($link))
        );
        $old_links = $links;
        while ($link = array_shift($links)) {
          $old_link = $link;
          $link = basename($link);
          echo "<td style=\"text-align:center;border:none;\" class=\"text-xs\">
                  <a class=\"pkg_dir\" href=\"?" . (isset($_ENV['DEFAULT_CLIENT']) && $_ENV['DEFAULT_CLIENT'] == $link ? '' : "domain=$link") . "\">
                  <img src=\"resources/images/directory.png\" width=\"50\" height=\"32\" />
                  <br />$link/</a><br />
                </td>";
          if ($count >= 6)
            echo '</tr><tr>';
          elseif ($old_link == end($old_links))
            echo '</tr>';
          $count = ($count >= 6) ? 1 : $count + 1;
        }
        ?>
    </table>
    <?php
    foreach (['000', '100', '200', '300', '400'] as $key => $status) {
      if ($key != 0)
        echo "</table>\n\n\n";
      $links = array_filter(glob(APP_PATH . APP_BASE['clients'] . $status . '*'), 'is_dir');
      $statusCode = $status;
      $status = ($status == 000) ? "On-call" :
        (($status == 100) ? "Working" :
          (($status == 200) ? "Planning" :
            (($status == 300) ? "Previous" :
              (($status == 400) ? "Future" : "Unknown")))); ?>
      <h3>&#9660; Stage: <?= $status ?> (<?= $statusCode ?>)</h3>
      <table style="border:none;">
        <tr style="border:none;">
          <?php
          $count = 1;
          $old_links = $links;
          while ($link = array_shift($links)) {
            $old_link = $link;
            $link = basename($link);
            echo "<td style=\"text-align:center;border:none;\" class=\"text-xs\">
                    <a class=\"pkg_dir\" href=\"?client=$link\">
                    <img src=\"resources/images/directory.png\" width=\"50\" height=\"32\" />
                    <br />$statusCode-Client$count/</a><br />
                  </td>";
            if ($count >= 6)
              echo '</tr><tr>';
            elseif ($old_link == end($old_links))
              echo '</tr>';
            $count = ($count >= 6) ? 1 : $count + 1;
          } ?>
      </table>
      <?php
    }
    break;

  case 'projects-base':
    // dd("$context : $absDir | APP_ROOT=" . APP_ROOT, false);
    ?>
    <div style="text-align:center;border:none;" class="text-xs">
      <a class="pkg_dir" href="#" onclick="document.getElementById('app_project-container').style.display='block';">
        <img src="resources/images/project-icon.png" width="50" height="32" />
      </a><br />
      <a href="?project">./project/</a>
    </div>
    <table style="border:none;">
      <tr style="border:none;">
        <?php
        $links = array_filter(glob(APP_PATH . app_base('projects', null, 'rel') . '*'), 'is_dir');
        $count = 1;
        if (empty($links))
          echo "<hr />\n";
        $old_links = $links;
        while ($link = array_shift($links)) {
          $old_link = $link;
          $link = basename($link);
          echo "<td style=\"text-align:center;border:none;\" class=\"text-xs\">
                  <a class=\"pkg_dir\" href=\"?project=$link\">
                  <img src=\"resources/images/directory.png\" width=\"50\" height=\"32\" />
                  <br />$link</a><br />
                </td>";
          if ($count >= 7)
            echo '</tr><tr>';
          elseif ($old_link == end($old_links))
            echo '</tr>';
          $count = ($count >= 7) ? 1 : $count + 1;
        } ?>
    </table>
    <?php
    break;

  case 'clients':
  case 'projects':
  case 'app':
  default:
    // Your generic directory grid for $absDir
    //render_directory_grid($absDir);
    //dd("$context : $absDir", false);  // dd(APP_ROOT, false);

    // dd(APP_ROOT, false);

    $path = rtrim(ctx('absDir'), '/\\') . '/';
    /*
          if (defined('APP_ROOT') && APP_ROOT) {
            $path .= APP_ROOT;


          } elseif (APP_ROOT === '') {
            // Handle client-specific path
            if (isset($_GET['client'])) {
              $path = APP_BASE['clients'] . $_GET['client'] . DIRECTORY_SEPARATOR;
            }

            // Add domain to the path if applicable
            if (isset($_GET['domain'])) {
              $path = APP_PATH . APP_BASE['clients'] . $_GET['domain'] . DIRECTORY_SEPARATOR;
            } elseif (!isset($_GET['client']) && isset($_GET['path']) && $_GET['path'] == 'vendor') {
              // Default to vendor path if no domain or client is set
              $path .= 'vendor' . DIRECTORY_SEPARATOR;
              if (isset($_GET['client'])) {
                $path .= $_GET['client'] . DIRECTORY_SEPARATOR;
              }
            }
          } else {
            $path .= APP_ROOT;
          }
    */
    // dd("$COMPLETE_PATH_INSTALL", false);

    if (isset($_GET['path']) && preg_match('/^vendor\/?/', $_GET['path'])) {

      //if ($_ENV['COMPOSER']['AUTOLOAD'] == true)
      //require_once APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
      //require_once APP_PATH . 'api' . DIRECTORY_SEPARATOR . 'composer.php'; ?>
      <!-- iframe src="composer_pkg.php" style="height: 500px; width: 700px;"></iframe -->
      <div style="width: 700px; ">
        <div style="display: inline-block; width: 350px;"><a href="#!"
            onclick="handleClick(event, 'vendor/'); openApp(\'tools/registry/composer\');">Composers</a>
          Vendor Packages [Installed] List</div>
        <div style="display: inline-block; text-align: right; width: 300px;">
          <form
            action="<?= !defined('APP_URL') ? '//' . APP_DOMAIN . APP_URL_PATH . '?' . http_build_query(APP_QUERY, '', '&amp;') : APP_URL . '?' . http_build_query(APP_QUERY, '', '&amp;') ?>"
            method="POST">
            <input id="RequirePkg" type="text" title="Enter Text and onSelect" list="RequirePkgs"
              placeholder="[vendor]/[package]" name="composer[package]" value onselect="get_package(this);" autocomplete="off"
              style=" margin-top: 4px;">
            <button type="submit" style="border: 1px solid #000; margin-top: 4px;"> Add </button>
            <div style="display: inline-block; float: right; text-align: left; margin-left: 10px;" class="text-xs">
              <input type="checkbox" name="composer[install]" value="" /> Install<br />
              <input type="checkbox" name="composer[update]" value="" /> Update
            </div>
            <datalist id="RequirePkgs">
              <option value=""></option>
            </datalist>
          </form>
        </div>
      </div>
    <?php }
    /* var_dump(COMPOSER_VENDORS); null; */ //dd($_GET, false); 
    ?>
    <div class="directory-grid" data-app-path="devtools/directory">
      <?php

      if (isset($_REQUEST['path']) && $_REQUEST['path'] !== '') {
        /*
                  echo <<<END
        <div class="directory-entry">
        <div
          style="position: relative; display: block;">
        END;

          echo '<a href="?' . (isset($_REQUEST['client']) ? 'client=test' : 'testing') . '&domain={$_REQUEST['domain']}&path={$_REQUEST['path']}" onclick="handleClick(event, '../{$_REQUEST['path']}');">

        echo <<<END
            <img src="resources/images/directory.png" width="50" height="32" />
            ../</a>
        </div>
        </div>
        END;
        */
      }

      //dd(APP_CLIENT, false);
  
      //$path = (defined('APP_CLIENT')) ? APP_CLIENT : APP_PATH . (!isset($_GET['domain']) && isset($_GET['client']) ? APP_ROOT : APP_ROOT);
  
      //echo dirname($pathAvail) . DIRECTORY_SEPARATOR . ($_GET['path'] ?? '');
  
      //$paths = ['thgsgfhfgh.php']; // dirname(APP_PATH . APP_ROOT) . DIRECTORY_SEPARATOR
      $paths = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '{.[!.]*,*}', GLOB_BRACE | GLOB_MARK);

      unset($path);
      //dd(urldecode($_GET['path']));
  
      usort($paths, function ($a, $b) {
        $aIsDir = is_dir($a);
        $bIsDir = is_dir($b);

        // Check if either $a or $b is the "project.php" file
        $aIsProjectFile = !$aIsDir && basename($a) === 'project.php';
        $bIsProjectFile = !$bIsDir && basename($b) === 'project.php';

        // Handle the case when either $a or $b is the "project.php" file
        if ($aIsProjectFile || $bIsProjectFile) {
          if ($aIsProjectFile && $bIsProjectFile) {   // -1 0 1
            return -1; // Both are "project.php" files, no change in order
          } elseif ($aIsProjectFile) {
            return 0; // $a is "project.php", move it down
          } else {
            return 1; // $b is "project.php", move it up
          }
        }

        // Directories go first, then files
        if ($aIsDir && !$bIsDir) {
          return -1;
        } elseif (!$aIsDir && $bIsDir) {
          return 1;
        }

        // If both are directories or both are files, sort alphabetically
        return strcasecmp($a, $b);
      });
      /*
      usort($paths, function ($a, $b) {
          $aIsDir = is_dir($a);
          $bIsDir = is_dir($b);

          // Directories go first, then files
          if ($aIsDir && !$bIsDir) {
              return -1;
          } elseif (!$aIsDir && $bIsDir) {
              return 1;
          }

          // If both are directories or both are files, sort alphabetically
          return strcasecmp($a, $b);
      });
      */

      $count = 1;
      $lastKey = array_key_last($paths);
      /*
          <table style="border:none;">
            <tr style="border:none;">
              <?php
              $count = 1;
              $links = array_filter(
                glob(APP_PATH . APP_BASE['clients'] . '*', GLOB_ONLYDIR),
                fn($link) => preg_match('/^(?!\d{3}-)[a-z0-9\-]+\.[a-z]{2,6}$/i', basename($link))
              );
              $old_links = $links;
              while ($link = array_shift($links)) {
                $old_link = $link;
                $link = basename($link);
                echo "<td style=\"text-align:center;border:none;\" class=\"text-xs\">
                        <a class=\"pkg_dir\" href=\"?" . (isset($_ENV['DEFAULT_CLIENT']) && $_ENV['DEFAULT_CLIENT'] == $link ? '' : "domain=$link") . "\">
                        <img src=\"resources/images/directory.png\" width=\"50\" height=\"32\" />
                        <br />$link/</a><br />
                      </td>";
                if ($count >= 6)
                  echo '</tr><tr>';
                elseif ($old_link == end($old_links))
                  echo '</tr>';
                $count = ($count >= 6) ? 1 : $count + 1;
              }
              ?>
          </table>      
      */
      if (!empty($paths))
        foreach ($paths as $key => $path) {

          // Adjust the path to be relative to the current directory
          $relativePath = str_replace(APP_PATH . APP_ROOT, '', rtrim($path, DIRECTORY_SEPARATOR));

          echo '<div class="directory-entry">' . "\n";
          if (is_dir($path)) {
            if (substr(PHP_OS, 0, 3) == 'WIN') {
              $relativePath = rtrim(str_replace('\\', '/', $relativePath), DIRECTORY_SEPARATOR); //
            } elseif (stripos(PHP_OS, 'LIN') == 0) {
              $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath) . DIRECTORY_SEPARATOR;
            }

            //dd($relativePath);
  

            //function buildQueryString($queryParams, $relativePath) {
/*
              $client = isset($_GET['client']) ? 'client=' . urlencode($_GET['client']) . '&' : '';
              $domain = isset($_GET['domain']) && $_GET['domain'] !== ''
                ? 'domain=' . urlencode($_GET['domain']) . '&'
                : '';
              $project = isset($_GET['project']) ? 'project=' . urlencode($_GET['project']) . '&' : '';

              // return null;
              $url = $client . $domain . $project . 'path=' . urlencode($relativePath); */
            //}
  
            /**
             * Inputs:
             *  - $path: absolute or mixed path of the current item (file/dir entry you are rendering)
             *  - $relativePath: path relative to your current context (can be same as $_GET['path'] or derived)
             *
             * Contract:
             *  - We never mutate $_GET
             *  - We only show at most ONE of project|client|domain in the query
             *  - We always carry a normalized "path" that preserves the parent and appends the child "/"
             */

            /* -------------- main logic (unchanged except using PathUtils::) -------------- */

            $GET = [
              'project' => $_GET['project'] ?? '',
              'client' => $_GET['client'] ?? '',
              'domain' => $_GET['domain'] ?? '',
              'path' => $_GET['path'] ?? '',
            ];

            /* ---------- usage inside your link render ---------- */
            $GET = $_GET; // read-only use
            $visibleBase = PathUtils::norm($GET['path'] ?? '');
            $childName = basename(rtrim((string) $path, "/\\"));
            $nextPath = PathUtils::buildChildPath($visibleBase, $childName);


            if (isDomainName($childName)) {
              // Domain: use ?domain=... and remove path
              $href = build_query_href([], ['client' => $_GET['client'] ?? '', 'domain' => $childName] ?? '', ['path']);
              $dataDir = ''; // htmlspecialchars($childName, ENT_QUOTES);
            } else {
              // Folder/file: use ?path=... and remove domain (keep them mutually exclusive)
              $href = build_query_href([], [/*'path' => $nextPath*/], ['domain']);
              $dataDir = htmlspecialchars($nextPath, ENT_QUOTES);
            }

            //$href = QueryUrl::build($GET, $nextPath);
            //$onclickAttr = " onclick=\"return App['devtools/directory'].handleClick('" . htmlspecialchars($nextPath, ENT_QUOTES) . "')\"";
            //$dataDir = htmlspecialchars($nextPath, ENT_QUOTES);
            /* ---------- end usage ---------- */
            switch (true) {


              default:
                // Render the folder link
  
                echo '<a href="' . $href . '"' . /* $onclickAttr .*/ ' data-dir="' . $dataDir . '">
  <img src="resources/images/directory.png" width="50" height="32" alt=""></a>'
                  . '<a href="' . $href . '"' . /* $onclickAttr .*/ ' data-dir="' . $dataDir . '">'
                  . htmlspecialchars($childName) . '/</a>';
                break;
            }
          } elseif (is_file($path)) {
            $relativePath = str_replace(APP_PATH . APP_ROOT, '', rtrim($path, DIRECTORY_SEPARATOR));
            // Ensure the path excludes the domain if present in the folder structure
            $relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);

            if (!empty($_GET['domain'])) {
              // Remove the domain from the relative path if it exists at the start
              $relativePath = preg_replace(
                '#^' . preg_quote($_GET['domain'], '#') . '/?#',
                '',
                $relativePath
              );
            }

            // Initialize query parameters
            $queryParams = [];

            if (!isset($_GET['path']))
              $_GET['path'] = '';

            $_GET['app'] = 'ace_editor';

            // Determine the parameters to use based on the conditions
            if (isset($_GET['client']) && isset($_GET['domain'])) {
              // Case 1: Both client and domain are set
              $queryParams = [
                'client' => $_GET['client'],
                'domain' => $_GET['domain'],
                'path' => $_GET['path'] ?? dirname(rtrim($path, '/')) . '/', // Add the path parameter
                'app' => $_GET['app'],
                'file' => basename($path),
              ];
            } elseif (isset($_GET['client'])) {
              // Case 2: Only client is set
              $queryParams = [
                'client' => $_GET['client'],
                'domain' => $_GET['domain'] ?? '' /*rtrim($relativePath, '/')*/ , // Default domain if not explicitly provided
                'path' => $_GET['path'] ?? dirname(rtrim($path, '/')) . '/', // Add the path parameter
                'app' => $_GET['app'],
                'file' => basename($path),
              ];
            } elseif (isset($_GET['domain'])) {
              // Case 2: Only client is set
              $queryParams = [
                'domain' => $_GET['domain'] ?? '' /*rtrim($relativePath, '/')*/ , // Default domain if not explicitly provided
                'path' => $_GET['path'] ?? dirname(rtrim($path, '/')) . '/', // Add the path parameter
                'app' => $_GET['app'],
                'file' => basename($path),
              ];
            } elseif (isset($_GET['project'])) {
              $queryParams = [
                //'path' => rtrim($relativePath, '/') . '/', // Use the path parameter
                'project' => $_GET['project'] ?? '', // Add the path parameter
                'path' => $_GET['path'] ?? '',
                'app' => $_GET['app'],
                'file' => basename($path),
              ];
            } elseif (isset($_GET['path'])) {
              // Case 3: Only path is set
              $queryParams = [
                //'path' => rtrim($relativePath, '/') . '/', // Use the path parameter
                'path' => $_GET['path'] ?? '', // Add the path parameter
                'app' => $_GET['app'],
                'file' => basename($path),
              ];
            }

            // Filter out empty parameters to avoid unnecessary query string entries
//$queryParams = array_filter($queryParams);
  
            // Build the query string
            $queryString = http_build_query($queryParams);

            // Final URL
            $url = /*basename(__FILE__) .*/ "?$queryString";

            if (preg_match('/^\..*/', basename($path))) {

              //$relativePath = str_replace('\\', '\\\\', $relativePath );
  
              switch (basename($path)) {
                case '.htaccess':
                  echo '<div style="position: relative; border: 4px dashed #A50F5E;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : "path={$_GET['path']}&") . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/htaccess_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
                  break;
                case '.babelrc':
                  echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : "path={$_GET['path']}&") . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/babelrc_file.gif" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
                  break;
                case '.gitignore':
                  echo '<div style="position: relative; border: 4px dashed #F05033;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . 'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/gitignore_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
                  break;
                case '.env.bck':
                case '.env':
                  echo '<div style="position: relative;"><a onclick="openNewEditorWindow(\'' . basename($path) . '\', \'Hello123\');"><img src="resources/images/env_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
                  break;
                default:
                  echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/env_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
              }
            } elseif (preg_match('/^package(?:-lock)?\.(json)/', basename($path))) {
              echo '<div style="position: relative; border: 4px dashed #E14747;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\'); document.getElementById(\'app_node_js-container\').style.display=\'block\';">';

              switch (basename($path)) {
                case 'package.json':
                  echo '<img src="resources/images/package_json_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                    . '</div>' . "\n";
                  break;
                case 'package-lock.json':
                  echo '<img src="resources/images/package-lock_json_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                    . '</div>' . "\n";
                  break;
              }

            } elseif (preg_match('/^composer(?:-setup)?\.(json|lock|php|phar)/', basename($path))) {
              echo '<div style="position: relative;"><div style="position: relative; border: 4px dashed #6B4329;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\'); openApp(\'tools/registry/composer\');">';

              switch (basename($path)) {
                case 'composer.json':
                  echo '<img src="resources/images/composer_json_file.gif" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\');">' . basename($path) . '</a>'
                    . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                    . '</div></div>' . "\n";
                  break;
                case 'composer.lock':
                  echo '<img src="resources/images/composer_lock_file.gif" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    . (isset($errors['COMPOSER-VALIDATE-LOCK']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div></div>' . "\n";
                  break;
                case 'composer.phar':
                  echo '<img src="resources/images/phar_file.png" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                    /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div></div>' . "\n";
                  break;
                default:
                  echo '<img src="resources/images/composer_php_file.gif" width="40" height="50" /></a>'
                    . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                    . '</a></div></div>' . "\n";
                  break;
              }
            } elseif (preg_match('/^.*\.js$/', basename($path))) {
              switch (basename($path)) {
                case 'webpack.config.js':
                  echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '"><img src="resources/images/webpack_config_js_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                  break;
                default:
                  echo '<a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/js_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                  break;
              }

            } elseif (preg_match('/^.*\.md$/', basename($path))) {
              echo '<div style="position: relative; border: 4px dashed #8BBB4B;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/md_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';

            } elseif (preg_match('/^.*\.php$/', basename($path))) {
              if (preg_match('/^project\.php/', basename($path)))
                echo '<div style="position: relative; border: 4px dashed #2C88DA;"><a style="position: relative;" href="' . (isset($_GET['project']) ? 'project#!' : '#') . '" onclick="document.getElementById(\'app_project-container\').style.display=\'block\';"><div style="position: absolute; left: -60px; top: -20px; color: red; font-weight: bold;">' . (isset($_GET['project']) ? '' : '') . '</div><img src="resources/images/project-icon.png" width="40" height="50" /></a><a href="' . /*basename(__FILE__) .*/ '?' . (isset($_GET['project']) ? 'project#!' : (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'app=ace_editor' . '&file=' . basename($path)) . '" ' . (isset($_GET['project']) ? 'onclick="document.getElementById(\'app_ace_editor-container\').style.display=\'block\';"' : 'onclick="handleClick(event, \'' . basename($relativePath) . '\')"') . '>' . basename($path) . '</a></div>';
              elseif (basename($path) == 'phpunit.php')
                echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . 'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/phpunit_php_file.png" width="40" height="50" /></a>' . '<a href="' . /*basename(__FILE__) .*/ '?file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
              elseif (basename($path) == 'bootstrap.php')
                echo '<div style="position: relative; border: 4px dashed #897AE3;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . '&path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';
              elseif (basename($path) == 'server.php')
                echo '<div style="position: relative; border: 4px dashed #897AE3;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . '&path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';
              else
                echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . 'path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';

            } elseif (basename($path) == 'LICENSE' && preg_match('/^' . preg_quote(APP_PATH, '/') . 'LICENSE$/', $path)) {
              /* https://github.com/unlicense */
              echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path']) . '&app=ace_editor' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/license_file.png" width="40" height="50" /></a>un' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                . '.org</a>'
                /* . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                . '</div>' . "\n";
            } elseif (basename($path) == basename(ini_get('error_log')))
              echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path']) . '&app=ace_editor' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">'
                . '<img src="resources/images/error_log.png" width="40" height="50" /></a><a id="app_php-error-log" href="' . (APP_URL_BASE['query'] != '' ? '?' . APP_URL_BASE['query'] : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') . /* '?' . basename(ini_get('error_log')) . '=unlink' */ '" style="text-decoration: line-through; background-color: red; color: white;"></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '</a><div style="position: absolute; top: -8px; left: 8px; color: red; font-weight: bold;"><a href="#" onclick="$(\'#requestInput\').val(\'unlink error_log\'); $(\'#requestSubmit\').click();">[X]</a></div>' : '')
                . '</div>' . "\n";
            elseif (preg_match('/^.*\.exe$/', basename($path))) {
              echo '<div style="position: relative; border: 4px dashed #8BBB4B;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . 'download&' . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '"><img src="resources/images/exe_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '">' . basename($path) . '</a></div>';

            } else
              echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
          }
          echo "</div>\n";
        } ?>
    </div>
    <?php
    break;
}

/*
  $basePath = rtrim(APP_PATH, DIRECTORY_SEPARATOR);
  $clientPath = $_GET['client'] ?? null;
  $domainPath = $_GET['domain'] ?? null;
  $projectPath = $_GET['project'] ?? null;
  $relativePath = isset($_GET['path']) ? trim($_GET['path'], DIRECTORY_SEPARATOR) : null;

  // Base link
  $navigation = '<div style="height: 25px; display: inline;"><br /><br />[[ <a href="#" onclick="handleClick(event, \'./\');">' . APP_PATH . '</a> ]' . (APP_ROOT !== '' ? '[ <a href="#" onclick="handleClick(event, \'../\');">' . APP_ROOT . '</a> ]' : '') . (isset($_REQUEST['path']) && $_REQUEST['path'] !== '' ? '[ <a href="#" onclick="handleClick(event, \'../\');">' . (APP_BASE[rtrim($_REQUEST['path'], DIRECTORY_SEPARATOR)] ?? $_REQUEST['path']) . '</a> ]' : '') . '] ' . '</div><div style="display: inline;">' . '</div>';
  echo $navigation;
*/
/*  $cwd = getcwd();
(str_starts_with($cwd, APP_PATH)
  ? substr($cwd, strlen(APP_PATH))
  : '')
dd($_GET, false);

dd(APP_PATH . APP_ROOT, false);
  dd(get_required_files(), false);
dd($_POST, false);*/
//dd(APP_CLIENT, false);

if (isset($_GET['path']) && preg_match('/^project\/?/', $_GET['path']) || isset($_GET['project']) && empty($_GET['project'])) {

} elseif (isset($_GET['application'])) {
  if (readlinkToEnd($_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'applications') == '/mnt/c/www/applications') {
    if ($_GET['application']) {

      $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'applications/' . $_GET['application']), 'is_file');

      echo '<h3>Application: 7-Zip</h3>';

      echo '<br /><div style="text-align: center; margin: 0 auto;"><a href="https://www.7-zip.org/download.html"><img width="110" height="63" src="http://www.7-zip.org/7ziplogo.png" alt="7-Zip" border="0" /><br />' . basename($links[0]) . ' =&gt; <a href="https://www.7-zip.org/a/7z2301-x64.exe">7z2301-x64.exe</a></a></div>' . "<br />";
    } else {
      $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'applications/*'), 'is_file'); ?>
      <h3>Applications:</h3>
      <table width="" style="border: none;">
        <tr style=" border: none;">
          <?php
          //if (empty($links)) {
          //  echo '<option value="" selected>---</option>' . "\n"; // label="     "
          //} else  //dd($links);
          $count = 1;
          $old_links = $links;
          while ($link = array_shift($links)) {
            $old_link = $link;
            $link = basename($link);

            echo '<td style="text-align: center; border: none;" class="text-xs">' . "\n";

            echo '<a class="pkg_dir" href="?application=' . $link . '">'
              . '<img src="resources/images/app_file.png" width="50" height="32" style="" /><br />' . $link . '</a><br />'
              . '</td>' . "\n";

            if ($count >= 3)
              echo '</tr><tr>';
            elseif ($old_link == end($old_links))
              echo '</tr>';

            if (isset($count) && $count >= 3)
              $count = 1;
            else
              $count++;
          } ?>
      </table>
    <?php }
  }
} elseif (isset($_GET['node_module']) && empty($_GET['node_module'])) {
  //if (readlinkToEnd('/var/www/applications') == '/mnt/c/www/applications') { }
  $links = array_filter(glob(APP_PATH . 'node_modules/*'), 'is_dir'); ?>
  <div style="display: inline-block; width: 350px;">Node Modules [Installed] List</div>
  <div style="display: inline-block; text-align: right; width: 300px; ">
    <form action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + ['app' => 'composer', 'path' => 'vendor']) ?>"
      method="POST">
      <input id="RequirePkg" type="text" title="Enter Text and onSelect" list="RequirePkgs"
        placeholder="[vendor]/[package]" name="composer[package]" value="" onselect="get_package(this);"
        autocomplete="off" style=" margin-top: 4px;">
      <button type="submit" style="border: 1px solid #000; margin-top: 4px;"> Add </button>
      <div style="display: inline-block; float: right; text-align: left; margin-left: 10px;" class="text-xs">
        <input type="checkbox" name="composer[install]" value=""> Install<br>
        <input type="checkbox" name="composer[update]" value=""> Update
      </div>
      <datalist id="RequirePkgs">
        <option value=""></option>
      </datalist>
    </form>
  </div>
  <table width="" style="border: none;">
    <?php
    //if (empty($links)) {
    //  echo '<option value="" selected>---</option>' . "\n"; // label="     "
    //} else  //dd($links);
    $count = 1;
    $old_links = $links;
    while ($link = array_shift($links)) {
      $old_link = $link;
      $link = basename($link);
      echo '<tr style=" border: none;">' . "\n";
      echo '<td style="text-align: center; border: none;" class="text-xs">' . "\n";
      echo '<a class="pkg_dir" href="?application=' . $link . '">'
        . '<img src="resources/images/directory.png" width="50" height="32" style="" /><br />' . $link . '</a><br />'
        . '</td>' . "\n";

      if ($count >= 3)
        echo '</tr><tr>';
      elseif ($old_link == end($old_links))
        echo '</tr>';

      if (isset($count) && $count >= 3)
        $count = 1;
      else
        $count++;
    }

    ?>
  </table>
<?php } elseif (isset($_GET['path']) && preg_match('/^client(?:s|ele)?\/?/', $_GET['path']) || isset($_GET['client']) && empty($_GET['client'])) {

} else {

}
?>
</div>
<?php $UI_APP['body'] = ob_get_contents();
ob_end_clean();


if (false) { ?>
  <script><?php }
ob_start(); ?>
    // devtools/directory module script
    (() => {
      const APP_ID = 'devtools/directory';
      const CONTAINER_ID = 'app_devtools_directory-container';

      const container = document.getElementById(CONTAINER_ID);
      if (!container) return;

      // avoid double-binding if this app reloads
      if (container.dataset.bound === '1') return;
      container.dataset.bound = '1';

      // ensure registries exist (keeps inline access patterns working)
      window.AppMods = window.AppMods || {};
      window.App = window.App || new Proxy({}, {
        get(_t, k) { return window.AppMods[k]; },
        has(_t, k) { return k in window.AppMods; }
      });

      // --- URL + reload helper (fixed app param) ---
      async function handleClick(path) {
        const url = new URL(location.href);

        if (path) url.searchParams.set('path', path);
        else url.searchParams.delete('path');

        // ensure ?app is never shown in the URL
        url.searchParams.delete('app');

        history.pushState({ path: path || '' }, '', url.toString());

        await window.openApp?.('devtools/directory', {
          params: path ? { path } : {},
          forceReload: true,
          from: 'dir-click'
        });
        return false;
      }

      // Public API (kept for other modules / inline)
      const api = { init() { }, handleClick };
      window.AppMods[APP_ID] = Object.assign(window.AppMods[APP_ID] || {}, api);
      window.App[APP_ID] = window.AppMods[APP_ID];

      // --- Delegated clicks inside the directory container ---
      container.addEventListener('click', async (e) => {
        const el = e.target.closest('[data-dir],[data-path],[data-open-app]');
        if (!el) return;

        const path = el.getAttribute('data-dir') ?? el.getAttribute('data-path');
        const app = el.getAttribute('data-open-app');
        const openAfter = el.getAttribute('data-open-after-dir') === '1';

        // Case 1: directory click (we own this; prevent bubbling to global delegate)
        if (path) {
          e.preventDefault();
          e.stopPropagation();

          // If also flagged to open an app afterwards, do it in sequence
          if (openAfter && app) {
            await handleClick(path);
            // pass path along if your other app uses it; otherwise call without params
            window.openApp?.(app, { params: { path }, from: 'dir-tile' });
            return;
          }

          // Just a plain directory change
          await handleClick(path);
          return;
        }

        // Case 2: pure app tile (no path) — let the global document delegate handle it
        // (Do NOT call preventDefault/stopPropagation here.)
      });

      // --- Back/forward support for path changes ---
      window.addEventListener('popstate', (ev) => {
        // Only react if this module is currently present
        if (!document.getElementById('app_devtools_directory-container')) return;

        const sp = new URLSearchParams(location.search);
        const path = (ev.state && 'path' in ev.state) ? ev.state.path : (sp.get('path') || '');

        window.openApp?.('devtools/directory', {
          params: path ? { path } : {},
          forceReload: true,
          from: 'popstate'
        });
      });

      // Respect ?path=… on initial load (optional: trigger a reload if you want)
      // const urlPath = new URLSearchParams(location.search).get('path');
      // if (urlPath) { handleClick(urlPath); }
    })();

  // register on a global so index.php can find it
  //window.AppModules ??= {};
  //window.AppModules['devtools/directory'] = { init, handleClick };

  // tell the host page we’re ready
  //window.__registerAppModule('devtools/directory', { init, handleClick });

  // example returned by dispatcher when Accept: text/javascript

  <?php
  $UI_APP['script'] = ob_get_contents();
  ob_end_clean();

  if (false) { ?></script><?php }

  /**
   * Expect $UI_APP = ['style' => '...', 'body' => '...', 'script' => '...'];
   * This file will:
   *   - If included: return $UI_APP as-is (no HTML rendering, no $UI_APP['html'])
   *   - If accessed directly: bootstrap (if needed), render and echo a full HTML page, then exit
   */

  if (!isset($UI_APP) || !is_array($UI_APP)) {
    $UI_APP = ['style' => '', 'body' => '', 'script' => ''];
  }

  /* ───────────────────────────── Helpers ───────────────────────────── */

  $__isDirect = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__);
  $__hasApp = defined('APP_RUNNING');

  /**
   * Resolve and include bootstrap if we’re executed from /public or APP isn’t running yet.
   */
  $__maybeBootstrap = function (): void {
    if (defined('APP_RUNNING'))
      return;

    // If launched from /public, move to project root
    $scriptDirBase = basename(dirname(realpath($_SERVER['SCRIPT_FILENAME'] ?? __FILE__)));
    if ($scriptDirBase === 'public') {
      @chdir(dirname(__DIR__)); // go up from /public to project root
    }

    $bootstrap = __DIR__ . '/../bootstrap/bootstrap.php';
    if (is_file($bootstrap)) {
      require_once $bootstrap;
    }
  };

  /**
   * Get Tailwind script src (CDN if online and reachable; fallback to cached local copy).
   * Will refresh local cache every 5 days when APP_IS_ONLINE is true.
   */
  $__tailwindSrc = function (string $version = '3.3.5'): string {
    // You have app_base() and check_http_status() in your project.
    $cdnUrl = 'https://cdn.tailwindcss.com';
    $localPath = rtrim(app_base('resources', null, 'abs'), DIRECTORY_SEPARATOR) . '/js/tailwindcss-' . $version . '.js';
    $localRelDir = rtrim(app_base('resources', null, 'rel'), '/'); // e.g. 'resources/'
    $localRel = $localRelDir . 'js/tailwindcss-' . $version . '.js';

    // Ensure local dir
    is_dir(dirname($localPath)) || @mkdir(dirname($localPath), 0755, true);

    // Online + stale or missing → refresh cache (every 5 days)
    if (defined('APP_IS_ONLINE') && APP_IS_ONLINE) {
      $stale = !is_file($localPath) || (time() - @filemtime($localPath) > 5 * 24 * 60 * 60);
      if ($stale) {
        $ch = curl_init($cdnUrl);
        curl_setopt_array($ch, [
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_TIMEOUT => 10,
        ]);
        $js = curl_exec($ch);
        curl_close($ch);
        if ($js) {
          @file_put_contents($localPath, $js);
        }
      }
    }

    // Prefer CDN when reachable, else local relative path
    if (defined('APP_IS_ONLINE') && APP_IS_ONLINE && function_exists('check_http_status') && check_http_status($cdnUrl)) {
      // Use protocol-relative to match your original pattern
      $host = parse_url($cdnUrl, PHP_URL_HOST);
      $pos = strpos($cdnUrl, $host);
      return substr($cdnUrl, $pos + strlen($host)); // e.g. "//cdn.tailwindcss.com"
    }

    return $localRel; // e.g. "resources/js/tailwindcss-3.3.5.js"
  };

  /**
   * Render full page.
   */
  $__renderPage = function (array $UI_APP) use ($__tailwindSrc): void {
    $tailwindSrc = $__tailwindSrc();
    ?>
  <!DOCTYPE html>
  <html>

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />
    <script src="<?= htmlspecialchars($tailwindSrc, ENT_QUOTES) ?>"></script>

    <style type="text/tailwindcss">
      <?= $UI_APP['style'] ?? '' ?>

                                                                                  </style>
  </head>

  <body>
    <?= $UI_APP['body'] ?? '' ?>

    <!-- jQuery + jQuery UI -->
    <script src="//code.jquery.com/jquery-1.12.4.js"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <script>
      <?= $UI_APP['script'] ?? '' ?>

    </script>
  </body>

  </html>
  <?php
  };

  /* ───────────────────────────── Flow ───────────────────────────── */

  if ($__isDirect) {
    // bootstrap (if not already)
    $__maybeBootstrap();

    // If bootstrap failed to set the project root and we’re not where we expect, error out
    // (Optional strictness — keep or remove as you like)
    // if (!defined('APP_RUNNING')) { die('Bootstrap failed.'); }
  
    // Render and exit — DO NOT populate $UI_APP['html']
    $__renderPage($UI_APP);
    exit;
  }

  // If included: return data (no HTML string added)
  return $UI_APP;
