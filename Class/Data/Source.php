<?php
/**
 *
 * @desc Источник данных
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Data_Source
{
	/**
	 * @desc Текущий адаптер
	 * @var Data_Adapter_Abstract
	 */
	protected $_adapter;

	/**
	 * @desc Коллекция фильтров
	 * @var Filter_Collection
	 */
	protected $_filters;

	/**
	 * @desc Модели
	 * @var Data_Mapper_Result
	 */
	protected static $_models;

	/**
	 * @desc Текущий запрос
	 * @var Query
	 */
	private $_query;

	/**
	 * @desc Результат выполнения запроса
	 * @var Query_Result
	 */
	private $_result;

	/**
	 * @desc Счетчик объектов
	 * @var integer
	 */
	protected static $_objCount = 0;

	/**
	 * @desc Текущий индекс объекта
	 * @var integer
	 */
	protected $_objIndex = null;

	public function __construct ()
	{
		$this->initFilters ();
	}

	/**
	 * @desc Проверяет доступность источника данных
	 * @return boolean
	 */
	public function available ()
	{
		return is_object ($this->_mapper);
	}

	/**
	 * @desc Наполнить источник данных
	 * @param Query $query
	 * @param Query_Options $options
	 * @return Data_Source_Abstract $this
	 */
	public function execute ($query = null, $options = null)
	{
		Loader::load ('Data_Mapper');

		$this->setQuery ($query);
		$this->setModels (Data_Mapper::getModels ());

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
		)->translate ($clone, self::$_models);

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

		$this->_result = new Query_Result (array (
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
			'source'		=> $this
		));

		return $this;
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
	 * @return integer
	 */
	public function getIndex ()
	{
		if (is_null ($this->_objIndex))
		{
			$this->_objIndex = ++self::$_objCount;
		}
		return $this->_objIndex;
	}

	/**
	 * @desc Получить текущие модели
	 * @return Data_Mapper_Result
	 */
	public function getModels ()
	{
		return self::$_models;
	}

	/**
	 * @desc Возвращает запрос
	 * @params null|string $translator
	 * 		Ожидаемый вид запроса.
	 * 		Если необходим объект запроса, ничего не указывется (по умолчанию).
	 * 		Если указать транслятор, то результом будет результат трансляции.
	 * @return Query|mixed
	 */
	public function getQuery ($translator = null)
	{
		return
			$translator ?
			$this->_query->translate ($translator) :
			$this->_query;
	}

	/**
	 * @return Query_Result
	 */
	public function getResult ()
	{
		return $this->_result;
	}

	/**
	 *
	 * @param Data_Source_Collection $sources
	 * @return Data_Source_Abstract $this
	 */
	public function setIndexSources (Data_Source_Collection $sources)
	{
		$this->_indexSources = $sources;
		return $this;
	}

	/**
	 * @desc Установить модели
	 * @param Data_Mapper_Result $models
	 */
	public function setModels (Data_Mapper_Result $models)
	{
		self::$_models = $models;
	}

	/**
	 * @desc Устанавливает результат запроса.
	 * @param Query_Result $result
	 * @return Data_Source_Abstract
	 */
	public function setResult (Query_Result $result)
	{
		$this->_result = $result;
		return $this;
	}

	/**
	 * @desc Устанавливает запрос.
	 * @param Query $query
	 * @return Data_Source_Abstract
	 */
	public function setQuery (Query $query)
	{
		$this->_query = $query;
		return $this;
	}

	/**
	 * @desc Устанавливает адаптер.
	 * @param Data_Adapter_Abstract $adapter
	 */
	public function setAdapter (Data_Adapter_Abstract $adapter)
	{
		$this->_adapter = $adapter;
		return $this;
	}

	/**
	 * @desc Проверяет, что последний запрос выполнен успешно.
	 * @return boolean
	 */
	public function success ()
	{
		if ($this->_result)
		{
			return (bool) ($this->_result->touchedRows () > 0);
		}
		return false;
	}

}