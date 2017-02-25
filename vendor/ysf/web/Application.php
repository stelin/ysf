<?php
namespace ysf\web;

use ysf\Ysf;

abstract class Application extends \ysf\base\Application
{
    private $masterPid = 0;
    private $managerPid = 0;
    private $command = "";
    private $startFile = "";
    protected $pidFile = '/tmp/ysf.pid';
    
    
    /**
     * parse command
     *
     * @param array $args
     */
    public function parseCommand($args)
    {
        if (!isset($args[1])) {
            exit("Usage: ysf {start|stop|reload|restart}\n");
        }
    
        $command = trim($args[1]);
        $methodName = "parse".ucfirst($command);
        if(method_exists($this, $methodName) == false){
            exit("Usage: ysf invalid option: '".$command."'\n");
        }
        
        $this->command = $command;
        $this->startFile = $args[0];
        
        $this->checkServerStatus();
        $this->$methodName();
    }
    
    public function checkServerStatus()
    {
        $masterIslive = false;
        if (file_exists($this->pidFile)) {
            $pidFile = file_get_contents($this->pidFile);
            $pids = explode(',', $pidFile);
        
            $this->masterPid = $pids[0];
            $this->managerPid = $pids[1];
            $masterIslive = $this->masterPid && @posix_kill($masterPid, 0);
        }
        
        if($masterIslive && $this->command == 'start'){
            echo("ysf ".$this->startFile." is already running \n");
            exit;
        }
        
        if($masterIslive == false && $this->command != "start"){
            echo("ysf ".$this->startFile." is not running \n");
            exit;
        }
    }
    
    public function parseStart()
    {
        echo "ysf ".$this->startFile." start success \n";
        $this->start();
    }
    public function parseStop()
    {
        @unlink($this->pidFile);
        echo("ysf ".$this->startFile." is stoping ... \n");
        
        $this->masterPid && posix_kill($this->masterPid, SIGTERM);
        
        $timeout = 5;
        $startTime = time();
        
        while (1) {
            $masterIslive = $this->masterPid && posix_kill($this->masterPid, SIGTERM);
            if ($masterIslive) {
                if (time() - $startTime >= $timeout) {
                    echo("ysf ".$this->startFile." stop fail \n");
                    exit;
                }
                usleep(10000);
                continue;
            }
            echo("ysf ".$this->startFile." stop success \n");
            break;
        }
    }
    public function parseReload()
    {
        echo("ysf ".$this->startFile." is reloading \n");
        posix_kill($this->managerPid, SIGUSR1);
        echo("ysf ".$this->startFile." reload success \n");
    }
    public function parseRestart()
    {
        $this->parseStop();
        $this->parseStart();
    }
}