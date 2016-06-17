<?php
defined('APP_PATH') || define('APP_PATH', dirname(__FILE__));

require_once(APP_PATH . '/ext/line-bot-sdk-php/src/LINEBot.php');
require_once(APP_PATH . '/Logic.php');
require_once(APP_PATH . '/Log.php');
require_once(APP_PATH . '/Auth.php');

(new Logic())->run();

