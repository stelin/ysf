<?php
namespace app\controllers;

use ysf\base\Controller;
use ysf\filters\LoginFilter;
use ysf\Ysf;

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
        $this->outputJson(null, "this default action name=".$this->getGet('name').json_encode(Ysf::app()->params));
    }
    public function actionShowHtml()
    {
        $this->render("show html");
    }
}