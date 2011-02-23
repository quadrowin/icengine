<?php

/**
 * 
 * @desc Менеджер провайдеров данных.
 * По переданному названию создает и возвращает соответсвующего провайдера.
 * @author Юрий
 * @package IcEngine
 *
 */

class Data_Provider_Manager
{
	
	/**
	 * Загруженные провайдеры.
	 * @var array <Data_Provider_Abstract>
	 */
	protected static $_providers = array ();
	
	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected static $_config = array (
		
	);
	
	/**
	 * @desc Загружает и возвращает конфиг
	 * @return Objective
	 */
	protected function _config ()
	{
		if (is_array (self::$_config))
		{
			self::$_config = Config_Manager::load (__CLASS__, self::$_config);
		}
		
		return self::$_config;
	}
	
	/**
	 * Получение данных провайдера.
	 * @param string $name
	 * @return Data_Provider_Abstract
	 */
	public static function get ($name)
	{	
		if (!isset (self::$_providers [$name]))
		{
			$config = self::_config ()->$name;
			
			foreach ($config as $conf)
			{
				$class = 'Data_Provider_' . $conf->provider;
				
				Loader::load ($class);
				
				self::$_providers [$name] = new $class ($conf->params);
				
				if (self::$_providers [$name]->available ())
				{
					break;
				}
			}
		}
		
		return self::$_providers [$name];
	}
	
}