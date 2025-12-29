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