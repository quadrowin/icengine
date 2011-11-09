<?php

Loader::load ('Data_Adapter_Abstract');

/**
 * @desc Адаптер для соеденения с mysql
 * @author Илья Колесников, Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Adapter_Mysqli extends Data_Adapter_Abstract
{
	/**
	 * @see Data_Abapter_Abstract::_connectionOptions
	 * @var array
	 */
	protected static $_connectionOptions = array (
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
	protected function _executeChange (Query $query, Query_Options $options)
	{
		if (!mysql_query ($this->_query, $this->_connection))
		{
			$this->_errno = mysql_errno ($this->_connection);
			$this->_error = mysql_error ($this->_connection);
			return false;
		}

		$this->_affectedRows = mysql_affected_rows ($this->_connection);

		return true;
	}

	/**
	 * @see Data_Adapter_Abstract::_executeInsert
	 * @param Query $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	protected function _executeInsert (Query $query, Query_Options $options)
	{
		if (!mysql_query ($this->_query, $this->_connection))
		{
			$this->_errno = mysql_errno ($this->_connection);
			$this->_error = mysql_error ($this->_connection);
			return false;
		}

		$this->_affectedRows = mysql_affected_rows ($this->_connection);

		$this->_insertId = mysql_insert_id ($this->_connection);

		return true;
	}

	/**
	 * @see Data_Adapter_Abstract::_executeSelect
	 * @param Query $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return array|null
	 */
	protected function _executeSelect (Query $query, Query_Options $options)
	{
		$result = mysql_query ($this->_query, $this->_connection);

		if (!$result)
		{
			$this->_errno = mysql_errno ($this->_connection);
			$this->_error = mysql_error ($this->_connection);
			return null;
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
			$result = mysql_query (
				self::SELECT_FOUND_ROWS_QUERY,
				$this->_connection
			);
			$row = mysql_fetch_row ($result);
			$this->_foundRows = reset ($row);
			mysql_free_result ($result);
		}

		return $rows;
	}

	/**
	 * @see Data_Adapter_Abstract::isCurrency
	 */
	protected function isCurrency ($result, $options)
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

		$this->_connection = mysql_connect (
			$this->_connectionOptions ['host'],
			$this->_connectionOptions ['username'],
			$this->_connectionOptions ['password']
		);

		mysql_select_db (
			$this->_connectionOptions ['database'],
			$this->_connection
		);

		if ($this->_connectionOptions ['charset'])
		{
			mysql_query (
				'SET NAMES ' . $this->_connectionOptions ['charset'],
				$this->_connection
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

	/**
	 * @see Data_Adapter_Abstract::setTranslatedQuery
	 * @param string $query
	 */
	public function setTranslatedQuery ($query)
	{
		$this->_sql = $query;
	}

}
