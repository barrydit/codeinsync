<?php
defined('APP_BASE') or
    require_once APP_PATH . 'config/constants.paths.php';
    
global $errors;

defined('APP_ROOT') or
    define('APP_ROOT', APP_BASE['root'] ?? '');

if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"]))
    if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
        chdir('../');
        if ($path = realpath(/*'config' . DIRECTORY_SEPARATOR . */ 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.php')) // is_file('bootstrap.php')
            require_once $path;

        //die(var_dump(APP_PATH));
    } else
        die(var_dump("Path was not found. file=$path"));
//else {}

defined('APP_URL_BASE') or
    require_once APP_PATH . 'config/constants.url.php';

require_once APP_PATH . 'config' . DIRECTORY_SEPARATOR . 'config.php';

//require_once APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php';
//require_once APP_PATH . APP_ROOT . 'app' . DIRECTORY_SEPARATOR . 'composer.php';

// !isset($_GET['path']) and $_GET['path'] = '';

//namespace App\Directory;

if (preg_match('/^([\w\-.]+)\.php$/', basename(__FILE__), $matches))
    ${$matches[1]} = $matches[1];

if (false) { ?>
    <style>
    <?php }
ob_start(); ?>
    /* app-wide */
    .app-container {
        position: relative;
    }

    /* mark this app as fixed (not floating) */
    .app-fixed {
        position: relative;
        /* not absolute, so it flows with the page */
        width: 100%;
        max-width: 100%;
        margin: 0;
        /* no floating margins */
        left: auto;
        top: auto;
        /* ignore any drag-positioning */
    }

    /* header/content */
    .app-header {
        display: flex;
        gap: .75rem;
        align-items: center;
        justify-content: space-between;
        padding: .5rem .75rem;
        border-bottom: 1px solid #ddd;
    }

    .app-title {
        font-weight: 600;
    }

    .app-content {
        padding: .75rem;
    }

    /* a simple responsive grid for entries */
    .dir-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: .75rem;
    }

    .dir-item {
        display: flex;
        gap: .5rem;
        align-items: center;
        padding: .5rem;
        border: 1px solid #eee;
        border-radius: .5rem;
        cursor: pointer;
    }

    .dir-item:hover {
        background: #fafafa;
    }

    .dir-item .icon {
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
    }

    .dir-item .name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    <?php
    $UI_APP['style'] = ob_get_contents();
    ob_end_clean();
    if (false) { ?>
    </style><?php }

    /**
     * Generates a table.
     *
     * @return string
     */
    $group_type = $_POST['group_type'] ?? null;
    $tableGen = function () use ($group_type): string {
        ob_start(); ?>

    <div id="info"
        style="position: fixed; display: none; width: 570px; height: 500px; top: calc(50% - 300px); /* 500 / 2 */
left: calc(50% - 265px); /* 1207 / 2 */ /*transform: translate(-50%, -50%);*/ border: 5px solid #000; background-repeat: no-repeat; background-color: #FFFFFF; z-index:99;">
        <div
            style="position: absolute; display: block; background-color: #FFFFFF; z-index: 1; right: 0px; margin-top: -20px;">
            [<a href="#" onclick="document.getElementById('info').style.display = 'none';">x</a>]</div>
        <form method="post" action="/?path" enctype="multipart/form-data">
            <div class="directory-grid">
                <div class="directory-entry">
                    <div style="position: relative;">
                        <a href="#!" onclick="handleClick(event, '../');">
                            <img src="assets/images/new_file.png" width="58" height="69" />
                            New File</a>
                    </div>
                </div>
                <div class="directory-entry">
                    <div style="position: relative;">
                        <a href="#!" onclick="handleClick(event, '../');">
                            <img src="assets/images/git_clone.png" width="69" height="69" />
                            Git<br>(clone)</a>
                    </div>
                </div>
                <div class="directory-entry">
                    <div style="position: relative;">
                        <a href="#!" onclick="handleClick(event, '../');">
                            <img src="assets/images/ftp_conn.png" width="82" height="71" />
                            FTP</a>
                    </div>
                </div>
                <div class="directory-entry">
                    <div style="position: relative;">
                        <a href="#!" onclick="handleClick(event, '../');">
                            <img src="assets/images/www_curl.png" width="75" height="81" />
                            www<br>(curl)</a>
                    </div>
                </div>
                <div class="directory-entry">
                    <div style="position: relative;">
                        <a href="#!" onclick="handleClick(event, '../');">
                            <img src="assets/images/clients.png" width="74" height="79" />
                            Clients</a>
                    </div>
                </div>
                <div class="directory-entry">
                    <div style="position: relative;">
                        <a href="#!" onclick="handleClick(event, '../');">
                            <img src="assets/images/projects.png" width="74" height="79" />
                            Projects</a>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <?php
    //$path = APP_PATH . APP_ROOT . ($_GET['path'] ?? '');
//dd($_GET);
// Base navigation path

    $base = rtrim(APP_PATH, '/');               // e.g., /mnt/c/www
    $root = defined('APP_ROOT') ? trim(APP_ROOT, '/') : null;
    $client = $_GET['client'] ?? null;
    $domain = $_GET['domain'] ?? null;
    $path = $_GET['path'] ?? null;

    $segments = [];

    // Segment 1: APP_PATH (always shown)
    $segments[] = '[ <a href="' . /* basename(__FILE__) .*/ '?path=" onclick="handleClick(event, \'./\')">' . $base . '/</a> ]';

    // Segment 2: if APP_ROOT is defined
    if ($root) {
        // Split APP_ROOT to extract "projects/clients" + "000-Doe,John/domain"
        $parts = explode('/', $root);

        // Static prefix (projects/clients)
        $static = implode('/', array_slice($parts, 0, 2));
        $staticPath = "$base/$static";

        $segments[] = '[ <a href="' . /* basename(__FILE__) .*/ '?client=" onclick="handleClick(event, \'' . basename($static) . '/\')">' . '(projects/)' . basename($static) . '</a> ]';

        // Dynamic client/domain part
        $dynamic = implode('/', array_slice($parts, 2));
        $finalPath = "$staticPath/$dynamic";

        $segments[] = '[ <a href="#" class="breadcrumb" data-path="' . $finalPath . '">' . $dynamic . '/</a> ]';

    } elseif ($client || $domain) {
        // Only show static "projects/clients/" if APP_ROOT is not defined
        $clientBase = 'projects/clients';
        $clientPath = "$base/$clientBase";
        $segments[] = '[ <a href="' . /* basename(__FILE__) .*/ '?client=" onclick="handleClick(event, \'' . $clientBase . '/\')">' . $clientBase . '/</a> ]';
    }

    // Optional fallback: If path is set and APP_ROOT not defined
    if (!$root && $path) {
        $segments[] = '[ <a href="' . /* basename(__FILE__) .*/ '?path=' . urlencode($path) . '" onclick="handleClick(event, \'' . addslashes($path) . '\')">' . rtrim($path, '/') . '/</a> ]';
    }

    echo '<div id="breadcrumb" style="height: 25px; display: inline;"><br /><br />' . implode('', $segments) . '</div>';


    /*
      $basePath = rtrim(APP_PATH, DIRECTORY_SEPARATOR);
      $clientPath = $_GET['client'] ?? null;
      $domainPath = $_GET['domain'] ?? null;
      $projectPath = $_GET['project'] ?? null;
      $relativePath = isset($_GET['path']) ? trim($_GET['path'], DIRECTORY_SEPARATOR) : null;

      // Base link
      $navigation = '<div style="height: 25px; display: inline;"><br /><br />[[ <a href="#" onclick="handleClick(event, \'./\');">' . APP_PATH . '</a> ]' . (APP_ROOT !== '' ? '[ <a href="#" onclick="handleClick(event, \'../\');">' . APP_ROOT . '</a> ]' : '') . (isset($_REQUEST['path']) && $_REQUEST['path'] !== '' ? '[ <a href="#" onclick="handleClick(event, \'../\');">' . (APP_BASE[rtrim($_REQUEST['path'], DIRECTORY_SEPARATOR)] ?? $_REQUEST['path']) . '</a> ]' : '') . '] ' . '</div><div style="display: inline;">' . '</div>';
      echo $navigation;
    */
    /*  $cwd = getcwd();
    (str_starts_with($cwd, APP_PATH)
      ? substr($cwd, strlen(APP_PATH))
      : '')
    dd($_GET, false);

    dd(APP_PATH . APP_ROOT, false);
      dd(get_required_files(), false);
    dd($_POST, false);*/
    //dd(APP_CLIENT, false);

    if (isset($_GET['path']) && preg_match('/^project\/?/', $_GET['path']) || isset($_GET['project']) && empty($_GET['project'])) {
        if (isset($_SERVER['HOME']) && readlinkToEnd($_SERVER['HOME'] . DIRECTORY_SEPARATOR . APP_BASE['projects'] . DIRECTORY_SEPARATOR) == APP_BASE['projects'] || realpath(APP_BASE['projects'])) { ?>
            <div style="text-align: center; border: none;" class="text-xs">
                <a class="pkg_dir" href="#" onclick="document.getElementById('app_project-container').style.display='block';">
                    <img src="assets/images/project-icon.png" width="50" height="32" style="" /></a><br /><a
                    href="?project">./project/</a>
            </div>
            <table width="" style="border: none;">
                <tr style=" border: none;">
                    <?php $links = array_filter(glob(APP_PATH . /*'../../'.*/ APP_BASE['projects'] . '*'), 'is_dir');

                    $count = 1;

                    if (empty($links))
                        echo "<hr />\n"; // label="     "
                    else  //dd($links);
                        $old_links = $links;

                    while ($link = array_shift($links)) {
                        $old_link = $link;
                        $link = basename($link);


                        echo "<td style=\"text-align: center; border: none;\" class=\"text-xs\">\n";
                        echo "<a class=\"pkg_dir\" href=\"?project=$link\"><img src=\"assets/images/directory.png\" width=\"50\" height=\"32\" style=\"\" /><br />$link</a><br /></td>\n";
                        if ($count >= 7)
                            echo '</tr><tr>';
                        elseif ($old_link == end($old_links))
                            echo '</tr>';

                        if (isset($count) && $count >= 7)
                            $count = 1;
                        else
                            $count++;
                    }

        } ?>
        </table>
        <!--
      <li>
      <?php if (isset($_SERVER['HOME']) && readlinkToEnd($_SERVER['HOME'] . DIRECTORY_SEPARATOR . APP_BASE['projects'] . DIRECTORY_SEPARATOR) == APP_BASE['projects'] || realpath(APP_BASE['projects'])) { ?>
      <a href="projects/">project/</a>
        <ul style="padding-left: 10px;">
          <form action method="GET">
            <select id="sproject" name="project" style="color: #000;">
      <?php while ($link = array_shift($links)) {
                      $link = basename($link); // Get the directory name from the full path
                      if (is_dir(APP_PATH . /*'../../'.*/ APP_BASE['projects'] . $link))
                          echo '  <option value="' . $link . '" ' . (current($_GET) == $link ? 'selected' : '') . '>' . $link . '</option>' . "\n";
                  } ?>
            </select>
          </form>
      </ul>
      <?php } ?></li>
      -->
    <?php } elseif (isset($_GET['application'])) { ?>

        <?php if (readlinkToEnd($_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'applications') == APP_BASE['applications']) {
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
                                . '<img src="assets/images/app_file.png" width="50" height="32" style="" /><br />' . $link . '</a><br />'
                                . '</td>' . "\n";

                            if ($count >= 3)
                                echo '</tr><tr>';
                            elseif ($old_link == end($old_links))
                                echo '</tr>';

                            if (isset($count) && $count >= 3)
                                $count = 1;
                            else
                                $count++;
                        } ?>
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
            <?php
            //if (empty($links)) {
            //  echo '<option value="" selected>---</option>' . "\n"; // label="     "
            //} else  //dd($links);
            $count = 1;
            $old_links = $links;
            while ($link = array_shift($links)) {
                $old_link = $link;
                $link = basename($link);
                echo '<tr style=" border: none;">' . "\n";

                echo '<td style="text-align: center; border: none;" class="text-xs">' . "\n";

                echo '<a class="pkg_dir" href="?application=' . $link . '">'
                    . '<img src="assets/images/directory.png" width="50" height="32" style="" /><br />' . $link . '</a><br />'
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
        <?php if (isset($_SERVER['HOME']) && readlinkToEnd($_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'clients' . DIRECTORY_SEPARATOR) == APP_BASE['clients'] || realpath(APP_PATH . 'clients')) { ?>
            <h3>&#9660; Domains: </h3>
            <table width="" style="border: none;">
                <tr style="border: none;">
                    <?php
                    $count = 1;

                    //if (empty($links)) {
                    //  echo '<option value="" selected>---</option>' . "\n"; // label="     "
                    //} else  //dd($links);
        
                    $links = array_filter(glob(APP_PATH . 'clients' . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR), function ($link) {
                        // Apply regex to the basename (last part of the path)
                        return preg_match('/^(?!\d{3}-)[a-z0-9\-]+\.[a-z]{2,6}$/i', basename($link));
                    });

                    $old_links = $links;
                    while ($link = array_shift($links)) {
                        $old_link = $link;
                        $link = basename($link);

                        echo "<td style=\"text-align: center; border: none;\" class=\"text-xs\">\n"
                            . "<a class=\"pkg_dir\" href=\"?" . (isset($_ENV['DEFAULT_CLIENT']) && $_ENV['DEFAULT_CLIENT'] == $link ? '' : "domain=$link") . "\" onclick=\"\"><img src=\"assets/images/directory.png\" width=\"50\" height=\"32\" style=\"\" /><br />$link/</a><br /></td>\n";

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

                $links = array_filter(glob(APP_PATH . /*'../../'.*/ APP_BASE['clients'] . $status . '*'), 'is_dir');
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
                                . "<a class=\"pkg_dir\" href=\"?" . (isset($_ENV['DEFAULT_CLIENT']) && $_ENV['DEFAULT_CLIENT'] == $link ? '' : "client=$link") . "\" onclick=\"\"><img src=\"assets/images/directory.png\" width=\"50\" height=\"32\" style=\"\" /><br />$statusCode-Client$count/</a><br /></td>\n";

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
                <?php echo "<a class=\"pkg_dir\" style=\"border: 1px dashed blue;\" href=\"?client=\">Missing directory.<br/>"
                    . "<img src=\"assets/images/directory.png\" width=\"60\" height=\"42\" style=\"\" /><br />"
                    . "Create <input type=\"text\" style=\"text-align: right;\"size=\"7\" name=\"cients\" value=\"clients/\"></a><br />\n"; ?>
            </div>

        <?php }
    } else {

        //if(isset($_GET['client']) && !empty($_GET['client']))
        //  $path = APP_BASE['clients'] . $_GET['client'] . '/' . (isset($_GET['domain']) && !empty($_GET['domain']) ? $_GET['domain'] . '/' : '');

        //elseif(isset($_GET['project']) && !empty($_GET['project']))
        //  $path = APP_BASE['projects'] . $_GET['project'] . '/';


        // >>>

        //    $path = defined('APP_ROOT') && APP_ROOT ? APP_PATH . APP_ROOT : (APP_ROOT == '' ? APP_PATH . (isset($_GET['client']) ? APP_BASE['clients'] . $_GET['client'] . DIRECTORY_SEPARATOR : '') . (isset($_GET['client']) || isset($_GET['domain']) ? (isset($_GET['domain']) ? $_GET['domain'] . DIRECTORY_SEPARATOR : '') : (isset($_GET['path']) ? '' : 'vendor' . DIRECTORY_SEPARATOR . (isset($_GET['client']) ? $_GET['client'] . DIRECTORY_SEPARATOR : ''))) : APP_PATH . APP_ROOT);
        $path = APP_PATH;

        if (defined('APP_ROOT') && APP_ROOT) {
            $path .= APP_ROOT;
        } elseif (APP_ROOT === '') {
            // Handle client-specific path
            if (isset($_GET['client'])) {
                $path .= APP_BASE['clients'] . $_GET['client'] . DIRECTORY_SEPARATOR;
            }

            // Add domain to the path if applicable
            if (isset($_GET['domain'])) {
                $path .= APP_BASE['clients'] . $_GET['domain'] . DIRECTORY_SEPARATOR;
            } elseif (!isset($_GET['client']) && isset($_GET['path']) && $_GET['path'] == 'vendor') {
                // Default to vendor path if no domain or client is set
                $path .= 'vendor' . DIRECTORY_SEPARATOR;
                if (isset($_GET['client'])) {
                    $path .= $_GET['client'] . DIRECTORY_SEPARATOR;
                }
            }
        } else {
            $path .= APP_ROOT;
        }

        if (!realpath($path . ($_GET['path'] ?? ''))) { ?>

            <?= "<br /><br />Missing directory $path"; ?>

        <?php } else { // 

            if (isset($_GET['path']) && preg_match('/^vendor\/?/', $_GET['path'])) {

                //if ($_ENV['COMPOSER']['AUTOLOAD'] == true)
                require_once APP_PATH . APP_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
                require_once APP_PATH . 'api' . DIRECTORY_SEPARATOR . 'composer.php'; ?>
                <!-- iframe src="composer_pkg.php" style="height: 500px; width: 700px;"></iframe -->
                <div style="width: 700px; ">
                    <div style="display: inline-block; width: 350px;"><a href="#!"
                            onclick="handleClick(event, 'vendor/'); document.getElementById('app_composer-container').style.display='block';">Composers</a>
                        Vendor Packages [Installed] List</div>
                    <div style="display: inline-block; text-align: right; width: 300px;">
                        <form
                            action="<?= !defined('APP_URL') ? '//' . APP_DOMAIN . APP_URL_PATH . '?' . http_build_query(APP_QUERY, '', '&amp;') : APP_URL . '?' . http_build_query(APP_QUERY, '', '&amp;') ?>"
                            method="POST">
                            <input id="RequirePkg" type="text" title="Enter Text and onSelect" list="RequirePkgs"
                                placeholder="[vendor]/[package]" name="composer[package]" value onselect="get_package(this);"
                                autocomplete="off" style=" margin-top: 4px;">
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
            <?php }
            /* var_dump(COMPOSER_VENDORS); null; */ //dd($_GET, false); 
            ?>
            <div class="directory-grid">
                <?php

                if (isset($_REQUEST['path']) && $_REQUEST['path'] !== '') {
                    /*
                              echo <<<END
                    <div class="directory-entry">
                    <div
                      style="position: relative; display: block;">
                    END;

                      echo '<a href="?' . (isset($_REQUEST['client']) ? 'client=test' : 'testing') . '&domain={$_REQUEST['domain']}&path={$_REQUEST['path']}" onclick="handleClick(event, '../{$_REQUEST['path']}');">

                    echo <<<END
                        <img src="assets/images/directory.png" width="50" height="32" />
                        ../</a>
                    </div>
                    </div>
                    END;
                    */
                }

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

                        echo '<div class="directory-entry">' . "\n";
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
                                        . '<img src="assets/images/directory-git.png" width="50" height="32" /></a>'
                                        . '<a href="' . /* basename(__FILE__) .*/ '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\'); document.getElementById(\'app_git-container\').style.display=\'block\';">' . basename($path) . '/</a></div>' . "\n";
                                    break;
                                case 'applications':
                                    echo '<div style="position: relative;">'
                                        . '<a href="?application" onclick="document.getElementById(\'app_application-container\').style.display=\'block\';"><img src="assets/images/directory-application.gif" width="50" height="32" /></a>'
                                        . '<a href="' . /* basename(__FILE__) .*/ '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\');">' . basename($path)  // "?path=' . basename($path) . '"
                                        . '/</a></div>' . "\n";
                                    break;
                                case 'clients': //case 'clientele':
                                    echo '<div style="position: relative; border: 4px dashed orange;">'
                                        . '<a href="' . /* basename(__FILE__) .*/ '?' . $url /*. (!defined('APP_ROOT') || empty(APP_ROOT) ? '' : (array_key_first($_GET) == 'client' ? 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '') : (array_key_first($_GET) == 'project' ? 'project=' . $_GET['project'] . '&' : ''))) . 'path=' . $relativePath*/ . '" onclick="handleClick(event, \'' . $relativePath . '\')">'
                                        // (!isset($_GET['path']) ? '' : $_GET['path'] . ($_GET['path'] == '' ? '' : '/' )) . basename($path)
                                        . '<img src="assets/images/directory.png" width="50" height="32" />' . basename($path) . '/</a></div>' . "\n";
                                    break;
                                case 'node_modules':
                                    echo '<div style="position: relative; border: 4px dashed #E14747;">'
                                        . '<a href="#!" onclick="handleClick(event, \'' . $relativePath . '\');document.getElementById(\'app_npmjs-container\').style.display=\'block\';"><img src="assets/images/directory-npm.gif" width="50" height="32" /></a>'
                                        . '<a href="' . /* basename(__FILE__) .*/ '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\')">' . basename($path)  // "?path=' . basename($path) . '"         
                                        . '/</a></div>' . "\n";
                                    break;
                                case 'data': // case 'database'
                                    echo '<div style="position: relative; border: 4px dashed #2C88DA;">'
                                        . '<a href="' . /* basename(__FILE__) .*/ '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\')">'
                                        // (!isset($_GET['path']) ? '' : $_GET['path'] . ($_GET['path'] == '' ? '' : '/' )) . basename($path)
                                        . '<img src="assets/images/directory.png" width="50" height="32" />' . basename($path) . '/</a></div>' . "\n";
                                    break;
                                case 'projects':
                                    echo '<div style="position: relative; border: 4px dashed #2C88DA;">'
                                        . '<a href="#!" onclick="document.getElementById(\'app_project-container\').style.display=\'block\';"><img src="assets/images/directory-project.gif" width="50" height="32" /></a>'
                                        . '<a href="' . /* basename(__FILE__) .*/ '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\')">' . basename($path)  // "?path=' . basename($path) . '"
                                        . '/</a></div>' . "\n";
                                    break;
                                case 'public':
                                    echo '<div style="position: relative; border: 4px dashed #CC9900;">'
                                        . '<a href="' . /* basename(__FILE__) .*/ '?' . (!defined('APP_ROOT') || empty(APP_ROOT) ? '' : '') . $url . '" onclick="handleClick(event, \'' . $relativePath . '\')">'
                                        // (!isset($_GET['path']) ? '' : $_GET['path'] . ($_GET['path'] == '' ? '' : '/' )) . basename($path)
                                        . '<img src="assets/images/directory.png" width="50" height="32" />' . basename($path) . '/</a></div>' . "\n";
                                    break;
                                case 'vendor':
                                    echo '<div style="position: relative; border: 4px dashed #6B4329;">'
                                        . '<a href="#!" onclick="handleClick(event, \'' . $relativePath . '\');document.getElementById(\'app_composer-container\').style.display=\'block\';">' . '<img src="assets/images/directory-composer.png" width="50" height="32" /></a>'
                                        . '<a href="' . /* basename(__FILE__) .*/ '?' . $url . '" onclick="handleClick(event, \'' . $relativePath . '\');" >' . basename($path)  // "?path=' . basename($path) . '"         
                                        . '/</a></div>' . "\n";
                                    break;
                                default:
                                    // Ensure the path excludes the domain if present in the folder structure
    
                                    $relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);
                                    $queryParams = [];

                                    // Remove domain from relative path if present
                                    if (!empty($_GET['domain'])) {
                                        $relativePath = preg_replace('#^' . preg_quote($_GET['domain'], '#') . '/?#', '', $relativePath);
                                        $queryParams['domain'] = $_GET['domain'];
                                        $queryParams['path'] = defined('APP_ROOT') && APP_ROOT != ''
                                            ? ($_GET['path'] ?? dirname($path) ?? '')
                                            : basename(rtrim($relativePath, '/') ?? basename($path));
                                    }

                                    if (!empty($_GET['client'])) {
                                        $queryParams['client'] = $_GET['client'];

                                        // Ensure domain is set, but not the same as path
                                        $queryParams['domain'] = $_GET['domain'] ?? basename($path);

                                        // Determine path while ensuring it does not duplicate domain
                                        $calculatedPath = $_GET['path'] ?? (
                                            (!empty($_GET['domain']) && strpos($relativePath, $_GET['domain']) === 0)
                                            ? substr($relativePath, strlen($_GET['domain']) + 1)
                                            : basename(rtrim($relativePath, '/'))
                                        );

                                        // Only remove path if it's identical to domain AND not empty
                                        if (!empty($calculatedPath) && $queryParams['domain'] === $calculatedPath) {
                                            $calculatedPath = '';
                                        }

                                        $queryParams['path'] = $calculatedPath;
                                    }

                                    /*
                                                        if (!empty($_GET['client'])) {
                                                          $queryParams['client'] = $_GET['client'];
                                                          $queryParams['domain'] = $_GET['domain'] ?? basename($path);
                                                          $queryParams['path'] = $_GET['path'] ?? (
                                                            (!empty($_GET['domain']) && strpos($relativePath, $_GET['domain']) === 0)
                                                            ? substr($relativePath, strlen($_GET['domain']) + 1)
                                                            : basename(rtrim($relativePath, '/'))
                                                          );
                                                        }

                                                        if (!empty($_GET['client'])) {
                                                          $queryParams['client'] = $_GET['client'];

                                                          // Ensure domain is set, but not the same as path
                                                          $queryParams['domain'] = $_GET['domain'] ?? '';

                                                          // Determine path while ensuring it does not duplicate domain
                                                          $calculatedPath = $_GET['path'] ?? (
                                                            (!empty($_GET['domain']) && strpos($relativePath, $_GET['domain']) === 0)
                                                            ? substr($relativePath, strlen($_GET['domain']) + 1)
                                                            : basename(rtrim($relativePath, '/'))
                                                          );

                                                          // Prevent domain and path from being the same
                                                          if (!empty($queryParams['domain']) && $queryParams['domain'] === $calculatedPath) {
                                                            $calculatedPath = ''; // Cancel out path if it matches domain
                                                          }

                                                          $queryParams['path'] = $calculatedPath;
                                                        }*/

                                    // Remove project from relative path if present
                                    if (!empty($_GET['project'])) {
                                        $relativePath = preg_replace('#^' . preg_quote($_GET['project'], '#') . '/?#', '', $relativePath);
                                        $queryParams['project'] = $_GET['project'];
                                        $queryParams['path'] = defined('APP_ROOT') && APP_ROOT != ''
                                            ? ($_GET['path'] ?? basename($path) ?? '')
                                            : basename(rtrim($relativePath, '/') ?? basename($path));
                                    }

                                    // Handle base path if 'path' is not set
                                    if (empty($_GET['path'])) {
                                        $basePath = rtrim(APP_PATH . APP_ROOT, DIRECTORY_SEPARATOR);
                                        $path = preg_replace('#^' . preg_quote($basePath, '#') . '/?#', '', $path);
                                        $_GET['path'] = (string) $path;
                                        unset($_GET['path']);
                                    }

                                    $queryString = http_build_query($queryParams);
                                    $url = (defined('APP_ROOT') && APP_ROOT != '' && !empty($_GET['client']))
                                        ? /* basename(__FILE__) .*/ "?$queryString"
                                        : "?$queryString";

                                    $basePath = $_GET['path'] ?? ''; // Get the current path
                                    $project = $_GET['project'] ?? ''; // Get the project parameter
                                    $domain = $_GET['domain'] ?? ''; // Get the domain parameter
                                    $client = $_GET['client'] ?? ''; // Get the client parameter
    
                                    // Define conditions
                                    $domainCondition = (!defined('APP_ROOT') || APP_ROOT == '') && empty($domain);
                                    $clientCondition = !empty($client) && empty($domain);

                                    // Initialize query parameters
                                    $queryParams = [];

                                    // Only include one of the parameters in the query string based on which is set
                                    if (!empty($project)) {
                                        $queryParams['project'] = $project; // Only include project
                                    } elseif (!empty($client)) {
                                        $queryParams['client'] = $client; // Only include client
                                    } elseif (!empty($domain)) {
                                        $queryParams['domain'] = $domain; // Only include domain
                                    }

                                    // Construct the current directory path
                                    $currentPath = $basePath /*rtrim(, '/')*/ . basename($path) . '/'; // Maintain the structure
    
                                    // Ensure `path` stays in the query parameters
                                    $queryParams['path'] = $currentPath;

                                    // Generate the correct `href`
                                    $href = '?' . http_build_query($queryParams);

                                    // Determine the correct `onclick` path
                                    $onclickPath = $basePath /*rtrim(, '/')*/ . basename($path) . '/'; // Correctly format for child directory
    
                                    // Construct `onclick` attribute
                                    $onclickAttribute = " onclick=\"handleClick(event, '" . htmlspecialchars($onclickPath, ENT_QUOTES) . "')\"";

                                    // Render the folder link
                                    echo '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '"' . $onclickAttribute . '>
    <img src="assets/images/directory.png" width="50" height="32">' . htmlspecialchars(basename($path), ENT_QUOTES) . '/ 
</a>';
                                    // Render the link
                                    //echo '<a href="' . htmlspecialchars($url) . '"' . $onclickAttribute . '><br />' . basename($path) . '/</a>';
    

                                    /*
                                                      $appRoot = APP_BASE['clients'] . ($_GET['client'] ?? '') . '/' . ($_GET['domain'] ?? '') . '/';

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
                            } elseif (isset($_GET['domain'])) {
                                // Case 2: Only client is set
                                $queryParams = [
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
                            $url = /*basename(__FILE__) .*/ "?$queryString";

                            if (preg_match('/^\..*/', basename($path))) {

                                //$relativePath = str_replace('\\', '\\\\', $relativePath );
    
                                switch (basename($path)) {
                                    case '.htaccess':
                                        echo '<div style="position: relative; border: 4px dashed #A50F5E;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : "path={$_GET['path']}&") . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/htaccess_file.png" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                                            /*            . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '<div style="position: absolute; right: 8px; bottom: -6px; color: red; font-weight: bold;">[1]</div>' : '' ) */
                                            . '</div>' . "\n";
                                        break;
                                    case '.babelrc':
                                        echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : "path={$_GET['path']}&") . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/babelrc_file.gif" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                                            . '</div>' . "\n";
                                        break;
                                    case '.gitignore':
                                        echo '<div style="position: relative; border: 4px dashed #F05033;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . 'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/gitignore_file.png" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                                            . '</div>' . "\n";
                                        break;
                                    case '.env.example':
                                    case '.env':
                                        echo '<div style="position: relative;"><a onclick="openNewEditorWindow(\'' . basename($path) . '\', \'Hello123\');"><img src="assets/images/env_file.png" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                                            . '</div>' . "\n";
                                        break;
                                    default:
                                        echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/env_file.png" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                                            . '</div>' . "\n";
                                }
                            } elseif (preg_match('/^package(?:-lock)?\.(json)/', basename($path))) {
                                echo '<div style="position: relative; border: 4px dashed #E14747;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\'); document.getElementById(\'app_node_js-container\').style.display=\'block\';">';

                                switch (basename($path)) {
                                    case 'package.json':
                                        echo '<img src="assets/images/package_json_file.png" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                                            . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                                            . '</div>' . "\n";
                                        break;
                                    case 'package-lock.json':
                                        echo '<img src="assets/images/package-lock_json_file.png" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                                            . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                                            . '</div>' . "\n";
                                        break;
                                }

                            } elseif (preg_match('/^composer(?:-setup)?\.(json|lock|php|phar)/', basename($path))) {
                                echo '<div style="position: relative;"><div style="position: relative; border: 4px dashed #6B4329;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\'); document.getElementById(\'app_composer-container\').style.display=\'block\';">';

                                switch (basename($path)) {
                                    case 'composer.json':
                                        echo '<img src="assets/images/composer_json_file.gif" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\');">' . basename($path) . '</a>'
                                            . (isset($errors['COMPOSER-VALIDATE-JSON']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                                            . '</div></div>' . "\n";
                                        break;
                                    case 'composer.lock':
                                        echo '<img src="assets/images/composer_lock_file.gif" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                                            . (isset($errors['COMPOSER-VALIDATE-LOCK']) ? '<div style="position: absolute; right: 8px; top: -6px; color: red; font-weight: bold;">[1]</div>' : '')
                                            . '</div></div>' . "\n";
                                        break;
                                    case 'composer.phar':
                                        echo '<img src="assets/images/phar_file.png" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>'
                                            . '</div></div>' . "\n";
                                        break;
                                    default:
                                        echo '<img src="assets/images/composer_php_file.gif" width="40" height="50" /></a>'
                                            . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                                            . '</a></div></div>' . "\n";
                                        break;
                                }
                            } elseif (preg_match('/^.*\.js$/', basename($path))) {
                                switch (basename($path)) {
                                    case 'webpack.config.js':
                                        echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '"><img src="assets/images/webpack_config_js_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                                        break;
                                    default:
                                        echo '<a href="' . basename(__FILE__) . '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/js_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                                        break;
                                }

                            } elseif (preg_match('/^.*\.md$/', basename($path))) {
                                echo '<div style="position: relative; border: 4px dashed #8BBB4B;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/md_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';

                            } elseif (preg_match('/^.*\.php$/', basename($path))) {
                                if (preg_match('/^project\.php/', basename($path)))
                                    echo '<div style="position: relative; border: 4px dashed #2C88DA;"><a style="position: relative;" href="' . (isset($_GET['project']) ? 'project#!' : '#') . '" onclick="document.getElementById(\'app_project-container\').style.display=\'block\';"><div style="position: absolute; left: -60px; top: -20px; color: red; font-weight: bold;">' . (isset($_GET['project']) ? '' : '') . '</div><img src="assets/images/project-icon.png" width="40" height="50" /></a><a href="' . /*basename(__FILE__) .*/ '?' . (isset($_GET['project']) ? 'project#!' : (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'app=ace_editor' . '&file=' . basename($path)) . '" ' . (isset($_GET['project']) ? 'onclick="document.getElementById(\'app_ace_editor-container\').style.display=\'block\';"' : 'onclick="handleClick(event, \'' . basename($relativePath) . '\')"') . '>' . basename($path) . '</a></div>';
                                elseif (basename($path) == 'phpunit.php')
                                    echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . 'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/phpunit_php_file.png" width="40" height="50" /></a>' . '<a href="' . /*basename(__FILE__) .*/ '?file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                                elseif (basename($path) == 'bootstrap.php')
                                    echo '<div style="position: relative; border: 4px dashed #897AE3;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . '&path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';
                                elseif (basename($path) == 'server.php')
                                    echo '<div style="position: relative; border: 4px dashed #897AE3;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . '&path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a></div>';
                                else
                                    echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . 'path=' . /*(basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path))))*/ ($_GET['path'] ?? '') . '&app=ace_editor' . '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';

                            } elseif (basename($path) == 'LICENSE' && preg_match('/^' . preg_quote(APP_PATH, '/') . 'LICENSE$/', $path)) {
                                /* https://github.com/unlicense */
                                echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path']) . '&app=ace_editor' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/license_file.png" width="40" height="50" /></a>un' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                                    . '.org</a>'
                                    . '</div>' . "\n";
                            } elseif (basename($path) == basename(ini_get('error_log')))
                                echo '<div style="position: relative;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path']) . '&app=ace_editor' . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ '&file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">'
                                    . '<img src="assets/images/error_log.png" width="40" height="50" /></a><a id="app_php-error-log" href="' . (APP_URL_BASE['query'] != '' ? '?' . APP_URL_BASE['query'] : '') . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') . /* '?' . basename(ini_get('error_log')) . '=unlink' */ '" style="text-decoration: line-through; background-color: red; color: white;"></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path)
                                    . (is_readable($path = ini_get('error_log')) && filesize($path) > 0 ? '</a><div style="position: absolute; top: -8px; left: 8px; color: red; font-weight: bold;"><a href="#" onclick="$(\'#requestInput\').val(\'unlink error_log\'); $(\'#requestSubmit\').click();">[X]</a></div>' : '')
                                    . '</div>' . "\n";
                            elseif (preg_match('/^.*\.exe$/', basename($path))) {
                                echo '<div style="position: relative; border: 4px dashed #8BBB4B;"><a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&') . 'download&' . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . /*'path=' . (basename(dirname($path)) == basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ? 'failed' : basename(dirname($path)))) .*/ 'file=' . basename($path) . '"><img src="assets/images/exe_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '">' . basename($path) . '</a></div>';

                            } else
                                echo '<a href="' . /*basename(__FILE__) .*/ '?' . (!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '')) . (!isset($_GET['path']) ? '' : 'path=' . $_GET['path'] . '&') . 'app=ace_editor&' . 'file=' . basename($path) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')"><img src="assets/images/php_file.png" width="40" height="50" /></a>' . '<a href="' . htmlspecialchars($url) . '" onclick="handleClick(event, \'' . basename($relativePath) . '\')">' . basename($path) . '</a>';
                        }
                        echo "</div>\n";
                    }
                ?>
            </div>
        <?php }

    }

    $returnValue = ob_get_contents();
    ob_end_clean();
    return $returnValue;
    };

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

        //if (isset($_POST['cmd']) && $_POST['cmd'] != '')
        // require_once 'app.console.php';
    
        if (isset($_GET['app']) && $_GET['app'] == 'ace_editor')
            require_once 'ui.ace_editor.php';

        if (isset($_POST['cmd'])) {

            chdir(APP_PATH . APP_ROOT);

            $output = [];

            //$GLOBALS['runtime']['socket'] = fsockopen(SERVER_HOST, SERVER_PORT, $errno, $errstr, 5);
    
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
                                defined('APP_CLIENT') ? '' : define('APP_CLIENT', $path);

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
                                //dd(get_required_files(), false);
                                ob_start();
                                //if (is_file($include = APP_PATH . APP_ROOT . APP_BASE['vendor'] . 'autoload.php'))
                                //if (isset($_ENV['COMPOSER']['AUTOLOAD']) && (bool) $_ENV['COMPOSER']['AUTOLOAD'] === TRUE)
                                //  require_once $include;
    
                                isset($tableGen) and $tableValue = $tableGen();
                                ob_end_clean();
                                return $tableValue ?? ''; // $app['directory']['body'];
                            })();
                            $output[] = (string) $resultValue;
                            //die();
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
                              require 'app/directory.php';
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
    
                    //(isset($_GET['path']) ? (empty($_GET['client']) ? '' : APP_BASE['clients'] . (isset($_GET['client']) || isset($_GET['domain']) ? /* APP_BASE['clients'] */ (!isset($_GET['client']) ? '' : $_GET['client']) . DIRECTORY_SEPARATOR : '') . (!isset($_GET['domain']) ? '' : $_GET['domain'])) /*APP_BASE[''] . $_GET['client'] . '/'*/ : APP_ROOT  ) . 
    
                    $rootFilter = '';
                    $filePath = APP_PATH;

                    // Determine the root filter based on client and domain
                    if (!empty($_GET['client'])) {
                        $rootFilter = APP_BASE['clients'] . $_GET['client'] . DIRECTORY_SEPARATOR;
                        if (isset($_GET['domain'])) {
                            $rootFilter .= $_GET['domain'] . DIRECTORY_SEPARATOR;
                        }
                    } elseif (isset($_GET['domain'])) {
                        $rootFilter = APP_BASE['clients'] . $_GET['domain'] . DIRECTORY_SEPARATOR;
                    }

                    // Add project-specific root filter if applicable
                    if (isset($_GET['project'])) {
                        $rootFilter = APP_BASE['projects'] . $_GET['project'] . DIRECTORY_SEPARATOR;
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
                    //$output[] = is_file($file = APP_PATH . (empty($_GET['client']) ? '' : $root_filter = APP_BASE['clients'] . (isset($_GET['client']) || isset($_GET['domain']) ? /* APP_BASE['clients'] */ (!isset($_GET['client']) ? '' : $_GET['client']) . DIRECTORY_SEPARATOR : '') . (!isset($_GET['domain']) ? '' : $_GET['domain'])) . DIRECTORY_SEPARATOR . (!isset($_GET['path']) ? (!isset($_GET['project']) ? '' : $root_filter = APP_BASE['projects'] . $_GET['project']) : $_GET['path']) . DIRECTORY_SEPARATOR . trim(preg_replace('#^' . preg_quote($root_filter, '#') . '/?#', '', $match[1]))) ? file_get_contents($file) : "File not found: $file";
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
            //require_once /*APP_BASE['app'] .*/ 'console.php';
        }

        if (!isset($_POST['group_type']))
            Shutdown::setEnabled(true)->setShutdownMessage(function () {
                return ''; // 'The application has been terminated.';  
            })->shutdown();
    }



    ob_start(); ?>
<!-- app/devtools/directory.php -->
<div id="app_directory-container" class="app-container app-fixed" data-app="directory" data-draggable="false">
    <div class="app-header">
        <div
            style="position: absolute; top: -10px; left: 0px; width: 395px; z-index: 50; background-color: rgb(250, 250, 250); border: 1px solid black; box-shadow: rgba(0, 0, 0, 0.5) 0px 0px 10px; border-radius: 5px; padding: 3px;">
            <form action="" method="GET" style="display: inline; margin: 0;">
                <input type="hidden" name="path" value="" />
                <button id="displayDirectoryBtn" style="margin: 2px 5px 0 0; border: 3px dashed red;" type=""
                    onclick="this.form.submit();"><img src="assets/images/directory-www.fw.png" width="18"
                        height="10" style="vertical-align: middle;">&nbsp;&#9650;</button>
            </form>
            <div style="display: inline; margin-top: -3px;"><a
                    style="font-size: 18pt; font-weight: bold; padding: 0 3px 0 0 ;" href="/">&#8962;</a></div>
            <form style="display: inline;" autocomplete="off" spellcheck="false" action="" method="GET">/
                <select name="category" onchange="this.form.submit();">
                    <option value=""></option>
                    <option value="application">applications</option>
                    <option value="client">clients</option>
                    <option value="projects">projects</option>
                    <option value="node_module">./node_modules</option>
                    <option value="resources">./resources</option>
                    <option value="project">./project</option>
                    <option value="vendor">./vendor</option>
                </select>
            </form>
            <form style="display: inline;" action="" method="GET">
                <span title="<?= APP_PATH; ?>" style="margin: 2px 5px 0 0; cursor: pointer;" onclick=""> /
                    <select name="path" style="" onchange="this.form.submit(); return false;">
                        <option value="">.</option>
                        <option value="">..</option>
                        <option value="/applications">applications/</option>
                        <option value="/bin">bin/</option>
                        <option value="/clients">clients/</option>
                        <option value="/config">config/</option>
                        <option value="/data">data/</option>
                        <option value="/dist">dist/</option>
                        <option value="/docs">docs/</option>
                        <option value="/node_modules">node_modules/</option>
                        <option value="/projects">projects/</option>
                        <option value="/public">public/</option>
                        <option value="/resources">resources/</option>
                        <option value="/src">src/</option>
                        <option value="/tests">tests/</option>
                        <option value="/var">var/</option>
                        <option value="/vendor">vendor/</option>
                    </select> / <a href="#" onclick="document.getElementById('info').style.display = 'block';">+</a>
                </span>
            </form>

        </div>
        <!-- div class="app-title">Directories</div>

        <div class="app-controls">
            <select id="directory-roots" class="dir-select"></select>
        </div -->
    </div>

    <div id="app_directory-content" class="app-content">
        <!-- The directory grid/list will be rendered here -->

        <?= $tableGen(); /*'';*/ ?>
    </div>
</div>
<?php $UI_APP['body'] = ob_get_contents();
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

$('input[name="restore_backup"]').val(path); // path.substring(0, path.lastIndexOf('/'))

$('form[name="ace_form"]').attr('action', DirQueryParams);

document.getElementsByClassName('ace_text-input')[0].name = 'ace_contents';

// Use jQuery to update the request input and submit
$('#requestInput').val('edit ' + path);

$('#requestSubmit').href = event.currentTarget.href;
$('#requestSubmit').click();


//document.getElementsByClassName('ace_text-input')[0].value = 'hello world';
}

//if (!isFixed) isFixed = false;
//show_console();

// Optionally, you could update the div directly if needed
// $('#app_directory-container').html('Loading ' + folder + '...');
}
}

document.addEventListener('DOMContentLoaded', () => {
// Support clicking
document.querySelectorAll('.breadcrumb').forEach(link => {
link.addEventListener('click', e => {
e.preventDefault();
const path = e.target.dataset.path;
handleClick(e, path); // your custom function
});
link.setAttribute('tabindex', '0'); // make it keyboard focusable
link.addEventListener('keydown', e => {
if (e.key === 'Enter') {
const path = e.target.dataset.path;
handleClick(e, path);
}
});
});

// Optional: Left/Right arrow navigation
let crumbs = Array.from(document.querySelectorAll('.breadcrumb'));
crumbs.forEach((el, idx) => {
el.addEventListener('keydown', e => {
if (e.key === 'ArrowRight' && idx < crumbs.length - 1) { crumbs[idx + 1].focus(); } if (e.key==='ArrowLeft' && idx> 0) {
    crumbs[idx - 1].focus();
    }
    });
    });
    });
    <?php
    $UI_APP['script'] = ob_get_contents();
    ob_end_clean();
    ?>