<?php if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null)))
    require_once($path); 
else die(var_dump($path . ' was not found. file=config.php'));

//die(basename(getcwd()) . ' ==' . 'public');

if ($path = (basename(getcwd()) == 'public')
    ? (is_file('composer_app.php') ? 'composer_app.php' : (is_file('../composer_app.php') ? '../composer_app.php' : null))
    : (is_file('../composer_app.php') ? '../composer_app.php' : (is_file('config/composer_app.php') ? 'config/composer_app.php' : (is_file('public/composer_app.php') ? 'public/composer_app.php' : 'composer_app.php'))))
  require_once($path); 
else die(var_dump($path . ' was not found. file=composer_app.php'));