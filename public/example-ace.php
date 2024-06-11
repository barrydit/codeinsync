<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiple Ace Editor Instances</title>
    <style>
        .editor {
            width: 500px;
            height: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div id="editor1" class="editor">This is the first editor.</div>
    <div id="editor2" class="editor">This is the second editor.</div>

    <script src="resources/js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script> 
    <script src="resources/js/ace/src/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var editor1 = ace.edit("editor1");
            editor1.setTheme("ace/theme/monokai");
            editor1.session.setMode("ace/mode/javascript");

            var editor2 = ace.edit("editor2");
            editor2.setTheme("ace/theme/github");
            editor2.session.setMode("ace/mode/html");
        });
    </script>
</body>
</html>
