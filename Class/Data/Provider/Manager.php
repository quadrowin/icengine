<?php

/**
 * 
 * @desc Менеджер провайдеров данных.
 * По переданному названию загружает конфиг из директории
 * "{$config}/Data/Provider/" и создает соответсвующего провайдера.
 * @author Юрий
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
	 * Получение данных провайдера.
	 * @param string $name
	 * @return Data_Provider_Abstract
	 */
	public static function get ($name)
	{	
		if (!isset (self::$_providers [$name]))
		{
			$config = 
				Config_Manager::load ('Data_Provider', $name)
				->asArray ();
				
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