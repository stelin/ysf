<?php
namespace app\controllers;

use ysf\base\Controller;

class DemoController extends Controller
{
    public function showHtml()
    {
        $this->render("show html");
    }
}