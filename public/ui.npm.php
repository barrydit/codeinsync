<?php

if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '') require_once $path;
  else die(var_dump("$path path was not found. file=config.php"));

ob_start(); ?>

#app_npm-container { position: absolute; display: none; top: 60px; margin: 0 auto; left: 50%; right: 50%;  }
#app_npm-container.selected { display: block; z-index: 1; 
  /* Add your desired styling for the selected container */
  /*
  // background-color: rgb(240, 224, 198); //  240, 224, 198, .75    #FBF7F1; // rgba(240, 224, 198, .25);
  
  bg-[#FBF7F1];
  bg-opacity-75;

  font-weight: bold;
  #top { background-color: rgba(240, 224, 198, .75); }
  */
}


img { display: inline; }


<?php $app['style'] = ob_get_contents();
ob_end_clean(); 

ob_start(); ?>
  <div id="app_npm-container" class="absolute <?= (__FILE__ ==  get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'npm') && !isset($_GET['path']) ? 'selected' : '') ?>" style="z-index: 1; width: 424px; background-color: rgba(255,255,255,0.8); padding: 10px;">
    <div style="position: relative; margin: 0 auto; width: 404px; height: 306px; border: 3px dashed #DD0000; background-color: #FBF7F1;">
      <div class="absolute ui-widget-header" style="position: absolute; display: inline-block; width: 100%; height: 25px; margin: -50px 0 25px 0; padding: 24px 0; border-bottom: 1px solid #000; z-index: 3;">
        <label class="npm-home" style="cursor: pointer;">
          <div class="" style="position: relative; display: inline-block; top: 0; left: 0; margin-top: -5px;">
            <img src="resources/images/npm_icon.png" width="32" height="32" />
          </div>
        </label>
        <div style="display: inline;">
          <span style="background-color: white; color: #DD0000;">Node.js <?= /* (version_compare(NPM_LATEST, NPM_VERSION, '>') != 0 ? 'v'.substr(NPM_LATEST, 0, similar_text(NPM_LATEST, NPM_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(NPM_LATEST, similar_text(NPM_LATEST, NPM_VERSION)) . '</span>' : 'v'.NPM_VERSION ); */ NULL; ?></span> <span style="background-color: #0078D7; color: white;"><code class="text-sm" style="background-color: white; color: #0078D7;">$ <?= (defined('NPM_EXEC') ? NPM_EXEC : null); ?></code></span>
        </div>
        
        <div style="display: inline; float: right; text-align: center; color: blue;"><code style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_npm-container').style.display='none';">[X]</a></code></div> 
      </div>
      
      <div class=" ui-widget-content" style="position: relative; display: block; width: 398px; background-color: rgba(251,247,241); z-index: 2;">

        <div style="display: inline-block; text-align: left; width: 125px;">
          <div class="npm-menu text-sm" style="cursor: pointer; font-weight: bold; padding-left: 25px; border: 1px solid #000;">Main Menu</div>
          <div class="text-xs" style="display: inline-block; border: 1px solid #000;">
            <a class="text-sm" id="app_npm-frameMenuPrev" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '#') ?>"> &lt; Menu</a> | <a class="text-sm" id="app_npm-frameMenuNext" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '#') ?>">Init &gt;</a>
          </div>
        </div>
        <div class="absolute" style="position: absolute; display: inline-block; top: 4px; text-align: right; width: 272px; ">
          <div class="text-xs" style="display: inline-block;">
          + 3357 <a href="https://github.com/nodejs/node/graphs/contributors">contributors</a>
          <br /><a href="https://github.com/nodejs"><img src="resources/images/node.js.png" title="https://github.com/nodejs" width="18" height="18" /></a>
          <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;" href="https://nodejs.org/" title="https://nodejs.org/">https://nodejs.org/</a>
          </div>
        </div>
        <div style="clear: both;"></div>
      </div>

      <div class="" style="position: absolute; top: 0; left: 0; right: 0; margin: 10px auto; opacity: 1.0; text-align: center; cursor: pointer; z-index: 1;">
        <img class="npm-menu" src="resources/images/node_npm.fw.png" style="margin-top: 45px;" width="150" height="198" />
      </div>


<div style="position: relative; overflow: hidden; width: 398px; height: 256px;">

      <div id="app_git-frameMenu" class="app_git-frame-container absolute selected" style="background-color: rgb(225,196,151,.75); margin-top: 8px; height: 100%;">

        <div style="display: block; margin: 10px auto; width: 100%; background-color: rgb(255,255,255,.75);">

<div class="text-sm" style="display: inline-block; width: 100%;"><span>Dependencies (install):</span>
        <div style="position: relative; float: right;">
          <input id="composerReqPkg" type="text" title="Enter Text and onSelect" list="composerReqPkgs" placeholder="" value="" onselect="get_package(this);">
          <datalist id="composerReqPkgs">
            <option value=""></option>
          </datalist>
      </div>

        <div style="clear: both;"></div>
<input type="checkbox" />
<input type="text" value="jquery:^3.7.1" /><br />
<input type="checkbox" />
<input type="text" value="npm:^10.2.3" /><br /><br />
Dev. Dependencies<br />
<input type="checkbox" />
<input type="text" value="@babel/core:^7.23.2" /><br />
<input type="checkbox" />
<input type="text" value="@babel/preset-env:^7.23.2" size="30" /><br />
<input type="checkbox" />
<input type="text" value="babel-loader:^9.1.3" /><br />
<input type="checkbox" />
<input type="text" value="webpack:^5.89.0" /><br />
<input type="checkbox" />
<input type="text" value="webpack-cli:^5.1.4" />
          </div>

        </div>
        <div style="height: 35px;"></div>
      </div>

      <div id="app_git-frameInit" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
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

      <div id="app_git-frameStatus" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?app=git#!" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: #6781B2; color: white;">? <code>Status</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run">
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]">git status</textarea>
      </div>
    </form>
      </div>
      
      <div id="app_git-frameConfig" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?app=git#!" method="POST">
      <div style="position: absolute; right: 0; float: right; text-align: center;">
        <button id="gitConfigSubmit" class="btn absolute" style="position: absolute; top: 0; right: 0; z-index: 2;" type="submit" value="">Modify</button>
      </div> 
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(29, 100%, 42%); color: white; cursor: pointer;">? <code>GIT_PATH/.gitignore</code></label>
        </div>
      </div>
      <div id="appGitIgnoreForm" style="display:inline-block; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="2" name="git[status]">git status</textarea>
      </div>
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(29, 100%, 42%); color: white; cursor: pointer;">? <code>GIT_PATH/.gitconfig</code></label>
        </div>
      </div>
      <div id="appGitConfigForm" style="display: inline-block; overflow-x: hidden; overflow-y: auto; height: 180px; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
      <div style="display: none; width: 100%;">
        <input type="checkbox" name="gitConfigList">
        <label style="font-style: italic;">git config -l</label>
        <div style="float: right;">
          <input type="checkbox" name="gitIngoreFile" 1=""> <label style="font-style:italic;">.gitignore</label>
          <input type="checkbox" name="gitConfigFile" 1=""> <label style="font-style:italic;">.gitconfig</label>
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Name:</label>
        
        <div style="float: right;">
          <input name="gitConfigName" value="Barry Dick">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Email:</label>
        <div style="float: right;">
          <input name="gitConfigEmail" value="barryd.it@gmail.com">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Editor (Core):</label>
        <div style="float: right;">
          <input name="gitConfigCoreEditor" size="40" value="\&quot;C:/Program Files (x86)/Programmer's Notepad/pn.exe\&quot; --allowmulti -w">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Default Branch (Init)</label> 
        <div style="float: right;">
          <input name="gitConfigInitDefaultBranch" value="master">
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Helper (Credential)</label>
        <div style="float: right;">
          <input name="gitConfigCredentialHelper" value="manager-core">
        </div>
      </div>
      </div>

    </form>
      </div>


      <div id="app_git-frameCommit" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?app=git#!" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(343, 100%, 42%); color: white;">? <code>Stage / Commit</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run">
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]">git commit</textarea>
      </div>
    </form>
      </div>

      <div id="app_git-frameUpdate" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?app=git#!" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color:hsl(89, 100%, 42%); color: white;">? <code>Update</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run">
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]">git update</textarea>
      </div>
    </form>
      </div>


      </div>
    </div>
  </div>
<?php $app['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>


<?php $app['script'] = ob_get_contents();
ob_end_clean();



//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'npm' && APP_DEBUG) {
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache"); 
ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" /-->

<?php
// (check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_WWW . 'resources/js/tailwindcss-3.3.5.js')?
is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/') or mkdir($path, 0755, true);
if (is_file($path . 'tailwindcss-3.3.5.js')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path . 'tailwindcss-3.3.5.js'))))) / 86400)) <= 0 ) {
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

      function makeDraggable(windowId) {
        const windowElement = document.getElementById(windowId);
        const headerElement = windowElement.querySelector('.ui-widget-header');
      
        let isDragging = false;
        let offsetX, offsetY;
      
        headerElement.addEventListener('mousedown', function(event) {
          // Bring the clicked window to the front
          document.body.appendChild(windowElement);
          offsetX = event.clientX - windowElement.getBoundingClientRect().left;
          offsetY = event.clientY - windowElement.getBoundingClientRect().top;
          isDragging = true;
        });

        document.addEventListener('mousemove', function(event) {
          if (isDragging) {
            const left = event.clientX - offsetX;
            const top = event.clientY - offsetY;
            //windowElement.style.left = `${left}px`;
            //windowElement.style.top = `${top}px`;

            // Boundary restrictions
            const maxX = window.innerWidth - windowElement.clientWidth - 100;
            const maxY = window.innerHeight - windowElement.clientHeight;

            windowElement.style.left = `${Math.max(-200, Math.min(left, maxX))}px`;
            windowElement.style.top = `${Math.max(0, Math.min(top, maxY))}px`;
            
            console.log('Left: ' + windowElement.style.left + '    Top: ' + windowElement.style.top);
          }
        });

        document.addEventListener('mouseup', function() {
          isDragging = false;
        });
      }

      makeDraggable('app_npm-container');

<?= $app['script']; ?>
</script>
</body>
</html>
<?php $contents = ob_get_contents(); 
  ob_end_clean(); 
  return $contents;

} else {
  return $app;
}