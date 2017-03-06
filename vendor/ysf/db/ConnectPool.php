<?php
namespace ysf\base;

abstract class ConnectPool implements IPool{
    
    private $maxSize = 10;
    private $poolSize = 1024*100;
    private $channel = null;
    private static $instance = null;
    
    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($config){
        if($this->channel == null){
            $this->channel = new Swoole\Channel($poolSize);
        }
        
        $connect = $this->channel->pop();
        if($connect === false){
            return $this->create($config);
        }
        
        return $connect;
    }
    
    public function create($config){
        
    }
    public function free($object){
        if($this->channel == null){
            throw new \Exception("pool channel pool is null");
        }
        
        if($this->channel instanceof  \Swoole\Channel){
            $stats = $this->channel->stats();
            if($stats['queue_num'] < self::$maxSize){
                $this->channel->push($object);
            }
        }
    }
    public function release(){
        
    }
    
    public function setPoolSize(int $poolSize){
        $this->maxSize = $poolSize;
    }
}