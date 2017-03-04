<?php
namespace ysf\base;

class ApplicationContext {
    
    const CONTEXT_LOGID = "logid";
    const CONTEXT_BEGIN_TIME = "requestBeginTime";
    const CONTEXT_URI = "requestUri";
    
    private static $applicationContext = [];
    
    private function __construct(){
        
    }
    
    public static function setContext(string $key, $mixed){
        $cid = \Swoole\Coroutine::getuid();
        self::$applicationContext[md5($cid)][$key] = $mixed;
    }
    
    public static function setContexts(array $contexts){
        $cid = \Swoole\Coroutine::getuid();
        foreach ($contexts as $key => $context){
            self::$applicationContext[md5($cid)][$key] = $context;
        }
    }
    
    public static function getContext(string $key)
    {
        $cid = \Swoole\Coroutine::getuid();
        if(!isset(self::$applicationContext[md5($cid)][$key])){
            return null;
        }
        return self::$applicationContext[md5($cid)][$key];
    }
    
    public static function clearContext()
    {
        $cid = \Swoole\Coroutine::getuid();
        if(isset(self::$applicationContext[md5($cid)])){
            unset(self::$applicationContext[md5($cid)]);
        }
    }
}