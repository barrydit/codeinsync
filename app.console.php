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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['cmd'])) {
      if ($_POST['cmd'] && $_POST['cmd'] != '') 
        if (preg_match('/^install/i', $_POST['cmd']))
          include('templates/' . preg_split("/^install (\s*+)/i", $_POST['cmd'])[1] . '.php');
        //else if (preg_match('/^edit\s+(:?(.*))/i', $_POST['cmd'], $match))
          //exec($_POST['cmd'], $output);
          //die(header('Location: ' . APP_URL_BASE . '?app=text_editor&filename='.$_POST['cmd']));
        else if (preg_match('/^php\s+(:?(.*))/i', $_POST['cmd'], $match))
          exec($_POST['cmd'], $output);
        else if (preg_match('/^composer\s+(:?(.*))/i', $_POST['cmd'], $match)) {
          $output[] = 'sudo ' . COMPOSER_EXEC['bin'] . ' ' . $match[1];
$proc=proc_open('sudo ' . COMPOSER_EXEC['bin'] . ' ' . $match[1],
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
          } //else {
        
          $output[] = 'sudo ' . GIT_EXEC . ' ' . $match[1];
$proc=proc_open('sudo ' . GIT_EXEC . ' ' . $match[1],
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
  
            list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
          $output[] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : (preg_match('/^To\s' . DOMAIN_EXPR . '/', $stderr) ? $stderr : 'Error: ' . $stderr) ) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
          //$output[] = $_POST['cmd'];
          //}
  
/*
 Error: To https://github.com/barrydit/composer_app.git
   5fbad5b..29f689e  main -> main
   
^To\s(?:[a-z]+\:\/\/)?(?:[a-z0-9\\-]+\.)+[a-z]{2,6}(?:\/\S*)?
   
   
*/
  // 


        } else if (preg_match('/^npm\s+(:?(.*))/i', $_POST['cmd'], $match)) {
          $output[] = 'sudo ' . NPM_EXEC . ' ' . $match[1];
$proc=proc_open('sudo ' . NPM_EXEC . ' ' . $match[1],
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
          list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
          $output[] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
          //$output[] = $_POST['cmd'];

        }


          //exec($_POST['cmd'], $output);
        else {
          if (preg_match('/^(\w+)\s+(:?(.*))/i', $_POST['cmd'], $match))
            if (isset($match[1]) && in_array($match[1], ['tail', 'cat', 'echo', 'env', 'sudo'])) {
              //exec('sudo ' . $match[1] . ' ' . $match[2], $output); // $output[] = var_dump($match);
              
$output[] = 'sudo ' . $match[1] . ' ' . $match[2];
$proc=proc_open('sudo ' . $match[1] . ' ' . $match[2],
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
.app_console-container {
position: fixed;
bottom: 62px;
left: 50%;
transform: translateX(-50%);
width: auto;
height: 45px;
background-color: #FFA6A6; /* rgba(255, 0, 0, 0.35) */
color: white;
text-align: center;
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

<?php $appConsole['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->

<div id="myDiv" class="app_console-container">
    <div style="text-align: left; position: relative;">
    
        <div style="display: inline-block; margin: 5px; ">
            <button id="requestSubmit" type="submit">Run</button>&nbsp;&nbsp;

            <input list="commandHistory" id="requestInput" type="text" size="54" name="requestInput" autocomplete="off" spellcheck="off" placeholder="php -r &quot;echo 'hello world';&quot;" value="">
            <datalist id="commandHistory">
                <option value="Edge"></option>
            </datalist>

        </div>
        <div style="display: inline-block;">
        
        <div style="position: relative; display: inline-block; margin: 5px 10px 0px 0px; width: 175px; float: right;">
            <div style="float: right;">
                <button id="consoleAnykeyBind" class="text-xs" type="submit">Bind Any[key] </button>
                <input id="app_text_editor-auto_bind_anykey" type="checkbox" name="auto_bind_anykey" checked="">
            </div>
            <div style="float: left;">
                <button id="consoleCls" class="text-xs" type="submit">Clear (auto)</button>
                <input id="app_text_editor-auto_clear" type="checkbox" name="auto_clear" checked="">
            </div>
        </div>
        </div>
        <button id="changePositionBtn" style="float: right; margin: 5px 10px 0 0;" type="submit">&#9650;</button>
        <textarea id="responseConsole" spellcheck="false" rows="10" cols="85" name="responseConsole"><?php
//$errors->{'CONSOLE'}  = 'wtf';

//dd($errors);

if (!empty($errors))
  foreach($errors as $key => $error) {
      echo /*$key . '=>' . */$error . ($key != end($errors) ? '' : "\n");
  }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  //var_dump($output['results']);
  if (!empty($output['command'])) // echo join("\n$ > ", $output['command']) . "\n";
    foreach($output['command'] as $command) {
      if (!empty($output['results'])) {
        echo '$ > ' . $command . "\n";
        foreach($output['results'] as $result) 
          foreach($result as $line) { echo $line . "\n"; }
      }
    }
  else echo '$ > ';

}?></textarea>
        
    </div>
</div>

<!-- </div> -->

<?php $appConsole['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

const myDiv = document.getElementById('myDiv');
//const reqInp = document.getElementById('requestInput');
const respCon = document.getElementById('responseConsole');
    
const styles = window.getComputedStyle(myDiv);
const changePositionBtn = document.getElementById('changePositionBtn');

const initSubmit = document.getElementById('app_composer-init-submit');
let isFixed = false; // Store the current position state

var requestInput = document.getElementById('requestInput');

changePositionBtn.addEventListener('click', () => {
  if (myDiv.style.position == 'fixed') {
      isFixed = !isFixed;
      changePositionBtn.innerHTML = '&#9650;';
  } else {
      isFixed = true;
      changePositionBtn.innerHTML = '&#9660;';
  }
  show_console();
});

function show_console(event) {
      
    const myDiv = document.getElementById('myDiv');

    //requestInput.focus();
    
    if (typeof event !== 'undefined')
        if (event.key === '`' || event.keyCode === 192) // c||67
            if (document.activeElement !== requestInput) {
                // Replace the following line with your desired function
                // If it's currently absolute, change to fixed
                
                if (!isFixed)
                    requestInput.focus();
                event.preventDefault();
                isFixed = true; 
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
                }
                event.preventDefault();
            } else {
                document.activeElement = null;
                return false;
            }
        }
  //isFixed = !isFixed; 
    
  if (typeof isFixed === 'undefined') {
    //if (event !== undefined)
    console.log('isFixed is undefined');
  } else {
  if (isFixed) {

    // If it's currently fixed, change back to absolute
    myDiv.style.position = 'absolute';
    myDiv.style.top = '';
    myDiv.style.left = '50%';
    myDiv.style.right = '';
    myDiv.style.bottom = '30px';
    myDiv.style.transform = 'translate(-50%, -50%)';
    myDiv.style.textAlign = 'center';
    myDiv.style.zIndex = '999';

    respCon.style.height = '60px';

    changePositionBtn.innerHTML = '&#9650;';

/*
    myDiv.style.marginLeft = 'auto';
    myDiv.style.marginRight = 'auto';
    myDiv.style.textAlign = 'center';
    myDiv.style.transform = 'none';
*/  
  } else {

    // If it's currently absolute, change to fixed
    myDiv.style.position = 'fixed';
    myDiv.style.top = '35%'; // Set the fixed position as needed
    myDiv.style.left = '50%';
    myDiv.style.bottom = '32px';
    myDiv.style.transform = 'translate(-50%, -50%)';
    myDiv.style.zIndex = '999';

    respCon.style.height = '20%';

    changePositionBtn.innerHTML = '&#9660;';
  }
  }
  // Toggle the state for the next click
    isFixed = !isFixed;
}

// Attach a focus event listener to the input element
    requestInput.addEventListener('focus', function() {
        // Check the condition before calling the show_console function
        //if (myDiv.style.position !== 'fixed')
        if (  document.getElementById('app_console-container').style.position != 'absolute') {
          console.log('test 123');
          if (isFixed)
            requestInput.focus();
          show_console();
        } else {
        if (isFixed) isFixed = !isFixed;
        isFixed = true;
            show_console();
        }
    });

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
      requestConsole.value = '$ > ' + argv + " \n" + data;
    
      requestConsole.scrollTop = requestConsole.scrollHeight;
      console.log('changed scroll');
    }
  }
  
  );
});

window.addEventListener('resize', () => {
  // document.getElementById('responseConsole').style.width = window.innerWidth - 15 + 'px';
 });


//requestInput.addEventListener('focus', (myDiv.style.position == 'absolute' ? null : show_console()));

$(document).ready(function() {

  const autoClear = $("#app_text_editor-auto_clear").checked;


  //$('#responseConsole').css('width', $(window).width() - 20 + 'px');
  
    $(".slide-toggle").click(function(){
      $(".box").animate({
        width: "toggle"
      });
    });
<?php if (defined('APP_PROJECT')) { ?>
  //getDirectory('<?=(isset($_GET['project']) && !empty($_GET['project']) ? $projectRoot : '')?>', '<?=(isset($_GET['project'
]) && !empty($_GET['project']) ? '' : $projectPath ) ?>');
  console.log('Path: <?=$projectPath?>');
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
        break;
    }
  };


  $('#consoleCls').on('click', function() {
    console.log('Button Clicked!');
    $('#responseConsole').val('$ >');
    if ($('#myDiv').css('position') == 'absolute')
      show_console();
  });


  $('#changePositionBtn').on('click', function() {
    console.log('Button Clicked!');
    show_console();
  });
  
  $("#app_git-add-cmd").click(function() {
    $('#requestInput').val('git add .');
    $('#requestSubmit').click();
    console.log('wow');
  });

  $("#app_git-commit-cmd").click(function() {
    $('#requestInput').val('git commit -am "default message"');
    show_console();
    //$('#requestSubmit').click();
  });
  
  
  $("#app_composer-init-submit").click(function() {
    const requestValue = $('#app_composer-init-input').val().replace(/\n/g, ' ');
    
    $('#requestInput').val(requestValue);
    $('#requestSubmit').click(); //show_console();
    if ($('#myDiv').css('position') == 'absolute')
      $('#changePositionBtn').click();
    $('#requestInput').val('');
  });

  $('#requestSubmit').click(function() {
    let matches = null;
    const autoClear = document.getElementById('app_text_editor-auto_clear').checked;
    console.log('autoClear is ' + autoClear);
    
    if ($('#myDiv').css('position') == 'absolute') {
      show_console();
      //$('#changePositionBtn').click();
    }
    const argv = $('#requestInput').val();
    
    console.log('Argv: ' + argv);
    
    if (autoClear) $('#responseConsole').val('$ > ' + argv);
    if (argv == 'cls') $('#responseConsole').val('$ >');
    
    if (matches = argv.match(/^echo\s+(hello)\sworld$/i))  { // argv == 'edit'
      if (matches) {
        const pathname = matches[1]; // "/path/to/file.txt"
        $('#responseConsole').val('$ >' + argv + "\n" + matches[1] + ' ' + 'Barry');
        return false;
      } else {
        console.log("Invalid input format.");
      }
    }
    
    
    if (matches = argv.match(/^edit\s+(\S+)$/))  { // argv == 'edit'
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
    }

    else if (argv == 'clear') $('#responseConsole').val('clear');
    else if (argv == 'reset') $('#responseConsole').val('>_');
    else
    $.post("<?= basename(APP_SELF); /*APP_URL_BASE; $projectRoot*/?>",
    {
      cmd: argv
    },
    function(data, status) {
      console.log("Data: " + data + "Status: " + status);
      
      //data = data.trim(); // replace(/(\r\n|\n|\r)/gm, "")

      if (autoClear) {
        $('#responseConsole').val(data + argv);
        $('#responseConsole').val('$ > ' + argv + " \n" + data );
      } else {
        $('#responseConsole').val('$ > ' + argv + "\n" + data + $('#responseConsole').val()) ; //  + 
      }
      $('#requestInput').val('');
      
      $('#responseConsole').scrollTop = $('#responseConsole').scrollHeight;
    });
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
?>

  <script src="<?= 'resources/js/tailwindcss-3.3.5.js' ?? $url ?>"></script>

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
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'git' && APP_DEBUG)
  die($appConsole['html']);