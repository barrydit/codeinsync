<?php
// app/core/console.php

defined('APP_PATH') || define('APP_PATH', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR);
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . 'config' . DIRECTORY_SEPARATOR);

// const APP_ROOT = '123';

// Ensure bootstrap has run (defines env/paths/url/app and helpers)
if (!defined('APP_BOOTSTRAPPED')) {
  require_once APP_PATH . 'bootstrap/bootstrap.php';
}

global $shell_prompt, $auto_clear, $errors, $asset;

// -----------------------------------------------------------------------------

// Ensure COMPOSER_BIN or COMPOSER_PHAR is defined (best-effort, non-fatal)

$app_id = 'core/console';           // full path-style id

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

// -----------------------------------------------------------------------------
switch (__FILE__) {
  case get_required_files()[0]:
    if ($path = (basename(getcwd()) == 'public') ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '')
      require_once $path;
    else
      die(var_dump("$path path was not found. file=config.php"));
    break;
  default:
    file_exists(APP_PATH . 'config/constants.paths.php') && require_once APP_PATH . 'config/constants.paths.php';
    require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'constants.runtime.php';
}

/*
    realpath ? Returns canonicalized absolute pathname
    is_writable ? Tells whether the filename is writable
    unlink ? Deletes a file
*/

// die(var_dump(get_required_files()));


//require_once realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR  . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'class.sockets.php');

//if (__FILE__ == $_SERVER["SCRIPT_FILENAME"]) {
//  echo "called directly";
//} else {
//  echo "included/required";
//}

//dd(__FILE__, false);
//!function_exists('dd') ? die('dd is not defined') : dd(COMPOSER_EXEC);


/*
if ($path = (basename(getcwd()) == 'public')
  ? (is_file('../git.php') ? '../git.php' : (is_file('../config/git.php') ? '../config/git.php' : null))
  : (is_file('git.php') ? 'git.php' : (is_file('config/git.php') ? 'config/git.php' : null))) require_once $path; 
else die(var_dump($path . ' path was not found. file=git.php'));

if ($path = (basename(getcwd()) == 'public')
  ? (is_file('../composer.php') ? '../composer.php' : (is_file('../public/api/composer.php') ? '../public/api/composer.php' : null))
  : (is_file('composer.php') ? 'composer.php' : (is_file('public/api/composer.php') ? 'public/api/composer.php' : null))) require_once $path; 
else die(var_dump($path . ' path was not found. file=composer.php'));

if ($path = (basename(getcwd()) == 'public')
  ? (is_file('../npm.php') ? '../npm.php' : (is_file('../config/npm.php') ? '../config/npm.php' : null))
  : (is_file('npm.php') ? 'npm.php' : (is_file('config/npm.php') ? 'config/npm.php' : null))) require_once $path; 
else die(var_dump($path . ' path was not found. file=npm.php'));
*/
?>

<?php ob_start(); ?>

/* Styles for the absolute div */
<?= $selector ?> {

z-index: 999;
position: fixed;
top: 35%;
left: 50%;
bottom: 36px;
transform: translate(-50%, -50%);

/*position : fixed;
bottom : 0px;
left : 50%;
transform : translate(-50%, -50%);*/
width : auto;
/* height : 45px; */
background-color : #FFA6A6; /* rgba(255, 0, 0, 0.35) */
border: 1px dashed #000; display: block;
color : white;
text-align : center;
z-index : 999;
}

#responseConsole {
position : relative;
display : block;
margin : 0 auto;
background-color : #D0D0D0;
color : black;
cursor : pointer;
height : 185px;
}

input {
color : black;
}

.process-list {
position : absolute;
background-color : #FFA6A6;
left : -150px;
width : 150px;
height : 117px;
border : 2px solid #000;
overflow : hidden;
display : block;
}

.process {
border : #000 solid 1px;
color : #fff;
padding : 10px;
margin : 5px 0;
display : block;
position : relative;
white-space : nowrap; /* Prevent wrapping of the text */
width : fit-content; /* Set the width to fit the content */
clear : both; /* Ensure each process starts on a new line */
overflow : hidden;
}

@keyframes scroll {
0% {
transform : translateX(15%);
}
100% {
transform : translateX(-75%);
}
}

.scrolling {
animation : scroll 10s linear infinite;
}

.vert-slider-container {
position : relative;
float : right;
width : 10px; /* Adjust the width as needed */
height : 100px; /* Adjust the height as needed */
/* margin: 10px auto; Adjust margin to center vertically */
background-color: #f0f0f0; /* Background color for the slider */
}
.vert-slider {
position : absolute;
top : 45px;
left : -70px;
width : 100px; /* Adjust the width of the slider track */
height : 10px;
background : #4CAF50; /* Slider track color */
/*transform: translateX(-50%);*/
transform : rotate(90deg); /* Rotate the slider vertically */
cursor : pointer;
}

.vert-slider::-webkit-slider-thumb {
appearance : none;
width : 20px; /* Adjust the thumb width */
height : 20px; /* Adjust the thumb height */
background : #fff; /* Thumb color */
border : #4CAF50 solid 2px; /* Thumb border color */
border-radius : 50%; /* Rounded thumb */
cursor : pointer;
margin-top : -10px; /* Adjust thumb position */
margin-left : -10px; /* Adjust thumb position */
}

.vert-slider::-moz-range-thumb {
width : 20px; /* Adjust the thumb width */
height : 20px; /* Adjust the thumb height */
background : #fff; /* Thumb color */
border : #4CAF50 solid 2px;
border-radius : 50%; /* Rounded thumb */
cursor : pointer;
}

/*
@keyframes scroll {
0% {
transform : translateX(100%);
}
100% {
transform : translateX(-100%);
}
}*/
<?php $UI_APP['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
<!-- <div class="container" style="border: 1px solid #000;"> -->

<!-- Process list / marquee -->
<div id="process-list" class="process-list" onmouseout="stopScroll()" style="display: none;">
  <!--
    <div style="position: relative; width: 80px; height: 20px; background-color: #000; margin: 0 auto;">
      <div
        class="scroll-text"
        style="
          animation: none;
          border: 1px solid red;
          margin: auto;
          position: absolute;
          top: 50%;
          left: 30%;
          right: 50%;
          -ms-transform: translateY(-50%);
          transform: translateY(-50%);
        "
      >
        Testing
      </div>
    </div>

    <div
      class="scroll-text"
      style="animation: none; position: relative; width: 80px; height: 20px; background-color: #000; margin: 0 auto;"
    >
      test
    </div>

    <div
      class="scroll-text"
      style="animation: none; position: relative; width: 80px; height: 20px; background-color: #000; margin: 0 auto;"
    >
      test
    </div>
    -->
</div>

<!-- Help / reference overlay 1 -->
<div class="text-sm" style="
      position: absolute;
      display: none;
      top: -320px;
      background-color: #FFF;
      border: 1px dashed #000;
      height: 160px;
      width: 100%;
      padding: 20px 10px;
      color: #000;
      text-align: center;
      z-index: -1;
    ">
  <h1>&lt;html&gt; &lt;head&gt;</h1>
  <h2>&lt;meta&gt;, &lt;link&gt;, &lt;base&gt;,... &lt;/head&gt;</h2>

  <h1>body</h1>
  <h2>&lt;p&gt;, &lt;pre&gt;, &lt;div&gt;,...</h2>

  To put language manual / langauge specifics <br>
  langauge function paramters and related functions <br>
  math functions / order-of-operation <br>
</div>

<!-- Help / reference overlay 2 -->
<div class="text-sm" style="
      position: absolute;
      display: none;
      top: -160px;
      background-color: rgba(255, 255, 255, 0.6);
      border: 1px dashed #000;
      height: 160px;
      width: 100%;
      padding: 20px 10px;
      color: #000;
      text-align: left;
      z-index: -1;
    ">
  <div style="
        display: inline;
        float: left;
        background-color: #FFF;
        width: 50%;
        border: 1px dashed #000;
      ">
    <input type="checkbox" checked> Interactive<br>
    <input type="checkbox" checked> font-family: 'Courier New', Courier, monospace;
  </div>

  <div style="
        display: inline;
        float: right;
        text-align: right;
        width: 50%;
        border: 1px dashed #000;
      ">
    <div style="
          display: inline;
          float: left;
          width: 85%;
          text-align: right;
        ">
      Text Zoom:
    </div>

    <div class="vert-slider-container" style="display: inline; float: right;">
      <input type="range" min="-2" max="2" value="0" step="1" class="vert-slider" id="mySlider">
    </div>
  </div>
</div>

<!-- Settings button strip -->
<div style="
      position: absolute;
      top: -24px;
      background-color: #FFA6A6;
      border: 1px dashed #000;
      border-right: none;
      z-index: -1;
    ">
  <button id="console-settings-btn" class="text-xs" style="padding: 0 4px 4px; font-weight: bold;">
    [Settings...]
  </button>
</div>

<!-- Mode / language buttons -->
<div style="
      position: absolute;
      top: -24px;
      left: 95px;
      background-color: #FFA6A6;
      border: 1px dashed #000;
      border-left: none;
      z-index: -1;
    ">
  <button class="text-xs" style="border: 1px dashed #000; padding: 0 4px 2px; font-weight: bold; color: black;">
    Console
  </button>
  |
  <button class="text-xs" style="border: 1px dashed #000; padding: 0 4px 2px; font-weight: bold;">
    SQL Query
  </button>
  |
  <button class="text-xs" style="border: 1px dashed #000; padding: 0 4px 2px; font-weight: bold;"
    onclick="document.getElementById('app_ace_editor-container').style.display='block';">
    PHP
  </button>
  |
  <button class="text-xs" style="border: 1px dashed #000; padding: 0 4px 2px; font-weight: bold;">
    Perl
  </button>
  |
  <button class="text-xs" style="border: 1px dashed #000; padding: 0 4px 2px; font-weight: bold;">
    Python
  </button>
  |
  <button class="text-xs" style="border: 1px dashed #000; padding: 0 4px 2px; font-weight: bold;">
    JavaScript
  </button>
  |
  <button class="text-xs" style="border: 1px dashed #000; padding: 0 4px 2px; font-weight: bold;">
    CSS
  </button>
  |
  <button class="text-xs" style="border: 1px dashed #000; padding: 0 4px 2px; font-weight: bold;">
    HTML
  </button>
</div>

<!-- Main console area -->
<div style="text-align: left; position: relative;">
  <!-- Command input + Run -->
  <div style="display: inline-block; margin: 5px 0 0 10px; float: left;">
    <button id="requestSubmit" type="submit" style="border: 1px dashed #FFF; padding: 2px 4px;">
      Run
    </button>
    &nbsp;
    <input list="commandHistory" id="requestInput" class="console-input text-sm"
      style="font-family: 'Courier New', Courier, monospace;" type="text" size="31" name="requestInput"
      autocomplete="off" spellcheck="off" placeholder="php [-rn] &quot;echo 'hello world';&quot;" value="">
    <datalist id="commandHistory">
      <option value="Edge"></option>
    </datalist>
  </div>

  <!-- Clear / sudo / bind controls -->
  <div style="display: inline-block;">
    <div style="
          position: relative;
          display: inline-block;
          margin: 5px 15px 0 15px;
          float: right;
        ">
      <div style="float: left;">
        <button id="consoleCls" class="text-xs" type="submit" style="
              border: 1px dashed #FFF;
              padding: 2px 2px;
              color: black;
              background-color: yellow;
            ">
          Clear (auto)
        </button>
        <input id="app_core_console-auto_clear" type="checkbox" name="auto_clear" <?= $auto_clear ? 'checked="" ' : '' ?> />
        &nbsp;
      </div>

      <form action="http://localhost/?path" method="POST" style="float: right;">
        <div style="float: left; display: inline;">
          <button id="consoleSudo" class="text-xs" type="submit" style="
                border: 1px dashed #FFF;
                padding: 2px 2px;
                color: white;
                background-color: red;
              ">
            sudo
          </button>
          <input id="app_core_console-sudo" type="checkbox" name="auto_sudo" <?= defined('APP_SUDO') ? 'checked="" ' : '' ?> />&nbsp;
        </div>

        <div style="float: right; display: inline;">
          &nbsp;
          <button id="consoleAnykeyBind" class="text-xs" type="submit" style="
                border: 1px dashed #FFF;
                padding: 2px 2px;
                color: white;
                background-color: green;
              ">
            Bind Any[key]
          </button>
          <input id="app_ace_editor-auto_bind_anykey" type="checkbox" name="auto_bind_anykey" checked>
        </div>
      </form>
    </div>
  </div>

  <!-- Position toggle -->
  <button id="changePositionBtn" type="submit" style="float: right; margin: 5px 10px 0 0;">&#9650;</button>

  <!-- Output console -->
  <textarea id="responseConsole" name="responseConsole" rows="14" cols="92" spellcheck="false" readonly style="
        font-family: monospace;
        overflow-y: auto;
        height: 250px;
        width: 665px;
      "><?php
      echo "$shell_prompt\n";
      //$errors->{'CONSOLE'}  = 'wtf';
      
      //dd($errors);
      
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //var_dump($output['results']);
        if (!empty($output['command'])) // echo join("\n$shell_prompt", $output['command']) . "\n";
          foreach ($output['command'] as $command) {
            if (!empty($output['results'])) {
              echo "$shell_prompt$command";
              foreach ($output['results'] as $result)
                foreach ($result as $line) {
                  echo "$line\n";
                }
            }
          } else
          echo "$shell_prompt\n";

      }
      echo str_replace('{{STATUS}}', 'Server is running... PID=' . getmypid() . str_pad('', 6, " "), APP_DASHBOARD) . PHP_EOL;
      if (!empty($errors))
        foreach ($errors as $key => $error) {
          if (!is_array($error))
            echo /*$key . '=>' . */ $error . ($key != end($errors) ? '' : "\n");
          else
            echo var_export($error, true); // foreach($error as $err) echo $err . "\n";
          //else dd($error);
        }
      ?></textarea>
</div>

<!-- </div> -->

<?php $UI_APP['body'] = ob_get_contents();
ob_end_clean();

if (false) { ?>
  <script type="text/javascript"><?php }
ob_start(); ?>
  function deleteProcess(link) {
    const process = link.parentNode;
    process.parentNode.removeChild(process);
  }

  function startScroll(element) {
    const processList = document.getElementById('process-list');
    const duration = processList.offsetWidth / 75; // Adjust the speed by changing the divisor value
    element.style.animationDuration = `${duration}s`;
    element.classList.add('scrolling');
  }

  function stopScroll() {
    const processList = document.getElementById('process-list');
    const processes = processList.getElementsByClassName('process');
    for (const process of processes) {
      process.classList.remove('scrolling');
    }
  }

  var slider = document.getElementById("mySlider");

  slider.addEventListener("input", function () {

    var respConsole = document.getElementById("responseConsole");

    switch (this.value) {
      case "-2":
        respConsole.style.fontSize = "x-large";
        console.log("x-large");
        break;
      case "-1":
        respConsole.style.fontSize = "large";
        console.log("large");
        break;
      case "0":
        respConsole.style.fontSize = "medium";
        console.log("medium");
        break;
      case "1":
        respConsole.style.fontSize = "small";
        console.log("small");
        break;
      case "2":
        respConsole.style.fontSize = "x-small";
        console.log("x-small");
        break;
      default:
        console.log(this.value);
    }

    console.log(this.value); // Output the value to console (you can replace this with any other action)
  });

  const consoleContainer = document.getElementById('<?= $container_id; ?>');
  //const reqInp = document.getElementById('requestInput');
  const respCon = document.getElementById('responseConsole');

  const styles = window.getComputedStyle(consoleContainer);
  const changePositionBtn = document.getElementById('changePositionBtn');

  <?php if (defined('COMPOSER')) { ?>
    const initSubmit = document.getElementById('app_composer-init-submit');
  <?php } ?>

  let isFixed = false; // Store the current position state

  var requestInput = document.getElementById('requestInput');

  changePositionBtn.addEventListener('click', () => {
    if (consoleContainer.style.position == 'fixed') {
      isFixed = false;
      changePositionBtn.innerHTML = '&#9660;';
    } else {
      isFixed = !isFixed;
      changePositionBtn.innerHTML = '&#9650;';

    }
    //show_console();
  });

  export function show_console(event) {
    const consoleContainer = document.getElementById('<?= $container_id ?>');
    const respCon = document.getElementById('responseConsole');
    if (!consoleContainer || !respCon) return false;

    const KEY_BACKTICK = '`';
    const CODE_BACKTICK = 192;
    const CODE_BACKSPACE = 8;

    const isKeyEvent = !!event && typeof event === 'object' && ('key' in event || 'keyCode' in event);
    const key = isKeyEvent ? (event.key ?? '') : '';
    const keyCode = isKeyEvent ? (event.keyCode ?? 0) : 0;

    const active = document.activeElement;
    const inputActive = (active === requestInput);
    const bodyActive = (active === document.body);

    // ---------- Key handling (optional) ----------
    if (isKeyEvent) {
      // Toggle console with backtick
      if (key === KEY_BACKTICK || keyCode === CODE_BACKTICK) {
        event.preventDefault();

        // Open & focus if not already in input
        if (!inputActive) {
          requestInput.value = '';
          requestInput.focus();
        } else {
          // if already focused, allow closing by toggling state
          requestInput.blur();
        }

        // Toggle state and apply below
        isFixed = !isFixed;
      }

      // Prevent "Back" navigation when input is empty
      else if (keyCode === CODE_BACKSPACE && inputActive && requestInput.value === '') {
        event.preventDefault();
        return false;
      }

      // If typing while body focused, open console and place first char
      else if (bodyActive && key && key.length === 1) {
        event.preventDefault();

        // Ensure console is visible/open
        if (!isFixed) isFixed = true;

        requestInput.value = key;
        requestInput.focus();
      }
    }

    // ---------- Apply layout ----------
    applyConsoleLayout(consoleContainer, respCon);

    return false;
  }

  function applyConsoleLayout(consoleContainer, respCon) {
    // "isFixed === true" => fixed (smaller)
    // "isFixed === false" => absolute (bigger)
    if (isFixed) {
      // Fixed mode
      consoleContainer.style.position = 'fixed';
      consoleContainer.style.top = '-300px';
      consoleContainer.style.left = '50%';
      consoleContainer.style.bottom = '360px';
      consoleContainer.style.right = '';
      consoleContainer.style.height = '295px';
      consoleContainer.style.transform = 'translate(-50%, -50%)';
      consoleContainer.style.textAlign = 'center';
      consoleContainer.style.zIndex = '999';

      respCon.style.height = '256px';
      if (changePositionBtn) changePositionBtn.innerHTML = '&#9660;';
    } else {
      // Absolute mode
      consoleContainer.style.position = 'absolute';
      consoleContainer.style.top = '80px';
      consoleContainer.style.left = '50%';
      consoleContainer.style.right = '';
      consoleContainer.style.bottom = '0px';
      consoleContainer.style.height = '100px';
      consoleContainer.style.transform = 'translate(-50%, -50%)';
      consoleContainer.style.textAlign = 'center';
      consoleContainer.style.zIndex = '999';

      respCon.style.height = '375px';
      if (changePositionBtn) changePositionBtn.innerHTML = '&#9650;';
    }
  }
  /*
    async function runTask(name = 'startup', opts = { plain: true }) {
      const qs = new URLSearchParams({ api: 'tasks', task: name });
      if (opts.plain) qs.set('plain', '1');
      const res = await fetch('/?' + qs.toString(), { headers: { 'Accept': opts.plain ? 'text/plain' : 'application/json' } });
      const text = await res.text(); // fine for both; JSON if needed: await res.json()
      $('#responseConsole').val('runtask ' + name + '\n' + $('#responseConsole').val());
      $('#responseConsole').val(text + '\n' + $('#responseConsole').val());
      //$('#responseConsole').scrollTop($('#responseConsole')[0].scrollHeight);
      (window.ConsoleUI?.print ?? console.log)(text);
    } */

  window.runTaskSequence = function (taskName, step) {
    step = step || 0;

    const params = new URLSearchParams();
    params.set('task', taskName);
    params.set('step', step);
    params.set('format', 'json');

    return fetch('/?api=tasks', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: params
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        const $console = $('#responseConsole');

        if (!data || !data.ok) {
          $console.val(
            'Task ' + taskName + ' step ' + step + ' failed.' + "\n" +
            $console.val()
          );
          return;
        }

        const humanStep = data.step + 1; // 1-based
        const total = data.total_steps;

        // 1) Show job name/output now
        const lines = [];
        lines.push(
          'Job ' + humanStep + ' / ' + total +
          ' [' + (data.done ? 'done' : 'ok') + '] (' + data.duration_ms + ' ms)'
        );

        if (data.output) {
          lines.push(String(data.output).replace(/\n+$/, ''));
        }

        // prepend just the job lines first
        $console.val(lines.join("\n") + "\n" + $console.val());

        // 2) If this was the last step, prepend the completion line separately
        if (data.done) {
          $console.val(
            '=== Task ' + data.task + ' completed. ===' + "\n" +
            $console.val()
          );
          return; // no next step
        }

        // 3) If there is another step, start it AFTER this one
        if (data.next_step != null) {
          return window.runTaskSequence(taskName, data.next_step);
        }
      })
      .catch(function (err) {
        const $console = $('#responseConsole');
        $console.val(
          'Error running task ' + taskName + ' step ' + step + ': ' + err + "\n" +
          $console.val()
        );
      });
  };

  /*  window.runTask = function (taskName, opts) {
      opts = opts || {};
      const plain = !!opts.plain;
      const format = plain ? 'text' : 'json';
  
      const body = new URLSearchParams();
      body.set('task', taskName);
      body.set('format', format);
  
      return fetch('/?api=tasks', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body
      })
        .then(function (res) {
          if (plain) {
            return res.text();
          }
          return res.json();
        })
        .then(function (data) {
          const $console = $('#responseConsole');
  
          if (plain) {
            // text mode: prepend the dump
            $console.val(data + "\n" + $console.val());
            return;
          }
  
          // json mode: structured visual confirmation
          if (!data || !data.ok) {
            $console.val(
              'Task ' + taskName + ' failed: ' + (data && data.error ? data.error : 'Unknown error') +
              "\n" + $console.val()
            );
            return;
          }
  
          const lines = [];
          lines.push('=== Task ' + data.task + ' (' + data.status + ') ===');
  
          (data.jobs || []).forEach(function (job) {
            var line = '[' + job.status + '] ' + job.name;
            if (job.duration_ms != null) {
              line += ' (' + job.duration_ms + ' ms)';
            }
            lines.push(line);
  
            if (job.output) {
              lines.push(String(job.output).replace(/\n+$/, ''));
            }
          });
  
          lines.push('=== Task ' + data.task + ' done ===');
  
          $console.val(lines.join("\n") + "\n" + $console.val());
        })
        .catch(function (err) {
          const $console = $('#responseConsole');
          $console.val(
            'Error running task ' + taskName + ': ' + err +
            "\n" + $console.val()
          );
        });
    }; */

  window.show_console = show_console;

  // Attach a focus event listener to the input element
  requestInput.addEventListener('focus', function () {
    // Check the condition before calling the show_console function
    //if (consoleContainer.style.position !== 'fixed')
    if (document.getElementById('<?= $container_id ?>').style.position != 'absolute') {
      if (isFixed) {
        requestInput.focus();
        //isFixed = false;
        //show_console();
      }
    } isFixed = true; show_console();
  });

  <?php if (defined('COMPOSER_1')) { ?>
  initSubmit.addEventListener('click', () => {
    show_console();
    const requestInput = document.getElementById('requestInput');
    const requestConsole = document.getElementById('requestConsole');
    const argv = requestInput.value;
    $.post("<?= APP_URL . '?' . $_SERVER['QUERY_STRING']; /*$projectRoot*/ ?>",
      {
        cmd: argv
      },
      function (data, status) {
        console.log('I can love me better baby!');
        const requestConsole = document.getElementById('requestConsole');
        console.log("Data: " + data + "\nStatus1: " + status);
        if (requestConsole !== null) {
          requestConsole.value = data + argv;
          requestConsole.value = '<?= $shell_prompt; ?>' + argv + " \n" + data;

          requestConsole.scrollTop = requestConsole.scrollHeight;
          console.log('changed scroll');
        }
      }

    );
  });
  <?php } ?>
  window.addEventListener('resize', () => {
    // document.getElementById('responseConsole').style.width = window.innerWidth - 15 + 'px';
  });

  /* =========================
   * Console Command Runner
   * ========================= */
  async function runConsoleLine(line) {
    const input = String(line ?? '').trim();
    if (!input) return { ok: true };

    // 1) CLIENT/UI-FIRST (fast, no server)
    const uiRes = await window.handleConsoleCommand?.(input);
    if (uiRes?.ok) return uiRes;

    // 2) ROUTE BY PREFIX
    if (/^git(\s|$)/i.test(input)) {
      return await runRemoteGit(input);     // ✅ tools/code/git pipeline
    }

    if (/^(composer|npm|node|php)(\s|$)/i.test(input)) {
      return await runRemoteOp(input);      // ✅ generic server ops pipeline
    }

    // 3) FALLBACK (your existing console backend)
    return await runRemoteConsole(input);
  }

  async function executeAndPrint(argv) {
    // show prompt line (always new line)

    const res = await runConsoleLine(argv);
    prependToConsole('[DEBUG typeof res] ' + (typeof res));
    // prependToConsole('[DEBUG res] ' + JSON.stringify(res));
    const out = normalizeConsoleResponse(res);

    if (!out.ok) {
      prependToConsole(`[ERROR] ${out.error || 'Unknown error'}`);
      if (out.raw) prependToConsole(out.raw);
      return out;
    }

    if (out.prompt) prependToConsole(`<?= $shell_prompt; ?>${out.prompt}`);

    if (out.result) prependToConsole(out.result);


    prependToConsole(`<?= $shell_prompt; ?>`);
    return out;
  }


  /* =========================
   * Console Output Utilities
   * ========================= */

  function ensureNewline(s) {
    s = String(s ?? '').replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    return s.endsWith('\n') ? s : s + '\n';
  }

  function prependToConsole(text) {
    const el = document.getElementById('responseConsole');
    if (!el) return;
    el.value = ensureNewline(text) + el.value;
  }

  /* =========================
   * Response Normalizer
   * ========================= */

  function normalizeConsoleResponse(res) {
    // Plain string
    if (typeof res === 'string') {
      return { ok: true, result: res };
    }

    // Nothing usable
    if (!res || typeof res !== 'object') {
      return { ok: false, error: 'Empty response' };
    }

    // Preferred modern shape
    if ('ok' in res) {
      if (typeof res.result === 'string') return res;

      if (Array.isArray(res.output)) {
        return { ...res, result: res.output.join('\n') };
      }

      return {
        ...res,
        result: res.result != null ? String(res.result) : ''
      };
    }

    // Legacy api/git.php shape
    if ('status' in res) {
      const ok = res.status === 'success';

      const out = Array.isArray(res.output)
        ? res.output.join('\n')
        : String(res.output ?? '');

      const err =
        res.errors && typeof res.errors === 'object'
          ? Object.values(res.errors).join('\n')
          : String(res.errors ?? '');

      return {
        ...res,
        ok,
        result: (out + (err ? `\n\n${err}` : '')).trim(),
        error: ok ? undefined : (err || res.message || 'Git error')
      };
    }

    // Final fallback — never print [object Object]
    return { ok: true, result: JSON.stringify(res, null, 2) };
  }

  /* =========================
   * Remote Git Handler (POST)
   * ========================= */

  async function runRemoteGit(cmd) {
    // Prefer git tool app if loaded
    const tool = window.App?.['tools/code/git'];
    if (tool && typeof tool.run === 'function') {
      return await tool.run(cmd);
    }

    // API fallback
    const base = `<?= UrlContext::getBaseHref(); ?>`;
    const url = new URL(base, location.origin);
    url.searchParams.set('api', 'git');

    const res = await fetch(url.toString(), {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: new URLSearchParams({ cmd })
    });

    const raw = await res.text();

    console.log('[git] HTTP', res.status, 'raw:', raw);

    try {
      return JSON.parse(raw);
    } catch {
      return {
        ok: false,
        error: 'Git API returned invalid JSON',
        raw
      };
    }
  }

  /* =========================
   * Remote Console Fallback
   * ========================= */

  async function runRemoteConsole(cmd) {
    const base = `<?= UrlContext::getBaseHref(); ?>`;
    const url = new URL(base, location.origin);
    url.searchParams.set('api', 'console');

    const res = await fetch(url.toString(), {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: new URLSearchParams({ cmd })
    });

    const raw = await res.text();
    console.log('[console] HTTP', res.status, 'raw:', raw);

    try {
      return JSON.parse(raw);
    } catch {
      return raw;
    }
  }
  //requestInput.addEventListener('focus', (consoleContainer.style.position == 'absolute' ? null : show_console()));

  /* =========================
   * Submit Button Hook
   * ========================= */

  document.getElementById('requestSubmit')?.addEventListener('click', async (e) => {
    e.preventDefault();
    e.stopPropagation();

    const input = document.getElementById('requestInput');
    if (!input) return;

    const argv = input.value.trim();
    if (!argv) return;

    input.value = '';
    await executeAndPrint(argv);
  });

  $(document).ready(function () {
    const autoClear = $("#app_core_console-auto_clear").checked;

    //$('#responseConsole').css('width', $(window).width() - 20 + 'px');

    $(".slide-toggle").click(function () {
      $(".box").animate({
        width: "toggle"
      });
    });
    <?php if (defined('APP_PROJECT')) { ?>
    //getDirectory('<?= isset($_GET['project']) && !empty($_GET['project']) ? basename(APP_PATH . APP_ROOT) : '' ?>', '<?= isset($_GET['project']) && !empty($_GET['project']) ? '' : APP_PATH ?>');
    console.log('Path: <?= APP_PATH ?>');
    <?php } ?>

    $("#requestInput").bind("keydown", {}, keypressInBox); //keypress

    function keypressInBox(e) {
      var code = (e.keyCode ? e.keyCode : e.which);
      switch (code) {

        case 13: //Enter keycode
          e.preventDefault();
          if ($('#requestInput').val() == 'clear') {
            $('#responseConsole').val('>_');
            $('#requestInput').val('');
          } else
            if ($('#requestInput').val() != '')
              $("#requestSubmit").click();
          $('#requestInput').val('');
          break;
        //case 37:
        // str = 'Left Key pressed!';
        // break;
        case 38:
          $('#requestInput').val('test up');
          break;
        //case 39:
        // str = 'Right Key pressed!';
        // break;
        case 40:
          $('#requestInput').val('test down');

          break;
        default:
          console.log('Key Code: ' + code);
          //show_console();
          break;
      }
    };

    $('#consoleCls').on('click', function () {
      console.log('Button Clicked!');
      $('#responseConsole').val('<?= $shell_prompt; ?>');
      //if ($('<?= $selector ?>').css('position') == 'absolute')
      //  show_console();
    });


    $('#changePositionBtn').on('click', function () {
      console.log('Drop Button Clicked!');
      show_console();
    });

    $("#app_git-help-cmd").click(function () {
      $('#requestInput').val('git help');
      $('#requestSubmit').click();
      console.log('wow');

      if (!isFixed) isFixed = true;
      show_console();
    });

    $("#app_git-add-cmd").click(function () {
      $('#requestInput').val('git add .');
      $('#requestSubmit').click();
      console.log('wow');
    });

    $("#app_git-remote-cmd").click(function () {
      $('#requestInput').val('git remote -v');
      $('#requestSubmit').click();
      console.log('wow');
    });

    $("#app_git-commit-cmd").click(function () {
      $('#requestInput').val('git commit -am "default message"');
      document.getElementById('app_git-commit-msg').style.display = 'block';

      if (!isFixed) isFixed = true;
      show_console();
      //$('#requestSubmit').click();
    });

    $("#app_git-clone-cmd").click(function () {
      $('#requestInput').val('git clone '); /* I need to get the URL */

      document.getElementById('app_git-clone-url').style.display = 'block';

      if (!isFixed) isFixed = true;
      show_console();
      //$('#requestSubmit').click();
    });

    /*
        const el = document.getElementById('app_git-oauth-input');
        document.getElementById('app_git-oauth-input').addEventListener("keydown", function (event) {
          if (event.keyCode === 13) {
            // Enter key was pressed
            console.log("Enter key pressed");
    
            <?php
            //dd(APP_PATH . APP_ROOT . '.git/config');
            
            if (is_file($file = APP_PATH . APP_ROOT . '.git/config')) {

              $config = parse_ini_file($file, true);


              if (isset($config['remote origin']['url']) && preg_match('/(?:[a-z]+\:\/\/)?([^\s]+@)?((?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*))/', $config['remote origin']['url'], $matches))
                if (count($matches) >= 2) { ?>

    $('#requestInput').val('git remote set-url origin https://' + $("#app_git-oauth-input").val() + '@<?= $matches[2] ?>');

    <?php } else { ?>

    $('#requestInput').val('git remote set-url origin https://' + $("#app_git-oauth-input").val() + '@<?= $matches[1] ?>');

    <?php }
            } ?>

    document.getElementById('app_git-clone-url').style.display = 'none';

    $('#requestSubmit').click();
  }

    });

  document.getElementById('app_git-commit-input').addEventListener("keydown", function (event) {
    if (event.keyCode === 13) {
      // Enter key was pressed
      console.log("Enter key pressed");

      $('#requestInput').val('git commit -am "' + $("#app_git-commit-input").val() + '"');

      document.getElementById('app_git-commit-msg').style.display = 'none';

      $('#requestSubmit').click();
    }

  });

  document.getElementById('app_git-clone-url').addEventListener("keydown", function (event) {
    if (event.keyCode === 13) {
      // Enter key was pressed
      console.log("Enter key pressed");

      $('#requestInput').val('git clone ' + $("#app_git-clone-url-input").val() + ' .');

      document.getElementById('app_git-clone-url').style.display = 'none';

      $('#requestSubmit').click();
    }
  });
*/
  $("#app_php-error-log").click(function () {
    $('#requestInput').val('wget <?= APP_URL ?>?error_log=unlink'); // unlink
    //show_console();
    $('#requestSubmit').click();
  });

  $("#app_composer-init-submit").click(function () {
    const requestValue = $('#app_composer-init-input').val().replace(/\n/g, ' ');

    $('#requestInput').val(requestValue);
    $('#requestSubmit').click(); //show_console();
    if ($('<?= $selector ?>').css('position') == 'absolute')
      $('#changePositionBtn').click();
    $('#requestInput').val('');
  });

  $('#requestSubmit').href = 'javascript:void(0);';

/*
  $('#requestSubmit').click(async function () {
    const argv = $('#requestInput').val().trim();
    if (!argv) return;

    // show prompt line
    $('#responseConsole').val(`<?= $shell_prompt; ?>${ argv } \n` + $('#responseConsole').val());
    $('#requestInput').val('');

    // run through the router
    const res = await runConsoleLine(argv);

    // print output (normalize)
    if (res?.ok === false) {
      $('#responseConsole').val(`[ERROR] ${ res.error || 'Unknown error' } \n` + $('#responseConsole').val());
    } else if (res?.result != null) {
      $('#responseConsole').val(String(res.result).replace(/\n*$/, '\n') + $('#responseConsole').val());
    } else if (typeof res === 'string') {
      $('#responseConsole').val(res.replace(/\n*$/, '\n') + $('#responseConsole').val());
    }

    $('#responseConsole').scrollTop($('#responseConsole')[0].scrollHeight);
  });
*/
  });

  <?php $UI_APP['script'] = ob_get_contents();
  ob_end_clean();

  if (false) { ?></script><?php }

  ob_start(); ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

  <?php
  // (APP_IS_ONLINE && check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'public/assets/vendor/tailwindcss-3.3.5.js')?
// Path to the JavaScript file
  
  // Create the directory if it doesn't exist
  getcwd() === rtrim($path = APP_PATH . APP_BASE['public'], '/') && is_dir($path .= 'assets/vendor') or mkdir($path, 0755, true);

  $path .= '/tailwindcss-3.3.5.js';

  // URL for the CDN
  $url = 'https://cdn.tailwindcss.com';

  // Check if the file exists and if it needs to be updated
  if (defined('APP_IS_ONLINE') && APP_IS_ONLINE)
    if (!is_file($path) || (time() - filemtime($path)) > 5 * 24 * 60 * 60) { // ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path . 'tailwindcss-3.3.5.js'))))) / 86400)) <= 0 
      // Download the file from the CDN
      $handle = curl_init($url);
      curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
      $js = curl_exec($handle);

      // Check if the download was successful
      if (!empty($js)) {
        // Save the file
        file_put_contents($path, $js) or $errors['JS-TAILWIND'] = "$url returned empty.";
      }
    }
  ?>

  <script
    src="<?= !defined('APP_IS_ONLINE') || !APP_IS_ONLINE ? '' : (check_http_status($url) ? substr($url, strpos($url, parse_url($url)['host']) + strlen(parse_url($url)['host'])) : substr($path, strpos($path, dirname(APP_BASE['public'] . 'assets/vendor')))) ?>"></script>

  <style type="text/tailwindcss">
    <?= $UI_APP['style']; ?>
  </style>
</head>

<body>
  <?= $UI_APP['body']; ?>

  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="assets/vendor/jquery/jquery.min.js"></script> -->
  <script>
    <?= $UI_APP['script']; ?>
  </script>
</body>

</html>
<?php $UI_APP['html'] = ob_get_contents();
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'console' && APP_DEBUG)
  die($UI_APP['html']);

return $UI_APP;