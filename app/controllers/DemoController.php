<?php
namespace app\controllers;

use ysf\base\Controller;

class DemoController extends Controller
{
    public function actionIndex()
    {
        $this->outputJson(null, "this default action");
    }
    public function actionShowHtml()
    {
        $this->render("show html");
    }
}