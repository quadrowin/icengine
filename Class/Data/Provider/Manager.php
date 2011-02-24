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
			self::$_config = Config_Manager::get (__CLASS__, self::$_config);
		}
		return self::$_config;
	}
	
	/**
	 * @desc Возвращает провайдера.
	 * @param string $name Название провайдера в конфиге.
	 * @return Data_Provider_Abstract
	 */
	public static function get ($name)
	{	
		if (!isset (self::$_providers [$name]))
		{
			$config = self::_config ()->$name;
			
			if ($config)
			{
				foreach ($config as $conf)
				{
					$class = 'Data_Provider_' . $conf->provider;
					
					Loader::load ($class);
					
					/**
					 * @desc Новый провайдер данных
					 * @var Data_Provider_Abstract
					 */
					$provider = new $class ($conf->params);
					
					if ($provider->available ())
					{
						self::$_providers [$name] = $provider;
						break;
					}
				}
			}
		}
		
		return self::$_providers [$name];
	}
	
}