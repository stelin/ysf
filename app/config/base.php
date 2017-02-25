<?php
!defined('SYSTEM_NAME') && define('SYSTEM_NAME', 'ysf');
!defined('WWW_DIR') && define('WWW_DIR', realpath(__DIR__ . '/../..'));
!defined('RUNTIME_DIR') && define('RUNTIME_DIR', WWW_DIR . '/runtime/' . SYSTEM_NAME);

$config = [
    'id' => SYSTEM_NAME,
    'basePath' => __DIR__.DIRECTORY_SEPARATOR.'../app',
    'name' => SYSTEM_NAME,
    'runtimePath' => RUNTIME_DIR,
    'tcpEnable' => false,
    'configs' =>[
        'http' => [
            'host' => '127.0.0.1',
            'port' => 8099,
            'mode' => SWOOLE_PROCESS,
            'type' => SWOOLE_SOCK_TCP,
        ],
        'tcp' => [
            'host' => '127.0.0.1',
            'port' => 8099,
            'mode' => SWOOLE_PROCESS,
            'type' => SWOOLE_SOCK_TCP,
        ]
    ],
    // 组件配置
    'components' => [
        
    ],

    // 参数配置
    'params' => [
        
    ],
];

return $config;
