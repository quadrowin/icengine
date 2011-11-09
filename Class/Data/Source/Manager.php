<?php
/**
 * 
 * @desc Менеджер источников данных.
 * По переданному названию загружает конфиг из директории
 * "{$config}/Data/Source/" и создает соответсвующего провайдера.
 * @author Юрий
 * @package IcEngine
 */
class Data_Source_Manager
{
	
	/**
	 * @desc Загруженные источники.
	 * @var array <Data_Source>
	 */
	protected static $_sources = array ();
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	public static $config = array (
		/**
		 * @desc Название источника, вместо которого будет браться название домена.
		 * Название домена берется из $SERVER ['HTTP_HOST'].
		 * @var string
		 */ 
		'source_domain_alias'	=> 'domain',
		/**
		 * @desc Название источника, который будет использован вместо 
		 * имени домена, когда невозможно получить $SERVER ['HTTP_HOST'].
		 * @var string
		 */
		'empty_domain_source'	=> 'default',
		/**
		 * @desc Массив источников
		 * @var array
		 */ 
		'sources'	=> array (
			'default'	=> array (
				'mapper'	=> 'Null',
				'mapper_options'	=> array (
					
				)
			)
		)
	);
	
	/**
	 * @desc Загружает и возвращает конфиг
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (self::$config))
		{
			self::$config = Config_Manager::get (__CLASS__, self::$config);
		}
		return self::$config;
	}
	
	/**
	 * @desc Получение данных провайдера.
	 * @param string $name
	 * @return Data_Source
	 */
	public static function get ($name)
	{	
		$config = self::config ();
		
		if ($config ['source_domain_alias'] == $name)
		{
			$name = 
				isset ($_SERVER ['HTTP_HOST']) ? 
					$_SERVER ['HTTP_HOST'] :
					$config ['empty_domain_source'];
		}

		if (!isset (self::$_sources [$name]))
		{
			$source_config = $config ['sources'][$name];

			if (!$source_config)
			{
				foreach ($config ['sources'] as $key => $value)
				{
					if (fnmatch ($key, $name))
					{
						return self::$_sources [$name] = self::get ($key);
					}
				}
			}

			if (is_string ($source_config))
			{
				return self::$_sources [$name] = self::get ($source_config);
			}
			
			Loader::load ('Data_Source');
			self::$_sources [$name] = new Data_Source ();
			
			// Мэппер источника отличается от указанного в конфигах
			$mapper_class = 'Data_Mapper_' . $source_config ['mapper'];
			Loader::load ($mapper_class);
			$mapper = new $mapper_class;
			self::$_sources [$name]->setDataMapper ($mapper);
			
			if ($source_config ['mapper_options'])
			{
				foreach ($source_config ['mapper_options'] as $key => $value)
				{
					$mapper->setOption ($key, $value);
				}
			}
		}
		
		return self::$_sources [$name];
	}
	
	/**
	 * @desc Находит конфиг по названию.
	 * @param string $name Название источника.
	 * @return Objective|null Конфиг.
	 */
	public static function sourceConfig ($name)
	{
		$config = self::config ();
		
		if ($config ['source_domain_alias'] == $name)
		{
			$name = 
				isset ($_SERVER ['HTTP_HOST']) ? 
					$_SERVER ['HTTP_HOST'] :
					$config ['empty_domain_source'];
		}
		
		$src_cfg = $config ['sources'][$name];
		
		if (!$src_cfg)
		{
			foreach ($config ['sources'] as $key => $value)
			{
				if (fnmatch ($key, $name))
				{
					return self::sourceConfig ($key);
				}
			}
		}
		
		return
			is_string ($src_cfg) ?
				self::sourceConfig ($src_cfg) :
				$src_cfg;
	}
	
}