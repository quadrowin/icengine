<?php

class Config_Manager
{
	/**
	 * 
	 * @var string
	 */
	const PATH_TO_CONFIG = 'config/';
	
	/**
	 * 
	 * @var array <Config_Container>
	 */
	protected static $_containers;
	
    /**
     * Пустой конфиг
     * @var Config_Array
     */
    protected static $_emptyConfig;
	
	/**
	 * 
	 * @param Config_Container $container
	 */
	public static function appendContainer (Config_Container $container)
	{
		self::$_containers [$container->getType ()][$container->getName ()] = $container;
	}
	
	/**
	 * Пустой конфиг
	 * @return Config_Array
	 */
	public static function emptyConfig ()
	{
	    if (!self::$_emptyConfig)
	    {
	        Loader::load ('Config_Array');
            self::$_emptyConfig = new Config_Array (array ());
	    }
	    return self::$_emptyConfig;
	}
	
	/**
	 * 
	 * @param string $type
	 * @param string $name
	 * @return boolean
	 */
	public static function exists ($type, $name)
	{
		return (bool) !empty (self::$_containers [$type][$name]);
	}
	
	/**
	 * 
	 * @param string $type
	 * @param string $name
	 * @return Config_Container
	 */
	public static function get ($type, $name)
	{
		if (!self::exists ($type, $name))
		{
			self::load ($type, $name);
		}
		return self::$_containers [$type][$name];
	}
	
	/**
	 * @desc Загразить конфик из файла 
	 * @param string $type
	 * @param string $name
	 * @return Config_Array
	 */
	public static function loadConfig ($type, $name = null)
	{
		Loader::load ('Config_Container');
		
		$container = new Config_Container (
			$name,
			$type,
			Ice_Implementator::path () . '../' . self::PATH_TO_CONFIG
		);
		self::appendContainer ($container);
		return $container->config ();
	}
}