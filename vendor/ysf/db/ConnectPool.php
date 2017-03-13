<?php
namespace ysf\db;

use ysf\Ysf;
use Swoole\Lock;
use ysf\base\IPool;

abstract class ConnectPool implements IPool{
    
    protected $maxSize = 10;
    protected static $poolSize = 1024*100;
    
    protected static $channel = null;
    protected static $instance = null;
    
    public function getConnect(){
        $connect = self::$channel->pop();
        if($connect === false){
            return $this->create();
        }
        return $connect;
    }
    
    public function create(){
        
    }
    public function free($object){
        if(self::$channel == null){
            throw new \Exception("pool channel pool is null");
        }
        
        if(self::$channel instanceof  \Swoole\Channel){
            $stats = self::$channel->stats();
            if($stats['queue_num'] < self::$maxSize){
                self::$channel->push($object);
            }
        }
    }
    public function release(){
        
    }
    
    public function setPoolSize(int $poolSize){
        $this->maxSize = $poolSize;
    }
}