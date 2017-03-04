<?php
namespace ysf\base;

interface IPool {
    
    public function getInstance();
    public function get();
    public function free($object);
    public function release();
    public function setPoolSize(int $poolSize);
}