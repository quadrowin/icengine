<?php

class Query_Translator_Mysql extends Query_Translator
{
	
    // Для построения SQL запроса
	const SQL_AND			= 'AND';
	const SQL_ASC			= 'ASC';
	const SQL_COMMA			= ',';
	const SQL_DELETE		= 'DELETE';
	const SQL_DESC			= 'DESC';
	const SQL_DISTINCT		= 'DISTINCT';
	const SQL_DOT			= '.';
	const SQL_EQUAL			= '=';
	const SQL_ESCAPE		= '`';
	const SQL_FROM			= 'FROM';
	const SQL_GROUP_BY		= 'GROUP BY';
	const SQL_IN			= 'IN';
	const SQL_INSERT		= 'INSERT';
	const SQL_INNER_JOIN	= 'INNER JOIN';
	const SQL_LEFT_JOIN		= 'LEFT JOIN';
	const SQL_LIMIT			= 'LIMIT';
	const SQL_LIKE			= 'LIKE';
	const SQL_ON			= 'ON';
	const SQL_ORDER_BY		= 'ORDER BY';
	const SQL_QUOTE			= '"';
	const SQL_SELECT		= 'SELECT';
	const SQL_SET			= 'SET';
	const SQL_SHOW			= 'SHOW';
	const SQL_RLIKE			= 'RLIKE';
	const SQL_UPDATE		= 'UPDATE';
	const SQL_VALUES		= 'VALUES';
	const SQL_WHERE			= 'WHERE';
	const SQL_WILDCARD		= '*';
	
	const WHERE_VALUE_CHAR	= '?';
	
	/**
	 * 
	 * @var Model_Scheme_Abstract
	 */
	protected $_modelScheme;
		
	/**
	 * 
	 * @param string $value
	 * @return string
	 */
	public function _escape ($value)
	{
		return self::SQL_ESCAPE . mysql_real_escape_string($value) . self::SQL_ESCAPE;
	}
	
	/**
	 * 
	 * @param mixed $value
	 * @return string
	 */
	public function _quote ($value)
	{
        return self::SQL_QUOTE . mysql_real_escape_string ($value) . self::SQL_QUOTE;
	}
	
	public function _partCalcFoundRows (Query $query)
	{
	    if (!$query->part (Query::CALC_FOUND_ROWS))
	    {
	        return '';
	    }
	    
	    return 'SQL_CALC_FOUND_ROWS';
	}
	
	/**
	 * 
	 * @param Query $query
	 * @return string
	 */
	protected function _partDistinct (Query $query)
	{
		return $query->part (Query::DISTINCT, '');
	}
	
	public function _renderDelete (Query $query)
	{
		return 
			self::SQL_DELETE . ' ' .
			self::_renderFROM ($query, false) . ' ' . 
			self::_renderWhere ($query);
	}
	
	public function _renderFrom (Query $query, $use_alias = true)
	{
		$sql = self::SQL_FROM;
		$i = 0;
		foreach ($query->part (Query::FROM) as $alias => $from)
		{
			$table = strtolower ($this->_modelScheme->get ($from [Query::TABLE]));
			
			$table = $this->_escape ($table);
			$alias = $this->_escape ($alias);
			
			if ($from [Query::JOIN] == Query::FROM)
			{
				$a = ($table == $alias || !$use_alias);
				$sql .= 
					($i ? self::SQL_COMMA : ' ') .
					($a ? $table : ($table . ' AS ' . $alias));
			}
			else
			{
				if (is_array ($from[Query::WHERE]))
				{
					$where = 
						$this->_escape ($from[Query::WHERE][0]) . 
						self::SQL_DOT .
						$this->_escape ($from[Query::WHERE][1]) .
						'=' .
						$this->_escape ($from[Query::WHERE][2]) .
						self::SQL_DOT .
						$this->_escape ($from[Query::WHERE][3]);
				}
				else
				{
					$where = $from [Query::WHERE];
				}
				$sql .= ' ' . 
					$from[Query::JOIN] . ' ' . 
					$table . ' AS ' . $alias . ' ' .
					self::SQL_ON .
					'(' . $from[Query::WHERE] . ')';
			}
			$i++;
		}
		return $sql;
	}
	
	public function _renderGroup(Query $query)
	{
		$groups = $query->part (Query::GROUP);
		
		if (empty($groups))
		{
			return '';
		}
		
		$columns = array();
		foreach ($groups as $column)
		{
		    if (
		        strpos ($column, '(') !== false ||
				strpos ($column, '`') !== false
			)
			{
			    $columns [] = $column;
			}
			elseif (strpos($column, self::SQL_DOT) !== false)
			{
				$column = explode (self::SQL_DOT, $column);
				$column = array_map (array($this, '_escape'), $column);
				$columns [] = implode (self::SQL_DOT, $column);
			}
			else
			{
				$columns [] = $this->_escape ($column);
			}
		}
		return  
			self::SQL_GROUP_BY . ' ' . 
			implode (self::SQL_COMMA, $columns);
	}
	
	public function _renderInArray (array $value)
	{
	    if (empty ($value))
	    {
	        return 'NULL';
	    }
	    
	    $result = $this->_quote ($value [0]);
	    
	    for ($i = 1, $count = count ($value); $i < $count; $i++)
	    {
	        $result .= ',' . $this->_quote ($value [$i]);
	    }
	    
	    return $result;
	}
	
	public function _renderInsert (Query $query)
	{
		$table = $query->part (Query::INSERT);
		$sql = 
			self::SQL_INSERT . ' ' .
			strtolower ($this->_modelScheme->get ($table)) . 
			' (';
		
		$fields = array_keys ($query->part (Query::VALUES));
		$values = array_values ($query->part (Query::VALUES));
		
		for ($i = 0; $i < count ($fields); $i++)
		{
			$fields[$i] = self::_escape ($fields[$i]);
			$values[$i] = self::_quote ($values[$i]);
		}
		
		$fields = implode (', ', $fields);
		$values = implode (', ', $values);
		
		return $sql . $fields . ') ' . self::SQL_VALUES . ' (' . $values . ')';
	}
	
	public function _renderLimitoffset (Query $query)
	{
		$sql = '';
		$limit_count = $query->part (Query::LIMIT_COUNT);
		
		if (!empty ($limit_count))
		{
			$sql .= ' ' . 
				self::SQL_LIMIT . ' ' . 
				(int) $query->part (Query::LIMIT_OFFSET) . 
				self::SQL_COMMA . 
				(int) $query->part (Query::LIMIT_COUNT);
		}
		elseif ($query->part (Query::LIMIT_OFFSET))
		{
			$sql .= ' ' . 
				self::SQL_LIMIT . ' ' . 
				(int) $query->part (Query::LIMIT_OFFSET);
		}
		
		return $sql;
	}
	
	public function _renderOrder (Query $query)
	{
		$orders = $query->part (Query::ORDER);
		if (empty ($orders))
		{
			return '';
		}
		$columns = array();
		foreach ($orders as $order)
		{
			$field = explode (self::SQL_DOT, $order[0]);
			$field = array_map (array($this, '_escape'), $field);
			$field = implode (self::SQL_DOT, $field);
			
			if ($order[1] == self::SQL_DESC)
			{
				$columns[] = $field . ' ' . self::SQL_DESC;
			}
			else
			{
				$columns[] = $field;
			}
		}
		
		return 
			self::SQL_ORDER_BY . ' ' . 
			implode (self::SQL_COMMA, $columns);
	}
	
	public function _renderSelect (Query $query)
	{
		$sql =
		    self::SQL_SELECT . ' ' .
		    self::_partCalcFoundRows ($query) . ' ' . 
		    self::_partDistinct ($query) . ' ';
		$parts = $query->parts ();
		
		$columns = array ();
		foreach ($parts [Query::SELECT] as $alias => $sparts)
		{
			if (is_array ($sparts))
			{
				if (count ($sparts) > 1)
				{
					if (empty ($sparts [0]))
					{
						$source = '';
					}
					else
					{
						$source = 
							$this->_escape ($sparts[0]) . 
							self::SQL_DOT;
					}
							
					if (
						strpos ($sparts[1], self::SQL_WILDCARD) !== false ||
						strpos ($sparts[1], '(') === false ||
						strpos ($sparts[1], ' ') === false ||
						strpos ($sparts[1], '.') === false ||
						strpos ($sparts[1], '`') === false
					)
					{
						$source .= $sparts[1];
					}
					else
					{
						$source .= $this->_escape ($sparts[1]);
					}
				}
				elseif (strpos ($sparts[0], self::SQL_WILDCARD) !== false)
				{
					$source = $sparts [0];
				}
				else
				{
					$source = $this->_escape ($sparts [0]);
				}
			}
			elseif (
				strpos ($sparts, self::SQL_WILDCARD) === false &&
				strpos ($sparts, '(') === false &&
				strpos ($sparts, ' ') === false &&
				strpos ($sparts, '.') === false &&
				strpos ($sparts, '`') === false
			)
			{
				$source = $this->_escape ($sparts);
			}
			else
			{
				$source = $sparts;
			}
			
			if (is_numeric ($alias))
			{
				$columns[] = $source;
			}
			elseif (
				strpos ($alias, self::SQL_WILDCARD) !== false ||
				strpos ($alias, '(') !== false ||
				strpos ($alias, ' ') !== false
			)
			{
				$columns [] = $source;
			}
			else
			{
				$columns [] = $source . ' AS ' . $this->_escape ($alias);
			}
		}
		
		return $sql . implode (self::SQL_COMMA, $columns) . ' ' . 
			self::_renderFrom ($query) . ' ' .
			self::_renderWhere ($query) . ' ' .
			self::_renderGroup ($query) . ' ' .
			self::_renderOrder ($query) . ' ' .
			self::_renderLimitoffset ($query);
	}
	
	public function _renderShow(Query $query)
	{
		$sql = self::SQL_SHOW . ' ' . $this->_partDistinct ($query) . ' ';
		
		$sql .= $this->_modelScheme->get ($query->part (Query::SHOW));
		
		return $sql . ' ' .
			self::_renderFrom ($query) . ' ' .
			self::_renderWhere (query) . ' ' .
			self::_renderOrder ($query) . ' ' .
			self::_renderLimitoffset ($query) . ' ' .
			self::_renderGroup ($query);
	}
	
	public function _renderUpdate(Query $query)
	{
		$table = $query->part (Query::UPDATE);
		$sql = 
			self::SQL_UPDATE . ' ' . 
			strtolower ($this->_modelScheme->get ($table)) . ' ' . 
			self::SQL_SET . ' ';
			
		$values = $query->part (Query::VALUES);
		$sets = array();
		
		foreach ($values as $field => $value)
		{
		    if (
		        strpos ($field, '?') !== false ||
		        strpos ($field, '=') !== false
		    )
		    {
		        $sets [] = str_replace ('?', $this->_quote ($value), $field);
		    }
		    else
		    {
			    $sets [] = self::_escape ($field) . '=' . $this->_quote ($value);
		    }
		}
		
		return $sql . implode (', ', $sets) . ' ' . $this->_renderWhere ($query);
	}
	
	public function _renderWhere (Query $query)
	{
		$wheres = $query->part (Query::WHERE);
		
		if (empty ($wheres))
		{
			return '';
		}
		
		$sql = self::SQL_WHERE . ' ';
		foreach ($wheres as $i => $where)
		{
			if ($i > 0)
			{
				$sql .= ' ' . $where [0] . ' ';
				
			}
			
			if (array_key_exists (Query::VALUE, $where))
			{
				 $sql .= $this->_quoteCondition (
					$where [Query::WHERE],
					$where [Query::VALUE] 
				);
			}
			else
			{
				 $sql .= $this->_quoteCondition (
					$where [Query::WHERE] 
				);
			}
		}
		
		return $sql;
	}
	
	/**
	 * 
	 * @param string $condition
	 * @param mixed $value [optional]
	 * @return string
	 */
	protected function _quoteCondition ($condition)
	{
		if (func_num_args () == 1)
		{
			return $condition;
		}
		
		$value = func_get_arg (1);
		
		if (strpos ($condition, self::WHERE_VALUE_CHAR) === false)
		{
		    if (is_array ($value))
		    {
		        $value = ' IN (' . $this->_renderInArray ($value) . ')';
		    }
		    else
		    {
		        $value = '=' . $this->_quote ($value); 
		    }
		    
			if (
				strpos ($condition, '(') === false &&
				strpos ($condition, ' ') === false &&
				strpos ($condition, '.') === false &&
				strpos ($condition, '`') === false
			)
			{
				return $this->_escape ($condition) . $value;
			}
			
			return $condition . $value;
		}
		else
		{
		    return str_replace (
	            self::WHERE_VALUE_CHAR, 
	            is_array ($value) ? $this->_renderInArray ($value) : $this->_quote ($value),
	            $condition
	        );
		}
	}
	
	/**
	 * 
	 * @param Query $query
	 * @param Model_Scheme_Abstract $model_scheme
	 * @return string
	 */
	public function translate (Query $query, Model_Scheme_Abstract $model_scheme)
	{
		$this->_modelScheme = $model_scheme;
		
		$type = $query->type ();
		$type = 
		    strtoupper (substr ($type, 0, 1)) . 
		    strtolower (substr ($type, 1));
		
		$result = call_user_func (
			array ($this, '_render' . $type),
			$query
		);
		
		//echo $result.'<br />';
		
		return $result;
	}
	
}