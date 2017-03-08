<?php
namespace ysf\filters;

class Filter implements IFilter
{
    public function doFilter(FilterChain $filterChain){
        if($this->preFilter()){
            $filterChain->run();
            $this->postFilter();
        }else{
           $this->denyFilter(); 
        }
    }
    
    public function preFilter()
    {
        return true;
    }
    
    public function postFilter()
    {
        return true;
    }
    
    public function denyFilter()
    {
        throw new \Exception("filter deny");
    }
}