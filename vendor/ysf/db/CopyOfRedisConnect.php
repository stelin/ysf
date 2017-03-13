<?php
namespace ysf\base;

class RedisConnect extends ConnectPool
{
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
            if (! $connect->connect($server['sock'])) {
                throw new \RedisException("redis connect fail by sock, sock=" . $server['sock'], Errno::REDIS_ERROR);
            }
        } else {
            if (! $connect->connect($server['host'], $server['port'], $timeout)) {
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