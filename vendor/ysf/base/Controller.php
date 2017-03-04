<?php
namespace ysf\base;

use ysf\Ysf;
use ysf\exception\UnknownMethodException;
use ysf\helpers\ResponseHelper;

class Controller extends Object{
    
    /**
     * 
     * @var \Swoole\Http\Request $request
     */
    protected $request = null;
    /**
     * @var \Swoole\Http\Response $response
     */
    protected $response = null;
    
    private $controllerId = "";
    
    public function __construct($controllerId){
        $this->controllerId = $controllerId;
    }
    
    
    
    /**
     * @param \Swoole\Http\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @param \Swoole\Http\Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
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
        if (! method_exists($this, $id)) {
//             throw new \Exception($id.' action not found!');
//                throw new UnknownMethodException("action not found");
        }
        
        return call_user_func_array([$this, $id], $params);
    }
    
    public function render($templateId, $data = []){
        $html = $templateId;
        ResponseHelper::outputHtml($this->response, $html);
        $this->reset();
    }
    
    public function outputJson($data = null, $message = '', $status = 200, $callback = null)
    {
        ResponseHelper::outputJson($this->response, $data, $message, $status, $callback);
        $this->reset();
    }
    
    public function reset()
    {
        $this->controllerId = "";
        $this->request = null;
        $this->response = null;
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
}