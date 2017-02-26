<?php
/**
 * @link https://github.com/stelin/ysf
 * @copyright Copyright 2016-2017 stelin develper.
 * @license https://github.com/stelin/ysf/license/
 */
namespace ysf\base;

/**
 * base application
 *
 * @author stelin <phpcrazy@126.com>
 * @since 0.1
 */
abstract class Application extends Component{
    public $id = "";
    public $tcp = [];
    public $http = [];
    public $params = [];
    public $tcpEnable = true;
    public $processName = "php-ysf";
    
    
    protected $setings = [];
    private $version = "0.1";
    
    
    /**
     * @var \Swoole\Http\Server
     */
    public $server = null;
    
    public function __construct($config)
    {
        $this->initServer($config);
        
        parent::__construct($config);
    }
    
    public function initServer(&$config)
    {
        $serverConfigs = [];
        if(isset($config['configs'])){
            $serverConfigs = $config['configs'];
            unset($config['configs']);
        }
        if(isset($serverConfigs['http'])){
            $this->http = $serverConfigs['http'];
        }
        if(isset($serverConfigs['tcp'])){
            $this->tcp = $serverConfigs['tcp'];
        }
    }
    
    /**
     * 运行服务
     */
    public function run()
    {
        $this->setings = $this->readServerConf();
        
        global $argv;
        $this->parseCommand($argv);
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    
    public function readServerConf()
    {
        $path = "/home/worker/data/www/ysf/bin/ysf.conf";
        
        $serverConfs = [];
        $fp = @fopen($path, "r");
        while (! feof($fp)){
            $line = fgets($fp);
            if(strpos($line, "#") === 0){
                continue;
            }
            $result = preg_match("/\s*([a-z0-9\-\._\-]+)\s*=\s*(([0-9]+) | \"*\'*([a-z0-9\-\._\-]+)\"*\'*)/", $line, $confs);
            if($result && isset($confs[1]) && isset($confs[4])){
                $key = $confs[1];
                $value = $confs[4];
                $serverConfs[$key] = $value;
            }
        }
        
        return $serverConfs;
    }
    
    public abstract function start();
    public abstract function parseCommand($args);
    public abstract function parseStart();
    public abstract function parseStop();
    public abstract function parseReload();
    public abstract function parseRestart();
    
}