<?php
/**
 * @link https://github.com/stelin/ysf
 * @copyright Copyright 2016-2017 stelin develper.
 * @license https://github.com/stelin/ysf/license/
 */
namespace ysf\server;

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
class Application extends \ysf\web\Application implements InterfaceServer
{

    /**
     * @var string
     */
    const DEFAULT_HTTP_HOST = "172.17.0.3";

    /**
     * @var int
     */
    const DEFAULT_HTTP_PORT = 80;

    /**
     * @var string
     */
    const DEFAULT_TCP_HOST = "127.0.0.1";

    /**
     * @var int
     */
    const DEFAULT_TCP_PORT = 8099;

    const DEFAULT_MODEL = SWOOLE_PROCESS;
    
    const DEFAULT_TYPE = SWOOLE_SOCK_TCP;
    
    /**
     * @var \Swoole\Server\Port
     */
    private $listen = null;
    
    public function start()
    {
        $httpConf = $this->getHttpConfig();
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
            $tcpConf = $this->getTcpConfig();
            $this->listen = $this->server->listen($tcpConf['host'], $tcpConf['port'], $tcpConf['type']);
            $this->listen->on('connect', [$this, 'onConnect']);
            $this->listen->on('receive', [$this, 'onReceive']);
            $this->listen->on('close', [$this, 'onClose']);
            $this->listen->on('Packet', [$this, 'onPacket']);
        }
        
        $this->server->start();
    }
    
    private function getHttpConfig()
    {
        $configs = [];
        $type = self::DEFAULT_TYPE;
        $model = self::DEFAULT_MODEL;
        $host = self::DEFAULT_HTTP_HOST;
        $port = self::DEFAULT_HTTP_PORT;
        if(isset($this->http['type'])){
            $type = $this->http['type'];
        }
        if(isset($this->http['model'])){
            $model = $this->http['model'];
        }
        if(isset($this->http['host'])){
            $host = $this->http['host'];
        }
        if(isset($this->http['prot'])){
            $port = $this->http['port'];
        }
        $configs['host'] = $host;
        $configs['port'] = $port;
        $configs['type'] = $type;
        $configs['model'] = $model;
        
        return $configs;        
    }
    
    private function getTcpConfig()
    {
        $configs =[];
        $type = self::DEFAULT_TYPE;
        $host = self::DEFAULT_TCP_HOST;
        $port = self::DEFAULT_TCP_PORT;
        
        if(isset($this->tcp['type'])){
            $type = $this->tcp['type'];
        }
        if(isset($this->tcp['host'])){
            $host = $this->tcp['host'];
        }
        if(isset($this->tcp['prot'])){
            $port = $this->tcp['port'];
        }
        $configs['host'] = $host;
        $configs['port'] = $port;
        $configs['type'] = $type;
        
        return $configs;
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
            ApplicationContext::CONTEXT_URI => $request->server['path_info']
        ]);
        
        // chrome 2 once request
        if(isset($request->server['request_uri']) && $request->server['request_uri'] == '/favicon.ico'){
            return false;
        }
        
        try {
            list($route, $params) = $this->urlManager->parseRequest($request);
            
            /* @var $controller Controller */
            list($controller, $actionId) = $this->createController($route);
            
            $controller->setRequest($request);
            $controller->setResponse($response);
            $controller->run($actionId, $params);
        } catch (\Exception $e) {
            ResponseHelper::outputJson($response, null, $e->getMessage());
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