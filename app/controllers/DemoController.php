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
        $redis->mset(array('stelin2' => "stelin_cor2"));
        $redisGet = $redis->get("stelin");
        $redisMget = $redis->mget(array('stelin',"stelin2","stelin3","stelin5","stelin6"));
        $this->outputJson(null, "this default action name=".$this->getGet('name').json_encode(Ysf::app()->params)."-".$redisGet);
    }
    public function actionShowHtml()
    {
        $this->render("show html");
    }
}