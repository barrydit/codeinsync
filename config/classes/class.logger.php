<?php
class Logger
{
    public static function init()
    {
        // Initialize your logger here (e.g., Monolog)
    }

    public static function error($message)
    {
        // Log your error message
        error_log($message . '   testing');
    }
}
