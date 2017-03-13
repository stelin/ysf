<?php
$redisServer = array(
    'host' => '172.17.0.2',
    'port' => 6379,
    'timeout' => 2
);

$cache = [
    'redis' => [
        'db'      => 0,
        'servers' => $redisServer,
        'options' => array(
            \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP,
            \Redis::OPT_PREFIX => 'ugirls_demo_'
        ),
    ],
];

return $cache;