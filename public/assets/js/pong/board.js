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