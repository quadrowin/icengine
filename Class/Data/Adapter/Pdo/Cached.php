<?php

Loader::load ('Data_Adapter_Pdo');

/**
 * @desc Адаптер для соеденения с pdo с кэшированием
 * @author Илья Колесников, Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Adapter_Pdo_Cached extends Data_Adapter_Pdo
{
	/**
	 * @desc Кэшер запросов.
	 * @var Data_Provider_Abstract
	 */
	protected $_cacher;

	/**
	 * @desc Получение хэша запроса
	 * @return string
	 */
	protected function _sqlHash ()
	{
		return md5 ($this->_query);
	}

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
		if ($this->_affectedRows > 0)
		{
			$tags = $query->getTags ();

			for ($i = 0, $count = sizeof ($tags); $i < $count; ++$i)
			{
				$this->_cacher->tagDelete ($tags [$i]);
			}
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
		if ($this->_affectedRows > 0)
		{
			$tags = $query->getTags ();

			for ($i = 0, $count = sizeof ($tags); $i < $count; $i++)
			{
				$this->_cacher->tagDelete ($tags [$i]);
			}
		}
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
		$key = $this->_sqlHash ();

		$expiration = $options->getExpiration ();

		$cache = $this->_cacher->get ($key);

		$use_cache = false;

		if ($cache)
		{
			if (
	   			($cache ['a'] + $expiration > time () || $expiration == 0) &&
				$this->_cacher->checkTags ($cache ['t'])
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

		if (class_exists ('Tracer'))
		{
			Tracer::begin (
				__CLASS__,
				__METHOD__,
				__LINE__
			);
		}

		$statement = $this->_connection->prepare ($this->_query);
		$statement->execute ();

		if (class_exists ('Tracer'))
		{
			Tracer::end ($this->_query);
		}

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

		$tags = $query->getTags ();

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

		return $rows;
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

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Mysqli::setOption()
	 */
	public function setOption ($key, $value = null)
	{
		switch ($key)
		{
			case 'cache_provider':
				Loader::load ('Data_Provider_Manager');
				$this->setCacher (Data_Provider_Manager::get ($value));
				return;
			case 'expiration':
				$this->getDefaultOptions ()->setExpiration ($value);
				return;
		}
		return parent::setOption ($key, $value);
	}
}