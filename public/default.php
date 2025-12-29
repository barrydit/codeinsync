<?php

require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

// die(require_once dirname(__DIR__) . '/app/productivity/notes.php');

// dd($_ENV['PHP']['LOG_PATH'] . $_ENV['PHP']['ERROR_LOG']);
// dd(ini_get('error_log'));
/*
$url = 'https://cdn.tailwindcss.com';
$path = htmlspecialchars($asset(UrlContext::getBaseHref() . 'assets/vendor/tailwindcss-3.3.5.js'), ENT_QUOTES, 'UTF-8');

echo 'Param 1: ' . substr($url, strpos($url, parse_url($url)['host']) + strlen(parse_url($url)['host']));

echo 'Param 2: ' . substr($path, strpos($path, dirname(UrlContext::getBaseHref() . 'assets/vendor')));
*/
// dd(); 

/*
echo '<pre>hello world' . "<br />" . PHP_EOL;

foreach (['1', '2', '3'] as $key => $item) {
    echo $key . ': ' . $item . "<br />" . PHP_EOL;
}
echo '</pre>'; 

if (APP_ROOT === null) {
    die('APP_ROOT is not defined');
} elseif (APP_ROOT === '') {
    die('APP_ROOT is empty');
} else
    dd(APP_ROOT);
*/

$clientPath = realpath(APP_PATH . APP_BASE['clients'])
    . '/'
    . $_GET['client'];

$dirs = glob("$clientPath/*", GLOB_ONLYDIR); // array_filter(glob('/*'), 'is_dir');

// Extract folder names only
$domains = [];

foreach ($dirs as $key => $dir) {
    $name = basename($dir); // ← folder name only

    if (isDomainName($name)) {
        $domains[] = $name;
    }
}

$cards = [];

$client = rawurlencode($_GET['client']);

foreach ($domains as $domain) {
    $safeName = htmlspecialchars($domain, ENT_QUOTES, 'UTF-8');
    $safeDomain = rawurlencode($domain);

    $cards[] = [
        'name' => $safeName,
        'url' => "http://localhost/clients" . ($client === '' ? '' : "/{$client}") . "/{$safeDomain}/public/"
    ];
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Live Website Preview Wall</title>
    <style>
        body {
            margin: 0;
            background: #111;
            height: 100vh;
        }

        span {
            color: white;
        }

        .wrapper {
            display: flex;
            justify-content: center;
            /* horizontal center */
            align-items: center;
            /* vertical center */
            height: 100%;
        }

        .grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, auto));
            gap: 12px;
            justify-content: center;
        }

        .site-card {
            position: relative;
            background: #222;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #333;
        }

        .site-header {
            padding: 6px 10px;
            font-size: 13px;
            background: #181818;
            border-bottom: 1px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .site-header a {
            color: #4ea3ff;
            text-decoration: none;
            font-size: 11px;
        }

        .iframe-wrapper {
            position: relative;
            width: 100%;
            /* aspect-ratio keeps a nice fixed preview shape */
            aspect-ratio: 16 / 9;
            overflow: hidden;
            background: #000;
        }

        .iframe-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            /* Make the iframe bigger, then scale it down */
            width: 200%;
            height: 200%;
            transform: scale(0.5);
            transform-origin: 0 0;
            border: none;
            pointer-events: none;
            /* so clicks don’t go into the tiny iframe */
        }

        /* Overlay clickable area */
        .click-overlay {
            position: absolute;
            inset: 0;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="grid" id="previewGrid"></div>
    </div>

    <script>
        const sites = <?= json_encode($cards, JSON_UNESCAPED_SLASHES); ?>;

        if (Array.isArray(sites) && sites.length === 0) {
            window.parent.toggleMode();
            console.warn('No sites found for preview. Returning to directory view.');
        }
        //[{ name: 'David Raymant - client page', url: 'http://localhost/?client=000-Raymant%2CDavid&domain=davidraymant.ca&path=%2F' }],
        // add your own URLs here

        const grid = document.getElementById('previewGrid');

        sites.forEach(site => {
            const card = document.createElement('div');
            card.className = 'site-card';

            card.innerHTML = `
        <div class="site-header">
          <span>${site.name}</span>
          <a href="${site.url}">
            Visit &rarr;
          </a>
        </div>
        <div class="iframe-wrapper">
          <iframe src="${site.url}" loading="lazy"></iframe>
          <div class="click-overlay" data-url="${site.url}"></div>
        </div>
      `;

            grid.appendChild(card);
        });

        // When you click anywhere on the preview, open the full site
        grid.addEventListener('click', (e) => {
            const overlay = e.target.closest('.click-overlay');
            if (!overlay) return;
            const url = overlay.dataset.url;
            window.open(url, '_blank', 'noopener,noreferrer');
        });
    </script>

    <?php if (true /* APP_ENV['production'] === false */) { ?>
        <script src="<?php if (date(/*Y-*/ 'm-d') == /*1928-*/ '08-07' ?? /*2023-*/ '03-30' || date(/*Y-*/ 'm-d') == /*1976-*/ '03-20' ?? /*2017-*/ '07-20') {
            echo 'assets/reels/leave-a-light-on.js';
        } // elseif (date(/*Y-*/ 'm-d') == /*1967-*/ '12-28') { echo 'assets/reels/happy_birthday.js'; }
        else {
            $reels = array_values(array_filter(glob(__DIR__ . '/assets/reels/*.js') ?: [], 'is_file'));
            echo $reels ? 'assets/reels/' . basename($reels[random_int(0, count($reels) - 1)]) : ''; /* APP_BASE['public'] */
        } ?>" type="text/javascript" charset="utf-8"></script>

        <script type="text/javascript">
            (function () {
                // Safety: if snd isn't defined yet, bail
                if (typeof snd === "undefined" || !snd) return;

                let isPausedByUser = false;
                let wired = false;

                function wireOnce() {
                    if (wired) return;
                    wired = true;

                    // Repeat unless user paused
                    snd.addEventListener("ended", () => {
                        if (!isPausedByUser) {
                            try {
                                snd.currentTime = 0;
                                snd.play().catch(() => { });
                            } catch (_) { }
                        }
                    });
                }

                function toggleSound() {
                    // If you ever replace snd elsewhere, re-wire safely
                    if (typeof snd === "undefined" || !snd) return;
                    wireOnce();

                    if (snd.paused) {
                        isPausedByUser = false;

                        // (optional) always restart from beginning on resume:
                        // snd.currentTime = 0;

                        snd.play().catch(err => {
                            if (err?.name === "NotAllowedError") {
                                console.warn("Audio blocked until user interaction.");
                            } else {
                                console.error("Audio play failed:", err);
                            }
                        });
                    } else {
                        isPausedByUser = true;
                        snd.pause();
                    }
                }

                function playSound() {
                    if (typeof snd === "undefined" || !snd) return;
                    wireOnce();
                    isPausedByUser = false;
                    snd.play().catch(() => { });
                }

                function pauseSound() {
                    if (typeof snd === "undefined" || !snd) return;
                    isPausedByUser = true;
                    snd.pause();
                }

                // expose globally (drop-in friendly)
                window.toggleSound = toggleSound;
                window.playSound = playSound;
                window.pauseSound = pauseSound;
            })();


            document.addEventListener('DOMContentLoaded', () => {
                // Attempt to play the media element when the DOM is fully loaded
                try {
                    // Attempt to play the media element
                    if (typeof snd !== 'undefined') {
                        playSound(); // snd.play();
                    }
                } catch (error) {
                    // Check if the error is a DOMException
                    if (error instanceof DOMException && error.name === 'NotAllowedError') {
                        // Handle the error (e.g., show a message to the user)
                        console.error('The play method is not allowed by the user agent or the platform.');
                    } else {
                        // If it's a different type of error, rethrow it
                        throw error;
                    }
                }
            });

            document.addEventListener('click', () => {
                if (snd.paused) {
                    //isPausedByUser = false;
                    toggleSound().catch(err => {
                        if (err.name === 'NotAllowedError') {
                            console.warn('Playback blocked.');
                        }
                    });
                } else {
                    //isPausedByUser = true;
                    toggleSound();
                }
            });

        </script> <?php } ?>

</body>

</html>