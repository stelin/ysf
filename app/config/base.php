<?php
!defined('SYSTEM_NAME') && define('SYSTEM_NAME', 'ysf');
!defined('APP_PATH') && define('APP_PATH',  dirname(__FILE__).'/../../');
!defined('RUNTIME_PATH') && define('RUNTIME_PATH',  APP_PATH.'runtime/' . SYSTEM_NAME);
!defined('SETTING_PATH') && define('SETTING_PATH',  APP_PATH.'bin/ysf.ini');

$config = [
    'id' => SYSTEM_NAME,
    'basePath' =>dirname( __DIR__),
    'name' => SYSTEM_NAME,
    'runtimePath' => RUNTIME_PATH,
    'settingPath' => SETTING_PATH,
    
    // 组件配置
    'components' => [
        'urlManager' => [
            'rules' => [
                "/service" => "/service/demo2/showJson",
                '/post/<id:\d+>' => 'post/view'
            ],
        ],
        'log' => [
            'targets' => [
                'notice' => [
                    'class' => 'ysf\log\FileTarget',
                    'logFile' => '@runtime/notice.log',
                    'levels' => ['trace', 'notice'],
                ],
                'application' => [
                    'class' => 'ysf\log\FileTarget',
                    'logFile' => '@runtime/application.log',
                    'levels' => ['error','warning'],
                ],
            ],
            "logger" => [
                'class' => 'ysf\log\Logger',
            ],
        ],
    ],

    // 参数配置
    'params' => [
        
    ],
];

return $config;
