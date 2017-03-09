<?php
/**
 * @link https://github.com/stelin/ysf
 * @copyright Copyright 2016-2017 stelin develper.
 * @license https://github.com/stelin/ysf/license/
 */
namespace ysf\web;

/**
 * swoole callback list
 *
 * @author stelin <phpcrazy@126.com>
 * @since 0.1
 */
interface InterfaceServer
{

    /**
     * 服务启动后在主进程的主线程回调该函数，此事件之前已经完成如下操作
     * 
     * 1. 已创建了manager进程
     * 2. 已创建了worker子进程
     * 3. 已监听所有TCP/UDP端口
     * 4. 已监听了定时器
     * 
     * 之后，Reactor开始接收事件，客户端可以connect到Server，onStart回调中，
     * 仅允许echo、打印Log、修改进程名称。不得执行其他操作。onWorkerStart和onStart
     * 回调是在不同进程中并行执行的，不存在先后顺序。
     * 
     * 在onStart中创建的全局资源对象不能在worker进程中被使用，因为发生onStart调用时，
     * worker进程已经创建好了。新创建的对象在主进程内，worker进程无法访问到此内存区域。
     * 因此全局对象创建的代码需要放置在swoole_server_start之前。
     * 
     * @param \Swoole\Http\Server $server swoole_server对象
     */
    public function onStart(\Swoole\Http\Server $server);
    
    /**
     * 当管理进程启动时调用
     * 
     * 注意manager进程中不能添加定时器, manager进程中可以调用task功能
     * 
     * @param \Swoole\Http\Server $server swoole_server对象
     */
    public function onManagerStart(\Swoole\Http\Server $server);

    /**
     * 此事件在worker进程/task进程启动时发生。这里创建的对象可以在进程生命周期内使用
     * 
     * 通过$worker_id参数的值来，判断worker是普通worker还是task_worker。
     * $worker_id>= $serv->setting['worker_num'] 时表示这个进程是task_worker。
     * 
     * 如果想使用swoole_server_reload实现代码重载入，必须在workerStart中require你的业务文件，
     * 而不是在文件头部。在onWorkerStart调用之前已包含的文件，不会重新载入代码。
     * 
     * 可以将公用的，不易变的php文件放置到onWorkerStart之前。这样虽然不能重载入代码，
     * 但所有worker是共享的，不需要额外的内存来保存这些数据。onWorkerStart之后的代码,
     * 每个worker都需要在内存中保存一份
     * 
     * @param \Swoole\Http\Server $server swoole_server对象
     * @param int $workerId $worker_id是一个从0-$worker_num之间的数字，表示这个worker进程的ID,$worker_id和进程PID没有任何关系
     */
    public function onWorkerStart(\Swoole\Http\Server $server, int $workerId);
    
    /**
     * 有新的连接进入时，在worker进程中回调。函数原型：
     * 
     * @param \Swoole\Http\Server $server
     * @param int $fd
     * @param int $fromId
     */
    public function onConnect(\Swoole\Http\Server $server, int $fd, int $fromId);
    
    public function onReceive(\Swoole\Http\Server $server, int $fd, int $fromId, string $data);
    
    public function onTimer(\Swoole\Http\Server $server, int $interval);
    
    public function onTask(\Swoole\Http\Server $server, int $taskId, int $srcWorkerId, string $data);
    
    public function onPipeMessage(\Swoole\Http\Server $server, int $fromWorkerId, string $message);
    
    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response);
    
    public function onPacket(\Swoole\Http\Server $server, string $data, array $client_info);
    
    /**
     * 此事件在Server结束时发生,此事件之前已经完成如下操作：
     * 
     * 1. 已关闭所有线程
     * 2. 已关闭所有worker进程
     * 3. 已close所有TCP/UDP监听端口
     * 4. 已关闭主Rector
     * 
     * 强制kill进程不会回调onShutdown，如kill -9,需要使用kill -15来发送SIGTREM信号
     * 到主进程才能按照正常的流程终止
     * 
     * @param \Swoole\Http\Server $server
     */
    public function onShutdown(\Swoole\Http\Server $server);

    public function onWorkerStop(\Swoole\Http\Server $server, int $workerId);

    public function onClose(\Swoole\Http\Server $server, int $fd, int $fromId);

    public function onFinish(\Swoole\Http\Server $server, int $taskId, string $data);

    public function onWorkerError(\Swoole\Http\Server $server, int $workerId, int $workerPid, int $exitCode);

    public function onManagerStop(\Swoole\Http\Server $server);
}