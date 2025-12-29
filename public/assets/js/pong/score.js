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