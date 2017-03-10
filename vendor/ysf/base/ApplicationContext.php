<?php
namespace ysf\base;

use ysf\Ysf;

class ApplicationContext {
    
    const CONTEXT_LOGID = "logid";
    const CONTEXT_REQUEST = "request";
    const CONTEXT_RESPONSE = "response";
    const CONTEXT_BEGIN_TIME = "requestBeginTime";
    
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
        // ysf console comamnd
        if(defined('CONSOLE') && CONSOLE == 1){
            return self::getConsoleContext($key);
        }
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
    
    public static function getLogid()
    {
        return self::getContext(self::CONTEXT_LOGID);
    }
    
    public static function getBeginTime()
    {
        return self::getContext(self::CONTEXT_BEGIN_TIME);
    }
    
    
    
    public static function clearContext()
    {
        $cid = \Swoole\Coroutine::getuid();
        if(isset(self::$applicationContext[md5($cid)])){
            unset(self::$applicationContext[md5($cid)]);
        }
    }
    
    private static function getConsoleContext($key)
    {
        $context = null;
        switch ($key) {
            case self::CONTEXT_LOGID:
                $logid = isset($_SERVER['logid']) ? $_SERVER['logid'] : uniqid();
                $context = $logid;
                break;
            case self::CONTEXT_BEGIN_TIME:
                $beginTime = defined('BEGIN_TIME') ? BEGIN_TIME: microtime(true);
                $context = $beginTime;
                break;
            case self::CONTEXT_REQUEST:
                $context = Ysf::app()->request;
                break;
            case self::CONTEXT_RESPONSE:
                $context = $context = Ysf::app()->response;
                break;
        }
        
        return $context;
    }
}