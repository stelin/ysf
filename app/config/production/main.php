<?php
$config = ysf\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../base.php'),
    [
        'components' => [

        ],
        'params' => [
            'testing' => 'testing'
        ]
    ]
);

return $config;
