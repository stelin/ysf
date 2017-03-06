<?php
namespace ysf\base;

class ApplicationContext {
    
    const CONTEXT_LOGID = "logid";
    const CONTEXT_BEGIN_TIME = "requestBeginTime";
    const CONTEXT_REQUEST = "request";
    const CONTEXT_RESPONSE = "response";
    
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
    
    /**
     * 
     * @return \Swoole\Http\Request
     */
    public static function getRequest()
    {
        return self::getContext(self::CONTEXT_REQUEST);
    }
    
    /**
     *
     * @return \Swoole\Http\Response
     */
    public static function getResponse()
    {
        return self::getContext(self::CONTEXT_RESPONSE);
    }
    
    
    
    public static function clearContext()
    {
        $cid = \Swoole\Coroutine::getuid();
        if(isset(self::$applicationContext[md5($cid)])){
            unset(self::$applicationContext[md5($cid)]);
        }
    }
}