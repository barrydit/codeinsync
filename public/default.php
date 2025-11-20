<?php

require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

require_once dirname(__DIR__) . '/app/productivity/notes.php';

die();

// dd($_ENV['PHP']['LOG_PATH'] . $_ENV['PHP']['ERROR_LOG']);
// dd(ini_get('error_log'));
/*
$url = 'https://cdn.tailwindcss.com';
$path = htmlspecialchars($asset($baseHref . 'assets/js/tailwindcss-3.3.5.js'), ENT_QUOTES, 'UTF-8');

echo 'Param 1: ' . substr($url, strpos($url, parse_url($url)['host']) + strlen(parse_url($url)['host']));

echo 'Param 2: ' . substr($path, strpos($path, dirname($baseHref . 'assets/js')));
*/
// dd(); 

/*
echo '<pre>hello world' . "<br />" . PHP_EOL;

foreach (['1', '2', '3'] as $key => $item) {
    echo $key . ': ' . $item . "<br />" . PHP_EOL;
}
echo '</pre>'; 
*/
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($appLocale, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>
        <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
    </title>

    <!-- Base URL for all relative links -->
    <base href="<?= htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') ?>" />

    <!-- SEO/meta fallbacks -->
    <meta name="description" content="<?= htmlspecialchars($appDescription, ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="author" content="<?= htmlspecialchars($appAuthor, ENT_QUOTES, 'UTF-8') ?>" />
    <meta name="robots" content="noindex,nofollow" />
    <meta name="theme-color" content="<?= htmlspecialchars($appThemeColor, ENT_QUOTES, 'UTF-8') ?>" />

    <!-- Open Graph (optional but nice defaults) -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($appDescription, ENT_QUOTES, 'UTF-8') ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') ?>" />

    <!-- Favicons / Assets -->
    <link rel="icon" type="image/png"
        href="<?= htmlspecialchars($asset('assets/images/favicon.png'), ENT_QUOTES, 'UTF-8') ?>" />
    <link rel="shortcut icon" type="image/x-icon"
        href="<?= htmlspecialchars($asset('assets/images/favicon.ico'), ENT_QUOTES, 'UTF-8') ?>" />

    <link rel="stylesheet" href="<?= htmlspecialchars($asset('assets/css/styles.css'), ENT_QUOTES, 'UTF-8') ?>" />

    <style>
        body {
            background: rgba(255, 255, 255, 0.25) url("assets/images/developer.gif");
            background-repeat: no-repeat;
            background-position: center 25px;
            //margin-top: 200px;
            //height: 100vh;
            //background-size: cover;
        }


        input[type='checkbox'] {
            //-webkit-appearance:none;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 5px;
            border: 2px solid #555;
            margin-top: 6px;
            margin-bottom: 22px;
        }

        form#fimprovdev {}

        input[type='checkbox']:checked {
            background: #abd;
        }
    </style>
</head>

<body>
    <div style="position: absolute; left: 25%; top: 4%; margin: 0px auto;">
        <div style="">
            <form id="fimprovdev" action method="POST"
                style="width: 675px; border: 1px solid #000; height: 575px; padding: 210px 0px 0px 160px;">
                <input style="" type="checkbox" name="" value="" checked /><br />
                <input type="checkbox" name="" value="" checked /><br />
                <input type="checkbox" name="" value="" checked /><br />
                <input type="checkbox" name="" value="" checked /><br />
                <input type="checkbox" name="" value="" checked /><br />
                <input type="checkbox" name="" value="" checked /><br />
                <input type="checkbox" name="" value="" checked /><br />
                <input type="checkbox" name="" value="" checked /><br />
                <input type="checkbox" name="" value="" checked /><br />
                <input type="checkbox" name="" value="" checked /><br />
                <input type="checkbox" name="" value="" checked />
            </form>
        </div>
        <!-- <p style="text-align: center;"><img src="developer_1.jpg" width="650" height="800" /></p> -->
    </div>
</body>

</html>