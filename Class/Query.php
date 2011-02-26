<?php

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
	
	// Части выборки по умолчанию
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
	 * Части запроса
	 * @var array
	 */
	public $_parts;
	
	/**
	 * Тип запроса
	 * @var string
	 */
	protected $_type;
	
	public function __construct()
	{
		$this->reset ();
	}
	
	/**
	 * Преобразует части запроса в строку
	 * @return string
	 */
	public function __toString ()
	{
		return $this->translate ();
	}
	
	/**
	 * 
	 * @param string|array $table
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
	 * Работает только для Mysql.
	 * В запрос будет добавлен аргумент для получения полного
	 * количества строк (SQL_CALC_FOUND_ROWS).
	 */
	public function calcFoundRows ()
	{
	   $this->_parts [self::CALC_FOUND_ROWS] = true; 
	}
	
	/**
	 * Это запрос на удаление
	 * @return Query
	 */
	public function delete ()
	{
		$this->_type = self::DELETE;
		return $this;
	}
		
	/**
	 * Устанавливает значение флага DISTINCT
	 * @param boolean $value
	 * @return Query
	 */
	public function distinct($value)
	{
		$this->_parts [self::DINSTINCT] = ( bool ) $value;
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
	 * Возвращает часть запроса
	 * @param string $name
	 * @return mixed
	 */
	public function getPart ($name)
	{
		return isset ($this->_parts [$name]) ? $this->_parts [$name] : null;
	}
	
	/**
	 * 
	 * @param array|string $fields
	 * @return Query
	 */
	public function group ($columns)
	{
		if (!is_array ($columns))
		{
			$columns = array ($columns);
		}
		
		foreach ($columns as $column)
		{
			$this->_parts [self::GROUP] [] = $column;
		}
		
		return $this;
	}
	
	/**
	 * @return Query
	 */
	public static function instance ()
	{
		return new self ();
	}
	
	/**
	 * Запрос преобразуется в запрос на вставку
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
	 * 
	 * @param string|array $sort
	 * 		'id' | array('id' => Query::DESC)
	 * @return Query 
	 */
	public function order ($sort)
	{
		if (!is_array ($sort))
		{
			$sort = array ($sort);
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
	 * 
	 * @param string $condition
	 * @param mixed $value
	 * @return Query
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
	 * Возвращает часть запроса
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function part ($name, $default = null)
	{
		return isset ($this->_parts [$name]) ? $this->_parts [$name] : $default;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function parts ()
	{
		return $this->_parts;
	}
	
	/**
	 * Сброс частей выборки
	 * @return Query
	 */
	public function reset ()
	{
		$this->_parts = self::$_defaults;
		$this->_type = self::SELECT;
		return $this;
	}
	
	/**
	 * 
	 * @param string|array $parts
	 * @return Query
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
	 * Установка значения для UPDATE
	 * @param string $column
	 * @param string $value
	 * @return Query
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
		return $this;
	}
	
	/**
	 * Формирует новый запрос, определяющий количество записей,
	 * которые будут выбраны в результате этого запроса.
	 * 
	 * @return Query
	 * 		Новый запрос
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
	 * 
	 * @param string $translator
	 * @param Model_Scheme_Abstract
	 * @return mixed
	 */
	public function translate ($translator = 'Mysql', Model_Scheme $model_scheme)
	{
		return Query_Translator::factory ($translator)->translate ($this, $model_scheme);
	}
	
	/**
	 * Тип запроса
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
	 * 
	 * @param string $table
	 * @return Query
	 */
	public function update($table)
	{
		$this->_type = self::UPDATE;
		$this->_parts [self::UPDATE] = $table;
		return $this;
	}
	
	/**
	 * Использовать индекс
	 * @param string $index Название индекса
	 * @return Query
	 */
	public function useIndex($index)
	{
		$this->_parts [self::INDEX][] = $index;
		return $this;
	}
	
	/**
	 * Установка значений для INSERT/UPDATE
	 * @param array $values
	 * @return Query
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
	 * 
	 * @param string $condition
	 * @param string $value [optional]
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