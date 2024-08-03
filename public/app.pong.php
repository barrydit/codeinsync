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

if (!realpath($path = APP_BASE['resources'] . 'js/pong/'))
  (@!mkdir(APP_PATH . $path, 0755, true) ?: $errors['APP_PONG'] = "$path could not be created." );

if ($path) {
  if (!is_file($file = $path . 'ball.js'))
    if (@touch($file))
      file_put_contents($file, <<<END
export default class Ball {
  constructor (ctx, x, y) {
    this.ctx = ctx;
    this.x = x;
    this.y = y;
    this.width = 16;
    this.height = 16;
    this.xSpeed = 3;
    this.ySpeed = 0;
  }

  draw () {
    this.ctx.fillStyle = '#ff00ff';
    this.ctx.fillRect(this.x, this.y, this.width, this.height);
  }
}
END
);

  if (!is_file($file = $path . 'board.js'))
    if (@touch($file))
      file_put_contents($file, <<<END
export default class Board {
  constructor (ctx) {
    this.ctx = ctx
    this.left = 0;
    this.right = 500;
    this.top = 0;
    this.bottom = 170
  }

  collisionDetected(ball) {
    if (ball.x < this.left) {
      // collide with left boundry
      return 1
    }

    if (ball.x + ball.width > this.right) {
      // collide with right boundry
      return 2
    }

    if (ball.y < this.top) {
      // collide with top boundry
      return 3
    }

    if (ball.y + ball.height > this.bottom) {
      // collide with bottom boundry
      return 4
    }

    // no collision detected
    return 0;
  }

  draw () {
    this.ctx.fillStyle = "#FFF";
    this.ctx.fillRect(this.right / 2, 15, 2, 40);
    this.ctx.fillRect(this.right / 2, 65, 2, 40);
    this.ctx.fillRect(this.right / 2, 115, 2, 40);
    this.ctx.font = "50px serif"
    this.ctx.fillStyle = "#FFF"
    setTimeout(() => {this.ctx.fillText("READY?", 150, 100)}, 2000)
    
    this.ctx.fillRect(this.right / 2 - 40, 40, 10, 10);
    this.ctx.fillRect(this.right / 2 + 40, 40, 10, 10);
  }

  clear () {
    this.ctx.clearRect(this.left, this.top, this.right, this.bottom);
  }
}
END
);

  if (!is_file($file = $path . 'game.js'))
    if (@touch($file))
      file_put_contents($file, <<<END
import Board from './board.js'
import Paddle from './paddle.js'
import Ball from './ball.js'
import Score from './score.js'

class Game {
  constructor () {
    const canvas = document.getElementById("pong_game")
    this.ctx = canvas.getContext("2d")

    this.score = new Score()
  }

  init () {
    this.board = new Board(this.ctx)

    this.board.ctx.font = "50px serif"
    this.board.ctx.fillStyle = "#FFF"
    setTimeout(() => {this.board.ctx.fillText("GO!", 175, 100)}, 2000)

    this.leftPaddle = new Paddle(this.ctx, 10, 65, '#3DA45C')
    this.rightPaddle = new Paddle(this.ctx, 480, 65, '#2300ff')
    this.ball = new Ball(this.ctx, this.leftPaddle.x + this.leftPaddle.width, this.leftPaddle.y + this.leftPaddle.height / 2 - 8)

    this.board.draw()
    this.leftPaddle.draw()
    this.rightPaddle.draw()
    this.ball.draw()
  }

  setController () {
    const mousePad = document.getElementById('mousepad')
    mousePad.addEventListener('mousemove', (e) => {
      document.getElementById('xyposition').textContent = "clientY: " + (e.clientY - mousePad.offsetTop)

      this.leftPaddle.y_prev = this.leftPaddle.y
      this.rightPaddle.y_prev = this.rightPaddle.y

      this.rightPaddle.y = e.clientY - mousePad.offsetTop - this.rightPaddle.height / 2
      this.leftPaddle.y = e.clientY - mousePad.offsetTop - this.leftPaddle.height / 2
    })
  }
  
  bendBallOnCollision (that) {
    if ([1,2].includes(that.leftPaddle.collisionDetected(that.ball)) || [1,2].includes(that.rightPaddle.collisionDetected(that.ball))) {
      that.ball.xSpeed *= -1

      const diff = that.rightPaddle.y - that.rightPaddle.y_prev
      const absDiff = Math.abs(diff)
      const sign = diff / absDiff
      if (absDiff >= 5) {
        that.ball.ySpeed = 3 * sign
      } else if (absDiff >= 2) {
        that.ball.ySpeed = 2 * sign
      } else if (absDiff >= 1) {
        that.ball.ySpeed = sign
      } else {
        that.ball.ySpeed = 0
      }
    }
    
    if ([3,4].includes(that.leftPaddle.collisionDetected(that.ball)) || [3,4].includes(that.rightPaddle.collisionDetected(that.ball))) {
      that.ball.ySpeed *= -1
    }
  }

  play () {
    const that = this
    function start () {
      that.ball.x += that.ball.xSpeed      
      that.ball.y += that.ball.ySpeed

      that.board.clear()
      that.board.draw()
      that.leftPaddle.draw()
      that.rightPaddle.draw()
      that.ball.draw()

      that.bendBallOnCollision(that)

      const isRoundOver = that.board.collisionDetected(that.ball)

      if ([1, 2].includes(isRoundOver)) {
        isRoundOver !== 1 ? that.score.leftWin() : that.score.rightWin()

        setTimeout(() => {
          that.init()
          that.play()
        }, 2000)
        return
      } else if ([3, 4].includes(that.board.collisionDetected(that.ball))) {
        that.ball.ySpeed *= -1
      } // else { }

      const requestAnimationFrame = window.mozRequestAnimationFrame || window.requestAnimationFrame || window.msRequestAnimationFrame || window.oRequestAnimationFrame
      requestAnimationFrame(start)
    }
    start()
  }
}

export default Game
END
);

  if (!is_file($file = $path . 'index.js'))
    if (@touch($file))
      file_put_contents($file, <<<END
import Game from './game.js'

window.onload = () => {
  const game = new Game()
  game.init()
  game.setController()
  game.play()
}
END
);

  if (!is_file($file = $path . 'paddle.js'))
    if (@touch($file))
      file_put_contents($file, <<<END
export default class Paddle {
  constructor (ctx, x, y, fillStyle) {
    this.ctx = ctx;
    this.x = x;
    this.y = y;
    this.y_prev = y;
    this.width = 10;
    this.height = 40;
    this.fillStyle = fillStyle;
  }

  collisionDetected (ball) {
    if (this.x + this.width >= ball.x && this.x + this.width < ball.x + ball.width && ball.xSpeed < 0
      && ((this.y < ball.y && this.y + this.height > ball.y) || (this.y < ball.y + ball.height && this.y + this.height > ball.y + ball.height))) {
        // collide from right
        return 1;
    }

    if (this.x <= ball.x + ball.width && this.x > ball.x && ball.xSpeed > 0
      && ((this.y < ball.y && this.y + this.height > ball.y) || (this.y < ball.y + ball.height && this.y + this.height > ball.y + ball.height))) {
        // collide from left
        return 2;
    }

    if (this.y <= ball.y + ball.height && this.y > ball.y && ball.ySpeed > 0
    && ((this.x < ball.x && this.x + this.width > ball.x) || (this.x < ball.x + ball.width && this.x + this.width > ball.x + ball.width))) {
        // collide from top
        return 3;
    }

    if (this.y + this.height >= ball.y && this.y + this.height < ball.y + ball.height && ball.ySpeed < 0
    && ((this.x < ball.x && this.x + this.width > ball.x) || (this.x < ball.x + ball.width && this.x + this.width > ball.x + ball.width))) {
        // collide from bottom
        return 4;
    }

    // no collision detected
    return 0;
  }

  draw () {
    this.ctx.fillStyle = this.fillStyle;
    this.ctx.fillRect(this.x, this.y, this.width, this.height);
  }
}
END
);

  if (!is_file($file = $path . 'score.js'))
    if (@touch($file))
      file_put_contents($file, <<<END
export default class Score {
  constructor (left = 0, right = 0) {
    this.left = left
    this.right = right
    this.leftScoreEl = document.getElementById('score_1')
    this.rightScoreEl = document.getElementById('score_2')
  }

  leftWin() {
    this.left++
    this.leftScoreEl.textContent = this.left
  }

  rightWin() {
    this.right++
    this.rightScoreEl.textContent = this.right
  }
}
END
);

  if (!is_file($file = APP_BASE['resources'] . 'js/' . 'app.js'))
    if (@touch($file))
      file_put_contents($file, <<<END
import './bootstrap';
import './pong';
END
);


  if (!is_file($file = APP_BASE['resources'] . 'js/' . 'bootstrap.js'))
    if (@touch($file))
      file_put_contents($file, <<<END
import _ from 'lodash';
window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-$\{import.meta.env.VITE_PUSHER_APP_CLUSTER\}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
END
);
}


ob_start(); ?>

/* Styles for the absolute div */
#app_pong-container {
position: absolute;
display: none;
top: 5%;
//bottom: 60px;
left: 50%;
transform: translateX(-50%);
width: auto;
height: 400px;
background-color: rgba(255, 255, 255, 0.9);
color: black;
text-align: center;
padding: 10px;
z-index: 1;
}

<?php $appPong['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!-- <div class="container" style="border: 1px solid #000;"> -->
  <div id="app_pong-container" class="<?= (__FILE__ == get_required_files()[0] || (isset($_GET['app']) && $_GET['app'] == 'pong') ? 'selected' : '') ?>" style="border: 1px solid #000; overflow-x: scroll;">
    <div class="header ui-widget-header">
      <div style="display: inline-block;">Pong</div>
      <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_pong-container').style.display='none';">X</a>]</div> 
    </div>

      <div style="display: inline-block; width: auto;">
        <iframe src="<?= (is_dir($path = APP_PATH . APP_BASE['public']) && getcwd() == realpath($path) ? APP_BASE['public']:''  ) . basename(__FILE__) ?>" style="height: 350px; width: 750px;"></iframe>
      </div>
      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->
  </div>
<!-- </div> -->

<?php $appPong['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>



<?php $appPong['script'] = ob_get_contents();
ob_end_clean();

//dd($_SERVER);
ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Pong 2.0</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="resources/css/app.css" />
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

<?php
// (check_http_status('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_WWW . 'resources/js/tailwindcss-3.3.5.js')?
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
<?= /*$appWhiteboard['style'];*/ NULL; ?>
* { margin: 0; padding: 0; } /* to remove the top and left whitespace */

html, body { width: 100%; height: 100%; <?= ($_SERVER['SCRIPT_FILENAME'] == __FILE__ ? 'overflow:hidden;' : '') ?> } /* just to be sure these are full screen*/
</style>
</head>

<body class="bg-gray-300">
  <div class="h-screen flex justify-center items-center text-white">
    <div class="flex">
      <div class="w-40"></div>
      <div id="pong-panel" class="bg-black mx-4 px-8 py-4 rounded-md shadow-[5px_5px_20px_rgba(0,0,0,.4)]">
        <div class="text-right mb-2">
          Speed:
          <label class="mx-2">
            <input type="radio" name="speed" value="5">
            5x
          </label>
          <label class="mr-2">
            <input type="radio" name="speed" value="3">
            3x
          </label>
          <label>
            <input type="radio" name="speed" value="1" checked="checked">
            1x
          </label>
        </div>
        <div class="flex justify-between">
          <h3>Player: Wang</h3>
          <h3>Player: Barry</h3>
        </div>
        <canvas id="pong_game" width="500" height="170" class="border border-neutral-300">Canvas not supported</canvas>
        <div class="grid grid-cols-2 justify-items-center">
          <div id="score_1">0</div>
          <div id="score_2">0</div>
        </div>
      </div>
      <div id="right-panel" class="w-80 px-8 pt-12 bg-black rounded-md shadow-[5px_5px_20px_rgba(0,0,0,.4)]">
        <div id="xyposition" class="">&nbsp;</div>
        <div id="mousepad" class="h-[170px] w-full border border-white"></div>
      </div>
    </div>
  </div>
  <script type="module" src="resources/js/pong/index.js"></script>
  <script type="module" src="resources/js/bootstrap.js"></script>
</body>
</html>
<?php $appPong['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == realpath($_SERVER["SCRIPT_FILENAME"]) || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'php' && APP_DEBUG )
  die($appPong['html']);
