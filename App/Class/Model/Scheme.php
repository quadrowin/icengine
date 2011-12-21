<?php

namespace Ice;

/**
 *
 * @desc Класс хранящий и предоставляющий информацию о схеме моделей.
 * @author Юрий Шведов, Илья Колесников
 * @package Ice
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
	 * @desc Конфиг
	 * @var array
	 */
	protected $_config = array ();

	/**
	 * @desc Схема линка по умолчанию
	 * @var array
	 */
	public $defaultLinkScheme = array (

	);

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
			'indexes' => array (
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
		'alt_name' => 'abstract'
	);

	/**
	 * @desc Префиксы для пространств имен
	 * @var array
	 */
	public $namespaces = array (
		'ice' => array (
			'keyGen' => null,
			'prefix' => 'ice_'
		)
	);

	/**
	 * @desc Схемы моделей
	 * @var array
	 */
	protected $_modelSchemes = array ();

	/**
	 * @desc Схема по умолчанию
	 * @var array
	 */
	protected $_defaultScheme = array (
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
	protected function _makeScheme ($fields)
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
	public function init (Config_Array $config)
	{
		$this->_config = $config->__toArray ();

		if (isset ($this->_config ['models']))
		{
			$this->models = $this->_config ['models'];
		}

		if (isset ($this->_config ['namespaces']))
		{
			$this->namespaces = $this->_config ['namespaces'];
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

		if (!isset ($this->models [$name], $this->models [$name]['keyGen']))
		{
			$p = strrpos ($name, '\\');

			$ns = (false !== $p)
				? substr ($name, 0, $p)
				: strtolower (__NAMESPACE__);

			if (!isset ($this->namespaces [$ns]['keyGen']))
			{
				return null;

			}

			$keygen = explode ('::', $this->namespaces [$ns]['keyGen'], 2);
		}
		else
		{
			$keygen = explode ('::', $this->models [$name]['keyGen'], 2);
		}

		if (count ($keygen) != 2)
		{
			return null;
		}

		Loader::load ($keygen [0]);

		return call_user_func ($keygen, $model);
	}

	/**
	 * @desc Индексы модели.
	 * @param string $model Название модели.
	 * @return array Массив индексов.
	 */
	public function getIndexes ($model)
	{
		$model = strtolower ($model);

		if (!isset ($this->models [$model], $this->models [$model]['indexes']))
		{
			return array ();
		}

		return $this->models [$model]['indexes'];
	}

	/**
	 * @desc Возвращает используемый экземпляр класса
	 * @return Model_Scheme
	 */
	public static function getInstance ()
	{
		return Core::di ()->getInstance (__CLASS__);
	}

	/**
	 * @desc Возвращает названия полей модели.
	 * @param string $model Название модели.
	 * @return array <string> Массив названий полей.
	 */
	public function fieldsNames ($model)
	{
		Loader::load ('Helper_Data_Source');
		return Helper_Data_Source::fields ($model)->column ('Field');
	}

	/**
	 * @desc Источник данных для модели.
	 * @param string $model название модели.
	 * @return Data_Source_Abstract
	 */
	public function getDataSource ($model)
	{
		$model = strtolower ($model);

		if (isset ($this->models [$model], $this->models [$model]['source']))
		{
			$name = $this->models [$model]['source'];
			return Data_Source_Manager::get ($name);
		}

		$p = strrpos ($model, '\\');
		if ($p)
		{
			$ns = substr ($model, 0, $p);
			if (
				isset (
					$this->namespaces [$ns],
					$this->namespaces [$ns]['source']
				)
			)
			{
				$name = $this->namespaces [$ns]['source'];
				return Data_Source_Manager::get ($name);
			}
		}

		return DDS::getDataSource ();
	}

	/**
	 * @desc Ключевое поле для модели.
	 * @param string $model Название модели.
	 * @return string Имя ключевого поля.
	 */
	public function getKeyField ($model)
	{
		$model = strtolower ($model);

		if (!isset ($this->models [$model], $this->models [$model]['key']))
		{
			return self::DEFAULT_KEY_FIELD;
		}

		return $this->models [$model]['key'];
	}

	/**
	 * @desc Получить схему модели
	 * @param string $model_name
	 * @return array
	 */
	public function getScheme ($model_name)
	{
		if (!isset ($this->_modelSchemes [$model_name]))
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
					$fields = $this->_makeScheme ($fields);

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

			$this->setScheme ($model_name, $scheme);
		}

		return $this->_modelSchemes [$model_name];
	}

	/**
	 * @desc Получить все ссылки модели
	 * @param string $model1
	 * @return array
	 */
	public function links ($model1)
	{
		$model1 = strtolower ($model1);

		return !empty ($this->models [$model1]['links'])
			? $this->models [$model1]['links']
			: null;
	}

	/**
	 * @desc Возвращает схему связи.
	 * @param string $model1
	 * @param string $model2
	 * @return array
	 */
	public function getLinkScheme ($model1, $model2)
	{
		$model1 = strtolower ($model1);

	    return empty ($this->models [$model1]['links'][$model2])
			? $this->defaultLinkScheme
			: $this->models [$model1]['links'][$model2];
	}

	/**
	 * @desc Изменить схему модели
	 * @param Model $model
	 */
	public function setScheme ($model, $scheme = null)
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
				if (isset ($this->_defaultScheme [$field ['type']]))
				{
					$field = array_merge (
						$field,
						$this->_defaultScheme [$field ['type']]
					);
				}
			}
		}

		$this->_modelSchemes [$model_name] = $scheme;

		Resource_Manager::set (
			__CLASS__,
			$model_name,
			$scheme
		);
	}

	/**
	 * @desc Получение реального имени таблицы в БД.
	 * @param string|Model $model Имя модели или экземпляр класса модели.
	 * @return string Действительное имя таблицы.
	 */
	public function table ($model)
	{
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
		elseif (strpos ($model, '`') !== false)
		{
			return $model;
		}

		$p = strrpos ($model, '\\');
		if (false !== $p)
		{
			$namespace = substr ($model, 0, $p);
			$table = substr ($model, $p + 1);
			return $this->namespaces [$namespace]['prefix'] . $table;
		}

		$namespace = strtolower (__NAMESPACE__);
		return $this->namespaces [$namespace]['prefix'] . $model;

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
		$namespace = strtolower (__NAMESPACE__);
		$prefix = $this->namespaces [$namespace]['prefix'];

		foreach ($this->models as $name => $model)
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

}