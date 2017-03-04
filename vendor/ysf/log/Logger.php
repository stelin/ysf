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
        $requestBeginTime = ApplicationContext::getContext(ApplicationContext::CONTEXT_BEGIN_TIME);
        // php耗时单位ms毫秒
        $timeUsed = sprintf("%.0f", (microtime(true)-$requestBeginTime)*1000);
        // php运行内存大小单位M
        $memUsed = sprintf("%.0f", memory_get_peak_usage()/(1024*1024));
    
        $profileInfo = '';
        $countingInfo = '';
        $pushlogs = [];
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
        $this->log($message, self::LEVEL_NOTICE, $category);
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
