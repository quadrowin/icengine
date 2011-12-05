<?php

namespace Ice;

/**
 *
 * @desc Класс хранящий и предоставляющий информацию о схеме моделей.
 * @author Юрий Шведов, Илья Колесников
 * @package Ice
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
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array();

	/**
	 * @desc Схема линка по умолчанию
	 * @var array
	 */
	public static $defaultLinkScheme = array (

	);

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
	 * @desc Префиксы для пространств имен
	 * @var array
	 */
	public static $namespaces = array (
		__NAMESPACE__ => array (
			'keyGen' => null,
			'prefix' => 'ice_'
		)
	);

	/**
	 * @desc Схемы моделей
	 * @var array
	 */
	private static $_modelSchemes = array ();

	/**
	 * @desc Схема по умолчанию
	 * @var array
	 */
	private static $_defaultScheme = array (
		'int'		=> array (
			'size'		=> 11,
		),
		'tinyint'	=> array (
			'size'		=> 1
		),
		'varchar'	=> array (
			'collation'	=> 'utf8_general_ci',
			'default'	=> ''
		)
	);

	/**
	 * @desc Создает схему модели на основании полей,
	 * полученных от mysql
	 * @param array $fields
	 * @return array
	 */
	private static function _makeScheme ($fields)
	{
		$scheme = array ();

		foreach ($fields as $field)
		{
			$size = null;

			$auto_inc = false;

			if ($field ['Extra'] == 'auto_increment')
			{
				$auto_inc = true;
			}

			$type = $field ['Type'];
			$br_pos = strpos ($type, '(');
			if ($br_pos !== false)
			{
				$size = substr ($type, $br_pos);
				$type = substr ($type, 0, $br_pos);
				$size = (int) trim ($size, '()');
			}

			$collation = null;

			if ($field ['Collation'])
			{
				$collation = $field ['Collation'];
			}

			$comment = $field ['Comment'];

			$default = $field ['Default'];

			$s = array (
				'type'		=> $type,
				'comment'	=> $comment
			);

			if ($auto_inc)
			{
				$s ['auto_inc'] = true;
			}

			if (
				$type == 'varchar' ||
				strpos ($type, 'text') !== false
			)
			{
				$s ['collation'] = $collation;
			}

			if (strpos ($type, 'text') === false)
			{
				$s ['size'] = $size;
				$s ['default'] = $default;
			}

			$field = $field ['Field'];

			$scheme [$field] = $s;
		}
		return $scheme;
	}

	/**
	 * @desc Инициализация схемы моделей
	 * @param Config_Array $config
	 */
	public static function init (Config_Array $config)
	{
		self::$_config = $config->__toArray ();

		if (isset (self::$_config ['models']))
		{
			self::$models = self::$_config ['models'];
		}

		if (isset (self::$_config ['namespaces']))
		{
			self::$namespaces = self::$_config ['namespaces'];
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

		if (!isset (self::$models [$name], self::$models [$name]['keyGen']))
		{
			$p = strrpos ($name, '\\');

			$ns = (false !== $p)
				? substr ($name, 0, $p)
				: __NAMESPACE__;

			if (!isset (self::$namespaces [$ns]['keyGen']))
			{
				return null;

			}

			$keygen = explode ('::', self::$namespaces [$ns]['keyGen'], 2);
		}
		else
		{
			$keygen = explode ('::', self::$models [$name]['keyGen'], 2);
		}

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

		$p = strrpos ($model, '\\');
		if (false !== $p)
		{
			$namespace = substr ($model, 0, $p);
			$table = substr ($model, $p + 1);
			return self::$namespaces [$namespace]['prefix'] . $table;
		}

		return self::$namespaces [__NAMESPACE__]['prefix'] . $model;

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
		$prefix = self::$namespaces [__NAMESPACE__]['prefix'];

		foreach (self::$models as $name => $model)
		{
			if (isset ($model ['table']) && $model ['table'] == $table)
			{
				$name = explode ('_', $name);

				$name = array_map ('ucfirst', $name);
				return implode ('_', $name);
			}
		}

		$table = explode ('_', $table);

		if ($table [0] . '_' == $prefix)
		{
			array_shift ($table);
		}

		$table = array_map ('ucfirst', $table);
		return implode ('_', $table);
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
			return DDS::getDataSource ();
		}

		$name = self::$models [$model]['source'];

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
		return Helper_Data_Source::fields ($model)->column ('Field');
	}

	/**
	 * @desc Получить схему модели
	 * @param string $model_name
	 * @return array
	 */
	public static function getScheme ($model_name)
	{
		if (!isset (self::$_modelSchemes [$model_name]))
		{
			$scheme = Resource_Manager::get (
				__CLASS__,
				$model_name
			);

			if (!$scheme)
			{
				Loader::load ('Helper_Data_Source');
				$fields = Helper_Data_Source::fields ($model_name);

				if ($fields)
				{
					$fields = self::_makeScheme ($fields);

					$scheme = array (
						'fields'	=> $fields,
						'keys'		=> array ()
					);
				}
				else
				{
					return;
				}
			}

			self::setScheme ($model_name, $scheme);
		}

		return self::$_modelSchemes [$model_name];
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

	/**
	 * @desc Получить все ссылки модели
	 * @param string $model1
	 * @return array
	 */
	public static function links ($model1)
	{
		$model1 = strtolower ($model1);

		return !empty (self::$models [$model1]['links'])
			? self::$models [$model1]['links']
			: null;
	}

	/**
	 * @desc Возвращает схему связи.
	 * @param string $model1
	 * @param string $model2
	 * @return array
	 */
	public static function linkScheme ($model1, $model2)
	{
		$model1 = strtolower ($model1);

	    return empty (self::$models [$model1]['links'][$model2])
			? self::$defaultLinkScheme
			: self::$models [$model1]['links'][$model2];
	}

	/**
	 * @desc Изменить схему модели
	 * @param Model $model
	 */
	public static function setScheme ($model, $scheme = null)
	{
		$model_name = $model;

		if ($model instanceof Model)
		{
			$model_name = $model->modelName ();
			$scheme = $model->scheme ();
		}

		if (isset ($scheme ['fields']))
		{
			foreach ($scheme ['fields'] as &$field)
			{
				if (isset (self::$_defaultScheme [$field ['type']]))
				{
					$field = array_merge (
						$field,
						self::$_defaultScheme [$field ['type']]
					);
				}
			}
		}

		self::$_modelSchemes [$model_name] = $scheme;

		Resource_Manager::set (
			__CLASS__,
			$model_name,
			$scheme
		);
	}

}