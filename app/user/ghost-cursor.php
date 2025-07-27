<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ghost Cursor Hover Simulation</title>
  <style>
    body {
      height: 100vh;
      margin: 0;
      background: #f8f8f8;
      font-family: sans-serif;
      cursor: none; /* Hide real cursor */
    }

    .ghost-cursor {
      width: 16px;
      height: 16px;
      background: black;
      border-radius: 50%;
      position: absolute;
      pointer-events: none;
      z-index: 9999;
    }

    .hover-target {
      width: 150px;
      height: 100px;
      background: lightgray;
      margin: 40px;
      display: inline-block;
      transition: background 0.3s;
    }

    .hover-style {
      background: gold !important;
      box-shadow: 0 0 10px #ffc107;
    }
  </style>
</head>
<body>

  <div class="hover-target">Box 1</div>
  <div class="hover-target">Box 2</div>
  <div class="hover-target">Box 3</div>
  <div id="ghostCursor" class="ghost-cursor"></div>

  <script>
    const cursor = document.getElementById('ghostCursor');
    let pos = { x: 100, y: 100 };
    let target = { x: Math.random() * window.innerWidth, y: Math.random() * window.innerHeight };

    function randomTarget() {
      target.x = Math.random() * window.innerWidth;
      target.y = Math.random() * window.innerHeight;
    }

    function simulateHover(element) {
      document.querySelectorAll('.hover-style').forEach(el => el.classList.remove('hover-style'));
      if (element && element.classList.contains('hover-target')) {
        element.classList.add('hover-style');
        element.dispatchEvent(new MouseEvent('mouseover', { bubbles: true }));
      }
    }

    function moveGhostCursor() {
      const speed = 2;
      const dx = target.x - pos.x;
      const dy = target.y - pos.y;
      const dist = Math.sqrt(dx * dx + dy * dy);

      if (dist < 5) {
        setTimeout(() => {
          randomTarget();
          requestAnimationFrame(moveGhostCursor);
        }, 500 + Math.random() * 1000);
        return;
      }

      pos.x += (dx / dist) * speed;
      pos.y += (dy / dist) * speed;

      cursor.style.left = `${pos.x}px`;
      cursor.style.top = `${pos.y}px`;

      const hoveredElement = document.elementFromPoint(pos.x, pos.y);
      simulateHover(hoveredElement);

      requestAnimationFrame(moveGhostCursor);
    }

    moveGhostCursor();
  </script>
</body>
</html>