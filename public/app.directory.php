<?php

global $errors;

if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"]))
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    chdir('../');
    if ($path = realpath('bootstrap.php')) { // is_file()
      //dd('does this do anything?');
      require_once $path;
    }
  } else
    die(var_dump("Path was not found. file=$path"));
else
  require_once APP_PATH . 'config/config.php';

//require_once APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php';
//require_once APP_PATH . APP_ROOT . APP_BASE['config'] . 'composer.php';

// !isset($_GET['path']) and $_GET['path'] = '';

//namespace App\Directory;

if (preg_match('/^app\.([\w\-.]+)\.php$/', basename(__FILE__), $matches))
  ${$matches[1]} = $matches[1];

//dd($directory, true);

/**
 * Generates a table.
 *
 * @return string
 */
//

$tableGen = function (): string {
  ob_start(); ?>

  <div id="info"
    style="position: absolute; display: none; width: 400px; height: 100px; left: 200px; top: 100px; border: 5px solid #000; background-color: #FFFFFF; z-index:99;">
    <div
      style="position: absolute; display: block; background-color: #FFFFFF; z-index: 1; right: 0px; margin-top: -20px;">
      [<a href="#" onclick="document.getElementById('info').style.display = 'none';">x</a>]</div>Texting inside
  </div>

  <?php
  //$path = APP_PATH . APP_ROOT . ($_GET['path'] ?? '');
  //dd($_GET);
  // Base navigation path
  $basePath = rtrim(APP_PATH, DIRECTORY_SEPARATOR);
  $clientPath = $_GET['client'] ?? null;
  $domainPath = $_GET['domain'] ?? null;
  $projectPath = $_GET['project'] ?? null;
  $relativePath = isset($_GET['path']) ? trim($_GET['path'], DIRECTORY_SEPARATOR) : null;

  // Base link
  $navigation = '<div style="position: absolute; top: 2px; left: 2px; width: 100%;">';
  $navigation .= '<a href="' . '?path" ' .
    (/*!isset($_GET['client']) || !isset($_GET['domain']) || !isset($_GET['project']) && */ isset($_GET['path']) ? 'onclick="handleClick(event, \'/\')"' : '') .
    '>' . '<img src="resources/images/directory-www.fw.png" width="50" height="32">' . (!isset($_GET['client']) && isset($_GET['path']) || !isset($_GET['project']) ? $basePath . DIRECTORY_SEPARATOR : '') . (!isset($_GET['app']) ? '' : '') . '</a>';


  // dd(get_required_files());


  // Client navigation
  if ($clientPath) {
    //$navigation .= '<a href="' . basename(__FILE__) . '?client=' . ($clientPath ?? '') . '"' .
    //    (isset($_GET['path']) || isset($_GET['client']) ? 'onclick="handleClick(event, \'/\')"' : '') . 
    //    '>' .
    //    '</a>';
    $navigation .= '<a href="?client&path=clientele/">' . 'clientele</a>' . DIRECTORY_SEPARATOR . '<a href="' . basename(__FILE__) . '?client=' . ($clientPath ?? '') . '" onclick="handleClick(event, \'/\')">' .
      $clientPath .
      '</a><a href="' . basename(__FILE__) . '?client=' . ($clientPath ?? '') . '&domain=' . ($domainPath ?? '') . '" onclick="handleClick(event, \'/\')">' . ((!empty($domainPath) ? DIRECTORY_SEPARATOR . $domainPath : '') ?? '') . DIRECTORY_SEPARATOR .
      '</a>';

  } else
    // Domain navigation
    if ($domainPath) {
      $navigation .= '<a href="' . basename(__FILE__) . '?client=' . '&path=clientele/' . '" onclick="handleClick(event, \'/\')">' .
        'clientele/' . '</a>';
      $navigation .= '<a href="' . basename(__FILE__) . '?' .
        (isset($clientPath) ? "client=$clientPath&" : '') .
        'domain=' . $domainPath . '" onclick="handleClick(event, \'/\')">' .
        (basename(APP_ROOT) !== $domainPath ? '' : $domainPath . DIRECTORY_SEPARATOR) .
        '</a>';
    } elseif ($clientPath) {
      $navigation .= DIRECTORY_SEPARATOR . $domainPath . DIRECTORY_SEPARATOR ?? null;
    }

  // Project navigation
  if ($projectPath) {
    $navigation .= '<a href="' . basename(__FILE__) . '?project" onclick="handleClick(event, \'/\')">' .
      'projects' . DIRECTORY_SEPARATOR . ($projectPath ?? '') . DIRECTORY_SEPARATOR . '</a>';
  }

  // Path navigation
  if ($relativePath && $relativePath !== '/') {
    $navigation .= '<a href="' . basename(__FILE__) . '?' . (isset($_GET['client']) ? 'client=' . ($clientPath ?? '') . '&' : '') . (isset($_GET['domain']) ? 'domain=' . ($domainPath ?? '') . '&'/* */ : '') . (isset($_GET['project']) ? 'project=' . ($projectPath ?? '') . '&' : '') . 'path=' . dirname($relativePath) . '" onclick="handleClick(event, \'' . dirname($relativePath) . '/\')">' .
      $relativePath . DIRECTORY_SEPARATOR . '</a>';
  }

  $navigation .= '</div><div style="height: 25px;"><br />' . APP_PATH . APP_ROOT . '</div>';
  echo $navigation;

  /*
  dd($_GET, false);

  dd(APP_PATH . APP_ROOT, false);
    dd(get_required_files(), false);
  dd($_POST, false);*/
  //dd(APP_CLIENT, false);

  if (isset($_GET['path']) && preg_match('/^vendor\/?/', $_GET['path'])) {

    //if ($_ENV['COMPOSER']['AUTOLOAD'] == true)
    require_once APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php';
    require_once APP_PATH . APP_BASE['config'] . 'composer.php'; ?>
    <!-- iframe src="composer_pkg.php" style="height: 500px; width: 700px;"></iframe -->
    <div style="width: 700px; ">
      <div style="display: inline-block; width: 350px;">Composers Vendor Packages [Installed] List</div>
      <div style="display: inline-block; text-align: right; width: 300px;">
        <form
          action="<?= !defined('APP_URL') ? '//' . APP_DOMAIN . APP_URL_PATH . '?' . http_build_query(APP_QUERY, '', '&amp;') : APP_URL . '?' . http_build_query(APP_QUERY, '', '&amp;') ?>"
          method="POST">
          <input id="RequirePkg" type="text" title="Enter Text and onSelect" list="RequirePkgs"
            placeholder="[vendor]/[package]" name="composer[package]" value onselect="get_package(this);" autocomplete="off"
            style=" margin-top: 4px;">
          <button type="submit" style="border: 1px solid #000; margin-top: 4px;"> Add </button>
          <div style="display: inline-block; float: right; text-align: left; margin-left: 10px;" class="text-xs">
            <input type="checkbox" name="composer[install]" value="" /> Install<br />
            <input type="checkbox" name="composer[update]" value="" /> Update
          </div>
          <datalist id="RequirePkgs">
            <option value=""></option>
          </datalist>
        </form>
      </div>
    </div>

    <?= /*var_dump(COMPOSER_VENDORS);*/ null;
    dd($_GET, false); ?>


    <table style="width: inherit; border: none;">
      <tr style=" border: none;">
        <?php
        if (defined('COMPOSER_VENDORS')) {

          //error_log(var_export(COMPOSER_VENDORS, true));
          //$paths = glob($path . '/*');
          $paths = COMPOSER_VENDORS;
          //dd(COMPOSER_VENDORS, false);
          //dd(urldecode($_GET['path']));
          /*
          $paths = ['0' => ...];
          usort($paths, function ($a, $b) {
              $aIsDir = is_dir(APP_BASE['vendor'].$a);
              $bIsDir = is_dir(APP_BASE['vendor'].$b);
              
              // Directories go first, then files
              if ($aIsDir && !$bIsDir) {
                  return -1;
              } elseif (!$aIsDir && $bIsDir) {
                  return 1;
              }
              
              // If both are directories or both are files, sort alphabetically
              return strcasecmp($a, $b);
          });
          */
          if ($projIndex = realpath(APP_PATH . 'projects' . DIRECTORY_SEPARATOR . 'index.php'))
            $handle = fopen($projIndex, 'r');
          $pkgs_matched = [];

          if (@$handle) {
            while (($line = fgets($handle)) !== false) {
              if (preg_match('/^use\s+(.+?);/', $line, $matches)) {
                $pkgs_matched[] = addslashes($matches[1]);
              }
            }
            fclose($handle);
          } else {
            echo "Error opening the projects/index.php file.";
          }

          $dirs = [];

          foreach (array_filter(glob(APP_PATH . APP_BASE['var'] . 'packages' . DIRECTORY_SEPARATOR . '*.php'), 'is_file') as $key => $dir) {
            if (preg_match('/^(.*)-(.*).php$/', basename($dir), $matches)) {
              $name = $matches[1];
              if (!isset($uniqueNames[$name])) {
                $uniqueNames[$name] = true;
                $dirs[] = $name;
              }
            }
          }

          $count = 1;
          $lastKey = array_key_last($paths);
          if (!empty($paths))
            foreach ($paths as $vendor => $packages) {

              echo "          <td style=\"text-align: center; border: none;\" class=\"text-xs\">\n            <div class=\"container2\">\n";

              $show_notice = true;

              //var_dump(preg_grep('/^Psr\\\\Log/', ['Psr\\Log\\LogLevel']));
    
              //var_dump($dirs);
    
              foreach ($packages as $package) {
                //var_dump('/^' . ucfirst($vendor) . '\\\\' . ucFirst($package) . '/'); // $pkgs_matched[0]
                //var_dump(preg_grep($grep = '/^'. ucfirst($vendor) . '\\\\\\\\' . ucFirst($package) . '/', $pkgs_matched));
                //if (!in_array(APP_PATH.APP_BASE['vendor'].$vendor.'/'.$package.'/Psr/Log/LogLevel.php', get_required_files())) { break; }
                //if (isset($pkgs_matched) && !empty($pkgs_matched) && class_exists($pkgs_matched[0])) {
    
                //$grep = '/^' . ucfirst($vendor) . '\\\\' . ucFirst($package) . '/';
                //dd(get_declared_classes());
                //$arr = preg_grep($grep, get_declared_classes());
                //$show_notice = (!empty($arr) ? true : false);
                //if (!empty($arr)) {}
    

                // $arr = ;
                //$show_notice = (!empty($arr) ? true : false);
                //if (!empty($arr)) { }
    
                if ($show_notice)
                  $show_notice = isset($pkgs_matched) && !empty($pkgs_matched) && !empty(preg_grep($grep = '/^' . ucfirst($vendor) . '\\\\\\\\' . ucFirst($package) . '/', $pkgs_matched)) ? false : (in_array($vendor, $dirs) ? true : false); // $arr[0] class_exists() $pkgs_matched[0]
    
                // (!in_array($vendor, $dirs) ? true : false) 
    

                //var_dump($show_notice);
                //var_dump($grep);
                //var_dump(!empty(preg_grep($grep, $pkgs_matched)));
                //}
              }
              if ($show_notice)
                echo '<div style="position: absolute; left: -12px; top: -12px; color: red; font-weight: bold;">[1]</div>';

              //if (is_dir(APP_PATH . APP_ROOT . APP_BASE['vendor'] . $vendor) /*|| !is_dir(APP_BASE['vendor'].$vendor)*/)
              //if ($vendor == 'barrydit') continue;
              switch ($vendor) {
                case 'symfony':
                  echo '<a class="pkg_dir" href="?path=' . APP_BASE['vendor'] . $vendor . '">'
                    . '<img src="resources/images/directory-symfony.png" width="50" height="32" style="' . (isset(COMPOSER->{'require'}->{"$vendor/$package"}) || isset(COMPOSER->{'require-dev'}->{"$vendor/$package"}) ?: 'opacity:0.4;filter:alpha(opacity=40);') . '" /></a><br />'
                    . '<div class="overlay">';
                  foreach ($packages as $package) {
                    if (in_array(APP_PATH . APP_BASE['vendor'] . $vendor . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR . 'bootstrap.php', get_required_files()))
                      echo '<a href="?app=ace_editor&path=' . APP_BASE['vendor'] . $vendor . '/' . $package . '/&file=bootstrap.php"><code style="background-color: white; color: #0078D7; font-size: 9px;">' . $package . '</code></a><br />';
                    elseif (in_array(APP_PATH . APP_BASE['vendor'] . $vendor . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR . 'function.php', get_required_files()))
                      echo '<a href="?app=ace_editor&path=' . APP_BASE['vendor'] . $vendor . '/' . $package . '/&file=function.php"><code style="background-color: white; color: #0078D7; font-size: 9px;">' . $package . '</code></a><br />';
                    else
                      echo '<p style="background-color: #0078D7;">' . $package . '</p>' . PHP_EOL;
                    //echo APP_PATH. APP_BASE['vendor'] . $vendor.'/'.$package;
    
                    // /mnt/c/www/public/composer/vendor/symfony/deprecation-contracts
                  }
                  echo '</div>' . '<a href="?path=' . APP_BASE['vendor'] . $vendor . '">' . ucfirst($vendor) . '</a>';
                  break;
                case 'composer':
                  foreach ($packages as $package) {
                    if (is_file(APP_BASE['var'] . 'packages' . DIRECTORY_SEPARATOR . $vendor . '-' . $package . '.php'))
                      $app['composer'][$vendor][$package]['body'] = file_get_contents(APP_BASE['var'] . 'packages' . DIRECTORY_SEPARATOR . $vendor . '-' . $package . '.php');
                    //if (!in_array(APP_PATH.'vendor/'.$vendor.'/'.$package.'/Psr/Log/LogLevel.php', get_required_files())) {
                    //echo '<div style="position: absolute; left: -12px; top: -12px; color: red; font-weight: bold;">[1]</div>';
                    //  break;
                    //}
                  }
                  echo '<a class="pkg_dir" href="#!" onclick="document.getElementById(\'app_composer-container\').style.display=\'block\';">' // ?app=ace_editor&path=vendor/' . $vendor . '
                    . '<img src="resources/images/directory-composer.png" width="50" height="32" style="' . (isset(COMPOSER->{'require'}->{"$vendor/composer"}) || isset(COMPOSER->{'require-dev'}->{"$vendor/$package"}) ? '' : 'opacity:0.4;filter:alpha(opacity=40);') . '" /></a><br />'
                    . '<div class="pkg_dir overlay">';
                  foreach ($packages as $package) {
                    if (!in_array(APP_PATH . APP_ROOT . APP_BASE['vendor'] . $vendor . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR . 'Psr' . DIRECTORY_SEPARATOR . 'Log' . DIRECTORY_SEPARATOR . 'LogLevel.php', get_required_files()) && $package == 'log') {
                      echo '<a href="?app=ace_editor&path=vendor/' . $vendor . '/' . $package . '/Psr/Log/&file=LogLevel.php"><code style="background-color: white; color: #0078D7; font-size: 10px;">' . $package . '</code></a>';
                      continue;
                    }
                    echo '<p style="background-color: #0078D7;">' . $package . '</p>' . PHP_EOL;
                  }
                  echo '</div>' . '<a href="?path=vendor/' . $vendor . '">' . ucfirst($vendor) . '</a>' . "\n";
                  break;
                case 'psr':
                  echo '<a class="pkg_dir" href="#!" onclick="document.getElementById(\'app_project-container\').style.display=\'block\';">' // ?app=ace_editor&path=vendor/' . $vendor . '
                    . '<img src="resources/images/directory-psr.png" width="50" height="32" style="' . (isset(COMPOSER->{'require'}->{"$vendor/$package"}) || isset(COMPOSER->{'require-dev'}->{"$vendor/$package"}) ? '' : (!$show_notice ? '' : 'opacity:0.4;filter:alpha(opacity=40);')) . '" />' . '</a><br />'
                    . '<div class="overlay">';
                  foreach ($packages as $package) {
                    if (!in_array(APP_PATH . APP_BASE['vendor'] . $vendor . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR . 'Psr' . DIRECTORY_SEPARATOR . 'Log' . DIRECTORY_SEPARATOR . 'LogLevel.php', get_required_files()) && $package == 'log') {
                      echo "<a href=\"?app=ace_editor&path=vendor/$vendor/$package/Psr/Log/&file=LogLevel.php\"><code style=\"background-color: white; color: #0078D7; font-size: 10px;\">$package</code></a>";
                      continue;
                    }

                    echo '<p style="background-color: #0078D7;">' . $package . '</p>' . PHP_EOL;
                  }
                  echo '</div>' . '<a href="?path=vendor/' . $vendor . '">' . ucfirst($vendor) . '</a>' . "\n";
                  break;
                default:
                  echo '<a class="pkg_dir" href="?' . (APP_ROOT != '' ? array_key_first($_GET) . '=' . $_GET[array_key_first($_GET)] . '&' : '') . 'path=vendor/' . $vendor . '">'
                    . '<img src="resources/images/directory.png" width="50" height="32" style="' . (isset(COMPOSER->{'require'}->{"$vendor/$package"}) || isset(COMPOSER->{'require-dev'}->{"$vendor/$package"}) ?: 'opacity:0.4;filter:alpha(opacity=40);') . '" />' . '</a><br />'
                    . '<div class="overlay">';
                  foreach ($packages as $package) {
                    echo '<code style="background-color: white; color: #0078D7;">' . $package . '</code><br />' . PHP_EOL;
                  }
                  echo '</div>' . '<a href="?' . (APP_ROOT != '' ? array_key_first($_GET) . '=' . $_GET[array_key_first($_GET)] . '&' : '') . 'path=vendor/' . $vendor . '">' . ucfirst($vendor) . '</a>' . "\n";
                  break;
              }
              echo "</div>\n</td>\n";

              if ($count >= 6)
                echo '</tr><tr>';
              elseif ($lastKey == $key)
                echo '</tr>';

              if (isset($count) && $count >= 6)
                $count = 1;
              else
                $count++;
            }

          foreach (COMPOSER_VENDORS as $vendor => $packages) {
            $dirs_diff[] = $vendor;
          }

          if (is_array($dirs) && is_array($dirs_diff))
            $result = array_diff($dirs, $dirs_diff);

          //dd($result);
          if (!empty($result))
            $lastKey = array_key_last($result);
          if (!empty($result))
            foreach ($result as $key => $install) {
              echo '<td style="border: none; text-align: center;" class="text-xs">' . "\n"
                . '<a href="#!" onclick="document.getElementById(\'app_git-container\').style.display=\'block\';">' // "?path=' . basename($path) . '" 
                . '<img src="resources/images/directory-install.png" width="50" height="32" ' . /*style="opacity:0.4;filter:alpha(opacity=40);"*/ ' /><br />' . $install . '/</a>' . "\n";
              echo "</td>\n";

              if ($count >= 6)
                echo '</tr><tr>';
              elseif ($lastKey == $key)
                echo '</tr>';
              if (isset($count) && $count >= 6)
                $count = 1;
              else
                $count++;
            }
        } ?>
        <!-- /tr -->
    </table>
  <?php } elseif (isset($_GET['path']) && preg_match('/^project\/?/', $_GET['path']) || isset($_GET['project']) && empty($_GET['project'])) {
    if (readlinkToEnd($_SERVER['HOME'] . '/projects') == '/mnt/c/www/projects' || realpath(APP_PATH . 'projects')) { ?>
      <div style="text-align: center; border: none;" class="text-xs">
        <a class="pkg_dir" href="#" onclick="document.getElementById('app_project-container').style.display='block';">
          <img src="resources/images/project-icon.png" width="50" height="32" style="" /></a><br /><a
          href="?project">./project/</a>
      </div>
      <table width="" style="border: none;">
        <tr style=" border: none;">
          <?php
          $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'projects/*'), 'is_dir');

          $count = 1;
          ?>
          <?php
          if (empty($links)) {
            echo "<hr />\n"; // label="     "
          } else  //dd($links);
            $old_links = $links;
          while ($link = array_shift($links)) {
            $old_link = $link;
            $link = basename($link);


            echo "<td style=\"text-align: center; border: none;\" class=\"text-xs\">\n";
            echo "<a class=\"pkg_dir\" href=\"?project=$link\"><img src=\"resources/images/directory.png\" width=\"50\" height=\"32\" style=\"\" /><br />$link</a><br /></td>\n";
            if ($count >= 7)
              echo '</tr><tr>';
            elseif ($old_link == end($old_links))
              echo '</tr>';

            if (isset($count) && $count >= 7)
              $count = 1;
            else
              $count++;
          }

    }

    ?>
    </table>
    <!--
      <li>
      <?php if (readlinkToEnd('/var/www/projects') == '/mnt/c/www/projects' || realpath(APP_PATH . 'projects')) { ?>
      <a href="projects/">project/</a>
        <ul style="padding-left: 10px;">
          <form action method="GET">
            <select id="sproject" name="project" style="color: #000;">
      <?php
      while ($link = array_shift($links)) {
        $link = basename($link); // Get the directory name from the full path
        if (is_dir(APP_PATH . /*'../../'.*/ 'projects/' . $link))
          echo '  <option value="' . $link . '" ' . (current($_GET) == $link ? 'selected' : '') . '>' . $link . '</option>' . "\n";
      }
      ?>
            </select>
          </form>
      </ul>
      <?php } ?></li>
      -->
  <?php } elseif (isset($_GET['application'])) { ?>

    <?php if (readlinkToEnd('/var/www/applications') == '/mnt/c/www/applications') {
      if ($_GET['application']) {

        $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'applications/' . $_GET['application']), 'is_file');

        echo '<h3>Application: 7-Zip</h3>';


        echo '<br /><div style="text-align: center; margin: 0 auto;"><a href="https://www.7-zip.org/download.html"><img width="110" height="63" src="http://www.7-zip.org/7ziplogo.png" alt="7-Zip" border="0" /><br />' . basename($links[0]) . ' =&gt; <a href="https://www.7-zip.org/a/7z2301-x64.exe">7z2301-x64.exe</a></a></div>' . "<br />";


      } else {
        $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'applications/*'), 'is_file'); ?>
        <h3>Applications:</h3>
        <table width="" style="border: none;">
          <tr style=" border: none;">
            <?php
            //if (empty($links)) {
            //  echo '<option value="" selected>---</option>' . "\n"; // label="     "
            //} else  //dd($links);
            $count = 1;
            $old_links = $links;
            while ($link = array_shift($links)) {
              $old_link = $link;
              $link = basename($link);

              echo '<td style="text-align: center; border: none;" class="text-xs">' . "\n";

              echo '<a class="pkg_dir" href="?application=' . $link . '">'
                . '<img src="resources/images/app_file.png" width="50" height="32" style="" /><br />' . $link . '</a><br />'
                . '</td>' . "\n";

              if ($count >= 3)
                echo '</tr><tr>';
              elseif ($old_link == end($old_links))
                echo '</tr>';

              if (isset($count) && $count >= 3)
                $count = 1;
              else
                $count++;
            }

            ?>
        </table>
      <?php }
    }
  } elseif (isset($_GET['node_module']) && empty($_GET['node_module'])) { ?>
    <?php //if (readlinkToEnd('/var/www/applications') == '/mnt/c/www/applications') { }
        $links = array_filter(glob(APP_PATH . 'node_modules/*'), 'is_dir'); ?>
    <div style="display: inline-block; width: 350px;">Node Modules [Installed] List</div>
    <div style="display: inline-block; text-align: right; width: 300px; ">
      <form action="<?= APP_URL_BASE . '?' . http_build_query(APP_QUERY + ['app' => 'composer', 'path' => 'vendor']) ?>"
        method="POST">
        <input id="RequirePkg" type="text" title="Enter Text and onSelect" list="RequirePkgs"
          placeholder="[vendor]/[package]" name="composer[package]" value="" onselect="get_package(this);"
          autocomplete="off" style=" margin-top: 4px;">
        <button type="submit" style="border: 1px solid #000; margin-top: 4px;"> Add </button>
        <div style="display: inline-block; float: right; text-align: left; margin-left: 10px;" class="text-xs">
          <input type="checkbox" name="composer[install]" value=""> Install<br>
          <input type="checkbox" name="composer[update]" value=""> Update
        </div>
        <datalist id="RequirePkgs">
          <option value=""></option>
        </datalist>
      </form>
    </div>
    <table width="" style="border: none;">
      <tr style=" border: none;">
        <?php
        //if (empty($links)) {
        //  echo '<option value="" selected>---</option>' . "\n"; // label="     "
        //} else  //dd($links);
        $count = 1;
        $old_links = $links;
        while ($link = array_shift($links)) {
          $old_link = $link;
          $link = basename($link);

          echo '<td style="text-align: center; border: none;" class="text-xs">' . "\n";

          echo '<a class="pkg_dir" href="?application=' . $link . '">'
            . '<img src="resources/images/directory.png" width="50" height="32" style="" /><br />' . $link . '</a><br />'
            . '</td>' . "\n";

          if ($count >= 3)
            echo '</tr><tr>';
          elseif ($old_link == end($old_links))
            echo '</tr>';

          if (isset($count) && $count >= 3)
            $count = 1;
          else
            $count++;
        }

        ?>
    </table>
  <?php } elseif (isset($_GET['path']) && preg_match('/^client(?:s|ele)?\/?/', $_GET['path']) || isset($_GET['client']) && empty($_GET['client'])) { ?>
    <?php if (readlinkToEnd('/var/www/clientele') == '/mnt/c/www/clientele' || realpath(APP_PATH . 'clientele')) { ?>
      <h3>&#9660; Domains: </h3>
      <table width="" style="border: none;">
        <tr style=" border: none;">
          <?php
          $count = 1;

          //if (empty($links)) {
          //  echo '<option value="" selected>---</option>' . "\n"; // label="     "
          //} else  //dd($links);
    
          $links = array_filter(glob(APP_PATH . 'clientele/*', GLOB_ONLYDIR), function ($link) {
            // Apply regex to the basename (last part of the path)
            return preg_match('/^(?!\d{3}-)[a-z0-9\-]+\.[a-z]{2,6}$/i', basename($link));
          });

          $old_links = $links;
          while ($link = array_shift($links)) {
            $old_link = $link;
            $link = basename($link);

            echo "<td style=\"text-align: center; border: none;\" class=\"text-xs\">\n"
              . "<a class=\"pkg_dir\" href=\"?" . (isset($_ENV['DEFAULT_CLIENT']) && $_ENV['DEFAULT_CLIENT'] == $link ? '' : "domain=$link") . "\" onclick=\"\"><img src=\"resources/images/directory.png\" width=\"50\" height=\"32\" style=\"\" /><br />$link/</a><br /></td>\n";

            if ($count >= 6)
              echo '</tr><tr>';
            elseif ($old_link == end($old_links))
              echo '</tr>';

            if (isset($count) && $count >= 6)
              $count = 1;
            else
              $count++;
          }
          ?>
      </table>
      <?php

      foreach (['000', '100', '200', '300', '400'] as $key => $status) {

        if ($key != 0)
          echo "</table>\n\n\n";

        $links = array_filter(glob(APP_PATH . /*'../../'.*/ 'clientele/' . $status . '*'), 'is_dir');
        $statusCode = $status;
        $status = ($status == 000) ? "On-call" :
          (($status == 100) ? "Working" :
            (($status == 200) ? "Planning" :
              (($status == 300) ? "Previous" :
                (($status == 400) ? "Future" : "Unknown")))); ?>
        <h3>&#9660; Stage: <?= $status ?> (<?= $statusCode ?>)</h3>
        <table width="" style="border: none;">
          <tr style=" border: none;">
            <?php
            $count = 1;

            //if (empty($links)) {
            //  echo '<option value="" selected>---</option>' . "\n"; // label="     "
            //} else  //dd($links);
            $old_links = $links;
            while ($link = array_shift($links)) {
              $old_link = $link;
              $link = basename($link);

              echo "<td style=\"text-align: center; border: none;\" class=\"text-xs\">\n"
                . "<a class=\"pkg_dir\" href=\"?" . (isset($_ENV['DEFAULT_CLIENT']) && $_ENV['DEFAULT_CLIENT'] == $link ? '' : "client=$link") . "\" onclick=\"\"><img src=\"resources/images/directory.png\" width=\"50\" height=\"32\" style=\"\" /><br />$statusCode-Client$count/</a><br /></td>\n";

              if ($count >= 6)
                echo '</tr><tr>';
              elseif ($old_link == end($old_links))
                echo '</tr>';

              if (isset($count) && $count >= 6)
                $count = 1;
              else
                $count++;
            }
      } ?>
      </table>
    <?php } else { ?>

      <div
        style="position: absolute; top: 100px; width: 200px; left: 36%; right: 64%; text-align: center; border: 1px solid #000;">
        <?php echo "<a class=\"pkg_dir\" style=\"border: 1px dashed blue;\" href=\"?client=\">Missing directory.<br/>' . 
. '<img src=\"resources/images/directory.png\" width=\"60\" height=\"42\" style=\"\" /><br />'
. 'Create <input type=\"text\" style=\"text-align: right;\"size=\"7\" name=\"clientele\" value=\"clientele/\"></a><br />\n"; ?>
      </div>

    <?php }
  } else {

    //if(isset($_GET['client']) && !empty($_GET['client']))
    //  $path = 'clientele/' . $_GET['client'] . '/' . (isset($_GET['domain']) && !empty($_GET['domain']) ? $_GET['domain'] . '/' : '');

    //elseif(isset($_GET['project']) && !empty($_GET['project']))
    //  $path = 'projects/' . $_GET['project'] . '/';


    // >>>

    //    $path = defined('APP_ROOT') && APP_ROOT ? APP_PATH . APP_ROOT : (APP_ROOT == '' ? APP_PATH . (isset($_GET['client']) ? 'clientele' . DIRECTORY_SEPARATOR . $_GET['client'] . DIRECTORY_SEPARATOR : '') . (isset($_GET['client']) || isset($_GET['domain']) ? (isset($_GET['domain']) ? $_GET['domain'] . DIRECTORY_SEPARATOR : '') : (isset($_GET['path']) ? '' : 'vendor' . DIRECTORY_SEPARATOR . (isset($_GET['client']) ? $_GET['client'] . DIRECTORY_SEPARATOR : ''))) : APP_PATH . APP_ROOT);
    $path = APP_PATH;

    if (defined('APP_ROOT') && APP_ROOT) {
      $path .= APP_ROOT;
    } elseif (APP_ROOT === '') {
      // Handle client-specific path
      if (isset($_GET['client'])) {
        $path .= 'clientele' . DIRECTORY_SEPARATOR . $_GET['client'] . DIRECTORY_SEPARATOR;
      }

      // Add domain to the path if applicable
      if (isset($_GET['domain'])) {
        $path .= $_GET['domain'] . DIRECTORY_SEPARATOR;
      } elseif (!isset($_GET['client']) && !isset($_GET['path'])) {
        // Default to vendor path if no domain or client is set
        $path .= 'vendor' . DIRECTORY_SEPARATOR;
        if (isset($_GET['client'])) {
          $path .= $_GET['client'] . DIRECTORY_SEPARATOR;
        }
      }
    } else {
      $path .= APP_ROOT;
    }



    //


    //$path = dirname(APP_PATH . APP_ROOT) . DIRECTORY_SEPARATOR . ($_GET['path'] ?? '');

    //$path = APP_PATH . APP_ROOT . ($_GET['path'] ?? '');
//dd($path);

    // <<<

    if (!realpath($path . ($_GET['path'] ?? ''))) { ?>

      <?= "<br /><br />Missing directory $path"; ?>

    <?php } else { // ?>
      <table style="width: inherit; border: 0 solid #000;">
        <tr>
          <?php

          //dd(APP_CLIENT, false);
    
          //$path = (defined('APP_CLIENT')) ? APP_CLIENT : APP_PATH . (!isset($_GET['domain']) && isset($_GET['client']) ? APP_ROOT : APP_ROOT);
    
          //echo dirname($pathAvail) . DIRECTORY_SEPARATOR . ($_GET['path'] ?? '');
    
          //$paths = ['thgsgfhfgh.php']; // dirname(APP_PATH . APP_ROOT) . DIRECTORY_SEPARATOR 
          $paths = glob(rtrim($path . ($_GET['path'] ?? ''), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '{.[!.]*,*}', GLOB_BRACE | GLOB_MARK);

          unset($path);
          //dd(urldecode($_GET['path']));
    
          usort($paths, function ($a, $b) {
            $aIsDir = is_dir($a);
            $bIsDir = is_dir($b);

            // Check if either $a or $b is the "project.php" file
            $aIsProjectFile = !$aIsDir && basename($a) === 'project.php';
            $bIsProjectFile = !$bIsDir && basename($b) === 'project.php';

            // Handle the case when either $a or $b is the "project.php" file
            if ($aIsProjectFile || $bIsProjectFile) {
              if ($aIsProjectFile && $bIsProjectFile) {   // -1 0 1
                return -1; // Both are "project.php" files, no change in order
              } elseif ($aIsProjectFile) {
                return 0; // $a is "project.php", move it down
              } else {
                return 1; // $b is "project.php", move it up
              }
            }

            // Directories go first, then files
            if ($aIsDir && !$bIsDir) {
              return -1;
            } elseif (!$aIsDir && $bIsDir) {
              return 1;
            }

            // If both are directories or both are files, sort alphabetically
            return strcasecmp($a, $b);
          });
          /*
          usort($paths, function ($a, $b) {
              $aIsDir = is_dir($a);
              $bIsDir = is_dir($b);
              
              // Directories go first, then files
              if ($aIsDir && !$bIsDir) {
                  return -1;
              } elseif (!$aIsDir && $bIsDir) {
                  return 1;
              }
          
              // If both are directories or both are files, sort alphabetically
              return strcasecmp($a, $b);
          });
          */

          $count = 1;
          $lastKey = array_key_last($paths);

          if (!empty($paths))
            foreach ($paths as $key => $path) {
              // Adjust the path to be relative to the current directory
              $relativePath = str_replace(APP_PATH . APP_ROOT, '', rtrim($path, DIRECTORY_SEPARATOR));

              echo "<td style=\"border: 0 solid #000; text-align: center;\" class=\"text-xs\">\n";
              if (is_dir($path)) {
                if (substr(PHP_OS, 0, 3) == 'WIN') {
                  $relativePath = rtrim(str_replace('\\', '/', $relativePath), DIRECTORY_SEPARATOR); //
                } elseif (stripos(PHP_OS, 'LIN') == 0) {
                  $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath) . DIRECTORY_SEPARATOR;
                }

                //dd($relativePath);
    

                //function buildQueryString($queryParams, $relativePath)
//{
                $client = isset($_GET['client']) ? 'client=' . urlencode($_GET['client']) . '&' : '';
                $domain = isset($_GET['domain']) && $_GET['domain'] !== ''
                  ? 'domain=' . urlencode($_GET['domain']) . '&'
                  : '';
                $project = isset($_GET['project']) ? 'project=' . urlencode($_GET['project']) . '&' : '';

                /*return*/
                $url = $client . $domain . $project . 'path=' . urlencode($relativePath);
                //}
    
                switch (basename($path)) {
                  case '.git':
                    echo '<div style="position: relative; border: 4px dashed #F05033;">'
                      . '<a href="#!" onclick="document.getElementById(\'app_git-container\').style.display=\'block\';">' // "?path=' . basename($path) . '" 
                      . '<img src="resources/images/directory-git.png" width="50" height="32" /></a><br />'
                      . '<a href="' . basename(__FILE__) . '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\'); document.getElementById(\'app_git-container\').style.display=\'block\';">' . basename($path) . '/</a></div>' . "\n";
                    break;
                  case 'applications':
                    echo '<div style="position: relative;">'
                      . '<a href="?application" onclick="document.getElementById(\'app_application-container\').style.display=\'block\';"><img src="resources/images/directory-application.png" width="50" height="32" /></a><br />'
                      . '<a href="' . basename(__FILE__) . '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\');">' . basename($path)  // "?path=' . basename($path) . '"
                      . '/</a></div>' . "\n";
                    break;
                  case 'clients':
                  case 'clientele':
                    echo '<div style="position: relative; border: 4px dashed orange;">'
                      . '<a href="' . basename(__FILE__) . '?' . $url /*. (!defined('APP_ROOT') || empty(APP_ROOT) ? '' : (array_key_first($_GET) == 'client' ? 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '') : (array_key_first($_GET) == 'project' ? 'project=' . $_GET['project'] . '&' : ''))) . 'path=' . $relativePath*/ . '" onclick="handleClick(event, \'' . $relativePath . '\')">'
                      // (!isset($_GET['path']) ? '' : $_GET['path'] . ($_GET['path'] == '' ? '' : '/' )) . basename($path)
                      . '<img src="resources/images/directory.png" width="50" height="32" /><br />' . basename($path) . '/</a></div>' . "\n";
                    break;
                  case 'node_modules':
                    echo '<div style="position: relative; border: 4px dashed #E14747;">'
                      . '<a href="#!" onclick="handleClick(event, \'' . $relativePath . '\');document.getElementById(\'app_npm-container\').style.display=\'block\';"><img src="resources/images/directory-npm.png" width="50" height="32" /></a><br />'
                      . '<a href="' . basename(__FILE__) . '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\')">' . basename($path)  // "?path=' . basename($path) . '"         
                      . '/</a></div>' . "\n";
                    break;
                  case 'database':
                    echo '<div style="position: relative; border: 4px dashed #2C88DA;">'
                      . '<a href="' . basename(__FILE__) . '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\')">'
                      // (!isset($_GET['path']) ? '' : $_GET['path'] . ($_GET['path'] == '' ? '' : '/' )) . basename($path)
                      . '<img src="resources/images/directory.png" width="50" height="32" /><br />' . basename($path) . '/</a></div>' . "\n";
                    break;
                  case 'projects':
                    echo '<div style="position: relative; border: 4px dashed #2C88DA;">'
                      . '<a href="#!" onclick="document.getElementById(\'app_project-container\').style.display=\'block\';"><img src="resources/images/directory-project.png" width="50" height="32" /></a><br />'
                      . '<a href="' . basename(__FILE__) . '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\')">' . basename($path)  // "?path=' . basename($path) . '"
                      . '/</a></div>' . "\n";
                    break;
                  case 'public':
                    echo '<div style="position: relative; border: 4px dashed #CC9900;">'
                      . '<a href="' . basename(__FILE__) . '?' . (!defined('APP_ROOT') || empty(APP_ROOT) ? '' : '') . $url . '" onclick="handleClick(event, \'' . $relativePath . '\')">'
                      // (!isset($_GET['path']) ? '' : $_GET['path'] . ($_GET['path'] == '' ? '' : '/' )) . basename($path)
                      . '<img src="resources/images/directory.png" width="50" height="32" /><br />' . basename($path) . '/</a></div>' . "\n";
                    break;
                  case 'vendor':
                    echo '<div style="position: relative; border: 4px dashed #6B4329;">'
                      . '<a href="#!" onclick="handleClick(event, \'' . $relativePath . '\');document.getElementById(\'app_composer-container\').style.display=\'block\';">' . '<img src="resources/images/directory-composer.png" width="50" height="32" /></a><br />'
                      . '<a href="' . basename(__FILE__) . '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\');" >' . basename($path)  // "?path=' . basename($path) . '"         
                      . '/</a></div>' . "\n";
                    break;
                  default:
                    // Ensure the path excludes the domain if present in the folder structure
    
                    $relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);

                    //dd($relativePath, false);
    
                    //dd($_GET['domain'], false);
    
                    // Initialize query parameters
                    $queryParams = [];

                    if (!empty($_GET['domain'])) {
                      // Remove the domain from the relative path if it exists at the start
                      $relativePath = preg_replace(
                        '#^' . preg_quote($_GET['domain'], '#') . '/?#',
                        '',
                        $relativePath
                      );
                      $queryParams = [
                        'domain' => $_GET['domain'],
                        'path' => (defined('APP_ROOT') && APP_ROOT != '' ? ($_GET['path'] ?? basename($path) ?? '') : '') ?? basename(rtrim($relativePath, '/') ?? basename($path)),
                      ];
                    }

                    // Determine the parameters to use based on the conditions
                    if (isset($_GET['client']) && !empty($_GET['domain'])) {
                      // Case 1: Both client and domain are set
                      $queryParams = [
                        'client' => $_GET['client'],
                        'domain' => $_GET['domain'] ?? basename($path),
                        'path' => $_GET['path'] ?? basename($path) ?? basename(rtrim($relativePath, '/')), // Add the path parameter
                      ];
                    } elseif (isset($_GET['client'])) {
                      // Case 2: Only client is set
                      $queryParams = [
                        'client' => $_GET['client'],
                        'domain' => $_GET['domain'] ?? basename($path), // Default domain if not explicitly provided
                        'path' => (defined('APP_ROOT') && APP_ROOT != '' ? $_GET['path'] : '') ?? basename(rtrim($relativePath, '/') ?? basename($path)),
                      ];
                    } elseif (isset($_GET['path'])) {
                      // Case 3: Only path is set
                      $queryParams = [
                        'path' => (isset($_GET['path']) && $_GET['path'] != '' ? $_GET['path'] : basename($path)) /*?? rtrim($relativePath, '/')*/ , // Use the path parameter
                      ];
                    } else {
                      // Case 3: Only path is set
                      $queryParams = [
                        'path' => (isset($_GET['path']) && $_GET['path'] != '' ? $_GET['path'] : basename($path)) /*?? rtrim($relativePath, '/')*/ , // Use the path parameter
                      ];
                    }

                    if (!empty($_GET['project'])) {
                      // Remove the domain from the relative path if it exists at the start
                      $relativePath = preg_replace(
                        '#^' . preg_quote($_GET['project'], '#') . '/?#',
                        '',
                        $relativePath
                      );
                      $queryParams = [
                        'project' => $_GET['project'],
                        'path' => (defined('APP_ROOT') && APP_ROOT != '' ? ($_GET['path'] ?? basename($path) ?? '') : '') ?? basename(rtrim($relativePath, '/') ?? basename($path)),
                      ];
                    }

                    if (!isset($_GET['path']) || $_GET['path'] == '') {
                      $basePath = rtrim(APP_PATH . APP_ROOT, DIRECTORY_SEPARATOR);

                      $path = preg_replace('#^' . preg_quote($basePath, '#') . '/?#', '', $path);

                      $_GET['path'] = (string) $path; // dirname(rtrim($relativePath, '/')) . '/';
    
                      unset($_GET['path']);

                      // Filter out empty parameters to avoid unnecessary query string entries
//$queryParams = array_filter($queryParams);
    
                      // Build the query string
                      $queryString = http_build_query($queryParams);

                      $url = (defined('APP_ROOT') && APP_ROOT != '' && !empty($_GET['client']) ? basename(__FILE__) : '') . "?$queryString"; // 
                    } else {

                      // Build the query string
                      $queryString = http_build_query($queryParams);

                      $url = (defined('APP_ROOT') && APP_ROOT != '' ? basename(__FILE__) : '') . "?$queryString" . basename($path); // 
                    }

                    // Determine the base path
                    $basePath = $_GET['path'] ?? '';

                    // Determine the domain condition
                    $domainCondition = !defined('APP_ROOT') || APP_ROOT == ''
                      && (!isset($_GET['domain']) || $_GET['domain'] == '');

                    // Determine the client condition
                    $clientCondition = isset($_GET['client']) && !isset($_GET['domain']);

                    $projectCondition = !defined('APP_ROOT') || APP_ROOT == ''
                      && (!isset($_GET['project']) || $_GET['project'] == '');

                    //dd($_ENV, false);
                    // Determine the path suffix
                    $pathSuffix = isset($_ENV['DEFAULT_DOMAIN']) && $_ENV['DEFAULT_DOMAIN'] != basename($path)
                      ? (!$clientCondition ? basename($path) : '')
                      : basename(rtrim($relativePath, '/'));

                    // Combine the full path for the onclick attribute
                    $fullPath = "$basePath$pathSuffix/";
                    //die('test');
                    // Construct the onclick attribute if needed
                    $onclickAttribute = '';
                    if ($domainCondition && isset($_GET['client']) && !isset($_GET['domain'])) {
                      $onclickAttribute = (basename($path) == $_ENV['DEFAULT_DOMAIN']) ? " onclick=\"handleClick(event, '$fullPath')\"" : "";
                    } else if ($domainCondition && isset($_GET['domain'])) {
                      $onclickAttribute = " onclick=\"handleClick(event, '$fullPath')\"";
                    } elseif ($projectCondition && isset($_GET['project'])) {
                      $onclickAttribute = " onclick=\"handleClick(event, '$fullPath')\"";
                    } else { //elseif (!$domainCondition) { ... } 
                      $onclickAttribute = " onclick=\"handleClick(event, '$fullPath')\"";
                    }

                    // Render the link
                    echo '<a href="' . htmlspecialchars($url) . '"' . $onclickAttribute . '>
        <img src="resources/images/directory.png" width="50" height="32" /><br />'
                      . basename($path) . '/</a>';


                    /*
                                      $appRoot = 'clientele/' . ($_GET['client'] ?? '') . '/' . ($_GET['domain'] ?? '') . '/';

                                      // Ensure the path excludes the domain if it's present
                                      $relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);
                                      if (!empty($_GET['domain'])) {
                                          $relativePath = str_replace($_GET['domain'] . '/', '', $relativePath);
                                      }
                                      
                                      // Constructing URL parameters
                                      $clientParam = isset($_GET['client']) ? 'client=' . $_GET['client'] . '&' : '';
                                      $domainParam = isset($_GET['domain']) ? 'domain=' . $_GET['domain'] . '&' : '';
                                      $pathParam = "path=$relativePath";
                                      
                                      // Final URL without including the domain in the path
                                      $url = "?$clientParam$domainParam$pathParam";
                                      
                                      // Render link
                                      echo '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . $relativePath . '/\')">Link Text</a>'


                    // Initialize variables
                    $clientParam = isset($_GET['client']) ? 'client=' . $_GET['client'] . '&' : '';
                    $projectParam = isset($_GET['project']) ? 'project=' . $_GET['project'] . '&' : '';
                    $domain = isset($_GET['domain']) ? $_GET['domain'] : '';
                    $relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);

                    // Check if domain is part of the relative path and adjust accordingly
                    if ($domain) {
                        $relativePath = ($domain . '/' === (string)$relativePath)
                            ? str_replace($domain . '/', '', $relativePath)
                            : $relativePath;
                        $domainParam = "domain=$domain&";
                    } else {
                        $domainParam = '';
                    }

                    // Build the path, excluding the domain if set
                    $pathParam = 'path=' . str_replace($domain, '', $relativePath);

                    // Construct the href link based on provided parameters
                    $url = '?' . (!defined('APP_ROOT') || empty(APP_ROOT) ? '' : (
                            array_key_first($_GET) === 'client' ? $clientParam . $domainParam : (
                            array_key_first($_GET) === 'project' ? $projectParam : ''))) . $pathParam;

                    echo '<a href="' . $url . '" onclick="handleClick(event, \'' . $domain . '/\')">Link Text</a>'; */

                    break;
                }
              } elseif (is_file($path)) {
                $relativePath = str_replace(APP_PATH . APP_ROOT, '', rtrim($path, DIRECTORY_SEPARATOR));
                // Ensure the path excludes the domain if present in the folder structure
                $relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);

                if (!empty($_GET['domain'])) {
                  // Remove the domain from the relative path if it exists at the start
                  $relativePath = preg_replace(
                    '#^' . preg_quote($_GET['domain'], '#') . '/?#',
                    '',
                    $relativePath
                  );
                }

                // Initialize query parameters
                $queryParams = [];

                if (!isset($_GET['path']))
                  $_GET['path'] = '';
                //$_GET['path'] = '';
                $_GET['app'] = 'ace_editor';

                // Determine the parameters to use based on the conditions
                if (isset($_GET['client']) && isset($_GET['domain'])) {
                  // Case 1: Both client and domain are set
                  $queryParams = [
                    'client' => $_GET['client'],
                    'domain' => $_GET['domain'],
                    'path' => $_GET['path'] ?? dirname(rtrim($path, '/')) . '/', // Add the path parameter
                    'app' => $_GET['app'],
                    'file' => basename($path),
                  ];
                } elseif (isset($_GET['client'])) {
                  // Case 2: Only client is set
                  $queryParams = [
                    'client' => $_GET['client'],
                    'domain' => $_GET['domain'] ?? '' /*rtrim($relativePath, '/')*/ , // Default domain if not explicitly provided
                    'path' => $_GET['path'] ?? dirname(rtrim($path, '/')) . '/', // Add the path parameter
                    'app' => $_GET['app'],
                    'file' => basename($path),
                  ];
                } elseif (isset($_GET['project'])) {
                  $queryParams = [
                    //'path' => rtrim($relativePath, '/') . '/', // Use the path parameter
                    'project' => $_GET['project'] ?? '', // Add the path parameter
                    'path' => $_GET['path'] ?? '',
                    'app' => $_GET['app'],
                    'file' => basename($path),
                  ];
                } elseif (isset($_GET['path'])) {
                  // Case 3: Only path is set
                  $queryParams = [
                    //'path' => rtrim($relativePath, '/') . '/', // Use the path parameter
                    'path' => $_GET['path'] ?? '', // Add the path parameter
                    'app' => $_GET['app'],
                    'file' => basename($path),
                  ];
                }

                // Filter out empty parameters to avoid unnecessary query string entries
//$queryParams = array_filter($queryParams);
    
                // Build the query string
                $queryString = http_build_query($queryParams);

                // Final URL
                $url = basename(__FILE__) . "?$queryString";

                if (preg_match('/^\..*/', basename($path))) {

                  //$relativePath = str_replace('\\', '\\\\', $relativePath );
    
                  switch (basename($path)) {
                    case '.htaccess':
                      echo '<div style="position: relative; border: 4px dashed #A50F5E;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : "path={$_GET['path']}&") . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/htaccess_file.png" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                        /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                        . '</div>' . "\n";
                      break;
                    case '.babelrc':
                      echo '<div style="position: relative;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : "path={$_GET['path']}&") . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/babelrc_file.png" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                        /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                        . '</div>' . "\n";
                      break;
                    case '.gitignore':
                      echo '<div style="position: relative; border: 4px dashed #F05033;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . 'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/gitignore_file.png" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                        /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                        . '</div>' . "\n";
                      break;
                    case '.env.bck':
                    case '.env':
                      echo '<div style="position: relative;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . 'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/env_file.png" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                        /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                        . '</div>' . "\n";
                      break;
                    default:
                      echo '<div style="position: relative;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/env_file.png" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                        /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                        . '</div>' . "\n";
                      break;
                  }
                } elseif (preg_match('/^package(?:-lock)?\.(json)/', basename($path))) {
                  echo '<div style="position: relative; border: 4px dashed #E14747;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\'); document.getElementById(\'app_npm-container\').style.display=\'block\';">';

                  switch (basename($path)) {
                    case 'package.json':
                      echo '<img src="resources/images/package_json_file.png" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                        . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                        . '</div>' . "\n";
                      break;
                    case 'package-lock.json':
                      echo '<img src="resources/images/package-lock_json_file.png" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                        . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                        . '</div>' . "\n";
                      break;
                  }

                } elseif (preg_match('/^composer(?:-setup)?\.(json|lock|php|phar)/', basename($path))) {
                  echo '<div style="position: relative;"><div style="position: relative; border: 4px dashed #6B4329;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\'); document.getElementById(\'app_composer-container\').style.display=\'block\';">';

                  switch (basename($path)) {
                    case 'composer.json':
                      echo '<img src="resources/images/composer_json_file.gif" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\');">' . basename($path) . '</a>'
                        . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                        . '</div></div>' . "\n";
                      break;
                    case 'composer.lock':
                      echo '<img src="resources/images/composer_lock_file.gif" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                        . (isset($errors['COMPOSER-VALIDATE-LOCK']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                        /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                        . '</div></div>' . "\n";
                      break;
                    case 'composer.phar':
                      echo '<img src="resources/images/phar_file.png" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                        /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                        . '</div></div>' . "\n";
                      break;
                    default:
                      echo '<img src="resources/images/composer_php_file.gif" width="40" height="50" /></a><br />'
                        . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                        . '</a></div></div>' . "\n";
                      break;
                  }
                } elseif (preg_match('/^.*\.js$/', basename($path))) {
                  switch (basename($path)) {
                    case 'webpack.config.js':
                      echo '<a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '"><img src="resources/images/webpack_config_js_file.png" width="40" height="50" /></a><br />' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                      break;
                    default:
                      echo '<a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/js_file.png" width="40" height="50" /></a><br />' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                      break;
                  }

                } elseif (preg_match('/^.*\.md$/', basename($path))) {
                  echo '<div style="position: relative; border: 4px dashed #8BBB4B;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/md_file.png" width="40" height="50" /></a><br />' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';

                } elseif (preg_match('/^.*\.php$/', basename($path))) {
                  if (preg_match('/^project\.php/', basename($path)))
                    echo '<div style="position: relative; border: 4px dashed #2C88DA;"><a style="position: relative;" href="' . (isset($_GET['project']) ? 'project#!' : '#') . '" onclick="document.getElementById(\'app_project-container\').style.display=\'block\';"><div style="position: absolute; left: -60px; top: -20px; color: red; font-weight: bold;">' . (isset($_GET['project']) ? '' : '') . '</div><img src="resources/images/project-icon.png" width="40" height="50" /></a><br /><a href="' . basename(__FILE__) . '?' . (isset($_GET['project']) ? 'project#!' : (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'app=ace_editor' . '&file=' . basename($path)) . '" ' . (isset($_GET['project']) ? 'onclick="document.getElementById(\'app_ace_editor-container\').style.display=\'block\';"' : 'onclick="handleClick(event, \'' . basename($relativePath) . '\')"') . '>' . basename($path) . '</a></div>';
                  elseif (basename($path) == 'phpunit.php')
                    echo '<a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . 'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/phpunit_php_file.png" width="40" height="50" /></a><br />' . '<a href="' . basename(__FILE__) . '?file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                  elseif (basename($path) == 'bootstrap.php')
                    echo '<div style="position: relative; border: 4px dashed #897AE3;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . '&path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a><br />' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';
                  elseif (basename($path) == 'server.php')
                    echo '<div style="position: relative; border: 4px dashed #897AE3;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . '&path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a><br />' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';
                  else
                    echo '<a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . 'path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a><br />' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';

                } elseif (basename($path) == 'LICENSE' && preg_match('/^\/mnt\/c\/www\/LICENSE$/', $path)) {
                  /* https://github.com/unlicense */
                  echo '<div style="position: relative;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path']) . '&app=ace_editor' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/license_file.png" width="40" height="50" /></a><br />un' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                    . '.org</a>'
                    /* . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                    . '</div>' . "\n";
                } elseif (basename($path) == basename(ini_get('error_log')))
                  echo '<div style="position: relative;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path']) . '&app=ace_editor' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">'
                    . '<img src="resources/images/error_log.png" width="40" height="50" /></a><br /><a id="app_php-error-log" href="' . (APP_URL_BASE['query'] != '' ? '?' . APP_URL_BASE['query'] : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') . /* '?' . basename(ini_get('error_log')) . '=unlink' */ '" style="text-decoration: line-through; background-color: red; color: white;"></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                    . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '</a><div style="position: absolute; top: -8px; left: 8px; color: red; font-weight: bold;"><a href="#" onclick="$(\'#requestInput\').val(\'unlink error_log\'); $(\'#requestSubmit\').click();">[X]</a></div>' : '')
                    . '</div>' . "\n";
                elseif (preg_match('/^.*\.exe$/', basename($path))) {
                  echo '<div style="position: relative; border: 4px dashed #8BBB4B;"><a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . 'download&' . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '"><img src="resources/images/exe_file.png" width="40" height="50" /></a><br />' . '<a href="' . htmlspecialchars($url) . '">' . basename($path) . '</a></div>';

                } else
                  echo '<a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="resources/images/php_file.png" width="40" height="50" /></a><br />' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
              }
              echo "</td>\n";
              if ($count >= 6)
                echo '</tr><tr>';
              elseif ($lastKey == $key)
                echo '</tr>'; // ($path == end($paths))
    
              if (isset($count) && $count >= 6)
                $count = 1;
              else
                $count++;
            }
          ?>
      </table>
    <?php }

  }
  $returnValue = ob_get_contents();
  ob_end_clean();
  return $returnValue;
};

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

  //if (isset($_POST['cmd']) && $_POST['cmd'] != '')
  // require_once 'app.console.php';

  if (isset($_GET['app']) && $_GET['app'] == 'ace_editor') {
    require_once 'ui.ace_editor.php';
  }

  if (isset($_POST['cmd'])) {

    chdir(APP_PATH . APP_ROOT);

    $output = [];

    //$_SERVER['SOCKET'] = fsockopen(SERVER_HOST, SERVER_PORT, $errno, $errstr, 5);

    if ($_POST['cmd'] && $_POST['cmd'] != '')
      if (preg_match('/^chdir\s+(:?(.*))/i', $_POST['cmd'], $match)) {
        //exec($_POST['cmd'], $output);
        //die(header('Location: ' . APP_URL_BASE . '?app=text_editor&filename='.$_POST['cmd']));
        //$output[] = "Changing directory to " . $path;

        //$output[] = var_dump(get_required_files()); //'Location: ' . APP_PATH . APP_ROOT . rtrim(trim(preg_match('#(?:\.\./)+#', $match[1]) ? '/' : $match[1]), DIRECTORY_SEPARATOR);

        /**/
        error_log("Path: $match[1]");

        if (realpath($path = APP_PATH . APP_ROOT . rtrim(trim(preg_match('#(?:\.\./)+#', $match[1]) ? '/' : $match[1]), DIRECTORY_SEPARATOR))) {
          // Define the root directory you don't want to go past

          // Regular expression to normalize the path
          $match[1] = preg_replace('#(?:\.\./)+#', '../', $match[1]);

          // Optional: Ensure path does not start with excessive "../"
          //$maxUpLevels = 1; // Adjust as needed
          //$match[1] = preg_replace('#^((?:\.\./){' . ($maxUpLevels + 1) . ',})#', str_repeat('../', $maxUpLevels), $filteredPath);
          $rootDir = realpath(APP_PATH . APP_ROOT . ($match[1] ?? '../'));

          // Check if the resolved path is within the allowed root
          if (strpos($path, $rootDir) === 0 && strlen($path) >= strlen($rootDir)) {
            // Proceed with your existing logic if the path is valid
            $resultValue = (function () use ($path, $tableGen): string{
              define('APP_CLIENT', $path);

              $basePath = rtrim(APP_PATH . APP_ROOT, DIRECTORY_SEPARATOR);

              $path = preg_replace('#^' . preg_quote($basePath, '#') . '/?#', '', $path);

              // Replace the escaped APP_PATH and APP_ROOT with the actual directory path
              if (realpath(preg_replace('#^' . preg_quote($basePath, '#') . '/?#', '', $path)) == realpath($basePath)) {
                $_GET['path'] = '';
              } elseif (
                realpath(
                  $newPath = preg_replace(
                    '#^' . preg_quote(rtrim(APP_PATH . APP_ROOT . ($_GET['domain'] ?? ''), DIRECTORY_SEPARATOR), '#') . '/?#',
                    '',
                    $path
                  )
                )
              ) {
                ($newPath === '../' ? $_GET['path'] = '' : $_GET['path'] = "$newPath/");  ///  preg_replace('/' . preg_quote(APP_PATH . APP_ROOT, DIRECTORY_SEPARATOR) . '/', '', $path)
              } else {
                $_GET['path'] = '';
              }
              //$_GET['path'] =  . '>>' . APP_CLIENT . ($_GET['domain'] ?? '');

              ob_start();
              //if (is_file($include = APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php'))
              //if (isset($_ENV['COMPOSER']['AUTOLOAD']) && (bool) $_ENV['COMPOSER']['AUTOLOAD'] === TRUE)
              //  require_once $include;

              //require_once 'app.directory.php';
              //dd(get_required_files(), false);
              isset($tableGen) and $tableValue = $tableGen();
              ob_end_clean();
              return $tableValue ?? ''; // $app['directory']['body'];
            })();
            $output[] = (string) $resultValue;
          } else {
            // Handle the case where the path is trying to go past the root directory
            $output[] = "Cannot go past the root directory: $rootDir >> $path";
          }
        }
        /*
            if ($path = realpath(APP_PATH . APP_ROOT . rtrim(trim($match[1]), '/'))) {
              // Define the root directory you don't want to go past
              $root_dir = realpath(APP_PATH . APP_ROOT);
        
              // Resolve the parent directory path
              $parent_dir = realpath("$path/../");
        
              // Check if the parent directory is within the allowed root
              if (strpos($parent_dir, $root_dir) === 0 && strlen($parent_dir) >= strlen($root_dir)) {
                // Proceed with your existing logic if the path is valid
                $resultValue = (function() use ($path): string {
                  // Replace the escaped APP_PATH and APP_ROOT with the actual directory path
                  if (realpath($_GET['path'] = preg_replace('/' . preg_quote(APP_PATH . APP_ROOT, '/') . '/', '', $path)) == realpath(APP_PATH . APP_ROOT))
                    $_GET['path'] = '';
                  ob_start();
                  require 'app.directory.php';
                  $tableValue = $tableGen();
                  ob_end_clean();
                  return $tableValue; // $app['directory']['body'];
                })();
                $output[] = (string) $resultValue;
              } else {
                // Handle the case where the path is trying to go past the root directory
                $output[] = "Cannot go past the root directory: $root_dir";
              }
            }
        */
      } else if (preg_match('/^edit\s+(:?(.*))/i', $_POST['cmd'], $match)) {
        //exec($_POST['cmd'], $output);
        //die(header('Location: ' . APP_URL_BASE . '?app=text_editor&filename='.$_POST['cmd']));

        //$output[] = 'This works ... ' . dd(get_required_files(), false) . dd($_POST, false) . APP_PATH . APP_ROOT; // APP_ROOT; 

        // . DIRECTORY_SEPARATOR . ($_GET['domain'] ?? '')
        // . DIRECTORY_SEPARATOR . ($_GET['path'] ?? '')

        //(isset($_GET['path']) ? (empty($_GET['client']) ? '' : 'clientele1' . DIRECTORY_SEPARATOR . (isset($_GET['client']) || isset($_GET['domain']) ? /*'clientele' . DIRECTORY_SEPARATOR . */ (!isset($_GET['client']) ? '' : $_GET['client']) . DIRECTORY_SEPARATOR : '') . (!isset($_GET['domain']) ? '' : $_GET['domain'])) /*APP_BASE[''] . $_GET['client'] . '/'*/ : APP_ROOT  ) . 

        $rootFilter = '';
        $filePath = APP_PATH;

        // Determine the root filter based on client and domain
        if (!empty($_GET['client'])) {
          $rootFilter = 'clientele' . DIRECTORY_SEPARATOR . $_GET['client'] . DIRECTORY_SEPARATOR;
          if (isset($_GET['domain'])) {
            $rootFilter .= $_GET['domain'] . DIRECTORY_SEPARATOR;
          }
        } elseif (isset($_GET['domain'])) {
          $rootFilter = 'clientele' . DIRECTORY_SEPARATOR . $_GET['domain'] . DIRECTORY_SEPARATOR;
        }

        // Add project-specific root filter if applicable
        if (isset($_GET['project'])) {
          $rootFilter = 'projects' . DIRECTORY_SEPARATOR . $_GET['project'] . DIRECTORY_SEPARATOR;
        }

        // Add path to the file path
        if (isset($_GET['path'])) {
          $filePath .= $rootFilter . $_GET['path'] . DIRECTORY_SEPARATOR;
        } else {
          $filePath .= $rootFilter;
        }

        // Trim and clean the match path
        $matchPath = preg_replace('#^' . preg_quote($rootFilter, '#') . '/?#', '', $match[1] ?? '');
        $filePath .= trim($matchPath);

        // Check if the file exists and read its content
        $output[] = (is_file($filePath)) ? file_get_contents($filePath) : "File not found: $filePath";



        //$root_filter = '';
        //$output[] = is_file($file = APP_PATH . (empty($_GET['client']) ? '' : $root_filter = 'clientele' . DIRECTORY_SEPARATOR . (isset($_GET['client']) || isset($_GET['domain']) ? /*'clientele' . DIRECTORY_SEPARATOR . */ (!isset($_GET['client']) ? '' : $_GET['client']) . DIRECTORY_SEPARATOR : '') . (!isset($_GET['domain']) ? '' : $_GET['domain'])) . DIRECTORY_SEPARATOR . (!isset($_GET['path']) ? (!isset($_GET['project']) ? '' : $root_filter = 'projects' . DIRECTORY_SEPARATOR . $_GET['project']) : $_GET['path']) . DIRECTORY_SEPARATOR . trim(preg_replace('#^' . preg_quote($root_filter, '#') . '/?#', '', $match[1]))) ? file_get_contents($file) : "File not found: $file";
      }

    if (isset($output) && is_array($output)) {
      switch (count($output)) {
        case 1:
          echo /*(isset($match[1]) ? $match[1] : 'PHP') . ' >>> ' . */ join("\n... <<< ", $output);
          break;
        default:
          echo join("\n", $output);
          break;
      } // . "\n"
      //$output[] = 'post: ' . var_dump($_POST);
      //else var_dump(get_class_methods($repo));
    }
    $output = [];
    //echo $buffer;
    unset($match);
    require_once 'app.console.php';
  }
  Shutdown::setEnabled(true)->setShutdownMessage(function () { })->shutdown();
}

ob_start(); ?>

<?php $app[$directory]['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
<div id="app_directory-container"
  style="position: absolute; display: <?= isset($_GET['debug']) || isset($_GET['project']) || isset($_GET['path']) ? /*'block'*/ 'none' : 'none'; ?>; background-color: rgba(255,255,255,1); height: 70vh; top: 90px; margin-left: auto; margin-right: auto; left: 0; right: 0; width: 700px; overflow-x: hidden; overflow-y: scroll; padding: 10px;">

  <?= $tableGen(); /*'';*/ ?>
</div>
<?php $app[$directory]['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

let DirQueryParams = '';

function handleClick(event, path) {
// Prevent the default hyperlink action
event.preventDefault();

// Check for APP_DEBUG or any other condition
if (typeof APP_DEBUG !== 'undefined' && APP_DEBUG === true) {
// Allow the hyperlink to work as usual
window.location.href = event.currentTarget.href;
} else {
document.getElementById('app_directory-container').style.display = 'block';

if (matches = path.match(/^.*\/$/gm)) {

// Use jQuery to update the request input and submit
$('#requestInput').val('chdir ' + path);
DirQueryParams = event.currentTarget.href;
$('#requestSubmit').click();

} else {
DirQueryParams = event.currentTarget.href;

// Update the hidden ace_path input with the file path
$('input[name="ace_path"]').val(path); // path.substring(0, path.lastIndexOf('/'))

$('form[name="ace_form"]').attr('action', DirQueryParams);

document.getElementsByClassName('ace_text-input')[0].name = 'ace_contents';

// Use jQuery to update the request input and submit
$('#requestInput').val('edit ' + path);

$('#requestSubmit').href = event.currentTarget.href;
$('#requestSubmit').click();


//document.getElementsByClassName('ace_text-input')[0].value = 'hello world';
}

if (!isFixed) isFixed = false;
show_console();

// Optionally, you could update the div directly if needed
// $('#app_directory-container').html('Loading ' + folder + '...');
}
}
<?php
$app[$directory]['script'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

  <?php
  // (check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_URL . 'resources/js/tailwindcss-3.3.5.js')?
// Path to the JavaScript file
  $path = APP_PATH . APP_BASE['resources'] . 'js/tailwindcss-3.3.5.js';

  // Create the directory if it doesn't exist
  is_dir(dirname($path)) or mkdir(dirname($path), 0755, true);

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
        file_put_contents($path, $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
      }
    } ?>

  <script
    src="<?= defined('APP_IS_ONLINE') && APP_IS_ONLINE && check_http_status($url) ? substr($url, strpos($url, parse_url($url)['host']) + strlen(parse_url($url)['host'])) : substr($path, strpos($path, dirname(APP_BASE['resources'] . 'js'))) ?>"></script>

  <style type="text/tailwindcss">
    <?= $app[$directory]['style']; ?>
</style>
</head>

<body>
  <?= $app[$directory]['body']; ?>

  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="resources/js/jquery/jquery.min.js"></script> -->
  <script>
    <?= $app[$directory]['script']; ?>
  </script>
</body>

</html>
<?php $app[$directory]['html'] = ob_get_contents();
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'directory' && APP_DEBUG)
  die($app[$directory]['html']);
