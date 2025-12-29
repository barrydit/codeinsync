import Game from './game.js'

window.onload = () => {
  const game = new Game()
  game.init()
  game.setController()
  game.play()
}