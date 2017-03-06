<?php
namespace ysf\base;

use ysf\base\Object;
use ysf\Ysf;

class CoroutineRedis extends Object{
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
    public $db = 0;
    
    /**
     * @var array redis options, this is not must
     * @example
     * <pre>
     * $options = [
     *  \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP,
     *  \Redis::OPT_PREFIX => 'ugirls_module_'
     * ]
     * </pre>
     */
    public $options = array();
    
    /**
     * @var array you can use two methods to connect redis,one is host and port ,other is redis socket
     * @example
     * <pre>
     * $config = array(
     *             "host" => "127.0.0.1",
     *             "port" => "6379",
     *             "timeout" => 3,
     *           );
     *
     * $config = array(
     *              "sock" => "/tmp/redis.sock",
     *          )
     * </pre>
     */
    public $servers = array();
    
    /**
     * @var string log system profile prefix
     */
    public $profilePrefix   = 'redis.';
    
    /**
     * @var string log system count redis
     */
    public $countingPrefix  = 'redis.hit/req';
    
    
    /**
     * get value by key
     *
     * @param string $key   redis key
     * @throws UGRedisException
     * @return mixed
     */
    public function get($key)
    {
        $redis = $this->getRedisConnect();
        Ysf::profileStart($this->profilePrefix.__FUNCTION__);
        $ret = $redis->get($key);
        Ysf::counting($this->countingPrefix.__FUNCTION__, $ret == false ? 0 : 1, 1);
        Ysf::profileEnd($this->profilePrefix.__FUNCTION__);
    
        return $ret;
    }
    
    /**
     * set value by key
     *
     * @param string    $key        redis key
     * @param mixed     $value      redis value
     * @param int       $expire     key expire time
     * @throws UGRedisException
     * @return boolean
     */
    public function set($key, $value, $expire = 0)
    {
        $redis = $this->getRedisConnect();
    
        Ysf::profileStart($this->profilePrefix.__FUNCTION__);
        if($expire > 0){
            $ret = $redis->setex($key, $expire, $value);
        }else{
            $ret = $redis->set($key, $value);
        }
        Ysf::counting($this->countingPrefix.__FUNCTION__, $ret? 1 : 0, 1);
        Ysf::profileEnd($this->profilePrefix.__FUNCTION__);
    
        return $ret;
    }
    
    /**
     * get many keys values
     *
     * @param array $keys   key sets
     * @throws UGRedisException
     * @return array
     */
    public function mget(array $keys)
    {
        Ysf::profileStart($this->profilePrefix.__FUNCTION__);
        $redis = $this->getRedisConnect();
    
        $ret = $redis->mget($keys);
    
        $data = [];
        foreach ($ret as $index => $value){
            if(!isset($keys[$index])){
                continue;
            }
            $mkey = $keys[$index];
            if($value != false){
                $data[$mkey] = $value;
            }
        }
        Ysf::counting($this->countingPrefix.__FUNCTION__, count($data), count($keys));
        Ysf::profileEnd($this->profilePrefix.__FUNCTION__);
    
        return $data;
    }
    
    /**
     * set many keys and values
     *
     * @param array  $aryKV     keys values array
     * @param int    $expire    keys expire time
     * @return boolean
     */
    public function mset($aryKV, $expire = 0)
    {
        Ysf::profileStart($this->profilePrefix.__FUNCTION__);
        $redis = $this->getRedisConnect();
    
        $aryKeyValues = [];
        foreach ($aryKV as $key => $value){
            $svalue = $value;
            $aryKeyValues[$key] = $svalue;
        }
    
        $ret = $redis->mset($aryKeyValues);
        if($expire > 0){
            foreach ($aryKeyValues as $k => $v){
                $redis->expire($key, $expire);
            }
        }
        Ysf::profileEnd($this->profilePrefix.__FUNCTION__);
    
        return $ret;
    }
    
    /**
     * get redis connect status
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->redis->isConnected();
    }
    
    /**
     * set log system prefix
     *
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->profilePrefix = $prefix.".";
        $this->countingPrefix = $prefix.".hit/req.";
    }
    
    /**
     * get redis connect
     *
     * @return \Redis
     */
    private function getRedisConnect()
    {
        $config = $this->getConfig();
        return RedisConnect::getInstance()->get($config);
    }
    
    /**
     * get redis config
     *
     * @return array
     */
    private function getConfig()
    {
        $server = $this->initServer($this->servers);
        return array(
            'server' => $server,
            'db' => $this->db,
            'options' => $this->options,
        );
    }
    
    /**
     * init redis config
     *
     * @param array $server redis config
     * @return array
     */
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