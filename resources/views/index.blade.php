<x-layout>
  <div class="h-screen flex justify-center items-center text-white">
    <div class="flex">
      <div class="w-40"></div>
      <div id="pong-panel" class="bg-black mx-4 px-8 py-4 rounded-md shadow-[5px_5px_20px_rgba(0,0,0,.4)]">
        <div class="text-right mb-2">
          Speed:
          <label class="mx-2">
            <input type="radio" name="speed" value="5" />
            5x
          </label>
          <label class="mr-2">
            <input type="radio" name="speed" value="3" />
            3x
          </label>
          <label>
            <input type="radio" name="speed" value="1" checked />
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
      <div id="right-panel" class="w-40 px-8 pt-12 bg-black rounded-md shadow-[5px_5px_20px_rgba(0,0,0,.4)]">
        <div id="xyposition" class="">&nbsp;</div>
        <div id="mousepad" class="h-[170px] w-full border border-white"></div>
      </div>
    </div>
  </div>
</x-layout>