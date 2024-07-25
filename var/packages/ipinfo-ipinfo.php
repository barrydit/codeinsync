<?php
$source = "https://github.com/ipinfo/php/tree/master";
$composer_json = "{
    \"name\": \"barrydit/www\",
    \"description\": \"General Description\",
    \"type\": \"project\",
    \"homepage\": \"https://github.com/barrydit/www\",
    \"require\": {
        \"php\": \"^7.4||^8.0\",
        \"composer/composer\": \"^1.0\",
        \"aura/session\": \"^4.0\",
        \"laminas/laminas-session\": \"^2.5\",
        \"kevinoo/phpwhois\": \"dev-main\"
    },
    \"require-dev\": {
        \"pds/skeleton\": \"^1.0\"
    },
    \"license\": \"WTFPL\",
    \"authors\": [
        {
            \"name\": \"Barry Dick\",
            \"email\": \"barryd.it@gmail.com\"
        }
    ],
    \"minimum-stability\": \"dev\"
}
";
return '<form action method="POST">'
. '...'
. '</form>';