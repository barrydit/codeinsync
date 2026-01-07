<?php
// app/tools/registry/composer.php
declare(strict_types=1);

if (!defined('APP_BOOTSTRAPPED')) {
  require_once dirname(__DIR__, 3) . '/bootstrap/bootstrap.php';
}

global $errors, $asset;

defined('APP_PATH') || define('APP_PATH', dirname(__DIR__, 3) . '/');
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . 'config/');

// const APP_ROOT = '123';

// Ensure bootstrap has run (defines env/paths/url/app and helpers)
if (!defined('APP_BOOTSTRAPPED')) {
  require_once APP_PATH . 'bootstrap/bootstrap.php';
}

// Only Composer-specific constants are missing from the minimal chain
$composerConsts = CONFIG_PATH . 'constants.composer.php';
if (is_file($composerConsts)) {
  require_once $composerConsts;
}
$composerFuncs = CONFIG_PATH . 'functions.composer.php';
if (is_file($composerFuncs)) {
  require_once $composerFuncs;
}


//dd($cfg = composer_json(true));               // array
//$pkg = composer_json_get('name', '(none)');
//$req = composer_json_get('require', []);  // array of requires

// Ensure a usable $latest value (no fatal if constant absent)
if (!defined('COMPOSER_LATEST')) {
  // uses the helper we added in constants.composer.php
  [$v, $errs] = composer_latest_cached(false); // or true to force refresh
  if ($v) {
    define('COMPOSER_LATEST', $v);
  } else {
    define('COMPOSER_LATEST', 'unknown'); // safe fallback
  }
}

// -----------------------------------------------------------------------------

// Ensure COMPOSER_BIN or COMPOSER_PHAR is defined (best-effort, non-fatal)

$app_id = 'tools/registry/composer';           // full path-style id

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

if (!isset($composer_obj)) {
  if (function_exists('load_feature_constants')) {
    load_feature_constants('composer');
  }
  $composer_obj = json_decode(COMPOSER_JSON ?? '{}');
}

$COMPOSER_JSON = json_encode($composer_obj); // @file_get_contents("composer.json");
$COMPOSER = json_decode($COMPOSER_JSON, true);

// define("COMPOSER_JSON_RAW", $COMPOSER_JSON);
//!defined('COMPOSER') && define("COMPOSER", ['json' => $composer_obj]); // ← This is what’s missing!

/*
<?php ob_start(); ?>
<HTML ...>
<?php $UI_APP['css'] = ob_get_contents(); ?>
*/

ob_start(); ?>

<?= $selector ?> {
position : absolute;
display : block;
left : 250px;
top : 125px;
resize : both; /* Make the div resizable */
margin : 0 auto;
/* z-index : 1; */
}

<?= $selector ?>.selected {
display : block;
resize : both; /* Make the div resizable */
z-index : 1;
/* Add your desired styling for the selected container */
/*
// background-color: rgb(240, 224, 198); // 240, 224, 198, .75 #FBF7F1; // rgba(240, 224, 198, .25);

bg-[#FBF7F1];
bg-opacity-75;

font-weight: bold;
#top { background-color: rgba(240, 224, 198, .75); }
*/
}

.app_composer-frame-container {
position : absolute;
display : none;
top : 0;
left : 0;
width : 420px;
}
.app_composer-frame-container.selected {
display : block;
z-index : 1;
}

/* #app_composer-frameName == ['menu', 'conf', 'install', 'init', 'update'] */

#app_composer-frameMenu {}
#app_composer-frameMenuPrev {} /* composerMenuPrev */
#app_composer-frameMenuNext {} /* composerMenuNext */

#app_composer-frameMenuConf {}
#app_composer-frameMenuInstall {}
#app_composer-frameMenuInit {}
#app_composer-frameMenuUpdate {}

#app_composer-frameConf {}
#app_composer-frameInstall {}
#app_composer-frameInit {}
#app_composer-frameUpdate {}

#update { backgropund-color: rgba(240, 224, 198, .75); }
#middle { backgropund-color: rgba(240, 224, 198, .75); }
#bottom { backgropund-color: rgba(240, 224, 198, .75); }

.btn {
@apply rounded-md px-2 py-1 text-center font-medium text-slate-900 shadow-sm ring-1 ring-slate-900/10 hover:bg-slate-50
}

.composer-menu {
cursor : pointer;
}
.dropbtn {
background-color : #3498DB;
color : white;
padding : 2px 7px;
font-size : 14px;
border : none;
cursor : pointer;
}
.dropbtn:hover, .dropbtn:focus {
background-color : #2980B9;
}
.dropdown {
position : relative;
display : inline-block;
float : right;
z-index : 1;
}
.dropdown-content {
display : none;
position : absolute;
background-color : #f1f1f1;
min-width : 160px;
margin : -100px;
overflow : auto;
}
.dropdown-content a {
color : black;
padding : 8px 12px;
text-decoration : none;
display : block;
}
.dropdown a:hover {
background-color : #ddd;
}
.show {
display : block;
}
img {
display : inline;
}
<?php $UI_APP['style'] = ob_get_contents();
ob_end_clean();


// dd(glob('*')); dd(getcwd());

//(APP_SELF == __FILE__ || isset($_GET['app']) && $_GET['app'] == 'composer' ? 'selected' : (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? (isset($_GET['app']) && $_GET['app'] != 'composer' ? '' : 'selected') :  '')) 


ob_start(); ?>

<div id="" class="fixed window-header"
  style="position: fixed; display: inline-block; width: 445px; height: 25px; cursor: move; margin: -45px 0 25px 0; padding: 10px 0 10px 0; border-bottom: 1px solid #000; z-index: 3;"
  data-drag-handle="true">
  <label class="composer-home" style="cursor: pointer;">
    <div class="absolute"
      style="position: relative; float: left; display: inline-block; top: 0; left: 0; margin-top: -5px;">
      <img src="assets/images/composer_icon.png" width="32" height="40" />
    </div>
  </label>
  <div style="display: inline; float: left; margin-top: 10px;">

    <span style="background-color: #B0B0B0; color: white;">Composer
      <a href="#" alt="Installed: <?= COMPOSER_VERSION ?>"><?= COMPOSER_VERSION ?></a>
    </span>


    <form style="display: inline;" autocomplete="off" spellcheck="false"
      action="<?= APP_URL . '?' . http_build_query(/*APP_QUERY +*/ ['app' => 'composer']) . (defined('APP_ENV') && APP_ENV == 'development' ? '#' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>"
      method="GET">
      <?php if (isset($_GET['debug'])) { ?> <input type="hidden" name="debug" value="" /> <?php } ?>

      <code class="text-sm" style="background-color: #fff; color: #0078D7;">$ 
        <input type="hidden" name="app" value="composer" />
        <select name="exec" onchange="this.form.submit();">

  <?php if (defined('COMPOSER_BIN')): ?>
    <option <?= COMPOSER_EXEC_CMD === COMPOSER_BIN ? 'selected' : '' ?>
    value="bin"><?= COMPOSER_BIN ?></option>
    <?php endif; ?>
  
    <?php if (defined('COMPOSER_PHAR')): ?>
    <option <?= COMPOSER_EXEC_CMD === COMPOSER_PHAR['exec'] ? 'selected' : '' ?>
    value="phar">
    <?= COMPOSER_PHAR['exec'] ?>
    </option>
  <?php endif; ?>

        </select>
      </code>
    </form>

  </div>
  <div style="display: inline; float: right; margin-top: 10px; text-align: center; "><code
      style=" background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="closeApp('tools/registry/composer', {fullReset:true});">[X]</a></code>
  </div>
</div>

<div class="window-body" class=""
  style="width: 424px; background-color: rgba(255, 255, 255, 0.8); padding: 10px; display: block; left: 612px; top: 104px;">

  <div
    style="position: relative; margin: 0 auto; width: 421px; height: 324px; border: 3px dashed #6B4329; background-color: #FBF7F1;">

    <div class="ui-widget-content"
      style="position: relative; display: block; width: 421px; background-color: rgba(251,247,241); z-index: 2;">
      <div style="display: inline-block; text-align: left; width: 230px;">
        <div class="composer-menu text-sm" style="cursor: pointer; font-weight: bold; padding-left: 40px;">
          <div style=" border: 1px solid #000; width: 150px;">Main Menu</div>
        </div>
        <div class="text-xs" style="display: inline-block; border: 1px solid #000;">
          <?php
          // Build the query string
          // $query = http_build_query(APP_QUERY, '', '&', PHP_QUERY_RFC3986);
          
          // Replace `=` appended to empty keys
          // $query = ''; // preg_replace('/=(&|$)/', '$1', $query);
          ?>
          <a class="text-sm" id="app_composer-frameMenuPrev"
            href="<?= /*(!empty(APP_QUERY) ? "?$query" : '') . */ defined('APP_ENV') && APP_ENV == 'development' ? '#' : '#' ?>">
            &lt; Menu</a> | <a class="text-sm" id="app_composer-frameMenuNext"
            href="<?= /*(!empty(APP_QUERY) ? "?$query" : '') . */ defined('APP_ENV') && APP_ENV == 'development' ? '#' : '#' ?>">Init
            &gt;</a>
        </div>
        <form style="display:inline-block" action="/?api=composer" method="POST">
          <!-- optional: also send api via POST so either GET/POST extraction works -->
          <input type="hidden" name="api" value="composer">

          <!-- identify the intent (handy in your api/composer.php switch) -->
          <input type="hidden" name="composer[action]" value="set_autoload">

          <!-- default value when unchecked -->
          <input type="hidden" name="composer[autoload]" value="off">

          <!-- checkbox overrides when checked -->
          <label style="font-size:small;display:inline-flex;gap:.4em;align-items:center;">
            <input type="checkbox" name="composer[autoload]" value="on" onchange="this.form.submit();"
              <?= !empty($_ENV['COMPOSER']['AUTOLOAD']) ? 'checked' : '' ?>>
            AUTOLOAD
          </label>
        </form>
      </div>
      <div class="text-xs" style="position: relative; display: inline-block; text-align: right; float: right;">
          + 987 <a href="https://github.com/composer/composer/graphs/contributors" target="_blank" rel="noopener noreferrer">contributors</a>
          <br />
          <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;"
            href="http://getcomposer.org/" target="_blank" rel="noopener noreferrer">http://getcomposer.org/</a>
        </div>
      <div class="absolute"
        style="position: absolute; display: inline-block; top: 4px; text-align: right; width: 190px;">

        <!--
    <select id="frameSelector">
      <option value="0" selected>---</option>
      <option value="1">Update</option>
      <option value="2">Config</option>
      <option value="3">Initial</option>
      <option value="4">Install</option>
    </select>
-->
      </div>
      <div style="clear: both;"></div>
    </div>
    <div class="absolute"
      style="position: absolute; bottom: 60px; right: 0; margin: 0 auto; width: 225px; text-align: right;">
      <form action="/?app=composer" method="POST" class="text-sm">
        <input type="hidden" name="update" value="" />composer.lock requires an <button type="submit"
          style="border: 1px solid #000; z-index: 3;">Update</button>
      </form>
    </div>
    <div class="absolute"
      style="position: absolute; margin: 0px auto; text-align: center; height: 275px; width: 100%; background-repeat: no-repeat; <?= defined('COMPOSER_VERSION') and version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? "background-image: url('https://cliply.co/wp-content/uploads/2021/09/CLIPLY_372109170_FREE_FIREWORKS_400.gif')" : '' ?> ;">
    </div>

    <div class="absolute"
      style="position: absolute; top: 0; left: 0; right: 0; margin: 10px auto; opacity: 1.0; text-align: center; cursor: pointer; /*z-index: 1;*/">
      <img
        class="<?= defined('COMPOSER_VERSION') and version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') != 0 ? 'composer-update' : 'composer-menu' ?>"
        src="assets/images/composer.fw.png" style="margin-top: 45px;" width="150" height="198" />
    </div>

    <div class="absolute"
      style="position: absolute; bottom: 40px; left: 0; right: 0; width: 100%; text-align: center; z-index: 1; ">
      <form action="/?app=composer" method="POST">
        <input type="hidden" name="composer[create-project]" value="" />
        <span style="pdding-left: 125px"></span>
        <select name="composer[package]" onchange="this.form.submit();">
          <option value="" selected>create-project</option>
          <option value="laravel/laravel">laravel/laravel</option>
          <option value="symfony/skeleton">symfony/skeleton</option>
        </select>
        <span>/project/*</span>
      </form>
    </div>

    <div class="absolute" style="position: absolute; bottom: 24px; left: 0; right: 0; width: 100%; text-align: center;">
      <span style="text-decoration-line: underline; text-decoration-style: solid;">A Dependency Manager for PHP</span>
    </div>

    <div style="position: absolute; bottom: 0; left: 0; padding: 2px; z-index: 1;">
      <a href="https://github.com/composer/composer"><img src="assets/images/github-composer.fw.png" /></a>
    </div>

    <div class="absolute text-sm" style="position: absolute; bottom: 0; right: 0; padding: 2px; z-index: 1; ">
      <?= (defined('COMPOSER_VERSION') && version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') !== 0)
        ? 'Latest: ' . highlightVersionDiff(COMPOSER_VERSION, COMPOSER_LATEST)
        : 'Installed: ' . COMPOSER_VERSION; ?>
    </div>
    <div style="position: relative; overflow: hidden; width: 421px; height: 250px;">
      <?php

      $count = 0;
      if (count(composer_json()) !== 0)
        foreach (composer_json_get('require', []) as $key => $require) {
          if (preg_match('/.*\/.*:.*/', "$key:$require"))
            if (preg_match('/(.*)\/.*/', $key, $match))
              if (!empty($match) && !is_dir(app_base('vendor', null, 'abs') . $match[1] . '/'))
                $count++;
        }
      ?>
      <div id="app_composer-frameMenu"
        class="app_composer-frame-container <?= $count >= 1 ? '' : 'selected'; ?> absolute"
        style="background-color: rgb(225,196,151,.75); margin-top: 8px;">
        <!--<h3>Main Menu</h3> <h4>Update - Edit Config - Initalize - Install</h4> -->

        <div style="display: block; margin: 5px auto;">
          <div class="drop-shadow-2xl font-bold"
            style="display: inline-block; width: 192px; margin: 10px auto; text-align: right; cursor: pointer;">
            <div id="app_composer-frameMenuInit" style="text-align: center; padding-left: 18px;"><img
                style="display: block; margin: auto;" src="assets/images/initial_icon.fw.png" width="70"
                height="57" />Init</div>
          </div>

          <div class="config drop-shadow-2xl font-bold"
            style="display: inline-block; width: 192px; margin: 0px auto; text-align: center; cursor: pointer;">
            <div id="app_composer-frameMenuConf" class="" style="text-align: center;"><img
                style="display: block; margin: auto;" src="assets/images/folder.fw.png" width="70"
                height="58" />Config</div>
          </div>
        </div>
        <div style="display: block; margin: 4px auto;">
          <div class="install drop-shadow-2xl font-bold"
            style="display: inline-block; width: 192px; margin: 0 auto; text-align: right; cursor: pointer;">
            <div id="app_composer-frameMenuInstall" style="position: relative; text-align: center; padding-left: 15px;">
              <div style="position: absolute; top: -10px; left: 130px; color: red;"><?= ($count >= 1 ? $count : ''); ?>
              </div>
              <img style="display: block; margin: auto;" src="assets/images/install_icon.fw.png" width="54"
                height="54" />Install
            </div>
          </div>
          <div class="drop-shadow-2xl font-bold"
            style="display: inline-block; width: 192px; margin: 0 auto; text-align: center; cursor: pointer;">
            <div id="app_composer-frameMenuUpdate" style="text-align: center; "><img
                style="display: block; margin: auto;" src="assets/images/update_icon.fw.png" width="54"
                height="54" /><a href="#">Update<?=/*Now!*/ NULL; ?></a></div>
          </div>
        </div>
        <div style="height: 10px;"></div>
      </div>
      <?php ob_start(); ?>
      <div id="app_composer-frameUpdate" class="app_composer-frame-container absolute"
        style="overflow: scroll; background-color: rgb(225,196,151,.75);">
        <form autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
          action="<?= APP_URL . '?' . http_build_query(/*APP_QUERY +*/ ['app' => 'composer']) . (defined('APP_ENV') && APP_ENV == 'development' ? '#' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>"
          method="POST">
          <input type="hidden" name="composer[update]" value="" />
          <div style="position: absolute; right: 0; float: right; text-align: center;">
            <input class="btn" id="composerSetupSubmit" type="submit" value="self-update">
          </div>
          <div style="display: inline-block; width: 100%; margin: 0 auto;">
            <div class="text-sm" style="display: inline;">
              <label id="composerSetupLabel" for="composerSetup"
                style="background-color:hsl(89, 100%, 42%); color: white; text-decoration: underline; cursor: pointer; font-weight: bold;">&#9650;
                <code>Setup / Update</code></label>
            </div>
            <span style="background-color: white;">
              <span class="text-sm" style="display: inline-block;">was
                <?= defined('COMPOSER_VERSION') and version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') == 0 ? '<span style="font-weight: bold;">not</span>' : '' ?>
                found: </span>
            </span>
          </div>

          <div id="composerSetupForm"
            style="display: inline-block; padding: 5px; background-color: rgba(0,0,0,.03); border: 1px dashed #0078D7;">
            <div>
              <span class="text-xs"
                style="background-color: #0078D7; color: white;"><code>Version: (Installed) <?= !defined('COMPOSER_VERSION') ? '' : COMPOSER_VERSION ?> -> (Latest) <?= COMPOSER_LATEST ?></code></span>
            </div>
            <label>Composer Command</label>
            <textarea style="width: 100%" cols="40" rows="5" name="composer[cmd]">php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar -v
<?= stripos(PHP_OS, 'WIN') !== 0 && defined('APP_SUDO') ? APP_SUDO : '' ?>composer self-update</textarea>
          </div>
        </form>
      </div>
      <?php
      $frameUpdateContents = ob_get_contents();
      ob_end_clean(); ?>

      <?= defined('COMPOSER_LATEST') && defined('COMPOSER_VERSION') ? (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') == 0 ? NULL : $frameUpdateContents) : ''; ?>

      <div id="app_composer-frameInit"
        class="app_composer-frame-container absolute <?= is_file(COMPOSER_PROJECT_ROOT) ? '' : (count((array) composer_json(true)) !== 0 ? '' : 'selected'); ?>"
        style="overflow: hidden; height: 270px;">
        <?php if (!defined('CONSOLE') && CONSOLE != true) { ?><?php } ?>
        <form autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
          action="<?= APP_URL . '?' . http_build_query(/*APP_QUERY +*/ ['app' => 'composer']) . (defined('APP_ENV') && APP_ENV == 'development' ? '#' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>"
          method="POST">

          <div style="position: absolute; right: 0; float: right; text-align: center;">
            <button id="app_composer-init-submit" class="btn" type="submit" value>Init/Run</button>
          </div>
          <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
            <div class="text-sm" style="display: inline;">
              <label id="composerInitLabel" for="composerInit" style="background-color: #6781B2; color: white;">&#9650;
                <code>Init</code></label>
            </div>
          </div>
          <div id="composerInitForm"
            style="display: inline-block; padding: 10px; background-color: rgba(235, 216, 186, 0.8); border: 1px dashed #0078D7;">
            <label>Composer Command</label>
            <textarea id="app_composer-init-input" style="width: 100%" cols="40" rows="6" name="composer[init]"
              autocomplete="off" autocorrect="off" autocapitalize="off"
              spellcheck="false"><?= implode(' ', COMPOSER_DEFAULT_ARGS); /*preg_replace('/\n\s*--/', "--", COMPOSER_DEFAULT_ARGS);*/ ?></textarea>
          </div>
          <?php if (!defined('CONSOLE') && CONSOLE != true) { ?> <?php } ?>
        </form>

      </div>

      <div id="app_composer-frameConf"
        class="app_composer-frame-container absolute <?= is_file(APP_PATH . (defined('APP_ROOT') ? APP_ROOT : '') . 'composer.json') ? 'selected' : ''; ?>"
        style="overflow-x: hidden; overflow-y: auto; height: 230px;">
        <form autocomplete="off" spellcheck="false"
          action="<?= APP_URL . '?' . http_build_query(['app' => 'composer'], ''/*, '&amp;'*/) . (defined('APP_ENV') && APP_ENV == 'development' ? '#' : '') ?>"
          method="POST">
          <input type="hidden" name="composer[config]" value="" />

          <div style="position: absolute; right: 0; float: right; text-align: center; z-index: 2;">
            <button class="btn absolute" id="composerJsonSubmit" type="submit"
              style="position: absolute; top: 0; right: 0;"
              value=""><?= defined('COMPOSER_AUTH') && realpath(COMPOSER_AUTH) ? 'Modify' : 'Create'; ?></button>
          </div>
          <div
            style="position: relative; display: inline-block; width: 100%; background-color: rgb(225,196,151,.25); z-index: 1;">
            <div class="text-sm" style="display: inline;">
              <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="composerJson" value="true" checked=""> -->
              <label for="composerJson" id="appComposerAuthLabel"
                title="<?= defined('COMPOSER_AUTH') && realpath(COMPOSER_AUTH) ? COMPOSER_AUTH : '' /*NULL*/ ; ?>"
                style="background-color: #6B4329; <?= defined('COMPOSER_AUTH') && realpath(COMPOSER_AUTH) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color: red; text-decoration: underline; text-decoration: line-through;' ?> cursor: pointer; font-weight: bold;">&#9660;
                <code>COMPOSER_HOME/auth.json</code></label>
            </div>
          </div>
          <div id="appComposerAuthJsonForm"
            style="display: none; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
            <a class="text-sm" style="color: blue; text-decoration: underline;"
              href="https://github.com/settings/tokens?page=1">GitHub OAuth Token</a>:
            <span class="text-sm" style="float: right;">
              <?php
              // Countdown days until token “rotation”, using COMPOSER_AUTH mtime + 30 days
/* Example function you can use elsewhere
function composer_token_days_left(int $windowDays = 30): ?int
{
  if (!defined('COMPOSER_AUTH') || !is_file(COMPOSER_AUTH))
    return null;

  $tz = new DateTimeZone(date_default_timezone_get());
  $rotAt = (new DateTimeImmutable('@' . filemtime(COMPOSER_AUTH)))
    ->setTimezone($tz)
    ->modify("+{$windowDays} days");
  $today = new DateTimeImmutable('today', $tz);

  $days = $today->diff($rotAt)->days;
  // sign: negative if overdue
  if ($rotAt < $today)
    $days = -$days;
  return $days;
}

// Example output: clamp to 0 if you only want a countdown
$daysLeft = composer_token_days_left(30);
echo $daysLeft === null ? '' : max(0, $daysLeft);
*/
              ?>

              <?= (is_file(COMPOSER_AUTH)
                ? max(0, (new DateTimeImmutable('today'))
                  ->diff((new DateTimeImmutable('@' . filemtime(COMPOSER_AUTH)))
                    ->modify('+30 days'))->days)
                : '') ?>
              (Days left)
            </span>
            <div style="float: right;">
              <input type="text" size="40" name="auth[github_oauth]" value="<?= COMPOSER_GITHUB_OAUTH_TOKEN ?? '' ?>" />
            </div>
            <div style="clear: both;"></div>
          </div>

          <div
            style="position: relative; display: inline-block; width: 100%; background-color: rgb(225,196,151,.25); z-index: 1;">
            <div class="text-sm" style="display: inline;">
              <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="composerJson" value="true" checked=""> -->
              <label for="composerJson" id="appComposerConfigLabel"
                title="<?= defined('COMPOSER_CONFIG') ? (is_file(COMPOSER_CONFIG) ? COMPOSER_CONFIG : '') : '' /*NULL*/ ; ?>"
                style="background-color: #6B4329; <?= defined('COMPOSER_CONFIG') ? (is_file(COMPOSER_CONFIG ?? '') ? 'color: #F0E0C6; text-decoration: underline; ' : 'color: red; text-decoration: underline; text-decoration: line-through;') : '' ?> cursor: pointer; font-weight: bold;">&#9660;
                <code>COMPOSER_HOME/config.json</code></label>
            </div>
          </div>
          <div id="appComposerConfigJsonForm"
            style="display: none; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
            <a class="text-sm" style="color: blue; text-decoration: underline;"
              href="https://github.com/settings/tokens?page=1">GitHub OAuth Token</a>:
            <span class="text-sm" style="float: right;"></span>
            <div style="float: right;">
              <input type="text" size="40" name="config[github_oauth]" value="<?= COMPOSER_AUTH['token'] ?? "" ?>"
                disabled />
            </div>
            <div style="clear: both;"></div>
            <a class="text-sm" style="color: blue; text-decoration: underline;" href="">Platform</a>:
            <span class="text-sm" style="float: right;"></span>
            <div style="float: right;">
              <input type="text" size="40" name="config[platform]" value="php:^7.4||^8.1" disabled />
            </div>
            <div style="clear: both;"></div>
          </div>
          <!--  -->
          <div
            style="position: relative; display: inline-block; background-color: rgb(225,196,151,.25); width: 100%; z-index: 1;">
            <?php //if (defined('COMPOSER_JSON')) $composer = json_decode(COMPOSER_JSON); ?>
            <div class="text-sm" style="display: inline;">
              <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="composerJson" value="true" checked=""> -->
              <label for="composerJson" id="appComposerJsonLabel" class="text-sm"
                style="background-color: #6B4329; <?= defined('COMPOSER_JSON') && realpath(COMPOSER_JSON) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color:red; text-decoration: underline; text-decoration: line-through;' ?> cursor: pointer; font-weight: bold;"
                title="<?= defined('COMPOSER_JSON') && realpath(COMPOSER_JSON) ? COMPOSER_JSON : '' /*NULL*/ ; ?>">&#9650;
                <code>COMPOSER_PATH/composer.json</code></label>
              <div class="text-xs"
                style="display: <?= !is_file(APP_PATH . 'composer.lock') ? 'none' : 'inline-block' ?>; padding-top: 5px; padding-right: 10px; float: right;">
                <input type="checkbox" name="composer[lock]" value="" /> <span
                  style="background-color: white; color: red; text-decoration: line-through;">composer.lock</span>
              </div>
            </div>
          </div>
          <div id="appComposerJsonForm"
            style="position: relative; display: inline-block; overflow-x: hidden; overflow-y: auto; height: auto; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7;">
            <?php if (defined('COMPOSER_JSON') && realpath(COMPOSER_JSON)) { ?>
              <div style="display: inline-block; width: 100%; margin-bottom: 10px;">
                <div class="text-xs" style="display: inline-block; float: left; background-color: #0078D7; color: white;">
                  Last Update: <span <?= !is_file(COMPOSER_JSON) ? 'style="background-color: white; color: red;"' : 'style="background-color: white; color: #0078D7;"' ?>><?= is_file(COMPOSER_JSON) ? date('Y-m-d H:i:s', filemtime(COMPOSER_JSON)) : date('Y-m-d H:i:s') ?></span>
                </div>


                <div class="text-xs" style="display: inline-block; float: right;">
                  <input type="checkbox" name="composer[update]" value="" checked /> <span
                    style="background-color: #0078D7; color: white;">Update</span>
                  <input type="checkbox" name="composer[install]" value="" checked /> <span
                    style="background-color: #0078D7; color: white;">Install</span>
                </div>
              </div>
            <?php } ?>
            <div style="display: inline-block; width: 100%;"><span <?= !empty(composer_json_get('name', '')) ? '' : 'style="background-color: #fff; color: red;" title="Either Vendor or Package is missing"' ?>>Name:</span>
              <div style="position: relative; float: right;">
                <div class="absolute font-bold"
                  style="position: absolute; top: -8px; left: 5px; font-size: 10px; z-index: 1;">Vendor</div>
                <input type="text" id="tst" name="composer[config][name][vendor]" placeholder="vendor"
                  value="<?= explode('/', composer_json_get('name', ''))[0]; ?>" size="13"> / <div
                  class="absolute font-bold"
                  style="position: absolute; top: -8px; right: 82px; font-size: 10px; z-index: 1;">Package</div> <input
                  type="text" id="tst" name="composer[config][name][package]" placeholder="package"
                  value="<?= explode('/', composer_json_get('name', ''))[1]; ?>" size="13" />
              </div>
            </div>
            <div style="display: inline-block; width: 100%;"><label for="composer-description">Description:</label>
              <div style="float: right;">
                <input id="composer-description" type="text" name="composer[config][description]" placeholder="Details"
                  value="<?= composer_json_get('description', 'Description is missing'); ?>">
              </div>
            </div>

            <!-- version -->
            <?php
            $version = composer_json_get('version', '');

            $regex = defined('COMPOSER_EXPR_VER')
              ? COMPOSER_EXPR_VER
              : '/^\d+\.\d+(?:\.\d+)?$/'; // fallback: 1.2 or 1.2.3
            
            $isValid = ($version !== '') && preg_match($regex, $version);

            // For the HTML pattern attribute (no delimiters/anchors):
            $htmlPattern = defined('COMPOSER_EXPR_VER')
              ? '(?:v?\d+(?:\.\d+){0,3}|dev-[A-Za-z0-9._\-\/]+)'
              : '\d+\.\d+(?:\.\d+)?';

            $labelStyle = $isValid ? '' : 'style="background-color:#fff;color:red;cursor:pointer;"';
            $labelTitle = $isValid ? '' : 'title="Version must follow: ' . htmlspecialchars(trim($htmlPattern, '/'), ENT_QUOTES) . '"'; ?>
            <div style="display:inline-block;width:100%;">
              <label for="composer-version" <?= $labelStyle ?> <?= $labelTitle ?>>Version:</label>
              <div style="float:right;">
                <input id="composer-version" type="text" name="composer[config][version]" size="10" placeholder="1.2.3"
                  style="text-align:right;" pattern="<?= $htmlPattern ?>"
                  value="<?= htmlspecialchars($version, ENT_QUOTES) ?>">
              </div>
            </div>
            <!-- type -->
            <div style="display: inline-block; width: 100%;">Type:
              <div style="float: right;">
                <select name="composer[config][type]">
                  <?php foreach (['library', 'project', 'metapackage', 'composer-plugin'] as $type) { ?>
                    <option <?= composer_json_get('type', '') == $type ? ' selected=""' : ''; ?>>
                      <?= $type; ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div style="display: inline-block; width: 100%;">Keywords:
              <div style="float: right;">
                <input id="composerKeywordAdd" type="text" placeholder="Keywords" value="" onselect="add_keyword()">
              </div>
              <div class="clearfix"></div>
              <div id="composerAppendKeyword"
                style="padding: 10px 0 10px 0; display: <?= !empty(composer_json_get('keywords', '')) ? 'block' : 'none' ?>; width: 100%;">
                <?php
                foreach (composer_json_get('keywords', []) as $key => $keyword) { ?>
                  <label for="keyword_<?= $key; ?>"><sup
                      onclick="rm_keyword(\'keyword_<?= $key; ?>\');">[x]</sup><?= $keyword; ?></label><input
                    type="hidden" id="keyword_<?= $key; ?>" name="composer[config][keywords][]"
                    value="<?= $keyword; ?>" />&nbsp;
                <?php } ?>
              </div>
            </div>
            <!-- homepage -->
            <!-- readme -->
            <!-- time -->
            <!-- version_normalized -->
            <div style="display: inline-block; width: 100%;">License:
              <div style="float: right;">
                <select name="composer[config][license]">
                  <option label="" <?= $license = composer_json_get('license', '') ? '' : ' selected=""'; ?>>
                  </option>
                  <?php foreach (['WTFPL', 'GPL-3.0', 'MIT'] as $license) { ?>
                    <option <?= composer_json_get('license', '') == $license ? ' selected=""' : ''; ?>><?= $license; ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <!-- authors -->
            <div style="display: inline-block; width: 100%;">Authors:<br />

              <?php if (!empty(composer_json_get('authors', []))) {
                foreach (composer_json_get('authors', []) as $key => $author) { ?>
                  <div style="position: relative; float: left;">
                    <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name
                    </div>
                    <input type="text" id="tst" name="composer[config][authors][<?= $key ?>][name]" placeholder="name"
                      value="<?= $author['name'] ?>" size="10"> /
                    <div class="absolute font-bold" style="position: absolute; top: -8px; right: 134px; font-size: 10px;">
                      Email</div>
                    <input type="text" id="tst" name="composer[config][authors][<?= $key ?>][email]" placeholder="email"
                      value="<?= $author['email'] ?>" size="18" />
                  </div>
                  <div class="dropdown">
                    <div id="myDropdown" class="dropdown-content">
                      <?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key2 => $role) { ?>
                        <a href="#"><img style="float: left;" width="30" height="33"
                            src="<?= 'assets/images/role' . $key2 . '.fw.png' ?>"><?= $role; ?> <input type="radio" id="<?= $key2 ?>"
                            style="float: right; cursor: pointer;" name="composer[config][authors][<?= $key ?>][role]"
                            value="<?= $role; ?>" <?= isset($author['role']) && $author['role'] == $role ? ' checked=""' : '' ?> /></a>
                      <?php } ?>
                    </div>
                    <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
                  </div>

                <?php }
              } else { ?>

                <div style="position: relative; float: left;">
                  <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name
                  </div>
                  <input type="text" id="tst" name="composer[config][authors][0][name]" placeholder="name"
                    value="Barry Dick" size="10"> /
                  <div class="absolute font-bold" style="position: absolute; top: -8px; right: 140px; font-size: 10px;">
                    Email</div>
                  <input type="text" id="tst" name="composer[config][authors][0][email]" placeholder="email"
                    value="barryd.it@gmail.com" size="18" />
                </div>&nbsp;

                <div class="dropdown">
                  <div id="myDropdown" class="dropdown-content">
                    <?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key => $role) { ?>
                      <a href="#"><img style="float: left;" width="30" height="33"
                          src="<?= 'assets/images/role' . $key . '.fw.png' ?>"><?= $role; ?> <input type="radio" id="<?= $key ?>"
                          style="float: right; cursor: pointer;" name="composer[config][authors][0][role]"
                          value="<?= $role; ?>" /></a>
                    <?php } ?>
                  </div>
                  <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
                </div>

                <!--
    <select name="composerAuthorRole">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $role) { ?>
      <option<?= (composer_json_get('authors', [])['role'] ?? null ? "value=\"$role\"" : '') && (composer_json_get('authors', [])['role'] == $role ? ' selected=""' : ''); ?>><?= $role; ?></option>
<?php } ?>
    </select>
-->

                <!--        <label for="author_<?= $key; ?>"><sup onclick="rm_author(\'author_<?= $key; ?>\');">[x]</sup>' + event.target.value + '</label><input type="hidden" id="author_<?= $key; ?>" name="composerAuthors[]" value="' + event.target.value + '" />&nbsp; -->
              <?php } ?>

            </div>

            <!-- source -->
            <!-- dist -->

            <!-- funding -->


            <!--
"require": {
  "php": ">=5.3.0"
},
"autoload": {
  "psr-4": {
      "ResponseClass\\":"src/"
  }
},
"config":{
  "optimize-autoloader": true
}
-->

            <div style="display: inline-block; width: 100%;">
              <hr />Require:
              <div style="float: right;">
                <input id="composerReqPkg" type="text" title="Enter Text and onSelect" list="composerReqPkgs"
                  placeholder="" value="" oninput="App['tools/registry/composer'].get_package(this);"
                  onselect="App['tools/registry/composer'].get_package(this);">
                <!-- onselect="get_package(this);" -->
                <datalist id="composerReqPkgs">
                  <option value=""></option>
                </datalist>
              </div>
              <div id="composerAppendRequire"
                style="padding: 10px; display: <?= empty(composer_json_get('require', [])) ? 'none' : 'block' ?>;">
                <datalist id="composerReqVersResults">
                  <option value=""></option>
                </datalist>
                <?php $i = 0;
                if (!empty(composer_json_get('require', []))) {
                  if (composer_json_get('require', [])['php'] == null) { ?>
                    <input type="checkbox" checked=""
                      onchange="this.indeterminate = !this.checked; document.getElementById('pkg_<?= $i; ?>').disabled = !this.checked">
                    <input type="text" id="pkg_<?= $i; ?>" name="composer[config][require][]"
                      value="<?= 'php:^' . PHP_VERSION ?>" list="composerReqVersResults" size="30"
                      onselect="get_version('pkg_<?= $i; ?>')">
                    <label for="pkg_<?= $i; ?>"></label><br />
                    <?php $i++;
                  }
                  foreach (composer_json_get('require', []) as $key => $require) { ?>
                    <input type="checkbox" checked=""
                      onchange="this.indeterminate = !this.checked; document.getElementById('pkg_<?= $i; ?>').disabled = !this.checked">
                    <input type="text" id="pkg_<?= $i; ?>" name="composer[config][require][]"
                      value="<?= $key . ':' . $require ?>" list="composerReqVersResults" size="30"
                      onselect="get_version('pkg_<?= $i; ?>')">
                    <label for="pkg_<?= $i; ?>"></label><br />
                    <?php $i++;
                  }
                } else { ?>
                  <input type="checkbox" checked=""
                    onchange="this.indeterminate = !this.checked; document.getElementById('pkg_<?= $i; ?>').disabled = !this.checked">
                  <input type="text" id="pkg_<?= $i; ?>" name="composer[config][require][]"
                    value="<?= 'php:^' . PHP_VERSION ?>" list="composerReqVersResults" size="30"
                    onselect="get_version('pkg_<?= $i; ?>')">
                  <label for="pkg_<?= $i; ?>"></label><br />
                <?php } ?>
              </div>
            </div>
            <div style="display: inline-block; width: 100%;">Require-dev:
              <div style="float: right;">
                <input id="composerRequireDevPkg" type="text" placeholder="" value="" list="composerReqDevPackages"
                  onselect="get_dev_package()">
                <datalist id="composerReqDevPackages">
                  <option value=""></option>
                </datalist>
              </div>
              <div id="composerAppendRequire-dev"
                style="padding: 10px; display: <?= empty(composer_json_get('require', []) ? 'none' : 'block') ?>;">
                <datalist id="composerReq-devVersResults">
                  <option value=""></option>
                </datalist>
                <?php $i = 0;
                if (!empty(composer_json_get('require-dev', [])))
                  foreach (composer_json_get('require-dev', []) as $key => $require) { ?>
                    <input type="checkbox" checked=""
                      onchange="this.indeterminate = !this.checked; document.getElementById('pkg-dev_<?= $i; ?>').disabled = !this.checked">
                    <input type="text" id="pkg-dev_<?= $i; ?>" name="composer[config][require-dev][]"
                      value="<?= $key . ':' . $require ?>" list="composerReqVersResults" size="30"
                      onselect="get_version('pkg-dev_<?= $i; ?>')">
                    <label for="pkg-dev_<?= $i; ?>"></label><br />
                    <?php $i++;
                  } ?>
              </div>
            </div>

            <div style="display: inline-block; width: 100%;">Autoload:
              <div style="float: right;">
                <input type="text" name="composer[config][autoload]" placeholder="Autoload" value="">
              </div>
            </div>
            <div style="display: inline-block; width: 100%;">Autoload-dev:
              <div style="float: right;">
                <input type="text" name="composer[config][autoload-dev]" placeholder="Autoload-dev" value="">
              </div>
            </div>

            <div style="display: inline-block; width: 100%;">Minimum-Stability:
              <div style="float: right;">
                <select name="composer[config][minimum-stability]">
                  <?php
                  foreach (['stable', 'rc', 'beta', 'alpha', 'dev'] as $ms) { ?>
                    <option value="<?= $ms ?>" <?= composer_json_get('minimum-stability', 'dev') == $ms ? ' selected=""' : '' ?>><?= $ms ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          </div>
          <div
            style="position: relative; display: inline-block; background-color: rgb(225,196,151,.25); width: 100%; z-index: 1;">
            <?php //if (defined('COMPOSER_JSON')) $composer = json_decode(COMPOSER_JSON); ?>
            <div class="text-sm" style="display: inline;">
              <!-- <input id="composerJson" type="checkbox" style="cursor: pointer;" name="" value="true" checked=""> -->

              <label for="composerJson" id="appComposerVendorJsonLabel" class="text-sm"
                style="background-color: #6B4329; <?= defined('VENDOR_JSON') && realpath(VENDOR_JSON['path']) ? 'color: #F0E0C6; text-decoration: underline; ' : 'color:red; text-decoration: underline; text-decoration: line-through;' ?> cursor: pointer; font-weight: bold;"
                title="<?= defined('VENDOR_JSON') && realpath(VENDOR_JSON['path']) ? VENDOR_JSON['path'] : '' /*NULL*/ ; ?>">&#9650;
                <code>COMPOSER_PATH/[vendor/*].json</code></label>
              <div class="text-xs"
                style="display: <?= !is_file(APP_PATH . 'composer.lock') ? 'none' : 'inline-block' ?>; padding-top: 5px; padding-right: 10px; float: right;">
              </div>
            </div>
          </div>

          <div id="appComposerVendorJsonForm"
            style="position: relative; display: inline-block; overflow-x: hidden; overflow-y: auto; height: auto; padding: 10px; background-color: rgb(235,216,186,.80); border: 1px dashed #0078D7; width: 100%;">
            <?php if (defined('VENDOR')) { ?>


              <?php if (defined('VENDOR_JSON') && realpath(VENDOR_JSON['path'])) { ?>
                <div style="display: block; width: 100%; margin-bottom: 10px;">
                  <div class="text-xs" style="display: inline-block; float: left; background-color: #0078D7; color: white;">
                    Last Update: <span <?= isset(VENDOR->time) && VENDOR->time === '' ? 'style="background-color: white; color: red;"' : 'style="background-color: white; color: #0078D7;"' ?>><?= isset(VENDOR->time) && VENDOR->time !== '' ? VENDOR->{'time'} : date('Y-m-d H:i:s') ?></span>
                  </div>
                </div>
              <?php } ?>


              <div style="display: inline-block; width: 100%;"><span <?= isset(VENDOR->{'name'}) && VENDOR->{'name'} !== '' ? '' : 'style="background-color: #fff; color: red;" title="Either Vendor or Package is missing"' ?>>Vendor/Package:</span>
                <div style="position: relative; float: right;">
                  <?php

                  $keys = array_keys(composer_json_get('require', []));
                  if (count(composer_json_get('require-dev', [])) > 0)
                    $keys = array_merge($keys, array_keys(composer_json_get('require-dev', [])));

                  ?>
                  <select onselect="selectPackage()">
                    <option>---</option>
                    <?php
                    foreach ($keys as $package) {
                      if ($package == 'php')
                        continue;
                      elseif (isset(COMPOSER['json']->{'require'}->{$package}))
                        echo "<option selected>$package</option>";
                      else
                        echo "<option>$package</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div style="display: inline-block; width: 100%;"><label for="description" <?= isset(VENDOR->{'description'}) && VENDOR->{'description'} !== '' ? '' : 'style="background-color: #fff; color: red; cursor: pointer;" title="Description is missing"' ?>>Description:</label>
                <div style="float: right;">
                  <input id="description" type="text" name="" placeholder="Details"
                    value="<?= defined('VENDOR') && isset(VENDOR->description) ? VENDOR->description : ''; ?>">
                </div>
              </div>

              <!-- version -->
              <div style="display: inline-block; width: 100%;"><label for="version" <?= (isset(VENDOR->{'version'}) && preg_match(COMPOSER_EXPR_VER, VENDOR->{'version'}) ? '' : 'style="background-color: #fff; color: red; cursor: pointer;" title="Version must follow this format: ' . COMPOSER_EXPR_VER . '"') ?>>Version:</label>
                <div style="float: right;">
                  <input id="version" type="text" name="" size="10" placeholder="(Version) 1.2.3"
                    style="text-align: right;" pattern="(\d+\.\d+(?:\.\d+)?)"
                    value="<?= defined('VENDOR') && isset(VENDOR->version) ? VENDOR->version : ''; ?>">
                </div>
              </div>
              <!-- type -->
              <div style="display: inline-block; width: 100%;">Type:
                <div style="float: right;">
                  <select name="">
                    <option label="" <?= defined('VENDOR') && isset(VENDOR->license) ? '' : 'selected=""'; ?>></option>
                    <?php foreach (['library', 'project', 'metapackage', 'composer-plugin'] as $type) { ?>
                      <option <?= defined('VENDOR') && isset(VENDOR->type) && VENDOR->type == $type ? ' selected=""' : ''; ?>>
                        <?= $type; ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div style="display: inline-block; width: 100%;">Keywords:
                <div style="float: right;">
                  <input type="text" placeholder="Keywords" value="">
                </div>
                <div class="clearfix"></div>
                <div id="composerAppendKeyword"
                  style="padding: 10px 0 10px 0; display: <?= defined('VENDOR') && isset(VENDOR->keywords) && !empty(VENDOR->keywords) ? 'block' : 'none' ?>; width: 100%;">
                  <?php if (defined('VENDOR') && isset(VENDOR->keywords))
                    foreach (VENDOR->keywords as $key => $keyword) { ?>
                      <label for="keyword_<?= $key; ?>"><sup
                          onclick="rm_keyword(\'keyword_<?= $key; ?>\');">[x]</sup><?= $keyword; ?></label>&nbsp;
                    <?php } ?>
                </div>
              </div>
              <!-- homepage -->
              <!-- readme -->
              <!-- time -->
              <!-- version_normalized -->
              <div style="display: inline-block; width: 100%;">License:
                <div style="float: right;">
                  <select name="">
                    <option label="" <?= defined('VENDOR') && isset(VENDOR->license) ? '' : ' selected=""'; ?>></option>
                    <?php foreach (['WTFPL', 'GPL-3.0', 'MIT'] as $license) { ?>
                      <option <?= defined('VENDOR') && isset(VENDOR->license) && VENDOR->license == $license ? 'selected=""' : ''; ?>><?= $license; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <!-- authors -->
              <div style="display: inline-block; width: 100%;">Authors:<br />

                <?php if (defined('VENDOR') && isset(VENDOR->authors))
                  foreach (VENDOR->authors as $key => $author) { ?>
                    <div style="position: relative; float: left;">
                      <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name
                      </div>
                      <input type="text" id="tst" name="" placeholder="name" value="<?= $author->{'name'} ?>" size="10"> /
                      <div class="absolute font-bold" style="position: absolute; top: -8px; right: 134px; font-size: 10px;">
                        Email</div>
                      <input type="text" id="tst" name="" placeholder="email" value="<?= $author->{'email'} ?>" size="18" />
                    </div>
                    <div class="dropdown">
                      <div id="myDropdown" class="dropdown-content">
                        <?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key2 => $role) { ?>
                          <a href="#"><img style="float: left;" width="30" height="33"
                              src="<?= 'assets/images/role' . $key2 . '.fw.png' ?> "><?= $role; ?> <input type="radio" id="<?= $key2 ?>"
                              style="float: right; cursor: pointer;" name="" value="<?= $role; ?>" <?= isset($author->{'role'}) && $author->{'role'} == $role ? ' checked=""' : '' ?> /></a>
                        <?php } ?>
                      </div>
                      <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
                    </div>

                  <?php } else { ?>

                  <div style="position: relative; float: left;">
                    <div class="absolute font-bold" style="position: absolute; top: -8px; left: 10px; font-size: 10px;">Name
                    </div>
                    <input type="text" id="tst" name="" placeholder="name" value="Barry Dick" size="10"> /
                    <div class="absolute font-bold" style="position: absolute; top: -8px; right: 140px; font-size: 10px;">
                      Email</div>
                    <input type="text" id="tst" name="" placeholder="email" value="barryd.it@gmail.com" size="18" />
                  </div>&nbsp;

                  <div class="dropdown">
                    <div id="myDropdown" class="dropdown-content">
                      <?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $key => $role) { ?>
                        <a href="#"><img style="float: left;" width="30" height="33"
                            src="<?= 'assets/images/role' . $key . '.fw.png' ?>"><?= $role; ?> <input type="radio" id="<?= $key ?>"
                            style="float: right; cursor: pointer;" name="" value="<?= $role; ?>" /></a>
                      <?php } ?>
                    </div>
                    <button type="button" onclick="myFunction()" class="dropbtn">Role &#9660;</button>
                  </div>

                  <!--
    <select name="">
<?php foreach (['Backend', 'Designer', 'Developer', 'Programmer'] as $role) { ?>
      <option <?= (composer_json_get('authors', []) ? "value=\"$role\"" : '') == $role ? ' selected=""' : ''; ?>><?= $role; ?></option>
<?php } ?>
    </select>
-->

                  <!--        <label for="author_<?= $key; ?>"><sup onclick="rm_author(\'author_<?= $key; ?>\');">[x]</sup>' + event.target.value + '</label><input type="hidden" id="author_<?= $key; ?>" name="" value="' + event.target.value + '" />&nbsp; -->
                <?php } ?>

              </div>

              <!-- source -->
              <!-- dist -->

              <!-- funding -->


              <!--
"require": {
  "php": ">=5.3.0"
},
"autoload": {
  "psr-4": {
      "ResponseClass\\":"src/"
  }
},
"config":{
  "optimize-autoloader": true
}
-->

              <div style="display: inline-block; width: 100%;">
                <hr />Require:
                <div style="float: right;">
                  <input type="text" title="Enter Text and onSelect" placeholder="" value="">
                </div>
                <div
                  style="padding: 10px; display: <?= defined('VENDOR') && !isset(VENDOR->{'require'}) ? 'none' : 'block' ?>;">
                  <?php $i = 0;
                  if (defined('VENDOR') && isset(VENDOR->{'require'})) {
                    if (!isset(VENDOR->{'require'}->{'php'})) { ?>
                      <input type="checkbox" checked="" />
                      <input type="text" value="<?= 'php:^' . PHP_VERSION ?>" size="30" />
                      <label for="pkg_<?= $i; ?>"></label><br />
                      <?php $i++;
                    }
                    foreach (VENDOR->{'require'} as $key => $require) { ?>
                      <input type="checkbox" checked="" />
                      <input type="text" name="" value="<?= $key . ':' . $require ?>" size="30" />
                      <label for="pkg_<?= $i; ?>"></label><br />
                      <?php $i++;
                    }
                  } else { ?>
                    <input type="checkbox" checked="" />
                    <input type="text" id="pkg_<?= $i; ?>" name="" value="<?= 'php:^' . PHP_VERSION ?>" size="30" />
                    <label for="pkg_<?= $i; ?>"></label><br />
                  <?php } ?>
                </div>
              </div>
              <div style="display: inline-block; width: 100%;">Require-dev:
                <div style="float: right;">
                  <input type="text" placeholder="" name="" value="" />
                </div>
                <div
                  style="padding: 10px; display: <?= defined('VENDOR') && !isset(VENDOR->{'require-dev'}) ? 'none' : 'block' ?>;">
                  <?php $i = 0;
                  if (defined('VENDOR') && isset(VENDOR->{'require-dev'}))
                    foreach (VENDOR->{'require-dev'} as $key => $require) { ?>
                      <input type="checkbox" checked="" />
                      <input type="text" id="pkg-dev_<?= $i; ?>" name="" value="<?= $key . ':' . $require ?>" size="30" />
                      <label for="pkg-dev_<?= $i; ?>"></label><br />
                      <?php $i++;
                    } ?>
                </div>
              </div>

              <div style="display: inline-block; width: 100%;">Autoload:
                <div style="float: right;">
                  <input type="text" name="" placeholder="Autoload" value="">
                </div>
              </div>
              <div style="display: inline-block; width: 100%;">Autoload-dev:
                <div style="float: right;">
                  <input type="text" name="" placeholder="Autoload-dev" value="">
                </div>
              </div>

              <div style="display: inline-block; width: 100%;">Minimum-Stability:
                <div style="float: right;">
                  <select name="">
                    <?php if (defined('VENDOR'))
                      foreach (['stable', 'rc', 'beta', 'alpha', 'dev'] as $ms) { ?>
                        <option value="<?= $ms ?>" <?= (isset(VENDOR->{'minimum-stability'}) && VENDOR->{'minimum-stability'} == $ms ? ' selected=""' : '') ?>><?= $ms ?></option>
                      <?php } ?>
                  </select>
                </div>
              </div>
              <div style="padding: 10px; width: 100%;">

              </div>

            <?php } ?>

          </div>

          <div style="height: 15px;"></div>

        </form>

      </div>

      <?php
      $count = 0;
      if (count(composer_json_get('require', [])) > 0)
        foreach (composer_json_get('require', []) as $key => $require)
          if (preg_match('/.*\/.*:.*/', "$key:$require"))
            if (preg_match('/(.*\/.*)/', $key, $match))
              if (!empty($match) && !is_dir(app_base('vendor', null, 'abs') . $match[1] . '/'))
                $count++;

      ?>
      <div id="app_composer-frameInstall"
        class="app_composer-frame-container absolute <?= $count > 0 ? 'selected' : ''; ?>"
        style="overflow: scroll; width: 400px; height: 270px;">
        <form autocomplete="off" spellcheck="false"
          action="<?= APP_URL . '?' . http_build_query(/* APP_QUERY +*/ ['app' => 'composer']) . (defined('APP_ENV') && APP_ENV == 'development' ? '#' : '')  /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>"
          method="POST">
          <div style="display: inline-block; width: 100%; background-color: rgb(225,196,151,.75);">
            <input type="hidden" name="composer[install]" value="" />
            <div style="position: absolute; right: 0; float: right; text-align: center; z-index: 1;">

              <button id="composerInstallSubmit" class="btn" type="submit"
                style="<?= $count > 0 ? 'color: red;' : ''; ?>" value>Install
                (<?= $count > 0 ? $count : ''; ?>)</button>
            </div>
            <div class="text-sm" style="display: inline;">
              <label id="composerInstallLabel" for="composerInstall"
                style="background-color: hsl(343, 100%, 42%); color: white; cursor: pointer;">&#9650;
                <code>Install</code></label>
            </div>

          </div>
          <?php if ($count > 0) { ?>
            <div id=""
              style="display: inline-block; padding: 10px; margin-bottom: 5px; width: 100%; background-color: rgba(235, 216, 186, 0.8);  border: 1px dashed #0078D7;">

              Install (vendor/package):
              <span>
                <ul style="padding-left: 10px;">
                  <?php
                  foreach (composer_json_get('require', []) as $key => $require) {
                    if (preg_match('/.*\/.*:.*/', "$key:$require"))
                      if (preg_match('/(.*\/.*)/', $key, $match))
                        if (!empty($match) && !is_dir(app_base('vendor', null, 'rel') . $match[1] . '/'))
                          echo '<li style="color: red;"><code class="text-sm">' . $match[1] . ':' . '<span style="float: right">' . $require . '</span>' . "</code></li>\n";
                  }
                  ?>
                </ul>
              </span>
            </div>
          <?php } ?>
          <div id="composerInstallForm"
            style="display: inline-block; padding: 10px; margin-bottom: 5px; height: 250px; width: 100%; background-color: rgb(225,196,151,.25);  border: 1px dashed #0078D7;">
            <div style="display: inline-block; width: 100%;">
              <label>Self-update <!--(C:\ProgramData\ComposerSetup\bin\composer.phar)--></label>
              <div style="float: right;">
                <input type="checkbox" name="composer[self-update]" value="true" <?= !file_exists(APP_PATH . 'composer.phar') ? '' : 'checked=""' ?> />
              </div>
            </div>
            <div style="display: inline-block; width: 100%;">
              <label>Optimize Classes</label>
              <div style="float: right;">
                <input type="checkbox" name="composer[optimize-classes]" checked="">
              </div>
            </div>
            <div style="display: inline-block; width: 100%;">
              <label>Update Packages</label>
              <div style="float: right;">
                <input type="checkbox" name="composer[update]" checked="">
              </div>
            </div>
          </div>
        </form>
      </div>

      <?php if (defined(COMPOSER_LATEST) && defined(COMPOSER_VERSION))
        if (version_compare(COMPOSER_LATEST, COMPOSER_VERSION, '>') == 0)
          echo $frameUpdateContents; ?>

    </div>

  </div>
  <!-- future feature: convert div from absolute to fixed. make screen bigger. <div style="position: relative; text-align: right; cursor: pointer; width: 400px; margin: 0 auto; border: 1px solid #000;"> &#9660;</div> -->
</div>

<?php $UI_APP['body'] = ob_get_contents();
ob_end_clean();

if (false) { ?>
  <script type="text/javascript"><?php }
ob_start(); ?>
    (() => {
      const APP_ID = 'tools/registry/composer';
      const root = document.getElementById('app_tools_registry_composer-container');
      if (!root) return;

      // avoid double-binding if this app reloads
      if (root.dataset.bound === '1') return;
      root.dataset.bound = '1';

      // ensure globals exist (keeps inline onclick(...) working if present)
      window.AppMods = window.AppMods || {};
      window.App = window.App || new Proxy({}, {
        get(_t, k) { return window.AppMods[k]; },
        has(_t, k) { return k in window.AppMods; }
      });

      let keywordIndex = 0;

      // ───────── Dropdown helpers ─────────
      function toggleDropdown() {
        const dd = document.getElementById('myDropdown');
        if (dd) dd.classList.toggle('show');
      }

      // Close dropdown if clicking outside .dropbtn
      const closeDropdownOnDocClick = (ev) => {
        if (!(ev.target instanceof Element)) return;
        if (ev.target.matches('.dropbtn')) return;
        document.querySelectorAll('.dropdown-content.show')
          .forEach(el => el.classList.remove('show'));
      };
      document.addEventListener('click', closeDropdownOnDocClick);

      // ───────── Keyword helpers ─────────
      function rm_keyword(id) {
        const safeId = CSS && CSS.escape ? CSS.escape(id) : id.replace(/"/g, '\\"');
        const label = root.querySelector(`label[for="${safeId}"]`) || document.querySelector(`label[for="${safeId}"]`);
        const input = document.getElementById(id);
        if (label) label.remove();
        if (input) input.remove();

        const wrap = document.getElementById('composerAppendKeyword');
        if (!wrap) return;
        const hiddenCount = wrap.querySelectorAll('input[type="hidden"]').length;
        if (hiddenCount === 0) wrap.style.display = 'none';
      }

      function add_keyword(ev) {
        const e = ev || window.event;
        const v = e?.target?.value?.trim();
        if (!v) return;

        const wrap = document.getElementById('composerAppendKeyword');
        if (!wrap) return;
        wrap.style.display = 'inline-block';

        const id = `keyword_${keywordIndex++}`;
        const html = `
      <label class="text-sm" for="${id}">
        <sup onclick="App['${APP_ID}'].rm_keyword('${id}')">[x]</sup>${v}
      </label>
      <input type="hidden" id="${id}" name="composer[config][keywords][]" value="${v}" />
      &nbsp;`;
        wrap.insertAdjacentHTML('beforeend', html);

        const addInput = document.getElementById('composerKeywordAdd');
        if (addInput) addInput.value = '';
      }

      // ───────── Require (prod) ─────────
      const reqPkgInput = document.getElementById('composerReqPkg');
      if (reqPkgInput) {
        reqPkgInput.addEventListener('input', (ev) => {
          if (ev.inputType !== 'insertReplacementText' && ev.inputType != null) return;

          const wrap = document.getElementById('composerAppendRequire');
          if (!wrap) return;
          wrap.style.display = 'inline-block';

          const idx = wrap.querySelectorAll('input[type="text"]').length;
          const id = `pkg_${idx}`;
          const html = `
        <input type="checkbox" checked
               onchange="this.indeterminate=!this.checked;document.getElementById('${id}').disabled=!this.checked" />
        <input type="text" id="${id}" name="composer[config][require][]" value="${ev.target.value}"
               list="composerReqVersResults" size="30"
               onSelect="App['${APP_ID}'].get_version('${id}')" />
        <label for="${id}"></label><br />`;
          wrap.insertAdjacentHTML('beforeend', html);
          ev.target.value = '';
        });
      }

      function get_package(element) { // onSelect="App['tools/registry/composer'].get_package(this)"
        const el = typeof element === 'string' ? document.getElementById(element) : element;
        if (!el) return;
        const val = el.value.trim();
        const list = document.getElementById(`${el.id}s`);
        if (!val || !list) return;

        list.innerHTML = '';
        const url = `https://packagist.org/search.json?q=${encodeURIComponent(val)}`;
        $.getJSON(url, (data) => {
          (data?.results || []).forEach(r => {
            list.insertAdjacentHTML('beforeend', `<option value="${r.name}"></option>`);
          });
        });
      }

      function get_version(id) { // onSelect="App['tools/registry/composer'].get_version('pkg_0')"
        const input = document.getElementById(id);
        if (!input) return;
        const val = input.value.trim();
        const list = document.getElementById('composerReqVersResults');
        if (!val || !list) return;

        list.innerHTML = '';
        //const upstream = `https://repo.packagist.org/p2/${encodeURIComponent(val)}.json`; // proxy.php?url=${encodeURIComponent(upstream)}

        //console.log('Fetching upstream:', upstream);

        const proxied = `/?api=composer&action=packagist&p=${encodeURIComponent(val)}`; // avoid hardcoding host
        $.getJSON(proxied, (data) => {
          list.insertAdjacentHTML('beforeend', `<option value="${val}:dev-master"></option>`);
          const first = (data?.packages?.[val] || [])[0];
          const m = first?.version?.match(/(\d+\.\d+(?:\.\d+)?)/);
          if (m && m[1]) {
            list.insertAdjacentHTML('beforeend', `<option value="${val}:^${m[1]}"></option>`);
          }
        });
      }

      // ───────── Require (dev) ─────────
      const devReqInput = document.getElementById('composerRequireDevPkg');
      if (devReqInput) {
        devReqInput.addEventListener('input', (ev) => {
          if (ev.inputType !== 'insertReplacementText' && ev.inputType != null) return;

          const wrap = document.getElementById('composerAppendRequire-dev');
          if (!wrap) return;
          wrap.style.display = 'inline-block';

          const idx = wrap.querySelectorAll('input[type="text"]').length;
          const id = `pkg-dev_${idx}`;
          const html = `
        <input type="checkbox" checked
               onchange="this.indeterminate=!this.checked;document.getElementById('${id}').disabled=!this.checked" />
        <input type="text" id="${id}" name="composerRequireDevPkgs[]" value="${ev.target.value}"
               list="composerReq-devVersResults" size="30"
               onSelect="App['${APP_ID}'].get_dev_version('${id}')" />
        <label for="${id}"></label><br />`;
          wrap.insertAdjacentHTML('beforeend', html);
          ev.target.value = '';
        });
      }

      function get_dev_package() { // onSelect="App['tools/registry/composer'].get_dev_package()"
        const el = document.getElementById('composerRequireDevPkg');
        if (!el) return;
        const val = el.value.trim();
        const list = document.getElementById('composerReqDevPackages');
        if (!val || !list) return;

        list.innerHTML = '';
        const url = `https://packagist.org/search.json?q=${encodeURIComponent(val)}`;
        $.getJSON(url, (data) => {
          (data?.results || []).forEach(r => {
            list.insertAdjacentHTML('beforeend', `<option value="${r.name}"></option>`);
          });
        });
      }

      function get_dev_version(id) { // onSelect="App['tools/registry/composer'].get_dev_version('pkg-dev_0')"
        const input = document.getElementById(id);
        if (!input) return;
        const val = input.value.trim();
        const list = document.getElementById('composerReq-devVersResults');
        if (!val || !list) return;

        list.innerHTML = '';
        const url = `https://repo.packagist.org/p2/${encodeURIComponent(val)}.json`;
        $.getJSON(url, (data) => {
          list.insertAdjacentHTML('beforeend', `<option value="${val}:dev-master"></option>`);
          const first = (data?.packages?.[val] || [])[0];
          const m = first?.version?.match(/(\d+\.\d+(?:\.\d+)?)/);
          if (m && m[1]) {
            list.insertAdjacentHTML('beforeend', `<option value="${val}:^${m[1]}"></option>`);
          }
        });
      }

      // Register API under both registries (keeps inline calls working)
      const api = {
        init() { },
        toggleDropdown,
        rm_keyword,
        add_keyword,
        get_package,
        get_version,
        get_dev_package,
        get_dev_version
      };
      window.AppMods[APP_ID] = Object.assign(window.AppMods[APP_ID] || {}, api);
      window.App[APP_ID] = window.AppMods[APP_ID];
    })();

  //document.getElementById("bottom").style.zIndex = "1";

  $(document).ready(function () {
    var composer_frame_containers = $(".app_composer-frame-container");
    var totalFrames = composer_frame_containers.length;
    var currentIndex = 0;

    console.log(totalFrames + ' - total frames');

    $("#appComposerAuthLabel").click(function () {
      if ($('#appComposerAuthJsonForm').css('display') == 'none') {
        $('#appComposerAuthLabel').html("&#9650; <code>COMPOSER_HOME/auth.json");
        $('#appComposerAuthJsonForm').slideDown("slow", function () {
          // Animation complete.
        });
      } else {
        $('#appComposerAuthLabel').html("&#9660; <code>COMPOSER_HOME/auth.json</code>");
        $('#appComposerAuthJsonForm').slideUp("slow", function () {
          // Animation complete.
        });
      }
    });

    $("#appComposerVendorJsonLabel").click(function () {
      if ($('#appComposerVendorJsonForm').css('display') == 'none') {
        $('#appComposerVendorJsonLabel').html("&#9650; <code>COMPOSER_PATH/[vendor/*].json</code>");
        $('#appComposerVendorJsonForm').slideDown("slow", function () {
          // Animation complete.
        });
      } else {
        $('#appComposerVendorJsonLabel').html("&#9660; <code>COMPOSER_PATH/[vendor/*].json</code>");
        $('#appComposerVendorJsonForm').slideUp("slow", function () {
          // Animation complete.
        });
      }
    });

    $("#appComposerJsonLabel").click(function () {
      if ($('#appComposerJsonForm').css('display') == 'none') {
        $('#appComposerJsonLabel').html("&#9650; <code>COMPOSER_PATH/composer.json</code>");
        $('#appComposerJsonForm').slideDown("slow", function () {
          // Animation complete.
        });
      } else {
        $('#appComposerJsonLabel').html("&#9660; <code>COMPOSER_PATH/composer.json</code>");
        $('#appComposerJsonForm').slideUp("slow", function () {
          // Animation complete.
        });
      }
    });

    $("#app_composer-frameMenuInit").click(function () {
      currentIndex = 1;
      $("#app_composer-frameMenuPrev").html('&lt; Menu');
      $("#app_composer-frameMenuNext").html('Conf &gt;');
      composer_frame_containers.removeClass("selected");
      composer_frame_containers.eq(currentIndex).addClass('selected');
    });

    $("#app_composer-frameMenuConf").click(function () {
      currentIndex = 2;
      $("#app_composer-frameMenuPrev").html('&lt; Init');
      $("#app_composer-frameMenuNext").html('Install &gt;');
      composer_frame_containers.removeClass("selected");
      composer_frame_containers.eq(currentIndex).addClass('selected');
    });

    $("#app_composer-frameMenuInstall").click(function () {
      currentIndex = 3;
      $("#app_composer-frameMenuPrev").html('&lt; Conf');
      $("#app_composer-frameMenuNext").html('Update &gt;');
      composer_frame_containers.removeClass("selected");
      composer_frame_containers.eq(currentIndex).addClass('selected');
    });

    $("#app_composer-frameMenuUpdate").click(function () {
      currentIndex = 4;
      $("#app_composer-frameMenuPrev").html('&lt; Install');
      $("#app_composer-frameMenuNext").html('Menu &gt;');
      composer_frame_containers.removeClass("selected");
      composer_frame_containers.eq(currentIndex).addClass('selected');
    });

    $(".composer-home").click(function () {
      currentIndex = -1;
      composer_frame_containers.removeClass("selected");
      //composer_frame_containers.eq(currentIndex).addClass('selected');
    });

    $(".composer-menu").click(function () {
      currentIndex = 0;
      composer_frame_containers.removeClass("selected");
      composer_frame_containers.eq(currentIndex).addClass('selected');
    });

    $("#app_composer-frameMenuPrev").click(function () {
      if (currentIndex <= 0) currentIndex = 5; console.log(currentIndex + '!=' + totalFrames); currentIndex--; if
        (currentIndex >= totalFrames) {
        currentIndex = 0;
      }
      if (currentIndex == 0) {
        $("#app_composer-frameMenuPrev").html('&lt; Update');
        $("#app_composer-frameMenuNext").html('Init &gt;');
      } else if (currentIndex == 1) {
        $("#app_composer-frameMenuPrev").html('&lt; Menu');
        $("#app_composer-frameMenuNext").html('Conf &gt;');
      } else if (currentIndex == 2) {
        $("#app_composer-frameMenuPrev").html('&lt; Init');
        $("#app_composer-frameMenuNext").html('Install &gt;');
      } else if (currentIndex == 3) {
        $("#app_composer-frameMenuPrev").html('&lt; Conf');
        $("#app_composer-frameMenuNext").html('Update &gt;');
      } else if (currentIndex == 4) {
        $("#app_composer-frameMenuPrev").html('&lt; Install');
        $("#app_composer-frameMenuNext").html('Menu &gt;');
      }

      //else
      console.log('decided: ' + currentIndex);
      composer_frame_containers.removeClass("selected");
      composer_frame_containers.eq(currentIndex).addClass('selected');

      //currentIndex--;
      console.log(currentIndex);
    });

    $("#app_composer-frameMenuNext").click(function () {
      currentIndex++;
      console.log(currentIndex + '!=' + totalFrames);
      if (currentIndex >= totalFrames) {
        currentIndex = 0;
      }
      if (currentIndex == 0) {
        $("#app_composer-frameMenuPrev").html('&lt; Update');
        $("#app_composer-frameMenuNext").html('Init &gt;');
      } else if (currentIndex == 1) {
        $("#app_composer-frameMenuPrev").html('&lt; Menu');
        $("#app_composer-frameMenuNext").html('Conf &gt;');
      } else if (currentIndex == 2) {
        $("#app_composer-frameMenuPrev").html('&lt; Init');
        $("#app_composer-frameMenuNext").html('Install &gt;');
      } else if (currentIndex == 3) {
        $("#app_composer-frameMenuPrev").html('&lt; Conf');
        $("#app_composer-frameMenuNext").html('Update &gt;');
      } else if (currentIndex == 4) {
        $("#app_composer-frameMenuPrev").html('&lt; Install');
        $("#app_composer-frameMenuNext").html('Menu &gt;');
      }
      if (currentIndex < 0) currentIndex++; //else console.log('decided: ' + currentIndex);
      composer_frame_containers.removeClass("selected"); // composer_frame_containers.css("z-index", 0); // Reset z-index for all elements
      composer_frame_containers.eq(currentIndex).addClass(' selected'); // css("z-index", totalFrames); // Set top layer
      // z - index
    }); $("#frameSelector").change(function () {
      var selectedIndex = parseInt($(this).val(), 10);
      currentIndex = selectedIndex; $(".app_composer-frame-container").removeClass("selected"); // Remove selected class from all containers
      $(".app_composer-frame-container").eq(currentIndex).addClass("selected"); // Apply selected class to the chosen container
    }); /* $('select').on('change', function (e) { var optionSelected=$("option:selected", this); var valueSelected=this.value; }); */
  });

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

  $__isDirect = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__;
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

    $bootstrap = dirname(__DIR__) . '/bootstrap/bootstrap.php';
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
    $localPath = rtrim(app_base('public', null, 'abs'), DIRECTORY_SEPARATOR) . '/assets/vendor/tailwindcss-' . $version . '.js';
    $localRelDir = rtrim(app_base('public', null, 'rel'), '/'); // e.g. 'public/assets/'
    $localRel = $localRelDir . '/assets/vendor/tailwindcss-' . $version . '.js';

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

    return $localRel; // e.g. "assets/vendor/tailwindcss-3.3.5.js"
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