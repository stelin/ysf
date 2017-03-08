<?php
namespace app\models\logic;
use ysf\base\Singleton;
use app\models\data\RedisData;

class RedisLogic {
    use Singleton;
    
    public function redisMuti()
    {
        $muti = RedisData::getInstance()->getMuti();
        return $muti;
    }
}