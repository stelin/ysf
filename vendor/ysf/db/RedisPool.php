<?php
namespace ysf\db;

use ysf\Ysf;

class RedisPool extends ConnectPool
{
    /**
     * @var string default redis host
     */
    const DEFAULT_HOST = "127.0.0.1";
    
    /**
     * @var int default redis port
     */
    const DEFAULT_PORT = 6379;
    
    /**
     * @var int default redis timeout
     */
    const DEFAULT_TIMEOUT = 2;
    
    
    /**
     * @var int redis db is to use
     */
    const DEFAULT_DB = 0;
    
    public static function getInstance(){
    
        // 线程安全
        //         Ysf::app()->singleLock->lock();
        if(self::$instance == null){
            self::$instance = new self();
            self::$channel = new \Swoole\Channel(self::$poolSize);
        }
        //         Ysf::app()->singleLock->lock();
    
        return self::$instance;
    }
    
    /**
     * 创建连接
     * 
     * @return \Redis
     * {@inheritDoc}
     * @see \ysf\base\ConnectPool::create()
     */
    public function create(){
        
        /* @var $connect Redis */
        $connect = new RedisConnect();
        $config = $this->getConfig();
        $options = $config['options'];
        
        $db = $config['db'];
        $server = $config['server'];
        
        $timeout = $server['timeout'];
        
        // sock连接redis
        if (isset($server['sock'])) {
            if (! $connect->connect($server['sock'])) {
                throw new \RedisException("redis connect fail by sock, sock=" . $server['sock'], Errno::REDIS_ERROR);
            }
        } else {
            if (! $connect->connect($server['host'], $server['port'], $timeout)) {
                throw new \RedisException("redis connect fail by host and port, host=" . $server['host'] . " port=" . $server['port'] . " timeout=" . $timeout, Errno::REDIS_ERROR);
            }
        }
        
        foreach ($options as $optName => $optValue) {
//             $connect->setOption($optName, $optValue);
        }
        
        $connect->select($db);
        
        return $connect;
    }
    
    private function getConfig()
    {
        if(!isset(Ysf::app()->params['cache']['redis']['servers'])){
            throw new \RedisException("cache redis is not config");
        }
        
        $config = Ysf::app()->params['cache']['redis'];
        $db = $config['db'];
        $servers = $config['servers'];
        $options = $config['options'];
        
        $server = $this->initServer($servers);
        
        return [
            'server' => $server,
            'db' => $db,
            'options' => $options
        ];
    }
    
    private function initServer($server)
    {
        $newServer = $server;
    
        if (! isset($server['timeout'])) {
            $newServer['timeout'] = self::DEFAULT_TIMEOUT;
        }
        if (isset($server['sock'])) {
            return $newConfig;
        }
        if (! isset($server['host'])) {
            $newServer['host'] = self::DEFAULT_HOST;
        }
        if (! isset($server['port'])) {
            $newServer['port'] = self::DEFAULT_PORT;
        }
        return $newServer;
    }
}