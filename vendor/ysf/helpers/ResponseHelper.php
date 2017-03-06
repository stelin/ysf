<?php
namespace ysf\helpers;

use ysf\Ysf;
use ysf\base\ApplicationContext;

class ResponseHelper
{
    /**
     * 
     * @param unknown $data
     * @param string $message
     * @param number $status
     * @param unknown $callback
     */
    public static function outputJson($data = null, $message = '', $status = 200, $callback = null)
    {
        if($data === null){
            $data = new \stdClass();
        }
    
        $json = json_encode(array(
            'data'       => $data,
            'status'     => $status,
            'message'    => $message,
            'serverTime' => microtime(true)
        ));
        
        self::flushAndFree();
        
        $response = ApplicationContext::getResponse();
        $response->status($status);
        $response->header('Content-Type', 'application/json');
        $response->end($json);
    }
    
    /**
     * 
     * @param string $data
     */
    public static function outputHtml($html, $status = 200)
    {
        self::flushAndFree();
        
        $response = ApplicationContext::getResponse();
        $response->status($status);
        $response->end($html);
    }
    
    private static function flushAndFree()
    {
        Ysf::getLogger()->flush();
    }
    
    
}