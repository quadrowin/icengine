<?php

Loader::load ('Data_Adapter_Mysqli');

/**
 *
 * @desc Адаптер для работы с mysql, с кэшированием запросов.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Data_Adapter_Mysqli_Cached extends Data_Adapter_Mysqli
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
	 * @param Query $query
	 * @param Query_Options $options
	 * @return boolean
	 */
	public function _executeChange (Query $query, Query_Options $options)
	{
		if (!mysql_query ($this->_query))
		{
			$this->_errno = mysql_errno ();
			$this->_error = mysql_error ();
			return false;
		}

		$this->_affectedRows = mysql_affected_rows ();

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
	 * @param Query $query
	 * @param Query_Options $options
	 * @return boolean
	 */
	public function _executeInsert (Query $query, Query_Options $options)
	{
		if (!mysql_query ($this->_query))
		{
			$this->_errno = mysql_errno ();
			$this->_error = mysql_error ();
			return false;
		}

		$this->_affectedRows = mysql_affected_rows ();
		$this->_insertId = mysql_insert_id ();

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
	 * @see Data_Mapper_Abstract::_executeSelect
	 * @param Query $query
	 * @param Query_Options $options
	 * @return null|array
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

		$result = mysql_query ($this->_query);

		if (class_exists ('Tracer'))
		{
			Tracer::end ($this->_query);
		}

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