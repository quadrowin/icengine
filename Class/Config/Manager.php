<?php
/**
 * 
 * @desc Мэнеджер конфигов.
 * @author Юрий
 * @package IcEngine
 *
 */
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
	 * @desc Загразить конфик из файла.
	 * @param string $type
	 * 		Тип конфига.
	 * @param string|array $config
	 * 		Название конфига или конфиг по умолчанию.
	 * @return Config_Array Загруженный конфиг.
	 */
	public static function load ($type, $config = null)
	{
		Loader::load ('Config_Container');
		
		$container = new Config_Container (
			is_string ($config) ? $config : '',
			$type,
			IcEngine::root () . self::PATH_TO_CONFIG
		);
		self::appendContainer ($container);
		
		return 
			is_array ($config) ? 
			$container->config ()->merge ($config) :
			$container->config ();
	}
}