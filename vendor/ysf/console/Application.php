<?php
namespace ysf\console;

use ysf\helpers\ArrayHelper;

class Application extends \ysf\base\Application
{
    public function __construct($config = [])
    {
        $config = $this->loadConfig($config);
        parent::__construct($config);
    }
    
    protected function loadConfig($config)
    {
        if (!empty($_SERVER['argv'])) {
            $option = '--' . self::OPTION_APPCONFIG . '=';
            foreach ($_SERVER['argv'] as $param) {
                if (strpos($param, $option) !== false) {
                    $path = substr($param, strlen($option));
                    if (!empty($path) && is_file($file = Yii::getAlias($path))) {
                        return require($file);
                    } else {
                        exit("The configuration file does not exist: $path\n");
                    }
                }
            }
        }
    
        return $config;
    }
    
    public function run(){
        
    }
    
    public function coreComponents()
    {
        return ArrayHelper::merge(parent::coreComponents(), [
            'request' => 'ysf\console\request'
        ]);
    }
}