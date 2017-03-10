<?php
namespace ysf\web;

use ysf\Ysf;

trait CommandLine
{
    private $pidFile;
    private $tcp = [];
    private $http = [];
    private $processName;
    private $command = "";
    private $settings = [];
    private $tcpEnable = 0;
    private $masterPid = 0;
    private $managerPid = 0;
    private $startFile = "";
    
    public function run(){
        
        $this->initConfig();
        
        global $argv;
        $this->parseCommand($argv);
    }
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
            $masterIslive = $this->masterPid && @posix_kill($this->masterPid, 0);
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
    
    public function initConfig()
    {
        $inis = parse_ini_file($this->settingPath, true);
        if(!isset($inis['tcp'])){
            throw new \Exception('tcp is not config');
        }
        if(!isset($inis['http'])){
            throw new \Exception('http is not config');
        }
        if(!isset($inis['setting'])){
            throw new \Exception('setting is not config');
        }
        
        if(isset($inis['ysf']['tcp.enable'])){
            $this->tcpEnable = $inis['ysf']['tcp.enable'];
        }
        if(isset($inis['ysf']['pid.file'])){
            $this->pidFile = $inis['ysf']['pid.file'];
        }
        if(isset($inis['ysf']['process.name'])){
            $this->processName = $inis['ysf']['process.name'];
        }
        
        $this->tcp = $inis['tcp'];
        $this->http = $inis['http'];
        $this->settings = $inis['setting'];
    }
}