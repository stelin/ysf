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
        echo("ysf ".$this->startFile." $command\n");
        
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
            echo("ysf ".$this->startFile." already running\n");
            exit;
        }
        
        if($masterIslive == false && $this->command != "start"){
            echo("ysf ".$this->startFile." not run\n");
            exit;
        }
    }
    
    public function parseHelp()
    {
        $help = "Usage: ysf {help|start|stop|reload|restart}\n";
        $help .= "   ysf [help] show help information\n";
        $help .= "   ysf [start] start http server\n";
        $help .= "   ysf [stop] stop http server\n";
        $help .= "   ysf [reload] reload worker process files\n";
        $help .= "   ysf [restart] restart server and reload all files\n";
        exit($help);
    }
    
    public function parseStart()
    {
        $this->start();
    }
    public function parseStop()
    {
        @unlink($this->pidFile);
        echo("ysf ".$this->startFile." is stoping ...\n");
        
        $this->masterPid && posix_kill($this->masterPid, SIGTERM);
        
        $timeout = 5;
        $startTime = time();
        
        while (1) {
            $masterIslive = $this->masterPid && posix_kill($this->masterPid, SIGTERM);
            if ($masterIslive) {
                if (time() - $startTime >= $timeout) {
                    echo("ysf ".$this->startFile." stop fail\n");
                    exit;
                }
                usleep(10000);
                continue;
            }
            echo("ysf ".$this->startFile." stop success\n");
            break;
        }
        exit(0);
    }
    public function parseReload()
    {
    }
    public function parseRestart()
    {
    
    }
}