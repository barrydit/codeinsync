<?php

$app_id = 'tools/code/git';
$container_id = str_replace(['/', '-'], '_', $app_id) . '-container';
$selector = '#' . ($container_id ?? 'app_git-container');

$UI_APP = [
  'style' => '',
  'body' => '',
  'script' => '',
];

// Capture styles
//ob_start();

$UI_APP['style'] = <<<CSS
<style>
  $selector {
    display: none;
    position: fixed;
    top: 20%;
    left: 40%;
    margin-left: -212px;
    margin-top: -153px;
  }

  $selector.selected {
    display: block;
    z-index: 1;
  }

  .btn {
    @apply rounded-md px-2 py-1 text-center font-medium text-slate-900 shadow-sm ring-1 ring-slate-900/10 hover:bg-slate-50;
  }

  .git-menu {
    cursor: pointer;
  }
</style>
CSS;

//$UI_APP['style'] = ob_get_clean();

// Capture body
ob_start(); ?>
<div id="<?= $container_id ?>" class="selected" data-app="<?= $app_id ?>">
  <div class="<?= $container_id ?>-frame-container selected">
    <div id="<?= $container_id ?>-frameMenu" class="git-menu">Git Menu Placeholder</div>
    <div id="<?= $container_id ?>-frameStatus">Status Output</div>
    <!-- Add other frames as needed -->
  </div>
</div>
<?php
$UI_APP['body'] = ob_get_clean();

// Capture script
ob_start();
?>
<script>
  document.querySelectorAll('.git-menu').forEach(btn => {
    btn.addEventListener('click', () => {
      alert('Git menu clicked');
    });
  });
</script>
<?php
$UI_APP['script'] = ob_get_clean();