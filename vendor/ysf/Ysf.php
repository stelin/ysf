<?php
/**
 * @link https://github.com/stelin/ysf
 * @copyright Copyright 2016-2017 stelin develper.
 * @license https://github.com/stelin/ysf/license/
 */
namespace ysf;

class Ysf
{
    /**
     * @var \ysf\base\Application
     */
    private static $app;
    
    /**
     * Configures an object with the initial property values.
     * @param object $object the object to be configured
     * @param array $properties the property initial values given in terms of name-value pairs.
     * @return object the object itself
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }
    
        return $object;
    }
    /**
     * @return the $app
     */
    public static function app()
    {
        return self::$app;
    }

    /**
     * @param \ysf\base\Application $app
     */
    public static function setApp($app)
    {
        self::$app = $app;
    }

    
    
}

