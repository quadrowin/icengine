<?php

/**
 * Базовый класс коллекции моделей
 *
 * @author goorus, morph
 *
 */
class Model_Collection implements ArrayAccess, IteratorAggregate, Countable
{
	/**
	 * Связанные данные
     *
	 * @var array
	 */
	protected $data = array ();

	/**
	 * Элементы коллекции
     *
	 * @var array
	 */
	protected $items;

    /**
	 * Итератор коллекции
	 *
	 * @var Model_Collection_Iterator
	 */
	protected $iterator;

    /**
	 * Последний выполненный запрос
     *
	 * @var Query_Abstract
	 */
	protected $lastQuery;

	/**
	 * Опции
     *
	 * @var Model_Collection_Option_Collection
	 */
	protected $options;

	/**
	 * Текущий паджинатор
     *
	 * @var Paginator
	 */
	protected $paginator;

	/**
	 * Текущий запрос
     *
	 * @var Query_Abstract
	 */
	protected $query;

	/**
	 * Результат последнего выполненного запроса
     *
	 * @var Query_Result
	 */
	protected $queryResult;

	/**
	 * Преобразование коллекции к массиву
     *
	 * @return array
	 */
	public function __toArray()
	{
		$result = array(
			'class'	=> get_class($this),
			'items'	=> array(),
			'data'	=> $this->data
		);
		foreach ($this as $item) {
			$result['items'][] = $item->__toArray();
		}
		return $result;
	}

	/**
	 * Добавить модель в коллекцию
     *
	 * @param Model|Model_Collection|array $item
	 * @return Model_Collection
	 * @throws Zend_Exception
	 */
	public function add($item)
	{
		if ($item instanceof Model) {
			$this->items[] = $item;
		} elseif ($item instanceof Model_Collection) {
			foreach ($item as $model) {
				$this->items[] = $model;
			}
		} else {
			throw new Model_Exception('Model add error');
		}
		return $this;
	}

	/**
	 * Добавление нескольких опций к коллекции аналогично
     *
	 * @param $_
	 * @return Model_Collection Эта коллекция
	 */
	public function addOptions()
	{
		$options = func_get_args();
		foreach ($options as $option) {
			$this->options[] = $option;
		}
		return $this;
	}

	/**
	 * Клонировать модель
     *
	 * @param Model_Collection $source
	 * @return Model_Collection
	 */
	public function assign(Model_Collection $source)
    {
		$this->data($source->data());
        $this->setItems($source->items());
		return $this;
	}

    /**
     * Действия до загрузки коллекции
     */
    protected function beforeLoad()
    {
        $keyField = $this->keyField();
		$query = $this->query();
        $args = func_get_args();
		$modelName = $this->table();
        if (!$args || (count($args) == 1 && empty($args[0]))) {
			$query->select($modelName . '.*');
            $query->select(array($modelName => $keyField));
		} else {
            foreach ($args as $arg) {
                $query->select($arg);
            }
            if (!in_array($keyField, $args)) {
                $query->select(array($modelName => $keyField));
            }
		}
		$query->from($modelName);
		if ($this->paginator) {
			$query->calcFoundRows();
			$query->limit(
				$this->paginator->pageLimit,
				$this->paginator->offset()
            );
		}
		$schemeOptions = Model_Scheme::modelOptions($modelName);
		if ($schemeOptions) {
			$this->addOptions($schemeOptions);
		}
        Model_Collection_Option_Manager::executeBefore($this, $this->options);
		$this->lastQuery = $query;
    }

	/**
	 * Имя базового класса (без суффикса "_Collection")
     *
	 * @return string
	 */
	public function className()
	{
		return substr(get_class($this), 0, -strlen('_Collection'));
	}

	/**
	 * Получить значение поля для всех моделей коллеции
     *
	 * @param string|array $name
	 * @return array
	 */
	public function column($name)
	{
        return Helper_Array::column($this->items(), $name);
	}

	/**
	 * Количество моделей коллеции
     *
	 * @return integer
	 */
	public function count()
	{
		return count($this->items());
	}

	/**
	 * Получить текущий итератор коллекции
	 *
	 * @return Model_Collection_Iterator
	 */
	public function currentIterator()
	{
		return $this->iterator;
	}

	/**
	 * Устанавливает или получает связанные данные объекта
	 *
     * @param string $key [optional] Ключ
	 * @param mixed $value [optional]
	 * 		Значение (не обязательно)
	 * @return mixed
	 * 		Текущее значение
	 */
	public function data($key = null, $value = null)
	{
        $numArgs = func_num_args();
        if (!$numArgs) {
            return $this->data;
        } elseif ($numArgs == 1) {
            if (is_array($key)) {
                $this->data = array_merge($this->data, $key);
            } else {
                return isset($this->data[$key]) ? $this->data[$key] : null;
            }
        } else {
            $this->data[$key] = $value;
        }
	}

	/**
	 * @desc Удаление всех объектов коллекции
	 */
	public function delete()
	{
		$items = &$this->items();
		foreach ($items as $item) {
            $query = Query::instance()
                ->delete()
                ->from($item->table())
                ->where($item->keyField(), $item->key());
            Unit_Of_Work::push($query);
		}
        Unit_Of_Work::flush();
		$this->items = array();
	}

	/**
	 * Исключает из коллекции элемент с указанным индексом
     *
	 * @param integer $index Индекс элемента в списке.
	 * @return Model_Collection
	 */
	public function exclude($index)
	{
        if (!is_array($this->items)) {
            $this->items();
        }
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
        }
		return $this;
	}

	/**
	 * Фильтрация. Возвращает экземпляр новой коллекции
     *
	 * @param array $fields
	 * @return Model_Collection
	 */
	public function filter($fields)
	{
        $modelName = $this->modelName();
        $keyField = Model_Scheme::keyField($modelName);
		$collection = Model_Collection_Manager::create($modelName)
			->reset();
        $result = Helper_Array::filter($this->items(), $fields);
		if ($result) {
            $ids = Helper_Array::column($result, $keyField);
            foreach ($ids as $id) {
                foreach ($this->items as $model) {
                    if ($id != $model[$keyField]) {
                        continue;
                    }
                    $collection->add($model);
                }
            }
        }
        return $collection;
	}

	/**
	 * Возвращает первый элемент коллекции
     *
	 * @return Model
	 */
	public function &first()
	{
        $items = &$this->items();
        if (!$items) {
            return null;
        }
        $first = reset($items);
        return $first;
	}

	/**
	 * Получить коллекцию опшинов
     *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Возвращает элементы коллекции, не загружая ее
     *
     * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator()
	{
		$this->items();
		return new ArrayIterator($this->items);
	}

	/**
	 * Вернуть текущий пагинатор
     *
	 * @return Paginator
	 */
	public function getPaginator()
	{
		return $this->paginator;
	}

	/**
	 * Возвращает модель из коллекции
     *
	 * @param integer $index Индекс
	 * @return Model|null
	 */
	public function &item($index)
	{
		if (!is_array($this->items)) {
			$this->items();
		}
		if ($index < 0) {
			$index += count($this->items);
		}
		return isset($this->items[$index]) ? $this->items[$index] : null;
	}

	/**
	 * Получить элементы модели
     *
	 * @return array
	 */
	public function &items()
	{
		if (!is_array($this->items))
		{
			$this->load();
		}
		return $this->items;
	}

	/**
	 * Получить итератор коллекции
	 *
	 * @return Model_Collection_Iterator
	 */
	public function iterator($isFactory = false)
	{
		Loader::load('Model_Collection_Iterator');
		if (!$this->iterator) {
			$this->iterator = new Model_Collection_Iterator($this, $isFactory);
		}
		if (!is_array($this->items))
		{
			$this->load();
		}
		return $this->iterator;
	}

	/**
	 * Имя ключевого поля
     *
	 * @return string
	 */
	public function keyField()
	{
		return Model_Scheme::keyField($this->modelName());
	}

	/**
	 * Получить последнюю модель коллекции
     *
	 * @return Model
	 */
	public function &last()
	{
		$items = &$this->items();
        if (!$items) {
            return null;
        }
        $last = end($items);
        return $last;
	}

	/**
	 * Последний выполенный запрос коллекции.
	 * Если запрос еще не сформирован, запрос будет сформирован и коллекция
	 * будет загружена.
     *
	 * @return Query Зарос коллекции.
	 */
	public function lastQuery()
	{
		if (!$this->lastQuery) {
			$this->load();
		}
		return $this->lastQuery;
	}

	/**
	 * Загрузка данных
     *
	 * @return Model_Collection
	 */
	public function load($columns = array())
	{
		$this->beforeLoad($columns);
        $query = $this->lastQuery;
		Model_Collection_Manager::load($this, $query);
        Model_Collection_Option_Manager::executeAfter($this, $this->options);
		if ($this->paginator) {
			$this->paginator->fullCount = $this->data['foundRows'];
		}
		return $this;
	}

	/**
	 * Для каждого объекта коллекции будет вызвана функция $function
	 * и результат выполнения записан в данные объекта под именем $data
	 *
	 * @param function $function
	 * @param string $data
	 */
	public function mapToData($function, $data)
	{
		$items = &$this->items();
		foreach ($items as $item) {
			$item->data($data, call_user_func($function, $item));
		}
	}

	/**
	 * Название модели (без суффикса "_Collection")
     *
	 * @return string
	 */
	public function modelName()
	{
		return $this->table();
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->items[] = $value;
		} else {
			$this->items[$offset] = $value;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return isset($this->items[$offset]);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset)
	{
		unset($this->items[$offset]);
	}

	/**
	 * @param offset
	 * @return Model
	 */
	public function offsetGet($offset)
	{
		return $this->item($offset);
	}

	/**
	 * Возвращает текущий запрос
     *
	 * @return Query_Abstract
	 */
	public function query()
	{
		if (!$this->query) {
			$this->query = Query::factory('Select');
		}
		return $this->query;
	}

	/**
	 * Получить результат запроса коллекции
     *
	 * @return Query_Result
	 */
	public function queryResult($result = null)
	{
		if ($result) {
			$this->queryResult = $result;
		} else {
			if (!$this->queryResult) {
				$this->load();
			}
			return $this->queryResult;
		}
	}

    /**
     * Получить чистые данные
     *
     * @param array $columns
     * @return array
     */
    public function raw($columns = array())
    {
        $result = array();
        if ($this->items) {
            if (is_array($this->items[0])) {
                $result = Helper_Array::column($this->items, $columns);
            } else {
                $fullResult = array();
                foreach ($this->items as $item) {
                    $fullResult[] = $item->asRow();
                }
                $result = Helper_Array::column($fullResult, $columns);
            }
        } else {
            if ($columns) {
                call_user_func_array(array($this, 'beforeLoad'), $columns);
            } else {
                $this->beforeLoad(array());
            }
            $pack = Model_Collection_Manager::callDelegee(
                $this, $this->lastQuery
            );
            if ($pack) {
                $this->items = $pack['items'];
            }
            Model_Collection_Option_Manager::executeAfter($this, $this->options);
            if ($this->paginator) {
                $this->paginator->fullCount = $this->data('foundRows');
            }
            $result = Helper_Array::column($this->items, $columns);
        }
        return (array) $result;
    }

	/**
	 * Очищает коллекцию
     *
	 * @return Model_Collection Эта коллекция.
	 */
	public function reset()
	{
		$this->items = array();
		return $this;
	}

	/**
	 * Сбросить итератор коллекции
	 */
	public function resetIterator()
	{
		$this->iterator = null;
	}

	/**
	 * Реверсировать последовательность моделей коллекции
     *
	 * @return Model_Collection
	 */
	public function reverse()
	{
		$this->items = array_reverse($this->items());
        return $this;
	}

	/**
	 * Меняет поля модели
     *
	 * @param mixed (string,sting|array<string>) $fields
	 * @return Model_Collection
	 */
	public function set()
	{
		$args = func_get_args();
		if (count($args) == 2) {
			$args = array($args[0] => $args[1]);
		}
		foreach ($this as $item) {
			foreach ((array) $args as $field => $value) {
				$item->field($field, $value);
			}
		}
		return $this;
	}

	/**
	 * Заменить модели коллекции
     *
	 * @param array<Model> $items
	 * @return Model_Collection
	 */
	public function setItems($items)
	{
		$this->items = $items;
		return $this;
	}

	/**
	 * Изменить паджинатор коллекции
     *
	 * @param Paginator $paginator
	 */
	public function setPaginator($paginator)
	{
		$this->paginator = $paginator;
		if ($paginator) {
			$this->_paginator->fullCount = is_array($this->items)
                ? count ($this->items) : 0;
		}
	}

	/**
	 * Подмена запроса коллекции
     *
	 * @param Query_Abstract $query Новый запрос
	 * @return Model_Collection Эта коллекция
	 */
	public function setQuery(Query_Abstract $query)
	{
		$this->query = $query;
	}

	/**
	 * Вернуть первый элемент коллекции, удалив его из коллекции
     *
	 * @return Model|null
	 */
	public function shift()
	{
		return array_shift($this->items);
	}

	/**
	 * Перемешивает элементы коллекции в случайном порядке
     *
	 * @return Model_Collection
	 */
	public function shuffle()
	{
		$this->items();
		shuffle($this->items);
		return $this;
	}

	/**
	 * Оставить часть элементов коллекции
     *
	 * @param integer $offset
	 * @param integer $length
	 * @return Model_Collection Эта коллекция.
	 */
	public function slice($offset, $length)
	{
		$this->items();
		$this->items = array_slice($this->items, $offset, $length);
		return $this;
	}

	/**
	 * Сортировка коллекции
     *
	 * @param string $fields Список полей для сортировки.
	 * Например: "id", "id DESC", "id, rating DESC".
	 * @return Model_Collection
	 */
	public function sort()
	{
		$items = &$this->items();
		Helper_Array::mosort($items, implode (',', func_get_args()));
		return $this;
	}

	/**
	 * Имя таблицы по умолчанию для коллекции
     *
	 * @return string
	 */
	public function table ()
	{
		return substr(get_class($this), 0, -strlen('_Collection'));
	}

	/**
	 * Получить колекцию уникальный элементов
     *
	 * @return Model_Collection
	 */
	public function unique()
	{
		$modelName = $this->modelName();
		$keyField = $this->keyField();
		$keys = array_unique($this->column($keyField));
		$collection = Model_Collection_Manager::create($modelName)
			->reset();
        $existsKeys = array();
        foreach ($this->items() as $model) {
            foreach ($keys as $key) {
                if ($key == $model->key() && !in_array($key, $existsKeys)) {
                    $collection->add($model);
                    $existsKeys[] = $key;
                }
            }
        }
        return $collection;
	}

	/**
	 * Обновление всех элементов коллекции
     *
	 * @param array $data
	 */
	public function update(array $data)
	{
		$items = &$this->items();
		foreach ($items as $item) {
			$query = Query::instance()
                ->update($item->table())
                ->values($data)
                ->where($item->keyField(), $item->key());
            Unit_Of_Work::push($query);
		}
        Unit_Of_Work::flush();
	}
}