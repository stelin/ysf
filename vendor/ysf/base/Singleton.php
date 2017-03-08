<?php
namespace ysf\base;

trait Singleton
{
    private static $instance = null;
    private static $fakeObj = null;

    private function __clone(){

    }
    private function __construct(){

    }

    public static function getInstance()
    {
        // phpunit
        if(defined('TEST') && TEST == true){
            $fakeObj = (self::$fakeObj == null)? new self(): self::$fakeObj;
            return $fakeObj;
        }
        
        if(self::$instance == null){
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    public static function set($class)
    {
        self::$fakeObj = $class;
    }
    
    public static function clear()
    {
        self::$fakeObj = null;
    }
}