<?php
namespace ysf\base;

class ObjectPool
{
    private static $maxObjs = 3;
    private static $instance = null;
    private static $pool = [];
    
    private function __construct()
    {
        
    }
    
    public static function getInstance()
    {
        if (self::$instance == null) {
           self::$instance = new ObjectPool();
        }
        return self::$instance;
    }
    
    public static function getObject()
    {
        if (isset(self::$pool[$name])) {
            self::$pool[$name] = new \SplQueue();
        }
        
        $objSplQueue = self::$pool[$name];
        if($objSplQueue instanceof  \SplQueue && $objSplQueue->count() > 0){
            return self::$pool[$name]->pop();
        }
        return null;
    }
    
    public static function addObject($name, $obj)
    {
        if (! isset(self::$pool[$name])) {
            self::$pool[$name] = new \SplQueue();
        }
        
        $objSplQueue = self::$pool[$name];
        $objSplQueue = self::$pool[$name];
        if($objSplQueue instanceof  \SplQueue && $objSplQueue->count() < self::$maxObjs){
            self::$pool[$name]->push($obj);
        }
    }
}