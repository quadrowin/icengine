<?php

/**
 * @desc Класс мэпера данных. Мэпер имеет адаптера, через которого он будет
 * наполнять данными источник данных. Мэпер может применять бинды запроса
 * @author Илья Колесников
 * @package IcEngine
 */
class Data_Mapper
{
	/**
	 * @desc Адаптер данных
	 * @var Data_Adapter_Abstract
	 */
	protected $_adapter;

	/**
	 * @desc Текущий оттранслированный запрос
	 * @var string
	 */
	protected $_query;

	/**
	 * (non-PHPDoc)
	 */
	public function __construct ()
	{
		$this->initFilters ();
	}

	/**
	 * @desc Подготовить строку к запросу
	 * @param string $translated_query
	 * @param Query $query
	 * @return string
	 */
	private function _prepareQuery ($translated_query, $query)
	{
		$matches = array ();
		$binds = $query->getBinds ();
		preg_match_all (
			'#{([^}]+)}#', $translated_query, $matches
		);

		$not_array = false;

		if (!empty ($matches [1][0]))
		{
			foreach ($matches [1] as $i => $bind)
			{
				if (!isset ($binds [$bind]))
				{
					$table = Model_Scheme::table ($bind);
					if ($table)
					{
						$binds [$bind] = $table;
					}
					else
					{
						continue;
					}
				}

				if (!is_array ($translated_query))
				{
					$not_array = true;
					$translated_query = array ($translated_query);
				}

				foreach ($translated_query as &$string)
				{
					$string = str_replace (
						'{' . $bind . '}',
						$binds [$bind],
						$string
					);
				}
			}
		}

		if ($not_array)
		{
			$translated_query = reset ($translated_query);
		}

		return $translated_query;
	}

	/**
	 * @desc Выполнить запрос и наполнить источник данных
	 * @param Data_Source_Abstract $source
	 * @param Query $query
	 * @param array $options
	 * @return Query_Result
	 */
	public function execute (Data_Source_Abstract $source, Query $query,
		$options = null)
	{
		if (!$this->_adapter->isConnected ())
		{
			$this->_adapter->connect ();
		}

		$start = microtime (true);

		$clone = clone $query;

		$where = $clone->getPart (Query::WHERE);
		$this->_filters->apply ($where, Query::VALUE);
		$clone->setPart (Query::WHERE, $where);

		$translated_query = $clone->translate (
			$this->_adapter->getTranslatorName ()
		);

		$translated_query = $this->_prepareQuery (
			$translated_query, $clone
		);

		$result = null;

		if (!$options)
		{
			$options = $this->_adapter->getDefaultOptions ();
		}

		$this->_adapter->setTranslatedQuery ($translated_query);

		$query_type = $query->type ();

		$default_methods = $this->_adapter->getDefaultQueryMethod ();
		$adapter_methods = $this->_adapter->getQueryMethods ();
		$method = isset ($adapter_methods [$query_type])
			? $adapter_methods [$query_type]
			: $default_methods [$query_type];

		$result = $this->_adapter->{$method} ($clone, $options);

		$errno = $this->_adapter->getErrorCode ();
		$error = $this->_adapter->getErrorMessage ();
		$affectedRows = $this->_adapter->getAffectedRowsCount ();
		$foundRows = $this->_adapter->getFoundRowsCount ();

		if ($errno)
		{
			Loader::load ('Data_Mapper_Exception');
			if (class_exists ('Debug'))
			{
				Debug::errorHandler (
					E_USER_ERROR, json_encode ($translated_query) .
					'; ' . $error,
					__FILE__, __LINE__
				);
			}
			throw new Data_Mapper_Exception (
				$error . "\n" . json_encode ($translated_query), $errno
			);
		}

		if (!$errno && is_null ($result))
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
			'foundRows'		=> $foundRows,
			'numRows'		=> $this->_adapter->getRowsCount (),
			'result'		=> $result,
			'touchedRows'	=> $foundRows + $affectedRows,
			'insertKey'		=> $this->_adapter->getLastInsertId (),
			'currency'		=> $this->_adapter->isCurrency ($result, $options),
			'source'		=> $source
		));
	}

	/**
	 * @desc Инициализировать коллекцию фильтров для запроса
	 */
	public function initFilters ()
	{
		Loader::load ('Filter_Collection');
		$this->_filters = new Filter_Collection ();
	}

	/**
	 * @desc Получить текущий адаптер
	 * @return Data_Adapter_Abstract
	 */
	public function getAdapter ()
	{
		return $this->_adapter;
	}

	/**
	 * @desc Получить текушие фильтры
	 * @return Filter_Collection
	 */
	public function getFilters ()
	{
		return $this->_filters;
	}

	/**
	 * @desc Установить адаптер
	 * @param Data_Adapter_Abstract $adapter
	 */
	public function setAdapter (Data_Adapter_Abstract $adapter)
	{
		$this->_adapter = $adapter;
	}
}