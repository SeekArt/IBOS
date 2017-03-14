<?php

class ErrorLogger
{

    private static $error = array();

    /**
     *
     * @param string $content
     * @param string $category
     */
    public static function log($content, $category = '')
    {
        if (!empty($category)) {
            static::$error[$category][] = $content;
        } else {
            static::$error[] = $content;
        }
    }

    /**
     * 
     * @return array
     */
    public static function getError()
    {
        $return = static::$error;
        static::truncate();
        return $return;
    }

    /**
     *
     * @return boolean
     */
    public static function hasError()
    {
        return !empty(static::$error);
    }

    /**
     * @return void 
     */
    protected static function truncate()
    {
        static::$error = array();
    }

}
