<?php
namespace ysf\log;

use ysf\base\Object;

class Logger extends Object
{
    const LEVEL_ERROR = 0x01;
    const LEVEL_WARNING = 0x02;
    const LEVEL_HTTP = 0x04;
    const LEVEL_TRACE = 0x08;
    const LEVEL_MYSQL = 0x40;
    const LEVEL_MONGO = 0x50;
    const LEVEL_REDIS = 0x60;
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
        $messages = $this->messages;
        $this->messages = [];
        if ($this->dispatcher instanceof Dispatcher) {
            $this->dispatcher->dispatch($messages, $final);
        }
    }
    
    public static function getLevelName($level)
    {
        static $levels = [
            self::LEVEL_ERROR => 'error',
            self::LEVEL_WARNING => 'warning',
            self::LEVEL_HTTP => 'http',
            self::LEVEL_TRACE => 'trace',
            self::LEVEL_MONGO => 'mongodb',
            self::LEVEL_MYSQL => 'mysql',
            self::LEVEL_REDIS => 'redis',
            self::LEVEL_NOTICE => 'notice',
        ];

        return isset($levels[$level]) ? $levels[$level] : 'unknown';
    }
}
