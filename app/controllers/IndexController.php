<?php
namespace app\controllers;

use ysf\base\Controller;
use ysf\helpers\ResponseHelper;
class IndexController extends Controller
{
    public function actionIndex()
    {
        ResponseHelper::outputHtml("welecome to ysf!");
    }
}