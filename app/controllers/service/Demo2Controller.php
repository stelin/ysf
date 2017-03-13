<?php
namespace app\controllers\service;

use ysf\base\Controller;
use ysf\Ysf;
use app\models\logic\RedisLogic;

class Demo2Controller extends Controller {
    public function actionShowJson()
    {
        $this->outputJson(null, "route show hello");
    }
    
    public function actionHello()
    {
        Ysf::profileStart("helloClass");
        Ysf::warning("waning message");
        Ysf::error("error message");
        Ysf::pushlog("name", "stelin");
        Ysf::profileEnd("helloClass");
        
        Ysf::profileStart("helloClass");
        Ysf::warning("waning message");
        Ysf::error("error message");
        Ysf::pushlog("name", "stelin");
        Ysf::profileEnd("helloClass");
        
        Ysf::counting("redis.get", 1, 10);
        Ysf::counting("redis.get", 2, 100);
        
        Ysf::trace("stelin");
        $str = 'hello'.SYSTEM_NAME.json_encode(RedisLogic::getInstance()->redisMuti());
        
        
        $this->outputJson(null, $str);
    }
}