<?php
namespace ysf\log;

use ysf\base\Object;
use ysf\Ysf;

class Dispatcher extends Object
{
    public $targets = [];

    /**
     * @var Logger the logger.
     */
    private $_logger;


    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        // ensure logger gets set before any other config option
        if (isset($config['logger'])) {
            $this->setLogger($config['logger']);
            unset($config['logger']);
            
            Ysf::setLogger($this->_logger);
        }
        $this->getLogger();
        
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        foreach ($this->targets as $name => $target) {
            if (!$target instanceof Target) {
                $this->targets[$name] = Ysf::createObject($target);
            }
        }
    }

    public function getLogger()
    {
        if ($this->_logger === null) {
            $this->setLogger(Ysf::getLogger());
        }
        return $this->_logger;
    }

    public function setLogger($value)
    {
        if (is_string($value) || is_array($value)) {
            $value = Ysf::createObject($value);
        }
        $this->_logger = $value;
        $this->_logger->dispatcher = $this;
    }

    /**
     * @return integer how many application call stacks should be logged together with each message.
     * This method returns the value of [[Logger::traceLevel]]. Defaults to 0.
     */
    public function getTraceLevel()
    {
        return $this->getLogger()->traceLevel;
    }

    /**
     * @param integer $value how many application call stacks should be logged together with each message.
     * This method will set the value of [[Logger::traceLevel]]. If the value is greater than 0,
     * at most that number of call stacks will be logged. Note that only application call stacks are counted.
     * Defaults to 0.
     */
    public function setTraceLevel($value)
    {
        $this->getLogger()->traceLevel = $value;
    }

    /**
     * @return integer how many messages should be logged before they are sent to targets.
     * This method returns the value of [[Logger::flushInterval]].
     */
    public function getFlushInterval()
    {
        return $this->getLogger()->flushInterval;
    }

    /**
     * @param integer $value how many messages should be logged before they are sent to targets.
     * This method will set the value of [[Logger::flushInterval]].
     * Defaults to 1000, meaning the [[Logger::flush()]] method will be invoked once every 1000 messages logged.
     * Set this property to be 0 if you don't want to flush messages until the application terminates.
     * This property mainly affects how much memory will be taken by the logged messages.
     * A smaller value means less memory, but will increase the execution time due to the overhead of [[Logger::flush()]].
     */
    public function setFlushInterval($value)
    {
        $this->getLogger()->flushInterval = $value;
    }

    /**
     * Dispatches the logged messages to [[targets]].
     * @param array $messages the logged messages
     * @param boolean $final whether this method is called at the end of the current application
     */
    public function dispatch($messages, $final)
    {
        $targetErrors = [];
        foreach ($this->targets as $target) {
            if ($target->enabled) {
                try {
                    $target->collect($messages, $final);
                } catch (\Exception $e) {
                    $target->enabled = false;
                    $targetErrors[] = [
                        'Unable to send log via ' . get_class($target) . ': ' . $e->getMessage(),
                        Logger::LEVEL_WARNING,
                        __METHOD__,
                        microtime(true),
                        [],
                    ];
                }
            }
        }

        if (!empty($targetErrors)) {
            $this->dispatch($targetErrors, true);
        }
    }
}
