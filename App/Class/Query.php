<?php
/**
 *
 * @desc Запрос к источнику данных.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Query {

	const ASC				= 'ASC';
	const CALC_FOUND_ROWS	= 'CALC_FOUND_ROWS';
	const DELETE			= 'DELETE';
	const DESC				= 'DESC';
	const DISTINCT			= 'DISTINCT';
	const FROM 				= 'FROM';
	const GROUP				= 'GROUP';
	const INDEX				= 'INDEX';
	const INDEXES			= 'INDEXES';
	const INNER_JOIN		= 'INNER JOIN';
	const INSERT			= 'INSERT';
	const JOIN				= 'JOIN';
	const LEFT_JOIN			= 'LEFT JOIN';
	const ORDER				= 'ORDER';
	const REPLACE			= 'REPLACE';
	const SELECT			= 'SELECT';
	const SET				= 'SET';
	const SHOW				= 'SHOW';
	const TABLE				= 'TABLE';
	const TYPE				= 'TYPE';
	const LIMIT_COUNT		= 'LIMITCOUNT';
	const LIMIT_OFFSET		= 'LIMITOFFSET';
	const VALUE				= 'VALUE';
	const VALUES			= 'VALUES';
	const WHERE				= 'WHERE';
	const UPDATE			= 'UPDATE';

	const SQL_AND			= 'AND';
	const SQL_OR			= 'OR';

	/**
	 * @desc Части запроса по умолчанию.
	 * @var array
	 */
	public static $_defaults = array (
		self::CALC_FOUND_ROWS	=> false,
		self::DISTINCT			=> false,
		self::JOIN				=> false,
		self::FROM				=> array(),
		self::SELECT			=> array(),
		self::SHOW				=> array(),
		self::WHERE				=> array(),
		self::GROUP				=> array(),
		self::ORDER				=> array(),
		self::LIMIT_COUNT		=> null,
		self::LIMIT_OFFSET		=> 0,
		self::INDEX				=> array()
	);

	/**
	 * @desc Части запроса
	 * @var array
	 */
	public $_parts;

	/**
	 * @desc Тип запроса
	 * @var string
	 */
	protected $_type;

	/**
	 * @desc Возвращает новый пустой запрос.
	 */
	public function __construct()
	{
		$this->reset ();
	}

	/**
	 * @desc Преобразует части запроса в строку
	 * @return string
	 */
	public function __toString ()
	{
		return $this->translate ();
	}

	/**
	 * @desc Добавление джойна таблицы к запросу
	 * @param string|array $table Название таблицы или
	 * пара (table => alias) или, в случае нескольких алиасов
	 * (table => array (alias1, alias2,...)).
	 * Джойн нескольких таблиц не поддерживается.
	 * @param string $type
	 * @param string $condition optional
	 */
	protected function _join ($table, $type, $condition = null)
	{
		if (is_array ($table))
		{
			reset ($table);
			$aliases = (array) current ($table);
			$table = key ($table);
		}
		else
		{
			$aliases = (array) $table;
		}

		foreach ($aliases as $alias)
		{
			$this->_parts [self::FROM] [$alias] = array (
				self::TABLE		=> $table,
				self::WHERE		=> $condition,
				self::JOIN		=> $type
			);
		}
	}

	/**
	 * @desc В запрос будет добавлен аргумент для получения полного
	 * количества строк (SQL_CALC_FOUND_ROWS).
	 * Работает только для Mysql.
	 * @return Query Этот объект.
	 */
	public function calcFoundRows ()
	{
	   $this->_parts [self::CALC_FOUND_ROWS] = true;
	   return $this;
	}

	/**
	 * @desc Это запрос на удаление
	 * @return Query
	 */
	public function delete ()
	{
		$this->_type = self::DELETE;
		$this->_parts [self::DELETE] = func_get_args ();
		return $this;
	}

	/**
	 * @desc Устанавливает значение флага DISTINCT
	 * @param boolean $value
	 * @return Query
	 */
	public function distinct ($value)
	{
		$this->_parts [self::DISTINCT] = (bool) $value;
		return $this;
	}

	/**
	 *
	 * @param string|array $table Имя таблицы или array('table' => 'alias')
	 * @param string $alias
	 * @return Query
	 */
	public function from ($table, $alias = null)
	{
		$this->_join (
			$alias ? array ($table => $alias) : $table,
			self::FROM
		);
		return $this;
	}
	
	/**
	 * @desc Возвращает массив моделей, учавствующих в запросе
	 * @return array
	 */
	public function getModels ()
	{
		$result = array ();
		
		if ($this->_parts [self::INSERT])
		{
			$result [] = $this->_parts [self::INSERT];
		}
		
		if ($this->_parts [self::REPLACE])
		{
			$result [] = $this->_parts [self::REPLACE];
		}
		
		foreach ($this->_parts [self::FROM] as $from)
		{
			$result [] = $from [self::TABLE];
		}
		
		if ($this->_parts [self::UPDATE])
		{
			$result [] = $this->_parts [self::UPDATE];
		}
		
		if ($this->_parts [self::DELETE])
		{
			$rseult = $result + (array) $this->_parts [self::DELETE];
		}
		
		return array_unique ($result);
	}

	/**
	 * @desc Возвращает часть запроса
	 * @param string $name
	 * @return mixed
	 */
	public function getPart ($name)
	{
		return isset ($this->_parts [$name]) ? $this->_parts [$name] : null;
	}

	/**
	 * @desc Возвращает теги запроса
	 * @return array<string>
	 */
	public function getTags ()
	{
		$tags = array ();

		$from = $this->getPart (Query::FROM);
		foreach ($from as $info)
		{
			$tags [] = Model_Scheme::table ($info [Query::TABLE]);
		}

		$insert = $this->getPart (QUERY::INSERT);
		if ($insert)
		{
	   		$tags [] = Model_Scheme::table ($insert);
		}

		$update = $this->getPart (QUERY::UPDATE);
		if ($update)
		{
			$tags [] = Model_Scheme::table ($update);
		}

		return array_unique ($tags);
	}

	/**
	 * @desc Устанавливает правило группировки
	 * @param array|string $fields
	 * @param string $_ Поля для группировки.
	 * @return Query
	 */
	public function group ($columns)
	{
		if (!is_array ($columns))
		{
			$columns = func_get_args ();
		}

		foreach ($columns as $column)
		{
			$this->_parts [self::GROUP] [] = $column;
		}

		return $this;
	}

	/**
	 * @desc Создает и возвращает новый запрос.
	 * Аналогично "new Query()".
	 * @return Query Новый запрос.
	 */
	public static function instance ()
	{
		return new self ();
	}

	/**
	 * @desc Запрос преобразуется в запрос на вставку
	 * @return Query
	 */
	public function insert ($table)
	{
		$this->_parts [self::INSERT] = $table;
		$this->_type = self::INSERT;
		return $this;
	}

	/**
	 *
	 * @param string|array $table
	 * @param string $condition
	 * @return Query
	 */
	public function innerJoin ($table, $condition)
	{
		$this->_join ($table, self::INNER_JOIN, $condition);

		return $this;
	}

	/**
	 *
	 * @param string|array $table
	 * @param string $condition
	 * @return Query
	 */
	public function leftJoin ($table, $condition)
	{
		$this->_join ($table, self::LEFT_JOIN, $condition);

		return $this;
	}

	/**
	 * @desc Задает правило сортировки
	 * @param string|array $sort
	 * 		'id' | array('id' => Query::DESC)
	 * @return Query
	 */
	public function order ($sort)
	{
		if (!is_array ($sort))
		{
			$sort = func_get_args ($sort);
		}

		foreach ($sort as $field => $direction)
		{
			if (is_numeric ($field))
			{
				$field = $direction;
				$direction = self::ASC;
			}
			$this->_parts [self::ORDER] [] = array ($field, $direction);
		}

		return $this;
	}

	/**
	 * @desc Добавляет к запросу условие "или".
	 * @param string $condition
	 * @param mixed $value
	 * @return Query
	 * @deprecated
	 */
	public function orWhere ($condition)
	{
		$where = array (
			0				=> self::SQL_OR,
			self::WHERE		=> $condition
		);

		if (func_num_args () > 1)
		{
			$where [self::VALUE] = func_get_arg (1);
		}

		$this->_parts [self::WHERE] [] = $where;

		return $this;
	}

	/**
	 * @desc Возвращает часть запроса
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function part ($name, $default = null)
	{
		return isset ($this->_parts [$name]) ? $this->_parts [$name] : $default;
	}

	/**
	 * @desc Возвращает все части запроса.
	 * @return array
	 */
	public function parts ()
	{
		return $this->_parts;
	}

	/**
	 * @desc Запрос преобразуется в запрос на replace.
	 * @param string $table таблица.
	 * @return Query Этот запрос.
	 */
	public function replace ($table)
	{
		$this->_parts [self::REPLACE] = $table;
		$this->_type = self::REPLACE;
		return $this;
	}

	/**
	 * @desc Сброс всех частей запроса.
	 * @return Query Этот запрос.
	 */
	public function reset ()
	{
		$this->_parts = self::$_defaults;
		$this->_type = self::SELECT;
		return $this;
	}

	/**
	 * @desc Сбрасывает часть запроса
	 * @param string|array $parts
	 * @return Query Этот запрос.
	 */
	public function resetPart ($parts)
	{
		if (!is_array ($parts))
		{
			$parts = func_get_args ();
		}

		foreach ($parts as $part)
		{
			if (isset (self::$_defaults [$part]))
			{
				$this->_parts [$part] = self::$_defaults [$part];
			}
			else
			{
				unset ($this->_parts [$part]);
			}
		}

		return $this;
	}

	/**
	 * @desc Добавить в запрос SELECT часть.
	 * @param string|array $columns
	 * @tutorial
	 * 		select (
	 * 			'table1'	=> array ('field11' => 'alias11', 'field12'),
	 * 			'table2'	=> array ('field21', 'field22')
	 * 		)
	 * 		select ('field1', 'field2')
	 * 		select ('COUNT(*)')
	 * 		select (
	 * @return Query
	 */
	public function select ($columns)
	{
		foreach (func_get_args () as $columns)
		{
			if (is_array ($columns))
			{
				// Передано название таблицы
				foreach ($columns as $table => $fields)
				{
					$fields = (array) $fields;
					foreach ($fields as $name => $aliases)
					{
						$aliases = (array) $aliases;
						foreach ($aliases as $alias)
						{
							// реальное название столбца
							$rname = is_numeric ($name) ? $alias : $name;

							$this->_parts [self::SELECT] [$alias] = array ($table, $rname);
						}
					}
				}
			}
			elseif ($columns)
			{
				// переданы только поля
				$args = func_get_args ();
				for ($i = 0, $count = count ($args); $i < $count; $i++)
				{
					$this->_parts [self::SELECT] [$args [$i]] = $args [$i];
				}
			}
		}

		return $this;
	}

	/**
	 * @desc Установка значения для UPDATE
	 * @param string $column
	 * @param string $value
	 * @return Query Этот запрос.
	 */
	public function set ($column, $value)
	{
	   	if (isset ($this->_parts [self::VALUES]))
		{
			$this->_parts [self::VALUES][$column] = $value;
		}
		else
		{
			$this->_parts [self::VALUES] = array ($column => $value);
		}
		return $this;
	}

	/**
	 * @desc Подменяет часть запроса
	 * @param string $name Часть запроса.
	 * @param mixed $value Новое значение.
	 * @return Query Этот запрос.
	 */
	public function setPart ($name, $value)
	{
		$this->_parts [$name] = $value;
		return $this;
	}

	/**
	 *
	 * @param string|array $columns
	 * @return Query
	 */
	public function show ($columns)
	{
		$this->_type = self::SHOW;
		$this->_parts [self::SHOW] = $columns;
		return $this;
	}

	/**
	 * @desc Добавление джойна таблицы, если она еще не подключна.
	 * @param string $table Название таблицы. Алиас не принимается.
	 * @param string $condition
	 * @return Query
	 */
	public function singleInnerJoin ($table, $condition)
	{
		$joins = $this->getPart (self::FROM);
		foreach ($joins as $alias => $data)
		{
			if ($data [self::TABLE] == $table)
			{
				// Таблица уже подключена
				if ($data [self::JOIN] == self::FROM)
				{
					return $this->where ($condition);
				}

				if ($data [self::JOIN] == self::LEFT_JOIN)
				{
					$data [self::JOIN] = self::INNER_JOIN;
				}

				if ($data [self::WHERE] != $condition)
				{
					$data [self::WHERE] =
						'(' .
							$data [self::WHERE] .
						') AND (' .
							$condition .
						')';
				}
				return $this;
			}
		}

		// Еще не подключена
		return $this->innerJoin ($table, $condition);
	}

	/**
	 * @desc Добавление джойна таблицы, если она еще не подключна.
	 * @param string $table Название таблицы.
	 * Алиас не принимается.
	 * @param string $condition
	 * @return Query
	 */
	public function singleLeftJoin ($table, $condition)
	{
		$joins = $this->getPart (self::FROM);
		foreach ($joins as $alias => $data)
		{
			if ($data [self::TABLE] == $table)
			{
				// Таблица уже подключена
				if ($data [self::JOIN] == self::FROM)
				{
					return $this;
				}

				if ($data [self::WHERE] != $condition)
				{
					$data [self::WHERE] =
						'(' .
							$data [self::WHERE] .
						') AND (' .
							$condition .
						')';
				}
				return $this;
			}
		}

		// Еще не подключена
		return $this->leftJoin ($table, $condition);
	}

	/**
	 * @desc Формирует новый запрос, определяющий количество записей,
	 * которые будут выбраны в результате этого запроса.
	 * @return Query Новый запрос
	 */
	public function toCountQuery ()
	{
		$count_query = clone $this;

		$count_query
			->resetPart (self::SELECT)
			->select ('COUNT(1) AS `count`')
			->resetPart (self::LIMIT_COUNT)
			->resetPart (self::LIMIT_OFFSET)
			->resetPart (self::ORDER);

		return $count_query;
	}

	/**
	 * @desc Транслирует запрос указанным транслятором
	 * 2011-11-17 Пока будет так. Метод следует использовать исключительно 
	 * для отладки.
	 * @param string $translator Транслятор.
	 * @return mixed Транслированный запрос.
	 */
	public function translate ($translator = 'Mysql')
	{
		Loader::load ('Model_Map');
		$model_map = new Model_Map;

		$models = $this->getModels ();
		foreach ($models as $model)
		{
			$model_map->setTable ($model, Model_Scheme::table ($model));
		}
			
		return Query_Translator::factory ($translator)->translate (
			$this,
			$model_map
		);
	}

	/**
	 * @desc Тип запроса
	 * @return string SELECT, DELETE, INSERT, UPDATE, SHOW
	 */
	public function type ()
	{
		return $this->_type;
	}

	/**
	 * Sets a limit count and offset to the query.
	 * 10x Zend
	 *
	 * @param integer $count OPTIONAL The number of rows to return.
	 * @param integer $offset OPTIONAL Start returning after this many rows.
	 * @return Query
	 */
	public function limit ($count = null, $offset = null)
	{
		$this->_parts [self::LIMIT_COUNT]  = (int) $count;
		$this->_parts [self::LIMIT_OFFSET] = (int) $offset;
		return $this;
	}

	/**
	 * @desc Преобразует запрос к запросу на обновление.
	 * @param string $table Таблица для обновления.
	 * @return Query Этот запрос.
	 */
	public function update ($table)
	{
		$this->_type = self::UPDATE;
		$this->_parts [self::UPDATE] = $table;
		return $this;
	}

	/**
	 * Использовать индекс
	 * @param string $index Название индекса
	 * @return Query Этот запрос.
	 */
	public function useIndex ($index)
	{
		$this->_parts [self::INDEX][] = $index;
		return $this;
	}

	/**
	 * @desc Установка значений для INSERT/UPDATE
	 * @param array $values
	 * @return Query Этот запрос.
	 */
	public function values (array $values)
	{
		if (isset ($this->_parts [self::VALUES]))
		{
			$this->_parts [self::VALUES] = array_merge (
				$this->_parts [self::VALUES],
				$values
			);
		}
		else
		{
			$this->_parts [self::VALUES] = $values;
		}
		return $this;
	}

	/**
	 * @desc Добавляет условие к запросу
	 * @param string $condition Условие
	 * @param string $value [optional] Значение, подставляемое в условие.
	 * @return Query
	 */
	public function where ($condition)
	{
		$where = array (
			0				=> self::SQL_AND,
			self::WHERE		=> $condition
		);

		if (func_num_args () > 1)
		{
			$where [self::VALUE] = func_get_arg (1);
		}

		$this->_parts [self::WHERE] [] = $where;

		return $this;
	}

}