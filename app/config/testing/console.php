<?php
$config = ysf\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../base.php'),
    [
        'controllerNamespace' => 'app\consoles',
        'components' => [

        ],
        'params' => [
            'console' => 'console',
        ]
    ]
);

return $config;
