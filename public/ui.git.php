<?php

switch (__FILE__) {
  case get_required_files()[0]:
    if ($path = (basename(getcwd()) == 'public') ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '') require_once $path;
    else die(var_dump("$path path was not found. file=config.php"));

    break;
  default:
}

if (is_file($path = APP_PATH . APP_BASE['config'] . 'git.php') ? $path : '' )
  require_once $path; 
else die(var_dump("$path path was not found. file=git.php"));

/* https://stackoverflow.com/questions/73026623/how-to-ignore-or-permanently-block-the-files-which-contain-date-or-datetime-in */


/*
if ($path = realpath((!is_dir(dirname(__DIR__, 1)) . DIRECTORY_SEPARATOR . 'config' ? dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'config' : (!is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'config') ? (basename(__DIR__) != 'config' ? NULL : '.') : __DIR__ . DIRECTORY_SEPARATOR . 'config'))  . DIRECTORY_SEPARATOR . 'git.php')) // realpath() | is_file('config/git.php')) 
  require_once($path);
else die(var_dump($path . ' path was not found. file=git.php'));
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


/*
git reset filename   (unstage a specific file)

git branch
  -m   oldBranch newBranch   (Renaming a git branch)
  -d   Safe deletion
  -D   Forceful deletion

git commit -am "Default message"

git checkout -b branchName
*/

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
<?php $app['css'] = ob_get_contents();
ob_end_clean(); ?>
*/ 

ob_start(); ?>

#app_git-container { position: absolute; display: none; top: 60px; margin: 0 auto; left: 50%; right: 50%;  }
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


<?php $app['style'] = ob_get_contents();
ob_end_clean(); 

ob_start();
!defined('GIT_VERSION') and define('GIT_VERSION', '1.0.0');
!defined('GIT_LATEST') and define('GIT_LATEST', GIT_VERSION);
?>
  <div id="app_git-container" class="absolute <?= (__FILE__ ==  get_required_files()[0] || isset($_GET['app']) && $_GET['app'] == 'git' ? 'selected' : (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? (isset($_GET['app']) && $_GET['app'] != 'git' ? '' : '') :  '')) ?>" style="z-index: 1; width: 424px; background-color: rgba(255,255,255,0.8); padding: 10px;">
<div style="position: relative; margin: 0 auto; width: 404px; height: 306px; border: 3px dashed #F05033; background-color: #FBF7F1;">

      <div class="absolute ui-widget-header" style="position: absolute; display: inline-block; width: 100%; height: 25px; margin: -50px 0 25px 0; padding: 24px 0; border-bottom: 1px solid #000; z-index: 3;">
        <label class="git-home" style="cursor: pointer;">
          <div class="" style="position: relative; display: inline-block; top: 0; left: 0; margin-top: -5px;">
            <img src="resources/images/git_icon.fw.png" width="32" height="32" />
          </div>
        </label>
        <div style="display: inline;">
          <span style="background-color: white; color: #F05033;">Git <?= (version_compare(GIT_LATEST, GIT_VERSION, '>') != 0 ? 'v'.substr(GIT_LATEST, 0, similar_text(GIT_LATEST, GIT_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(GIT_LATEST, similar_text(GIT_LATEST, GIT_VERSION)) . '</span>' : 'v'.GIT_VERSION ) . ' '; ?></span><span style="background-color: #0078D7; color: white;"><code class="text-sm" style="background-color: white; color: #0078D7;">$ <?= (defined('GIT_EXEC') ? GIT_EXEC : null); ?></code></span>
        </div>
        
        <div style="display: inline; float: right; text-align: center; color: blue;"><code style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_git-container').style.display='none';">[X]</a></code></div> 
      </div>
      
      <div class="ui-widget-content" style="position: relative; display: block; width: 398px; background-color: rgba(251,247,241); z-index: 2;">
        <div style="display: inline-block; text-align: left; width: 125px;">
          <div class="git-menu text-sm" style="cursor: pointer; font-weight: bold; padding-left: 25px; border: 1px solid #000;">Main Menu</div>
          <div class="text-xs" style="display: inline-block; border: 1px solid #000;">
            <a class="text-sm" id="app_git-frameMenuPrev" href="<?= /*(!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . */ (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '#') ?>"> &lt; Menu</a> | <a class="text-sm" id="app_git-frameMenuNext" href="<?= /*(!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . */ (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '#') ?>">Init &gt;</a>
          </div>
        </div>
        <div class="absolute" style="position: absolute; display: inline-block; top: 4px; text-align: right; width: 272px; ">
          <div class="text-xs" style="display: inline-block;">
          + 1626 <a href="https://github.com/git/git/graphs/contributors">contributors</a>
          <br /><a href="http://github.com/git"><img src="resources/images/github.fw.png" title="http://github.com/git" width="18" height="18" /></a>
          <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;" href="http://git-scm.com/" title="http://git-scm.com/" target="_blank">http://git-scm.com/</a>
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
            <form class="app_git-push" action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'git')) . (defined('APP_ENV') && in_array(APP_ENV, ['development', 'production']) ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
              <!-- <input type="hidden"  /> -->
              <button type="submit" name="cmd" value="push"><img src="resources/images/green_arrow.fw.png" width="20" height="25" style="cursor: pointer; margin-left: 6px;" /><br />Push</button>
            </form>
          </div>
          <div class="text-sm" style="display: inline-block; margin: 0 auto;">
            <form class="app_git-pull" action="<?=APP_URL_BASE . '?' . http_build_query(APP_QUERY + array( 'app' => 'git')) . (defined('APP_ENV') && in_array(APP_ENV, ['development', 'production']) ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
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
          <div style="position: absolute; top: 0; left: 10px;"><img src="resources/images/oauth-token.gif" style="cursor: pointer;" width="20" height="23" alt="Git token" title="OAuth Token" onclick="document.getElementById('app_git-oauth').style.display='block';"/></div>
          <div style="position: absolute; top: 28px; left: 10px; border: 1px dashed #000; height: 84px; overflow-x: auto;" class="text-xs">
            <div style="position: fixed; font-weight: bold; color: #FFF; background-color: #B0B0B0;">Git Commands</div><br />
            <code class="text-xs">
            <a id="app_git-help-cmd" href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (defined('APP_ENV') && in_array(APP_ENV, ['development', 'production']) ? '#!' : '') ?>" onclick="">git <span style="color: red;">[Help]</span></a><br />
            <a id="app_git-add-cmd" href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (defined('APP_ENV') && in_array(APP_ENV, ['development', 'production']) ? '#!' : '') ?>" onclick="">git add .</a><br />
            <a id="app_git-remote-cmd" href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (defined('APP_ENV') && in_array(APP_ENV, ['development', 'production']) ? '#!' : '') ?>" onclick="">git remote -v</a><br />
            <a id="app_git-commit-cmd" href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (defined('APP_ENV') && in_array(APP_ENV, ['development', 'production']) ? '#!' : '') ?>">git commit -am "&lt;detail message&gt;"</a><br />
            <a id="app_git-clone-cmd" href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (defined('APP_ENV') && in_array(APP_ENV, ['development', 'production']) ? '#!' : '') ?>">git clone &lt;URL&gt;</a><br />
            <a>Testing Again</a><br />
            </code>
          </div>

          <div id="app_git-commit-msg" style="position: absolute; display: none; top: 30px; left: 20%; right: 50%; border: 1px solid #000;">
            <div style="position: absolute; top: -20px; left: -20px; color: red; font-weight: bold;">
              <a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_git-commit-msg').style.display='none';">[X]</a>
            </div>
            <textarea id="app_git-commit-input" type="text" placeholder="commit message" size="30"></textarea>
          </div>

          <div id="app_git-oauth" style="position: absolute; display: none; top: 30px; left: 20%; right: 50%; border: 1px solid #000;">
            <div style="position: absolute; top: -20px; left: -20px; color: red; font-weight: bold;">
              <a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_git-oauth').style.display='none';">[X]</a>
            </div>
            <input id="app_git-oauth-input" type="text" placeholder="ghp_54nj8sA53dZi9jgf..." size="26" />
          </div>

          <div id="app_git-clone-url" style="position: absolute; display: none; top: 80px; left: 20%; right: 50%; border: 1px solid #000;">
            <div style="position: absolute; top: -20px; left: -20px; color: red; font-weight: bold;">
              <a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_git-clone-url').style.display='none';">[X]</a>
            </div>
            <input id="app_git-clone-url-input" type="text" placeholder="https://github.com/barrydit/....git" size="26" />
          </div>

          <div style="display: inline-block; width: 32%; text-align: right;"><img src="resources/images/git.fw.png" width="52" height="37" style=" border: 1px dashed #F05033;" /></div>
          <div style="display: inline-block; width: 32%; text-align: center; border: 1px dashed #F05033; height: 44px; padding: 7px;">
            <select id="app_git-frameSelector">
              <!-- <option value="">---</option> -->
              <option value="init" <?= (is_dir(APP_PATH . APP_ROOT . '.git') ? 'disabled' : 'selected' ); ?>>init</option>
              <option value="status">status</option>
              <option value="config">config</option>
              <option value="commit">commit</option>
            </select>
          </div>
          <div style="display: inline-block; width: 33%; padding-top: 2px;">
          <form id="app_git-cmd-selected" method="GET">
            <button type="submit"><img src="resources/images/git_icon_selected.fw.png" width="44" height="29" style="border: 1px dashed #F05033;" /></button>
          </form>
          </div>
        </div>
        <div style="height: 35px;"></div>
      </div>

      <div id="app_git-frameInit" class="app_git-frame-container absolute" style="overflow: hidden; height: 270px;">
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
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
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
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
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
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
          <input type="checkbox" name="gitConfigFile" 1 /> <label style="font-style:italic;">.gitconfig</label>
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
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
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
    <form autocomplete="off" spellcheck="false" action="?<?=http_build_query(APP_QUERY + array( 'app' => 'git')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=git' */ ?>" method="POST">
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
<?php $app['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

// Select the element
const element = document.getElementById('app_git-container');

// Create a MutationObserver instance
const mutation_observer = new MutationObserver((mutationsList, observer) => {
    for(const mutation of mutationsList) {
        if (mutation.attributeName === 'style') {
            const display = element.style.display;
            if (display === 'block' && !isDragging) {
                // Trigger your action here

                // Get the <select> element
                const selectElement = document.getElementById('app_git-frameSelector');

                // Get the selected value
                const selectedValue = selectElement.value;

                // Log the selected value to the console
                console.log('Selected value:', selectedValue);
                if (confirm('Run Git ' + selectedValue.charAt(0).toUpperCase() + selectedValue.slice(1) + '?')) {
                    // User clicked OK
                    $('#requestInput').val('git ' + selectedValue );
                    $('#requestSubmit').click();
                }
                console.log('Element is displayed');
            }
        }
    }
});

// Start observing the target node for configured mutations
mutation_observer.observe(element, { attributes: true });


var appGitPushElements = document.getElementsByClassName('app_git-push'); // getElementById('app_git-push')
for (var i = 0; i < appGitPushElements.length; i++) {
    appGitPushElements[i].addEventListener('click', function() {

  // Prevent the default form submission
  event.preventDefault();

  // For example, you can show an alert to indicate that the form submission is disabled
  alert('Push request was made.');

  document.getElementById('requestInput').value = 'git push https://<?= getenv('COMPOSER_TOKEN') ?>@github.com/barrydit/<?= (isset($_GET['domain']) ? $_GET['domain']: (isset($_GET['project']) ? $_GET['project']: 'CodeHub.git')) ?>';

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
}

var appGitPullElements = document.getElementsByClassName('app_git-pull'); // getElementById('app_git-pull')
for (var i = 0; i < appGitPullElements.length; i++) {
    appGitPullElements[i].addEventListener('click', function() {

  // Prevent the default form submission
  event.preventDefault();

  // For example, you can show an alert to indicate that the form submission is disabled
  alert('Pull request was made.');
  
  document.getElementById('requestInput').value = 'git pull';

  // Get the element with the ID "requestSubmit"
  var requestSubmit = document.getElementById('requestSubmit');
  
  document.getElementById('app_git-container').style.display='block';

  // Create a new click event
  var clickEvent = new MouseEvent('click', {
    bubbles: true,
    cancelable: true,
    view: window
  });

  // Dispatch the click event on the element
  requestSubmit.dispatchEvent(clickEvent); //$("#requestSubmit").click();  
    });
}


// git_icon_selected  app_git-cmd-selected
document.getElementById('app_git-cmd-selected').addEventListener('submit', function(event) {
  // Prevent the default form submission
  event.preventDefault();
  
  var cmdSelect = document.getElementById('app_git-frameSelector');
  
  const git_cmd = document.getElementById('requestInput');
  const commit_msg_container = document.getElementById('app_git-commit_msg-container');
  const commit_msg = document.getElementById('app_git-commit_msg');
  
  git_cmd.value = 'git ' + cmdSelect.value;


  if (cmdSelect.value == 'config') {
    console.log('Testing...');
  } else if (cmdSelect.value == 'commit') {
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

//  var changePositionBtn = document.getElementById('changePositionBtn');
//  const myDiv = document.getElementById('myDiv');

//  if (myDiv.style.position == 'absolute')
//    changePositionBtn.dispatchEvent(clickEvent);

  isFixed = true;
  show_console();

  // For example, you can show an alert to indicate that the form submission is disabled
  console.log(cmdSelect.value + ' was executed.');
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
<?php $app['script'] = ob_get_contents();
ob_end_clean();


//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'git' && APP_DEBUG) {

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
// (check_http_200('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_WWW . 'resources/js/tailwindcss-3.3.5.js')?
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

  <script src="<?= (check_http_200('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : $path . 'jquery-3.7.1.min.js') ?>"></script>
  <!-- You need to include jQueryUI for the extended easing options. -->
<?php /* https://stackoverflow.com/questions/12592279/typeerror-p-easingthis-easing-is-not-a-function */ ?>
  <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script> <!-- Uncaught ReferenceError: jQuery is not defined -->

  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <!-- script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script -->
  <!-- <script src="resources/js/jquery/jquery.min.js"></script> -->
<script>
<?= $app['script']; ?>
</script>
</body>
</html>
<?php return ob_get_contents(); 
  // ob_end_clean();
} else {

  return $app;
}