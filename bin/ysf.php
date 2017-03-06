<?php
require_once '../vendor/autoload.php';
require_once '../app/config/mode.php';

$config = require('../app/config/'.strtolower(APP_ENV) . '/main.php');

(new ysf\server\Application($config))->run();





