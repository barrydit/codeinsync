<?php
// app/core/console.php

defined('APP_PATH') || define('APP_PATH', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR);
defined('CONFIG_PATH') || define('CONFIG_PATH', APP_PATH . 'config' . DIRECTORY_SEPARATOR);

// const APP_ROOT = '123';

// Ensure bootstrap has run (defines env/paths/url/app and helpers)
if (!defined('APP_BOOTSTRAPPED')) {
  require_once APP_PATH . 'bootstrap/bootstrap.php';
}

global $shell_prompt, $auto_clear, $errors, $asset, $baseHref;

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

ob_start(); ?>

/* Styles for the absolute div */
<?= $selector ?> {
position : absolute;
bottom : 0px;
left : 50%;
transform : translate(-50%, -50%);
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
  <button id="changePositionBtn" type="submit" style="float: right; margin: 5px 10px 0 0;">&#9660;</button>

  <!-- Output console -->
  <textarea id="responseConsole" name="responseConsole" rows="14" cols="92" spellcheck="false" readonly style="
        font-family: monospace;
        overflow-y: auto;
        height: 375px;
        width: 665px;
      "><?php
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
      changePositionBtn.innerHTML = '&#9650;';
    } else {

      isFixed = !isFixed;
      changePositionBtn.innerHTML = '&#9660;';

    }
    //show_console();
  });

  export function show_console(event) {
    console.log('showing console...');

    const consoleContainer = document.getElementById('<?= $container_id ?>');

    //requestInput.focus();

    if (typeof event !== 'undefined')
      if (event.key === '`' || event.keyCode === 192) // c||67
        if (document.activeElement !== requestInput) {
          // Replace the following line with your desired function
          // If it's currently absolute, change to fixed
          if (!isFixed) {
            requestInput.value = '';
            requestInput.focus();
          }
          event.preventDefault();
          //show_console();
        } else {
          document.activeElement = null;
          return false;
        }
      else if (event.keyCode === 8 && requestInput.value == '') {
        if (document.activeElement === requestInput)
          event.preventDefault();
        return false;
      } else {
        if (document.activeElement == document.body) {
          // Replace the following line with your desired function
          // If it's currently absolute, change to fixed
          if (!isFixed) {
            requestInput.value = event.key;
            requestInput.focus();
            //isFixed = true;
            show_console();
          } else { }
          event.preventDefault();
          console.log('activeElement');
        } else {
          document.activeElement = null;
          console.log('else');
          return false;
        }
      }
    //isFixed = !isFixed;

    if (typeof isFixed === 'undefined') {
      //if (event !== undefined)
      console.log('isFixed is undefined');
    } else {
      if (!isFixed) {

        // If it's currently fixed, change back to absolute
        consoleContainer.style.position = 'absolute';
        consoleContainer.style.top = '';
        consoleContainer.style.left = '50%';
        consoleContainer.style.right = '';
        consoleContainer.style.bottom = '0';
        consoleContainer.style.transform = 'translate(-50%, -50%)';
        consoleContainer.style.textAlign = 'center';
        consoleContainer.style.zIndex = '999';

        respCon.style.height = '375px';

        changePositionBtn.innerHTML = '&#9660;';

        /*
        consoleContainer.style.marginLeft = 'auto';
        consoleContainer.style.marginRight = 'auto';
        consoleContainer.style.textAlign = 'center';
        consoleContainer.style.transform = 'none';
        */
      } else {

        // If it's currently absolute, change to fixed
        consoleContainer.style.position = 'fixed';
        consoleContainer.style.top = '35%'; // Set the fixed position as needed
        consoleContainer.style.left = '50%';
        consoleContainer.style.bottom = '36px';
        consoleContainer.style.transform = 'translate(-50%, -50%)';
        consoleContainer.style.zIndex = '999';

        respCon.style.height = '220px';

        changePositionBtn.innerHTML = '&#9650;';
      }

    }
    if (isFixed) isFixed = !isFixed;
    //isFixed = true;
    // Toggle the state for the next click
    //isFixed = !isFixed;
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
      if (isFixed)
        requestInput.focus();

      //show_console();
      if (!isFixed) {
        isFixed = !isFixed; //isFixed = true;
        //show_console();
      } else {
        isFixed = !isFixed; //isFixed = false;
        show_console();
      }
    }


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


  //requestInput.addEventListener('focus', (consoleContainer.style.position == 'absolute' ? null : show_console()));

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

  $('#requestSubmit').click(function () {
    let matches = null;
    const autoClear = document.getElementById('app_core_console-auto_clear').checked;
    console.log('autoClear is ' + autoClear);

    if (!isFixed) isFixed = true;
    //show_console();

    if ($('<?= $selector ?>').css('position') != 'absolute') {
      //window.isFixed = true;
      //if (!window.isFixed) window.isFixed = !window.isFixed;

      //if (!isFixed) isFixed = true;
      //show_console();
      //$('#changePositionBtn').click();
    }
    const argv = $('#requestInput').val().trim();

    if (argv === '') return;

    const processList = document.getElementById('process-list');
    const newProcess = document.createElement('div');
    newProcess.classList.add('process');
    newProcess.innerHTML = `<a href="#" onclick="deleteProcess(this)">[X]</a> ${argv}`;

    // Add mouseover event
    newProcess.onmouseover = function () {
      setTimeout(() => { startScroll(newProcess); }, 3000);
    };

    setTimeout(() => {
      if (newProcess.parentNode) { // Check if process still exists
        newProcess.textContent = argv;
        newProcess.onmouseover = function () {
          startScroll(newProcess);
        };
        // Send post request
        // $.post('<?= /* basename(__FILE__) .*/ '?' . $_SERVER['QUERY_STRING']; /*$projectRoot*/ ?>', { cmd: argv });
      }
    }, 3000);

    processList.prepend(newProcess);

    console.log('Argv: ' + argv);

    if (autoClear) $('#responseConsole').val('<?= $shell_prompt; ?>' + argv);

    let DirQueryParams = '<?= $baseHref ?>?api=console&cmd=' + encodeURIComponent(argv);

    if (argv == '') $('#responseConsole').val('<?= $shell_prompt; ?>' + "\n" + $('#responseConsole').val()); // +
    else if (matches = argv.match(/^(?:echo\s+)?(hello)\s+world/i)) { // argv == 'edit'
      if (matches) {
        $('#responseConsole').val(matches[1].charAt(0).toUpperCase() + matches[1].slice(1) + ' ' + 'Barry' + "\n" +
          '<?= $shell_prompt; ?>' + argv + "\n" + $('#responseConsole').val());
        return false;
      } else {
        console.log("Invalid input format.");
      }
    }
    else if (matches = argv.match(/^project/i)) { // argv == 'edit'
      if (matches) {
        document.getElementById('app_project-container').style.display = 'block';
        $('#responseConsole').val('Barry, here you can begin editing your project.' + "\n" + '<?= $shell_prompt; ?>' + argv + "\n" + $('#responseConsole').val());
        changePositionBtn.click();
        return false;
      } else {
        console.log("Invalid input format.");
      }
    } else if (matches = argv.match(/^h(?:elp)?\s+?(\S+)$/)) {
      //$('#requestInput').val('help');
      //$('#requestSubmit').click();
    } else if ((matches = argv.match(/^(?:runtask\s+)?(\S+)$/))) {
      const taskName = matches[1];
      console.log('Running task: ' + taskName);
      window.runTaskSequence(taskName); // NEW
      $('#responseConsole').val(argv + "\n" + $('#responseConsole').val());
      return false;
    }/* else if ((matches = argv.match(/^(?:runtask\s+)?(\S+)$/))) {
      const taskName = matches[1];
      console.log('Running task: ' + taskName);
      window.runTask(taskName, { plain: true });
      $('#responseConsole').val(argv + "\n" + $('#responseConsole').val());
      return false;
    } */ else if (matches = argv.match(/^j(?:ava)?s(?:cript)?\s+?(\S+)$/)) {
      // Save the original console.log function
      var originalLog = console.log;

      // Create an array to store log messages
      var logMessages = [];

      var js_prompt = 'javascript: ';
      var codeString = matches[1]; // "console.log('Hello, world!');";
      var myFunction = new Function(codeString);

      myFunction();
      // Override console.log to capture messages
      console.log = function () {
        // Save the log message to the array
        logMessages.push(Array.from(codeString).join(' '));

        $('#responseConsole').val(logMessages[1] + "\n" + js_prompt + codeString + "\n" + $('#responseConsole').val());

        // Call the original console.log function
        originalLog.apply(console, logMessages);
        return false;
      };
      console.log();
      console.log = originalLog;
      return false;
    } else if (matches = argv.match(/^edit\s+(\S+)$/)) { // argv == 'edit'
      if (matches) {
        const pathname = matches[1]; // "/path/to/file.txt"
        console.log("Editing: ", pathname);

        const filePath = pathname;

        const lastSlashIndex = filePath.lastIndexOf('/');
        const dirname = filePath.substring(0, lastSlashIndex);
        const filename = filePath.substring(lastSlashIndex + 1);

        $.post(<?= 'DirQueryParams'; /*'"app.directory.php' . '?' . $_SERVER['QUERY_STRING'] . '' ;"*/ ?>,
{
            cmd: argv
          },
          function (data, status) {
            console.log("Data 1: " + data + "Status: " + status);
            console.log("Web Query: " + DirQueryParams);
            //data = data.trim(); // replace(/(\r\n|\n|\r)/gm, "")

            if (matches = argv.match(/edit(\s+(:?.*)?|)/gm)) {
              //editor1.setValue(data);

              document.getElementById('app_ace_editor-container').style.display = 'block';
              //console.log(data);
            }
          });

        // window.location.href = '<?= APP_URL ?>?app=ace_editor&path=' + dirname + '&file=' + filename; // filename= + pathname
        return false;
      } else {
        console.log("Invalid input format.");
      }
      return false;
    } else if (argv == 'clear') $('#responseConsole').val('clear');
    else if (argv == 'cls') $('#responseConsole').val('<?= $shell_prompt; ?>');
    else if (argv == 'reset') $('#responseConsole').val('>_');
    else {
      if (autoClear) {
        $('#responseConsole').val(data + argv);
        $('#responseConsole').val('<?= $shell_prompt; ?>' + argv + "\n");
      } else {
        $('#responseConsole').val('<?= $shell_prompt; ?>' + argv + "\n" + $('#responseConsole').val());
      }

      // $('#requestSubmit').href = 'javascript:void(0);';

      $.post(<?= 'DirQueryParams' /*'"' . basename(__FILE__). '?' . $_SERVER['QUERY_STRING']. '"' : '' */ ; ?>,
      {
        cmd: argv
      },
      function(data, status) {
        console.log("Web Query: " + DirQueryParams);
        console.log("Data 2: " + JSON.stringify(data) + "\n Status: " + status);
        console.log("Data Test: " + data + "\n Status: " + status);

        //data = data.trim(); // replace(/(\r\n|\n|\r)/gm, "")

        const gitPath = `<?= str_replace('/', '\/', defined('GIT_EXEC') ? dirname(GIT_EXEC) : ''); ?>`;
        const gitExec = `<?= defined('GIT_EXEC') ? basename(GIT_EXEC) : ''; ?>`;

        let parsed;
        try {
          parsed = JSON.parse(data);
        } catch (e) {
          console.error("Invalid JSON:", data);
          return;
        }

        // If server returned ok:true
        if (parsed.ok) {
          // Split the result string into an array
          //const items = parsed.result.split(',').map(s => s.trim());
          // console.log(items);
          // For example, show them in a textarea or console
          //$('#responseConsole').val(items.join(', '));
          $('#responseConsole').val(parsed.result + "\n" + $('#responseConsole').val());
        } else {
          // Handle error case
          $('#responseConsole').val('<?= $shell_prompt; ?>Error: ' + (parsed.error || 'Unknown error') + "\n" + $('#responseConsole').val());
        }

        /* if (JSON.parse(data).hasOwnProperty('ok')) {
          $('#responseConsole').val('<?= $shell_prompt; ?>Error: ' + JSON.parse(data).error + "\n" + $('#responseConsole').val());
      } else {

        processGitOutput(data, argv, gitPath, gitExec);
      }*/

      if (matches = argv.match(/chdir(\s+(:?.*)?|)/gm)) {
        document.getElementById('app_directory-container').innerHTML = data;
        //console.log(data);
      } else if (matches = data.match(new RegExp(`((:?sudo\\s+)?(:?${gitPath}) ? ${gitExec}.*)`, 'gm'))) {
        if (matches = data.match(/.*status.*\n+/gm)) {
          if (matches = data.match(/.*On branch main\nYour branch is (ahead of|up to date with).*(:?by\s[0-9]+commits)?/gm)) {
            if (matches = data.match(/.*On branch main\nYour branch is up to date with.*\n+/gm)) {
              if (matches = data.match(/.*nothing to commit, working tree clean/gm)) {
                //
              }
            }
            if (matches = data.match(/.*nothing to commit, working tree clean/gm)) {
              $('#requestInput').val('git push');
              $('#requestSubmit').click();
            } else if (matches = data.match(/.*Changes not staged for commit:/gm)) {
              $('#requestInput').val('git add .');
              $('#requestSubmit').click();
              if (confirm('(Re)Check Git Status?')) {
                // User clicked OK
                $('#requestInput').val('git status');
                $('#requestSubmit').click();
              } else {
                // User clicked Cancel
                console.log('User clicked Cancel');
              }
              //
            } else if (matches = data.match(/.*Changes to be committed:/gm)) {
              $('#requestInput').val('git commit -am "automatic <?= date('Y-m-d h:i:s'); ?> commit"');
              //$('#requestSubmit').click();
            }
          }
          $('#responseConsole').val(data + "\n" + $('#responseConsole').val());
        } else if (matches = data.match(/.*remote\s-v.*\n+/gm)) {
          if (matches = data.match(/.*origin\s+(?:[a-z]+\:\/\/)?([^\s]+@)?((?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*))\s+\((fetch|push)\)/gm)) {
            // if (matches === undefined || array.matches == 0) {
            // array empty or does not exist
            // }
            $('#responseConsole').val(data + "\n" + $('#responseConsole').val());
          } else {
            $('#responseConsole').val(data + "\nNo URL were found." + $('#responseConsole').val());
          }
        } else if (matches = data.match(/.*push.*\n+/gm)) {
          if (matches = data.match(/.*Error:.+(fatal: could not read Password for.+)\n+Exit Code:.([0-9]+)/gm)) {
            $('#responseConsole').val('<?= $shell_prompt; ?>Wrong Password!' + "\n" + data + "\n" + $('#responseConsole').val());
            document.getElementById('app_git-container').style.display = 'block';
            document.getElementById('app_git-oauth').style.display = 'block';
            document.getElementById('app_git-clone-url').style.display = 'none';
            document.getElementById('app_git-commit-msg').style.display = 'none';
          } else if (matches = data.match(/.*push.*\n+To.*/gm)) {
            if (matches = data.match(/.*push.*\n+To.*\n.*!.*\[rejected\].+(\w+).+[->].+(\w+).\(fetch first\)/gm)) {
              $('#responseConsole').val('<?= $shell_prompt; ?>Push unsuccessful. Fetch first ' + "\n" + data + "\n" +
                $('#responseConsole').val());
              $('#requestInput').val('git fetch origin main');
              $('#requestSubmit').click();
              $('#requestInput').val('git merge origin/main');
              $('#requestSubmit').click();
              $('#requestInput').val('git commit');
              $('#requestSubmit').click();
              $('#requestInput').val('git push origin main');
              if (confirm('git push origin main?')) {
                $('#requestSubmit').click();
              }
            } else if (matches = data.match(/.*push.*\n+To.*\n.*!.*\[rejected\].+(\w+).+[->].+(\w+).\(non-fast-forward\)/gm)) {
              $('#responseConsole').val('<?= $shell_prompt; ?>Push unsuccessful. "non-fast-forward" error ' + "\n" + data + "\n" +
                $('#responseConsole').val());
              $('#requestInput').val('git push --force origin main');
              if (confirm('(Force) git push origin main?')) {
                $('#requestSubmit').click();
              }
            } else {
              $('#responseConsole').val('<?= $shell_prompt; ?>Push successful' + "\n" + data + "\n" + $('#responseConsole').val());
            }
          } else if (matches = data.match(/.*push.*\n+Error: Everything up-to-date/gm)) {
            $('#responseConsole').val('<?= $shell_prompt; ?>Everything up-to-date' + "\n" + data + "\n" +
              $('#responseConsole').val());
          } else {
            $('#responseConsole').val('<?= $shell_prompt; ?>' + data + "\n" + $('#responseConsole').val());

            if (matches = data.match(/.*push.*\n+To.*\n.*!.*\[.*rejected\].+/gm)) {
              $('#responseConsole').val('<?= $shell_prompt; ?> Error: ... secret password may have been found.' + "\n" +
                $('#responseConsole').val());
            }
          }
        } else if (matches = data.match(/.*fetch.*\n+/gm)) {
          if (matches = data.match(/.*Error:.+From.+\n.+\* branch.+(\w+).+[->].+(\w+)/gm)) {
            $('#responseConsole').val('<?= $shell_prompt; ?>"non-fast-forward" error' + "\n" + data + "\n" +
              $('#responseConsole').val());
            $('#requestInput').val('git fetch origin main');
            $('#requestSubmit').click();
            if (confirm('(Re)Check Git Status?')) {
              // User clicked OK
              $('#requestInput').val('git status');
              $('#requestSubmit').click();
            } else {
              // User clicked Cancel
              console.log('User clicked Cancel');
            }
            $('#requestInput').val('git rebase origin/main');
            $('#requestSubmit').click();
            $('#requestInput').val('git rebase --continue');
            $('#requestSubmit').click();
            $('#requestInput').val('git push origin main');
            $('#requestSubmit').click();
          }
        } else if (matches = data.match(/.*pull.*\n/gm)) {
          $('#responseConsole').val(data + "\n" + $('#responseConsole').val());
          if (matches = data.match(/.*Already up to date\./gm))
            $('#responseConsole').val('<?= $shell_prompt; ?>Already up to date.' + "\n" + $('#responseConsole').val());
          else if (confirm('(Re)load Window?')) {
            // User clicked OK
            $('#responseConsole').val('<?= $shell_prompt; ?>Reloading page (User Prompt).' + "\n" + $('#responseConsole').val());
            window.location.reload(); // window.location.href = window.location.href;
          } else {
            // User clicked Cancel
            console.log('User clicked Cancel');
          }
        } else if (matches = data.match(new RegExp(`.*(:?${gitPath}) ? ${gitExec}.* commit.*\\n`, 'gm'))) {
          if (matches = data.match(/.*Error: Author identity unknown\./gm)) {
            $('#responseConsole').val('<?= $shell_prompt; ?>Author identity unknown' + "\n" + data + "\n" +
              $('#responseConsole').val());
            $('#requestInput').val('git config --global user.email "barryd.it@gmail.com"');
            $('#requestSubmit').click();
            $('#requestInput').val('git config --global user.name "Barry Dick"');
            $('#requestSubmit').click();
          } else {
            if (confirm('Git Push?')) {
              // User clicked OK
              $('#requestInput').val('git push');
              $('#requestSubmit').click();
            } else {
              // User clicked Cancel
              console.log('User clicked Cancel');
            }
          }
          $('#responseConsole').val(data + "\n" + $('#responseConsole').val());
        } else {
          // $('#responseConsole').val(data + "\n" + $('#responseConsole').val());
        }
      } else {
        //$('#requestInput').val(argv);
        //$('#requestSubmit').click();
        // $('#responseConsole').val(data + "\n" + $('#responseConsole').val());
        //$('#responseConsole').val(data + "\n" + $('#responseConsole').val());
      }
      //if (!autoClear) { $('#responseConsole').val("\n" + $('#responseConsole').val()); }

      //$('#requestInput').val('');

      $('#responseConsole').scrollTop = $('#responseConsole').scrollHeight;
    });
  }

  });

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
  // (APP_IS_ONLINE && check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'public/assets/js/tailwindcss-3.3.5.js')?
// Path to the JavaScript file
  
  // Create the directory if it doesn't exist
  (getcwd() === rtrim($path = APP_PATH . APP_BASE['public'], '/') && is_dir($path .= 'assets/js')) or mkdir($path, 0755, true);

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
    src="<?= !defined('APP_IS_ONLINE') || !APP_IS_ONLINE ? '' : (check_http_status($url) ? substr($url, strpos($url, parse_url($url)['host']) + strlen(parse_url($url)['host'])) : substr($path, strpos($path, dirname(APP_BASE['public'] . 'assets/js')))) ?>"></script>

  <style type="text/tailwindcss">
    <?= $UI_APP['style']; ?>
  </style>
</head>

<body>
  <?= $UI_APP['body']; ?>

  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="assets/js/jquery/jquery.min.js"></script> -->
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