<?php
/**
 *
 * @desc Базовый класс коллекции моделей
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Model_Collection implements ArrayAccess, IteratorAggregate, Countable
{
	/**
	 * @desc Клонировать дату
	 * @var integer
	 */
	const ASSIGN_DATA 		= 'Data';

	/**
	 * @desc Клонировать фильтры
	 * @var integer
	 */
	const ASSIGN_FILTERS 	= 'Filters';

	/**
	 * @desc Клонировать модели
	 * @var integer
	 */
	const ASSIGN_MODELS 	= 'Models';

	/**
	 * @desc Клонировать опшинсы
	 * @var integer
	 */
	const ASSIGN_OPTIONS 	= 'Options';

	/**
	 * @desc Клонировать пагинатор
	 * @var integer
	 */
	const ASSIGN_PAGINATOR 	= 'Paginator';

	/**
	 * @desc Клонировать запрос
	 * @var integer
	 */
	const ASSIGN_QUERY 		= 'Query';

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
	 * @var Model_Collection_Option_Collection
	 */
	protected $_options;

	/**
	 * @desc Текущий паджинатор
	 * @var Paginator
	 */
	protected $_paginator;

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

	public static $DIFF_EDIT_ADD = 'added';

	public static $DIFF_EDIT_NO = 'not_changed';

	public static $DIFF_EDIT_DEL = 'removed';

	/**
	 * @desc Создает и возвращает коллекцию моделей.
	 * Так же подключает связанный класс модели.
	 */
	public function __construct ()
	{
		Loader::load ('Model_Option_Collection');
		$this->_options = new Model_Option_Collection ($this);
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
			var_dump ($item);
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Model add error');
		}
		return $this;
	}

	/**
	 * @desc Добавление одного или нескольких фильтров.
	 * @param Data_Transport $data Транспорт входных данных контроллера.
	 * @param string $filter Название фильтра.
	 * @return Model_Collection
	 */
	public function addFilters (Data_Transport $data, $filter)
	{
		Loader::load ('Model_Collection_Filter_Manager');
		$arg_count = func_num_args ();
		for ($i = 1; $i < $arg_count; ++$i)
		{
			$filter = func_get_arg ($i);
			$p = strpos ($filter, '::');
			$filter =
				$p
				?
					substr ($filter, 0, $p) .
					'_Collection_Filter_' .
					substr ($filter, $p + 2)
				:
					$this->modelName () .
					'_Collection_Filter_' .
					$filter;

			Model_Collection_Filter_Manager::get (
				$filter
			)->filter ($this, $data);
		}
		return $this;
	}

	/**
	 * @desc Добавление нескольких опций к коллекции аналогично.
	 * @param array|string $options
	 * @param $_
	 * @return Model_Collection Эта коллекция
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
	 * @desc Клонировать модель
	 * @param Model_Collection $source
	 * @param array $flags
	 * @return Model_Collection
	 */
	public function assign (Model_Collection $source, array $flags = array ())
	{
		if (!$flags)
		{
			$flags = array (
				self::ASSIGN_DATA,
				self::ASSIGN_FILTERS,
				self::ASSIGN_MODELS,
				self::ASSIGN_OPTIONS,
				self::ASSIGN_PAGINATOR,
				self::ASSIGN_QUERY
			);
		}

		for ($i = 0, $icount = sizeof ($flags); $i < $icount; $i++)
		{
			$method_name = 'assign' . $flags [$i];

			if (is_callable (array ($this, $method_name)))
			{
				$this->$method_name ($source);
			}
		}

		return $this;
	}

	/**
	 * @desc Клонировать дату коллекции
	 * @param Model_Collection $source
	 */
	public function assignData (Model_Collection $source)
	{
		$this->data ($source->data ());
	}

	/**
	 * @desc Клонировать фильтры
	 * @param Model_Collection $source
	 */
	public function assignFilters (Model_Collection $source)
	{

	}

	/**
	 * @desc Клонировать модели
	 * @param Model_Collection $source
	 */
	public function assignModels (Model_Collection $source)
	{
		$this->setItems ($source->items ());
	}

	/**
	 * @desc Клонировать опшины
	 * @param Model_Collection $source
	 */
	public function assignOptions (Model_Collection $source)
	{
		$this
			->getOptions ()
			->setItems (
				$source
					->getOptions ()
					->getItems ()
			);
	}

	/**
	 * @desc Клонировать пагинатор
	 * @param Model_Collection $source
	 */
	public function assignPaginator (Model_Collection $source)
	{
		$paginator = $source->getPaginator ();
		if ($paginator)
		{
			$this
				->setPaginator (
					clone $paginator
				);
		}
	}

	/**
	 * @desc Клонировать запрос
	 * @param Model_Collection $source
	 */
	public function assignQuery (Model_Collection $source)
	{
		$query = $source->query ();
		$this
			->setQuery (
				clone $query
			);
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
	 * @desc Получить различные элементы двух коллекцийю
	 * @param Model_Collection $collection
	 * @return Model_Collection
	 */
	public function diff (Model_Collection $collection)
	{
		$model_name = $this->modelName ();

		$kf_this = Model_Scheme::keyField ($model_name);

		$kf_collection = Model_Scheme::keyField ($collection->modelName ());

		$array_this = $this->column ($kf_this);

		$array_collection = $collection->column ($kf_collection);

		$diff = array_diff ($array_this, $array_collection);

		$result = new Model_Collection ();
		$result->reset ();

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
	 * @desc Получить массив, содержащий добавленные и удаленные модели
	 * @param Model_Collection $collection
	 * @return array
	 */
    public function diffEdit($collection, $fields = array())
    {
		$collection_add = Model_Collection_Manager::create(
			$collection->modelName()
		);

		$collection_add->reset();

	$collection_no = Model_Collection_Manager::create($collection->modelName());
	$collection_no->reset();

		$collection_del = Model_Collection_Manager::create(
			$collection->modelName()
		);
		$collection_del->reset();

		$collection_count = $this->count();

		foreach ($collection as $model)
		{
	    $diff_model = $this->hasByFields($model, $fields);

	    if ($diff_model)
			{
		$collection_no->add($diff_model);
				$collection_count--;
			}
			else
			{
				$collection_add->add($model);
			}
		}

		// если $collection_count не 0, делаем вывод, что есть удаленные модели
		if ($collection_count)
		{
			foreach ($this as $model)
			{
				if (!$collection->hasByFields ($model, $fields))
				{
					$collection_del->add($model);
				}
			}
		}

		return array(
			self::$DIFF_EDIT_ADD => $collection_add,
	    self::$DIFF_EDIT_NO => $collection_no,
			self::$DIFF_EDIT_DEL => $collection_del
		);
    }

	/**
	 * @desc Исключает из коллекции элемент с указанным индексом.
	 * @param integer $index Индекс элемента в списке.
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
	 * @param array $fields
	 * @return Model_Collection
	 */
	public function filter ($fields)
	{
		$collection = new $this;
		$collection->reset ();

		$first_fields = array ();

		foreach ($fields as $field => $value)
		{
			$s = substr ($field, -2, 2);

			if ($s [0] == '=' || ctype_alnum ($s))
			{
				unset ($fields [$field]);

				$field = rtrim ($field, '=');
				$field = str_replace (' ', '', $field);

				$first_fields [$field] = $value;
			}
		}

		foreach ($this as $item)
		{
			$valid = true;
			if (!$first_fields || $item->validate ($first_fields))
		 	{
				if ($fields)
				{
					foreach ($fields as $field => $value)
					{
						$field = str_replace (' ', '', $field);

						$s = substr ($field, -2, 2);

						if (ctype_alnum ($s [0]))
						{
							$s = $s [1];
						}

						$field = substr ($field, 0, -1 * strlen ($s));

						switch ($s)
						{
							case '>':
								$valid = $item->$field > $value;
								break;
							case '>=':
								$valid = $item->$field >= $value;
								break;
							case '<': $valid = $item->$field < $value;
								break;
							case '<=': $valid = $item->$field <= $value;
								break;
							case '!=': $valid = $item->$field != $value;
								break;
						}

						if (!$valid)
						{
							break;
						}
					}
				}

				if ($valid)
				{
					$collection->add ($item);
				}
			}
		}

		return $collection;
	}

	/**
	 * @desc Подсчет количества моделей в коллекции, соответсвующих условию.
	 * @param type $fields
	 * @return integer Количество моделей, соответвующих фильтру.
	 */
	public function filterGetCount ($fields)
	{
		$count = 0;
		foreach ($this as $item)
		{
			if ($item->validate ($fields))
		 	{
				++$count;
			}
		}
		return $count;
	}

	/**
	 * @desc Возвращает первую модель, соответсвующую фильтру.
	 * @param array $fields
	 * @return Model|null
	 */
	public function filterGetFirst ($fields)
	{
		foreach ($this as $item)
		{
			if ($item->validate ($fields))
			{
				return $item;
			}
		}
		return null;
	}

	/**
	 * @desc Фильтрация. Возвращает экземпляр новой коллекции.
	 * Проверяет существование в моделях фильтруемых полей, в случае
	 * отсутствия ошибки не возникает.
	 * @param string $field
	 * @param string $value
	 * @return Model_Collection
	 */
	public function filterExt ($field, $value)
	{
		$collection = new $this;
		$collection->reset ();

		foreach ($this as $item)
		{
			if ($item->hasField ($field) && $item->field ($field) == $value)
		 	{
				$collection->add ($item);
			}
		}

		return $collection;
	}

	/**
	 * @desc Возвращает первый элемент коллекции.
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
		return reset ($this->_items);
	}

	/**
	 * @desc Определить размеры шрифта по полю count модели. Шрифты записываются в data('font_size') модели.
	 * @param integer $minSize минимальный размер шрифта.
	 * @param integer $maxSize максимальный размер шрифта.
	 * @param integer $sizeStep Шаг шрифта (сколько прибавляется в каждом диапазоне)
	 * @return Model_Collection
	 */
	public function fontSize ($minSize=12, $maxSize=30, $sizeStep=2)
	{
		$tags = $this;
		if (!$tags)
		{
			return;
		}

		$steps = ($maxSize-$minSize)/$sizeStep; // Количество шагов(диапазонов)
		$range = 1;
		//$range = ceil(count($tags)/$steps); // Диапазон

		$size = $minSize;
		$start = 1;
		for($i=0;$i<=$steps;$i++)
		{
			$end=$start+$range;
			$sizeArray[$size] = array('start'=>$start, 'end'=>$end);
			$end++;
			$start=$end;
			$size = $size+$sizeStep;
		}

		foreach($tags as $tag)
		{
			if ($tag->count <= $sizeArray[$maxSize]['end'])
			{
				foreach($sizeArray as $key=>$size)
				{
					if ($tag->count >= $size['start'] && $tag->count <= $size['end'])
					{
						$tag->data('font_size', $key);
					}
				}
			}
			else
			{
				$tag->data('font_size', $maxSize);
			}
		}
		return $tags;
	}

	/**
	 * @desc Создать коллекцию из массива с данными моделей.
	 * @param array $rows Массив моделей.
	 * @param boolean $clear Очистить коллекцию перед добавлением.
	 * @return Model_Collection
	 */
	public function fromArray (array $rows, $clear = true)
	{
		$model = $this->modelName ();
		if ($clear)
		{
			$this->_items = array ();
		}

		$kf = $this->keyField ();
		foreach ($rows as $row)
		{
			$key = isset ($row ['id']) ? $row ['id'] : $row [$kf];
			$this->_items [] = Model_Manager::get ($model, $key, $row);
		}
		return $this;
	}

	/**
	 * @desc Создать коллекцию из запроса
	 * @param Query $query
	 * @param boolean $clear Очистить коллекцию, перед добавлением
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
	 * @return Model_Collection_Option_Collection
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
     *
     * @desc Ищет в коллекции эквивалентную по полям (если $fields пустой массив - по совпадению
     *  первичных ключей) заданой модель, и, если находит, то возвращает ее (из коллекции в которой ищется)
     * @param Model $item
     * @param array $fields
     * @return null|Model
     */
    public function hasByFields(Model $item, $fields = array())
    {
	$model = null;
	foreach ($this as $i)
	{
	    if (empty($fields))
	    {
		if (/* $i instanceof $item->modelName() && */$i->key() == $item->key()) // хочу так - не рабтает( //dp
		{
		    $model = $i;
		}
	    }
	    else
	    {
		$model = $i;
		foreach ($fields as $field)
		{
		    if ($i->field($field) != $item->field($field))
		    {
			$model = null;
			break;
		    }
		}
	    }
	    if ($model) {
		break;
	    }
	}
	return $model;
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
	 * @param Model $model Модель.
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
	 * @param Model $model Модель.
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
	 * @desc Имя ключевого поля.
	 * @return string
	 */
	public function keyField ()
	{
		return Model_Scheme::keyField ($this->modelName ());
	}

	/**
	 * @desc Получить последнюю модель коллекции.
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
		return end ($this->_items);
	}

	/**
	 * @desc Последний выполенный запрос коллекции.
	 * Если запрос еще не сформирован, запрос будет сформирован и коллекция
	 * будет загружена.
	 * @return Query Зарос коллекции.
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
			$query->select ($this->table () . '.*');
		}
		else
		{
			$query->select ((array) $colums);
		}

		$query->select (array ($this->table () => $key_field));

		$query->from ($this->modelName ());

		if ($this->_paginator)
		{
			$query->calcFoundRows ();
			$query->limit (
				$this->_paginator->pageLimit,
				$this->_paginator->offset ());
		}

		$this->_options->executeBefore ($query);

		$this->_lastQuery = $query;

		Model_Collection_Manager::load ($this, $query);

		$this->_options->executeAfter ($query);

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
	 * @return Model_Collection Эта коллекция.
	 */
	public function reset ()
	{
		$this->_items = array ();
		return $this;
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
	 * @desc Установить select-часть запроса коллекции
	 * @param string|array $columns
	 * @return Model_Collection
	 */
	public function select ($columns)
	{
		call_user_func_array (
			array ($this->query (), 'select'),
			func_get_args ()
		);

		return $this;
	}

	/**
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
			foreach ((array) $args as $field => $value)
			{
				$item->field ($field, $value);
			}
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
	 * @desc Заменить модели коллекции.
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
		//$this->_options->setOption ($option);
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
	 * @desc Вернуть первый элемент коллекции, удалив его из коллекции.
	 * @return Model|null
	 */
	public function shift ()
	{
		return array_shift ($this->_items);
	}

	/**
	 * @desc Перемешивает элементы коллекции в случайном порядке.
	 * @return Model_Collection
	 */
	public function shuffle ()
	{
		$this->items ();
		shuffle ($this->_items);
		return $this;
	}

	/**
	 * @desc Оставить часть элементов коллекции.
	 * @param integer $offset
	 * @param integer $length
	 * @return Model_Collection Эта коллекция.
	 */
	public function slice ($offset, $length)
	{
		$this->items ();
		$this->_items = array_slice ($this->_items, $offset, $length);
		return $this;
	}

	/**
	 * @desc Сортировка коллекции.
	 * @param string $fields Список полей для сортировки.
	 * Например: "id", "id DESC", "id, rating DESC".
	 * @return Model_Collection
	 */
	public function sort ($fields)
	{
		$items = &$this->items ();
		Loader::load ('Helper_Array');
		Helper_Array::mosort (
			$items,
			implode (',', func_get_args ())
		);
		return $this;
	}

	/**
	 * @desc Упорядочивание списка для вывода дерева по полю parentId
	 * @param boolean $include_unparented Оставить элементы без предка.
	 * Если false, элементы будут исключены из списка.
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
	 * @desc Получить колекцию уникальный элементов
	 * @return Model_Collection
	 */
	public function unique ()
	{
		$model_name = $this->modelName ();
		$kf = Model_Scheme::keyField ($model_name);
		$keys = array_unique ($this->column ($kf));

		$collection = new self;
		$collection->reset ();

		foreach ($keys as $key)
		{
			$model = Model_Manager::byKey (
				$model_name,
				$key
			);
			if ($model)
			{
				$collection->add ($model);
			}
		}

		return $collection;
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
	 * @desc Добавление условия отбора.
	 * @param string $condition
	 * @param string $value [optional]
	 * @return Model_Collection Эта коллекция
	 */
	public function where ($condition)
	{
		call_user_func_array (
			array ($this->query (), 'where'),
			func_get_args ()
		);

		return $this;
	}
}