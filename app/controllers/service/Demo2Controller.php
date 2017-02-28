<?php
namespace app\controllers\service;

use ysf\base\Controller;

class Demo2Controller extends Controller {
    public function showJson()
    {
        $this->outputJson(['hello world!']);
    }
}