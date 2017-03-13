<?php
namespace ysf\db;

use ysf\Ysf;

class RedisConnect extends \Swoole\Coroutine\Redis
{
    public $profilePrefix   = 'redis.';
    public $countingPrefix  = 'redis.hit/req.';
    
    public function set($key, $value, $expire = 0)
    {
        Ysf::profileStart($this->profilePrefix.__FUNCTION__);
        if($expire > 0){
            $ret = parent::setex($key, $expire, $value);
        }else{
            $ret = parent::set($key, $value);
        }
        Ysf::profileEnd($this->profilePrefix.__FUNCTION__);
        
        return $ret;
    }
    
    public function get($key)
    {
        Ysf::profileStart($this->profilePrefix.__FUNCTION__);
        $ret = parent::get($key);
        Ysf::counting($this->countingPrefix.__FUNCTION__, $ret == false ? 0 : 1, 1);
        Ysf::profileEnd($this->profilePrefix.__FUNCTION__);
    
        return $ret;
    }
    
    public function mset($aryKV, $expire = 0)
    {
        Ysf::profileStart($this->profilePrefix.__FUNCTION__);
    
        $aryKeyValues = [];
        foreach ($aryKV as $key => $value){
            $aryKeyValues[$key] = $value;
        }
    
        $ret = parent::mset($aryKeyValues);
        if($expire > 0){
            foreach ($aryKeyValues as $k => $v){
                parent::expire($key, $expire);
            }
        }
        Ysf::profileEnd($this->profilePrefix.__FUNCTION__);
    
        return $ret;
    }
    
    public function mget(array $keys)
    {
        Ysf::profileStart($this->profilePrefix.__FUNCTION__);
        $ret = parent::mget($keys);
    
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
}