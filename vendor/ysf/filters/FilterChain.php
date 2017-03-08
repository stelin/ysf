<?php
namespace ysf\filters;

use ysf\base\Controller;

class FilterChain extends \SplQueue
{
    /**
     * @var \ysf\base\Controller
     */
    private $controller;
    private $route;
    private $params;
    
    
    public function run()
    {
        if($this->isEmpty() == false){
            /* @var $filter Filter */
            $filter = $this->shift();
            $filter->doFilter($this);
        }else{
            $this->controller ->runAction($this->route, $this->params);
        }
    }
    
    /**
     * @param field_type $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @param field_type $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @param field_type $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    
    
}