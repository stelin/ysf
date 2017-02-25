<?php
require_once '../vendor/autoload.php';

$config = "";
$serverConf = require_once 'conf.php';
(new ysf\server\Application($config))->run($serverConf);





