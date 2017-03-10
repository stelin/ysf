<?php
namespace ysf\log;

use ysf\Ysf;
use ysf\exception\InvalidConfigException;

class FileTarget extends Target
{
    public $logFile;
    public $fileMode;
    public $dirMode = 0775;

    /**
     * Initializes the route.
     * This method is invoked after the route is created by the route manager.
     */
    public function init()
    {
        parent::init();
        if ($this->logFile === null) {
            $this->logFile = Ysf::$app->getRuntimePath() . '/logs/app.log';
        } else {
            $this->logFile = Ysf::getAlias($this->logFile);
        }
        $logPath = dirname($this->logFile);
        if (!is_dir($logPath)) {
            $this->createDirectory($logPath, $this->dirMode, true);
        }
    }
    
    public static function createDirectory($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        // recurse if parent dir does not exist and we are not at the root of the file system.
        if ($recursive && !is_dir($parentDir) && $parentDir !== $path) {
            static::createDirectory($parentDir, $mode, true);
        }
        try {
            if (!mkdir($path, $mode)) {
                return false;
            }
        } catch (\Exception $e) {
            if (!is_dir($path)) {// https://github.com/yiisoft/yii2/issues/9288
                throw new \Exception("Failed to create directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        try {
            return chmod($path, $mode);
        } catch (\Exception $e) {
            throw new \Exception("Failed to change permissions for directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Writes log messages to a file.
     * @throws InvalidConfigException if unable to open the log file for writing
     */
    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        
        if (($fp = @fopen($this->logFile, 'a')) === false) {
            throw new InvalidConfigException("Unable to append to log file: {$this->logFile}");
        }
        @flock($fp, LOCK_EX);
        @fwrite($fp, $text);
        @flock($fp, LOCK_UN);
        @fclose($fp);
        if ($this->fileMode !== null) {
            @chmod($this->logFile, $this->fileMode);
        }
    }
}
