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
    public $defaultRoute = "/index/index";
    public $components;
    
    public $tcp = [];
    public $http = [];
    public $params = [];
    public $tcpEnable = true;
    public $processName = "php-ysf";
    
    public $controllerNamespace = 'app\\controllers';
    
    
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
//         $request = new \Swoole\Http\Request();
//         $request->server['path_info'] = '/service';
//         //         $request->server['path_info'] = '/post/1232';
//         $request->server['request_method'] = 'GET';
        
        
//         list($route, $params) = $this->urlManager->parseRequest($request);
//         /* @var $controller Controller */
//         list($controller, $actionId) = $this->createController($route);
        
//         $controller->setRequest($request);
//         $controller->setResponse($response);
//         $response = $controller->run($actionId, $params);
//         exit();

        
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
            $result = preg_match("/\s*([a-z0-9\-\._\-]+)\s*=\s*(([0-9]+) | \"*\'*([a-z0-9\-\._\-\/]+)\"*\'*)/", $line, $confs);
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
    
    public function createController($route)
    {
        list($controllerId, $actionId) = $this->getPathRoute($route);
        
        $controller = ObjectPool::getInstance()->getObject($controllerId);
        if($controller == null){
            $controller = $this->createControllerById($controllerId);
        }
        
        if ($controller === null && $route !== '') {
            $controller = $this->createControllerByID($controllerId . '/' . $actionId);
            $route = '';
        }
        
        if($controller === null){
            // exceptions
        }
        
        return [$controller, $actionId];
    }
    
       
    public function getPathRoute($route)
    {
        if ($route === '') {
            $route = $this->defaultRoute;
        }
        
        $route = trim($route, '/');
        if (strpos($route, '//') !== false) {
            return false;
        }
        
        if (strpos($route, '/') !== false) {
            list ($id, $route) = explode('/', $route, 2);
        } else {
            $id = $route;
            $route = '';
        }
        
        if (($pos = strrpos($route, '/')) !== false) {
            $id .= '/' . substr($route, 0, $pos);
            $route = substr($route, $pos + 1);
        }
        
        return [$id, $route];
    }
    
    /**
     * Creates a controller based on the given controller ID.
     *
     * The controller ID is relative to this module. The controller class
     * should be namespaced under [[controllerNamespace]].
     *
     * Note that this method does not check [[modules]] or [[controllerMap]].
     *
     * @param string $id the controller ID
     * @return Controller the newly created controller instance, or null if the controller ID is invalid.
     * @throws InvalidConfigException if the controller class and its file name do not match.
     * This exception is only thrown when in debug mode.
     */
    public function createControllerById($id)
    {
        $pos = strrpos($id, '/');
        if ($pos === false) {
            $prefix = '';
            $className = $id;
        } else {
            $prefix = substr($id, 0, $pos + 1);
            $className = substr($id, $pos + 1);
        }

    
        // 匹配正则修改兼容controller LoginUser/testOne loginUser/testOne login-user/testOne
        if (!preg_match('%^[a-zA-Z][a-zA-Z0-9\\-_]*$%', $className)) {
            return null;
        }
        if ($prefix !== '' && !preg_match('%^[a-z0-9_/]+$%i', $prefix)) {
            return null;
        }
    
        // namespace和prefix保持一致，搜字母都大写或都小写，namespace app\controllers\SecurityKey; prefix=SecurityKey
        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $className))) . 'Controller';
        $className = ltrim($this->controllerNamespace . '\\' . str_replace('/', '\\', $prefix)  . $className, '\\');
        if (strpos($className, '-') !== false || !class_exists($className)) {
            return null;
        }
    
        if (is_subclass_of($className, 'ysf\base\Controller')) {
            $controller = new $className($id);
            return get_class($controller) === $className ? $controller : null;
        }else{
            return null;
        }
    }
    
    public abstract function start();
    public abstract function parseCommand($args);
    public abstract function parseStart();
    public abstract function parseStop();
    public abstract function parseReload();
    public abstract function parseRestart();
    
}