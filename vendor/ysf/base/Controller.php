<?php
namespace ysf\base;

use ysf\Ysf;
use ysf\exception\UnknownMethodException;
use ysf\helpers\ResponseHelper;

class Controller extends Object{
    
    private $default_action = "index";
    private $controllerId = "";
    
    public function __construct($controllerId){
        $this->controllerId = $controllerId;
    }
    
    /**
     * 
     * @param string $route
     * @param array $params
     * @return \Swoole\Http\Response
     */
    public function run($route, $params = [])
    {
        return $this->runAction($route, $params);
    }
    
    
    public function runAction($id, $params = [])
    {
        
        if($this->beforeAction() === false){
            return false;
        }
        
        $id = $this->getDefaultAction($id);
        $method = 'action'.ucfirst($id);
        if (! method_exists($this, $method)) {
               throw new UnknownMethodException("action not found");
        }
        
        return call_user_func_array([$this, $method], $params);
    }
    
    public function render($templateId, $data = []){
        $html = $templateId;
        ResponseHelper::outputHtml($html);
        $this->reset();
    }
    
    public function outputJson($data = null, $message = '', $status = 200, $callback = null)
    {
        ResponseHelper::outputJson($data, $message, $status, $callback);
        $this->reset();
    }
    
    public function reset()
    {
        $this->controllerId = "";
        ObjectPool::getInstance()->addObject($this->controllerId, $this);
    }
    
    
    public function afterAction()
    {
        return true;
    }
    
    public function beforeAction()
    {
        return true;
    }

    private function getDefaultAction($id)
    {
        $id = empty($id) ? $this->default_action : $id;
        return $id;
    }
}