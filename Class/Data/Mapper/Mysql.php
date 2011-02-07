<?php

class Data_Mapper_Mysql extends Data_Mapper_Abstract
{
	
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
		switch ($query->type ())
		{
			case Query::DELETE:
				Mysql::delete (time (), $sql);
				$touched_rows = Mysql::affectedRows ();
				break;
			case Query::INSERT:
				$insert_id = Mysql::insert (time (), $sql);
				$touched_rows = Mysql::affectedRows ();
				break;
			case Query::SELECT; case Query::SHOW:
				$result = Mysql::select (time (), $sql);
				$touched_rows = Mysql::numRows ();
				break;
			case Query::UPDATE:
				Mysql::update (time (), $sql);
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
		
		return new Query_Result (array (
			'error'			=> $error,
			'errno'			=> $errno,
			'query'			=> $clone,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
			'result'		=> $result,
			'touchedRows'	=> $touched_rows,
			'insertKey'		=> $insert_id,
			'currency'		=> $this->_isCurrency ($result, $options),
			'source'		=> $source
		));
	}
}
