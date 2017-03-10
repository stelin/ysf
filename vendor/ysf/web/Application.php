<?php
/**
 * @link https://github.com/stelin/ysf
 * @copyright Copyright 2016-2017 stelin develper.
 * @license https://github.com/stelin/ysf/license/
 */
namespace ysf\web;

use Swoole\Http\Server;
use ysf\base\Controller;
use ysf\Ysf;
use ysf\helpers\ResponseHelper;
use ysf\base\ApplicationContext;

/**
 * http server 
 *
 * @author stelin <phpcrazy@126.com>
 * @since 0.1
 */
class Application extends \ysf\base\Application implements IServer
{
    use CommandLine;
    
    /**
     * @var \Swoole\Server\Port
     */
    private $listen = null;
    
    public function start()
    {
        $httpConf = $this->http;
        $this->server = new \Swoole\Http\Server($httpConf['host'], $httpConf['port'], $httpConf['model'], $httpConf['type']);
        
        Ysf::setApp($this);
        $this->server->set($this->settings);
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('workerstart', [$this, 'onWorkerStart']);
        $this->server->on('task', [$this, 'onTask']);
        $this->server->on('finish', [$this, 'onFinish']);
        $this->server->on('pipemessage', [$this, 'onPipeMessage']);
        $this->server->on('workererror', [$this, 'onWorkerError']);
        $this->server->on('managerstart', [$this, 'onManagerStart']);
        $this->server->on('managerstop', [$this, 'onManagerStop']);
        $this->server->on('request', [$this, 'onRequest']);
        $this->server->on('workerstop', [$this, 'onWorkerStop']);
        
        if($this->tcpEnable){
            $tcpConf = $this->tcp;
            $this->listen = $this->server->listen($tcpConf['host'], $tcpConf['port'], $tcpConf['type']);
            $this->listen->on('connect', [$this, 'onConnect']);
            $this->listen->on('receive', [$this, 'onReceive']);
            $this->listen->on('close', [$this, 'onClose']);
            $this->listen->on('Packet', [$this, 'onPacket']);
        }
        
        $this->server->start();
    }
    
    
    /**
     * {@inheritDoc}
     * @see interfaceServer::onStart()
     */
    public function onStart(\Swoole\Http\Server $server)
    {
        file_put_contents($this->pidFile, $server->master_pid);
        file_put_contents($this->pidFile, ',' . $server->manager_pid, FILE_APPEND);
        swoole_set_process_name($this->processName." master process (/home/worker/data/www/swoole/bin/server.php)");
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onManagerStart()
     */
    public function onManagerStart(\Swoole\Http\Server $server)
    {
        swoole_set_process_name($this->processName." manager process");
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onWorkerStart()
     */
    public function onWorkerStart(\Swoole\Http\Server $server, int $workerId)
    {
        $setting = $server->setting;
        if($workerId >= $setting['worker_num']) {
            swoole_set_process_name($this->processName. " task process");
        } else {
            swoole_set_process_name($this->processName. " worker process");
        }
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onConnect()
     */
    public function onConnect(\Swoole\Http\Server $server, int $fd, int $fromId)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onReceive()
     */
    public function onReceive(\Swoole\Http\Server $server, int $fd, int $fromId, string $data)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onTimer()
     */
    public function onTimer(\Swoole\Http\Server $server, int $interval)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onTask()
     */
    public function onTask(\Swoole\Http\Server $server, int $taskId, int $srcWorkerId, string $data)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onPipeMessage()
     */
    public function onPipeMessage(\Swoole\Http\Server $server, int $fromWorkerId, string $message)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onRequest()
     */
    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        $logid = uniqid();
        ApplicationContext::setContexts([
            ApplicationContext::CONTEXT_LOGID => $logid,
            ApplicationContext::CONTEXT_BEGIN_TIME => microtime(true),
            ApplicationContext::CONTEXT_REQUEST => $request,
            ApplicationContext::CONTEXT_RESPONSE => $response,
        ]);
        
        // chrome 2 once request
        if(isset($request->server['request_uri']) && $request->server['request_uri'] == '/favicon.ico'){
            $response->end("favicon.ico");
            return false;
        }
        
        $controller = null;
        try {
            list($route, $params) = $this->urlManager->parseRequest($request);
            
            /* @var $controller Controller */
            list($controller, $actionId) = $this->createController($route);
            
            $controller->run($actionId, $params);
        } catch (\Exception $e) {
            ResponseHelper::outputJson(null, $e->getMessage());
            $controller->free();
        }
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onPacket()
     */
    public function onPacket(\Swoole\Http\Server $server, string $data, array $client_info)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onShutdown()
     */
    public function onShutdown(\Swoole\Http\Server $server)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onWorkerStop()
     */
    public function onWorkerStop(\Swoole\Http\Server $server, int $workerId)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onClose()
     */
    public function onClose(\Swoole\Http\Server $server, int $fd, int $fromId)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onFinish()
     */
    public function onFinish(\Swoole\Http\Server $server, int $taskId, string $data)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onWorkerError()
     */
    public function onWorkerError(\Swoole\Http\Server $server, int $workerId, int $workerPid, int $exitCode)
    {
        
        
    }

    /**
     * {@inheritDoc}
     * @see interfaceServer::onManagerStop()
     */
    public function onManagerStop(\Swoole\Http\Server $server)
    {
        
        
    }
    
    



}