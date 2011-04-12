<?php
/**
 * 
 * @desc Базовый класс коллекции моделей
 * @author Юрий
 * @package IcEngine
 *
 */
class Model_Collection implements ArrayAccess, IteratorAggregate, Countable 
{
	
	/**
	 * @desc Для создаваемых моделей включен autojoin.
	 * @var boolean
	 */
	protected $_autojoin = true;
	
	/**
	 * @desc Связанные данные
	 * @var array
	 */
	protected $_data = array ();
	
	/**
	 * @desc Элементы коллекции
	 * @var array
	 */
	protected $_items;
	
	/**
	 * @desc Опции
	 * @var Model_Collection_Option_Item_Collection
	 */
	protected $_options;
	
	/**
	 * @desc Текущий паджинатор
	 * @var Paginator
	 */
	protected $_paginator;
	
	/**
	 * @desc Выбираемые поля
	 * @var array
	 */
	protected $_select = array ();
	
	/**
	 * @desc Последний выполненный запрос
	 * @var Query
	 */
	protected $_lastQuery;
	
	/**
	 * @desc Текущий запрос
	 * @var Query
	 */
	protected $_query;
	
	/**
	 * @desc Результат последнего выполненного запроса
	 * @var Query_Result
	 */
	protected $_queryResult;
	
	/**
	 * @desc Условия
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
	 * @desc Добавить модель в коллекцию
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
				$item = Model_Manager::get (
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
			throw new Zend_Exception ('Model add error');
		}
		return $this;
	}
	
	/**
	 * @desc Добавление нескольких опций к коллекции аналогично.
	 * @param array|string $options
	 * @param $_
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
	 * @desc Получить значение поля для всех моделей коллеции
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
	 * @desc Количество моделей коллеции
	 * @return integer
	 */
	public function count ()
	{
		return count ($this->items ());
	}
	
	/**
	 * @desc Имя базового класса (без суффикса "_Collection")
	 * @return string
	 */
	public function className ()
	{
		return substr (get_class ($this), 0, -11);
	}
	
	/**
	 * @desc Устанавливает или получает связанные данные объекта
	 * @param string $key [optional] Ключ
	 * @param mixed $value [optional]
	 * 		Значение (не обязательно)
	 * @return mixed
	 * 		Текущее значение
	 */
	public function data ($key = null)
	{
		if (func_num_args () == 0 || $key === null)
		{
			return $this->_data;
		}
		
		if (func_num_args () == 1)
		{
			if (is_array ($key))
			{
				$this->_data = array_merge (
					$this->_data,
					$key
				);
				return;
			}
			
			return isset ($this->_data [$key]) ? $this->_data [$key] : null;
		}
		
		$this->_data [$key] = func_get_arg (1);
	}
	
	/**
	 * @desc Удаление всех объектов коллекции
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
	 * @desc Получить различные элементы двух коллекций
	 * @param Model_Collection $collection
	 * @return Model_Collection
	 */
	public function diff (Model_Collection $collection)
	{
		$ms = DDS::modelScheme ();
		$model_name = $this->modelName ();
		$kf_this = $ms->keyField ($model_name);
		$kf_collection = $ms->keyField ($collection->modelName ());
		$array_this = $this->column ($kf_this);
		$array_collection = $collection->column ($kf_collection);
		$diff = array_diff ($array_this, $array_collection);
		$result = new Model_Collection ();
		for ($i = 0, $icount = sizeof ($diff); $i < $icount; $i++)
		{
			$result->add (Model_Manager::byKey (
				$model_name,
				$diff [$i]
			));
		}
		return $result;
	}
	
	/**
	 * @desc Исключает из коллекции элемент с указанным индексом.
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
	 * 
	 * @desc Фильтрация. Возвращает экземпляр новой коллекции
	 * @param array<string> $fields
	 * @return Model_Collection
	 */
	public function filter ($fields)
	{
		$collection = new $this;
		$collection->reset ();
		
		foreach ($this as $item)
		{
			$valid = true;
			if ($item->validate ($fields))
		 	{
				$collection->add ($item);
			}
		}
		
		return $collection;
	}
	
	/**
	 * @desc Получить первый элемент коллекции
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
	 * @desc Создать коллекцию из хэша
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
	 * @desc Создать коллекцию из запроса
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
	 * @desc Включен ли автоджоин
	 * @return boolean
	 */
	public function getAutojoin ()
	{
		return $this->_autojoin;
	}
	
	/**
	 * 
	 * @desc Получить коллекцию опшинов
	 * @return Model_Collection_Option_Item_Collection
	 */
	public function getOptions ()
	{
		return $this->_options;
	}
	
	/**
	 * @see items 
	 */
	public function getItems ()
	{
		return $this->_items;
	}

	/**
	 * (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator ()
	{
		$this->items ();
		return new ArrayIterator ($this->_items);
	}
	
	/**
	 * @desc Вернуть текущий пагинатор
	 * @return Paginator
	 */
	public function getPaginator ()
	{
		return $this->_paginator;
	}
	
	/**
	 * 
	 * @desc Ищет в коллекции эквивалентную заданой модель, и,
	 * если находит, то возвращает ее
	 * @param Model $item
	 * @return null|Model
	 */
	public function has (Model $item)
	{
		foreach ($this as $i)
		{
			if ($i === $item)
			{
				return $i;
			}
		}
	}
	
	/**
	 * @desc Возвращает модель из коллекции
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
	 * @desc Получить элементы модели
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
	 * @desc Пустая ли коллекция
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
	 * @desc Проверяет, чтобы модель была приджойнен хотя бы к одному элементу 
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
	 * @desc Проверяет, чтобы модель была приджойнена ко всем элеметам коллекции.
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
	 * @desc Приджойнить модель ко всем элементам коллекции
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
	 * @desc Имя ключевого поля
	 * @return string
	 */
	public function keyField ()
	{
		return IcEngine::$modelManager->modelScheme ()->keyField (
			$this->modelName ());
	}
	
	/**
	 * @desc Получить последнюю модель коллекции
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
	 * @desc Последний выполенный запрос коллекции
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
	 * @desc Загрузка данных 
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
		
		Loader::load ('Model_Collection_Manager');
		Model_Collection_Manager::load ($this, $query, !$this->_autojoin);

		$this->_options->executeAfter ($this, $query);
		
		if ($this->_paginator)
		{
			$this->_paginator->fullCount = $this->_data ['foundRows'];
		}
		
		return $this;
	}
	
	/**
	 * @desc Для каждого объекта коллекции будет вызвана функция $function 
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
	 * @desc Название модели (без суффикса "_Collection")
	 * @return string
	 */
	public function modelName ()
	{
		return substr (get_class ($this), 0, -11);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
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
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists ($offset)
	{
		return isset ($this->_items [$offset]);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset ($offset)
	{
		unset ($this->_items [$offset]);
	}
	
	/**
	 * @param offset
	 * @return Model
	 */
	public function offsetGet ($offset)
	{
		return $this->item ($offset);
	}
	
	/**
	 * @desc Возвращает текущий запрос.
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
	 * @desc Получить результат запроса коллекции
	 * @return Query_Result
	 */
	public function queryResult ($result = null)
	{
		if ($result)
		{
			$this->_queryResult = $result;
		}
		else 
		{
			if (!$this->_queryResult)
			{
				$this->load ();
			}
			return $this->_queryResult;
		}
	}
	
	/**
	 * 
	 * @desc Удаляет опшин по имени
	 * @param string $name
	 */
	public function removeOption ($name)
	{
		$this->_options->remove ($name);
	}
	
	/**
	 * @desc Очищает коллекцию.
	 */
	public function reset ()
	{
		$this->_items = array ();
	}
	
	/**
	 * @desc Реверсировать последовательность моделей коллекции
	 * @return Model_Collection
	 */
	public function reverse ()
	{
		$this->_items = array_reverse ($this->_items);
		return $this;
	}
	
	/**
	 * 
	 * @desc Сохраняет модели коллекции
	 * @return Model_Collection
	 */
	public function save ()
	{
		foreach ($this as $item)
		{
			$item->save ();
		}
		return $this;
	}
	
	/**
	 * @desc Устанавливает автоджойн моделей для создаваемых объектов.
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
	 * @desc Заменить модели коллекции
	 * @param array<Model> $items
	 * @return Model_Collection
	 */
	public function setItems ($items)
	{
		$this->_items = $items;
		return $this;
	}
	
	/**
	 * 
	 * @desc Изменить опшин
	 * @param unknown_type $option
	 */
	public function setOption ($option)
	{
		$this->_options->setOption ($option);
	}
	
	/**
	 * @desc Изменить паджинатор коллекции
	 * @param Paginator $paginator
	 */
	public function setPaginator (Paginator $paginator)
	{
		$this->_paginator = $paginator;
		$this->_paginator->fullCount = 
			is_array($this->_items) ? 
				count ($this->_items) : 
				0;
	}
	
	/**
	 * @desc Подмена запроса коллекции.
	 * @param Query $query Новый запрос
	 * @return Model_Collection Эта коллекция
	 */
	public function setQuery (Query $query)
	{
		$this->_query = $query;
	}
	
	/**
	 * @desc Вернуть первый элемент коллекции, удалив его из коллекции
	 * @return Model|null
	 */
	public function shift ()
	{
		return array_shift ($this->_items);
	}
	
	/**
	 * 
	 * @desc Установить select-часть запроса коллекции
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
	 * @desc Перемешивает элементы коллекции
	 * @return Model_Collection
	 */
	public function shuffle ()
	{
		$this->items ();
		shuffle ($this->_items);
		return $this;
	}
	
	/**
	 * 
	 * @desc Меняет поля модели
	 * @param mixed (string,sting|array<string>) $fields
	 * @return Model_Collection
	 */
	public function set ($fields)
	{
		$args = func_get_args ();
		if (count ($args) == 2)
		{
			$args = array ($args [0] => $args [1]);
		}
		foreach ($this as $item)
		{
			foreach ((array) $args as $field=>$value)
			{
				$item->field ($field, $value);
			}
		}
		return $this;
	}
	
	/**
	 * @desc Оставить часть элементов коллекции
	 * @param integer $offset
	 * @param integer $length
	 * @return Model_Collection
	 */
	public function slice ($offset, $length)
	{
		$this->items ();
		$this->_items = array_slice ($this->_items, $offset, $length);
		return $this;
	}
	
	/**
	 * @desc Сортировка коллекции.
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
		$fields = (array) $fields;
		for ($i = 0, $icount = sizeof ($fields); $i < $icount; $i++)
		{
			Helper_Array::mosort ($items, $fields [$i]);
		}
		return $this;
	}
	
	/**
	 * @desc Упорядочивание списка для вывода дерева по полю parentId
	 * 
	 * @param boolean $include_unparented
	 * 		Оставить элементы без предка.
	 * 		Если false, элементы будут исключены из списка.
	 * @return Model_Collection
	 */
	public function sortByParent ($include_unparented = true)
	{
		$list = &$this->items ();
		
		if (empty ($list))
		{
			// Список пуст
			return $this;
		}
		
		$parents = array ();
		$child_of = $list [0]->parentRootKey ();
		$result = array ();
		$i = 0;
		$index = array (0 => 0);
		$full_index = array (-1 => '');
		
		do {
			
			$finish = true;
			
			for ($i = 0; $i < count ($list); $i++)
			{
				if ($list [$i]->parentKey () == $child_of)
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
	 * @desc Имя таблицы по умолчанию для коллекции
	 * @return string
	 */
	public function table ()
	{
		return $this->modelName ();
	}
	
	/**
	 * @desc Обновление всех элементов коллекции
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
	 * @desc Добавление условия отбора
	 * 
	 * @param string $condition
	 * @param string $value [optional]
	 * @return Model_Collection
	 */
	public function where ($condition)
	{
		$this->_where [] = func_get_args ();
		return $this;
	}
}
