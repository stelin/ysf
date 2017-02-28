<?php
namespace ysf\helpers;

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
        
        $response->status($status);
        $response->header('Content-Type', 'application/json');
        $response->end($json);
    }
    
    
}