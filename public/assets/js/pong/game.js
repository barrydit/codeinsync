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