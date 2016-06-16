<?php
function strict_error_handler($errno, $errstr, $errfile, $errline)
{
    die ("STRICT: {$errno} {$errstr} {$errfile} {$errline} ".PHP_EOL);
}
set_error_handler("strict_error_handler");

require_once('./application/root.php');
