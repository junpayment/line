<?php
defined('APP_PATH') || define('APP_PATH', dirname(__FILE__));

require_once(APP_PATH . '/ext/vendor/autoload.php');

(new LineN\Logic())->run();
