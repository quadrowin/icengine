<?php

include dirname (__FILE__) . '/Object/Pool.php';
include dirname (__FILE__) . '/Object/Interface.php';

abstract class Model
{
	
    /**
     * Автосоздание связанных объектов.
     * @var boolean
     */
    protected    $_autojoin;
    
	/**
	 * 
	 * @var array <Coponent_Collection>
	 */
	protected    $_components = array ();
	
	/**
	 * Связанные данные
	 * @var array
	 */
	protected    $_data = array ();
	
	/**
	 * Индекс объекта для подсчета количества
	 * загруженных моделей
	 * @var integer
	 */
	protected static    $_objectIndex = 0;
	
	/**
	 * Подгруженные объекты
	 * @var array
	 */
	protected    $_joints;  
	
	/**
	 * Данные модели
	 * @var array
	 */
	protected	$_fields;
	
	/**
	 * Все данные загружены
	 * @var boolean
	 */
	protected    $_loaded;
	
	/**
	 * 
	 * @param string $method
	 * @param mixed $params
	 * @return mixed
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
	 * 
	 * @param array $fields
	 * 		Данные модели.
	 * @param boolean $autojoin=true
	 * 		Автосоздание связанны объектов.
	 * 		Если false, поля вида Model__id не будут преобразованы в объекты
	 */
	public function __construct (array $fields = array (), 
	    $autojoin = true)
	{
	    self::$_objectIndex++;
	    
		if (!is_array ($fields))
		{
		    Loader::load ('Model_Exception');
			throw new Model_Exception ('Construct parameter must be an array.');
		}
		
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
	 * Возвращает значение поля
	 * 
	 * @param string $field
	 * 		Поле
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
			
//			if (!array_key_exists ($field, $this->_fields))
//			{
//			    Loader::load ('Model_Exception');
//			    throw new Model_Exception ('Field ' . $field . ' not found.');
//			    return null;
//			}
		}

	    return $this->_fields [$field];
	}
	
	/**
	 * @return boolean
	 */
	public function __isset ($key)
	{
		return isset ($this->_fields [$key]);
	}
	
	/**
	 * Устанавливает значение поля
	 * 
	 * @param string $field
	 * 		Поле
	 * @param mixed $value
	 * 		Значение
	 */
	public function __set($field, $value)
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
			throw new Model_Exception ('Field unexists "' . $field . '".', E_USER_WARNING);
		}
	}
	
	protected function _afterConstruct ()
	{
	    
	}
	
	/**
	 * @return array
	 */
	public function asRow ()
	{
		return $this->_fields;
	}
	
	/**
	 * 
	 * @param string $key
	 * 		
	 * @param mixed $value [optional]
	 * 
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
	 * Имя класса модели
	 * @return string
	 */
	public function className ()
	{
		return get_class ($this); 
	}
	
	/**
	 * Возвращает коллекцию связанных компонентов или
	 * элемент коллекции с указанным индексом.
	 * 
	 * @param string $type
	 * 		Тип компонентов
	 * @param integer|null|stdClass $index|$value [optional]
	 * 		Индекс для получения или коллекция для установки значения.
	 * @return Component_Collection
	 * 		Коллекция связанных компонентов
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
	 * Устанавливает или получает связанные данные объекта
	 * 
	 * @param string $key
	 * 		Ключ
	 * @param mixed $value [optional]
	 * 		Значение (не обязательно)
	 * @return mixed
	 * 		Текущее значение
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
	 * Удаление модели
	 */
	public function delete ()
	{
	    $key = $this->key ();
	    if ($key)
	    {
	        $this->modelManager ()->remove ($this);
	        
	        DDS::execute (
	            Query::instance ()
	            ->delete ()
	            ->from ($this->table ())
	            ->where ($this->keyField (), $key)
	        );
	    }
	}
	
	/**
	 * Получение или установка значения
	 * 
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
	
	public function free ()
	{
		Object_Pool::push ($this);
	}
	
	/**
	 * Получение значения атрибута
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
	 * @return boolean
	 */
	public function getAutojoin ()
	{
	    return $this->_autojoin;
	}
	
	/**
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
	 * 
	 * @param string $model
	 * @param array $data
	 * @return Model
	 */
	public function joint ($model, array $data = array ())
	{
		if (!isset ($this->_joints [$model]))
		{
			Loader::load ($model);
			
			if (!class_exists ($model))
			{
			    Loader::load ('Zend_Exception');
			    throw new Zend_Exception ("Model $model not found.");
			    return null;
			}
			
			$key_field = $this->modelManager ()->modelScheme ()->keyField ($model);
			
			if (!$data || !isset ($data [$key_field]))
			{
			    var_dump ($data);
			    Loader::load ('Zend_Exception');
			    throw new Zend_Exception ("No key field for model $model received.");
			    return null;
			}
			
			$this->_joints [$model] = $this->modelManager ()->get (
			    $model,
			    $data [$key_field],
			    $data
			);
		}
		
		return $this->_joints [$model];
	}
	
	/**
	 * Возвращает значение первичного ключа
	 * @return integer|null
	 */
	public function key ()
	{
		$kf = $this->keyField ();
		
		if (!is_array ($this->_fields))
		{
			return null;
		}
		
		if (!isset ($this->_fields [$kf]))
		{
			return null;
		}
		
		return (int) $this->_fields [$kf];
	}
	
	/**
	 * Имя поля первичного ключа
	 * @return string
	 */
	public function keyField ()
	{
		return $this->modelManager ()->modelScheme ()->keyField (
		    $this->modelName ());
	}
	
	/**
	 * Имя класса модели
	 * @return string
	 */
	public function modelName ()
	{
		return get_class ($this);
	}
	
	/**
	 * @return Model_Manager
	 */
	public function modelManager ()
	{
	    return IcEngine::$modelManager;
	}
	
	public function reset ()
	{
		$this->_attributes = array ();
		$this->_data = array ();
		$this->_fields = array ();
		$this->_joints = array ();
		$this->_loaded = false;
	}
	
	/**
	 * @return string
	 */
	public function resourceKey ()
	{
	    return $this->modelName () . '__' . $this->key ();
	}
	
	/**
	 * Сохранение данных модели
	 * @param boolean $hard_insert
	 * 		
	 * @return Model
	 */
	public function save ($hard_insert = false)
	{
	    $kf = $this->keyField ();
	    
		if ($this->key () && !$hard_insert)
		{
			DDS::execute (
				Query::instance ()
				->update ($this->table ())
				->values ($this->_fields)
				->where ($kf, $this->key ())
			);
		}
		else
		{
		    if (isset ($this->_fields [$kf]))
		    {
		        unset ($this->_fields [$kf]);
		    }
			$this->_fields [$kf] = DDS::execute (
				Query::instance ()
				->insert ($this->table ())
				->values ($this->_fields)
			)->getResult ()->insertId ();
		}
		
		return $this;
	}
	
	/**
	 * Установка значений полей без обновления источника
	 * 
	 * @param string|array $field
	 * 		
	 * @param string $value
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
    					$field = $this->modelManager ()->modelScheme ()->keyField ($model);
    					$this->joint ($model, array ($field => $value));
					}
				}
			}
		}
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function setAttribute ($key, $value = null)
	{
	    IcEngine::$attributeManager->set ($this, $key, $value);
	}
	
	/**
	 * 
	 * @param boolean $value
	 * @return Model
	 */
	public function setAutojoin ($value)
	{
	    $this->_autojoin = $value;
	    return $this;
	}
	
	/**
	 * Таблица БД
	 * @return string
	 */
	public function table ()
	{
		return $this->modelName ();
	}
	
	/**
	 * Загрузка данных модели
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
	 * Обновление данных модели и полей в БД
	 * @param array $data
	 * 		Массив пар (поле => значение)
	 * @return Model
	 */
	public function update (array $data)
	{
		$this->set ($data);
		return $this->save ();
	}
	
}