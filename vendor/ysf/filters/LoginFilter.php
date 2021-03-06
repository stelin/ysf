<?php
namespace ysf\filters;

use ysf\base\Singleton;

class LoginFilter extends Filter
{
    use Singleton;
    
    public function preFilter()
    {
        return true;
    }
    
    public function postFilter()
    {
        return true;
    }
    
    public function denyFilter(){
        throw new \Exception('login error');
    }
}