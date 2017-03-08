<?php
namespace ysf\base;

use ysf\Ysf;
use ysf\exception\UnknownMethodException;
use ysf\helpers\ResponseHelper;
use ysf\filters\FilterChain;
use ysf\filters\Filter;

class Controller extends Object{
    
    private $default_action = "index";
    private $controllerId = "";
    private $filterChain = null;
    
    public function __construct($controllerId){
        $this->controllerId = $controllerId;
        $this->filterChain = new FilterChain();
    }
    
    /**
     * 
     * @param string $route
     * @param array $params
     * @return \Swoole\Http\Response
     */
    public function run($route, $params = [])
    {
        $this->runActionWithFilters($route, $params);
    }
    
    public function render($templateId, $data = []){
        $html = $templateId;
        ResponseHelper::outputHtml($html);
        $this->free();
    }
    
    public function outputJson($data = null, $message = '', $status = 200, $callback = null)
    {
        ResponseHelper::outputJson($data, $message, $status, $callback);
        $this->free();
    }
    
    public function free()
    {
        while ($this->filterChain->isEmpty() == false){
            $this->filterChain->shift();
        }
        
        ObjectPool::getInstance()->addObject($this->controllerId, $this);
        ApplicationContext::clearContext();
    }
    
    
    public function afterAction()
    {
        return true;
    }

    public function beforeAction()
    {
        return true;
    }

    protected function getGet($key, $default = null)
    {
        $get = $this->getGetParams();
        return isset($get[$key])?$get[$key]:$default;
    }
    
    protected function getPost($key, $default = null)
    {
        $post = $this->getPostParams();
        return isset($post[$key]) ? $post[$key] : $default;
    }
    
    protected function getRequest($key, $default = null)
    {
        $get = $this->getGetParams();
        $post = $this->getPostParams();
        $request = array_merge($get, $post);
        
        return isset($request[$key]) ? $request[$key] : $default;
    }
    
    protected function filters()
    {
        return [];
    }
    
    
    public function runActionWithFilters($route, $params)
    {
        foreach ($this->filters() as $filter){
            if($filter instanceof Filter){
                $this->filterChain->push($filter);
            }
        }
    
        if($this->filterChain->isEmpty()){
            $this->runAction($route, $params);
        }else{
            $this->filterChain->setController($this);
            $this->filterChain->setRoute($route);
            $this->filterChain->setParams($params);
            $this->filterChain->run();
        }
    }
    
    
    public function runAction($id, $params = [])
    {
        if($this->beforeAction() === false){
            throw new \Exception("before action error");
        }
    
        $id = $this->getDefaultAction($id);
        $method = 'action'.ucfirst($id);
        if (! method_exists($this, $method)) {
            throw new UnknownMethodException("action not found");
        }
    
        return call_user_func_array([$this, $method], $params);
    }
    
    
    private function getGetParams()
    {
        $request = ApplicationContext::getRequest();
        $get = [];
        if (property_exists($request, 'get')) {
            $get = $request->get;
        }
        
        return $get;
    }
    
    private function getPostParams()
    {
        $request = ApplicationContext::getRequest();
        $post = [];
        if (property_exists($request, 'post')) {
            $get = $request->post;
        }
        
        return $post;
    }
    

    private function getDefaultAction($id)
    {
        $id = empty($id) ? $this->default_action : $id;
        return $id;
    }
}