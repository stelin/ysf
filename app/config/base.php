<?php
!defined('SYSTEM_NAME') && define('SYSTEM_NAME', 'ysf');
!defined('WWW_DIR') && define('WWW_DIR', realpath(__DIR__ . '/../../..'));
!defined('RUNTIME_DIR') && define('RUNTIME_DIR', WWW_DIR . '/runtime/' . SYSTEM_NAME);

$config = [
    'id' => SYSTEM_NAME,
    'basePath' =>dirname( __DIR__),
    'name' => SYSTEM_NAME,
    'runtimePath' => RUNTIME_DIR,
    'tcpEnable' => false,
    
    // 组件配置
    'components' => [
        'urlManager' => [
            'rules' => [
                "/InterfaceMap" => "/InterfaceMap/Index/Index",
                '/post/<id:\d+>' => 'post/view'
            ],
        ],
    ],

    // 参数配置
    'params' => [
        
    ],
];

return $config;
