<?php
namespace ysf\console;

use ysf\base\Object;
use ysf\Ysf;

class Controller extends Object
{
    private $default_action = "index";
    private $controllerId = "";
    
    public function __construct($controllerId){
        // 逐条打印日志
        Ysf::getLogger()->flushInterval = 1;
        $this->controllerId = $controllerId;
    }
    
    public function run($route, $params = [])
    {
        $this->runAction($route, $params);
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
    
        call_user_func_array([$this, $method], $params);
        
        $this->afterAction();
    }
    
    public function afterAction()
    {
        Ysf::getLogger()->flush(true);
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