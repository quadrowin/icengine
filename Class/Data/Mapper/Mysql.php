<?php

class Data_Mapper_Mysql extends Data_Mapper_Abstract
{
	
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
		
		return array_unique ($tags);
	}
	
	/**
	 * 
	 * @param mixed $result
	 * @param mixed $options
	 * @return boolean
	 */
	protected function _isCurrency ($result, $options)
	{
		if (!$options)
		{
			return true;
		}

		return $options->getNotEmpty () && empty ($result) ? false : true;
	}
	
	public function execute (Data_Source_Abstract $source, Query $query, $options = null)
	{
		if (!($query instanceof Query))
		{
			return new Query_Result (null);
		}
		
		$start = microtime (true);
		
		$clone = clone $query;
		
		$where = $clone->getPart (Query::WHERE);
		$this->_filters->apply ($where, Query::VALUE);
		$clone->setPart (Query::WHERE, $where);
		
		$sql = $clone->translate ('Mysql', $this->_modelScheme);
		
		$result = null;
		$insert_id = null;
		$tags = implode ('.', $this->_getTags ($clone));
		switch ($query->type ())
		{
			case Query::DELETE:
				Mysql::delete ($tags, $sql);
				$touched_rows = Mysql::affectedRows ();
				break;
			case Query::INSERT:
				$insert_id = Mysql::insert ($tags, $sql);
				$touched_rows = Mysql::affectedRows ();
				break;
			case Query::SELECT; case Query::SHOW:
				$result = Mysql::select ($tags, $sql);
				$touched_rows = Mysql::numRows ();
				break;
			case Query::UPDATE:
				Mysql::update ($tags, $sql);
				$touched_rows = Mysql::affectedRows ();
				break;
		}
		$errno = mysql_errno ();
		$error = mysql_error ();
		
		if (!empty ($errno))
		{
			Loader::load ('Data_Mapper_Mysql_Exception');
			throw new Data_Mapper_Mysql_Exception ($error . "\n$sql", $errno);
		}
		
		if (empty ($errno) && is_null ($result))
		{
			$result = array ();
		}
		
		$finish = microtime (true);
		
		$found_rows = 0;
		if (
			$query->part (Query::CALC_FOUND_ROWS) &&
			method_exists ('Mysql', 'foundRows')
		)
		{
			$found_rows = Mysql::foundRows ();
		}
		
		return new Query_Result (array (
			'error'			=> $error,
			'errno'			=> $errno,
			'query'			=> $clone,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
			'foundRows'		=> $found_rows,
			'result'		=> $result,
			'touchedRows'	=> $touched_rows,
			'insertKey'		=> $insert_id,
			'currency'		=> $this->_isCurrency ($result, $options),
			'source'		=> $source
		));
	}
}
