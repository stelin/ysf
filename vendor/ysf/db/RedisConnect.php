<?php
namespace ysf\base;

class RedisConnect implements IPool
{
    private static $instance = null;
    private static $channel = null;
    
    private function __construct()
    {
    
    }
    public function getInstance(){
        if(self::$instance == null){
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($config){
        $poolSize = 1024*10;
        if(self::$channel == null){
            self::$channel = new Swoole\Channel($poolSize);
        }
        
        $connect = self::$channel->pop();
        if($connect === false){
            return self::create($config);
        }
        
        return $connect;
    }
    public function free($object){
        if(self::$channel == null){
            throw new \RedisException("redis channel pool is null");
        }
        $result = self::$channel->push($object);
        if($result === false){
            // 日志记录
        }
    }
    public function release(){
        
    }
    
    private static function create($config)
    {
        /* @var $connect \Redis */
        $connect = new Swoole\Coroutine\Redis();
        $options = $config['options'];
        
        $db = $config['db'];
        $server = $config['server'];
        
        $timeout = $server['timeout'];
        
        // sock连接redis
        if (isset($server['sock'])) {
            if (! $redis->connect($server['sock'])) {
                throw new \RedisException("redis connect fail by sock, sock=" . $server['sock'], Errno::REDIS_ERROR);
            }
        } else {
            if (! $redis->connect($server['host'], $server['port'], $timeout)) {
                throw new \RedisException("redis connect fail by host and port, host=" . $server['host'] . " port=" . $server['port'] . " timeout=" . $timeout, Errno::REDIS_ERROR);
            }
        }
        
        foreach ($options as $optName => $optValue) {
            $connect->setOption($optName, $optValue);
        }
        
        $connect->select($db);
        
        return $connect;
    }
}