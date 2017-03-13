<?php
namespace app\controllers;

use ysf\base\Controller;
use ysf\filters\LoginFilter;
use ysf\Ysf;
use ysf\db\RedisPool;

class DemoController extends Controller
{
    public function filters()
    {
        return [
            LoginFilter::getInstance()
        ];
    }
    public function actionIndex()
    {
        $redis = RedisPool::getInstance()->getConnect();
        $redis->set("stelin", 'stelin_cor');
        $redisGet = $redis->get("stelin");
        $this->outputJson(null, "this default action name=".$this->getGet('name').json_encode(Ysf::app()->params)."-".$redisGet);
    }
    public function actionShowHtml()
    {
        $this->render("show html");
    }
}