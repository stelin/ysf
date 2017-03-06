<?php
namespace ysf\base;

use ysf\Ysf;

class ObjectPool
{
    private $maxObjs = 3;
    private $poolSize = 1024*100;
    private $pool = [];
    
    private static $instance = null;
    
    private function __construct()
    {
        
    }
    
    public static function getInstance()
    {
        if (self::$instance == null) {
           self::$instance = new ObjectPool();
        }
        return self::$instance;
    }
    
    public function getObject($name)
    {
        if (!isset($this->pool[$name])) {
            $this->pool[$name] = new \Swoole\Channel($this->poolSize);
        }
        
        $channel = $this->pool[$name];
        if($channel instanceof  \Swoole\Channel){
            return $this->pool[$name]->pop();
        }
        return null;
    }
    
    public function addObject($name, $obj)
    {
        if (!isset($this->pool[$name])) {
            $this->pool[$name] = new \Swoole\Channel($this->poolSize);
        }
        
        $channel = $this->pool[$name];
        if($channel instanceof  \Swoole\Channel){
            
            $stats = $this->pool[$name]->stats();
            if($stats['queue_num'] < $this->maxObjs){
                $this->pool[$name]->push($obj);
            }
            
        }
    }
}