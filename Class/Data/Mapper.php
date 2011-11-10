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
	 * (non-PHPDoc)
	 */
	public function __construct ()
	{
		$this->initFilters ();
	}

	/**
	 * @desc Выполнить запрос и наполнить источник данных
	 * @param Data_Source_Abstract $source
	 * @param Query $query
	 * @param array $options
	 * @return Query_Result
	 */
	public function execute (Data_Source $source, Query $query, $options = null)
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

		$translator_result = Query_Translator::factory (
			$this->_adapter->getTranslatorName ()
		)
			->translate ($clone);

		$tables = $translator_result->getTranslator ()->getTables ();
		$vars = array ();
		foreach ($tables as $table)
		{
			$vars [$table] = Model_Scheme::table ($table);
		}

		$translator_result->applyVars ($vars);

		$result = null;

		if (!$options)
		{
			$options = $this->_adapter->getDefaultOptions ();
		}

		$translated_query = $translator_result->getTranslatedQuery ();
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