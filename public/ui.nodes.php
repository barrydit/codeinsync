<?php

if (isset($_GET['json'])) {
  defined('APP_PATH') or define('APP_PATH', realpath(__DIR__ . '/../') . '/');
  defined('APP_ROOT') or define('APP_ROOT', '');
  header('Content-Type: application/json');
  /*
    //$_GET['client'] = '000-Hardy,Darrell';
    if (is_file('../bootstrap.php'))
      require_once '../bootstrap.php';
    else
      require_once 'bootstrap.php';
  */
  /**
   * Get required files from a script and return their paths
   *
   * @param string $script
   * @return array
   */
  function getRequiredFilesFromScript(string $script): array
  {
    ob_start(); // /mnt/c/www
    chdir(APP_PATH);
    require_once $script; //eval('require_once \'' . $script . '\';'); 
    ob_end_clean();
    //return get_required_files();


    //$execute = function () use ($script, &$requiredFiles) {
    //    ob_start();
    //    require $script;
    //    ob_end_clean();
    //    $requiredFiles = get_required_files();
    //};

    //$output = shell_exec('php -r "require \'' . $script . '\'; return get_required_files();"');

    return get_required_files();
    //$execute();


    //$command = escapeshellcmd("php $script");
    //$output = shell_exec($command); // Capture output if needed

  }

  /**
   * Normalize file paths by removing the base directory prefix
   *
   * @param array $files
   * @param string $baseDir
   * @return array
   */
  function normalizeFilePaths(array $files, string $baseDir): array
  {
    return array_map(
      fn($file) => str_replace($baseDir, '', $file),
      $files
    );
  }

  if (defined('APP_ROOT') && APP_ROOT != '') {
    $baseDir = APP_PATH . APP_ROOT;
    $requiredFiles = [
      //'server.php' => 'server.php',
      'public/index.php' => APP_ROOT . 'public/index.php',
      //'config/php.php' => 'config/php.php',
      'config/composer.php' => APP_ROOT . 'config/composer.php',
      //'config/git.php' => 'config/git.php',
      //'public/idx.product.php' => 'public/idx.product.php',
    ];
    $jsonData = [];
    foreach ($requiredFiles as $key => $scriptPath) {
      $jsonData[$key] = normalizeFilePaths(getRequiredFilesFromScript($scriptPath), $baseDir);
    }

  } else {
    $baseDir = APP_PATH;
    //die(getcwd());
    // Define scripts to process
    $requiredFiles = [
      'server.php' => 'server.php',
      'public/index.php' => 'public/index.php',
      //'config/php.php' => 'config/php.php',
      'config/composer.php' => 'config/composer.php',
      //'config/git.php' => 'config/git.php',
      'public/idx.product.php' => 'public/idx.product.php',
    ];
    //dd($requiredFiles);
    // Prepare JSON data
    $jsonData = [];
    foreach ($requiredFiles as $key => $scriptPath) {
      $jsonData[$key] = normalizeFilePaths(getRequiredFilesFromScript($scriptPath), $baseDir);
    }

  }

  // Ensure vendor packages are only under composer.php
  if (isset($jsonData['config/composer.php'], $jsonData['public/index.php'])) {
    $vendorPackages = array_filter(
      $jsonData['public/index.php'],
      fn($file) => str_contains($file, 'vendor/')
    );
    /*
        $jsonData['config/php.php'] = array_merge(
          $jsonData['config/php.php'],
          $vendorPackages
        );

        $jsonData['config/git.php'] = array_merge(
          $jsonData['config/git.php'],
          $vendorPackages
        );
    */
    $jsonData['config/composer.php'] = array_merge(
      $jsonData['config/composer.php'],
      $vendorPackages
    );

    $jsonData['public/index.php'] = array_values(array_diff(
      $jsonData['public/index.php'],
      $vendorPackages
    ));

    $jsonData['public/idx.product.php'] = array_values(array_diff(
      $jsonData['public/idx.product.php'],
      $vendorPackages
    ));
  }
  echo json_encode($jsonData);
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
  if (is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/ace') && empty(glob($path)))
    exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
  elseif (!is_dir($path)) {
    if (!mkdir($path, 0755, true))
      $errors['GIT-CLONE-ACE'] = ' resources/js/ace does not exist.';
    exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
  }

ob_start(); ?>
#app_nodes-container {
width : 550px;
height : 450px;
/* border: 1px solid black; */
position : absolute;
top : 60px;
left : 30%;
right : 0;
z-index : 1;
/* resize: both; Make the div resizable */
/* overflow: hidden; Hide overflow to ensure proper resizing */
}

#app_nodes-container.selected {
display : block;
z-index : 1;
resize : both; /* Make the div resizable */
overflow : hidden; /* Hide overflow to ensure proper resizing */
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
margin-top: -60px;
width : 100%;
height : calc(100% - 260px);
position : absolute;
z-index : 1;
}

.node circle {
fill : #999;
stroke : #fff;
stroke-width : 3px;
}

.link {
fill : none;
stroke : #555;
stroke-width : 1.5px;
}

.link.green {
stroke : green;
}

text {
font : 10px sans-serif;
}
<?php $app['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<div id="app_nodes-container"
  class="absolute <?= __FILE__ == get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'nodes') && !isset($_GET['path']) ? 'selected' : '' ?>"
  style="display: <?= __FILE__ == get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'nodes') ? 'block' : 'none' ?>; resize: both; overflow: hidden;">
  <div class="ui-widget-header"
    style="position: relative; display: inline-block; width: 100%; cursor: move; border-bottom: 1px solid #000;background-color: #FFF;">
    <label class="nodes-home" style="cursor: pointer;">
      <div class="" style="position: relative; display: inline-block; top: 0; left: 0;">
        <img src="resources/images/ace_editor_icon.png" width="32" height="32" />
      </div>
    </label>
    <div style="display: inline;">
      <span style="background-color: #38B1FF; color: #FFF; margin-top: 10px;">Nodes
        <?= /* (version_compare(NPM_LATEST, NPM_VERSION, '>') != 0 ? 'v'.substr(NPM_LATEST, 0, similar_text(NPM_LATEST, NPM_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(NPM_LATEST, similar_text(NPM_LATEST, NPM_VERSION)) . '</span>' : 'v'.NPM_VERSION ); */ NULL; ?></span>
      <span style="background-color: #0078D7; color: white;"><code id="AceEditorVersionBox" class="text-sm"
          style="background-color: white; color: #0078D7;"></code></span>
    </div>

    <div style="display: inline; float: right; text-align: center; color: blue; z-index: -1;"><code
        style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_nodes-container').style.display='none';">[X]</a></code>
    </div>
  </div>

  <div id=""
    style="position: relative; width: 100%; height: 100%; border: 3px dashed #38B1FF; background-color: rgba(56,177,255,0.6);">

    <div class="ui-widget-content"
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
          + 478 <a href="https://github.com/ajaxorg/ace/graphs/contributors">contributors</a>
          <br /><!-- a href="https://github.com/ajaxorg"><img src="resources/images/node.js.png" title="https://github.com/nodejs" width="18" height="18" /></a -->
          <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;"
            href="https://ace.c9.io/" title="https://ace.c9.io/">https://ace.c9.io/</a>
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
*/ NULL; ?>

    </div>



    <div style="position: relative; margin: 0 auto; width: calc(100% - 2px); height: 100%; background-color: #fff;">
      <div id="visualization"></div>
    </div>

    <div id="app_nodes-frameInit" class="app_nodes-frame-container absolute" style="overflow: hidden; height: 270px;">
      <!--
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
-->
    </div>

    <div id="app_nodes-frameExtra" style="position: relative; width: 100%; height: 100%; border: 1px #000 solid;">

    </div>
  </div>
</div>

<?php $app['body'] = ob_get_contents();
ob_end_clean();

if (false) { ?>
  <script type="text/javascript">
  <?php }
ob_start(); ?>

  fetch('<?= basename(__FILE__) ?>?json')
    .then(response => response.json())
    .then(data => createVisualization(data));

  function createVisualization(data) {
    const width = 940, height = 680;

    const nodes = [];
    const links = [];
    const fileIndex = {};

    let index = 0;

    function addNode(name, group) {
      if (!fileIndex[name]) {
        nodes.push({ name, group, index });
        fileIndex[name] = index++;
      }
    }

    function addLink(source, target, color) {
      links.push({ source: fileIndex[source], target: fileIndex[target], color });
    }

    Object.keys(data).forEach((file, i) => {
      addNode(file, i + 1);
      data[file].forEach(childFile => addNode(childFile, i + 1));
    });

    Object.keys(data).forEach(file => {
      data[file].forEach(childFile => {
        const color = (data['server.php'].includes(childFile) && data['public/index.php'].includes(childFile)) ? 'green' : '';
        addLink(file, childFile, color);
      });
    });

    const svg = d3.select("#visualization")
      .append("svg")
      .attr("width", width)
      .attr("height", height);

    const simulation = d3.forceSimulation(nodes)
      .force("link", d3.forceLink(links).id(d => d.index).distance(100))
      .force("charge", d3.forceManyBody().strength(-300))
      .force("center", d3.forceCenter(width / 2, height / 2));

    const link = svg.append("g")
      .attr("class", "links")
      .selectAll("line")
      .data(links)
      .enter().append("line")
      .attr("class", d => `link ${d.color}`);

    const node = svg.append("g")
      .attr("class", "nodes")
      .selectAll("g")
      .data(nodes)
      .enter().append("g");

    node.append("circle")
      .attr("r", 10);

    node.append("text")
      .text(d => d.name)
      .attr("x", 12)
      .attr("y", 3);

    simulation
      .nodes(nodes)
      .on("tick", ticked);

    simulation.force("link")
      .links(links);

    function ticked() {
      link
        .attr("x1", d => d.source.x)
        .attr("y1", d => d.source.y)
        .attr("x2", d => d.target.x)
        .attr("y2", d => d.target.y);

      node
        .attr("transform", d => `translate(${d.x},${d.y})`);
    }
  }

  $("#app_nodes-container").resizable({ // , #ui_ace_editor
    alsoResize: "#visualization"
  });

  <?php $app['script'] = ob_get_contents();
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
  is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/') or mkdir($path, 0755, true);
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
    <?= $app['style']; ?>
</style>
</head>

<body>
  <?= $app['body']; ?>

  <script
    src="<?= check_http_status('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : APP_BASE['resources'] . 'js/jquery/' . 'jquery-3.7.1.min.js' ?>"></script>
  <?php
  if (!is_file($path = APP_PATH . APP_BASE['resources'] . 'js/jquery-ui/' . 'jquery-ui-1.12.1.js') || ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path))))) / 86400)) <= 0) {
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
    src="<?= check_http_status('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js') ? 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js' : APP_BASE['resources'] . 'js/jquery-ui/' . 'jquery-ui-1.12.1.js' ?>"></script>

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

    <?= $app['script']; ?>
  </script>
</body>

</html>
<?php
$return_contents = ob_get_contents();

ob_end_clean();

if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"]))
  print $return_contents;
elseif (in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'nodes' && APP_DEBUG)
  return $return_contents;
else
  return $app;
