<?php

Loader::load ('Data_Adapter_Abstract');

/**
 * @desc Адаптер для соеденения с pdo
 * @author Илья Колесников, Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Adapter_Pdo extends Data_Adapter_Abstract
{
	/**
	 * @see Data_Abapter_Abstract::_connectionOptions
	 * @var array
	 */
	protected $_connectionOptions = array (
		'type'		=> 'mysql',
		'host'		=> 'localhost',
		'username'	=> '',
		'password'	=> '',
		'database'	=> 'unknown',
		'charset'	=> 'utf8'
	);

	/**
	 * @see Data_Abapter_Abstract::_translatorName
	 * @var string
	 */
	protected $_translatorName = 'Mysql';

	/**
	 * @desc Запрос на получения количество записей
	 */
	const SELECT_FOUND_ROWS_QUERY = 'SELECT FOUND_ROWS()';

	/**
	 * @see Data_Adapter_Abstract::_executeChange
	 * @param Query $query Запрос
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	public function _executeChange (Query $query, Query_Options $options)
	{
		$this->_affectedRows = $this->_connection->exec ($query);
		$error = $this->_connection->errorInfo ();
		if ($error)
		{
			$this->_errno = $error [0];
			$this->_error = $error [2];
			return false;
		}
		return true;
	}

	/**
	 * @see Data_Adapter_Abstract::_executeInsert
	 * @param Query $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	public function _executeInsert (Query $query, Query_Options $options)
	{
		$this->_affectedRows = $this->_connection->exec ($query);
		$error = $this->_connection->errorInfo ();
		if ($error)
		{
			$this->_errno = $error [0];
			$this->_error = $error [2];
			return false;
		}
		$this->_insertId = $this->_connection->lastInsertId ();
		return true;
	}

	/**
	 * @see Data_Adapter_Abstract::_executeSelect
	 * @param Query $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return array|null
	 */
	public function _executeSelect (Query $query, Query_Options $options)
	{
		$statement = $this->_connection->prepare ($this->_query)->execute ();
		$rows = $statement->fetchAll ();

		if (!$rows)
		{
			$rows = array ();
		}

		$this->_numRows = count ($rows);

		if ($query->part (Query::CALC_FOUND_ROWS))
		{
			$statement = $this->_connection->prepare ($this->_query)->execute (
				self::SELECT_FOUND_ROWS_QUERY
			);
			$row = $statement->fetch ();
			$this->_foundRows = reset ($row);
		}

		return $rows;
	}

	/**
	 * @see Data_Adapter_Abstract::isCurrency
	 */
	public function isCurrency ($result, $options)
	{
		if (!$options)
		{
			return true;
		}

		return $options->getNotEmpty () && empty ($result) ? false : true;
	}

	/**
	 * @see Data_Adapter_Abstract::connect
	 * @param Objective|array $config [optional]
	 */
	public function connect ($config = null)
	{
		if ($this->_connection)
		{
			return ;
		}

		if ($config)
		{
			$this->setOption ($config);
		}

		$db_string = $this->_connectionOptions ['type'] . ':host=' .
			$this->_connectionOptions ['host'] . ';dbname=' .
			$this->_connectionOptions ['database'];

		$this->_connection = new PDO (
			$db_string,
			$this->_connectionOptions ['username'],
			$this->_connectionOptions ['password']
		);

		if ($this->_connectionOptions ['charset'])
		{
			$this->_connection->exec (
				'SET NAMES ' . $this->_connectionOptions ['charset']
			);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Adapter_Abstract::setOption
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
			$this->_connectionOptions [$key] =
				Crypt_Manager::autoDecode ($value);
			return;
		}
		return parent::setOption ($key, $value);
	}
}