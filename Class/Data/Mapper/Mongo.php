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
	 * @var Mongo
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
	 * @return Mongo
	 */
	public function connect ($config = null)
	{
		if ($this->_connection)
		{
			return $this->_connection;
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
		
		return $this->_connection;
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
		
		$q = $clone->translate ('Mongo');
		
		$collection = $this->connect ()->selectCollection (
			$this->_connectionOptions ['database'],
			$q ['collection']
		);
		
		$found_rows = 0;
		$result = null;
		$insert_id = null;
		$tags = implode ('.', $this->_getTags ($clone));
		
		switch ($query->type ())
		{
			case Query::DELETE:
				$r = $collection->remove ($q ['criteria']);
				//Mysql::delete ($tags, $sql);
				$touched_rows = 1;
				break;
			case Query::INSERT:
				if (isset ($q ['a']['_id']))
				{
					$insert_id = $q ['a']['_id'];
					$collection->update (
						array (
							'_id'		=> $insert_id
						),
						$q ['a'],
						array (
							'upsert'	=> true
						)
					);
				}
				else
				{
					$r = $collection->insert ($q ['a']);
					$insert_id = $q ['a'] ['_id'];
				}
				
				$touched_rows = 1;
				break;
			case Query::SELECT:
				if ($q ['find_one'])
				{
					$r = $collection->findOne ($q ['query']);
				}
				else
				{
					$r = $collection->find ($q ['query']);
				}
				
				if ($query->part (Query::CALC_FOUND_ROWS))
				{
					$found_rows = $r->count ();
				}
				
				if ($q ['sort'])
				{
					$r->sort ($q ['sort']);
				}
				if ($q ['skip'])
				{
					$r->skip ($q ['skip']);
				}
				if ($q ['limit'])
				{
					$r->limit ($q ['limit']);
				}
				//$result = Mysql::select ($tags, $sql);
				$touched_rows = $r->count (true);
				
				$result = $r;
				
				break;
			case Query::SHOW:
				
				break;
			case Query::UPDATE:
				$collection->update (
					$q ['criteria'],
					$q ['newobj'],
					$q ['options']
				);
				//Mysql::update ($tags, $sql);
				$touched_rows = 1; // unknown count
				break;
		}
		
		//$error = $this->_connection->lastError ();
		
		if ($result == null)
		{
			$result = array ();
		}
		
		$finish = microtime (true);
		
		return new Query_Result (array (
			'error'			=> '',
			'errno'			=> 0,
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
