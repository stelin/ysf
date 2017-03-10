<?php
namespace ysf\console;

use ysf\base\Object;

class Response extends Object
{
    private $status = 200;
    private $headers =[];
    
    public function init()
    {
        $this->headers['Content-Type'] = 'application/json';
    }
    
    public function status($status)
    {
        $this->status = $status;
    }
    
    public function end($string)
    {
        foreach ($this->headers as $key => $value){
            header("$key: $value");
        }
        
        echo $string;
    }
    
    public function header($key, $value){
        $this->headers[$key] =$value;
    }
}