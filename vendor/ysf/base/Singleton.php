<?php
namespace ysf\base;

use Swoole\Lock;
use ysf\Ysf;
use app\models\logic\RedisLogic;
use app\models\data\RedisData;
use phpDocumentor\Reflection\Types\This;

trait Singleton
{
    /**
     * @var self 线程安全运行
     */
    private static $instance = null;
    
    /**
     * @var Singleton  phpunit单测mock对象使用
     */
    private static $fakeObj = null;

    /**
     * 禁止clone对象
     */
    private function __clone(){

    }
    
    /**
     * 禁止构造函数创建对象
     */
    private function __construct(){
        
    }

    /**
     * 获取单实例对象
     * 
     * @return self object instance
     */
    public static function getInstance()
    {
        // phpunit
        if(defined('TEST') && TEST == true){
            $fakeObj = (self::$fakeObj == null)? new self(): self::$fakeObj;
            return $fakeObj;
        }
        
        Ysf::app()->singleLock->lock();
        if(self::$instance == null){
            self::$instance = new self();
        }
        Ysf::app()->singleLock->unlock();
        
        return self::$instance;
    }
    
    /**
     * 设置单测mock对象
     * 
     * @param Object $class
     */
    public static function set($class)
    {
        self::$fakeObj = $class;
    }
    
    /**
     * 清空mock对象
     */
    public static function clear()
    {
        self::$fakeObj = null;
    }
}