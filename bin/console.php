<?php
!defined('CONSOLE') && define('CONSOLE', 1);
!defined('BEGIN_TIME') && define('BEGIN_TIME', microtime(true));

require_once '../vendor/autoload.php';
require_once '../app/config/mode.php';

$config = require('../app/config/'.strtolower(APP_ENV) . '/console.php');

(new ysf\console\Application($config))->run();





