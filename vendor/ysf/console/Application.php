<?php
namespace ysf\console;

use ysf\helpers\ArrayHelper;
use ysf\Ysf;

/**
 * 
 *  @property \ysf\console\request $request console request
 *  @property \ysf\console\response $response console request
 */
class Application extends \ysf\base\Application
{
    public function __construct($config = [])
    {
        $_SERVER['logid'] = uniqid();
        parent::__construct($config);
        
        Ysf::setApp($this);
    }
    
    public function run(){
//         try {
            list ($route, $params) = $this->request->resolve();
            
            $this->request->server['path_info'] = $route;
            /* @var $controller Controller */
            list($controller, $actionId) = $this->createController($route);
            
            $controller->run($actionId, $params);
//         } catch (\Exception $e) {
            
//         }
    }
    
    public function coreComponents()
    {
        return ArrayHelper::merge(parent::coreComponents(), [
            'request' => 'ysf\console\request',
            'response' => 'ysf\console\response',
        ]);
    }
    
    public function getRequest()
    {
        return $this->get('request');
    }
    
    public function getResponse()
    {
        return $this->get('response');
    }
}