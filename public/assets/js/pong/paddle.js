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