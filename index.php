<?php if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null)))
    require_once($path); 
else die(var_dump($path . ' was not found. file=config.php'));

//die(basename(getcwd()) . ' ==' . 'public');

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('composer_app.php') ? 'composer_app.php' : (is_file('../composer_app.php') ? '../composer_app.php' : (is_file('../config/composer_app.php') ? '../config/composer_app.php' : NULL)))
    : (is_file('../composer_app.php') ? '../composer_app.php' : (is_file('public/composer_app.php') ? 'public/composer_app.php' : (is_file('config/composer_app.php') ? 'config/composer_app.php' : 'composer_app.php'))))
  require_once($path); 
else die(var_dump($path . ' was not found. file=composer_app.php'));

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('git_app.php') ? 'git_app.php' : (is_file('../git_app.php') ? '../git_app.php' : (is_file('../config/git_app.php') ? '../config/git_app.php' : NULL)))
    : (is_file('../git_app.php') ? '../git_app.php' : (is_file('public/git_app.php') ? 'public/git_app.php' : (is_file('config/git_app.php') ? 'config/git_app.php' : 'git_app.php'))))
  require_once($path); 
else die(var_dump($path . ' was not found. file=git_app.php'));

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('text_editor_app.php') ? 'text_editor_app.php' : (is_file('../text_editor_app.php') ? '../text_editor_app.php' : (is_file('../config/text_editor_app.php') ? '../config/text_editor_app.php' : NULL)))
    : (is_file('../text_editor_app.php') ? '../text_editor_app.php' : (is_file('public/text_editor_app.php') ? 'public/text_editor_app.php' : (is_file('config/text_editor_app.php') ? 'config/text_editor_app.php' : 'text_editor_app.php'))))
  require_once($path); 
else die(var_dump($path . ' was not found. file=text_editor_app.php'));

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('console_app.php') ? 'console_app.php' : (is_file('../console_app.php') ? '../console_app.php' : (is_file('../config/console_app.php') ? '../config/console_app.php' : 'public/console_app.php')))
    : (is_file('../console_app.php') ? '../console_app.php' : (is_file('public/console_app.php') ? 'public/console_app.php' : (is_file('config/console_app.php') ? 'config/console_app.php' : 'console_app.php'))))
  require_once($path); 
else die(var_dump($path . ' was not found. file=console_app.php'));
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

<script src="https://cdn.tailwindcss.com"></script>

<style type="text/tailwindcss">
<?= $appComposer['style']; ?>

<?= $appGit['style']; ?>

<?= $appConsole['style']; ?>

<?= $appTextEditor['style']; ?>

</style>
</head>
<body>

  <div class="absolute" style="position: absolute; margin-left: auto; margin-right: auto; left: 0; right: 0; text-align: center; background-color: rgba(255, 255, 255, 0.8); border: 1px solid #000; width: 700px; z-index: 1;">
    <div style="position: relative; margin: 0px auto; width: 700px;">
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('gitHubForm').style.display='block';"><img src="resources/images/github_icon.png" width="72" height="23"></a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('app_git-container').style.display='block';"><img src="resources/images/git_icon.png" width="58" height="24"></a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('app_composer-container').style.display='block';"><img src="resources/images/composer_icon.png" width="31" height="40"> Composer</a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('phpForm').style.display='block';"><img src="resources/images/php_icon.png" width="40" height="27"> PHP <?= (preg_match("#^(\d+\.\d+)#", PHP_VERSION, $match) ? $match[1] : '8.0' ) ?></a> |
      <a href="https://packagist.org/"><img src="resources/images/packagist_icon.png" width="30" height="34"> Packagist</a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '')?>" onclick="document.getElementById('app_text_editor-container').style.display='block';"><img src="resources/images/code_icon.png" width="32" height="32"> Text</a> |
      <a href="<?= (APP_URL['query'] != '' ? '?'.APP_URL['query'] : '') . (APP_ENV == 'development' ? '#!' : '') ?>" onclick="document.getElementById('httpHeaderForm').style.display='block';"><img src="resources/images/http_icon.jpg" width="32" height="32"> HTTP</a>
    </div>
  </div>

<?= $appComposer['body']; ?>

<?= $appGit['body']; ?>

<?= $appConsole['body']; ?>

<?= $appTextEditor['body']; ?>


<!-- For Text / Ace Editor -->
  <script src="resources/js/ace/ace.js" type="text/javascript" charset="utf-8"></script>
<!--  <script src="resources/js/ace/ext-language_tools.js" type="text/javascript" charset="utf-8"></script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>

  <script src="resources/js/ace/mode-php.js" type="text/javascript" charset="utf-8"></script>
<!-- End: For Text / Ace Editor -->

  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="resources/js/jquery/jquery.min.js"></script> -->
<script>

<?= $appConsole['script']; ?>

// Define the function to be executed when "c" key is pressed
function executeFunctionOnKeyPress(event) {
  // Check if the pressed key is "c" (you can use event.key or event.keyCode)

}

// Attach the event listener to the window object
window.addEventListener('keydown', show_console);


<?= $appComposer['script']; ?>

<?= $appGit['script']; ?>

<?= $appTextEditor['script']; ?>
</script>
</body>
</html>