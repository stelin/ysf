<?php
namespace ysf\log;

use ysf\base\Object;
use ysf\base\ApplicationContext;
use ysf\Ysf;

class Logger extends Object
{
    const LEVEL_ERROR = 0x01;
    const LEVEL_WARNING = 0x02;
    const LEVEL_TRACE = 0x08;
    const LEVEL_NOTICE = 0x80;
    
    public $messages = [];
    public $flushInterval = 1000;
    public $traceLevel = 0;
    public $dispatcher;
    
    public $pushlogs = [];
    public $profileStacks = [];
    public $profiles = [];
    public $countings = [];

    public function init()
    {
        parent::init();
    }

    public function log($message, $level, $category = 'application')
    {
        $logid = ApplicationContext::getContext(ApplicationContext::CONTEXT_LOGID);
        $message = "[logid:".$logid."] ". $message;
        
        $category = $this->getCategory($category);
        
        $time = microtime(true);
        $traces = [];
        if ($this->traceLevel > 0) {
            $count = 0;
            $ts = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_pop($ts); // remove the last trace since it would be the entry script, not very useful
            foreach ($ts as $trace) {
                if (isset($trace['file'], $trace['line']) && strpos($trace['file'], YII2_PATH) !== 0) {
                    unset($trace['object'], $trace['args']);
                    $traces[] = $trace;
                    if (++$count >= $this->traceLevel) {
                        break;
                    }
                }
            }
        }
        $this->messages[] = [$message, $level, $category, $time, $traces];
        if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval) {
            $this->flush();
        }
    }

    /**
     * Flushes log messages from memory to targets.
     * @param boolean $final whether this is a final call during a request.
     */
    public function flush($final = false)
    {
        // 所有日志后面追加一条notice日志
        $this->apendNoticeLog();
        
        $messages = $this->messages;
        $this->messages = [];
        if ($this->dispatcher instanceof Dispatcher) {
            $this->dispatcher->dispatch($messages, $final);
        }
    }
    
    /**
     * 追加一条notice日志
     */
    public function apendNoticeLog()
    {
        $logid = ApplicationContext::getContext(ApplicationContext::CONTEXT_LOGID);
        $requestBeginTime = ApplicationContext::getContext(ApplicationContext::CONTEXT_BEGIN_TIME);
        // php耗时单位ms毫秒
        $timeUsed = sprintf("%.2f", (microtime(true)-$requestBeginTime)*1000);
        // php运行内存大小单位M
        $memUsed = sprintf("%.0f", memory_get_peak_usage()/(1024*1024));
    
        $profileInfo = $this->getProfilesInfos();
        $countingInfo = $this->getCountingInfo();
        $pushlogs = isset($this->pushlogs[$logid]) ? $this->pushlogs[$logid]: [];
        $uri = ApplicationContext::getContext(ApplicationContext::CONTEXT_URI);
        
    
        $messageAry = array(
            "[$timeUsed(ms)]",
            "[$memUsed(MB)]",
            "[{$uri}]",
            "[".implode(" ", $pushlogs)."]",
            "profile[".$profileInfo."]",
            "counting[".$countingInfo."]"
                );
        $category = $this->getCategory();
        $message = implode(" ", $messageAry);
    
//         $this->profiles = [];
//         $this->countings = [];
//         $this->pushlogs = [];
//         $this->profileStacks = [];
        
        unset($this->pushlogs[$logid]);
        $this->log($message, self::LEVEL_NOTICE, $category);
    }
    
    /**
     * pushlog日志
     *
     * @param string $key
     * @param mixed $value
     */
    public function pushLog($key, $val)
    {
        $logid = ApplicationContext::getContext(ApplicationContext::CONTEXT_LOGID);
        
        if (!(is_string($key) || is_numeric($key))) {
            return;
        }
        $key = urlencode($key);
        if (is_array($val)) {
            $this->pushlogs[$logid][] = "$key=" . json_encode($val);
        } elseif (is_bool($val)) {
            $this->pushlogs[$logid][] = "$key=" . var_export($val, true);
        } elseif (is_string($val) || is_numeric($val)) {
            $this->pushlogs[$logid][] = "$key=" . urlencode($val);
        } elseif (is_null($val)) {
            $this->pushlogs[$logid][] = "$key=";
        }
    }
    
    /**
     * 标记开始
     *
     * @param string $name
     */
    public function profileStart($name)
    {
        $logid = ApplicationContext::getContext(ApplicationContext::CONTEXT_LOGID);
        if(is_string($name) == false || empty($name)){
            return ;
        }
        $this->profileStacks[$logid][$name]['start'] = microtime(true);
    }
    
    /**
     * 标记开始
     *
     * @param string $name
     */
    public function profileEnd($name)
    {
        $logid = ApplicationContext::getContext(ApplicationContext::CONTEXT_LOGID);
        if (is_string($name) == false || empty($name)) {
            return;
        }
    
        if (! isset($this->profiles[$logid][$name])) {
            $this->profiles[$logid][$name] = [
                'cost' => 0,
                'total' => 0
            ];
        }
    
        $this->profiles[$logid][$name]['cost'] += microtime(true) - $this->profileStacks[$logid][$name]['start'];
        $this->profiles[$logid][$name]['total'] = $this->profiles[$logid][$name]['total'] + 1;
        
    }
    
    /**
     * 组装profiles
     */
    public function getProfilesInfos()
    {
        $logid = ApplicationContext::getContext(ApplicationContext::CONTEXT_LOGID);
        
        $profiles = [];
        if(isset($this->profiles[$logid])){
            $profiles = $this->profiles[$logid];
        }
        $profileAry = [];
        foreach ($profiles as $key => $profile){
            if(!isset($profile['cost']) || !isset($profile['total'])){
                continue;
            }
            $cost = sprintf("%.2f", $profile['cost'] * 1000);
            $profileAry[] = "$key=" .  $cost. '(ms)/' . $profile['total'];
        }
    
        return implode(",", $profileAry);
    }
    
    
    public function counting($name, $hit, $total = null)
    {
        $logid = ApplicationContext::getContext(ApplicationContext::CONTEXT_LOGID);
        
        if (!is_string($name) || empty($name)) {
            return;
        }
        if (!isset($this->countings[$logid][$name])) {
            $this->countings[$logid][$name] = ['hit' => 0, 'total' => 0];
        }
        $this->countings[$logid][$name]['hit'] += intval($hit);
        if ($total !== null) {
            $this->countings[$logid][$name]['total'] += intval($total);
        }
    }
    
    /**
     * 组装字符串
     */
    public function getCountingInfo()
    {
        $logid = ApplicationContext::getContext(ApplicationContext::CONTEXT_LOGID);
        
        if(!isset($this->countings[$logid]) || empty($this->countings[$logid])){
            return "";
        }
    
        $countAry = [];
        foreach ($this->countings[$logid] as $name => $counter){
            if(isset($counter['hit'], $counter['total']) && $counter['total'] != 0){
                $countAry[] = "$name=".$counter['hit']."/".$counter['total'];
            }elseif(isset($counter['hit'])){
                $countAry[] = "$name=".$counter['hit'];
            }
        }
        return implode(',', $countAry);
    }
    
    
    public static function getLevelName($level)
    {
        static $levels = [
            self::LEVEL_ERROR => 'error',
            self::LEVEL_WARNING => 'warning',
            self::LEVEL_TRACE => 'trace',
            self::LEVEL_NOTICE => 'notice',
        ];

        return isset($levels[$level]) ? $levels[$level] : 'unknown';
    }
    
    public static function getCategory($category = 'application')
    {
        $category = SYSTEM_NAME;
        return $category;
    }
}
