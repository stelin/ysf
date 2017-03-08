<?php
namespace ysf\filters;

use ysf\base\Singleton;

class LoginFilter extends Filter
{
    use Singleton;
    
    public function preFilter()
    {
        return false;
    }
    
    public function postFilter()
    {
        return true;
    }
    
    public function denyFilter(){
        throw new \Exception('login error');
    }
}