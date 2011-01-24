<?php

/**
 * 
 * @desc Класс хранящий и предоставляющий информацию о схеме моделей.
 * @author Юрий
 *
 */

class Model_Scheme
{
    
	/**
	 * Имя ключего поля модели по умолчанию.
	 * @var string
	 */
    const DEFAULT_KEY_FIELD = 'id';
	
	/**
	 * Префикс по умолчанию для всех таблиц
	 * @var string
	 */
	public $defaultPrefix = 'ice_';
    
	/**
	 * Модели
	 * @var array
	 */
	public $models = array (
		/**
		 * Класс модели.
		 * @var array
		 */
		'Abstract'	=> array (
			/**
			 * Ключевое поле.
			 * @var string
			 */
			'key'		=> 'id',
			/**
			 * Префикс таблицы.
			 * @var string
			 */
			'prefix'	=> '',
			/**
			 * Источник данных о модели.
			 * @var string
			 */
			'source'	=> 'user_session',
			/**
			 * Существующие индексы (могут использоваться в источнике).
			 * @var array
			 */
			'indexes'	=> array (
				array ('phpSessionId')
			)
		)
	);
	
	public function __construct (Config_Array $config)
	{
		if ($config->default_prefix)
		{
			$this->defaultPrefix = $config->default_prefix;
		}
		
		if ($config->models)
		{
			$this->models = $config->models->__toArray ();
		}
	}
	
	/**
	 * Получение реального имени таблицы в БД
	 * 
	 * @param string $model
	 * 		Имя модели или экземпляр класса модели
	 * @return string
	 * 		Действительное имя таблицы
	 */
	public function table ($model)
	{	
	    $model = strtolower ($model);

		if (isset ($this->models [$model]))
		{
			if (isset ($this->models [$model]['table']))
			{
				return $this->models [$model]['table'];
			}
			elseif (isset ($this->models [$model]['prefix']))
			{
				return $this->models [$model]['prefix'] . $model;
			}
		}
		
		return $this->defaultPrefix . $model;
		
//		$prefix = isset ($this->prefixes [$model]) ?
//			$this->prefixes [$model] :
//			$this->defaultPrefix;
//
//		$table = defined ($model) ? constant ($model) : $model;
//			
//		return $prefix . $table;
	}
	
	/**
	 * Источник данных для модели.
	 * @param string $model
	 * @return Data_Source_Abstract
	 */
	public function dataSource ($model)
	{
		$model = strtolower ($model);
		
		if (!isset ($this->models [$model], $this->models [$model]['source']))
		{
			return DDS::getDataSource ();
		}
		
		return Data_Source_Manager::get ($this->models [$model]['source']);
	}
	
	/**
	 * 
	 * @param string $model
	 * @return array
	 */
	public function indexes ($model)
	{
		$model = strtolower ($model);
		
		if (!isset ($this->models [$model], $this->models [$model]['indexes']))
		{
			return array ();
		}
		
		return $this->models [$model]['indexes'];
	}
	
	/**
	 * Ключевое поле для модели.
	 * @param string $model
	 * @return string
	 */
	public function keyField ($model)
	{
		if (isset ($this->keyFields [$model]))
		{
			return $this->keyFields [$model];
		}
		
		$model = $this->table ($model);
		
		if (isset ($this->keyFields [$model]))
		{
			return $this->keyFields [$model];
		}
		
		return self::DEFAULT_KEY_FIELD;
	}
    
}