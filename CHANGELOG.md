CHANGELOG


:: PHP

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