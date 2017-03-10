<?php
namespace ysf\log;

use ysf\base\Object;
use ysf\exception\InvalidConfigException;

abstract class Target extends Object
{
    public $enabled = true;
    public $categories = [];
    public $except = [];
    public $prefix;
    public $exportInterval = 1;
    public $messages = [];

    private $_levels = 0;

    abstract public function export();

    public function collect($messages, $final)
    {
        $this->messages = array_merge($this->messages, static::filterMessages($messages, $this->getLevels(), $this->categories, $this->except));
        $count = count($this->messages);
        
        if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {
            $oldExportInterval = $this->exportInterval;
            $this->exportInterval = 0;
            $this->export();
            $this->exportInterval = $oldExportInterval;

            $this->messages = [];
        }
    }
    
    public static function filterMessages($messages, $levels = 0, $categories = [], $except = [])
    {
        foreach ($messages as $i => $message) {
            if ($levels && !($levels & $message[1])) {
                unset($messages[$i]);
                continue;
            }
        }
        return $messages;
    }
    
    public function getLevels()
    {
        return $this->_levels;
    }


    /**
     * Sets the message levels that this target is interested in.
     *
     * The parameter can be either an array of interested level names or an integer representing
     * the bitmap of the interested level values. Valid level names include: 'error',
     * 'warning', 'info', 'trace' and 'profile'; valid level values include:
     * [[Logger::LEVEL_ERROR]], [[Logger::LEVEL_WARNING]], [[Logger::LEVEL_INFO]],
     * [[Logger::LEVEL_TRACE]] and [[Logger::LEVEL_PROFILE]].
     *
     * For example,
     *
     * ```php
     * ['error', 'warning']
     * // which is equivalent to:
     * Logger::LEVEL_ERROR | Logger::LEVEL_WARNING
     * ```
     *
     * @param array|integer $levels message levels that this target is interested in.
     * @throws InvalidConfigException if an unknown level name is given
     */
    public function setLevels($levels)
    {
        static $levelMap = [
            'error' => Logger::LEVEL_ERROR,
            'warning' => Logger::LEVEL_WARNING,
            'trace' => Logger::LEVEL_TRACE,
            'notice' => Logger::LEVEL_NOTICE,
        ];
        if (is_array($levels)) {
            $this->_levels = 0;
            foreach ($levels as $level) {
                if (isset($levelMap[$level])) {
                    $this->_levels |= $levelMap[$level];
                } else {
                    throw new InvalidConfigException("Unrecognized level: $level");
                }
            }
        } else {
            $this->_levels = $levels;
        }
    }

    /**
     * Formats a log message for display as a string.
     * @param array $message the log message to be formatted.
     * The message structure follows that in [[Logger::messages]].
     * @return string the formatted message
     */
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

        return date('Y/m/d H:i:s', $timestamp) . " [$level] [$category] $text";
    }
}
