<?php
namespace ysf\filters;

interface IFilter 
{
    public function doFilter(FilterChain $filterChain);
}