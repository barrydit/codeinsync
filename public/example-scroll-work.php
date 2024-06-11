<?php 
require_once __DIR__ . '/config/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Process List with Scrolling Text</title>
  <style>
    .process-list {
      width: 150px;
      height: 200px;
      border: 2px solid #000;
      overflow: hidden;
      display: block;
      background-color: #ff0000;
    }

    .process {
      border: 1px solid #000;
      color: #fff;
      padding: 10px;
      margin: 5px 0;
      display: block;
      position: relative;
      white-space: nowrap; /* Prevent wrapping of the text */
      width: fit-content; /* Set the width to fit the content */
      clear: both; /* Ensure each process starts on a new line */
      overflow: hidden;
    }

    @keyframes scroll {
      0% { transform: translateX(50%); }
      100% { transform: translateX(-50%); }
    }

    .scrolling {
      animation: scroll 10s linear infinite;
    }
  </style>
</head>
<body>
  <div id="process-list" class="process-list" onmouseout="stopScroll()"></div>
  <input type="text" id="command-input" placeholder="Enter command">
  <button onclick="addProcess()">Add Process</button>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    function addProcess() {
      const commandInput = document.getElementById('command-input');
      const command = commandInput.value.trim();
      if (command === '') return;

      const processList = document.getElementById('process-list');
      const newProcess = document.createElement('div');
      newProcess.classList.add('process');
      newProcess.innerHTML = `<a href="#" onclick="deleteProcess(this)">[X]</a> ${command}`;

      // Add mouseover event
      newProcess.onmouseover = function() {
        setTimeout(() => { startScroll(newProcess); }, 3000);
      };
 
      setTimeout(() => {
        if (newProcess.parentNode) { // Check if process still exists
          newProcess.textContent = command;
          newProcess.onmouseover = function() {
            startScroll(newProcess);
          };
          // Send post request
          $.post('/your-server-endpoint', { cmd: command });
        }
      }, 3000);

      processList.prepend(newProcess);
      commandInput.value = '';
    }

    function deleteProcess(link) {
      const process = link.parentNode;
      process.parentNode.removeChild(process);
    }

    function startScroll(element) {
      const processList = document.getElementById('process-list');
      const duration = processList.offsetWidth / 100; // Adjust the speed by changing the divisor value
      element.style.animationDuration = `${duration}s`;
      element.classList.add('scrolling');
    }

    function stopScroll() {
      const processList = document.getElementById('process-list');
      const processes = processList.getElementsByClassName('process');
      for (const process of processes) {
        process.classList.remove('scrolling');
      }
    }
  </script>
</body>
</html>
