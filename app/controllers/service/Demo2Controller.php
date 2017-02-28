<?php
namespace app\controllers\service;

use ysf\base\Controller;

class Demo2Controller extends Controller {
    public function showJson()
    {
        $this->outputJson(null, "route show hello");
    }
    
    public function hello()
    {
        $this->outputJson(null, 'hello');
    }
}