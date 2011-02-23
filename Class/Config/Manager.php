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
	 * @desc Пустой конфиг
	 * @var Config_Array
	 */
	protected static $_emptyConfig;
	
	/**
	 * @desc Загружает конфиг из файла и возвращает класс конфига.
	 * @param string|array $config Название конфига или конфиг по умолчанию.
	 * @param string $type Тип конфига.
	 * @return Config_Array|Objective Заруженный конфиг.
	 */
	public static function _load ($type, $config = null)
	{
		$filename = 
			IcEngine::root () . self::PATH_TO_CONFIG .
			str_replace ('_', '/', $type) . 
			(is_string ($config) && $config ? '/' . $config : '') . 
			'.php';
			
		if (is_file ($filename))
		{
			$ext = ucfirst (strtolower (substr (strrchr ($filename, '.'), 1)));
			$class = 'Config_' . $ext;
			Loader::load ($class);
			
			$result = new $class ($filename);
		}
		else
		{
			$result = self::emptyConfig ();
		}
		
		return is_array ($config) ? $result->merge ($config) : $result;
	}
	
	/**
	 * @desc Пустой конфиг.
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
	 * @desc Загружает и возвращает конфиг.
	 * @param string $type Тип конфига.
	 * @param string $name [optional] Название.
	 * @return Objective
	 */
	public static function get ($type, $config = null)
	{
		Loader::load ('Resource_Manager');
		
		if ($type == 'Resource_Manager')
		{
			return self::_load ($type, $config);
		}
		
		$rname = $type . (is_string ($config) ? '/' . $config : '');
		$cfg = Resource_Manager::get ('Config', $rname);
		if (!$cfg)
		{
			$cfg = self::_load ($type, $config);
			Resource_Manager::set ('Config', $rname, $cfg);
		}
		return $cfg;
	}
	
}