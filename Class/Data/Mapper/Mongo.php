<?php
/**
 * 
 * @desc Мэппер для работы с MongoDB
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Mapper_Mongo extends Data_Mapper_Abstract
{
	
	/**
	 * @desc Соединение с монго.
	 * @var resource
	 */
	protected $_connection;
	
	/**
	 * @desc Параметры соединения
	 * @var array
	 */
	public $_connectionOptions = array (
		'host'		=> 'localhost',
		'username'	=> '',
		'password'	=> '',
		'database'	=> 'unknown',
		'charset'	=> 'utf8'
	);
	
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
			$tags [] = Model_Scheme::table ($info [Query::TABLE]);
		}
		
		$insert = $query->getPart (QUERY::INSERT);
		if ($insert)
		{
	   		$tags [] = Model_Scheme::table ($insert);
		}
	   	
		$update = $query->getPart (QUERY::UPDATE);
		if ($update)
		{
			$tags [] = Model_Scheme::table ($update);
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
	
	/**
	 * @desc Подключение к БД
	 * @param Objective|array $config [optional]
	 */
	public function connect ($config = null)
	{
		if ($this->_linkIdentifier)
		{
			return ;
		}
		
		if ($config)
		{
			$this->setOption ($config);
		}
		
		$url = 'mongodb://';
		if (
			$this->_connectionOptions ['username'] && 
			$this->_connectionOptions ['password']
		)
		{
			$url .= 
				$this->_connectionOptions ['username'] . ':' . 
				$this->_connectionOptions ['password'] . '@';
		}
		$url .= $this->_connectionOptions ['host'];
		$this->_connection = new Mongo ($url, array ("connect" => true));		
		$this->_connection->selectDB ($this->_connectionOptions ['database']);
		
//		$this->_collectionName = $config ['collection'];
//		$this->_collection = $this->_connection->selectCollection (
//			$this->_databaseName,
//			$this->_collectionName
//		);
//		$this->_collection->ensureIndex (
//			array ('k' => 1),
//			array ('unique' => true)
//		);
	}
	
	public function execute (Data_Source_Abstract $source, Query $query, 
		$options = null)
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
		
		$sql = $clone->translate ('Mongo');
		
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
	
	/**
	 * @desc Возвращает ресурс соединения с mysql.
	 * @return resource
	 */
	public function linkIdentifier ()
	{
		$this->connect ();
		return $this->_linkIdentifier;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::setOption()
	 */
	public function setOption ($key, $value = null)
	{
		if (is_array ($key) || !is_scalar ($key))
		{
			foreach ($key as $k => $v)
			{
				$this->setOption ($k, $v);
			}
			return;
		}
		
		if (isset ($this->_connectionOptions [$key]))
		{
			Loader::load ('Crypt_Manager');
			$this->_connectionOptions [$key] = Crypt_Manager::autoDecode ($value);
			return;
		}
		return parent::setOption ($key, $value);
	}
	
}
