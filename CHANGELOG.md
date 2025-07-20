CHANGELOG


root@localhost:/mnt/c/www# git remote -v
origin  git@github.com:barrydit/codeinsync.git (fetch)
origin  git@github.com:barrydit/codeinsync.git (push)
root@localhost:/mnt/c/www# git remote set-url origin https://github.com/barrydit/codeinsync.git
root@localhost:/mnt/c/www# git push https://<TOKEN>@github.com/barrydit/codeinsync.git


considering

function () use ($json_decode) {
        return json_encode($json_decode);
      }

with PHP 7 / 8 appearence/occurance/introduced in 7.4?

Shutdown::setEnabled(false)->setShutdownMessage(fn() => json_encode($json_decode))->shutdown();

sudo /usr/bin/git status
Error: fatal: detected dubious ownership in repository at '/mnt/c/www/clientele/000-Client1/domain.ca'
To add an exception for this directory, call:

	git config --global --add safe.directory /mnt/c/www/clientele/000-Client1/domain.ca


Exit Code: 128Error: [2] preg_match(): Unknown modifier '(' - /mnt/c/www/clientele/000-Client1/domain.ca/src/app.console.php:66
www-data@localhost:/var/www$ git status



To remove the last two commits and push the updated history to your remote repository, follow these steps:

1. Check Your Current Git History
Run the following command to review your commit history:

git log --oneline
Identify the two commits you want to remove (they should be at the top of the history).

2. Remove the Last Two Commits
To remove the last two commits while preserving your working directory:

git reset --soft HEAD~2
HEAD~2 means "go back two commits."
The --soft flag keeps your changes staged, so you can make any edits or recommit.

3. Check the Current State
To verify the changes, run:

git status
This will show the files from the removed commits as staged for commit.

4. Create a New Commit Without the Token
Edit any sensitive files (e.g., .env) to ensure the token is removed. Then, create a new commit:

git commit -m "Remove sensitive token"

5. Force Push the Updated History
Since you’ve rewritten history, you’ll need to force push:

git push --force
⚠️ Warning: A force push rewrites the history on the remote. Ensure no one else is working on the same branch, or coordinate with your team before doing this.

6. Optional: Verify the Push
Check the remote repository to confirm the updates were applied:

git log origin/main --oneline



Basic
CSS HTML JAVASCRIPT

Front-end/ Framework
Angular.js React.js Vue.js

Back-end/ framework
Meteor.js Next.js Node.js Express.js Ghost.js

Database
MySql NoSQL MongoDB PostreSQL

Hybrid/cross platform
Electron React-Native Ionic-Vue Ionic NativeScript

API -> Application Programming Interface
JSON -> JavaScript Object Notation
XML -> Extensible Markup Language
AJAX -> Asynchronous JavaScript and XML
REST -> Representational State Transfer

Web Dev.
HTML, CSS, JS, PHP, ASP.net, python, java, Ruby, sql, xml

Software Dev
C, C++, C#, Java, Ruby, VB.Script, Python, SQL

Machine Learning
Python, C++, JavaScript, Java, C#, Alaa, Julia, Shell, R, TypeScript, Scala



Ace Editor => (themed) text editor for editing any file on the system (web app. or client/project)
    It is easier to comprehend code with a themed text editor is used for a particular language

* Backup => Recovery files from var/source_code.json using Ace Editor

Weekly Time Clock-in  => Count the amount of time the user spends on programming clients/projects.
    A lot of valueable time can go into application. debugging, feature writing, application building

Multiple computer language / terminal emu. So People can learn to program by 1 liners, and be able to do 10-100 lines at 1 click
  [php eval(), python, perl, npm, sql]
    angular, node, npm, json, react, git, vue, api

Server (Socket) -> Web Socket (future) for real time exchange of commands and data from the web application.

Git/Hub => Source code sharing
    Clients can have the source code to the application, and git pull/download fresh updates.

* Build a workflow / process task list for a particular procedure for the ui / app

Composer/Packagist => 375,000+ applications avail. to install with the api/ui ... the user just needs to know how to customize, and create a project.
    [of course where is to many, should it be segmented, and easily install packages on demand]
      depend on vendor/ or node_modules/ and any other library dependencies

-> Unto this point that it should become a ... [project/[client/[domain]]]

  App/[client/[domain/]][project] - Structure for domain, clients, projects

    APP_PATH = /mnt/c/www/ . APP_ROOT = projects/project_name/ . $_GET['path']
    APP_PATH = /mnt/c/www/ . APP_ROOT = clientele/client_name/domain.com/ . $_GET['path']
    APP_PATH = /mnt/c/www/ . APP_ROOT = clientele/domain.com/ . $_GET['path']

These are just the tools needed to turn on a PHP web site.        Next is organizing and accessing client applications, and projects that benefit the application. an IDE. I mean there are already online vs code editors that already do every thing I have listed. But their not open source.

~~~~
php does not work, along with other cmds

projects/*project1* opens clientele/ when it should be projects/

?domain=example.com produces a extra forward-slash->/example.com/

   http://localhost/app.directory.php?path=&app=ace_editor&file=composer.json
   http://localhost/app.directory.php?domain=example.com&path=&app=ace_editor&file=composer.json

vendor/package1
  should open its directory/contents
~~~
Tasks:

    Create a skeleton for .env file for the file to referer to bool/string/value filler
    
    --Fix the composer.json file open / php exec problem
    --path is returning to app direction under client/domain
    --double forward slashes on child directories under domains
    --Create customized code to open/handle Client=000&domain=&...file=file
    Code properally opens directories and files now.
    
      .htaccess Base Rewrite / Resources


Feature:

      Use Ace editor as editor to edit realtime multi line code
      
      Feature move files new locations?
      
      Feature outside folder range !== APP_PATH . APP_ROOT


Bug:
      <b>Fatal error</b>: Uncaught TypeError: fclode(): supplied resource is not a valid stream resource in 
/mnt/c/www/config/classes/class.sockets.php:347
Stack trace:
#0 /mnt/c/www/config/classes/class.sockets.php(347): fclose()
#1 [internal function]: Sockets-&gt;__destrtuct()
#2 {main}
      thrown in <b>/mnt/c/www/config/classes/class.sockets.php</b> on line <b>347</b><br />
      
      Error: [2] socket_write(): unable to write to socket [32]: Broken pipe - /mnt/c/www/server.php:806
      
      /clientele/000-Raymant,David/davidraymant.ca/public/ POST index.php retreats to /clientele/000-Raymant,David/
      
      php and any other commands are not responding because of the custom href ... need to figure out where it needs to go,
      assuming that its app.console.php , while the other link is for app.directory.php
      
      ace editor form appears to be GET method, rather then POST to submit the contents. This could cause loss of data
      



Error: [2] socket_write(): unable to write to socket [32]: Broken pipe - /mnt/c/www/server.php:806

If APP_ROOT is populated / !empty and I am changing the directory to the client label, I wish the default directory would go to the domain, rather then the label directory.

www-data@localhost:/var/www$ chdir dist/ -> fails ???
www-data@localhost:/var/www$ chdir / -> mnt/c/www/clientele/000-Raymant,David/davidraymant.ca
what is/does the url look like http://localhost/app.directory.php?  



--  0 => '/mnt/c/www/public/app.directory.php',
--  1 => '/mnt/c/www/bootstrap.php',
--  2 => '/mnt/c/www/config/php.php',
  3 => '/mnt/c/www/config/functions.php',  
  
  packagist_return_source()
  
--  4 => '/mnt/c/www/config/config.php',

  5 => '/mnt/c/www/config/constants.php',
  
  319:  resolve_host_to_ip() << - Takes twice as long 22, to load the page
    APP_IS_ONLINE / APP_NO_INTERNET_CONNECTION
    
    Could this function be made after the site is loaded?
  
    
  check_http_status() 

  check_internet_connection()
  
--  6 => '/mnt/c/www/classes/class.sockets.php',
  
  345 - //fclose(self::$socket);
  
  
  createsocket
  
    fsockopen
  
--  7 => '/mnt/c/www/classes/class.clientorproj.php',
--  8 => '/mnt/c/www/classes/class.logger.php',
--  9 => '/mnt/c/www/classes/class.notification.php',


Alert when php/composer/git, .env file are not available



Framework Suggestions
  Frontend Frameworks (for dynamic updates, avoiding screen refreshes):

    Vue.js: A lightweight JavaScript framework perfect for building dynamic user interfaces. It integrates easily with existing static content and can handle your dynamic directory browsing with reactive components.
    React: Excellent for building interactive user interfaces. React’s state and props system can help manage dynamic and static content seamlessly.
    Svelte: A newer option that compiles components into highly efficient JavaScript. It minimizes the overhead and can easily handle your dynamic directory browser.
    Backend Frameworks (to support API-driven development):

    Laravel (PHP): A feature-rich PHP framework with built-in support for API development and a clean templating engine (Blade) to mix static and dynamic elements.
    Symfony (PHP): If you need more flexibility and modularity, Symfony provides robust tools for API development and works well with templates for static content.
    Node.js with Express: If you're open to using JavaScript for both frontend and backend, Express can serve dynamic APIs efficiently.
    Full-Stack Frameworks (if you want to integrate frontend and backend):

    Next.js: React-based, with server-side rendering and API routes built-in. It works well for apps that need a mix of static and dynamic content.
    Nuxt.js: Vue.js-based, great for creating dynamic web apps with server-side rendering.
    Techniques to Improve Usability and Minimize Refreshes
    AJAX and Fetch API: Use asynchronous calls to fetch data and update parts of the UI dynamically. This keeps the app interactive without full-page reloads.

    WebSockets: Implement real-time communication for commands like chdir. This provides instant feedback and keeps the user interface synchronized with backend operations.

  Breadcrumb Navigation: Display the current directory path as breadcrumbs to help users track their navigation in the directory browser.

  Dynamic UI Components:

    Use collapsible panels or tabs to separate static and dynamic content visually.
    Provide context-sensitive help or tooltips explaining dynamic features.
    State Management: Employ state management tools (e.g., Vuex for Vue.js or Redux for React) to handle interactions between static and dynamic components cleanly.

    Skeleton Loading: For dynamic components, display skeleton loaders while data is being fetched to improve the perception of responsiveness.






.git/config

fatal: could not read Username for 'https://github.com': No such device or address

[remote "origin"]
	url = git@github.com:barrydit/repository.git

git remote set-url origin https://github.com/barrydit/repository.git



$composerUser = !isset($_ENV['COMPOSER']['USER']) ?: $_ENV['COMPOSER']['USER'];  
$componetPkg = !isset($_ENV['COMPOSER']['PACKAGE']) ?: $_ENV['COMPOSER']['PACKAGE'];
$user = getenv('USERNAME') ?: (getenv('APACHE_RUN_USER') ?: getenv('USER') ?: '');



(APP_SELF !== APP_PATH_SERVER) and $socketInstance = Sockets::getInstance();
//$socketInstance->handleClientRequest("composer self-update\n");
fclose($socketInstance->getSocket());


config/config.php

die(getcwd() . ' == ' . __DIR__); // /mnt/c/www/public == /mnt/c/www/config/classes


get_required_files() == 
  ===
  [0] => "/mnt/c/www/server.php" (Socket Server 0.0.0.0:8080) || Web Application "/mnt/c/www/public/index.php" ,.. app.console.php, app.browser.php, app.notes.php, app.directory.php 
  ===
  1  "/mnt/c/www/index.php" (inaccessbile index.php file used to include config.php)
  2    "/mnt/c/www/config/config.php"
  3      "/mnt/c/www/config/functions.php"
  4        "/mnt/c/www/classes/class.clientorproj.php"
  5        "/mnt/c/www/classes/class.logger.php"
  6        "/mnt/c/www/classes/class.notification.php"
  7        "/mnt/c/www/classes/class.shutdown.php"
  8      "/mnt/c/www/config/constants.php"
  9      "/mnt/c/www/config/php.php"
  10        "/mnt/c/www/classes/class.sockets.php"

get_required_files() == 
 [0]=> "/mnt/c/www/server.php" (Socket Server 0.0.0.0:8080) ||
       "/mnt/c/www/public/index.php" Web Application ,.. app.console.php, app.browser.php, app.notes.php, app.directory.php 
 [1]=>   "/mnt/c/www/bootstrap.php"
 [2]=>     "/mnt/c/www/config/php.php"
 [3]=>       "/mnt/c/www/config/config.php"
 [4]=>       "/mnt/c/www/config/functions.php"
 [5]=>         "/mnt/c/www/classes/class.clientorproj.php"
 [6]=>         "/mnt/c/www/classes/class.logger.php"
 [7]=>         "/mnt/c/www/classes/class.notification.php"
 [8]=>       "/mnt/c/www/config/constants.php"
 [9]=>         "/mnt/c/www/classes/class.sockets.php"
 [10]=>    "/mnt/c/www/public/ui.ace_editor.php"
 [11]=>    "/mnt/c/www/public/ui.composer.php"
 [12]=>    "/mnt/c/www/config/composer.php"
 [13]=>      "/mnt/c/www/vendor/autoload.php"

.htpasswd

/*
[$_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']] = 
  explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Text to send if user hits Cancel button';
    exit;
} else { echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p><p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>"; }
*/




if (__FILE__ == get_required_files()[0] && __FILE__ == realpath($_SERVER["SCRIPT_FILENAME"])) 
  if ($path = basename(dirname(get_required_files()[0])) == 'public') { // (basename(getcwd())
    if (is_file($path = realpath('index.php')))
      require_once $path;
  } else {
    die(var_dump("Path was not found. file=$path"));
  }

switch (__FILE__) {
  case get_required_files()[0]:
    if ($path = (basename(getcwd()) == 'public') ? (is_file('config.php') ? 'config.php' : '../config/config.php') : '') require_once $path;
    else die(var_dump("$path path was not found. file=config.php"));

    break;
  default:
}

PHP

  $cmd = 'test';

  $msg = (FALSE == FALSE) . 1 ? : 'another test'; // 11

  die($msg);



        $runServer = function($serverExec, $phpExec) {
            if (is_executable($serverExec)) {
                echo 'Running (New) Server' . PHP_EOL;

                $tty = trim(shell_exec('tty'));
                $tty = ($tty !== 'not a tty') ? $tty : null;

                // Verify that we successfully retrieved a TTY
                if ($tty) { 
                  $command = "$phpExec $serverExec";
                  // Open a process with custom descriptors
                  $descriptors = [
                      0 => ["pipe", "r"],  // stdin
                      1 => ["file", "/dev/$tty", "w"],  // stdout to TTY or change to a file if needed
                      2 => ["file", "/dev/$tty", "a"]   // stderr to TTY or change to a file if needed
                  ];

                  $process = proc_open($command, $descriptors, $pipes);

                  // Check if the process was successfully started
                  if (is_resource($process)) {
                      // Close pipes as needed
                      fclose($pipes[0]);
                  
                      // Wait for the process to finish and close it
                      $return_value = proc_close($process);
                      echo "Command finished with return value: $return_value\n";
                  } else {
                      echo "Failed to start the process.\n";
                  }

                } else {
                    $tty ??= 'pts/1';

                  $command = "$phpExec $serverExec > /dev/$tty 2>&1";
                  $descriptors = [
                      0 => ["pipe", "r"],  // stdin
                      1 => ["pipe", "w"],  // stdout
                      2 => ["pipe", "w"]   // stderr
                  ];

                  $process = proc_open($command, $descriptors, $pipes);

                  // Check if the process was successfully started
                  if (is_resource($process)) {
                      // Close pipes as needed

                    foreach ($pipes as $pipe) {
                        fclose($pipe);
                    }
                  
                      // Wait for the process to finish and close it
                    $return_value = trim(proc_close($process));
                    echo "Command finished with return value: $return_value\n";
                  } else {
                    echo "Failed to start the process.\n";
                  }
                }

                echo "Current TTY: " . ($tty ? : 'pts/1 (default)') . "\n";
                echo "Command: $command\n";
                $return_value = shell_exec((string) $command /*. ' > ' . escapeshellarg("/dev/pts/1") . ' 2>&1 &'*/); // Preferred executable (bash/php with shebang)

                echo "server.php has been started and output is redirected to $tty.\nResult: $return_value";

            } else {
                echo 'Running (New) Server via PHP' . PHP_EOL;
                shell_exec('nohup ' . escapeshellcmd($phpExec) . ' ' . escapeshellcmd($serverExec) . ' > /dev/null 2>&1 &');
            }
        };





Sockets



        $bytesReceived = @socket_recv($socket, $buffer, 1024, MSG_DONTWAIT);
        if ($bytesReceived !== 0) {
            ///echo "Socket connection is open.\n";
            if ($client = @socket_accept($socket)) {
              handleSocketClientConnection($client);
            }
        } 
        



/*

stream_set_blocking($GLOBALS['runtime']['socket'], false);

stream_set_timeout($GLOBALS['runtime']['socket'], 10);

$writtenBytes = fwrite($GLOBALS['runtime']['socket'], $message);
if ($writtenBytes === false) {
  $error = socket_last_error($GLOBALS['runtime']['socket']);
  $errorMessage = socket_strerror($error);
  echo "Socket write error: $errorMessage\n";

} else {
  fflush($GLOBALS['runtime']['socket']); // Flush the buffer
  echo "Bytes written: $writtenBytes\n";
}

// Check if the socket is ready for reading
$read = [$GLOBALS['runtime']['socket']];
$write = null;
$except = null;
$ready = stream_select($read, $write, $except, 5); // 5 seconds timeout
if ($ready > 0) {

} else {
    echo "Socket not ready for reading.";
}


// Stream error check
$error = error_get_last();
if ($error) {
    echo "Stream Error: " . $error['message'];
}

*/



HTACCESS

RewriteEngine On
RewriteBase "/clientele/000-Lastname,\ Firstname/example.ca/public"

# Rewrite requests for /assets to the /../resources directory if the file doesn't exist in /assets
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^assets/(.*)$ ../resources/$1 [L]


vendor/composer/installed.php

<?php return array(
    'root' => array(
        'name' => 'barrydit/1234',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '1d21ed08f33201dc985e9f2dca4984dd1783d9a9',
        'type' => 'project',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'barrydit/1234' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '1d21ed08f33201dc985e9f2dca4984dd1783d9a9',
            'type' => 'project',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);




:: PHP

if (!is_resource($GLOBALS['runtime']['socket']) || empty($GLOBALS['runtime']['socket'])) { // get_resource_type($stream) == 'stream'

  $proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . 'APPLICATION', [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

  [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

  if ($exitCode !== 0) {
    if (empty($stdout)) {
      if (!empty($stderr)) {
        $errors['APPLICATION'] = $stderr;
        error_log($stderr);
      }
    } else {
      $errors['APPLICATION'] = $stdout;
    }
  }

} else {
  $errors['server-1'] = "Connected to Server: " . SERVER_HOST . ':' . SERVER_PORT . "\n";
  
  // Send a message to the server
  $errors['server-2'] = 'Client request: ' . $message = "cmd: composer update\n";
    
  fwrite($GLOBALS['runtime']['socket'], $message);
  $output[] = trim($message) . ': ';
  // Read response from the server
  while (!feof($GLOBALS['runtime']['socket'])) {
    $response = fgets($GLOBALS['runtime']['socket'], 1024);
    $errors['server-3'] = "Server responce: $response\n";
    if (isset($output[end($output)])) $output[end($output)] .= $response = trim($response);
    //if (!empty($response)) break;
  }
}


exec() shell_exec() escapeshellarg()


      $proc=proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO) . "composer $matches[1]",  
        array(
          array("pipe","r"),
          array("pipe","w"),
          array("pipe","w")
        ),
        $pipes);
                [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
                $output[] = !isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : " Error: $stderr") . (isset($exitCode) && $exitCode == 0 ? NULL : "Exit Code: $exitCode");



$proc = proc_open((stripos(PHP_OS, 'WIN') === 0 ? '' : APP_SUDO ) . basename($bin) . ' --version;', array( array("pipe","r"), array("pipe","w"), array("pipe","w")), $pipes);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        $exitCode = proc_close($proc);


if (is_resource($socket) || !empty($socket)) {
  //socket_close($socket);
  socket_write
} elseif (is_resource($stream) && get_resource_type($stream) == 'stream') {
  //fclose($stream);
  fwrite()
}



ui.composer.php

  autoload.php
  composer.php


Many php functions either produce a forward slash, indicating the end of folder/path

  $path = dirname(__DIR__) . '/config/config.php'


Which is the better format ... 

1. stripos(PHP_OS, 'LIN') === 0
...
2. strpos(PHP_OS, 'WIN') === 0
...
3. strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'


(isset($_GET['client']) ? 'client=' . $_GET['client'] . '&' : '') . (isset($_GET['domain']) ? 'domain=' . $_GET['domain'] . '&' : '') . (isset($_GET['project']) ? 'project=' . $_GET['project'] . '&' : '')

(!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') : 'client=' . $_GET['client'] . '&' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '&' : '') : '' ) . (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') ) // client | project | client / ??domain / project

(!isset($_GET['client']) ? (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '/') : 'client=' . $_GET['client'] . '/' . (isset($_GET['domain']) ? 'domain=' . ($_GET['domain'] != '' ? $_GET['domain'] . '/' : '') : '' ) . (!isset($_GET['project']) ? '' : 'project=' . $_GET['project'] . '&') ) client/project/domain/project

:: JavaScript

JScript
  tailwindcss-3.3.5.js
Style (type="text/tailwindcss")


- Non-indented/tab code means that it is included/required/imported

Javascript libraries must be loaded in a particular order. If a library can not be loaded, "ace is not defined", then they are in the wrong order.

jQuery, jQuery-ui, ace-editor, requirejs


    <script src="https://d3js.org/d3.v4.min.js"></script>
    <script src="https://d3js.org/d3-selection-multi.v1.min.js"></script>
    <script src="https://d3js.org/d3-force.v1.min.js"></script>
    <script src="https://d3js.org/d3-drag.v1.min.js"></script>
    <script src="https://d3js.org/d3-timer.v1.min.js"></script>
    <script src="https://d3js.org/d3-dispatch.v1.min.js"></script>
    <script src="https://d3js.org/d3-selection.v1.min.js"></script>
    <script src="https://d3js.org/d3-transition.v1.min.js"></script>
    <script src="https://d3js.org/d3-quadtree.v1.min.js"></script>
    <script src="https://d3js.org/d3-interpolate.v1.min.js"></script>
    <script src="https://d3js.org/d3-color.v1.min.js"></script>
    <script src="https://d3js.org/d3-ease.v1.min.js"></script>
    <script src="https://d3js.org/d3-scale.v1.min.js"></script>
    <script src="https://d3js.org/d3-scale-chromatic.v1.min.js"></script>
    <script src="https://d3js.org/d3-path.v1.min.js"></script>
    <script src="https://d3js.org/d3-shape.v1.min.js"></script>
    <script src="https://d3js.org/d3-array.v1.min.js"></script>
    <script src="https://d3js.org/d3-format.v1.min.js"></script>
    <script src="https://d3js.org/d3-time-format.v1.min.js"></script>
    <script src="https://d3js.org/d3-time.v1.min.js"></script>
    <script src="https://d3js.org/d3-axis.v1.min.js"></script>
    <script src="https://d3js.org/d3-brush.v1.min.js"></script>
    <script src="https://d3js.org/d3-zoom.v1.min.js"></script>
    <script src="https://d3js.org/d3-fetch.v1.min.js"></script>
    <script src="https://d3js.org/d3-geo.v1.min.js"></script>
    <script src="https://d3js.org/d3-geo-projection.v2.min.js"></script>
    <script src="https://d3js.org/d3-polygon.v1.min.js"></script>
    <script src="https://d3js.org/d3-quadtree.v1.min.js"></script>
    <script src="https://d3js.org/d3-random.v1.min.js"></script>
    <script src="https://d3js.org/d3-request.v1.min.js"></script>
    <script src="https://d3js.org/d3-voronoi.v1.min.js"></script>
    <script src="https://d3js.org/d3-delaunay.v1.min.js"></script>
    <script src="https://d3js.org/d3-hierarchy.v1.min.js"></script>


APP_DOMAIN config.php:310
COMPOSER_HOME composer.php:297
COMPOSER_AUTH['token'] composer.php:477
COMPOSER_CONFIG ui.composer.php:678


Documentation

Clients who have 1 folder under a [client]/ directory can only see 1 [domain]/ at the moment.
This could benifit directories that have 1 website within the directory, with no extension.

This means that the filter maybe excluding directories that don't look like a parseable url, but
if the website was under a [client]/ as a single directory, It maybe then seen as a client web site.
No matter the name.

When listing websites that are behind [client]/ directories or websites without [client]/, its going
to be important to show their absolute path, because I want to be able to show websites w/o [client]'s.



  
  

// Check if the user has requested logout
if (filter_input(INPUT_GET, 'logout')) { // ?logout=true
  // Set headers to force browser to drop Basic Auth credentials
  header('WWW-Authenticate: Basic realm="Logged Out"');
  header('HTTP/1.0 401 Unauthorized');
    
  // Add cache control headers to prevent caching of the authorization details
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
  header("Pragma: no-cache");
    
  // Unset the authentication details in the server environment
  unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    
  // Optional: Clear any existing headers related to authorization
  if (function_exists('header_remove')) {
    header_remove('HTTP_AUTHORIZATION');
    //header_remove('PHP_AUTH_USER');
    //header_remove('PHP_AUTH_PW');
  }

  // Provide feedback to the user and exit the script
  //header('Location: http://test:123@localhost/');
  exit('You have been logged out.');
}

//die(var_dump($_SERVER));
if (PHP_SAPI !== 'cli') {
  // Ensure the HTTP_AUTHORIZATION header exists
  if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    // Decode the HTTP Authorization header
    $authHeader = base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6));
    if ($authHeader) {
      // Split the decoded authorization string into user and password
      [$user, $password] = explode(':', $authHeader);

      // Set the PHP_AUTH_USER and PHP_AUTH_PW if available
      $_SERVER['PHP_AUTH_USER'] = $user ?? '';
      $_SERVER['PHP_AUTH_PW'] = $password ?? '';
    }
  }

  // Check if user credentials are provided
  if (empty($_SERVER['PHP_AUTH_USER'])) {
    // Prompt for Basic Authentication if credentials are missing
    header('WWW-Authenticate: Basic realm="Dashboard"');
    header('HTTP/1.0 401 Unauthorized');
  
    // Stop further script execution
    exit('Authentication required.');
  } else {
    // Display the authenticated user's details
    //echo "<p>Hello, {$_SERVER['PHP_AUTH_USER']}.</p>";
    //echo "<p>You entered '{$_SERVER['PHP_AUTH_PW']}' as your password.</p>";
    //echo "<p>Authorization header: {$_SERVER['HTTP_AUTHORIZATION']}</p>";
  }
}


----------------------------------


To push to GitHub using only a token (no SSH) and remove any mention of SSH, follow these steps:
✅ 1. Ensure You're Using HTTPS (Not SSH)

First, check your remote:

git remote -v

If you see something like:

origin  git@github.com:username/repo.git (fetch)
origin  git@github.com:username/repo.git (push)

That’s SSH. You need to change it to HTTPS:

git remote set-url origin https://github.com/username/repo.git

Replace username/repo with your actual GitHub path.
✅ 2. Generate a GitHub Personal Access Token (PAT)

    Go to https://github.com/settings/tokens

    Click "Generate new token (classic)"

    Give it scopes like:

        repo (for private repos)

        workflow (if using actions)

    Copy the token (you won’t see it again)

✅ 3. Push with the Token

You can push like this from the command line:

git push https://<TOKEN>@github.com/username/repo.git

Example:

git push https://ghp_abcd1234YourTokenHere@github.com/yourusername/yourrepo.git

    ⚠️ Never paste this URL into your code or .git/config — use it once to cache credentials or store it in a credential manager.

✅ 4. (Optional) Store Token in Git Credential Manager

Use Git's credential helper to avoid typing your token each time:

git config --global credential.helper store

Then do a push, and Git will prompt you once:

git push origin main

Use:

    Username: your GitHub username

    Password: your token

It gets saved in ~/.git-credentials.
✅ 5. Verify No SSH References Remain

Check .git/config:

cat .git/config

Ensure [remote "origin"] has an https:// URL, not git@.

If needed, manually edit:

nano .git/config

Replace any line like:

url = git@github.com:username/repo.git

With:

url = https://github.com/username/repo.git

✅ Summary
Action	Command/Step
Convert SSH to HTTPS	git remote set-url origin https://...
Push using token	git push https://<TOKEN>@github.com/...
Avoid repeat logins	git config --global credential.helper store
Remove SSH traces	Edit .git/config or check git remote -v

Let me know if you want this token to be stored in a script or used in CI/CD pipelines.


