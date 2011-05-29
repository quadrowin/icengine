<?php
/**
 * 
 * @desc Класс хранящий и предоставляющий информацию о схеме моделей.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
abstract class Model_Scheme
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
	public static $defaultPrefix = 'ice_';
    
	/**
	 * @desc Модели
	 * @var array
	 */
	public static $models = array (
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
	
	/**
	 * @desc Инициализация схемы моделей
	 * @param Config_Array $config
	 */
	public static function init (Config_Array $config)
	{
		if ($config->default_prefix)
		{
			self::$defaultPrefix = $config->default_prefix;
		}
		
		if ($config->models)
		{
			self::$models = $config->models->__toArray ();
		}
	}
	
	/**
	 * @desc Генерирует новый ID для модели, если для этого в схеме заданы
	 * правила.
	 * @param Model $model
	 * @return string|null.
	 * 		Сгенерированный ключ или null, если правила не заданы.
	 */
	public static function generateKey (Model $model)
	{
		$name = strtolower ($model->modelName ());
		
		if (!isset (
			self::$models, self::$models [$name], 
			self::$models [$name]['keyGen']
		))
		{
			return null;
		}
		
		$keygen = explode ('::', self::$models [$name]['keyGen'], 2);
		
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
	public static function table ($model)
	{	
		if (is_array ($model))
		{
			var_dump ($model);
			echo '<pre>';
			debug_print_backtrace ();
			echo '</pre>';
		}
		
	    $model = strtolower (
	    	is_object ($model) ? $model->modelName () : $model
	    );

		if (isset (self::$models [$model]))
		{
			if (is_string (self::$models [$model]))
			{
				if (empty (self::$models [$model]))
				{
					return $model;
				} 
				$model = self::$models [$model];
			}
			
			if (isset (self::$models [$model]['table']))
			{
				return self::$models [$model]['table'];
			}
			elseif (isset (self::$models [$model]['prefix']))
			{
				return self::$models [$model]['prefix'] . $model;
			}
		}
		elseif (strpos ($model, '`') !== false)
		{
			return $model;
		}
		
		return self::$defaultPrefix . $model;
		
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
	public static function tableToModel ($table)
	{
		foreach (self::$models as $name => $model)
		{
			if (isset ($model ['table']) && $model ['table'] == $table)
			{
				$name = explode ('_', $name);
				$name = array_map ('ucfirst', $name);
				return implode ('_', $name);
			}
		}

		$table = explode('_', $table);
		$table = array_map('ucfirst', $table);
		return implode('_', $table);
	}
	
	/**
	 * @desc Источник данных для модели.
	 * @param string $model название модели.
	 * @return Data_Source_Abstract
	 */
	public static function dataSource ($model)
	{
		$model = strtolower ($model);
		
		if (!isset (self::$models [$model], self::$models [$model]['source']))
		{
			$name = 'default';
		}
		else
		{
			$name = self::$models [$model]['source'];
		}
		
		return Data_Source_Manager::get ($name);
	}
	
	/**
	 * @desc Возвращает названия полей модели.
	 * @param string $model Название модели.
	 * @return array <string> Массив названий полей.
	 */
	public static function fieldsNames ($model)
	{
		Loader::load ('Helper_Data_Source');
		
		return Helper_Data_Source::fields ($model);
	}
	
	/**
	 * @desc Индексы модели.
	 * @param string $model Название модели.
	 * @return array Массив индексов.
	 */
	public static function indexes ($model)
	{
		$model = strtolower ($model);
		
		if (!isset (self::$models [$model], self::$models [$model]['indexes']))
		{
			return array ();
		}
		
		return self::$models [$model]['indexes'];
	}
	
	/**
	 * @desc Ключевое поле для модели.
	 * @param string $model Название модели.
	 * @return string Имя ключевого поля.
	 */
	public static function keyField ($model)
	{
		$model = strtolower ($model);
		
		if (!isset (self::$models [$model], self::$models [$model]['key']))
		{
			return self::DEFAULT_KEY_FIELD;
		}
		
		return self::$models [$model]['key'];
	}
    
}