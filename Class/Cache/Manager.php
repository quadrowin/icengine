<?php

class Cache_Manager
{

    /**
     * Кэшеры.
     * @var array <Data_Provider_Abstract>
     */
    protected static $_cachers = array ();
    
    /**
     * 
     * @param string $class
     * @return Data_Provider_Abstract
     */
    public static function cacherFor ($class)
    {
        if (!isset (self::$_cachers [$class]))
        {
            self::$_cachers [$class] = new Data_Provider_Abstract ();
        }
        return self::$_cachers [$class]; 
    }
    
    /**
     * 
     * @param string $file
     * @return Cache_Options
     */
    public static function load ($file)
    {
        if (!file_exists ($file))
        {
            return null;
        }
        
        $options = new Cache_Options ();
        
        $conf = new Config_Php ($file);

        $options->applyConfig ($conf);
        
        return $options;
    }
    
}