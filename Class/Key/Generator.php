<?php
/**
 * 
 * @desc Генератор уникальных ключей
 * @author Yury Shveodv, Ilya Kolesnikov
 * @package IcEngine
 * 
 */
class Key_Generator
{
	
	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected static $_config = array (
		// Провайдер
		'provider'		=> null,
		// Минимальное значение
		'min_value'		=> 1
	);
	
	/**
	 * @desc Количество сгенерированных с начала выполнения скрипта
	 * @var integer
	 */
	protected static $_generatedCount = 0;
	
	/**
	 * @desc Провайдер для хранения текущего значения
	 * @var Data_Provider_Abstract
	 */
	protected static $_provider;
	
	/**
	 * @desc Вовзращает конфиги
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (self::$_config))
		{
			self::$_config = Config_Manager::get (__CLASS__, self::$_config);
		}
		return self::$_config;
	}
	
	/**
	 * @desc Генерирует новый ключ
	 * @param string|Model $type 
	 * @return integer
	 */
	public static function get ($type = 'def')
	{
		if (is_object ($type))
		{
			$type = $type->modelName ();
		}
		
		self::$_generatedCount++;
		$provider = self::provider ();
		$val = $provider->increment ($type);
		if ($val < self::config ()->min_value)
		{
			$val = self::load ($type, self::config ()->min_value);
			
			if (!$provider->lock ($type, 1, 5, 100))
			{
				throw new Exception ('Failed to lock key value');
			}
			
			$provider->set ($type, $val);
			
			$provider->unlock ($type);
			
			$val = $provider->increment ($type);
		}
		
		static $saved = 0;
		$ten = self::$_generatedCount / 10;
		$unit = $val % 10;
		if ($ten > $saved || $unit = 10)
		{
			$saved = $ten;
			self::save ($type, $val);
		}
		
		return $val;
	}
	
	/**
	 * @desc Возвращает название файла с бэкапом ключей.
	 * @return string
	 */
	public static function lastFile ($type)
	{
		$dir = IcEngine::root () . 'Ice/Var/Key/Generator/' .
			urlencode (Helper_Site_Location::getLocation ());
		if (!is_dir ($dir))
		{
			mkdir ($dir, 0666);
			chmod ($dir, 0666);
		}
		return $dir . '/' . urlencode ($type) . '.txt';
	}
	
	/**
	 *
	 * @param type $type
	 * @param type $min 
	 */
	public static function load ($type, $min)
	{
		$file = self::lastFile ($type);
		
		$vals = file_exists ($file)
			? json_decode (file_get_contents ($file), true)
			: array ();
		
		return isset ($vals [$type]) 
			? max ($vals [$type] + 11, $min)
			: $min;
	}
	
	/**
	 * @desc Провайдер 
	 * @return Data_Provider_Abstract
	 */
	public static function provider ()
	{
		if (!self::$_provider)
		{
			Loader::load ('Data_Provider_Manager');
			self::$_provider = Data_Provider_Manager::get (
				self::config ()->provider
			);
		}
		return self::$_provider;
	}
	
	/**
	 * @desc Дублирование значения в файл
	 */
	public static function save ($type, $value)
	{
		$file = self::lastFile ($type);
		
		$vals = file_exists ($file)
			? (int) file_get_contents ($file)
			: array ();
		
		$vals [$type] = $value;
		
		file_put_contents ($file, $value);
	}
	
}