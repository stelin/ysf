<?php
/**
 * @link https://github.com/stelin/ysf
 * @copyright Copyright 2016-2017 stelin develper.
 * @license https://github.com/stelin/ysf/license/
 */
namespace ysf\base;

use ysf\web\UrlManager;
use ysf\Ysf;
use ysf\exception\InvalidParamException;
use ysf\exception\InvalidConfigException;
use ysf\di\ServiceLocator;

/**
 * base application
 *
 * @property \ysf\web\UrlManager $urlManager The URL manager for this application. This property is read-only.
 * 
 * @author stelin <phpcrazy@126.com>
 * @since 0.1
 */
abstract class Application extends ServiceLocator{
    public $id;
    public $name;
    public $basePath;
    public $runtimePath;
    public $components;
    
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
        $this->preInit($config);
        $this->setComponents($config['components']);
        
        parent::__construct($config);
    }
    
    /**
     * 运行服务
     */
    public function run()
    {
        $request = new \Swoole\Http\Request();
        $request->server['path_info'] = '/InterfaceMap';
        $request->server['path_info'] = '/post/1232';
        $request->server['request_method'] = 'GET';
        
        var_dump($this->urlManager->parseRequest($request));
        exit();
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
    
    public function coreComponents()
    {
        return [
            'urlManager' => ['class' => 'ysf\web\UrlManager'],
        ];
    }
    
    /**
     * Sets the root directory of the module.
     * This method can only be invoked at the beginning of the constructor.
     * @param string $path the root directory of the module. This can be either a directory name or a path alias.
     * @throws InvalidParamException if the directory does not exist.
     */
    public function setBasePath($path)
    {
        $path = Ysf::getAlias($path);
        $p = strncmp($path, 'phar://', 7) === 0 ? $path : realpath($path);
        if ($p !== false && is_dir($p)) {
            $this->basePath = $p;
        } else {
            throw new InvalidParamException("The directory does not exist: $path");
        }
    }
    
    /**
     * Sets the directory that stores runtime files.
     * @param string $path the directory that stores runtime files.
     */
    public function setRuntimePath($path)
    {
        $this->runtimePath = Ysf::getAlias($path);
        Ysf::setAlias('@runtime', $this->runtimePath);
    }
    
    /**
     * Returns the directory that stores runtime files.
     * @return string the directory that stores runtime files.
     * Defaults to the "runtime" subdirectory under [[basePath]].
     */
    public function getRuntimePath()
    {
        if ($this->runtimePath === null) {
            $this->setRuntimePath($this->getBasePath() . DIRECTORY_SEPARATOR . 'runtime');
        }
    
        return $this->runtimePath;
    }
    
    /**
     * Returns the root directory of the module.
     * It defaults to the directory containing the module class file.
     * @return string the root directory of the module.
     */
    public function getBasePath()
    {
        if ($this->basePath === null) {
            $class = new \ReflectionClass($this);
            $this->basePath = dirname($class->getFileName());
        }
    
        return $this->basePath;
    }
    
    /**
     * Sets the time zone used by this application.
     * This is a simple wrapper of PHP function date_default_timezone_set().
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * @param string $value the time zone used by this application.
     * @see http://php.net/manual/en/function.date-default-timezone-set.php
     */
    public function setTimeZone($value)
    {
        date_default_timezone_set($value);
    }
    
    public function preInit(&$config)
    {
        if (!isset($config['id'])) {
            throw new InvalidConfigException('The "id" configuration for the Application is required.');
        }
        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        } else {
            throw new InvalidConfigException('The "basePath" configuration for the Application is required.');
        }
    
        if (isset($config['runtimePath'])) {
            $this->setRuntimePath($config['runtimePath']);
            unset($config['runtimePath']);
        } else {
            // set "@runtime"
            $this->getRuntimePath();
        }
    
        if (isset($config['timeZone'])) {
            $this->setTimeZone($config['timeZone']);
            unset($config['timeZone']);
        } elseif (!ini_get('date.timezone')) {
            $this->setTimeZone('UTC');
        }
    
        // merge core components with custom components
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $config['components'][$id]['class'] = $component['class'];
            }
        }
    }
    
    public abstract function start();
    public abstract function parseCommand($args);
    public abstract function parseStart();
    public abstract function parseStop();
    public abstract function parseReload();
    public abstract function parseRestart();
    
}