<?php

class Data_Mapper_Mysqli extends Data_Mapper_Abstract
{
    
    const SELECT_FOUND_ROWS_QUERY = 'SELECT FOUND_ROWS()';
    
    /**
     * Параметры соединения
     * @var array
     */
    protected $_connectionOptions = array (
    	'host'		=> 'localhost',
    	'username'	=> '',
    	'password'	=> '',
    	'database'	=> 'unknown',
    	'charset'	=> 'utf8'
    );
    
    /**
     * Последний оттранслированный запрос.
     * @var string
     */
    protected $_sql = '';
	
    protected $_errno = 0;
    protected $_error = '';
    
    protected $_affectedRows = 0;
    protected $_foundRows = 0;
    protected $_numRows = 0;
    protected $_insertId = null;
    
    /**
     * Обработчики по видам запросов.
     * @var array
     */
    protected $_queryMethods = array (
        Query::SELECT    => '_executeSelect',
        Query::SHOW      => '_executeSelect',
        Query::DELETE    => '_executeChange',
        Query::UPDATE    => '_executeChange',
        Query::INSERT    => '_executeInsert'
    );
    
    protected function _executeChange (Query $query, Query_Options $options)
    {
        if (!mysql_query ($this->_sql))
        {
            $this->_errno = mysql_errno ();
            $this->_error = mysql_error ();
            return false;
        }
        
		$this->_affectedRows = mysql_affected_rows ();
				
		return true;
	}
	
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
		
		return true;
	}
    
	/**
	 * 
	 * 
	 * @param Query $query
	 * @param Query_Options $options
	 */
    protected function _executeSelect (Query $query, Query_Options $options)
    {
        $result = mysql_query ($this->_sql);
        
        if (!$result)
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
		
        return $rows;
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
	
	/**
	 * Подключение к БД
	 * @param Config_Array $config
	 */
	public function connect (Config_Array $config)
	{
	    mysql_connect ($config->server, $config->username, $config->password);
	    mysql_select_db ($config->database);
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
		
		$this->_sql = $clone->translate ('Mysql', $this->_modelScheme);
		
		if (false)
		{
		    $f = fopen ('cache/sql.txt', 'ab');
		    fwrite ($f, $this->_sql . "\r\n");
		    fclose ($f);
		}
		
		$result = null;
		$this->_errno = 0;
		$this->_error = '';
		$this->_affectedRows = 0;
		$this->_foundRows = 0;
		$this->_numRows = 0;
		$this->_insertId = null;
		
		if (!$options)
		{
		    $options = $this->getDefaultOptions ();
		}
		
		$m = $this->_queryMethods [$query->type ()];
		$result = $this->{$m} ($query, $options);
		
		if ($this->_errno)
		{
			Loader::load ('Data_Mapper_Mysqli_Exception');
			throw new Data_Mapper_Mysqli_Exception (
			    $this->_error . "\n" . $this->_sql,
			    $this->_errno
			);
		}
		
		if (!$this->_errno && is_null ($result))
		{
			$result = array ();
		}
		
		$finish = microtime (true);
		
		return new Query_Result (array (
			'error'			=> $this->_error,
			'errno'			=> $this->_errno,
			'query'			=> $clone,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
		    'foundRows'		=> $this->_foundRows,
			'result'		=> $result,
			'touchedRows'	=> $this->_numRows + $this->_affectedRows,
			'insertKey'		=> $this->_insertId,
			'currency'		=> $this->_isCurrency ($result, $options),
			'source'		=> $source
		));
	}
	
	public function setOption ($key, $value)
	{
		if (isset ($this->_connectionOptions [$key]))
		{
			$this->_connectionOptions [$key] = $value;
			return;
		}
		return parent::setOption ($key, $value);
			
	}
}
