<?php

/**
 * Class Log
 */
class Log
{
    /**
     * @param $priority
     * @param $method
     * @param $line
     * @param $message
     */
    public static function logger($priority, $method, $line, $message = "")
    {
        syslog($priority, "$method $line $message");
    }
}
