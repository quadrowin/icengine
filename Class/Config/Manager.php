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
	 * 
	 * @param Config_Container $container
	 * @return Config_Manager
	 */
	public static function appendContainer (Config_Container $container)
	{
		self::$_containers [$container->getType ()][$container->getName ()] = $container;
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
	 * 
	 * @param string $type
	 * @param string $name
	 */
	public static function load ($type, $name)
	{
		Loader::load ('Config_Container');
		$container = new Config_Container (
			$name,
			$type,
			self::PATH_TO_CONFIG
		);
		self::appendContainer ($container);
	}
}