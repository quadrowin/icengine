<?php
/**
 * 
 * @desc Транслятор в Mongo запрос
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class Query_Translator_Mongo extends Query_Translator
{
	
	const SQL_WILDCARD		= '*';
	const WHERE_VALUE_CHAR	= '?';
	
	/**
	 *
	 * @param Query $query
	 * @return array
	 */
	public function _partCalcFoundRows (Query $query)
	{
		return array (
			'count'	=> (bool) $query->part (Query::CALC_FOUND_ROWS)
		);
	}
	
	/**
	 * @desc Формирует запрос на удаление
	 * @param Query $query
	 * @return array
	 */
	public function _renderDelete (Query $query)
	{
		return array_merge (
			array ('method'	=> 'remove'),
			self::_renderFrom ($query, false),
			self::_renderWhere ($query)
		);
	}
	
	public function _renderFrom (Query $query, $use_alias = true)
	{
		$from = $query->part (Query::FROM);
	
		if (!$from)
		{
			return;
		}
		
		if (count ($from) > 1)
		{
			throw new Zend_Exception ('Multi from not supported.');
		}
		
		//foreach ($from as $alias => $from)
		
		$from = reset ($from);
		return array (
			'collection'	=> $from
		);
	}
	
	public function _renderInArray (array $value)
	{
		if (empty ($value))
		{
			return 'NULL';
		}

		$result = implode (',', array_map (array ($this, '_quote'), $value));
		
		return $result;
	}
	
	/**
	 * @desc Рендеринг INSERT запроса.
	 * @param Query $query Запрос.
	 * @return string Сформированный SQL запрос.
	 */
	public function _renderInsert (Query $query)
	{
		$table = $query->part (Query::UPDATE);
		return array (
			'method'		=> 'save',
			'collection'	=> strtolower (Model_Scheme::table ($table)),
			'arg0'			=> $values
		);
	}
	
	/**
	 * @desc отступ и лимит.
	 * @param Query $query
	 * @return array
	 */
	public function _renderLimitoffset (Query $query)
	{
		$sql = '';
		$limit_count = $query->part (Query::LIMIT_COUNT);
		
		if ($limit_count)
		{
			return array (
				'skip'	=> (int) $query->part (Query::LIMIT_OFFSET),
				'limit'	=> (int) $query->part (Query::LIMIT_COUNT)
			);
		}
		elseif ($query->part (Query::LIMIT_OFFSET))
		{
			return array (
				'skip'	=> (int) $query->part (Query::LIMIT_OFFSET)
			);
		}
		
		return array ();
	}
	
	/**
	 * @desc Сортировка
	 * @param Query $query
	 * @return array
	 */
	public function _renderOrder (Query $query)
	{
		$orders = $query->part (Query::ORDER);
		if (!$orders)
		{
			return array ();
		}
		
		$sort = array (
			
		);
		foreach ($orders as $order)
		{
			if ($order [1] == self::SQL_DESC)
			{
				$sort [$order [0]] = -1;
			}
			else
			{
				$sort [$order [0]] = 1;
			}
		}
		
		return array (
			'sort'	=> $sort
		);
	}
	
	/**
	 * @desc Рендеринг REPLACE запроса.
	 * @param Query $query Запрос
	 * @return string Сформированный SQL запрос
	 */
	public function _renderReplace (Query $query)
	{
		$table = $query->part (Query::UPDATE);
		return array (
			'method'		=> 'save',
			'collection'	=> strtolower (Model_Scheme::table ($table)),
			'arg0'			=> $values
		);
	}
	
	/**
	 * @desc Рендеринг SELECT (find) запроса. 
	 * @param Query $query Запрос
	 * @return string Сформированный Mongo запрос
	 */
	public function _renderSelect (Query $query)
	{
//		$parts = $query->parts ();
//		
//		$columns = array ();
//		foreach ($parts [Query::SELECT] as $alias => $sparts)
//		{
//			if (is_array ($sparts))
//			{
//				if (count ($sparts) > 1)
//				{
//					if (empty ($sparts [0]))
//					{
//						$source = '';
//					}
//					else
//					{
//						$source = 
//							$this->_escape ($sparts [0]) . 
//							self::SQL_DOT;
//					}
//							
//					if (
//						strpos ($sparts [1], self::SQL_WILDCARD) !== false ||
//						strpos ($sparts [1], '(') === false ||
//						strpos ($sparts [1], ' ') === false ||
//						strpos ($sparts [1], '.') === false ||
//						strpos ($sparts [1], '`') === false
//					)
//					{
//						$source .= $sparts [1];
//					}
//					else
//					{
//						$source .= $this->_escape ($sparts [1]);
//					}
//				}
//				elseif (strpos ($sparts [0], self::SQL_WILDCARD) !== false)
//				{
//					$source = $sparts [0];
//				}
//				else
//				{
//					$source = $this->_escape ($sparts [0]);
//				}
//			}
//			elseif (
//				strpos ($sparts, self::SQL_WILDCARD) === false &&
//				strpos ($sparts, '(') === false &&
//				strpos ($sparts, ' ') === false &&
//				strpos ($sparts, '.') === false &&
//				strpos ($sparts, '`') === false
//			)
//			{
//				$source = $this->_escape ($sparts);
//			}
//			else
//			{
//				$source = $sparts;
//			}
//			
//			if (is_numeric ($alias))
//			{
//				$columns [] = $source;
//			}
//			elseif (
//				strpos ($alias, self::SQL_WILDCARD) !== false ||
//				strpos ($alias, '(') !== false ||
//				strpos ($alias, ' ') !== false ||
//				strpos ($alias, '.') !== false
//			)
//			{
//				$columns [] = $source;
//			}
//			else
//			{
//				$columns [] = $source . ' AS ' . $this->_escape ($alias);
//			}
//		}
		
		return array_merge (
			array ('method'		=> 'find'),
			self::_renderFrom ($query),
			self::_renderWhere ($query),
			self::_renderGroup ($query),
			self::_renderOrder ($query),
			self::_renderLimitoffset ($query)
		);
	}
	
	/**
	 * @desc Рендеринг UPDATE запроса.
	 * @param Query $query Запрос.
	 * @return array
	 */
	public function _renderUpdate (Query $query)
	{

		$table = $query->part (Query::UPDATE);
		return array (
			'method'		=> 'update',
			'collection'	=> strtolower (Model_Scheme::table ($table)),
			'arg0'			=> $this->_renderWhere ($query)
		);
		
		$sql = 
			'UPDATE ' . 
			strtolower (Model_Scheme::table ($table)) . 
			' SET ';
			
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
		
		if (!$wheres)
		{
			return '';
		}
		
		$sql = 'WHERE ';
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
	
}