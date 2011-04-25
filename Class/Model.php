<?php

include dirname (__FILE__) . '/Object/Pool.php';
include dirname (__FILE__) . '/Object/Interface.php';

/**
 * 
 * @desc Базовая модель для всех сущностей.
 * @author Юрий
 * @package IcEngine
 *
 */
abstract class Model implements ArrayAccess
{
	
	/**
	 * @desc Автосоздание связанных объектов.
	 * @var boolean
	 */
	protected	$_autojoin;
	
	/**
	 * @desc Компоненты для модели.
	 * Прикрепленные к модели изображения, видео, комментарии и пр.
	 * @var array <Coponent_Collection>
	 */
	protected	$_components = array ();
	
	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected	$_config = array ();
	
	/**
	 * @desc Связанные данные
	 * @var array
	 */
	protected	$_data = array ();
	
	/**
	 * @desc Индекс объекта для подсчета количества
	 * загруженных моделей
	 * @var integer
	 */
	protected static	$_objectIndex = 0;
	
	/**
	 * @desc Подгруженные объекты
	 * @var array
	 */
	protected	$_joints;  
	
	/**
	 * @desc Данные модели
	 * @var array
	 */
	protected	$_fields;
	
	/**
	 * @desc Все данные загружены
	 * @var boolean
	 */
	protected	$_loaded;
	
	/**
	 * @param string $method
	 * @param mixed $params
	 * @return mixed
	 * @throws Model_Exception
	 */
	public function __call ($method, $params)
	{
		if (strlen ($method) > 3 && strncmp ($method, 'get', 3) == 0)
		{
			return $this->attr (
				strtolower ($method[3]) .
				substr ($method, 4)
			);
		}
		Loader::load ('Model_Exception');
		throw new Model_Exception ("Method $method not found");
	}
	
	/**
	 * @desc Создает и возвращает модель.
	 * @param array $fields Данные модели.
	 * @param boolean $autojoin=true
	 * 		Автосоздание связанны объектов.
	 * 		Если false, поля вида Model__id не будут преобразованы в объекты
	 */
	public function __construct (array $fields = array (), $autojoin = true)
	{
		self::$_objectIndex++;
		
		$this->_loaded = false;
		$this->_fields = $fields;
		$this->_autojoin = $autojoin;
		
		if ($fields)
		{
			$this->set ($fields);
		}
		
		$this->_afterConstruct ();
	}
	
	/**
	 * @desc Возвращает поле.
	 * @param string $field Поле
	 * @return mixed
	 */
	public function __get ($field)
	{
		if (!array_key_exists ($field, $this->_fields))
		{
			if (isset ($this->_joints [$field]))
			{
				return $this->_joints [$field];
			}
			
			if (array_key_exists ($field . '__id', $this->_fields))
			{
				return $this->joint ($field);
			}
			elseif (!$this->_loaded)
			{
				$this->load ();
				if (
					!array_key_exists ($field, $this->_fields) &&
					array_key_exists ($field . '__id', $this->_fields)
				)
				{
					return $this->joint ($field);
				}
			}
		}

		return $this->_fields [$field];
	}
	
	/**
	 * (non-PHPDoc)
	 * @return boolean
	 */
	public function __isset ($key)
	{
		return isset ($this->_fields [$key]);
	}
	
	/**
	 * (non-PHPDoc)
	 * @param string $field
	 * 		Поле
	 * @param mixed $value
	 * 		Значение
	 */
	public function __set ($field, $value)
	{
		if (!array_key_exists ($field, $this->_fields) && !$this->_loaded)
		{
			$this->load ();
		}
		
		if (array_key_exists ($field, $this->_fields))
		{
			$this->_fields [$field] = $value;
		}
		else
		{
			Loader::load ('Model_Exception');
			throw new Model_Exception ('Field unexists "' . $field . '".');
		}
	}
	
	/**
	 * @desc Метод вызывается из конструктора после завершения инициализации.
	 */
	protected function _afterConstruct ()
	{
		
	}
	
	/**
	 * @desc Возвращает массив, создержащий все поля модели.
	 * @return array
	 */
	public function asRow ()
	{
		return $this->_fields;
	}
	
	/**
	 * @desc Возвращает или устанавливает значение атрибута.
	 * @param string|array $key
	 * 		Название атрибута или массив пар (название => значение)
	 * @param mixed $value [optional]
	 * 		Новое значение атрибута.
	 * @return mixed
	 * 		Если не задан второй параметр, возвращает значение аттрибута,
	 * 		иначе null
	 */
	public function attr ($key)
	{
		if (func_num_args () == 1)
		{
			if (!is_array ($key))
			{
				return $this->getAttribute ($key);	
			}
			else
			{
				$this->setAttribute ($key);
				return;
			}
		}

		$v = func_get_arg (1);
		$this->setAttribute ($key, $v);
	}
	
	/**
	 * @desc Имя класса модели
	 * @return string
	 */
	public function className ()
	{
		return get_class ($this); 
	}
	
	/**
	 * @desc Возвращает коллекцию связанных компонентов или
	 * элемент коллекции с указанным индексом.
	 * 
	 * @param string $type Тип компонентов.
	 * @param integer|null|stdClass $index|$value [optional]
	 * 		Индекс для получения или коллекция для установки значения.
	 * @return Component_Collection Коллекция связанных компонентов.
	 */
	public function component ($type)
	{
		$index = null;
		
		if (func_num_args () > 1)
		{
			$arg1 = func_get_arg (1);
			if (is_null ($arg1) || ($arg1 instanceof stdClass))
			{
				$this->_components [$type] = $arg1;
				return ;
			}
			
			if (is_int ($arg1))
			{
				$index = $arg1;
			}
		}
		
		if (!isset ($this->_components [$type]))
		{
			$this->_components [$type] = Component::getFor ($this, $type);
		}
		
		return is_null ($index) ? 
			$this->_components [$type] : 
			$this->_components [$type]->item ($index);
	}
	
	/**
	 * @desc Загружает и возвращает конфиг для модели
	 * @param string $class Класс модели, если отличен от get_class ($this)
	 * @return Objective
	 */
	public function config ($class = null)
	{
		if (is_array ($this->_config))
		{
			$this->_config = Config_Manager::get (
				$class ? $class : get_class ($this),
				$this->_config
			);
		}
		return $this->_config;
	}
	
	/**
	 * @desc Устанавливает или получает связанные данные объекта
	 * @param string $key Ключ.
	 * @param mixed $value [optional] Значение (не обязательно).
	 * @return mixed Текущее значение или null.
	 */
	public function data ($key)
	{
		if (func_num_args () == 1)
		{
			return isset ($this->_data [$key]) ? $this->_data [$key] : null;
		}
		
		$this->_data [$key] = func_get_arg (1);
	}
	
	/**
	 * @desc Удаление модели
	 */
	public function delete ()
	{
		$key = $this->key ();
		if ($key)
		{
			$this->modelManager ()->remove ($this);
		}
	}
	
	/**
	 * @desc Возвращает коллекцию моделей типа $model,
	 * связанных по первичному ключу с этой моделью.
	 * В модели $model должно существовать поле "THISMODEL__id",
	 * где THISMODEl - название этой модели.
	 * @param string $model_name
	 * @return Model_Collection
	 */
	public function external ($model)
	{
		$coll = Model_Collection_Manager::byQuery (
			$model,
			Query::instance ()
				->where ($this->modelName () . '__id', $this->key ())
		);
		
		return $coll;
	}
	
	/**
	 * @desc Получение или установка значения
	 * @param string $key
	 * 		Поле
	 * @param mixed $value
	 * 		Значение (не обязательно).
	 * 		Если указано значение, оно будет записано в поле.
	 * @return mixed
	 * 		Если $value не передан, будет возвращено значение поля.
	 */
	public function field ($key)
	{
		if (func_num_args () > 1)
		{
			$this->__set ($key, func_get_arg (1));
		}
		else
		{
			return $this->__get ($key);
		}
	}
	
	/**
	 * @desc Освободить модель и поместить ее в пул моделей
	 */
	public function free ()
	{
		Object_Pool::push ($this);
	}
	
	/**
	 * @desc Получение значения атрибута
	 * 
	 * @param string $key
	 * 		Название атрибута
	 * @return mixed
	 * 		Значение атрибута.
	 * 		Если атрибута не существует, возвращает null.
	 */
	public function getAttribute ($key)
	{
		return IcEngine::$attributeManager->get ($this, $key);
	}
	
	/**
	 * @desc Установлен ли автоджоин
	 * @return boolean
	 */
	public function getAutojoin ()
	{
		return $this->_autojoin;
	}
	
	/**
	 * @desc Получить значения полей
	 * @return array<string>
	 */
	public function getFields ()
	{
		return $this->_fields;
	}
	
	/**
	 * @desc Проверяет существование поля в модели.
	 * @return boolean
	 */
	public function hasField ($field)
	{
		if (!isset ($this->_fields))
		{
			if ($this->_loaded)
			{
				return false;
			}
			
			$this->load ();
		}
		
		return isset ($this->_fields [$field]);
	}
	
	/**
	 * @desc Присоединить сущность
	 * @param string $model
	 * @param array $data
	 * @return Model
	 * @throws Zend_Exception
	 */
	public function joint ($model, array $data = array ())
	{
		if ($data)
		{
			Loader::load ($model);
						
			if (!class_exists ($model))
			{
				Loader::load ('Zend_Exception');
				throw new Zend_Exception ("Model $model not found.");
				return null;
			}
			
			$key_field = $this->modelManager ()->modelScheme ()->keyField ($model);
			
			if (!$data || !array_key_exists ($key_field, $data))
			{
				var_dump ($data, $this->_joints);
				Loader::load ('Zend_Exception');
				throw new Zend_Exception (
					'In the model ' . get_class ($this) .
					" no key field for model $model received."
				);
				return null;
			}
			
			$this->_joints [$model] = Model_Manager::byKey (
				$model,
				$data [$key_field]
			);
//			$this->_joints [$model] = $this->modelManager ()->get (
//				$model,
//				$data [$key_field],
//				$data
//			);
		}
		
		return $this->_joints [$model];
	}
	
	/**
	 * @desc Возвращает значение первичного ключа
	 * @return string|null
	 */
	public function key ()
	{
		$kf = $this->keyField ();
		
		if (!is_array ($this->_fields) || !isset ($this->_fields [$kf]))
		{
			return null;
		}
		
		return $this->_fields [$kf];
	}
	
	/**
	 * @desc Имя поля первичного ключа
	 * @return string
	 */
	public function keyField ()
	{
		return $this->modelManager ()->modelScheme ()->keyField (
			$this->modelName ());
	}
	
	/**
	 * @desc Имя класса модели
	 * @return string
	 */
	public function modelName ()
	{
		return get_class ($this);
	}
	
	/**
	 * @desc Получить текущий менеджер моделей
	 * @return Model_Manager
	 */
	public function modelManager ()
	{
		return IcEngine::$modelManager;
	}
	
	/**
	 * @desc Проверяет существование поля.
	 * @param string $offset Название поля
	 * @return boolean true если поле существует
	 */
	public function offsetExists ($offset)
	{
		return isset ($this->_fields [$offset]);
	}

	/**
	 * @see Model::__get
	 */
	public function offsetGet ($offset)
	{
		return $this->__get ($offset);
	}

	/**
	 * @see Model::__set
	 */
	public function offsetSet ($offset, $value)
	{
		$this->__set ($offset, $value);
	}

	/**
	 * @desc Исключение поля из модели.
	 * @param string $offset название поля
	 */
	public function offsetUnset ($offset)
	{
		unset ($this->_fields [$offset]);
	}
	
	/**
	 * 
	 * @desc Сбросить модель
	 */
	public function reset ()
	{
		$this->_attributes = array ();
		$this->_data = array ();
		$this->_fields = array ();
		$this->_joints = array ();
		$this->_loaded = false;
	}
	
	/**
	 * @desc Название ресурса модели.
	 * Состоит из название модели и первичного ключа.
	 * @return string
	 */
	public function resourceKey ()
	{
		return $this->modelName () . '__' . $this->key ();
	}
	
	/**
	 * @desc Сохранение данных модели
	 * @param boolean $hard_insert
	 * @return Model
	 */
	public function save ($hard_insert = false)
	{
		$this->modelManager ()->set ($this, $hard_insert);
		
		return $this;
	}
	
	/**
	 * @desc Установка значений полей без обновления источника.
	 * При использовании этого метод не проверяется сущестовование полей
	 * у модели. Это позволяет установить поля для создаваемой модели,
	 * однако может привести к ошибкам в дальнейшем при сохранее, если 
	 * были заданы несуществующие поля.
	 * 
	 * @param string|array $field
	 * 		Имя поля или массив пар "поле - значение".
	 * @param string $value
	 * 		Значение поля для случае, если первым параметром передано имя.
	 */
	public function set ($field, $value = null)
	{
		$fields = is_array ($field) ? $field : array ($field => $value);
		
		$this_model = $this->modelName ();
		
		foreach ($fields as $key => $value)
		{
			$p = strpos ($key, '__');
			if ($p === false)
			{
				$this->_fields [$key] = $value;
			}
			else
			{
				$model = substr ($key, 0, $p);
				$field = substr ($key, $p + 2);
			
				if ($model == $this_model)
				{
					$this->_fields [$field] = $value;
				}
				else
				{
					$this->_fields [$key] = $value;
					if ($this->_autojoin)
					{
						$field = Model_Manager::modelScheme ()->keyField ($model);
						$this->joint ($model, array ($field => $value));
					}
				}
			}
		}
	}
	
	/**
	 * @desc Устанавливает значение аттрибута.
	 * @param string|array $key
	 * 		Название аттрибута или массив пар (название => значение)
	 * @param mixed $value [optional]
	 * 		Новое значение аттрибута.
	 */
	public function setAttribute ($key, $value = null)
	{
		IcEngine::$attributeManager->set ($this, $key, $value);
	}
	
	/**
	 * @desc Установить автоджоин
	 * @param boolean $value
	 * @return Model
	 */
	public function setAutojoin ($value)
	{
		$this->_autojoin = $value;
		return $this;
	}
	
	/**
	 * @desc Таблица БД
	 * @return string
	 */
	public function table ()
	{
		return $this->modelName ();
	}
	
	/**
	 * @desc Загрузка данных модели
	 * @param mixed $key
	 * @return Model
	 */
	public function load ($key = null)
	{
		if (is_null ($key))
		{
			$this->modelManager ()->get (
				$this->modelName (), $this->key (), $this);
		}
		else
		{
			$this->modelManager ()->get (
				$this->modelName (), $key, $this);
		}
		
		return $this;
	}
	
	/**
	 * @desc Проверить модель на валидность по полям
	 * @param mixed (string,string|array<string>|Query) $fields
	 * @return boolean
	 */
	public function validate ($fields)
	{
		$args = func_get_args ();
		if (sizeof ($args) == 2)
		{
			$args = array ($args [0] => $args [1]);
		}
		else
		{
			$args = $args [0];
			if ($args instanceof Query)
			{
				$tmp = array ();
				$args = $args->getPart (Query::WHERE);
				for ($i = 0, $icount = sizeof ($args); $i < $icount; $i++)
				{
					$tmp [$args [$i][Query::WHERE]] = $args [$i][Query::VALUE];
				}
			}
		}
		$valid = true;
		foreach ((array) $args as $field=>$value)
		{
			if ($this->_fields [$field] != $value)
			{
				$valid = false;
				break;
			}
		}
		return $valid;
	}
	
	/**
	 * @desc Удаляет поле из объекта.
	 * Используется в Model_Manager для удаления первичного ключа перед 
	 * вставкой в БД.
	 * @param string $name Имя поля.
	 * @return Model Эта модель.
	 */
	public function unsetField ($name)
	{
		if (array_key_exists ($name, $this->_fields))
		{
			unset ($this->_fields [$name]);
		}
		return $this;
	}
	
	/**
	 * @desc Обновление данных модели и полей в БД.
	 * @param array $data Массив пар (поле => значение).
	 * @return Model Эта модель.
	 */
	public function update (array $data)
	{
		$this->set ($data);
		return $this->save ();
	}
	
	/**
	 * @desc Аккуратное обновление модели. Используется, когда в модели
	 * могут присутствовать посторонние поля (результаты запросов опций и
	 * т.п.). В таком случае все поля будут помещены в буфер, модель будет
	 * сохранена, после чего отложенные поля будут обратно наложены.
	 * @param array $data Массив пар (поле => значение).
	 * @return Model Эта модель.
	 */
	public function updateCarefully (array $data)
	{
		$this->set ($data);
		
		$pseudos = array ();
		
		// Список существующий в модели полей
		$scheme = Model_Manager::modelScheme ()->fieldsNames (
			$this->modelName ()
		);
		
		foreach ($this->_fields as $name => $value)
		{
			if (array_search ($name, $scheme) === false)
			{
				// Псевдополе
				$pseudos [$name] = $value;
				unset ($this->_fields [$name]);
			}
		}
		
		$this->save ();
		
		$this->_fields = array_merge (
			$this->_fields,
			$pseudos
		);
		
		return $this;
	}
	
}