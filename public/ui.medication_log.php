<?php
global $errors;
//if (isset($_GET['path']) && isset($_GET['file']) && $path = realpath($_GET['path'] . $_GET['file']))

//$errors->{'TEXT_MANAGER'} = $path . "\n" . 'File Modified:    Rights:    Date of creation: ';


if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"]))
    if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
        if (is_file($path = realpath('../bootstrap.php'))) {
            require_once $path;
        }
    } else {
        die(var_dump("Path was not found. file=$path"));
    }

//dd(get_required_files(), false);
$jsonFile = APP_BASE['database'] . 'medication_log.json'; // Load existing data
$data = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [
    "date" => $date,
    "doses" => [
        ["time" => $timeSlot, "status" => $status, "note" => $note]
    ]
];

/*
foreach ($data['logs'] as $key => $log) {
    //echo "Date: Key:" . $key . '  ' . $log['date'] . "\n";
}
*/


// dd($data['logs'][0]['doses']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    function medication_log()
    {
        if (isset($_GET['app']) && $_GET['app'] == 'medication_log') {
            $jsonFile = APP_BASE['database'] . 'medication_log.json';

            // Load existing data
            $data = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : ['{
        "date": "' . date('Y-m-d') . '",
        "doses": [
            {
                "time": "10:00 AM",
                "status": "taken",
                "note": ""
            },
            {
                "time": "10:00 PM",
                "status": "taken",
                "note": ""
            }
        ]
    }'
            ];

            // Ensure data is an array
            if (!is_array($data)) {
                $data = [];
            }

            $date = $_POST["date"];
            $parsedTime = date_parse($_POST["time_slot"]);
            $formattedTime = sprintf('%02d:%02d', $parsedTime["hour"], $parsedTime["minute"]);
            $timeSlot = DateTime::createFromFormat('H:i', $formattedTime)->format('h:i A');

            $status = $_POST["status"] ?? null;
            if ($status === null)
                return;

            $note = $_POST["note"] ?? "";

            // Determine if it's AM or PM
            $isAM = (int) date('H', strtotime($timeSlot)) < 12;
            $isPM = !$isAM;

            // Find existing entry for the date
            $found = false;
            foreach ($data as &$entry) {
                if ($entry["date"] === $date) {
                    // Ensure "doses" is an array
                    if (!isset($entry["doses"]) || !is_array($entry["doses"])) {
                        $entry["doses"] = [];
                    }

                    // Track existing AM/PM doses
                    $existingAM = null;
                    $existingPM = null;

                    foreach ($entry["doses"] as &$dose) {
                        $doseHour = (int) date('H', strtotime($dose["time"]));

                        if ($doseHour < 12) {
                            $existingAM = &$dose;
                        } else {
                            $existingPM = &$dose;
                        }
                    }

                    // Append the new dose (allow multiple doses)
                    $entry["doses"][] = [
                        "time" => $timeSlot,
                        "status" => $status,
                        "note" => $note
                    ];

                    // Sort doses: AM first, PM second
                    usort($entry["doses"], function ($a, $b) {
                        return strtotime($a["time"]) - strtotime($b["time"]);
                    });

                    $found = true;
                    break;
                }
            }

            // If no entry exists for the date, create a new one with the first dose
            if (!$found) {
                $data[] = [
                    "date" => $date,
                    "doses" => [
                        [
                            "time" => $timeSlot,
                            "status" => $status,
                            "note" => $note
                        ]
                    ]
                ];
            }

            // Save to file
            file_put_contents($jsonFile, json_encode(array_reverse(array_slice(array_reverse($data), 0, 50)), JSON_PRETTY_PRINT));

            // Redirect back
            die(header('Location: http://' . APP_DOMAIN . '/?' . http_build_query(['path' => '', 'app' => 'medication_log'])));
        }
    }

    medication_log();

    /*
    if (isset($_POST['ace_path']) && realpath($path = APP_PATH . (APP_ROOT ?? ('clientele/' . ($_GET['client'] . '/' . ($_GET['domain'] ?? '') ?? $_GET['domain'] ?? ''))) . ($_GET['path'] . '/' . $_GET['file'] ?? $_POST['ace_path']))) {
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
*/
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
    if (is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/ace') && empty(glob($path)))
        exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
    elseif (!is_dir($path)) {
        if (!mkdir($path, 0755, true))
            $errors['GIT-CLONE-ACE'] = ' resources/js/ace does not exist.';
        exec((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . GIT_EXEC . ' clone https://github.com/ajaxorg/ace-builds.git resources/js/ace', $output, $returnCode) or $errors['GIT-CLONE-ACE'] = $output;
    }

ob_start(); ?>
#app_medication_log-container {
width : 480px;
height : 220px;
/* border: 1px solid black; */
position : absolute;
top : 280px;
left : 36%;
right : 50%;
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
<?php $app['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
<?php $jsonFile = APP_BASE['database'] . 'medication_log.json'; // Load existing data
$data = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
//dd($data);

$tmpkey = count($data) - 1;
$shouldHide = isset($data[$tmpkey])
    && isset($data[$tmpkey]['doses'])
    && $data[$tmpkey]['date'] != date('Y-m-d')
    && count($data[$tmpkey]['doses']) > 2;
?>
<div id="app_medication_log-container"
    class="<?= __FILE__ == get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'medication_log') && !isset($_GET['path']) ? 'selected' : '' ?>"
    style="position: fixed; display: <?= /* __FILE__ == get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'medication_log') ? 'block' : 'block'*/ $shouldHide ? 'none' : 'block' ?>; resize: both; overflow: hidden; z-index: 1;">
    <div class="ui-widget-header"
        style="position: relative; display: inline-block; width: 100%; cursor: move; border-bottom: 1px solid #000;background-color: #FFF;">
        <label class="medication_log-home" style="cursor: pointer;">
            <div class="" style="position: relative; display: inline-block; top: 0; left: 0;">
                <img src="resources/images/medication_log_icon.png" width="53" height="32" />
            </div>
        </label>
        <div style="display: inline;">
            <span style="background-color: #38B1FF; color: #FFF; margin-top: 10px;">Medication log
                <?= /* (version_compare(NPM_LATEST, NPM_VERSION, '>') != 0 ? 'v'.substr(NPM_LATEST, 0, similar_text(NPM_LATEST, NPM_VERSION)) . '<span class="update" style="color: green; cursor: pointer;">' . substr(NPM_LATEST, similar_text(NPM_LATEST, NPM_VERSION)) . '</span>' : 'v'.NPM_VERSION ); */ NULL; ?></span>
            <span style="background-color: #0078D7; color: white;"><code id="AceEditorVersionBox" class="text-sm"
                    style="background-color: white; color: #0078D7;"></code></span>
        </div>

        <div style="display: inline; float: right; text-align: center; color: blue;"><code
                style="background-color: white; color: #0078D7;"><a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_medication_log-container').style.display='none';">[X]</a></code>
        </div>
    </div>

    <div class="ui-widget-content"
        style="position: relative; display: block; margin: 0 auto; width: calc(100% - 2px); height: 45px; background-color: rgba(251,247,241); text-align: center; font-weight: bold; padding-top: 0px;">
        Did you remember to <br />take your medication?
        <?php /*
<div style="display: inline-block; text-align: left; width: 125px;">
<div class="npm-menu text-sm"
style="cursor: pointer; font-weight: bold; padding-left: 25px; border: 1px solid #000;">Main Menu
</div>
<div class="text-xs" style="display: inline-block; border: 1px solid #000;">
<a class="text-sm" id="app_medication_log-frameMenuPrev"
href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '#') ?>">
&lt; Menu</a> | <a class="text-sm" id="app_medication_log-frameMenuNext"
href="<?= (!empty(APP_QUERY) ? '?' . http_build_query(APP_QUERY) : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '#') ?>">Init
&gt;</a>
</div>
</div>
<!-- onclick="document.getElementsByClassName('ace_text-input')[0].value = globalEditor.getSession().getValue(); document.getElementsByClassName('ace_text-input')[0].name = 'editor';"   -->
<div
style="position:absolute; right: 100px; top: 10px; display: inline-block; width: auto; text-align: right;">
<input type="submit" name="ace_save" value="Save" class="btn" style="margin: -5px 5px 5px 0;">
</div>

<div class="absolute"
style="position: absolute; display: inline-block; top: 5px; right: 0; text-align: right; float: right;">
<div class="text-xs" style="position: relative; display: inline-block;">
+ 495 <a href="https://github.com/ajaxorg/ace/graphs/contributors">contributors</a>
<br /><!-- a href="https://github.com/ajaxorg"><img src="resources/images/node.js.png" title="https://github.com/nodejs" width="18" height="18" /></a -->
<a style="color: blue; text-decoration-line: underline; text-decoration-style: solid;"
href="https://ace.c9.io/" title="https://ace.c9.io/">https://ace.c9.io/</a>
</div>
</div>
*/ NULL; ?>
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
. '<img src="../../resources/images/directory.png" width="50" height="32" /><br />' . basename($path) . '</a>' . "\n";
elseif (is_file($path))
echo '<a href="?app=errors&path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&file=' . basename($path) . '">'
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



    <div style="position: relative; margin: 0 auto; width: calc(100% - 2px); height: 100%;">
        <!--
      <div id="app_medication_log-frameMenu" class="app_medication_log-frame-container absolute selected" style="background-color: rgb(225,196,151,.75); margin-top: 8px; height: 100%;">
-->

        <!--   A (<?= /* $path */ ''; ?>) future note: keep ace-editor nice and tight ... no spaces, as it interferes with the content window.
 https://scribbled.space/ace-editor-setup-usage/ -->

        <div id="ui_medication_log" class="medication_log"
            style="display: <?= isset($_GET['file']) && isset($_GET['path']) && is_file($_GET['path'] . $_GET['file']) ? 'block' : 'block' ?>; z-index: 1;">
            <div style="background-color: white; height: 100%; width: 100%; overflow: hidden;">
                <div style="text-align: center; width: 50%; margin-left:auto; margin-right:auto; margin-top: 20px;">
                    <form autocomplete="off" spellcheck="false" method="POST"
                        action="<?= APP_URL . basename(__FILE__) . /**/ '?' . http_build_query(['app' => 'medication_log']/**/) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /* $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>">
                        <input type="hidden" name="app" value="medication_log" />
                        <input id="date_slot" type="date" name="date" value="<?= date('Y-m-d') ?>" />
                        <input id="time_slot" type="time" name="time_slot"
                            value="<?= date('H:i') ?>" /><br />&nbsp;&nbsp;
                        <input type="radio" name="status" value="taken" />Taken&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="status" value="missed" />Missed<br />
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

<div id="app_medication_log-frameInit" class="app_medication_log-frame-container absolute"
    style="overflow: hidden; height: 270px;">
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

<div id="container1" style="position: relative; width: 100%; height: 100%; border: 1px #000 solid;">


</div>
<?php

$app['body'] = ob_get_contents();
ob_end_clean();

if (false) { ?>
    <script type="text/javascript"><?php }

ob_start();
//if (isset($_GET['client']) && $_GET['client'] != '') { 
//if (isset($_GET['domain']) && $_GET['domain'] != '') {
?>
    /*
        function scrollToElement() {
            var iframe = document.getElementById("myIframe");
            iframe.onload = function () {
                var target = iframe.contentDocument.getElementById("yourTargetElement");
                if (target) {
                    target.scrollIntoView();
                }
            };
        }
    
        scrollToElement();
    */
    function autoScroll() {
        var iframe = document.getElementById("iWindow");
        setInterval(function () {
            iframe.contentWindow.scrollTo(0, iframe.contentDocument.body.scrollHeight);
        }, 1000); // Adjust interval as needed
    }

    autoScroll();
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

    <script src="<?= 'resources/js/tailwindcss-3.3.5.js' ?? $url ?>"></script>

    <style type="text/tailwindcss">
        <?= $app['style']; ?>
    </style>
</head>

<body>
    <?= $app['body']; ?>

    <?php
    is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/jquery/') or mkdir($path, 0755, true);
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
        src="<?= defined('APP_IS_ONLINE') && APP_IS_ONLINE && check_http_status('https://code.jquery.com/jquery-3.7.1.min.js') ? 'https://code.jquery.com/jquery-3.7.1.min.js' : APP_BASE['resources'] . 'js/jquery/' . 'jquery-3.7.1.min.js' ?>"></script>
    <!-- You need to include jQueryUI for the extended easing options. -->
    <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->

    <script
        src="<?= defined('APP_IS_ONLINE') && APP_IS_ONLINE && check_http_status('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js') ? 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js' : APP_BASE['resources'] . 'js/jquery-ui/' . 'jquery-ui-1.12.1.js' ?>"></script>
    <!-- Uncaught ReferenceError: jQuery is not defined -->

    <script src="resources/js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script>
    <script src="resources/js/ace/src/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>

  <script src="resources/js/ace/src/mode-php.js" type="text/javascript" charset="utf-8"></script> -->
    <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->

    <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
    <!-- script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script -->
    <!-- <script src="../resources/js/jquery/jquery.min.js"></script> -->
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

        makeDraggable('app_medication_log-container');

        $(function () {
            $("#container1").resizable({
                alsoResize: "#app_medication_log" // Resize textarea along with the dialog
            });

        });


        <?= $app['script']; ?>
    </script>
</body>

</html>
<?php
$app['html'] = ob_get_contents();
ob_end_clean();

if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"])) {
    print $app['html'];
} elseif (in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'medication_log' && APP_DEBUG) {
    return $app['html'];
} else {
    return $app;
}
