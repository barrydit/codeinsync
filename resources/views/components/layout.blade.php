<!DOCTYPE html>
<html>
<head>
  <title>Pong 2.0</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite('resources/css/app.css')
</head>
<body class="bg-gray-300">
  {{ $slot }}
  @vite('resources/js/app.js')
</body>
</html>