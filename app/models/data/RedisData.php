<?php
namespace app\models\data;

use ysf\base\Singleton;

class RedisData 
{
    use Singleton;
    
    public function getMuti()
    {
        return array('one','two');
    }
}