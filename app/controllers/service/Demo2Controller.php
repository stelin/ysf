<?php
namespace app\controllers\service;

use ysf\base\Controller;
use ysf\Ysf;

class Demo2Controller extends Controller {
    public function showJson()
    {
        $this->outputJson(null, "route show hello");
    }
    
    public function hello()
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
        
        $str = 'hello'.SYSTEM_NAME;
        
        $a = $b;
        
        $this->outputJson(null, $str);
    }
}