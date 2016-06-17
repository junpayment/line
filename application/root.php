<?php
defined('APP_PATH') || define('APP_PATH', dirname(__FILE__));

require_once(APP_PATH . '/ext/vendor/autoload.php');
require_once(APP_PATH . '/Logic.php');
require_once(APP_PATH . '/Auth.php');
require_once(APP_PATH . '/Log.php');
require_once(APP_PATH . '/Request.php');

(new Logic())->run();
