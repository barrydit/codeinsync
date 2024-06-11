<?php

if (!in_array($path = dirname(__DIR__) . '/config/config.php', get_required_files()))
  require_once($path);

if (isset($_GET['CLIENT']) || isset($_GET['DOMAIN']) && !defined('APP_ROOT')) {

  if (!isset($_ENV['DEFAULT_CLIENT'])) $_ENV['DEFAULT_CLIENT'] = $_GET['CLIENT'];

  if (!isset($_ENV['DEFAULT_DOMAIN'])) $_ENV['DEFAULT_DOMAIN'] = $_GET['DOMAIN'];

  if (defined('APP_QUERY') && empty(APP_QUERY))
    die(header('Location: ' . APP_URL_BASE . '?' . http_build_query([
        'client' => $_ENV['DEFAULT_CLIENT'],
        'domain' => $_ENV['DEFAULT_DOMAIN']
    ]) . '#'));
  else
    $_GET = array_merge($_GET, APP_QUERY);

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <base href="<?=(!is_array(APP_URL) ? APP_URL : APP_URL_BASE) . (APP_URL['query'] != '' ? '?' . APP_URL['query'] : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : ''); ?>">

    <title>Multiple Ace Editor Instances</title>
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
unset($path);
?>
    <script src="<?= APP_BASE['resources'] . 'js/tailwindcss-3.3.5.js' ?? $url ?>"></script>
    
    <style>
        .editor {
            width: 500px;
            height: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div id="ui_ace_editor" class="editor">This is the first editor.</div>
    <div id="app_project_editor" class="editor">This is the second editor.</div>


    <script src="resources/js/jquery/jquery-3.7.1.min.js"></script>
    <!-- You need to include jQueryUI for the extended easing options. -->
        <!-- script src="//code.jquery.com/jquery-1.12.4.js"></script -->
    
    <script src="resources/js/jquery-ui/jquery-ui-1.12.1.js"></script> <!-- Uncaught ReferenceError: jQuery is not defined -->

 
    <script src="<?= APP_BASE['resources']; ?>js/requirejs/require.js" type="text/javascript" charset="utf-8"></script>

<script>    require.config({
        baseUrl: window.location.protocol + "//" + window.location.host + window.location.pathname.split("/").slice(0, -1).join("/"),
        paths: {
            jquery: 'resources/js/jquery/jquery-3.7.1.min',
            'jquery-ui': 'resources/js/jquery-ui/jquery-ui-1.12.1',
            //domReady: 'resources/js/domReady',
            //bootstrap: 'resources/js/bootstrap/dist/js/bootstrap',
            ace: 'resources/js/ace/src/ace',
            'ace/ext-language_tools': 'resources/js/ace/src/ext-language_tools',
            'ace/mode/javascript': 'resources/js/ace/src/mode-javascript',
            'ace/mode/html': 'resources/js/ace/src/mode-html',
            'ace/mode/php': 'resources/js/ace/src/mode-php',
            'ace/theme/monokai': 'resources/js/ace/src/theme-monokai',
            'ace/theme/github': 'resources/js/ace/src/theme-github'
        },
        shim: {
            'ace': {
                deps: ['ace/ext-language_tools'],
                exports: 'ace'
            },
            //'ace/ext-language_tools': ['ace'],
            'ace/mode/javascript': ['ace'],
            'ace/mode/html': ['ace'],
            'ace/mode/php': ['ace'],
            'ace/theme/monokai': ['ace'],
            'ace/theme/github': ['ace']
        }
    });

    //require(['jquery', 'domReady', 'ace', 'ace/ext-language_tools', 'ace/mode/javascript', 'ace/mode/html', 'ace/theme/monokai', 'ace/theme/github'], function($, domReady, ace) {
    //    domReady(function() {}
    //});

    require(['ace', 'ace/ext-language_tools', 'ace/mode/php', 'ace/mode/javascript', 'ace/mode/html', 'ace/theme/monokai', 'ace/theme/github'], function () {
        if (!ace) {
                console.error("Ace editor not loaded");
                return;
            }
        var editor1 = ace.edit("ui_ace_editor");
        //var JavaScriptMode = ace.require("ace/mode/javascript").Mode;
        editor1.setTheme("ace/theme/monokai");
        editor1.session.setMode("ace/mode/php");
        editor1.setAutoScrollEditorIntoView(true);
        editor1.setShowPrintMargin(false);
        editor1.setOptions({
            //  resize: "both"
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            enableSnippets: true
        });

        var editor2 = ace.edit("app_project_editor");
        editor2.setTheme("ace/theme/dracula");
        // (file_ext .js = javascript, .php = php)
        editor2.session.setMode("ace/mode/php");
        editor2.setAutoScrollEditorIntoView(true);
        editor2.setShowPrintMargin(false);
        editor2.setOptions({
            //  resize: "both"
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            enableSnippets: true
        });
        
        globalEditor = editor2;
    }, function (err) {
        console.error("Error loading Ace modules: ", err.requireModules);
        console.error(err);
    });
</script>
</body>

</html>