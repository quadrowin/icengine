<?php

abstract class Model_Collection implements ArrayAccess, IteratorAggregate, Countable 
{
	
    /**
     * Для создаваемых моделей включен autojoin.
     * @var boolean
     */
    protected $_autojoin = true;
    
	/**
	 * Связанные данные
	 * @var array
	 */
	protected $_data = array ();
	
	/**
	 * Элементы коллекции
	 * @var array
	 */
	protected $_items;
	
	/**
	 * Опции
	 * @var Model_Collection_Option_Item_Collection
	 */
	protected $_options;
	
	/**
	 * 
	 * @var Paginator
	 */
	protected $_paginator;
	
	/**
	 * Выбираемые поля
	 * @var array
	 */
	protected $_select = array ();
	
	/**
	 * 
	 * @var Query
	 */
	protected $_lastQuery;
	
	/**
	 * 
	 * @var Query
	 */
	protected $_query;
	
	/**
	 * 
	 * @var Query_Result
	 */
	protected $_queryResult;
	
	/**
	 * Условия
	 * @var array
	 */
	protected $_where = array ();
	
	public function __construct ()
	{
       	Loader::load ('Model_Collection_Option_Item_Collection');
    	$this->_options =
    	    new Model_Collection_Option_Item_Collection ($this->modelName ());
    	Loader::load ($this->modelName ());
	}
	
	/**
	 * 
	 * @param Model|Model_Collection|array $item
	 * @return Model_Collection
	 * @throws Zend_Exception
	 */
	public function add ($item)
	{
		if ($item instanceof Model)
		{
			$this->_items [] = $item;
		}
		elseif ($item instanceof Model_Collection)
		{
			foreach ($item as $model)
			{
				$this->_items [] = $model;
			}
		}
		elseif (is_array ($item))
		{
		    $key_field = $this->keyField ();
		    
		    if (isset ($item [$key_field]))
		    {
		        // Ести ключевое поле
		        $item = IcEngine::$modelManager->get (
		            $this->modelName (),
		            $item [$key_field],
		            $item
		        );
		    }
		    else
		    {
		        // Ключевое поле не задано
		        $class = $this->modelName ();
		        $item = new $class ($item);
		    }
		    
		    $this->_items [] = $item;
		}
		else
		{
			Loader::load ('Zend_Exception');
		    throw new Zend_Exception ('Model create error');
		}
		return $this;
	}
	
	/**
	 * 
	 * @param array|string $_
	 * @desc Добавление нескольких опций к коллекции аналогично
	 * @return Model_Collection
	 */
	public function addOptions ($options)
	{
		if (
        	(is_array ($options) && !empty ($options ['name'])) ||
        	!is_array ($options)
        )
        {
            $options = func_get_args ();
        }
	    
	    foreach ($options as $option)
	    {
		    $this->_options->add ($option);
	    }
        
		return $this;
	}
	
    /**
     * 
     * @param string $name
     * @return array
     */
	public function column ($name)
	{
	    $result = $this->items ();
    	foreach ($result as &$item)
    	{
    	    $item = $item->field ($name);
    	}
    	return $result;
	}
	
	/**
	 * @return integer
	 */
	public function count ()
	{
		return count ($this->items ());
	}
	
	/**
	 * Имя базового класса (без суффикса "_Collection")
	 * @return string
	 */
	public function className ()
	{
//		return substr (get_class ($this), 0, -strlen('_Collection'));
		return substr (get_class ($this), 0, -11);
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
	 * Удаление всех объектов коллекции
	 */
	public function delete ()
	{
		$items = &$this->items ();
		foreach ($items as $item)
		{
			$item->delete ();
		}
		$this->_items = array ();
	}
	
	/**
	 * Исключает из коллекции элемент с указанным индексом.
	 * @param integer $index
	 * 		Индекс элемента в списке.
	 * @return Model_Collection
	 */
	public function exclude ($index)
	{
		if (is_array ($this->_items) && isset ($this->_items [$index]))
		{
			unset ($this->_items [$index]);
		}
		return $this;
	}
	
	/**
	 * @return Model
	 */
	public function first ()
	{
		if (!is_array ($this->_items))
		{
			$this->load();
		}
		if (empty ($this->_items))
		{
			return null;
		}
		reset ($this->_items);
		return current ($this->_items);
	}
	
	/**
	 * 
	 * @param array $rows
	 * @param boolean $clear
	 * 		Очистить коллекцию перед добавлением
	 * @return Model_Collection
	 */ 
	public function fromArray (array $rows, $clear = true)
	{
		$model = $this->modelName ();
		if ($clear)
		{
			$this->_items = array ();
		}
		$model_manager = IcEngine::$modelManager;
		$kf = $this->keyField ();
		foreach ($rows as $row)
		{
			$key = isset ($row ['id']) ? $row ['id'] : $row [$kf];
			$this->_items [] = $model_manager->get ($model, $key, $row);
		}
		return $this;
	}
	
	/**
	 * 
	 * @param Query $query
	 * @param boolean $clear
	 * 		Очистить коллекцию, перед добавлением
	 * @return Model_Collection
	 */
	public function fromQuery (Query $query, $clear = true)
	{
		$rows = DDS::execute ($query)->getResult ()->asTable ();
		return $this->fromArray ((array) $rows, $clear);
	}

	/**
	 * @return boolean
	 */
	public function getAutojoin ()
	{
	    return $this->_autojoin;
	}

	public function getIterator ()
	{
	    $this->items ();
        return new ArrayIterator ($this->_items);
    }
    
    /**
     * @return Paginator
     */
    public function getPaginator ()
    {
        return $this->_paginator;
    }
    
	/**
	 * Возвращает модель из коллекции
	 * @param integer $index Индекс
	 * @return Model|null
	 */
	public function item ($index)
	{
		if (!is_array ($this->_items))
		{
			$this->load ();
		}
		
	    if ($index < 0)
	    {
	        $index += count ($this->_items);
	    }
		
		return isset ($this->_items [$index]) ? $this->_items [$index] : null;
	}
    
	/**
	 * @return array <Model>
	 */
	public function &items ()
	{
		if (!is_array ($this->_items))
		{
			$this->load ();
		}
		return $this->_items;
	}
	
	/**
	 * @return boolean
	 */
	public function isEmpty ()
	{
	    if (!is_array ($this->_items))
	    {
	        $this->load ();
	    }
		return empty ($this->_items);
	}
	
	/**
	 * Проверяет, чтобы модель была приджойнен хотя бы к одному элементу 
	 * коллекции.
	 * @param Model $model
	 * 		Модель.
	 * @return boolean
	 */
	public function isJoinedSome (Model $model)
	{
	    Loader::load ('Helper_Link');
	    foreach ($this as $item)
	    {
	        if (Helper_Link::wereLinked ($item, $model))
	        {
	            return true;
	        }
	    }
	    
	    return false;
	}
	
	/**
	 * Проверяет, чтобы модель была приджойнена ко всем элеметам коллекции.
	 * @param Model $model
	 * 		Модель.
	 * @return boolean
	 */
	public function isJoinedAll (Model $model)
	{
	    Loader::load ('Helper_Link');
	    foreach ($this as $item)
	    {
	        if (!Helper_Link::wereLinked ($item, $model))
	        {
	            return false;
	        }
	    }
	    
	    return true;
	}
	
	/**
	 * Приджойнить модель ко всем элементам коллекции
	 * @param Model $model
	 * @return Model_Collection
	 */
	public function join (Model $model)
	{
	    Loader::load ('Helper_Link');
	    foreach ($this as $item)
	    {
	        Helper_Link::link ($item, $model);
	    }
	    return $this;
	}
	
	/**
	 * Имя ключевого поля
	 * @return string
	 */
	public function keyField ()
	{
		return IcEngine::$modelManager->modelScheme ()->keyField (
		    $this->modelName ());
	}
	
	/**
	 * @return Model
	 */
	public function last ()
	{
		if (!is_array ($this->_items))
		{
			$this->load ();
		}
		if (empty ($this->_items))
		{
			return null;
		}
		end ($this->_items);
		return current ($this->_items);
	}
	
	/**
	 * @return Query
	 */
	public function lastQuery ()
	{
	    if (!$this->_lastQuery)
	    {
	        $this->load ();
	    }
	    return $this->_lastQuery;
	}
	
	/**
	 * Загрузка данных 
	 * @return Model_Collection
	 */
	public function load ($colums = array ())
	{
	    $key_field = $this->keyField ();
		$query = clone $this->query ();
		
		if (!$colums)
		{
			if ($this->_select)
			{
				$query->select ($this->_select);
			}
			else
			{
				$query->select ($this->table () . '.*');
			}
		}
		else
		{
			$query->select ((array) $colums);
		}
		
		$query->select (array ($this->table () => $key_field));
		
		$query->from ($this->modelName ());
		
		foreach ($this->_where as $where)
		{
		    if (count ($where) > 1)
		    {
		        $query->where ($where [0], $where [1]);
		    }
		    else
		    {
			    $query->where ($where [0]);
		    }
		}
		
		if ($this->_paginator)
		{
		    $query->calcFoundRows ();
		    $query->limit (
		        $this->_paginator->pageLimit,
		        $this->_paginator->offset ());
		}
		
		$this->_options->executeBefore ($this, $query);
		$this->_lastQuery = $query;
		$this->_queryResult = DDS::execute ($query)->getResult ();
		$this->_items = $this->_queryResult->asTable ();
		
		if ($this->_paginator)
		{
		    $this->_paginator->fullCount = $this->queryResult ()->foundRows ();
		}
		
		$model = $this->modelName ();
		
		$mmanager = IcEngine::$modelManager;
		
		foreach ($this->_items as &$item)
		{
			$key = $item [$key_field];
			$item = $this->_autojoin ? 
			    $mmanager->get ($model, $key, $item) :
			    $mmanager->forced ()->get ($model, $key, $item);
		}
		
		$this->_options->executeAfter ($this, $query);
		
		return $this;
	}
	
	/**
	 * Для каждого объекта коллекции будет вызвана функция $function 
	 * и результат выполнения записан в данные объекта под именем $data
	 * 
	 * @param function $function
	 * @param string $data
	 */
	public function mapToData ($function, $data)
	{
		$items = &$this->items();
		foreach ($items as $item)
		{
			$item->data ($data, call_user_func ($function, $item));
		}
	}
	
	/**
	 * Название модели (без суффикса "_Collection")
	 * 
	 * @return string
	 */
	public function modelName ()
	{
//		return substr (get_class ($this), 0, -strlen('_Collection'));
		return substr (get_class ($this), 0, -11);
	}
	
    public function offsetSet ($offset, $value)
    {
        if (is_null ($offset))
        {
            $this->_items [] = $value;
        }
        else
        {
            $this->_items [$offset] = $value;
        }
    }
    
    public function offsetExists ($offset)
    {
        return isset ($this->_items [$offset]);
    }
    
    public function offsetUnset ($offset)
    {
        unset ($this->_items [$offset]);
    }
    
    public function offsetGet ($offset)
    {
        return 
        	isset ($this->_items [$offset]) ?
        	$this->_items [$offset] : null;
    }
    
	/**
	 * @return Query
	 */
	public function query ()
	{
		if (!$this->_query)
		{
			$this->_query = Query::instance ();
		}
		return $this->_query;
	}
	
	/**
	 * @return Query_Result
	 */
	public function queryResult ()
	{
	    if (!$this->_queryResult)
	    {
	        $this->load ();
	    }
	    return $this->_queryResult;
	}

    /**
     * Очищает коллекцию.
     */
	public function reset ()
	{
		$this->items = array ();
	}
    
    /**
     * @return Model_Collection
     */
    public function reverse ()
    {
        $this->_items = array_reverse ($this->_items);
        return $this;
    }
    
    /**
     * Устанавливает автоджойн моделей для создаваемых объектов.
     * 
     * @param boolean $value
     * @return Model_Collection
     */
    public function setAutojoin ($value)
    {
        $this->_autojoin = $value;
        return $this;
    }
    
	/**
	 * 
	 * @param Paginator $paginator
	 */
	public function setPaginator (Paginator $paginator)
	{
		$this->_paginator = $paginator;
		$this->_paginator->fullCount = 0;
	}
    
    /**
     * @return Model|null
     */
    public function shift ()
    {
    	return array_shift ($this->_items);
    }
	
	/**
	 * 
	 * @param string|array $columns
	 * @return Model_Collection
	 */
	public function select ($columns)
	{
		if (!is_array ($columns))
		{
			$columns = func_get_args ();
		}
		
		$this->_select = array_merge ($this->_select, $columns);
		return $this;
	}
	
	/**
	 * 
	 * @param integer $offset
	 * @param integer $length
	 * @return Model_Collection
	 */
	public function slice ($offset, $length)
	{
		$this->_items = array_slice ($this->_items, $offset, $length);
		return $this;
	}
	
	/**
	 * Сортировка коллекции.
	 * @param string $fields
	 * 		Список полей для сортировки.
	 * 		Примеры: "id", "id DESC", "id, rating DESC".
	 * 		
	 * @return Sn_Collection_Abstract
	 */
	public function sort ($fields)
	{
	    $items = &$this->items ();
		Loader::load ('Helper_Array');
		Helper_Array::mosort ($items, $fields);
		return $this;
	}
	
	/**
	 * Упорядочивание списка для вывода дерева по полю parentId
	 * 
	 * @param boolean $include_unparented
	 * 		Оставить элементы без предка.
	 * 		Если false, элементы будут исключены из списка.
	 * @return Model_Collection
	 */
	public function sortByParent ($include_unparented = true)
	{
		$list = &$this->items ();
		$parents = array ();
		$child_of = 0;
		$result = array ();
		$i = 0;
		$index = array (0 => 0);
		$full_index = array (-1 => '');
		
		do {
			
			$finish = true;
			
			for ($i = 0; $i < count ($list); $i++)
			{
				if ($list [$i]->parentId == $child_of)
				{
					//
					if (!isset ($index[count ($parents)]))
					{
						$index [count ($parents)] = 1;
					}
					else
					{
						$index [count ($parents)]++;
					}
					
					$n = count ($result);
					$result[$n] = $list [$i];
					$result[$n]->data ('level', count ($parents));
					$result[$n]->data ('index', $index [count ($parents)]);
					$parents_count = count ($parents);
					if ($parents_count > 0)
					{
					    $full_index = $full_index [$parents_count - 1] . $index [count ($parents)];
					}
					else
					{
					    $full_index = (string) $index [count ($parents)];
					}
					$result[$n]->data ('full_index', $full_index);
					$result[$n]->data ('broken_parent', false);
					
					$full_index [$parents_count] = $full_index . '.';

					array_push ($parents, $child_of);
					$child_of = $list[$i]->key ();

					for ($j = $i; $j < count ($list) - 1; $j++)
					{
						$list [$j] = $list [$j + 1];
					}
					array_pop ($list);
					$finish = false;
					break;
				}
			}
			
			// Элементы с неверно указанным предком
			if ($finish && count ($parents) > 0)
			{
				$index [count ($parents)] = 0;
				$child_of = array_pop ($parents);
				$finish = false;
			}
			
		} while (!$finish);
		
		$this->_items = $result;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function table ()
	{
		return $this->modelName ();
	}
	
	/**
	 * Обновление всех элементов коллекции
	 * @param array $data
	 */
	public function update (array $data)
	{
	   $items = &$this->items ();
	   foreach ($items as $item)
	   {
	       $item->update ($data);
	   } 
	}
	
	/**
	 * Добавление условия отбора
	 * 
	 * @param string $condition
	 * @param string $value [optional]
	 * @return Model_Collection
	 */
	public function where ($condition)
	{
		$this->_where [] = func_get_args ();//array ($condition, $value);
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getItems ()
	{
		return $this->_items;
	}
	
	/**
	 * 
	 * @param array $items
	 * @return Model_Collection
	 */
	public function setItems ($items)
	{
		$this->_items = (array) $items;
		return $this;
	}
	
}