<?php

/*
    realpath — Returns canonicalized absolute pathname
    is_writable — Tells whether the filename is writable
    unlink — Deletes a file
*/

if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=config.php'));

//!function_exists('dd') ? die('dd is not defined') : dd();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['cmd'])) {
      chdir(APP_PATH . APP_ROOT);
      if ($_POST['cmd'] && $_POST['cmd'] != '') 
        if (preg_match('/^install/i', $_POST['cmd']))
          include('templates/' . preg_split("/^install (\s*+)/i", $_POST['cmd'])[1] . '.php');
        //else if (preg_match('/^edit\s+(:?(.*))/i', $_POST['cmd'], $match))
          //exec($_POST['cmd'], $output);
          //die(header('Location: ' . APP_URL_BASE . '?app=text_editor&filename='.$_POST['cmd']));
        else if (preg_match('/^php\s+(:?(.*))/i', $_POST['cmd'], $match)) {
          if (preg_match('/^php\s+(?!(-r))/i', $_POST['cmd'])) {
            $match[1] = trim($match[1], '"');
            $output[] = eval($match[1] . (substr($match[1], -1) != ';' ? ';' : ''));
          } else if (preg_match('/^php\s+(?:(-r))\s+(:?(.*))/i', $_POST['cmd'], $match)) {
            $match[2] = trim($match[2], '"');
            $_POST['cmd'] = 'php -r "' . $match[2] . (substr($match[2], -1) != ';' ? ';' : '') . '"';
            exec($_POST['cmd'], $output);
            //$output[] = $_POST['cmd'];
          }

        } else if (preg_match('/^composer\s+(:?(.*))/i', $_POST['cmd'], $match)) {
          $output[] = 'sudo ' . COMPOSER_EXEC['bin'] . ' ' . $match[1];
$proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . COMPOSER_EXEC['bin'] . ' ' . $match[1],
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
          list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
          $output[] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (!isset($exitCode) && $exitCode == 0  ? NULL : ' Exit Code: ' . $exitCode));
          //$output[] = $_POST['cmd'];

        } else if (preg_match('/^git\s+(:?(.*))/i', $_POST['cmd'], $match)) {

          $requireFile = function($file) { require_once($file); };
          $requireFile(APP_PATH . 'config/git.php');
          if (preg_match('/^git\s+(help)(:?\s+)?/i', $_POST['cmd'])) {
            $output[] = <<<END
git reset filename   (unstage a specific file)

git branch
  -m   oldBranch newBranch   (Renaming a git branch)
  -d   Safe deletion
  -D   Forceful deletion

git commit -am "Default message"

git checkout -b branchName
END;
          $output[] = $command = ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '' : 'sudo ') . GIT_EXEC . (is_dir($path = APP_PATH . APP_ROOT . '.git') || APP_PATH . APP_ROOT != APP_PATH ? '' : '' ) . ' ' . $match[1];

$proc=proc_open($command,
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
  
          list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
          $output[] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : (preg_match('/^To\s' . DOMAIN_EXPR . '/', $stderr) ? $stderr : 'Error: ' . $stderr) ) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
          } else if (preg_match('/^git\s+(clone)(:?\s+)?/i', $_POST['cmd'])) {
          
            //$output[] = dd($_POST['cmd']);
            $output[] = 'test'; 
          
            if (preg_match('/^git\s+(clone).+(https:\/\/github\.com\/[\w.-]+\/[\w.-]+\.git).+([\w.-])/', $_POST['cmd'], $github_repo)) {

              if (realpath($github_repo[3])) $output[] = realpath($github_repo[3]);

              //$output[] = dd($github_repo);
              if (!is_dir('.git')) exec((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . 'git init', $output);

              exec('git branch -m master main', $output);

              exec((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ')  . 'git --git-dir="' . APP_PATH . APP_ROOT . '.git" --work-tree="' . APP_PATH . APP_ROOT . '" remote add origin ' . $github_repo[2], $output);

              //exec('git remote add origin ' . $github_repo[2], $output);

              exec((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ')  . 'git config core.sparseCheckout true', $output);

              //touch('.git/info/sparse-checkout');

              file_put_contents('.git/info/sparse-checkout', '*'); /// exec('echo "*" >> .git/info/sparse-checkout', $output);

              exec((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . 'git pull origin main', $output);

              //exec('sudo git init', $output);
              //$output[] = dd($output);
            }
          
            $output[] = 'This works ... ';
          
          } else {

          // git --git-dir=/var/www/.git --work-tree=/var/www pull
          
          // $GIT_DIR environment variable
          if (preg_match('/^(init)(:?\s+)?/i', $match[1])) 
            if (!is_file($path = APP_PATH . APP_ROOT . '.gitignore')) touch($path);
          
          $output[] = 'www-data@localhost:' . getcwd() . '# ' . $command = ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '' : 'sudo ') . GIT_EXEC . (is_dir($path = APP_PATH . APP_ROOT . '.git') || APP_PATH . APP_ROOT != APP_PATH ? ' --git-dir="' . $path . '" --work-tree="' . dirname($path) . '"': '' ) . ' ' . $match[1];

$proc=proc_open($command,
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
  
          list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
          $output[] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : (preg_match('/^To\s' . DOMAIN_EXPR . '/', $stderr) ? $stderr : 'Error: ' . $stderr) ) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
          //$output[] = $_POST['cmd'];
          }
  
/*
 Error: To https://github.com/barrydit/composer_app.git
   5fbad5b..29f689e  main -> main
   
^To\s(?:[a-z]+\:\/\/)?(?:[a-z0-9\\-]+\.)+[a-z]{2,6}(?:\/\S*)?
   
   
*/
  // 

        } else if (preg_match('/^npm\s+(:?(.*))/i', $_POST['cmd'], $match)) {
          $output[] = 'sudo ' . NPM_EXEC . ' ' . $match[1];
$proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . NPM_EXEC . ' ' . $match[1],
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
          list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
          $output[] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
          //$output[] = $_POST['cmd'];

          //exec($_POST['cmd'], $output);
        } else if (preg_match('/^whoami(:?(.*))/i', $_POST['cmd'], $match))
          exec('whoami', $output);
        else if (preg_match('/^wget\s+(:?(.*))/i', $_POST['cmd'], $match))
          /* https://stackoverflow.com/questions/9691367/how-do-i-request-a-file-but-not-save-it-with-wget */
          exec('wget -qO- ' . $match[1] . ' &> /dev/null', $output);
        else {
          if (preg_match('/^(\w+)\s+(:?(.*))/i', $_POST['cmd'], $match))
            if (isset($match[1]) && in_array($match[1], ['tail', 'cat', 'echo', 'env', 'sudo', 'whoami'])) {
              //exec('sudo ' . $match[1] . ' ' . $match[2], $output); // $output[] = var_dump($match);
              
$output[] = 'sudo ' . $match[1] . ' ' . $match[2];
$proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : 'sudo ') . $match[1] . ' ' . $match[2],
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
          list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
          $output[] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
              
}
          //$output[] = $_POST['cmd'] . "\n";
      
        }
      //else var_dump(NULL); // eval('echo $repo->status();')
      if (isset($output) && !empty($output))
        if (count($output) == 1) echo /*(isset($match[1]) ? $match[1] : 'PHP') . ' >>> ' . */ join("\n... <<< ", $output); // . "\n" var_dump($output);
        else echo join("\n", $output); // . "\n"
        //$output[] = 'post: ' . var_dump($_POST);
      //else var_dump(get_class_methods($repo));
      exit();
    }
}
/*
if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../git.php') ? '../git.php' : (is_file('../config/git.php') ? '../config/git.php' : null))
    : (is_file('git.php') ? 'git.php' : (is_file('config/git.php') ? 'config/git.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=git.php'));

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../composer.php') ? '../composer.php' : (is_file('../config/composer.php') ? '../config/composer.php' : null))
    : (is_file('composer.php') ? 'composer.php' : (is_file('config/composer.php') ? 'config/composer.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=composer.php'));

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../npm.php') ? '../npm.php' : (is_file('../config/npm.php') ? '../config/npm.php' : null))
    : (is_file('npm.php') ? 'npm.php' : (is_file('config/npm.php') ? 'config/npm.php' : null))) require_once($path); 
else die(var_dump($path . ' path was not found. file=npm.php'));
*/


define('CONSOLE', true);

ob_start(); ?>
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

/* Styles for the container div */
.container {
position: relative;
height: 100%;
width: 100%;
background-color: lightblue;
}

/* Styles for the absolute div */
#app_console-container {
position: fixed;
bottom: 65px;
left: 50%;
transform: translateX(-50%);
width: auto;
height: 45px;
background-color: #FFA6A6; /* rgba(255, 0, 0, 0.35) */
color: white;
text-align: center;
z-index: 1;
}

#responseConsole {
position: relative;
display: block;
margin: 0 auto;
background-color: #D0D0D0; /* rgba(200,200,200,0.85) */
color: black;
cursor: pointer;
height: 60px;
}

input {
  color: black;
}

    .vert-slider-container {
      position: relative;
      float: right;
      width: 10px; /* Adjust the width as needed */
      height: 100px; /* Adjust the height as needed */
      /* margin: 10px auto; Adjust margin to center vertically */
      background-color: #f0f0f0; /* Background color for the slider */
    }

    .vert-slider {
      position: absolute;
      top: 45px;
      left: -70px;
      width: 100px; /* Adjust the width of the slider track */
      height: 10px;
      background: #4CAF50; /* Slider track color */
      /*transform: translateX(-50%);*/
      transform: rotate(90deg); /* Rotate the slider vertically */
      cursor: pointer;
    }

    .vert-slider::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 20px; /* Adjust the thumb width */
      height: 20px; /* Adjust the thumb height */
      background: #fff; /* Thumb color */
      border: 2px solid #4CAF50; /* Thumb border color */
      border-radius: 50%; /* Rounded thumb */
      cursor: pointer;
      margin-top: -10px; /* Adjust thumb position */
      margin-left: -10px; /* Adjust thumb position */
    }

    .vert-slider::-moz-range-thumb {
      width: 20px; /* Adjust the thumb width */
      height: 20px; /* Adjust the thumb height */
      background: #fff; /* Thumb color */
      border: 2px solid #4CAF50; /* Thumb border color */
      border-radius: 50%; /* Rounded thumb */
      cursor: pointer;
    }

<?php $appConsole['style'] = ob_get_contents();
ob_end_clean();


$auto_clear = false;

$shell_prompt = 'www-data@localhost:~$ '; // '$ >'

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->

<div id="app_console-container" class="" style="border: 1px dashed #000; ">
    <div style="position: absolute; display: none; top: -320px; background-color: #FFF; border: 1px dashed #000; height: 160px; width: 100%; padding: 20px 10px; color: #000; text-align: left; z-index: -1; text-align: center;" class="text-sm">
    <h1>&lt;html&gt; &lt;head&gt;</h1>
    <h2>&lt;meta&gt;, &lt;link&gt;, &lt;base&gt;,... &lt;/head&gt;</h2>
    
    <h1>body</h1>
    <h2>&lt;p&gt;, &lt;pre&gt;, &lt;div&gt;,...</h2>
    
    To put language manual / langauge specifics <br />
    langauge function paramters and related functions <br />
    math functions / order-of-operation <br />
    </div>
    <div style="position: absolute; display: none; top: -160px; background-color: rgba(255, 255, 255, 0.6); border: 1px dashed #000; height: 160px; width: 100%; padding: 20px 10px; color: #000; text-align: left; z-index: -1;" class="text-sm">
      <div style="display: inline; float: left; background-color: #FFF; width: 50%; border: 1px dashed #000; ">
        <input type="checkbox" checked /> Interactive<br />
        <input type="checkbox" checked /> font-family: 'Courier New', Courier, monospace;
      </div>
      <div style="display: inline; float: right; text-align: right; width: 50%; border: 1px dashed #000; ">
      <div style="display: inline; float: left; width: 85%; text-align: right;">Text Zoom:</div>
      <div class="vert-slider-container" style="display: inline; float: right;">
        <input type="range" min="-2" max="2" value="0" step="1" class="vert-slider" id="mySlider">
      </div>
      </div>
    </div>

    <div style="position: absolute; top: -24px; background-color: #FFA6A6; border: 1px dashed #000; border-right: none; z-index: -1;"><button style="padding: 0 4px 4px 4px; font-weight: bold;" class="text-xs">[Settings...]</button></div>
        <div style="position: absolute; top: -24px; left: 73px; background-color: #FFA6A6; border: 1px dashed #000; border-left: none; z-index: -1;"><button style="border: 1px dashed #000; padding: 0 4px 2px 4px; font-weight: bold; color: black;" class="text-xs">Console</button> | <button style="border: 1px dashed #000; padding: 0 4px 2px 4px; border: 1px dashed #000; font-weight: bold;" class="text-xs">SQL Query</button> | <button style="border: 1px dashed #000; padding: 0 4px 2px 4px; font-weight: bold;" class="text-xs">PHP</button> | <button style="border: 1px dashed #000; padding: 0 4px 2px 4px; font-weight: bold;" class="text-xs">Perl</button> | <button style="border: 1px dashed #000; padding: 0 4px 2px 4px; font-weight: bold;" class="text-xs">Python</button> | <button style="border: 1px dashed #000; padding: 0 4px 2px 4px; font-weight: bold;" class="text-xs">JavaScript</button> | <button style="border: 1px dashed #000; padding: 0 4px 2px 4px; font-weight: bold;" class="text-xs">CSS</button> | <button style="border: 1px dashed #000; padding: 0 4px 2px 4px; font-weight: bold;" class="text-xs">HTML</button> </div>
    <div style="text-align: left; position: relative;">

        <div style="display: inline-block; margin: 5px 0px 0px 10px; float: left;">
            <button id="requestSubmit" type="submit" style="border: 1px dashed #FFF; padding: 2px 4px;">Run</button>&nbsp;

            <input list="commandHistory" id="requestInput" class="text-sm" style="font-family: 'Courier New', Courier, monospace;" type="text" size="46" name="requestInput" autocomplete="off" spellcheck="off" placeholder="php [-rn] &quot;echo 'hello world';&quot;" value="">
            <datalist id="commandHistory">
                <option value="Edge"></option>
            </datalist>

        </div>
        <div style="display: inline-block;">
        
        <div style="position: relative; display: inline-block; margin: 5px 10px 0px 0px; width: 210px; float: right;">
            <div style="float: right;">
                &nbsp;<button id="consoleAnykeyBind" class="text-xs" type="submit" style="border: 1px dashed #FFF; padding: 2px 2px;">Bind Any[key]</button>
                <input id="app_ace_editor-auto_bind_anykey" type="checkbox" name="auto_bind_anykey" checked="">
            </div>
            <div style="float: left;">
                <button id="consoleCls" class="text-xs" type="submit" style="border: 1px dashed #FFF; padding: 2px 2px;">Clear (auto)</button>
                <input id="app_console-auto_clear" type="checkbox" name="auto_clear" <?= ($auto_clear ? 'checked="" ' : '') ?> />&nbsp;
            </div>
        </div>

        </div>
        <button id="changePositionBtn" style="float: right; margin: 5px 10px 0 0;" type="submit">&#9650;</button>
        <textarea id="responseConsole" spellcheck="false" rows="10" cols="85" name="responseConsole" readonly=""><?php
//$errors->{'CONSOLE'}  = 'wtf';

//dd($errors);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  //var_dump($output['results']);
  if (!empty($output['command'])) // echo join("\n$shell_prompt", $output['command']) . "\n";
    foreach($output['command'] as $command) {
      if (!empty($output['results'])) {
        echo $shell_prompt . $command;
        foreach($output['results'] as $result) 
          foreach($result as $line) { echo $line . "\n"; }
      }
    }
  else echo $shell_prompt . "\n";

}

if (!empty($errors))
  foreach($errors as $key => $error) {
      if (!is_array($error))
      echo /*$key . '=>' . */$error . ($key != end($errors) ? '' : "\n");
      //else dd($error);
  } ?>
</textarea>
        
    </div>
</div>

<!-- </div> -->

<?php $appConsole['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

var slider = document.getElementById("mySlider");

slider.addEventListener("input", function() {

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

const consoleContainer = document.getElementById('app_console-container');
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

function show_console(event) {
    console.log('showing console...'); 

    const consoleContainer = document.getElementById('app_console-container');

    //requestInput.focus();
    
    if (typeof event !== 'undefined')
        if (event.key === '`' || event.keyCode === 192) // c||67
            if (document.activeElement !== requestInput) {
                // Replace the following line with your desired function
                // If it's currently absolute, change to fixed
                if (!isFixed)
                    requestInput.focus();
                event.preventDefault();
                show_console();
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
                } else {                }
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
    consoleContainer.style.bottom = '35px';
    consoleContainer.style.transform = 'translate(-50%, -50%)';
    consoleContainer.style.textAlign = 'center';
    consoleContainer.style.zIndex = '999';

    respCon.style.height = '60px';

    changePositionBtn.innerHTML = '&#9650;';

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
    consoleContainer.style.bottom = '32px';
    consoleContainer.style.transform = 'translate(-50%, -50%)';
    consoleContainer.style.zIndex = '999';

    respCon.style.height = '20%';

    changePositionBtn.innerHTML = '&#9660;';
  }

  }
  if (isFixed) isFixed = !isFixed;
  //isFixed = true;
  // Toggle the state for the next click
    //isFixed = !isFixed;
}

// Attach a focus event listener to the input element
    requestInput.addEventListener('focus', function() {
        // Check the condition before calling the show_console function
        //if (consoleContainer.style.position !== 'fixed')
        if (  document.getElementById('app_console-container').style.position != 'absolute') {
          if (isFixed)
            requestInput.focus();

          //show_console();
        }
        if (isFixed) {isFixed = !isFixed;}
        isFixed = true;
        show_console();
    });
<?php if (defined('COMPOSER')) { ?>
initSubmit.addEventListener('click', () => {
  show_console();
  const requestInput = document.getElementById('requestInput');
  const requestConsole = document.getElementById('requestConsole');
  const argv = requestInput.value;
  $.post("<?= APP_URL_BASE; /*$projectRoot*/?>",
  {
    cmd: argv
  },
  function(data, status) {
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

$(document).ready(function() {

  const autoClear = $("#app_console-auto_clear").checked;


  //$('#responseConsole').css('width', $(window).width() - 20 + 'px');
  
    $(".slide-toggle").click(function(){
      $(".box").animate({
        width: "toggle"
      });
    });
<?php if (defined('APP_PROJECT')) { ?>
  //getDirectory('<?=(isset($_GET['project']) && !empty($_GET['project']) ? basename(APP_PATH . APP_ROOT) : '') ?>', '<?=(isset($_GET['project'
]) && !empty($_GET['project']) ? '' : APP_PATH ) ?>');
  console.log('Path: <?=APP_PATH?>');
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
      //  str = 'Left Key pressed!';
      //  break;
      case 38:
        $('#requestInput').val('test up');
        break;
      //case 39:
      //  str = 'Right Key pressed!';
      //  break;
      case 40:
        $('#requestInput').val('test down');
        
        break;
      default:
        console.log('Key Code: ' + code);
        //show_console();
        break;
    }
  };


  $('#consoleCls').on('click', function() {
    console.log('Button Clicked!');
    $('#responseConsole').val('<?= $shell_prompt; ?>');
    if ($('#app_console-container').css('position') == 'absolute')
      show_console();
  });


  $('#changePositionBtn').on('click', function() {
    console.log('Drop Button Clicked!');
    show_console();
  });
    
  $("#app_git-help-cmd").click(function() {
    $('#requestInput').val('git help');
    $('#requestSubmit').click();
    console.log('wow');

    if (!isFixed) isFixed = true;
    show_console();
  });

  $("#app_git-add-cmd").click(function() {
    $('#requestInput').val('git add .');
    $('#requestSubmit').click();
    console.log('wow');
  });
  
  $("#app_git-remote-cmd").click(function() {
    $('#requestInput').val('git remote -v');
    $('#requestSubmit').click();
    console.log('wow');
  });

  $("#app_git-commit-cmd").click(function() {
    $('#requestInput').val('git commit -am "default message"');
    document.getElementById('app_git-commit-msg').style.display='block';

    if (!isFixed) isFixed = true;
    show_console();
    //$('#requestSubmit').click();
  });
  
  $("#app_git-clone-cmd").click(function() {
    $('#requestInput').val('git clone ');  <!--  I need to get the URL -->
    
    document.getElementById('app_git-clone-url').style.display='block';

    if (!isFixed) isFixed = true;
    show_console();
    //$('#requestSubmit').click();
  });

  document.getElementById('app_git-oauth-input').addEventListener("keydown", function(event) {
  if (event.keyCode === 13) {
    // Enter key was pressed
    console.log("Enter key pressed");

<?php
//dd(APP_PATH . APP_ROOT . '.git/config');

if (is_file(APP_PATH . APP_ROOT . '.git/config')) {

$config = parse_ini_file(APP_PATH . APP_ROOT . '.git/config', true);

if (isset($config['remote origin']['url']) && preg_match('/(?:[a-z]+\:\/\/)?([^\s]+@)?((?:[a-z0-9\-]+\.)+[a-z]{2,6}(?:\/\S*))/', $config['remote origin']['url'], $matches))
  if (count($matches) >= 2) { ?>

    $('#requestInput').val('git remote set-url origin https://' + $("#app_git-oauth-input").val() + '@<?= $matches[2] ?>');

<?php } else { ?>

    $('#requestInput').val('git remote set-url origin https://' + $("#app_git-oauth-input").val() + '@<?= $matches[1] ?>');

<?php } } ?>

    document.getElementById('app_git-clone-url').style.display='none';
    
    $('#requestSubmit').click();
  }

});

  document.getElementById('app_git-commit-input').addEventListener("keydown", function(event) {
  if (event.keyCode === 13) {
    // Enter key was pressed
    console.log("Enter key pressed");
    
    $('#requestInput').val('git commit -am "' + $("#app_git-commit-input").val() + '"');
    
    document.getElementById('app_git-commit-msg').style.display='none';
    
    $('#requestSubmit').click();
  }

});



  document.getElementById('app_git-clone-url').addEventListener("keydown", function(event) {
  if (event.keyCode === 13) {
    // Enter key was pressed
    console.log("Enter key pressed");
    
    $('#requestInput').val('git clone ' + $("#app_git-clone-url-input").val() + ' .');
    
    document.getElementById('app_git-clone-url').style.display='none';
    
    $('#requestSubmit').click();
  }
});
  
  
  $("#app_php-error-log").click(function() {
    $('#requestInput').val('wget <?= APP_WWW ?>?error_log=unlink'); // unlink
    //show_console();
    $('#requestSubmit').click();
  });

  $("#app_composer-init-submit").click(function() {
    const requestValue = $('#app_composer-init-input').val().replace(/\n/g, ' ');
    
    $('#requestInput').val(requestValue);
    $('#requestSubmit').click(); //show_console();
    if ($('#app_console-container').css('position') == 'absolute')
      $('#changePositionBtn').click();
    $('#requestInput').val('');
  });

  $('#requestSubmit').click(function() {
    let matches = null;
    const autoClear = document.getElementById('app_console-auto_clear').checked;
    console.log('autoClear is ' + autoClear);
    
    
    if (!isFixed) isFixed = true;
    show_console();

    
    if ($('#app_console-container').css('position') != 'absolute') {
      //window.isFixed = true;
      //if (!window.isFixed) window.isFixed = !window.isFixed;
      
    //if (!isFixed) isFixed = true;
    //show_console();
      //$('#changePositionBtn').click();
    }
    const argv = $('#requestInput').val();

    console.log('Argv: ' + argv);
    
    if (autoClear) $('#responseConsole').val('<?= $shell_prompt; ?>' + argv);
    
    if (argv == '') $('#responseConsole').val('<?= $shell_prompt; ?>' + "\n" + $('#responseConsole').val()) ; //  + 
    else if (matches = argv.match(/^(?:echo\s+)?(hello)\s+world/i)) { // argv == 'edit'
      if (matches) {
        $('#responseConsole').val(matches[1].charAt(0).toUpperCase() + matches[1].slice(1) + ' ' + 'Barry' + "\n" + '<?= $shell_prompt; ?>' + argv + "\n" + $('#responseConsole').val());
        return false;
      } else {
        console.log("Invalid input format.");
      }
    } else if (matches = argv.match(/^project/i)) { // argv == 'edit'
      if (matches) {
        document.getElementById('app_project-container').style.display='block';
        $('#responseConsole').val('Barry, here you can begin editing your project.' + "\n" + '<?= $shell_prompt; ?>' + argv + "\n" + $('#responseConsole').val());
        changePositionBtn.click();
        return false;
      } else {
        console.log("Invalid input format.");
      }
    } else if (matches = argv.match(/^(?:j(?:ava)?s(?:cript)?\s+)?(\S+)$/)) {
// Save the original console.log function
var originalLog = console.log;

// Create an array to store log messages
var logMessages = [];

  var js_prompt = 'javascript: ';
  var codeString = matches[1]; // "console.log('Hello, world!');";
  var myFunction = new Function(codeString);

  myFunction();
// Override console.log to capture messages
console.log = function() {
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

        window.location.href = '<?= APP_URL_BASE ?>?app=text_editor&path=' + dirname + '&file=' + filename;  // filename= + pathname
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

      $.post("<?= basename(__FILE__) . '?' . $_SERVER['QUERY_STRING']  ; /*APP_URL_BASE; $projectRoot*/?>",
      {
        cmd: argv
      },
      function(data, status) {
        console.log("Data: " + data + "Status: " + status);

        //data = data.trim(); // replace(/(\r\n|\n|\r)/gm, "")
        
        if (matches = data.match(/((:?sudo\s+)?(:?<?=str_replace('/', '\/', dirname(GIT_EXEC)); ?>)?<?= basename(GIT_EXEC); ?>.*)/gm)) {
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
              document.getElementById('app_git-container').style.display='block';
              document.getElementById('app_git-oauth').style.display='block';
              document.getElementById('app_git-clone-url').style.display='none';
              document.getElementById('app_git-commit-msg').style.display='none';
            } else if (matches = data.match(/.*push.*\n+To.*/gm)) {
              if (matches = data.match(/.*push.*\n+To.*\n.*!.*\[rejected\].+(\w+).+[->].+(\w+).\(fetch first\)/gm)) {
                $('#responseConsole').val('<?= $shell_prompt; ?>Push unsuccessful. Fetch first ' + "\n" + data + "\n" + $('#responseConsole').val());
                $('#requestInput').val('git fetch origin main');
                $('#requestSubmit').click();
                $('#requestInput').val('git merge origin/main');
                $('#requestSubmit').click();
                $('#requestInput').val('git commit');
                $('#requestSubmit').click();
                $('#requestInput').val('git push origin main');
                $('#requestSubmit').click();
              } else if (matches = data.match(/.*push.*\n+To.*\n.*!.*\[rejected\].+(\w+).+[->].+(\w+).\(non-fast-forward\)/gm)) {
                $('#responseConsole').val('<?= $shell_prompt; ?>Push unsuccessful. "non-fast-forward" error ' + "\n" + data + "\n" + $('#responseConsole').val());
                $('#requestInput').val('git push --force origin main');
                $('#requestSubmit').click();
              } else {
              $('#responseConsole').val('<?= $shell_prompt; ?>Push successful' + "\n" + data + "\n" + $('#responseConsole').val());
              }
            } else if (matches = data.match(/.*push.*\n+Error: Everything up-to-date/gm)) {
              $('#responseConsole').val('<?= $shell_prompt; ?>Everything up-to-date' + "\n" + data + "\n" + $('#responseConsole').val());
            }
          } else if (matches = data.match(/.*fetch.*\n+/gm)) {
            if (matches = data.match(/.*Error:.+From.+\n.+\* branch.+(\w+).+[->].+(\w+)/gm)) {
              $('#responseConsole').val('<?= $shell_prompt; ?>"non-fast-forward" error' + "\n" + data + "\n" + $('#responseConsole').val());
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
            if (matches = data.match(/.*Already up to date\./gm))
              $('#responseConsole').val('<?= $shell_prompt; ?>Already up to date.' + "\n" + data + "\n" + $('#responseConsole').val());
            else if (confirm('(Re)load Window?')) {
              // User clicked OK
              window.location.reload();  // window.location.href = window.location.href;
            }
          } else if (matches = data.match(/.*(:?<?=str_replace('/', '\/', dirname(GIT_EXEC)); ?>)?<?= basename(GIT_EXEC); ?>.*commit.*\n/gm)) {
            if (matches = data.match(/.*Error: Author identity unknown\./gm)) {
              $('#responseConsole').val('<?= $shell_prompt; ?>Author identity unknown' + "\n" + data + "\n" + $('#responseConsole').val());
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
            $('#responseConsole').val(data + "\n" + $('#responseConsole').val());
          }
        } else {
          $('#responseConsole').val(data + "\n" + $('#responseConsole').val());
        }
        //if (!autoClear) { $('#responseConsole').val("\n" + $('#responseConsole').val()); }
      
        //$('#requestInput').val('');
      
        $('#responseConsole').scrollTop = $('#responseConsole').scrollHeight;
      });
    }
  });
});
<?php $appConsole['script'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

<?php
// (check_http_200('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_WWW . 'resources/js/tailwindcss-3.3.5.js')?
// Path to the JavaScript file
$path = APP_PATH . APP_BASE['resources'] . 'js/tailwindcss-3.3.5.js';

// Create the directory if it doesn't exist
is_dir(dirname($path)) or mkdir(dirname($path), 0755, true);

// URL for the CDN
$url = 'https://cdn.tailwindcss.com';

// Check if the file exists and if it needs to be updated
if (!is_file($path) || (time() - filemtime($path)) > 5 * 24 * 60 * 60) { // ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path . 'tailwindcss-3.3.5.js'))))) / 86400)) <= 0 
  // Download the file from the CDN
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  $js = curl_exec($handle);
  
  // Check if the download was successful
  if (!empty($js)) {
    // Save the file
    file_put_contents($path, $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
  }
}
?>

  <script src="<?= check_http_200($url) ? substr($url, strpos($url, parse_url($url)['host']) + strlen(parse_url($url)['host'])) : substr($path, strpos($path, dirname(APP_BASE['resources'] . 'js'))) ?>"></script>     

<style type="text/tailwindcss">
<?= $appConsole['style']; ?>
</style>
</head>
<body>
<?= $appConsole['body']; ?>

  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="resources/js/jquery/jquery.min.js"></script> -->
<script>
<?= $appConsole['script']; ?>
</script>
</body>
</html>
<?php $appConsole['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'console' && APP_DEBUG)
  die($appConsole['html']);