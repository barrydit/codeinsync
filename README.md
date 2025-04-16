![Made with PHP](https://img.shields.io/badge/Made_with-PHP(Vanilla)-blue)
![Supports GIT(hub)](https://img.shields.io/badge/Supports-GIT(hub)-red)
![Supports Composer](https://img.shields.io/badge/Supports-Composer-brown)
![Dynamic JSON Badge](https://img.shields.io/badge/dynamic-json-green)

<h2>💻 CodeInSync — A Modular, PHP-Based Dev Environment</h2>
#codeinsync
<p align="center">
  <img style="text-align: center;" alt="demo loading" src="https://github.com/barrydit/codeinsync/assets/6217010/73ead4bf-8e20-47e4-a909-e259353a5275" width="60%" height="60%" /><br />
  Demo Loading...
</p>

CodeInSync is a lightweight, vanilla PHP application that functions as a dynamic, windowed workspace for managing code, clients, projects, and development tasks — without the overhead of a traditional framework.
<h3>⚙️ Features</h3>
<p><ul><li>Modular App Architecture: Self-contained app.*.php and ui.*.php files are loaded dynamically using output buffering and sorted into draggable windows via JavaScript.</li>
    <li>Project-Aware Context Switching: Intelligent directory detection via APP_PATH and APP_ROOT lets Git and Composer commands execute in project-specific contexts — useful for managing multiple client setups.</li>
    <li>Built-in Terminal: Run inline PHP commands or php -r scripts directly from the browser. eval() is sandboxed for testing and debugging, with support for WebSocket socket server communication for longer processes.</li>
    <li>Time & Activity Tracking: Background JavaScript monitors idle times and logs activity to JSON-based timesheets, providing historical views of work habits per project.</li>
    <li>Graph View (d3.js): Visualize project architecture with dynamic JSON-based node maps showing file relationships, includes, and app dependencies.</li>
    <li>Socket-Ready Execution: Offload heavy or interactive tasks to a separate PHP socket server (WSL-ready) with real-time status feedback via WebSockets.</li>
    <li>Zero Refresh UI: All internal navigation is handled via JavaScript and AJAX — iframe-free and fast.</li>
</ul></p>
<h3>🧠 Why No Framework (Yet)?</h3>

This app was intentionally built with vanilla PHP for speed, transparency, and full control. It's a dev tool, not a product — by developers, for developers. Frameworks like Laravel can be bolted on later if business logic or API complexity demands it.

![app_ace_editor](https://github.com/user-attachments/assets/ff747b1a-3bc5-4a07-8aaf-c92eaf287249)

![image](https://github.com/barrydit/codeinsync/assets/6217010/763efd9c-71f2-4e66-8ced-0e2b17c82da2)

The code has the ability to operate in a root directory (/public_html/) scenario, but only on initial install of the program, it corrects itself into a project root directory(project/[config|public]), where the configuration files are in in config, and the web code is under public. I learned in this course of work how to use .htaccess mod rewrite, and being able to change the url in order for [project/index.php] and [project/public/index.php] can still result in see the same thing. 

<p align="center">
  <img style="text-align: center;" src="https://github.com/barrydit/codeinsync/assets/6217010/cba3f3b1-3f9b-44d1-ba95-2f94d86e3994" width="60%" height="60%" />
<br />
<img style="text-align: center;" src="https://github.com/barrydit/codeinsync/assets/6217010/05b4ddf4-6087-4c9c-8a8e-58cf7d9705d6" width="60%" height="60%" />
<br />
<img style="text-align: center;" src="https://github.com/barrydit/codeinsync/assets/6217010/62d368bd-bc23-4dfe-a6b9-1fcc5808114e" width="60%" height="60%" />
</p>

I have a set of tools that I want to be able to replicate, as well as having 1 click solutions. Git is my next project, and then php. And then I have a terminal program I am working on for skeleton. I might just put it all into 1 project here on github. You can see at the end of the gif animation, that I have basic animation skills. Enough that I can create simple screen grab videos.
