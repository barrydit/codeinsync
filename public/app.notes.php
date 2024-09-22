<?php

if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"])) {
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    if (is_file($path = realpath('../config/config.php'))) {
      require_once $path;
    }
  } elseif (is_file($path = realpath('config/config.php'))) {
    require_once $path;
  } else {
    die(var_dump("Path was not found. file=$path"));
  }
} 

if (preg_match('/^app\.([\w\-.]+)\.php$/', basename(__FILE__), $matches))
  ${$matches[1]} = $matches[1];

/*
if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../console_app.php') ? '../console_app.php' : (is_file('../config/console_app.php') ? '../config/console_app.php' : 'console_app.php'))
    : (is_file('console_app.php') ? 'console_app.php' : (is_file('public/console_app.php') ? 'public/console_app.php' : null))) require_once $path; 
else die(var_dump($path . ' path was not found. file=console_app.php'));
*/
//if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  //if (isset($_GET['app']) && $_GET['app'] == 'php')
  //  if (isset($_POST['path']) && isset($_GET['filename']) && $path = realpath($_POST['path'] . $_GET['filename']))
  //    file_put_contents($path, $_POST['editor']);
      
  //dd($_POST);

//  if (isset($_GET['filename'])) {
//    file_put_contents($projectRoot.(!$_POST['path'] ? '' : DIRECTORY_SEPARATOR.$_POST['path']).DIRECTORY_SEPARATOR.$_POST['filename'], $_POST['editor']);
//  }

/*
    if (isset($_POST['cmd'])) {
      if ($_POST['cmd'] && $_POST['cmd'] != '') 
        if (preg_match('/^install/i', $_POST['cmd']))
          include('templates/' . preg_split("/^install (\s*+)/i", $_POST['cmd'])[1] . '.php');
        else if (preg_match('/^php(:?(.*))/i', $_POST['cmd'], $match))
          exec($_POST['cmd'], $output);
        else if (preg_match('/^composer(:?(.*))/i', $_POST['cmd'], $match)) {
        $output[] = 'env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . APP_SUDO . COMPOSER_EXEC . ' ' . $match[1];
$proc=proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; ' . APP_SUDO . COMPOSER_EXEC . ' ' . $match[1],
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
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
list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
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
//}

$data = (!file_exists($path = APP_PATH . APP_BASE['var'] . 'notes.json')) ? json_decode(<<<'JSON'
[{
  "language":"PHP",
  "category":"String Manipulation",
  "snippets":[{
    "title":"Example PHP Code Snippet",
    "description":"This is an example code snippet in PHP.",
    "version":"1.0.0",
    "tags":["php","string","manipulation"],
    "stackoverflow":{"url":"https:\/\/stackoverflow.com\/questions\/12345678","title":""},
    "code":"&lt;?php\r\n\r\n\/\/ Your PHP code snippet goes here\r\n\r\n?&gt;"
  }]
}]
JSON
  ,
  true
) : json_decode(file_get_contents($path), true);

$categories = ['String Manipulation', 'Array Manipulation', 'Regular Expressions', 'Error Handling', 'File Handling', 'Database Operations', 'Form Handling', 'Date and Time', 'Image Manipulation', 'Email Handling', 'Encryption and Security', 'API Integration', 'Performance Optimization', 'Session Management', 'Authentication and Authorization', 'File Upload and Download', 'Templating', 'Caching', 'Logging and Debugging', 'Web Scraping', 'PDF Generation', 'XML and JSON Manipulation', '(CLI) Applications', 'Web Services and RESTful APIs', 'Internationalization and Localization', 'Error Reporting and Logging', 'HTML and Markup Generation', 'Server-Side Rendering', 'Image Processing and Manipulation', 'Data Validation and Sanitization', 'Networking and HTTP Requests', 'Templating Engines', 'Testing and Test Frameworks'];

$tags = [0 => ['Concatenation', 'Substring', 'Replace', 'Split', 'Trim', 'Search', 'Case Conversion', 'Regular Expressions', 'Character Encoding', 'Length/Size', 'String Comparison', 'Format/Parse', 'Join', 'Padding', 'Reverse', 'Tokenize', 'Escape/Unescape', 'Format Specifiers', 'Palindrome', 'Anagram'], 1 => ['Indexing', 'Iteration/Traversal', 'Adding/Removing Elements', 'Concatenation', 'Copying/Cloning', 'Sorting', 'Filtering/Selecting Elements', 'Mapping/Transforming Elements', 'Searching', 'Joining Arrays', 'Splitting Arrays', 'Reversing', 'Slicing', 'Merging Arrays', 'Finding Min/Max Elements', 'Finding Duplicates', 'Finding Unique Elements', 'Shuffling/Randomizing', 'Flattening Nested Arrays', 'Combining Arrays'], 2 => ['Pattern Matching', 'Regex Syntax', 'Metacharacters', 'Quantifiers', 'Anchors', 'Character Classes', 'Alternation', 'Grouping and Capturing', 'Escape Sequences', 'Assertions', 'Modifiers', 'Greedy vs. Non-Greedy', 'Backreferences', 'Lookahead and Lookbehind', 'Boundary Matchers', 'Unicode Characters in Regex', 'Matching HTML/XML Tags', 'Validating Email Addresses', 'Validating Dates', 'Password Validation'], 3 => []];

foreach($data as $key => $sample) {
  if (!in_array($sample['category'], $categories))
    $categories[] = $sample['category'];
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $id = explode('-', !isset($_GET['id']) ? '0-0' : $_GET['id']);

  if (isset($_GET['id'])) {
    header('Content-type: application/json');
    $json = [];
    foreach($data as $key1 => $sample) {
      if (isset($id[0]) && $id[0] == $key1) {
        $json['language'] = $sample['language'];
        $json['category'] = $sample['category'];

        foreach ($sample['snippets'] as $key2 => $snippet) {
          if (isset($id[1]) && $id[1] == $key2) {
            $json['snippets'] = $snippet;
            break 1;
          } elseif (!isset($id[1])) {
            $json['snippets'] = $sample['snippets'];
            break 1;
          }
        }
      }
      continue;
    }

    $json = array_map("unserialize", array_unique(array_map("serialize", $json)));

    exit(json_encode($json));
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = explode('-', !isset($_POST['id']) ? '0-0' : $_POST['id']);
  if (count($id) == 2)
    $id = [0 => (int) $id[0], 1 => (int) $id[1]];
  elseif (count($id) <= 1)
    $id = [0 => (int) $id[0]];
    
  if (isset($_POST['n_id'])) {
    $new_id = explode('-', $_POST['n_id']);
    $new_id = [0 => (int) $new_id[0], 1 => (int) $new_id[1]];

    if (!empty($new_id)) {
      $snippet = $data[$id[0]]['snippets'][$new_id[1]];
      $data[$id[0]]['snippets'][$new_id[1]] = $data[$id[0]]['snippets'][$id[1]];
      $data[$id[0]]['snippets'][$id[1]] = $snippet;
      file_put_contents($path, json_encode($data), LOCK_EX);
    }
  }

  if (isset($_POST['inputval'])) {
    $code = mb_convert_encoding(
      htmlspecialchars(
        html_entity_decode($_POST['inputval'], ENT_QUOTES, 'UTF-8'), 
        ENT_QUOTES, 'UTF-8'
      ), 'HTML-ENTITIES', 'utf-8'
    );
    
    /* $decode = mb_convert_encoding(
      htmlentities(
        htmlspecialchars_decode($code, ENT_QUOTES),
        ENT_QUOTES, 'UTF-8'),
      'UTF-8', 'HTML-ENTITIES'
    ); */

    //$data[$id[0]]['language'] = $_POST['language'];
    //$data[$id[0]]['category'] = $_POST['category'];

    $snippets = $data[$id[0]]['snippets'];
    $counter = 0;

    switch (count($id)) {
      case 2:
        $data[$id[0]]['snippets'][$id[1]] = ['title' => $_POST['title'], 'description' => $_POST['description'], 'stackoverflow' => ['url' => $_POST['so-url'], 'title' => ''], 'code' => $code];
        break;
      default:
        foreach ($snippets as $snippet) {
          if ($counter == 0) {
            $data[$id[0]]['snippets'] = [];
            $data[$id[0]]['snippets'][] = ['title' => $_POST['title'], 'description' => $_POST['description'], 'stackoverflow' => ['url' => $_POST['so-url'], 'title' => ''], 'code' => $code];
          }
          $data[$id[0]]['snippets'][] = $snippet;
          $counter += 1;
        }
        break;
    }
    file_put_contents($path, json_encode($data), LOCK_EX);
  }
}

ob_start(); ?>

/* Styles for the absolute div */
#app_notes-container {
position: absolute;
display: none;
top: 5%;
//bottom: 60px;
left: 50%;
transform: translateX(-50%);
width: auto;
height: 500px;
background-color: rgba(255, 255, 255, 0.9);
color: black;
text-align: center;
padding: 10px;
z-index: 1;
}

<?php $app[$notes]['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
  <div id="app_notes-container" class="<?= (APP_SELF == __FILE__ || (isset($_GET['app']) && $_GET['app'] == 'notes') ? 'selected' : '') ?>" style="border: 1px solid #000;">
    <div class="header ui-widget-header">
      <div style="display: inline-block;">Notes</div>
      <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_notes-container').style.display='none';">X</a>]</div> 
    </div>

      <div style="display: inline-block; width: auto;">
        <iframe src="<?= (is_dir($path = APP_PATH . APP_BASE['public']) && getcwd() == realpath($path) ?  APP_BASE['public']:'' ) . basename(__FILE__) ?>" style="height: 460px; width: 800px;"></iframe>
      </div>
      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->
  </div>
<!-- </div> -->

<?php $app[$notes]['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>



<?php $app[$notes]['script'] = ob_get_contents();
ob_end_clean();

//dd($_SERVER);
ob_start(); ?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link rel="stylesheet"
      href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.2.0/styles/a11y-dark.min.css">

  <!-- href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.2.0/styles/default.min.css" -->

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

<?php
// (check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/tailwindcss-3.3.5.js')?
is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/') or mkdir($path, 0755, true);
if (is_file("{$path}tailwindcss-3.3.5.js")) {
  if (ceil(abs(strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime('+5 days', filemtime("{$path}tailwindcss-3.3.5.js")))) / 86400) <= 0 )) {
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
?>

  <script src="<?= 'resources/js/tailwindcss-3.3.5.js' ?? $url ?>"></script>
  <!-- <style type="text/tailwindcss"> -->
  
  <style type="text/tailwindcss">
* { margin: 0; padding: 0; } /* to remove the top and left whitespace */

html, body { width: 100%; height: 100%; <?= ($_SERVER['SCRIPT_FILENAME'] == __FILE__ ? 'overflow:hidden;' : '') ?> } /* just to be sure these are full screen*/

pre {
  background: #eee;
  padding-bottom: 1em;
}
</style>

<style>
<?= /*$appWhiteboard['style'];*/ NULL; ?>

button.move_up::before {
  content: "\25b2";
  display: block;
  width: 16px;
  height: 16px;
  border: none;
}

button.move_down::before {
  content: "\25bc";
  display: block;
  width: 16px;
  height: 16px;
  border: none;
}

p#open_add_new {
  font-weight: bold;
  cursor: pointer;
}

input, textarea {
  border: 1px solid #000;
}
</style>
</head>
<body>
  <div style="position: relative;">
    <div id="add_new" style="position: fixed; display: none; top: 25px; left: 50%; transform: translateX(-50%); width: 725px; padding: 5px; border: 1px solid #000; background-color: #fff; z-index: 1;">
      <form action method="POST">
        <input id="id" type="hidden" name="id" value="<?= $id[0] . '-' . $id[1] ?>" />
        <div style=" width: 725px;">
          <div style="display: inline-block;">Category: <!-- use datalist instead -->
            <select id="category" name="category">
<?php foreach ($categories as $category) { ?>
              <option value="<?= $category; ?>"><?= $category; ?></option>
<?php } ?>
            </select><br />
            <label for="title">Title: </label><input id="title" type="text" style="width: 390px;" name="title" value="" /><br />
            <label for="so-url">Stackoverflow: </label><input id="so-url" type="text" name="so-url" style="width: 300px;" value="" /><br />
          </div>

          <div style="display: inline-block; text-align: right; float: right; clear: both;">[<a href="#" id="close_add_new">X</a>]<br />
            <input type="submit" value="Update/Save" /><br />
      Language:
            <select id="language" name="language">
<?php foreach (array('bash' => 'BASH', 'diff' => 'Diff', 'javascript' => 'JavaScript', 'php' => 'PHP', 'python' => 'Python', 'sql' => 'SQL', 'xml' => 'XML/HTML') as $key => $language) { ?>
              <option value="language-<?= $key ?>"><?= $language; ?></option>
<?php } ?>
            </select><br />
          </div>
        </div><br />
        <label for="description">Description: </label><br />
        <textarea id="description" rows="5" style="width: 100%;" <?= /* cols="100" */ NULL; ?> name="description" spellcheck="false"></textarea><br />
        <label for="inputval">Source Code: </label><br />
        <textarea id="inputval" rows="15" style="width: 100%;" <?= /* cols="100" */ NULL; ?>  name="inputval" spellcheck="false"></textarea><br />
      </form>

    </div>
  </div>
  <p id="open_add_new"><img src="resources/images/notes-edit.png" style="margin-left: 4px; padding-top: 5px; cursor: pointer; " height="50" width="42" />Add New Snippet</p>
<?php
foreach($data as $key1 => $sample) {
  $counter = 0;
  foreach ($sample['snippets'] as $key2 => $snippet) {
?>
  <h3><?= $snippet['title']; ?></h3>
  <a href="<?= $snippet['stackoverflow']['url']; ?>"><?= !empty($snippet['stackoverflow']['title']) ? $snippet['stackoverflow']['title'] : 'Stackoverflow Question/Answer' ?></a>
  <form action method="POST">
    <img class="open_edit" src="resources/images/notes-edit.png" style="display: inline-block;margin-left: 4px; padding-top: 5px; cursor: pointer; " height="25" width="21" onclick="document.getElementById('id').value = '<?= $key1 . '-' . $key2; ?>';" />  
    <input type="hidden" name="id" value="<?= $key1 . '-' . $key2; ?>" />
    <div style="display: inline-block; font-size: 18px; margin-top: 0px; padding-bottom: 5px;">
      <button type="submit" class="move_up" name="n_id" value="<?= $key1 . '-' . (empty($key2) ? $key2 : $key2 - 1 ); ?>" style="<?= ($counter == 0 ? 'visibility: hidden;' : '' ) /* NULL; */ ?>" onchange="this.form.submit();"></button>
      <button type="submit" class="move_down" name="n_id" value="<?= $key1 . '-' . $key2 + 1; ?>" style="<?= ($counter == count( $sample['snippets'] ) - 1 ? 'visibility: hidden;' : '' ) /* NULL; */ ?>" onchange="this.form.submit();"></button>
    </div>
  <pre style="margin: 0px;"><code class="language-<?= $sample['language']; ?>"><?= $snippet['code']; ?></code>
<?= $snippet['description']; ?>
  </pre>
  </form>
  <div style=" margin-left: 15px;">

  </div>
<?php $counter += 1; } } ?>
  

  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="../resources/js/jquery/jquery.min.js"></script> -->

<?php
// (check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/highlight.min.js"')?
is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/highlight.js/') or mkdir($path, 0755, true);
if (is_file($path . 'highlight.min.js')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path . 'highlight.min.js'))))) / 86400)) <= 0 ) {
    $url = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/highlight.min.js';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($js = curl_exec($handle))) 
      file_put_contents($path . 'highlight.min.js', $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
  }
} else {
  $url = 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/highlight.min.js';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($js = curl_exec($handle))) 
    file_put_contents($path . 'highlight.min.js', $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
}
?>

  <script src="<?= 'resources/js/highlight.js/highlight.min.js' ?? $url ?>"></script>

<script>
<?= /* $appWhiteboard['script']; */ NULL; ?>

hljs.highlightAll();
$(document).ready(function(){
  $('#open_add_new').click(function() {
    val = $('#id').val('0');
    console.log($('#id').val());
    if ($( "#add_new" ).css('display') == 'none') {
      $( '#add_new' ).slideDown( "slow", function() {
      // Animation complete.
      });
    } else {
      $( '#add_new' ).slideUp( "slow", function() {
        // Animation complete.
      });
    }
      
  });  
  $('.open_edit').click(function() {
    console.log($('#id').val());
    if ($( "#add_new" ).css('display') == 'none') {
      $( '#add_new' ).slideDown( "slow", function() {
        // Animation complete.
        var val, url;
        val = $('#id').val();
        url = 'http://localhost/composer-src/app.notes.php?id=' + val;
        if (val != '')
          $.getJSON(url, function(data) {
            //populate the packages datalist
            $("select[name='language'] option[value='language-" + $(data)[0].language.toLowerCase() + "']").attr('selected','selected');
            $("select[name='category'] option[value='" + $(data)[0].category + "']").attr('selected','selected');
            $('#title').val($(data.snippets)[0].title);
            $('#so-url').val($(data.snippets)[0].stackoverflow['url']);
            $('#description').val($(data.snippets)[0].description);
            $('#inputval').html($(data.snippets)[0].code).text();
          });
      });
    } else {
      $( '#add_new' ).slideUp( "slow", function() {
        // Animation complete.
      });
    }
  });
  $('#close_add_new').click(function() {
    console.log($('#id').val());
    $( '#add_new' ).slideUp( "slow", function() {
      // Animation complete.
    });
  });
});
  </script>

</body>
</html>
<?php $app[$notes]['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'php' && APP_DEBUG)
  die($app[$notes]['html']);

