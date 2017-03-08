<?php
namespace app\controllers;

use ysf\base\Controller;
use ysf\filters\LoginFilter;

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
        $this->outputJson(null, "this default action name=".$this->getGet('name'));
    }
    public function actionShowHtml()
    {
        $this->render("show html");
    }
}