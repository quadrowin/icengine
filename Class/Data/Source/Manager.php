<?php

/**
 * 
 * @desc Менеджер источников данных.
 * По переданному названию загружает конфиг из директории
 * "{$config}/Data/Source/" и создает соответсвующего провайдера.
 * @author Юрий
 *
 */

class Data_Source_Manager
{
	
	/**
	 * Загруженные источники.
	 * @var array <Data_Source_Abstract>
	 */
	protected static $_sources = array ();
	
	/**
	 * Получение данных провайдера.
	 * @param string $name
	 * @return Data_Source_Abstract
	 */
	public static function get ($name)
	{	
		if (!isset (self::$_sources [$name]))
		{
			$config = 
				Config_Manager::load ('Data_Source', $name);
				
			foreach ($config as $conf)
			{
				$source_class = 'Data_Source_' . $conf->source;
				$mapper_class = 'Data_Mapper_' . $conf->mapper;
				
				Loader::load ($source_class);
				Loader::load ($mapper_class);
				
				self::$_sources [$name] = new $source_class ();
				/**
				 * 
				 * @var Data_Mapper_Abstract $mapper
				 */
				$mapper = self::$_sources [$name]->getDataMapper ();
				
				if (!is_a ($mapper, $mapper_class))
				{
					// Мэппер источника отличается от указанного в конфигах
					$mapper = new $mapper_class ($conf->mapper_params);
					self::$_sources [$name]->setDataMapper ($mapper);
				}
				
				foreach ($conf->mapper_options as $key => $value)
				{
					$mapper->setOption ($key, $value);
				}
				
				if (self::$_sources [$name]->available ())
				{
					break;
				}
			}
		}
		
		return self::$_sources [$name];
	}
	
}