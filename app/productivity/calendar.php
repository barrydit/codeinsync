<?php
// app/productivity/calendar.php
declare(strict_types=1);

if (!defined('APP_BOOTSTRAPPED')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/bootstrap.php';
}

global $errors, $asset;


if (!function_exists('calendar_h')) {
    function calendar_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('calendar_asset')) {
    function calendar_asset(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (class_exists('UrlContext') && method_exists('UrlContext', 'assetPath')) {
            return UrlContext::assetPath($path);
        }

        $prefix = UrlContext::getBaseUrl() ?? (defined('APP_PUBLIC_RELATIVE_URL') ? APP_PUBLIC_RELATIVE_URL : 'public/');
        return rtrim($prefix, '/') . ($prefix === '' ? '' : '/') . $path;
    }
}

if (!function_exists('calendar_app_url')) {
    function calendar_app_url(array $params = [], string $fragment = ''): string
    {
        $query = array_merge($_GET, $params);

        $base = '';
        if (class_exists('UrlContext') && method_exists('UrlContext', 'getBaseUrl')) {
            $base = UrlContext::getBaseUrl();
        } elseif (defined('APP_URL')) {
            $base = APP_URL;
        }

        return rtrim($base, '/')
            . ($query !== [] ? '?' . http_build_query($query) : '')
            . $fragment;
    }
}

$app_id = 'productivity/calendar';           // full path-style id

// Always normalize slashes first
$app_norm = str_replace('\\', '/', $app_id);

// Last segment only (for titles, labels, etc.)
$slug = basename($app_norm);                    // "console"

// Sanitized full path for DOM ids (underscores only from non [A-Za-z0-9_ -])
$key = preg_replace('/[^\w-]+/', '_', $app_norm);  // "core_console"

// If you prefer strictly underscores (no hyphens), do: '/[^\w]+/'

// Core DOM ids/selectors
$container_id = "app_{$key}-container";         // "app_core_console-container"
$selector = "#{$container_id}";

// Useful companion ids
$style_id = "style-{$key}";                    // "style-core_console"
$script_id = "script-{$key}";                   // "script-core_console"

// Optional: data attributes you can stamp on the container for easy introspection
$data_attrs = sprintf(
    'data-app-path="%s" data-app-key="%s" data-app-slug="%s"',
    htmlspecialchars($app_norm, ENT_QUOTES),
    htmlspecialchars($key, ENT_QUOTES),
    htmlspecialchars($slug, ENT_QUOTES),
);

//global $errors;
//if (isset($_GET['path']) && isset($_GET['file']) && $path = realpath($_GET['path'] . $_GET['file']))

//$errors->{'TEXT_MANAGER'} = $path . "\n" . 'File Modified:    Rights:    Date of creation: ';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_GET['app']) && $_GET['app'] == 'errors' && isset($_POST['ace_save']))

        if (isset($_POST['ace_path']) && realpath($path = APP_PATH . (APP_ROOT ?? (APP_BASE['clients'] . ($_GET['client'] . '/' . ($_GET['domain'] ?? '') ?? $_GET['domain'] ?? ''))) . ($_GET['path'] . '/' . $_GET['file'] ?? $_POST['ace_path']))) {
            //dd($path, false);   
            if (isset($_POST['ace_contents']))
                //dd($_POST['ace_contents']);
                file_put_contents($path, $_POST['ace_contents']);

            //dd($_POST, true);
            //http://localhost/Array?app=errors&path=&file=test.txt    obj.prop.second = value    obj->prop->second = value
            //dd( APP_URL . '1234?' . http_build_query(['path' => dirname( $_POST['ace_path']), 'app' => 'errors', 'file' => basename($path)]), true);


            //dd(APP_URL_BASE);

            die(header('Location: ' . APP_URL_BASE['scheme'] . '://' . APP_URL_BASE['host'] . '/?' . http_build_query(APP_QUERY + ['path' => dirname($_POST['ace_path']), 'file' => basename($path)])));
        } else
            dd("Path: $path was not found.", true);
    //dd($_POST);

    //  if (isset($_GET['file'])) {
//    file_put_contents($projectRoot.(!$_POST['path'] ? '' : DIRECTORY_SEPARATOR.$_POST['path']).DIRECTORY_SEPARATOR.$_POST['file'], $_POST['editor']);
//  }

    /*
        if (isset($_POST['cmd'])) {
          if ($_POST['cmd'] && $_POST['cmd'] != '') 
            if (preg_match('/^install/i', $_POST['cmd']))
              include('templates/' . preg_split("/^install (\s*+)/i", $_POST['cmd'])[1] . '.php');
            else if (preg_match('/^php(:?(.*))/i', $_POST['cmd'], $match))
              exec($_POST['cmd'], $output);
            else if (preg_match('/^composer(:?(.*))/i', $_POST['cmd'], $match)) {
            $output[] = 'env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC . ' ' . $match[1];
    $proc=proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC . ' ' . $match[1],
      array(
        array("pipe","r"),
        array("pipe","w"),
        array("pipe","w")
      ),
      $pipes);
    [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    $output[] = 'Composer: ' . (!isset($stdout) ? NULL : $stdout . (!isset($stderr) ? NULL : ' Error: ' . $stderr) . (!isset($exitCode) ? NULL : ' Exit Code: ' . $exitCode));
    $output[] = $_POST['cmd'];

            } else if (preg_match('/^git(:?(.*))/i', $_POST['cmd'], $match)) {
            $output[] = APP_SUDO . GIT_EXEC . ' ' . $match[1];
    $proc=proc_open(APP_SUDO . GIT_EXEC . ' ' . $match[1],
      array(
        array("pipe","r"),
        array("pipe","w"),
        array("pipe","w")
      ),
      $pipes);
    [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    $output[] = 'Composer: ' . (!isset($stdout) ? NULL : $stdout . (!isset($stderr) ? NULL : ' Error: ' . $stderr) . (!isset($exitCode) ? NULL : ' Exit Code: ' . $exitCode));
    $output[] = $_POST['cmd'];

            }

              //exec($_POST['cmd'], $output);
            else echo $_POST['cmd'] . "\n";
          //else var_dump(NULL); // eval('echo $repo->status();')
          if (!empty($output)) echo 'PHP >>> ' . join("\n... <<< ", $output) . "\n"; // var_dump($output);
          //else var_dump(get_class_methods($repo));
          exit();
        }
    */
}

/*
$proc=proc_open('sudo ' . GIT_EXEC . ' ' . $match[1],
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
          [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
          $output[] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
*/

if (defined('GIT_EXEC'))
    if (is_dir($path = APP_BASE['public'] . 'assets/vendor/ace') && empty(glob($path)))
        exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git public/assets/vendor/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
    elseif (!is_dir($path)) {
        if (!mkdir($path, 0755, true))
            $errors['GIT-CLONE-ACE'] = ' public/assets/vendor/ace does not exist.';
        exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git public/assets/vendor/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
    }

ob_start(); ?>
<?= $selector ?> {
position : absolute;
width : 445px;
min-height : 420px;
/* border: 1px solid black; */

top : 60px;
right : 42px;
z-index : 1;
margin-bottom: 0;
/* resize: both; Make the div resizable */
/* overflow: hidden; Hide overflow to ensure proper resizing */
}

<?= $selector ?>.selected {
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

#ui_errors {
width : 100%;
height : calc(100% - 80px);
position : absolute;
}
#errors {
margin : 0;
position : relative;
/*resize: both;*/
overflow : auto;
white-space : pre-wrap;
/*width: 100%;
height: 100%;*/
}


#app_medication_log-container {
width : 320px;
height : 220px;
/* border: 1px solid black; */
position : absolute;
z-index : 1;
/* resize: both; Make the div resizable */
/* overflow: hidden; Hide overflow to ensure proper resizing */

border: 1px solid #000;
}

#app_medication_log-container.selected {
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

#ui_medication_log {
width : 100%;
height : calc(100% - 80px);
position : absolute;
}
#medication_log {
margin : 0;
position : relative;
/*resize: both;*/
overflow : auto;
white-space : pre-wrap;
/*width: 100%;
height: 100%;*/
}

input {
color : black;
}
.containerTbl {
display : flex;
justify-content : center;
align-items : center;
height : 100vh;
}
table {
border-collapse : collapse;
}
td, th {
border : black solid 1px;
padding : 8px;
}
img {
display : inline;
}

/* Hide the native radio input */
input[type="radio"].green-radio {
appearance: none;
-webkit-appearance: none;
background-color: lightgreen; /* Always green */
border-color: green;
margin: 0 5px 0 0;
width: 1em;
height: 1em;
border: 2px solid white;
border-radius: 50%;
display: inline-block;
vertical-align: middle;
cursor: pointer;
}

input[type="radio"].green-radio:checked {
/* Optional: change border when selected, or leave same */
border-color: green;
}

/* Hide the native radio input */
input[type="radio"].red-radio {
appearance: none;
-webkit-appearance: none;
background-color: red; /* Always green */
border-color: red;
margin: 0 5px 0 0;
width: 1em;
height: 1em;
border: 2px solid white;
border-radius: 50%;
display: inline-block;
vertical-align: middle;
cursor: pointer;
}

input[type="radio"].red-radio:checked {
/* Optional: change border when selected, or leave same */
border-color: darkred;
}
<?php $UI_APP['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<div class="window-header ui-widget-header ui-draggable-handle" data-drag-handle="true"
    style="position: absolute; width: 445px; min-height: 420px; top: 60px; right: 42px; z-index: 1000;">
    <div class="window-header ui-widget-header ui-draggable-handle"
        style="position: relative; display: block; width: 100%; min-height: 45px; cursor: move; padding: 0; border-bottom: 1px solid #000; background-color: rgba(255, 255, 255, 0.8); z-index: 3;">
        <label class="calendar-home" style="cursor: pointer;">
            <div class="" style="position: relative; display: inline-block; top: 0; left: 0;">
                <img src="<?= calendar_h(calendar_asset('assets/images/calendar_icon.png')) ?>" width="41"
                    height="41" />
            </div>
        </label>
        <div
            style="position: absolute; top: 11px; left: 45px; display: inline-block; text-align: center; display: inline;">
            <span style="background-color: #38B1FF; color: #FFF; margin-top: 10px;">PHP Calendar
                <?= /* (version_compare(NPM_LATEST, NPM_VERSION, '>') != 0 ? 'v'.substr(NPM_LATEST, 0, similar_text(NPM_LATEST, NPM_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(NPM_LATEST, similar_text(NPM_LATEST, NPM_VERSION)) . '</span>' : 'v'.NPM_VERSION ); */ NULL; ?></span>
            <span style="background-color: #0078D7; color: white;"><code id="AceEditorVersionBox" class="text-sm"
                    style="background-color: white; color: #0078D7;"></code></span>
        </div>
        <div
            style="position: absolute; right: 35px; display: inline-block; text-align: center; color: blue; width: auto; border: 2px solid lightblue; margin-top: 20px;">
            <small><a href="#medication_log" onclick="
      const el = document.getElementById('app_medication_log-container');
      el.style.display = (el.style.display === 'none') ? 'block' : 'none';
      return false;
   " style="display:block; color: red; text-decoration-line: underline; text-decoration-style: solid; font-size: 13px;">
                    Medication Log
                </a></small>
        </div>
        <div style="display: inline; float: right; text-align: center; color: blue;"><code
                style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="closeApp('productivity/calendar', {fullReset:true})">[X]</a></code>
        </div>
    </div>

    <!-- form id="" name="ace_form"
        style="position: relative; width: 100%; height: 100%; border: 3px dashed #38B1FF; background-color: rgba(56,177,255,0.6);"
        action="app/directory.php<?= /*basename(__FILE__) . */ '?' . http_build_query(APP_QUERY + ['app' => 'errors']) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>"
        method="POST" onsubmit="syncAceContent()">
        <input type="hidden" name="ace_path" value="<?= /* APP_BASE['public']; */ NULL; ?>" / -->

    <div class="window-body"
        style="position: relative; width: calc(100% - 20px); background-color: rgba(255, 255, 255, 0.8); padding: 10px; display: block;">
        <div style="display: none; text-align: left; width: 125px;">
            <div class="npm-menu text-sm"
                style="cursor: pointer; font-weight: bold; padding-left: 25px; border: 1px solid #000;">Main Menu
            </div>
            <div class="text-xs inline-block border border-black w-[400px]" style="text-align: center;">

            </div>
        </div>
        <!-- onclick="document.getElementsByClassName('ace_text-input')[0].value = globalEditor.getSession().getValue(); document.getElementsByClassName('ace_text-input')[0].name = 'editor';"   -->
        <div style="position:absolute; right: 100px; top: 10px; display: inline-block; width: auto; text-align: right;">
            <!-- input type="submit" name="ace_save" value="Save" class="btn" style="margin: -5px 5px 5px 0;">
            </div -->

            <!-- div class="absolute"
                style="position: absolute; display: inline-block; top: 5px; right: 0; text-align: right; float: right;">
                <div class="text-xs" style="position: relative; display: inline-block;">
                    + 495 <a href="https://github.com/ajaxorg/ace/graphs/contributors">contributors</a>
                    <br />< a href="https://github.com/ajaxorg"><img src="assets/images/node.js.png" title="https://github.com/nodejs" width="18" height="18" /></a>
                    <a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;"
                        href="https://ace.c9.io/" title="https://ace.c9.io/">https://ace.c9.io/</a>
                </div>
            </div -->
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
echo '<a href="?app=errors&path=' . basename($path) . '">'
. '<img src="assets/images/directory.png" width="50" height="32" /><br />' . basename($path) . '</a>' . "\n";
elseif (is_file($path))
echo '<a href="?app=errors&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '">'
. '<img src="assets/images/php_file.png" width="40" height="50" /><br />' . basename($path) . '</a>' . "\n";
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

        <div style="position: relative; margin: 0 auto; width: calc(100% - 2px); height: 100%; float: right;">
            <!--
      <div id="app_calendar-frameMenu" class="app_calendar-frame-container absolute selected" style="background-color: rgb(225,196,151,.75); margin-top: 8px; height: 100%;">
-->

            <!--   A (<?= /* $path */ ''; ?>) future note: keep ace-editor nice and tight ... no spaces, as it interferes with the content window.
 https://scribbled.space/ace-editor-setup-usage/ -->

            <div id="ui_calendar" class="errors"
                style="position: relative; display: <?= isset($_GET['file']) && isset($_GET['path']) && is_file($_GET['path'] . $_GET['file']) ? 'block' : 'block' ?>; z-index: 1;">
                <div id="app_calendar-frameInit" class="app_calendar-frame-container absolute"
                    style="overflow: hidden; height: 100%; position: relative; margin: 0 auto;">
                    <div id="app_medication_log-container" class=""
                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: block; resize: both; overflow: hidden; z-index: 2;">
                        <div class="ui-widget-header"
                            style="position: relative; display: inline-block; width: 100%; cursor: move; border-bottom: 1px solid #000;background-color: #FFF;">
                            <label class="medication_log-home" style="cursor: pointer;">
                                <div class="" style="position: relative; display: inline-block; top: 0; left: 0;">
                                    <img src="<?= calendar_h(calendar_asset('assets/images/medication_log_icon.png')) ?>"
                                        width="53" height="32" />
                                </div>
                            </label>
                            <div
                                style="position: absolute; top: 11px; left: 45px; display: inline-block; text-align: center; display: inline;">
                                <span style="background-color: #38B1FF; color: #FFF; margin-top: 10px;">Medication log
                                    <?= /* (version_compare(NPM_LATEST, NPM_VERSION, '>') != 0 ? 'v'.substr(NPM_LATEST, 0, similar_text(NPM_LATEST, NPM_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(NPM_LATEST, similar_text(NPM_LATEST, NPM_VERSION)) . '</span>' : 'v'.NPM_VERSION ); */ NULL; ?></span>
                                <span style="background-color: #0078D7; color: white;"><code id="AceEditorVersionBox"
                                        class="text-sm" style="background-color: white; color: #0078D7;"></code></span>
                            </div>

                            <div style="display: inline; float: right; text-align: center; color: blue;"><code
                                    style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_medication_log-container').style.display='none';">[X]</a></code>
                            </div>
                        </div>

                        <div class="ui-widget-content"
                            style="position: relative; display: block; margin: 0 auto; width: calc(100% - 2px); height: 45px; background-color: rgba(251,247,241); text-align: center; font-weight: bold; padding-top: 0px;">
                            Did you remember to <br />take your medication?
                            <div style="clear: both;"></div>
                        </div>


                        <div style="position: relative; margin: 0 auto; width: calc(100% - 2px); height: 100%;">
                            <!--
      <div id="app_medication_log-frameMenu" class="app_medication_log-frame-container absolute selected" style="background-color: rgb(225,196,151,.75); margin-top: 8px; height: 100%;">
-->

                            <!--   A (<?= /* $path */ ''; ?>) future note: keep ace-editor nice and tight ... no spaces, as it interferes with the content window.
 https://scribbled.space/ace-editor-setup-usage/ -->

                            <div id="ui_medication_log" class="medication_log"
                                style="display: <?= isset($_GET['file']) && isset($_GET['path']) && is_file($_GET['path'] . $_GET['file']) ? 'block' : 'block' ?>; z-index: 1;">
                                <div style="background-color: white; height: 100%; width: 100%; overflow: hidden;">
                                    <div
                                        style="text-align: center; width: 92%; margin-left:auto; margin-right:auto; margin-top: 20px;">
                                        <form autocomplete="off" spellcheck="false" method="POST"
                                            action="<?= calendar_h(calendar_app_url(['api' => 'calendar'])) ?>">
                                            <input type="hidden" name="app" value="medication_log" />
                                            <input id="date_slot" type="date" name="date"
                                                value="<?= date('Y-m-d') ?>" />
                                            <input id="time_slot" type="time" name="time_slot"
                                                value="<?= date('H:i') ?>" /><br />&nbsp;&nbsp;
                                            <input type="radio" name="status" value="taken"
                                                class="green-radio" />Taken&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="radio" name="status" value="missed"
                                                class="red-radio" />Missed<br />
                                            <input type="text" name="note" value="" />
                                            <input type="submit" value="Save" />
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- div style="position: relative; display: inline-block; width: 100%; height: 100%; padding-left: 10px;">

      <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ ['app' => 'medication_log']*/) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
        <input type="hidden" name="app" value="medication_log" />
      <?php $path = realpath(getcwd() . (isset($_GET['path']) ? DIRECTORY_SEPARATOR . $_GET['path'] : '')) . DIRECTORY_SEPARATOR;
      if (isset($_GET['path'])) { ?>
       <input type="hidden" name="path" value="<?= $_GET['path']; ?>" /> 
      <?php }
      echo '<span title="' . $path . '">' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '</span>'; /* $path; */ ?>
        <select name="path" onchange="this.form.submit();">
          <option value>.</option>
          <option value>..</option>
<?php
if ($path)
    foreach (array_filter(glob($path . DIRECTORY_SEPARATOR . '*'), 'is_dir') as $dir) {
        echo '<option value="' . (isset($_GET['path']) ? $_GET['path'] . DIRECTORY_SEPARATOR : '') . basename($dir) . '"' . (isset($_GET['path']) && $_GET['path'] == basename($dir) ? ' selected' : '') . '>' . basename($dir) . '</option>' . "\n";
    }
?>
        </select>
      </form>
 / <input type="text" name="file" value="index.php" /> 

      <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ ['app' => 'medication_log']*/) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
      <input type="hidden" name="app" value="medication_log" />

      <input type="hidden" name="path" value="<?= $_GET['path'] ?? '' ?>" />

        <select name="file" onchange="this.form.submit();">
          <option value="">---</option>
<?php
if ($path)
    foreach (array_filter(glob($path . DIRECTORY_SEPARATOR . '*.php'), 'is_file') as $file) {
        echo '<option value="' . basename($file) . '"' . (isset($_GET['file']) && $_GET['file'] == basename($file) ? ' selected' : '') . '>' . basename($file) . '</option>' . "\n";
    }
?>
        </select>
      </form>
      </div> -->



                        <!-- <pre id="ace-editor" class="medication_log"></pre> -->

                    </div>

                    <div style="background-color: white; height: 100%; width: 100%; overflow: hidden;">
                        <?php function generateCalendar($month, $year)
                        {
                            $firstDayOfMonth = strtotime("$year-$month-01");
                            $totalDays = date('t', $firstDayOfMonth);
                            $startingDay = date('w', $firstDayOfMonth);

                            $currentDate = new DateTime();
                            $nextMonth = (clone $currentDate)->modify('+1 month')->format('F');
                            $prevMonth = (clone $currentDate)->modify('-1 month')->format('F');

                            $calendar = "<table border='1' cellpadding='5' cellspacing='0'>";
                            $calendar .= '<tr><th colspan="7"><div class="float-left w-auto" style="display: inline;">
                    <a class="text-sm" id="app_calendar-frameMenuPrev"
                        href="' . (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV === 'development' ? '#!' : '#') . '">&lt; ' . $prevMonth . '</a> | </div>' . date('F Y', $firstDayOfMonth) .
                                '<div class="float-right w-auto" style="display: inline;"> | 
                    <a class="text-sm" id="app_calendar-frameMenuNext"
                        href="' . (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV === 'development' ? '#!' : '#') . '">' . $nextMonth . ' &gt;</a></div></th></tr>';

                            $today = date('Y-m-d');

                            // Determine background color
                            $isToday = $currentDate == $today;
                            $bgColor = $isToday ? 'background-color: lightblue;' : '';

                            $calendar .= "<tr><th style=\"$bgColor\">Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>";
                            "<tr>";

                            // Print empty cells before the first day
                            for ($i = 0; $i < $startingDay; $i++) {
                                $calendar .= "<td></td>";
                            }

                            // Load medication log
                            $logFile = APP_BASE['data'] . 'medication_log.json';
                            //error_log("Loading medication log from: $logFile");
                            $logData = json_decode(file_get_contents($logFile), true);

                            // Convert log to date-indexed array
                            $medLogByDate = [];
                            foreach ($logData as $entry) {
                                $date = $entry['date'];
                                $statuses = array_column($entry['doses'], 'status');
                                $medLogByDate[$date] = $statuses;
                            }

                            // Print days of the month
                            for ($day = 1; $day <= $totalDays; $day++) {
                                $dayStr = str_pad((string) $day, 2, '0', STR_PAD_LEFT);
                                $currentDate = "$year-$month-$dayStr";

                                // Determine dose indicators
                                $indicators = '';
                                if (isset($medLogByDate[$currentDate])) {
                                    $statuses = $medLogByDate[$currentDate];
                                    foreach ($statuses as $status) {
                                        $color = $status == 'taken' ? 'limegreen' : 'red';
                                        $indicators .= "<div style='width:10px;height:10px;background:$color;margin:1px;border-radius:50%;'></div>";
                                    }
                                }

                                // Build cell
                                $calendar .= "<td style=\"$bgColor; vertical-align: top; text-align: center; min-width: 40px;" . (date('d') == $day ? 'background-color: lightgreen;' : '') . "\">";
                                $calendar .= "<a href=\"?date-app=calendar&day=$day\">$day</a>";
                                $calendar .= "<div style='display:flex;justify-content:center;gap:2px;'>$indicators</div>";
                                $calendar .= "</td>";

                                if (($startingDay + $day) % 7 == 0) {
                                    $calendar .= "</tr><tr>";
                                }
                            }
                            /*
                                                        // Print days of the month
                                                        for ($day = 1; $day <= $totalDays; $day++) {
                                                            $calendar .= "<td style=\"" . ($day == date('d') ? 'background-color: lightblue;' : '') . "\"><a href=\"?app=calendar&day=$day\">$day</a></td>";

                                                            if (($startingDay + $day) % 7 == 0) {
                                                                $calendar .= "</tr><tr>";
                                                            }
                                                        }
                            */
                            // Complete the last row with empty cells
                            while (($startingDay + $day) % 7 != 1) {
                                $calendar .= "<td></td>";
                                $day++;
                            }

                            $calendar .= "</tr></table>";
                            return $calendar;
                        }

                        // Get current month and year
                        $month = date('m');
                        $year = date('Y');

                        echo generateCalendar($month, $year); ?>
                    </div>
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

                <div id="app_calendar-frameExtra"
                    style="position: relative; width: 100%; height: 100%; border: 1px #000 solid;">


                </div>

            </div>

        </div>
        <!-- /form -->
        <!-- div style="position: relative; display: inline-block; width: 100%; height: 100%; padding-left: 10px;">

      <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ ['app' => 'errors']*/) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
        <input type="hidden" name="app" value="errors" />
      <?php $path = realpath(getcwd() . (isset($_GET['path']) ? DIRECTORY_SEPARATOR . $_GET['path'] : '')) . DIRECTORY_SEPARATOR;
      if (isset($_GET['path'])) { ?>
       <input type="hidden" name="path" value="<?= $_GET['path']; ?>" /> 
      <?php }
      echo '<span title="' . $path . '">' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '</span>'; /* $path; */ ?>
        <select name="path" onchange="this.form.submit();">
          <option value>.</option>
          <option value>..</option>
<?php
if ($path)
    foreach (array_filter(glob($path . DIRECTORY_SEPARATOR . '*'), 'is_dir') as $dir) {
        echo '<option value="' . (isset($_GET['path']) ? $_GET['path'] . DIRECTORY_SEPARATOR : '') . basename($dir) . '"' . (isset($_GET['path']) && $_GET['path'] == basename($dir) ? ' selected' : '') . '>' . basename($dir) . '</option>' . "\n";
    }
?>
        </select>
      </form>
 / <input type="text" name="file" value="index.php" /> 

      <form style="display: inline;" autocomplete="off" spellcheck="false" action="<?= APP_URL . /*basename(__FILE__) .*/ '?' . http_build_query(APP_QUERY /*+ ['app' => 'errors']*/) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
      <input type="hidden" name="app" value="errors" />

      <input type="hidden" name="path" value="<?= (isset($_GET['path']) ? $_GET['path'] : '') ?>" />

        <select name="file" onchange="this.form.submit();">
          <option value="">---</option>
<?php
if ($path)
    foreach (array_filter(glob($path . DIRECTORY_SEPARATOR . '*.php'), 'is_file') as $file) {
        echo '<option value="' . basename($file) . '"' . (isset($_GET['file']) && $_GET['file'] == basename($file) ? ' selected' : '') . '>' . basename($file) . '</option>' . "\n";
    }
?>
        </select>
      </form>
      </div> -->



        <!-- <pre id="ace-editor" class="errors"></pre> -->

    </div>
</div>

<?php $UI_APP['body'] = ob_get_contents();
ob_end_clean();

if (false) { ?>
    <script type="text/javascript"><?php }
ob_start();
//if (isset($_GET['client']) && $_GET['client'] != '') { 
//if (isset($_GET['domain']) && $_GET['domain'] != '') {
?>

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
    is_dir($path = APP_BASE['public'] . 'assets/vendor/') or mkdir($path, 0755, true);
    if (is_file($path . 'tailwindcss-3.3.5.js')) {
        if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path . 'tailwindcss-3.3.5.js'))))) / 86400)) <= 0) {
            $url = 'https://cdn.tailwindcss.com';
            $handle = curl_init($url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

            if (!empty($js = curl_exec($handle)))
                file_put_contents("{$path}tailwindcss-3.3.5.js", $js) or $errors['JS-TAILWIND'] = "$url returned empty.";
        }
    } else {
        $url = 'https://cdn.tailwindcss.com';
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        if (!empty($js = curl_exec($handle)))
            file_put_contents("{$path}tailwindcss-3.3.5.js", $js) or $errors['JS-TAILWIND'] = "$url returned empty.";
    }
    unset($path);
    ?>

    <script src="<?= calendar_h(calendar_asset('assets/vendor/tailwindcss-3.3.5.js')) ?>"></script>

    <style type="text/tailwindcss">
        <?= $UI_APP['style']; ?>
    </style>
</head>

<body>
    <?= $UI_APP['body']; ?>

    <?php
    is_dir($path = APP_BASE['public'] . 'assets/vendor/jquery/') or mkdir($path, 0755, true);
    if (is_file($path . 'jquery-3.7.1.min.js')) {
        if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime($path . 'jquery-3.7.1.min.js'))))) / 86400)) <= 0) {
            $url = 'https://code.jquery.com/jquery-3.7.1.min.js';
            $handle = curl_init($url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

            if (!empty($js = curl_exec($handle)))
                file_put_contents("{$path}jquery-3.7.1.min.js", $js) or $errors['JS-JQUERY'] = "$url returned empty.";
        }
    } else {
        $url = 'https://code.jquery.com/jquery-3.7.1.min.js';
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        if (!empty($js = curl_exec($handle)))
            file_put_contents("{$path}jquery-3.7.1.min.js", $js) or $errors['JS-JQUERY'] = "$url returned empty.";
    }
    unset($path); ?>
    <script
        src="<?= calendar_h(defined('APP_IS_ONLINE') && APP_IS_ONLINE && check_http_status('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : calendar_asset('assets/vendor/jquery/jquery-3.7.1.min.js')) ?>"></script>
    <!-- You need to include jQueryUI for the extended easing options. -->
    <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->

    <script
        src="<?= calendar_h(defined('APP_IS_ONLINE') && APP_IS_ONLINE && check_http_status('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js') ? 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js' : calendar_asset('assets/vendor/jquery/jquery-ui-1.12.1.js')) ?>"></script>
    <!-- Uncaught ReferenceError: jQuery is not defined -->

    <script src="<?= calendar_h(calendar_asset('assets/vendor/ace/src/ace.js')) ?>" type="text/javascript"
        charset="utf-8"></script>
    <script src="<?= calendar_h(calendar_asset('assets/vendor/ace/src/ext-language_tools.js')) ?>"
        type="text/javascript" charset="utf-8"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>

  <script src="assets/vendor/ace/src/mode-php.js" type="text/javascript" charset="utf-8"></script> -->
    <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->

    <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
    <!-- script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script -->
    <!-- <script src="assets/vendor/jquery/jquery.min.js"></script> -->
    <script>

        let calendarDragState = null;
        let calendarTopZIndex = 1000;

        function makeDraggable(windowId, handleSelector) {
            const windowElement = document.getElementById(windowId);

            if (!windowElement) {
                return;
            }

            const handleElement = windowElement.querySelector(handleSelector || '.window-header, .ui-widget-header');

            if (!handleElement) {
                return;
            }

            if (windowElement.dataset.draggableBound === '1') {
                return;
            }

            windowElement.dataset.draggableBound = '1';
            windowElement.style.position = windowElement.style.position || 'absolute';
            handleElement.style.cursor = 'move';

            handleElement.addEventListener('mousedown', function (event) {
                if (event.button !== 0) {
                    return;
                }

                if (event.target.closest('a, button, input, select, textarea, label')) {
                    return;
                }

                const rect = windowElement.getBoundingClientRect();

                windowElement.style.right = 'auto';
                windowElement.style.bottom = 'auto';
                windowElement.style.transform = 'none';
                windowElement.style.left = rect.left + 'px';
                windowElement.style.top = rect.top + 'px';
                windowElement.style.zIndex = String(++calendarTopZIndex);

                calendarDragState = {
                    element: windowElement,
                    offsetX: event.clientX - rect.left,
                    offsetY: event.clientY - rect.top
                };

                event.preventDefault();
            });
        }

        document.addEventListener('mousemove', function (event) {
            if (!calendarDragState) {
                return;
            }

            const element = calendarDragState.element;
            const maxLeft = window.innerWidth - element.offsetWidth;
            const maxTop = window.innerHeight - element.offsetHeight;
            const left = event.clientX - calendarDragState.offsetX;
            const top = event.clientY - calendarDragState.offsetY;

            element.style.left = Math.max(0, Math.min(left, maxLeft)) + 'px';
            element.style.top = Math.max(0, Math.min(top, maxTop)) + 'px';
        });

        document.addEventListener('mouseup', function () {
            calendarDragState = null;
        });

        makeDraggable('app_productivity_calendar-container', '.window-header');
        makeDraggable('app_medication_log-container', '.ui-widget-header');

        $(function () {
            $("#app_calendar-frameExtra").resizable({
                alsoResize: "#app_calendar" // Resize textarea along with the dialog
            });

        });


        <?= $UI_APP['script']; ?>
    </script>
</body>

</html><?php $UI_APP['html'] = ob_get_contents();
ob_end_clean();

//check if file is included or accessed directly
if (cis_is_direct_http_file(__FILE__) && APP_DEBUG && ($_GET['app'] ?? null) === 'calendar')
    die($UI_APP['html']);