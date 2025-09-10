<?php

if (!defined('APP_PATH')) {
  define('APP_PATH', rtrim(realpath(__DIR__ . '/../'), '/\\') . DIRECTORY_SEPARATOR);
}
if (!defined('APP_ROOT')) {
  define('APP_ROOT', '');
}

$pathsFile = APP_PATH . 'config/constants.paths.php';
if (is_file($pathsFile))
  require_once $pathsFile;


$app_id = 'visual/nodes';           // full path-style id

// Always normalize slashes first
$app_norm = str_replace('\\', '/', $app_id);

// Last segment only (for titles, labels, etc.)
$slug = basename($app_norm);                    // "composer"

// Sanitized full path for DOM ids (underscores only from non [A-Za-z0-9_ -])
$key = preg_replace('/[^\w-]+/', '_', $app_norm);  // "tools_registry_composer"

// If you prefer strictly underscores (no hyphens), do: '/[^\w]+/'

// Core DOM ids/selectors
$container_id = "app_{$key}-container";         // "app_tools_registry_composer-container"
$selector = "#{$container_id}";

// Useful companion ids
$style_id = "style-{$key}";                    // "style-tools_registry_composer"
$script_id = "script-{$key}";                   // "script-tools_registry_composer"

// Optional: data attributes you can stamp on the container for easy introspection
$data_attrs = sprintf(
  'data-app-path="%s" data-app-key="%s" data-app-slug="%s"',
  htmlspecialchars($app_norm, ENT_QUOTES),
  htmlspecialchars($key, ENT_QUOTES),
  htmlspecialchars($slug, ENT_QUOTES),
);

//$hash = substr(sha1($app_norm), 0, 6);
//$key = preg_replace('/[^\w-]+/', '_', $app_norm) . "_{$hash}";
if (isset($_GET['app'], $_GET['graph']) && $_GET['app'] === 'visual/nodes') {
  // Ensure APP_PATH/APP_ROOT exist (safe defaults)
  if (!defined('APP_PATH'))
    define('APP_PATH', rtrim(realpath(__DIR__ . '/../'), '/\\') . DIRECTORY_SEPARATOR);
  if (!defined('APP_ROOT'))
    define('APP_ROOT', '');

  header('Content-Type: application/json');

  // --- sandboxed include collector (subprocess) ---
  function getRequiredFilesFromScript(string $script): array
  {
    $php = PHP_BINARY ?: 'php';
    $cwd = escapeshellarg(APP_PATH);
    $file = escapeshellarg($script);
    $code = 'chdir(' . $cwd . '); require ' . $file . '; echo json_encode(get_included_files(), JSON_UNESCAPED_SLASHES);';
    $cmd = $php . ' -d detect_unicode=0 -r ' . escapeshellarg($code);
    $out = shell_exec($cmd);
    if (!is_string($out) || $out === '')
      return [];
    $arr = json_decode($out, true);
    return is_array($arr) ? $arr : [];
  }

  // Normalize to base-relative and keep only files within base
  function normalizeFilePaths(array $files, string $baseDir): array
  {
    $base = rtrim(str_replace('\\', '/', $baseDir), '/') . '/';
    $out = [];
    foreach ($files as $f) {
      $n = str_replace('\\', '/', $f);
      if (stripos($n, $base) === 0) {
        $out[] = substr($n, strlen($base));
      }
    }
    $out = array_values(array_unique($out));
    sort($out, SORT_STRING);
    return $out;
  }

  // Determine base dir
  $baseDir = APP_PATH . (APP_ROOT ?: '');

  // Entry scripts to analyze (make these relative to base)
  $entries = [
    'public/index.php' => (APP_ROOT ? APP_ROOT : '') . 'public/index.php',
    'api/composer.php' => (APP_ROOT ? APP_ROOT : '') . 'api/composer.php', // add back when desired
  ];

  // Collect per-entry required files
  $jsonData = [];
  foreach ($entries as $label => $scriptPath) {
    $filesAbs = getRequiredFilesFromScript($scriptPath);
    $jsonData[$label] = normalizeFilePaths($filesAbs, $baseDir);
  }

  // OPTIONAL: keep vendor packages only under composer
  if (isset($jsonData['api/composer.php'], $jsonData['public/index.php'])) {
    $vendor = array_filter($jsonData['public/index.php'], fn($f) => strpos($f, 'vendor/') === 0);
    $jsonData['api/composer.php'] = array_values(array_unique(array_merge($jsonData['api/composer.php'], $vendor)));
    $jsonData['public/index.php'] = array_values(array_diff($jsonData['public/index.php'], $vendor));
    sort($jsonData['api/composer.php'], SORT_STRING);
    sort($jsonData['public/index.php'], SORT_STRING);
  }

  // OPTIONAL: emit a D3-ready graph (nodes/links)
  if (isset($_GET['graph'])) {
    $index = [];
    $nodes = [];
    $links = [];

    // Create nodes from all unique files
    $allFiles = [];
    foreach ($jsonData as $list)
      $allFiles = array_merge($allFiles, $list);
    $allFiles = array_values(array_unique($allFiles));
    sort($allFiles, SORT_STRING);

    foreach ($allFiles as $i => $file) {
      $index[$file] = $i;
      $nodes[] = ['id' => $i, 'name' => $file];
    }

    // Links: from entry → each required file (or build transitive links if you want)
    foreach ($jsonData as $entry => $list) {
      // ensure the entry itself is a node
      if (!isset($index[$entry])) {
        $index[$entry] = count($nodes);
        $nodes[] = ['id' => $index[$entry], 'name' => $entry];
      }
      $src = $index[$entry];
      foreach ($list as $to) {
        if (!isset($index[$to]))
          continue;
        $links[] = ['source' => $src, 'target' => $index[$to]];
      }
    }

    echo json_encode(['nodes' => $nodes, 'links' => $links], JSON_UNESCAPED_SLASHES);
    exit;
  }

  // Default: return your original map structure
  echo json_encode($jsonData, JSON_UNESCAPED_SLASHES);
  exit;
}

if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"]))
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    if (is_file($path = realpath('index.php')))
      require_once $path;
  } else
    die(var_dump("Path was not found. file=$path"));

//if ($_SERVER['REQUEST_METHOD'] == 'POST')
//  if (isset($_GET['app']) && $_GET['app'] == 'nodes')

if (defined('GIT_EXEC'))
  if (is_dir($path = app_base('resources', null, 'rel') . 'js/ace') && empty(glob($path)))
    exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
  elseif (!is_dir($path)) {
    if (!mkdir($path, 0755, true))
      $errors['GIT-CLONE-ACE'] = ' resources/js/ace does not exist.';
    exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
  }

ob_start(); ?>
<?= $selector ?? '' ?> {
width: 500px;
height: 380px;
/* border: 1px solid black; */
position: absolute;
top: 60px;
left: 30%;
right: 0;
/* z-index: 1; */
/* resize: both; Make the div resizable */
/* overflow: hidden; Hide overflow to ensure proper resizing */
}

<?= $selector ?? '' ?>.selected {
display: block;
z-index: 1;
resize: both;
/* Make the div resizable */
cursor: move;
overflow: hidden;
/* Hide overflow to ensure proper resizing */
/* Add your desired styling for the selected container */
/*
// background-color: rgb(240, 224, 198); // 240, 224, 198, .75 #FBF7F1; // rgba(240, 224, 198, .25);

bg-[#FBF7F1];
bg-opacity-75;

font-weight: bold;
#top { background-color: rgba(240, 224, 198, .75); }
*/
}

#visualization {
/* margin-top: -50px; */
width: 100%;
height: calc(100% - 260px);
position: absolute;
z-index: 1;
}

.node circle {
fill: #999;
stroke: #fff;
stroke-width: 3px;
}

.link {
fill: none;
stroke: #555;
stroke-width: 1.5px;
}

.link.green {
stroke: green;
}

text {
font: 10px sans-serif;
}

<?php $UI_APP['style'] = ob_get_contents();
ob_end_clean();

/*
<div id="<?= $container_id ?>"
  class="absolute <?= __FILE__ == get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'nodes') && !isset($_GET['path']) ? 'selected' : '' ?>"
  style="display: <?= __FILE__ == get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'nodes') ? 'block' : 'block' ?>; resize: both; overflow: hidden;">
*/

ob_start(); ?>
<div class="window-header"
  style="position: relative; display: inline-block; width: 100%; cursor: move; border-bottom: 1px solid #000;background-color: #FFF;"
  data-drag-handle>
  <label class="nodes-home" style="cursor: pointer;">
    <div class="" style="position: relative; display: inline-block; top: 0; left: 0;">
      <img src="resources/images/d3_icon.png" width="32" height="32" />
    </div>
  </label>
  <div style="display: inline;">
    <span style="background-color: #38B1FF; color: #FFF; margin-top: 10px;">Nodes
      <?= /* (version_compare(NPM_LATEST, NPM_VERSION, '>') != 0 ? 'v'.substr(NPM_LATEST, 0, similar_text(NPM_LATEST, NPM_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(NPM_LATEST, similar_text(NPM_LATEST, NPM_VERSION)) . '</span>' : 'v'.NPM_VERSION ); */ NULL; ?></span>
    <span style="background-color: #0078D7; color: white;"><code id="AceEditorVersionBox" class="text-sm"
        style="background-color: white; color: #0078D7;"></code></span>
  </div>

  <div style="display: inline; float: right; margin-top: 10px; text-align: center; color: blue; z-index: -1;"><code
      style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="closeApp('visual/nodes', {fullReset:true})">[X]</a></code>
  </div>
</div>

<div id=""
  style="position: relative; width: 100%; height: 100%; border: 3px dashed #F5834A; background-color: #D0684D;">

  <div class="window-body"
    style="position: relative; display: block; margin: 0 auto; width: calc(100% - 2px); height: 50px; background-color: rgba(251,247,241);">
    <div style="display: inline-block; text-align: left; width: 125px;">
      <div class="npm-menu text-sm"
        style="cursor: pointer; font-weight: bold; padding-left: 25px; border: 1px solid #000;">Main Menu</div>
      <div class="text-xs" style="display: inline-block; border: 1px solid #000;">
        <a class="text-sm" id="app_nodes-frameMenuPrev"
          href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '#') ?>">
          &lt; Menu</a> | <a class="text-sm" id="app_nodes-frameMenuNext"
          href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '#') ?>">Init
          &gt;</a>
      </div>
    </div>
    <div class="absolute"
      style="position: absolute; display: inline-block; top: 5px; right: 0; text-align: right; float: right;">
      <div class="text-xs" style="position: relative; display: inline-block;">
        + 153 <a href="https://github.com/d3/d3/graphs/contributors">contributors</a>
        <br /><!-- a href="https://github.com/ajaxorg"><img src="resources/images/node.js.png" title="https://github.com/nodejs" width="18" height="18" /></a -->
        <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;" href="https://ace.c9.io/"
          title="https://d3js.org/">https://d3js.org/</a>
      </div>
    </div>
    <div style="clear: both;"></div>

    <?= /*
<div class="containerTbl" style="background-ground: #fff; border: 1px solid #000; display: <?= (isset($_GET['file']) && isset($_GET['path']) && is_file($_GET['path'] . $_GET['file']) ? 'none': 'block' ) ?>;">
<table width="" style="border: 1px solid #000;">
<tr>
<?php
$paths = glob($path . '/*');
//dd(urldecode($_GET['path']));
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

$count = 1;
if (!empty($paths))
foreach($paths as $key => $path) {
echo '<td style="border: 1px solid #000;" class="text-xs">' . "\n";
if (is_dir($path))
echo '<a href="?app=nodes&path=' . basename($path) . '">'
. '<img src="../../resources/images/directory.png" width="50" height="32" /><br />' . basename($path) . '</a>' . "\n";
elseif (is_file($path))
echo '<a href="?app=nodes&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '">'
. '<img src="../../resources/images/php_file.png" width="40" height="50" /><br />' . basename($path) . '</a>' . "\n";
echo '</td>' . "\n";
if ($count >= 6 || $path == end($paths)) echo '</tr>';
if (isset($count) && $count >= 6) $count = 1;
else $count++;
} 
?>
</tr>
</table>
</div>
*/ ''; ?>

  </div>



  <div style="position: relative; margin: 0 auto; width: calc(100% - 2px); height: 250px; background-color: #fff;">
    <div id="visualization"></div>
  </div>
  <!--
  <div id="app_nodes-frameInit" class="app_nodes-frame-container absolute" style="overflow: hidden; height: 270px;">

    <form autocomplete="off" spellcheck="false" action="?app=git#!" method="POST">
      <div style="position: absolute; right: 0; float: right; text-align: center;">
        <input id="gitInitSubmit" class="btn" type="submit" value="Init/Run">
      </div> 
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitInitLabel" for="gitInit" style="background-color: #6781B2; color: white;">? <code>Init</code></label>
        </div>
      </div>
      <div id="gitInitForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="2" name="git[init]">git init</textarea>
      </div>
    </form>

  </div>

  <div id="app_nodes-frameExtra" style="position: relative; width: 100%; height: 100%; border: 1px #000 solid;">

  </div> -->
</div>


<?php $UI_APP['body'] = ob_get_contents();
ob_end_clean();
/*</div>*/
if (false) { ?>
  <script type="text/javascript">
  <?php }
ob_start(); ?>
  function toGraph(data) {
    // If already in {nodes,links} form, just normalize and return
    if (data && Array.isArray(data.nodes) && Array.isArray(data.links)) {
      const nodes = data.nodes.map((n, i) => ({
        id: n.id ?? i,
        name: n.name ?? n.file ?? String(n)
      }));
      const links = data.links.map(l => ({
        // accept {source: id|obj, target: id|obj}
        source: (typeof l.source === 'object') ? (l.source.id ?? l.source) : l.source,
        target: (typeof l.target === 'object') ? (l.target.id ?? l.target) : l.target,
        color: l.color || ''
      }));
      return { nodes, links };
    }

    // Otherwise, convert from { entryFile: [dep1, dep2, ...], ... }
    const fileIndex = new Map();
    const nodes = [];
    const links = [];

    function ensureNode(name) {
      if (!fileIndex.has(name)) {
        fileIndex.set(name, nodes.length);
        nodes.push({ id: nodes.length, name });
      }
      return fileIndex.get(name);
    }

    Object.keys(data).forEach((file) => {
      const src = ensureNode(file);
      data[file].forEach((child) => {
        const dst = ensureNode(child);
        links.push({ source: src, target: dst, color: '' });
      });
    });

    // Color links from public/index.php as green
    const publicIdxId = fileIndex.get('public/index.php');
    if (publicIdxId !== undefined) {
      links.forEach(l => { if (l.source === publicIdxId) l.color = 'green'; });
    }

    return { nodes, links };
  }

  function createVisualization(raw) {
    const { nodes, links } = toGraph(raw);

    const width = 490, height = 180;

    const svg = d3.select("#visualization")
      .append("svg")
      .attr("width", width)
      .attr("height", 325)
      .attr("style", "margin-top:0; background-color:white;");

    const simulation = d3.forceSimulation(nodes)
      .force("link", d3.forceLink(links).id(d => d.id).distance(100))
      .force("charge", d3.forceManyBody().strength(-50))
      .force("center", d3.forceCenter(width / 2, height / 2));

    const link = svg.append("g")
      .attr("class", "links")
      .selectAll("line")
      .data(links)
      .enter().append("line")
      .attr("class", d => `link ${d.color || ''}`);

    const node = svg.append("g")
      .attr("class", "nodes")
      .selectAll("g")
      .data(nodes)
      .enter().append("g");

    node.append("circle").attr("r", 10);

    node.append("text")
      .text(d => d.name)
      .attr("x", 12)
      .attr("y", 3);

    simulation.nodes(nodes).on("tick", ticked);
    simulation.force("link").links(links);

    function ticked() {
      link
        .attr("x1", d => d.source.x)
        .attr("y1", d => d.source.y)
        .attr("x2", d => d.target.x)
        .attr("y2", d => d.target.y);

      node.attr("transform", d => `translate(${d.x},${d.y})`);
    }
  }

  fetch('/?app=visual/nodes&graph=1')
    .then(r => r.json())
    .then(data => createVisualization(data));

  $("#app_nodes-container").resizable({
    alsoResize: "#visualization"
  });

  <?php $UI_APP['script'] = ob_get_contents();
  ob_end_clean();

  if (false) { ?></script><?php }

  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Pragma: no-cache");
  //check if file is included or accessed directly
  ob_start(); ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <!-- link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" / -->

  <?php
  is_dir($path = app_base('resources', null, 'rel') . 'js/') or mkdir($path, 0755, true);
  if (is_file($path . 'tailwindcss-3.3.5.js')) {
    if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path . 'tailwindcss-3.3.5.js'))))) / 86400)) <= 0) {
      $url = 'https://cdn.tailwindcss.com';
      $handle = curl_init($url);
      curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

      if (!empty($js = curl_exec($handle)))
        file_put_contents($path . 'tailwindcss-3.3.5.js', $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
    }
  } else {
    $url = 'https://cdn.tailwindcss.com';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($js = curl_exec($handle)))
      file_put_contents($path . 'tailwindcss-3.3.5.js', $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
  }
  unset($path);
  ?>

  <script src="<?= 'resources/js/tailwindcss-3.3.5.js' ?? $url ?>"></script>

  <style type="text/tailwindcss">
    <?= $UI_APP['style']; ?>
</style>
</head>

<body>
  <?= $UI_APP['body']; ?>

  <script
    src="<?= APP_IS_ONLINE && check_http_status('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : app_base('resources', null, 'rel') . 'js/jquery/' . 'jquery-3.7.1.min.js' ?>"></script>
  <?php
  if (!is_file($path = app_base('resources', null, 'rel') . 'js/jquery-ui/' . 'jquery-ui-1.12.1.js') || ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path))))) / 86400)) <= 0) {

    if (!realpath($pathdir = dirname($path)))
      if (!mkdir($pathdir, 0755, true))
        $errors['DOCS'] = "$pathdir does not exist";

    $url = 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($js = curl_exec($handle)))
      file_put_contents($path, $js) or $errors['JS-JQUERY-UI'] = "$url returned empty.";
  } ?>

  <script
    src="<?= APP_IS_ONLINE && check_http_status('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js') ? 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js' : app_base('resources', null, 'rel') . 'js/jquery-ui/' . 'jquery-ui-1.12.1.js' ?>"></script>

  <script src="https://d3js.org/d3.v4.min.js"></script>

  <script>
    let isDragging = false;
    let activeWindow = null;

    function makeDraggable(windowId) {
      const windowElement = document.getElementById(windowId);
      const headerElement = windowElement.querySelector('.ui-widget-header');
      let offsetX, offsetY;

      headerElement.addEventListener('mousedown', function (event) {
        if (!isDragging) {
          // Bring the clicked window to the front
          document.body.appendChild(windowElement);
          offsetX = event.clientX - windowElement.getBoundingClientRect().left;
          offsetY = event.clientY - windowElement.getBoundingClientRect().top;
          isDragging = true;
          activeWindow = windowElement;
        }
      });

      document.addEventListener('mousemove', function (event) {
        if (isDragging && activeWindow === windowElement) {
          const left = event.clientX - offsetX;
          const top = event.clientY - offsetY;

          // Boundary restrictions
          const maxX = window.innerWidth - windowElement.clientWidth - 100;
          const maxY = window.innerHeight - windowElement.clientHeight;

          windowElement.style.left = `${Math.max(-200, Math.min(left, maxX))}px`;
          windowElement.style.top = `${Math.max(0, Math.min(top, maxY))}px`;
        }
      });

      document.addEventListener('mouseup', function () {
        if (activeWindow === windowElement) {
          isDragging = false;
          activeWindow = null;
        }
      });
    }

    makeDraggable('app_nodes-container');

    <?= $UI_APP['script']; ?>
  </script>
</body>

</html>
<?php
$return_contents = ob_get_contents();
ob_end_clean();

if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"]))
  print $return_contents;
elseif (in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'visual/nodes' && APP_DEBUG)
  return $return_contents; // Return only script if requested
else {
  //dd($_GET);
  if (isset($_GET['script'])) {
    header('Content-Type: application/javascript');
    die($UI_APP['script']);

    //echo json_encode($UI_APP, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

  }
  //return $UI_APP['script'] ?? $UI_APP;

}
//

//$UI_APP = ['style' => '', 'body' => $UI_APP['body'], 'script' => ''];