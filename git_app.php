<?php

if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=config.php'));

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../git.php') ? '../git.php' : (is_file('../config/git.php') ? '../config/git.php' : null))
    : (is_file('git.php') ? 'git.php' : (is_file('config/git.php') ? 'config/git.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=git.php'));

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../composer.php') ? '../composer.php' : (is_file('../config/composer.php') ? '../config/composer.php' : null))
    : (is_file('composer.php') ? 'composer.php' : (is_file('config/composer.php') ? 'config/composer.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=composer.php'));


/*
if ($path = realpath((!is_dir(dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR . 'config' ? dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' : (!is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'config') ? (basename(__DIR__) != 'config' ? NULL : '.') : __DIR__ . DIRECTORY_SEPARATOR . 'config'))  . DIRECTORY_SEPARATOR . 'git.php')) // realpath() | is_file('config/git.php')) 
  require_once($path);
else die(var_dump($path . ' path was not found. file=git.php'));
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


/*

function testGit()
	{
		$descriptorspec = [
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$pipes = [];
		$resource = proc_open(Git::getBin(), $descriptorspec, $pipes);

		foreach ($pipes as $pipe) {
			fclose($pipe);
		}

		$status = trim(proc_close($resource));

		return ($status != 127);
	}

$repo = Git::open('/path/to/repo');  // -or- Git::create('/path/to/repo')

$repo->add('.');

    if (is_array($files))
		$files = '"'.implode('" "', $files).'"';
    GIT_EXEC . " add $files -v";

$repo->commit('Some commit message');

    GIT_EXEC . ' commit -av -m ' . escapeshellarg($message)


$repo->push('origin', 'master');

    GIT_EXEC . " push $remote $branch"

*/


// 
}

/*
<?php ob_start(); ?>
<HTML ...>
<?php $appGit['css'] = ob_get_contents();
ob_end_clean(); ?>
*/ 

ob_start(); ?>

#app_git-container { position: absolute; display: none; top: 0; left: 0; }
#app_git-container.selected { display: block; z-index: 1; 
  /* Add your desired styling for the selected container */
  /*
  // background-color: rgb(240, 224, 198); //  240, 224, 198, .75    #FBF7F1; // rgba(240, 224, 198, .25);
  
  bg-[#FBF7F1];
  bg-opacity-75;

  font-weight: bold;
  #top { background-color: rgba(240, 224, 198, .75); }
  */
}

.app_git-frame-container { position: absolute; display: none; top:0; left: 0; width: 400px; }
.app_git-frame-container.selected { display: block; z-index: 1; }

/* #app_git-frameName == ['menu', 'init', 'status', 'config', 'commit', 'update'] */

#app_git-frameMenu {}
#app_git-frameMenuPrev {} /* composerMenuPrev */
#app_git-frameMenuNext {} /* composerMenuNext */
/*
#app_git-frameMenuConf {}
#app_git-frameMenuInstall {}
#app_git-frameMenuInit {}
#app_git-frameMenuUpdate {}
*/
#app_git-frameInit {} /* Either there is a .git directory, or go to config with .git_*ignore ... */
#app_git-frameStatus {} /* these maybe just for console results */
#app_git-frameConfig {} /* Frame */
#app_git-frameCommit {} /* same with this one */
#app_git-frameUpdate {} /* Frame */
/*
#update { backgropund-color: rgba(240, 224, 198, .75); }
#middle { backgropund-color: rgba(240, 224, 198, .75); }
#bottom { backgropund-color: rgba(240, 224, 198, .75); }
*/
.btn {
  @apply rounded-md px-2 py-1 text-center font-medium text-slate-900 shadow-sm ring-1 ring-slate-900/10 hover:bg-slate-50
}

.git-menu {
  cursor: pointer;
}

img { display: inline; }

.show { display: block; }
<?php $appGit['style'] = ob_get_contents();
ob_end_clean(); 

ob_start(); ?>
  <div id="app_git-container" class="absolute <?= (APP_SELF == __FILE__ || isset($_GET['app']) && $_GET['app'] == 'git' ? 'selected' : (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? (isset($_GET['app']) && $_GET['app'] != 'git' ? '' : 'selected') :  '')) ?>" style="position: absolute; top: 60px; left: 0; right: 0; z-index: 1; margin: 0 auto; width: 600px; background-color: rgba(255,255,255,0.8); padding: 10px;">
<div style="position: relative; margin: 0 auto; width: 404px; height: 306px; border: 3px dashed #F05033; background-color: #FBF7F1;">

      <div class="absolute" style="position: absolute; display: inline-block; width: 100%; margin: -25px 0 10px 0; border-bottom: 1px solid #000; z-index: 3;">
        <label class="git-home" style="cursor: pointer;">
          <div class="absolute" style="position: absolute; top: 0px; left: 3px;">
            <img src="resources/images/git_icon.fw.png" width="32" height="32" />
          </div>
        </label>
        <div style="display: inline; padding-left: 40px;">
          <span style="background-color: white; color: #F05033;">Git <?= (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? 'v'.substr(GIT_LATEST, 0, similar_text(GIT_LATEST, GIT_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(GIT_LATEST, similar_text(GIT_LATEST, GIT_VERSION)) . '</span>' : 'v'.GIT_VERSION ); ?></span> <span style="background-color: #0078D7; color: white;"><code class="text-sm" style="background-color: white; color: #0078D7;">$ <?= GIT_EXEC; ?></code></span>
        </div>
        
        <div style="display: inline; float: right; text-align: center; color: blue;"><code style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_git-container').style.display='none';">[X]</a></code></div> 
      </div>
      
      <div class="" style="position: relative; display: block; width: 398px; background-color: rgba(251,247,241); z-index: 2;">
        <div style="display: inline-block; text-align: left; width: 125px;">
          <div class="git-menu text-sm" style="cursor: pointer; font-weight: bold; padding-left: 25px; border: 1px solid #000;">Main Menu</div>
          <div class="text-xs" style="display: inline-block; border: 1px solid #000;">
            <a class="text-sm" id="app_git-frameMenuPrev" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (APP_ENV == 'development' ? '#!' : '') ?>"> &lt; Menu</a> | <a class="text-sm" id="app_git-frameMenuNext" href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (APP_ENV == 'development' ? '#!' : '') ?>">Init &gt;</a>
          </div>
        </div>
        <div class="absolute" style="position: absolute; display: inline-block; top: 4px; text-align: right; width: 272px; ">
          <div class="text-xs" style="display: inline-block;">
          + 1612 <a href="https://github.com/git/git/graphs/contributors">contributors</a>
          <br /><a href="http://github.com/git"><img src="resources/images/github.fw.png" title="http://github.com/git" width="18" height="18" /></a>
          <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;" href="http://git-scm.com/" title="http://git-scm.com/">http://git-scm.com/</a>
          </div>
        </div>
        <div style="clear: both;"></div>
      </div>

      <div class="absolute" style="position: absolute; top: 0; margin: 0px auto; text-align: center; height: 200px; width: 100%; background-repeat: no-repeat; <?= (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? "background-image: url('https://editablegifs.com/gifs/gifs/fireworks-1/output.gif?egv=3258'); opacity: 0.2;" : '' ) ?> z-index: 1;">
      </div>
      <div style="position: absolute; top: 0; left: 0; right: 0; margin: 10px auto; opacity: 1.0; text-align: center; cursor: pointer; z-index: 1; ">
        <img class="<?= (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? 'git-menu' : 'git-update') ?>" src="resources/images/git_logo.gif<?= /*.fw.png*/ NULL; ?>" style="" width="229" height="96" />
      </div>
      <div class="absolute" style="position: absolute; bottom: 24px; left: 0; right: 0; width: 100%; text-align: center;">
        <span style="text-decoration-line: underline; text-decoration-style: solid;">Git is a distributed version control system</span>
      </div>
      <div style="position: absolute; bottom: 0; left: 0; padding: 2px; z-index: 1;">
        <a href="https://github.com/git"><img src="resources/images/github-composer.fw.png" /></a>
      </div>
      <div class="absolute text-sm" style="position: absolute; bottom: 0; right: 0; padding: 2px; z-index: 1; "><?= '<code>Latest: </code>'; ?> <?= (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? '<span class="update" style="color: green; cursor: pointer;">' . 'v'.substr(GIT_LATEST, 0, similar_text(GIT_LATEST, GIT_VERSION)). substr(GIT_LATEST, similar_text(GIT_LATEST, GIT_VERSION))  . '</span>': 'Installed: v' . GIT_VERSION ); ?></div>
      <div style="position: relative; overflow: hidden; width: 398px; height: 286px;">

      <div id="app_git-frameMenu" class="app_git-frame-container selected absolute" style="background-color: rgb(225,196,151,.75); margin-top: 8px;">
        <!--<h3>Main Menu</h3> <h4>Update - Edit Config - Initalize - Install</h4> -->
        <div style="position: absolute; right: 10px; float: right; z-index: 1;">
          <div class="text-sm" style="display: inline-block; margin: 0 auto;">
            <form id="app_git-push" action="<?=APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
              <!-- <input type="hidden"  /> -->
              <button type="submit" name="cmd" value="push"><img src="resources/images/green_arrow.fw.png" width="20" height="25" style="cursor: pointer; margin-left: 6px;" /><br />Push</button>
            </form>
          </div>
          <div class="text-sm" style="display: inline-block; margin: 0 auto;">
            <form id="app_git-pull" action="<?=APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
              <!-- <input type="hidden"  /> -->
              <button type="submit" name="cmd" value="pull"><img src="resources/images/red_arrow.fw.png" width="20" height="25" style="cursor: pointer; margin-left: 4px;" /><br />Pull</button>
            </form>
          </div>
        </div>
        <div style="position: relative; height: 100px;">
          <div id="app_git-commit_msg-container" style="display: none; position: absolute; top: 80px; left: 25%; right: 25%;">
            <input id="app_git-commit_msg" type="text "/>
          </div>
        </div>
        <div style="display: block; margin: 10px auto; width: 100%; background-color: rgb(255,255,255,.75);">

          <div style="display: inline-block; width: 32%; text-align: right;"><img src="resources/images/git.fw.png" width="52" height="37" style=" border: 1px dashed #F05033;" /></div>
          <div style="display: inline-block; width: 32%; text-align: center; border: 1px dashed #F05033; height: 44px; padding: 7px;">
            <select id="app_git-frameSelector">
              <!-- <option value="">---</option> -->
              <option value="init" <?= (is_dir('.git') ? 'disabled' : 'selected' ); ?>>init</option>
              <option value="status">status</option>
              <option value="config">config</option>
              <option value="commit">commit</option>
            </select>
          </div>
          <div style="display: inline-block; width: 33%; padding-top: 2px;">
          <form id="app_git-cmd-selected" action action="GET">
            <button type="submit"><img src="resources/images/git_icon_selected.fw.png" width="44" height="29" style="border: 1px dashed #F05033;" /></button>
          </form>
          </div>
        </div>
        <div style="height: 35px;"></div>
      </div>

      <div id="app_git-frameInit" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
      <div style="position: absolute; right: 0; float: right; text-align: center;">
        <input id="gitInitSubmit" class="btn" type="submit" value="Init/Run" />
      </div> 
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitInitLabel" for="gitInit" style="background-color: #6781B2; color: white;">&#9650; <code>Init</code></label>
        </div>
      </div>
      <div id="gitInitForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="2" name="git[init]"><?= /* GIT_INIT_PARAMS  NULL*/ 'git init'; ?></textarea>
      </div>
    </form>
      </div>

      <div id="app_git-frameStatus" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: #6781B2; color: white;">&#9650; <code>Status</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run" />
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]"><?= /* GIT_INIT_PARAMS  NULL*/ 'git status'; ?></textarea>
      </div>
    </form>
      </div>
      
      <div id="app_git-frameConfig" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
      <div style="position: absolute; right: 0; float: right; text-align: center;">
        <button id="gitConfigSubmit" class="btn absolute" style="position: absolute; top: 0; right: 0; z-index: 2;" type="submit" value>Modify</button>
      </div> 
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(29, 100%, 42%); color: white; cursor: pointer;">&#9650; <code>GIT_PATH/.gitignore</code></label>
        </div>
      </div>
      <div id="appGitIgnoreForm" style="display:<?= (file_exists('.gitignore') ? 'inline-block' : 'none') ?>; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="2" name="git[status]"><?= /* GIT_INIT_PARAMS  NULL*/ 'git status'; ?></textarea>
      </div>
      <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(29, 100%, 42%); color: white; cursor: pointer;">&#9650; <code>GIT_PATH/.gitconfig</code></label>
        </div>
      </div>
      <div id="appGitConfigForm" style="display: <?= (exec('git config -l') == NULL ? 'inline-block' : 'none') ?>; overflow-x: hidden; overflow-y: auto; height: 180px; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
      <div style="display: none; width: 100%;">
        <input type="checkbox" name="gitConfigList" />
        <label style="font-style: italic;">git config -l</label>
        <div style="float: right;">
          <input type="checkbox" name="gitIngoreFile" 1 /> <label style="font-style:italic;">.gitignore</label>
          <input type="checkbox" name="gitConfigFile" 1/> <label style="font-style:italic;">.gitconfig</label>
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Name:</label>
        
        <div style="float: right;">
          <input name="gitConfigName" value="<?= 'Barry Dick'; ?>" />
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Email:</label>
        <div style="float: right;">
          <input name="gitConfigEmail" value="<?= 'barryd.it@gmail.com'; ?>" />
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Editor (Core):</label>
        <div style="float: right;">
          <input name="gitConfigCoreEditor" size="40" value="\&quot;C:/Program Files (x86)/Programmer's Notepad/pn.exe\&quot; --allowmulti -w" />
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Default Branch (Init)</label> 
        <div style="float: right;">
          <input name="gitConfigInitDefaultBranch" value="master" />
        </div>
      </div>
      <div style="display: inline-block; width: 100%;">
        <label>Helper (Credential)</label>
        <div style="float: right;">
          <input name="gitConfigCredentialHelper" value="manager-core" />
        </div>
      </div>
      </div>

    </form>
      </div>


      <div id="app_git-frameCommit" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color: hsl(343, 100%, 42%); color: white;">&#9650; <code>Stage / Commit</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run" />
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]"><?= /* GIT_INIT_PARAMS  NULL*/ 'git commit'; ?></textarea>
      </div>
    </form>
      </div>

      <div id="app_git-frameUpdate" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
      <div style="display: inline-block; width: 100%; margin: -10px 0 10px 0; background-color: rgb(225,196,151,.75);">
        <div class="text-sm" style="display: inline;">
          <label id="gitStatusLabel" for="gitStatus" style="background-color:hsl(89, 100%, 42%); color: white;">&#9650; <code>Update</code></label>
        </div>
        <div style="display: inline; float: right; text-align: center;">
          <input id="gitStatusSubmit" class="btn" type="submit" value="Status/Run" />
        </div> 
      </div>
      <div id="gitStatusForm" style="display: inline-block; padding: 10px; background-color: rgb(225,196,151,.75);7 border: 1px dashed #0078D7;">
        <label>Git Command</label>
        <textarea cols="40" rows="6" name="git[status]"><?= /* GIT_INIT_PARAMS  NULL*/ 'git update'; ?></textarea>
      </div>
    </form>
      </div>


      </div>
    </div>
  </div>
<?php $appGit['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

document.getElementById('app_git-push').addEventListener('submit', function(event) {
  // Prevent the default form submission
  event.preventDefault();

  // For example, you can show an alert to indicate that the form submission is disabled
  alert('Push request was made made.');

  document.getElementById('requestInput').value = 'git push https://<?= getenv('COMPOSER_TOKEN') ?>@github.com/barrydit/composer_app.git';

  // Get the element with the ID "requestSubmit"
  var requestSubmit = document.getElementById('requestSubmit');

  // Create a new click event
  var clickEvent = new MouseEvent('click', {
    bubbles: true,
    cancelable: true,
    view: window
  });

  // Dispatch the click event on the element
  requestSubmit.dispatchEvent(clickEvent);
});

// git_icon_selected  app_git-cmd-selected
document.getElementById('app_git-cmd-selected').addEventListener('submit', function(event) {
  // Prevent the default form submission
  event.preventDefault();
  
  var cmdSelect = document.getElementById('app_git-frameSelector');
  
  const git_cmd = document.getElementById('requestInput');
  const commit_msg_container = document.getElementById('app_git-commit_msg-container');
  const commit_msg = document.getElementById('app_git-commit_msg');
  
  git_cmd.value = 'git ' + cmdSelect.value;

  if (cmdSelect.value == 'commit') {
    commit_msg_container.style.display = 'block';
    
    if (commit_msg.value != '') {
      document.getElementById('requestInput').value = git_cmd.value + ' -a -m "' + commit_msg.value + '"';
    } else {
      document.getElementById('requestInput').value = git_cmd.value + ' -a -m "default message"';
      return false;    
    }
  }
  
  // Get the element with the ID "requestSubmit"
  var requestSubmit = document.getElementById('requestSubmit');

  // Create a new click event
  var clickEvent = new MouseEvent('click', {
    bubbles: true,
    cancelable: true,
    view: window
  });

  // Dispatch the click event on the element

  requestSubmit.dispatchEvent(clickEvent);

  var changePositionBtn = document.getElementById('changePositionBtn');
  const myDiv = document.getElementById('myDiv');

  if (myDiv.style.position == 'absolute')
    changePositionBtn.dispatchEvent(clickEvent);

  //show_console();

  // For example, you can show an alert to indicate that the form submission is disabled
  console.log(cmdSelect.value + ' was executed.');
  
});

document.getElementById('app_git-pull').addEventListener('submit', function(event) {
  // Prevent the default form submission
  event.preventDefault();

  // For example, you can show an alert to indicate that the form submission is disabled
  alert('Pull request was made made.');
  
  document.getElementById('requestInput').value = 'git pull';

  // Get the element with the ID "requestSubmit"
  var requestSubmit = document.getElementById('requestSubmit');

  // Create a new click event
  var clickEvent = new MouseEvent('click', {
    bubbles: true,
    cancelable: true,
    view: window
  });

  // Dispatch the click event on the element
  requestSubmit.dispatchEvent(clickEvent);
  
  //$("#requestSubmit").click();  
  
});

$(document).ready(function() {
  var git_frame_containers = $(".app_git-frame-container");
  var git_frame_totalFrames = git_frame_containers.length;
  var currentIndex = 0;
  
  $("#app_git-frameSelector").value = 0;
  
  console.log(git_frame_totalFrames + ' - total frames');
/*
  $("#appGitAuthLabel").click(function() {
    if ($('#appGitAuthJsonForm').css('display') == 'none') {
      $('#appGitAuthLabel').html("&#9650; <code>GIT_HOME/auth.json");
      $('#appGitAuthJsonForm').slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('#appGitAuthLabel').html("&#9660; <code>GIT_HOME/auth.json</code>");
      $('#appGitAuthJsonForm').slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });

  $("#appGitJsonLabel").click(function() {
    if ($('#appGitJsonForm').css('display') == 'none') {
      $('#appGitJsonLabel').html("&#9650; <code>GIT_PATH/git.json");
      $('#appGitJsonForm').slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $('#appGitJsonLabel').html("&#9660; <code>GIT_PATH/git.json</code>");
      $('#appGitJsonForm').slideUp( "slow", function() {
      // Animation complete.
      });
    }
  });

  $("#app_git-frameMenuConf").click(function() {
    currentIndex = 1;
    $("#app_git-frameMenuPrev").html('&lt; Menu');
    $("#app_git-frameMenuNext").html('Conf &gt;');
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
  });   

  $("#app_git-frameMenuInstall").click(function() {
    currentIndex = 2;
    $("#app_git-frameMenuPrev").html('&lt; Conf');
    $("#app_git-frameMenuNext").html('Init &gt;');
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
  });

  $("#app_git-frameMenuInit").click(function() {
    currentIndex = 3;
    $("#app_git-frameMenuPrev").html('&lt; Install');
    $("#app_git-frameMenuNext").html('Update &gt;');
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
  });
  
  $("#app_git-frameMenuUpdate").click(function() {
    currentIndex = 4;
    $("#app_git-frameMenuPrev").html('&lt; Init');
    $("#app_git-frameMenuNext").html('Menu &gt;');
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
  });   
*/

  $(".git_icon_selected").click(function() {
    currentIndex = 0;
    
    console.log('test');

    //git_frame_containers.removeClass("selected");
    //git_frame_containers.eq(currentIndex).addClass('selected');
  });

  $(".git-home").click(function() {
    currentIndex = 0; 
    git_frame_containers.removeClass("selected");
    //git_frame_containers.eq(currentIndex).addClass('selected');
  });

  $(".git-menu").click(function() {
    currentIndex = 0;
    $("#app_git-frameSelector").value = 0;
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
  });

  $("#app_git-frameMenuPrev").click(function() {
    if (currentIndex <= 0) currentIndex = 5;
    console.log(currentIndex + '!=' + git_frame_totalFrames);
    currentIndex--;
    if (currentIndex >= git_frame_totalFrames) {
      currentIndex = 0;
    }
    if (currentIndex == 0) {
      $("#app_git-frameMenuPrev").html('&lt; Update');
      $("#app_git-frameMenuNext").html('Conf &gt;');
    } if (currentIndex == 1) {
      $("#app_git-frameMenuPrev").html('&lt; Menu');
      $("#app_git-frameMenuNext").html('Install &gt;');
    } else if (currentIndex == 2) {
      $("#app_git-frameMenuPrev").html('&lt; Conf');
      $("#app_git-frameMenuNext").html('Init &gt;');
    } else if (currentIndex == 3) {
      $("#app_git-frameMenuPrev").html('&lt; Install');
      $("#app_git-frameMenuNext").html('Update &gt;');
    } else if (currentIndex == 4) {
      $("#app_git-frameMenuPrev").html('&lt; Init');
      $("#app_git-frameMenuNext").html('Menu &gt;');
    }

    //else 
    console.log('decided: ' + currentIndex);
    git_frame_containers.removeClass("selected");
    git_frame_containers.eq(currentIndex).addClass('selected');
    
    //currentIndex--;    
    console.log(currentIndex);
  });
    
  $("#app_git-frameMenuNext").click(function() {
    currentIndex++;
    console.log(currentIndex + '!=' + git_frame_totalFrames);
    if (currentIndex >= git_frame_totalFrames) {
      currentIndex = 0;
    }
    if (currentIndex == 0) {
      $("#app_git-frameMenuPrev").html('&lt; Update');
      $("#app_git-frameMenuNext").html('Conf &gt;');
    } else if (currentIndex == 1) {
      $("#app_git-frameMenuPrev").html('&lt; Menu');
      $("#app_git-frameMenuNext").html('Install &gt;');
    } else if (currentIndex == 2) {
      $("#app_git-frameMenuPrev").html('&lt; Conf');
      $("#app_git-frameMenuNext").html('Init &gt;');
    } else if (currentIndex == 3) {
      $("#app_git-frameMenuPrev").html('&lt; Install');
      $("#app_git-frameMenuNext").html('Update &gt;');
    } else if (currentIndex == 4) {
      $("#app_git-frameMenuPrev").html('&lt; Init');
      $("#app_git-frameMenuNext").html('Menu &gt;');
    }
    if (currentIndex < 0) currentIndex++;
    //else 
    console.log('decided: ' + currentIndex);
    git_frame_containers.removeClass("selected"); // git_frame_containers.css("z-index", 0); // Reset z-index for all elements
    git_frame_containers.eq(currentIndex).addClass('selected'); // css("z-index", git_frame_totalFrames); // Set top layer z-index
  });

  
/*
  $("#app_git-push").click(function() {
     e.preventDefault();
  });

  $("#app_git-pull").click(function() {
  
  });
  

  $("#app_git-frameSelector").change(function() {
    var selectedIndex = parseInt($(this).val(), 10);
    currentIndex = selectedIndex;

    if (currentIndex >= git_frame_totalFrames) {
      currentIndex = 0;
    }
    console.log(currentIndex + ' = currentIndex');
    $(".app_git-frame-container").removeClass("selected"); // Remove selected class from all containers
    
    if (currentIndex <= git_frame_totalFrames && currentIndex > 0) {
      $(".app_git-frame-container").eq(currentIndex).addClass("selected"); // Apply selected class to the chosen container
    }
    this.value = currentIndex;
    //
  });

  $('select').on('change', function (e) {
    var optionSelected = $("option:selected", this);
    var valueSelected = this.value;
  });
*/
});
<?php $appGit['script'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

<script src="https://cdn.tailwindcss.com"></script>

<style type="text/tailwindcss">
<?= $appGit['style']; ?>
</style>
</head>
<body>
<?= $appGit['body']; ?>

  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="resources/js/jquery/jquery.min.js"></script> -->
<script>
<?= $appGit['script']; ?>
</script>
</body>
</html>
<?php $appGit['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'git' && APP_DEBUG)
  die($appGit['html']);