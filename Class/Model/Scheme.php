<?php
/**
 * 
 * @desc Класс хранящий и предоставляющий информацию о схеме моделей.
 * @author Юрий
 * @package IcEngine
 *
 */
class Model_Scheme
{
    
	/**
	 * @desc Имя ключего поля модели по умолчанию.
	 * @var string
	 */
    const DEFAULT_KEY_FIELD = 'id';
	
	/**
	 * @desc Префикс по умолчанию для всех таблиц
	 * @var string
	 */
	public $defaultPrefix = 'ice_';
    
	/**
	 * @desc Модели
	 * @var array
	 */
	public $models = array (
		/**
		 * @desc Класс модели.
		 * Обязательно в нижнем регистре.
		 * @var array
		 */
		'abstract'	=> array (
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
		),
		/**
		 * @desc Название таблицы, которое не должно изменяться 
		 * при построении запроса.
		 * @var string
		 */
		'table'	=> '',
		/**
		 * @desc Альтернативное название модели.
		 * @var string
		 */
		'alt_name'	=> 'abstract'
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
	 * @desc Генерирует новый ID для модели, если для этого в схеме заданы
	 * правила.
	 * @param Model $model
	 * @return string|null.
	 * 		Сгенерированный ключ или null, если правила не заданы.
	 */
	public function generateKey (Model $model)
	{
		$name = strtolower ($model->modelName ());
		
		if (!isset (
			$this->models, $this->models [$name], 
			$this->models [$name]['keyGen']
		))
		{
			return null;
		}
		
		$keygen = explode ('::', $this->models [$name]['keyGen'], 2);
		
		if (count ($keygen) != 2)
		{
			return null;
		}
		
		Loader::load ($keygen [0]);
		
		return call_user_func ($keygen, $model);
	}
	
	/**
	 * @desc Получение реального имени таблицы в БД.
	 * @param string|Model $model Имя модели или экземпляр класса модели.
	 * @return string Действительное имя таблицы.
	 */
	public function table ($model)
	{	
		if (is_array($model))
		{
			var_dump ($model);
			echo '<pre>';
			debug_print_backtrace ();
			echo '</pre>';
			
		}
		
	    $model = strtolower (
	    	is_object ($model) ? $model->modelName () : $model
	    );

		if (isset ($this->models [$model]))
		{
			if (is_string ($this->models [$model]))
			{
				if (empty ($this->models [$model]))
				{
					return $model;
				} 
				$model = $this->models [$model];
			}
			
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
	 * @desc Определеяет название модели по названию таблицы.
	 * @param string $table
	 * @return string
	 */
	public function tableToModel ($table)
	{
		foreach ($this->models as $name => $model)
		{
			if (isset ($model ['table']) && $model ['table'] == $table)
			{
				$name = explode ('_', $name);
				$name = array_map ('ucfirst', $name);
				return implode ('_', $name);
			}
		}
		
		return $table;
	}
	
	/**
	 * @desc Источник данных для модели.
	 * @param string $model название модели.
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
	 * @desc Возвращает названия полей модели.
	 * @param string $model Название модели.
	 * @return array <string> Массив названий полей.
	 */
	public function fieldsNames ($model)
	{
		Loader::load ('Model_Field');
		return $this->dataSource ($model)->execute (
			Query::instance ()
				->show ('COLUMNS')
				->from ($model)
		)->getResult ()->asColumn ('Field');
	}
	
	/**
	 * @desc Индексы модели.
	 * @param string $model Название модели.
	 * @return array Массив индексов.
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
	 * @desc Ключевое поле для модели.
	 * @param string $model Название модели.
	 * @return string Имя ключевого поля.
	 */
	public function keyField ($model)
	{
		$model = strtolower ($model);
		
		if (!isset ($this->models [$model], $this->models [$model]['key']))
		{
			return self::DEFAULT_KEY_FIELD;
		}
		
		return $this->models [$model]['key'];
	}
    
}