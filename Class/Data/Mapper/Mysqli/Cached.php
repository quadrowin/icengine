<?php

class Data_Mapper_Mysqli_Cached extends Data_Mapper_Mysqli
{	   
	/**
	 * 
	 * 
	 * @var Data_Provider_Abstract
	 */
	protected $_cacher;
	
	/**
	 * Получение хэша запроса
	 * @return string
	 */
	protected function _sqlHash ()
	{
		return md5 ($this->_sql);
	}
	
	/**
	 * Выполняет запрос на изменение данных.
	 * @param Query $query
	 * @param Query_Options $options
	 * @return boolean
	 */
	protected function _executeChange (Query $query, Query_Options $options)
	{
		if (!mysql_query ($this->_sql))
		{
			$this->_errno = mysql_errno ();
			$this->_error = mysql_error ();
			return false;
		}
		
		$this->_affectedRows = mysql_affected_rows ();
		
		if ($this->_affectedRows > 0)
		{
			$tags = $this->_getTags ($query);
			
			for ($i = 0, $count = sizeof ($tags); $i < $count; $i++)
			{
				$this->_cacher->tagDelete ($tags [$i]);
			}
		}
		
		return true;
	}
	
	/**
	 * Выполняет запрос на вставку данных.
	 * @param Query $query
	 * @param Query_Options $options
	 * @return boolean
	 */
	protected function _executeInsert (Query $query, Query_Options $options)
	{
		if (!mysql_query ($this->_sql))
		{
			$this->_errno = mysql_errno ();
			$this->_error = mysql_error ();
			return false;
		}
		
		$this->_affectedRows = mysql_affected_rows ();
		$this->_insertId = mysql_insert_id ();
		
		if ($this->_affectedRows > 0)
		{
			$tags = $this->_getTags ($query);
			
			for ($i = 0, $count = sizeof ($tags); $i < $count; $i++)
			{
				$this->_cacher->tagDelete ($tags [$i]);
			}
		}
		
		return true;
	}
	
	/**
	 * Выполняет запрос на получение данных.
	 * @param Query $query
	 * @param Query_Options $options
	 * @return null|array
	 */
	protected function _executeSelect (Query $query, Query_Options $options)
	{
		$key = $this->_sqlHash ();
		$key_hits = $key . '_h';
		
		$expiration = $options->getExpiration ();
		$hits = $options->getHits ();
		
		$cache = $this->_cacher->get ($key);
		
		$use_cache = false;
		
		if ($cache)
		{
			if (
	   			($cache ['a'] + $expiration > time () || $expiration == 0) && 
				$this->_cacher->checkTags ($cache ['t']) &&
				(!$hits || $this->_cacher->get ($key_hits) < $hits)
			)
			{
	  			$use_cache = true;
			}
			
			if (!$this->_cacher->lock ($key, 5, 1, 1))
			{
				$use_cache = true;
			}
		}
		
		if ($use_cache)
		{
			$this->_numRows = count ($cache ['v']);
			$this->_foundRows = $cache ['f'];
			return $cache ['v'];
		}
		
		$result = mysql_query ($this->_sql);
		
		if (!is_resource ($result))
		{
			$this->_errno = mysql_errno ();
			$this->_error = mysql_error ();
			return;
		}
		
		$rows = array ();
		while (false != ($row = mysql_fetch_assoc ($result)))
		{
			$rows [] = $row;
		}
		mysql_free_result ($result);
		
		$this->_numRows = count ($rows);
		
		if ($query->part (Query::CALC_FOUND_ROWS))
		{
			$result = mysql_query (self::SELECT_FOUND_ROWS_QUERY);
			$row = mysql_fetch_row ($result);
			$this->_foundRows = reset ($row);
			mysql_free_result ($result);
		}
		
		$tags = $this->_getTags ($query);
		
		$this->_cacher->set (
			$key, 
			array (
				'v' => $rows,
				'a' => time (),
				't' => $this->_cacher->getTags ($tags),
				'f'	=> $this->_foundRows
			)
		);
		
		if ($cache)
		{
			$this->_cacher->unlock ($key);
		}
		
		if ($hits)
		{
			$this->_cacher->set ($key_hits, 0);
		}
		
		return $rows;
	}
	
	/**
	 * 
	 * @param Query $query
	 * @return array
	 */
	public function _getTags (Query $query)
	{
		$tags = array ();
		
		$from = $query->getPart (Query::FROM);
		foreach ($from as $info)
		{
			$tags [] = $this->_modelScheme->table ($info [Query::TABLE]);
		}
		
		$insert = $query->getPart (QUERY::INSERT);
		if ($insert)
		{
	   		$tags [] = $this->_modelScheme->table ($insert);
		}
	   	
		$update = $query->getPart (QUERY::UPDATE);
		if ($update)
		{
			$tags [] = $this->_modelScheme->table ($update);
		}
		
		
//		echo DDS::getDataSource ()->getQuery ()->translate ('Mysql', DDS::modelScheme ()) . ' => ';
//		var_dump ($tags);
		
		return array_unique ($tags);
	}
	
	/**
	 * @return Data_Provider_Abstract
	 */
	public function getCacher ()
	{
		return $this->_cacher;
	}
	
	/**
	 * 
	 * @param Data_Provider_Abstract $cacher
	 */
	public function setCacher (Data_Provider_Abstract $cacher)
	{
		$this->_cacher = $cacher;
	}
	
	public function setOption ($key, $value)
	{
		switch ($key)
		{
			case "cache_provider":
				Loader::load ('Data_Provider_Manager');
				$this->setCacher (Data_Provider_Manager::get ($value));
				return;
			case "expiration":
				$this->getDefaultOptions ()->setExpiration ($value);
				return;
		}
		return parent::setOption ($key, $value);
	}
	
}