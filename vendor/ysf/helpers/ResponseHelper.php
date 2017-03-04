<?php
namespace ysf\helpers;

use ysf\Ysf;

class ResponseHelper
{
    /**
     * 
     * @param \Swoole\Http\Response $response
     * @param unknown $data
     * @param string $message
     * @param number $status
     * @param unknown $callback
     */
    public static function outputJson($response, $data = null, $message = '', $status = 200, $callback = null)
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
        
        self::flushLog();
        $response->status($status);
        $response->header('Content-Type', 'application/json');
        $response->end($json);
    }
    
    /**
     * 
     * @param \Swoole\Http\Response $response $response
     * @param string $data
     */
    public static function outputHtml($response, $html, $status = 200)
    {
        self::flushLog();
        $response->status($status);
        $response->end($html);
    }
    
    
    private static function flushLog()
    {
        Ysf::getLogger()->flush();
    }
    
    
}