<?php
namespace ysf\web;

interface UrlRuleInterface
{

    /**
     * 
     * @param \ysf\web\UrlManager $manager
     * @param \Swoole\Http\Request $request
     */
    public function parseRequest($manager, $request);

    public function createUrl($manager, $route, $params);
}