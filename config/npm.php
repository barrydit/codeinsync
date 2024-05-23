<?php
/*
 Error: npm WARN EBADENGINE Unsupported engine {
npm WARN EBADENGINE   package: 'npm@10.2.3',
npm WARN EBADENGINE   required: { node: '^18.17.0 || >=20.5.0' },
npm WARN EBADENGINE   current: { node: 'v12.22.12', npm: '7.5.2' }
npm WARN EBADENGINE }
*/

define('NODE_ENV', !defined('APP_ENV') ? 'production' : APP_ENV);
putenv('NODE_ENV=' . (string) NODE_ENV);

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
  define('NODE_EXEC', 'node.exe');
else
  define('NODE_EXEC', '/usr/bin/node');

$proc = proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . NODE_EXEC . ' --version', [ array("pipe","r"), array("pipe","w"), array("pipe","w")], $pipes);

$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);

$exitCode = proc_close($proc);

if (preg_match('/v(\d+\.\d+\.\d+)/', $stdout, $matches))
  define('NODE_VERSION', $matches[1]);
else
  if (empty($stdout)) {
    if (!empty($stderr))
      $errors['NODE_VERSION'] = "\$stderr = $stderr";
  } // else $errors['NODE_VERSION'] = $stdout . ' does not match $version'; }


define('NODE_MODULES_PATH', APP_PATH . 'node_modules/');

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
  define('NPM_EXEC', 'npm' /*.'.cmd'*/);
else {
  define('NPM_EXEC', '/usr/bin/npm');

  $npmExecPath = shell_exec('which ' . NPM_EXEC);
  if ($npmExecPath !== false) {
    // npm_exec not found
    // handle the error here
  
$proc = proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . NPM_EXEC . ' --version', [array("pipe","r"), array("pipe","w"), array("pipe","w")], $pipes);

$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);

$exitCode = proc_close($proc);
    
  }
}

if (preg_match('/(\d+\.\d+\.\d+)/', $stdout, $matches))
  define('NPM_VERSION', $matches[1]);
else
  if (empty($stdout)) {
    if (!empty($stderr))
      $errors['NPM_VERSION'] = "\$stderr = $stderr";
  } // else $errors['NPM_VERSION'] = $stdout . ' does not match $version'; }

if (!is_file(APP_PATH . 'package.json'))
  if (@touch(APP_PATH . 'package.json'))
    file_put_contents(APP_PATH . 'package.json', <<<END
{
  "scripts": {
    "start": "NODE_ENV=development node main.js",
    "build": "NODE_ENV=production webpack"
  }
}
END
);

if (!is_dir(NODE_MODULES_PATH)) {
  $proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . NPM_EXEC . ' install',
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
  list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
  $errors['NPM-INSTALL']= (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
  
  $proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . NPM_EXEC . ' install -g npm',
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
  list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
  $errors['NPM-INSTALL']= (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
} else {
/*

  $proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . NPM_EXEC . ' --force update',
  array(
    array("pipe","r"),
    array("pipe","w"),
    array("pipe","w")
  ),
  $pipes);
  list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
  $errors['NPM-UPDATE']= (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
  
   // Error: npm WARN using --force Recommended protections disabled.
*/
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
  $npmExecPath = shell_exec('which ' . NPM_EXEC);
  if ($npmExecPath !== false) {
    $proc=proc_open(APP_SUDO . NPM_EXEC . ' cache clean -f',
    array(
      array("pipe","r"),
      array("pipe","w"),
      array("pipe","w")
    ),
    $pipes);
    list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];

    if (!preg_match('/npm\sWARN\susing\s--force\sRecommended\sprotections\sdisabled./', $stderr))
      $errors['NPM-CACHE-CLEAN-F'] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : (preg_match('/npm\sWARN\susing\s--force\sRecommended\sprotections\sdisabled./', $stderr) ? $stderr : ' Error: ' . $stderr)) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
  }


  // Error: npm WARN using --force Recommended protections disabled.

  if (!is_dir(NODE_MODULES_PATH . 'jquery') ) {
    $proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . NPM_EXEC . ' install jquery@3.7.1',
      array(
        array("pipe","r"),
        array("pipe","w"),
        array("pipe","w")
      ),
    $pipes);
    list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    $errors['NPM-INSTALL-JQUERY'] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
  }

  //webpack - Packs CommonJs/AMD modules for the browser
  
  $proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . 'webpack --version', // Prints out System, Binaries, Packages
      array(
        array("pipe","r"),
        array("pipe","w"),
        array("pipe","w")
      ),
    $pipes);
    list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    $errors['NPM-WEBPACK'] = (!isset($stdout) ? NULL : ltrim($stdout) . (isset($stderr) && $stderr === '' ? NULL : (preg_match('/sudo:\swebpack:\scommand\snot\sfound/', $stderr) ? '`webpack` is not currently installed (locally) on this computer.' . "\n" : ' Error: ' . $stderr)) . (isset($exitCode) && $exitCode == 0 ? NULL : /* 'Exit Code: ' . $exitCode*/ '' ));
    
    if (!isset($errors['NPM-WEBPACK']) && !empty($errors['NPM-WEBPACK'])) {
    
  if (!is_dir(NODE_MODULES_PATH . 'webpack') || !is_dir(NODE_MODULES_PATH . 'webpack-cli') ) {
    $proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . NPM_EXEC . ' install webpack webpack-cli --save-dev',
      array(
        array("pipe","r"),
        array("pipe","w"),
        array("pipe","w")
      ),
    $pipes);
    list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    $errors['NPM-INSTALL-WEBPACK[-cli]']= (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
  } else {
    // webpack.config.js
    if (is_dir(NODE_MODULES_PATH . 'webpack')) {
      if (!is_file(APP_PATH . 'webpack.config.js'))
        if (@touch(APP_PATH . 'webpack.config.js'))
          file_put_contents(APP_PATH . 'webpack.config.js', <<<END
module.exports = {
  entry: './your_entry_file.js', // Entry point of your application
  mode: '{APP_ENV}',
  output: {
    filename: 'bundle.js', // Output file name
    path: __dirname + '/dist',
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        },
      },
    ],
  },
};
END
);  
    }

    if (!is_dir(NODE_MODULES_PATH . 'babel-loader') ) {
      $proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . NPM_EXEC . ' install babel-loader @babel/core @babel/preset-env --save-dev',
        array(
          array("pipe","r"),
          array("pipe","w"),
          array("pipe","w")
        ),
      $pipes);
      list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
      $errors['NPM-INSTALL-BABEL-LOADER']= (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
    } else {
      if (!is_file(APP_PATH . '.babelrc'))
        if (@touch(APP_PATH . '.babelrc'))
          file_put_contents(APP_PATH . '.babelrc', <<<END
{
  "presets": ["@babel/preset-env"]
}

END
);

      if (!is_file(APP_PATH . '.babelrc'))
        if (@touch(APP_PATH . 'babel.config.js'))
          file_put_contents(APP_PATH . 'babel.config.js', <<<END
module.exports = {
  presets: ['@babel/preset-env']
};
END
);  
    }

    if (!is_file(APP_PATH . 'dist/bundle.js')) {
      $proc=proc_open((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : APP_SUDO) . NPM_EXEC . ' run build',
        array(
          array("pipe","r"),
          array("pipe","w"),
          array("pipe","w")
        ),
      $pipes);
      list($stdout, $stderr, $exitCode) = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
      $errors['NPM-RUN-BUILD[bundle.js]'] = (!isset($stdout) ? NULL : $stdout . (isset($stderr) && $stderr === '' ? NULL : ' Error: ' . $stderr) . (isset($exitCode) && $exitCode == 0 ? NULL : 'Exit Code: ' . $exitCode));
    }
  }  

    }
  }
}
