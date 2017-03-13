<?php
namespace ysf\base;

interface IPool {
    
    public static function getInstance();
    public function getConnect();
    public function free($object);
    public function release();
    public function setPoolSize(int $poolSize);
}