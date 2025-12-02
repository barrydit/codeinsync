<?php

//if (isset($_GET['path']) && isset($_GET['file']) && $path = realpath($_GET['path'] . $_GET['file']))

//$errors->{'TEXT_MANAGER'} = $path . "\n" . 'File Modified:    Rights:    Date of creation: ';

defined('APP_PATH') || define('APP_PATH', dirname(__DIR__, 3) . '/');
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . 'config/');

// const APP_ROOT = '123';

// Ensure bootstrap has run (defines env/paths/url/app and helpers)
if (!defined('APP_BOOTSTRAPPED')) {
    require_once APP_PATH . 'bootstrap/bootstrap.php';
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer !== '') {
        $query = parse_url($referer, PHP_URL_QUERY);
        if ($query) {
            // Parse query string and merge into $_GET
            parse_str($query, $refParams);
            if (is_array($refParams)) {
                $_GET = $refParams;
            }
        }
    }
}

// -----------------------------------------------------------------------------

// Ensure COMPOSER_BIN or COMPOSER_PHAR is defined (best-effort, non-fatal)

$app_id = 'ui/ace_editor';           // full path-style id

// Always normalize slashes first
$app_norm = str_replace('\\', '/', $app_id);

// Last segment only (for titles, labels, etc.)
$slug = basename($app_norm);                    // "ace_editor"

// Sanitized full path for DOM ids (underscores only from non [A-Za-z0-9_ -])
$key = preg_replace('/[^\w-]+/', '_', $app_norm);  // "ui_ace_editor"

// If you prefer strictly underscores (no hyphens), do: '/[^\w]+/'

// Core DOM ids/selectors
$container_id = "app_{$key}-container";         // "app_ui_ace_editor-container"
$selector = "#{$container_id}";                 // "#app_ui_ace_editor-container"

// Useful companion ids
$style_id = "style-{$key}";                    // "style-ui_ace_editor"
$script_id = "script-{$key}";                   // "script-ui_ace_editor"

// Optional: data attributes you can stamp on the container for easy introspection
$data_attrs = sprintf(
    'data-app-path="%s" data-app-key="%s" data-app-slug="%s"',
    htmlspecialchars($app_norm, ENT_QUOTES),
    htmlspecialchars($key, ENT_QUOTES),
    htmlspecialchars($slug, ENT_QUOTES),
);
// -----------------------------------------------------------------------------

if (false) { ?>
    <style>
    <?php }
ob_start(); ?>

    <?= $selector ?>
        {
        width: 720px;
        height: 460px;

        /* display : none; */
        /* Initially hidden */
        /* border: 1px solid black; */
        position: absolute;
        top: 60px;
        left: 30%;
        right: 0;
        z-index: 1;
        /* resize: both; Make the div resizable */
        / * overflow: hidden;
        Hide overflow to ensure proper resizing */
    }

    <?php $UI_APP['style'] = ob_get_contents();
    ob_end_clean();
    if (false) { ?>
    </style><?php }

    ob_start(); ?>
<!-- Header -->


<div class="ui-widget-header"
    style="display:flex;align-items:center;gap:.5rem;justify-content:space-between;border-bottom:1px solid #000;background:#fff;cursor:move;"
    data-drag-handle="true">
    <div style="display:flex;align-items:center;gap:.5rem;">
        <img src="assets/images/ace_editor_icon.png" width="24" height="24" alt="Ace" />
        <span style="background:#38B1FF;color:#fff;padding:.1rem .4rem;border-radius:.25rem;">Ace Editor</span>
        <code id="AceEditorVersionBox" class="text-sm" style="background:#fff;color:#0078D7;"></code>
    </div>
    <a style="cursor:pointer;font-size:13px;color:#0078D7;padding:.25rem .5rem;"
        onclick="closeApp('ui/ace_editor',{fullReset:true});">[X]</a>
</div>

<!-- Save Form + Editor -->
<form class="ace-form" action="/?api=file_save" method="POST"
    style="display:flex;flex-direction:column;height:calc(100% - 34px);">
    <!-- These can be filled by your dispatcher before render, or updated at runtime -->
    <input type="hidden" name="path" value="">
    <input type="hidden" name="file" value="">
    <input type="hidden" name="encoding" value="utf-8">

    <!-- Toolbar -->

    <div clas s="toolbar ui-widget-content"
        style="display:flex;align-items:center;justify-content:flex-end;gap:.5rem;padding:.3rem .4rem;background:rgba(251,247,241);border-bottom:1px solid #ddd;">
        <span
            style="position: absolute; left: 0;"><?= ltrim($_GET['path'] . DIRECTORY_SEPARATOR . ltrim($_GET['file'], '/'), '/') ?></span>
        <button type="submit" name="ace_save" class="btn">Save</button>
    </div>

    <!-- Editor host -->
    <div id="ui_ace_editor" class="ace-editor" style="flex:1;min-height:220px;background:#111;"></div>

    <!-- Hidden field populated on submit -->
    <textarea id="ace_contents" name="contents" hidden></textarea>
</form>

<!-- Optional initial content (used if no file contents are injected) -->
<?php //$fh = fopen(realpath(APP_PATH . APP_ROOT . APP_ROOT_DIR . ltrim($_GET['file'], '/')), 'r'); ?>
<script type="text/plain" id="initialContent"><?php
//while (!feof($fh)) {
// send the current file part to the browser
//    print htmlspecialchars(fread($fh, 8192), ENT_QUOTES, 'UTF-8');
//}
//fclose($fh); 
?></script>
<?php $UI_APP['body'] = ob_get_contents();
ob_end_clean();

//nl2br(htmlspecialchars(file_get_contents(realpath(APP_PATH . ltrim($_GET['path'], '/')), ENT_QUOTES, 'UTF-8'))) 
if (false) { ?>
    <script type="text/javascript"><?php }
ob_start(); ?>
    /*  Minimal module: initializes Ace, wires Save, and includes a tiny script loader */
    // --- tiny loader helpers -------------------------------------------------
    const loadScript = (src, attrs = {}) => new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = src; s.async = true; Object.assign(s, attrs);
        s.onload = () => resolve();
        s.onerror = () => reject(new Error('Failed to load ' + src));
        document.head.appendChild(s);
    });
    const ensureAce = async () => {
        if (window.ace) return;
        // Prefer your local copy; fall back to CDN if desired
        try { await loadScript('assets/vendor/ace/src/ace.js'); }
        catch { await loadScript('https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.9/ace.js'); }
    };

    // --- boot ---------------------------------------------------------------
    (async () => {
        await ensureAce();

        const container = document.getElementById('app_ui_ace_editor-container');
        if (!container) return;

        const editorEl = container.querySelector('#ui_ace_editor');
        const form = container.querySelector('form.ace-form');
        const hidden = container.querySelector('#ace_contents');
        const versionBox = container.querySelector('#AceEditorVersionBox');

        // Use injected content if present, else the <script type="text/plain"> fallback
        //const injected = editorEl.getAttribute('data-initial') || '';
        //const fallback = (container.querySelector('#initialContent')?.textContent || '');
        //const initial = injected || fallback;

        // Create Ace instance
        const editor = ace.edit(editorEl);
        // sane defaults; tweak as you like
        editor.session.setMode('ace/mode/php');
        editor.setTheme('ace/theme/monokai');
        editor.setOptions({
            useWorker: false,
            wrap: true,
            showPrintMargin: false,
            fontSize: 14,
        });
        //editor.session.setValue(initial);

        // --- CTRL+S / CMD+S Save Handler ---
        editor.commands.addCommand({
            name: "saveFile",
            bindKey: { win: "Ctrl-S", mac: "Command-S" },
            exec: function (editor) {
                const content = editor.getValue();

                // OPTIONAL: custom event to your window/app
                document.dispatchEvent(new CustomEvent("app:save", {
                    detail: { content }
                }));

                // OPTIONAL: your API call
                fetch("/?api=file_save&ace_save=1", {
                    method: "POST",
                    headers: {
                        "Content-Type": "text/plain; charset=UTF-8"
                    },
                    body: content   // <-- RAW TEXT, no JSON!
                })
                    .then(r => r.text())
                    .then(response => {
                        console.log("[SAVE]", response);
                        window.location.reload();
                        // optionally show a toast
                    });

                // Prevent browser “Save Page” dialog
                return false;
            }
        });

        async function loadFile() {
            const url = `/?api=file_load&` + `<?= http_build_query($_GET); ?>`; // encodeURIComponent(path);

            const resp = await fetch(url, { cache: 'no-store' });
            if (!resp.ok) {
                console.error('Failed to load file', await resp.text());
                return;
            }

            let text = await resp.text();

            // Normalize line endings so Ace sees consistent \n
            text = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n');

            editor.setValue(text, -1); // -1 keeps cursor at top
        }

        await loadFile();

        // Expose for other modules if needed
        container.__editor = editor;

        // Wire save: copy content into hidden field before submit
        form.addEventListener('submit', () => {
            hidden.value = editor.getValue();
        });

        // Show Ace version if available
        try { versionBox.textContent = window.ace?.version ? 'v' + window.ace.version : ''; } catch { }

        // --- OPTIONAL: load extra libraries dynamically when needed -----------
        // Example: await loadScript('assets/vendor/ace/ext-language_tools.js');
        // Then enable: editor.setOptions({ enableBasicAutocompletion: true, enableSnippets: true });
    })();

    window.addEventListener("keydown", function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "s") {
            e.preventDefault();
        }
    });
    <?php $UI_APP['script'] = ob_get_contents();
    ob_end_clean();

    if (false) { ?></script><?php }