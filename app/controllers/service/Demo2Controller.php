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
        Ysf::warning("waning message");
        Ysf::error("error message");
        $this->outputJson(null, 'hello');
    }
}