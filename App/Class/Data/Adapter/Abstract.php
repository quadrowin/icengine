<?php

namespace Ice;

/**
 *
 * @desc Абстрактный адаптер данных. Служит для выполнения запросов
 * @author Илья Колесников
 * @package Ice
 *
 */
class Data_Adapter_Abstract
{
	/**
	 * @desc Настройки адаптера по умолчанию
	 * @var Query_Options
	 */
	protected $_defaultOptions;

	/**
	 * @desc Затронуто сущностей при изменение
	 * @var integer
	 */
	protected $_affectedRows = 0;

	/**
	 * @desc Текущее соединения адаптера
	 * @var resource
	 */
	protected $_connection = null;

	/**
	 * @desc Текущие настройки адаптера
	 * @var array
	 */
	protected $_connectionOptions;

	/**
	 * @desc Обработчики по видам запросов.
	 * @var array
	 */
	protected $_defaultQueryMethods = array (
		Query::SELECT	=> '_executeSelect',
		Query::SHOW		=> '_executeSelect',
		Query::DELETE	=> '_executeChange',
		Query::UPDATE	=> '_executeChange',
		Query::INSERT	=> '_executeInsert'
	);

	/**
	 * @desc Код ошибки
	 * @var integer
	 */
	protected $_errno = 0;

	/**
	 * @desc Сообщение об ошибке
	 * @var string
	 */
	protected $_error = '';

	/**
	 * @desc Фильтры запроса
	 * @var Filter_Collection
	 */
	protected $_filters;

	/**
	 * @desc Общее количество найденных сущностей
	 * попадающих под запрос. Игнорирует лимиты
	 * @var integer
	 */
	protected $_foundRows = 0;

	/**
	 * @desc Id созданной адаптером сущности
	 * @var mixed
	 */
	protected $_insertId = null;

	/**
	 * @desc Найдено сущностей
	 * @var integer
	 */
	protected $_numRows = 0;

	/**
	 * @desc Оттранслированный запрос
	 * @var mixed
	 */
	protected $_query;

	/**
	 * @desc Методы для выполнения операций
	 * @var array
	 */
	protected $_queryMethods;

	/**
	 * @desc Название транслятора
	 * @var string
	 */
	protected $_translatorName;

	/**
	 * @desc Выполнить запрос на изменение сущности/удаление
	 * @param Query $query
	 * @param Query_Options $options
	 * @return mixed
	 */
	public function _executeChange (Query $query, Query_Options $options)
	{

	}

	/**
	 * @desc Выполнить запрос на создание новой сущности
	 * @param array $query
	 * @param array $options
	 * @return mixed
	 */
	public function _executeInsert (Query $query, Query_Options $options)
	{

	}

	/**
	 * @desc Выполнить запрос на получения списка сущностей
	 * @param array $query
	 * @param array $options
	 * @return mixed
	 */
	public function _executeSelect (Query $query, Query_Options $options)
	{

	}

	/**
	 * @desc Установить подключение
	 * @return mixed
	 */
	public function connect ()
	{
		return null;
	}

	/**
	 * @desc Проверить установлено ли соедидение адаптером
	 * @return boolean
	 */
	public function isConnected ()
	{
		return (bool) $this->_connection;
	}

	/**
	 * @desc Определить актуальность полученных адаптером данных
	 * @param array $result
	 * @param array $options
	 * @return boolean
	 */
	public function isCurrency ($result, $options)
	{
		return true;
	}

	/**
	 * @desc Получить количество сущностей затронутых последним запросом
	 * на изменение/удаление
	 * @return integer
	 */
	public function getAffectedRowsCount ()
	{
		return $this->_affectedRows;
	}

	/**
	 * @desc Получить ресурс текущего подключения
	 * @return resource
	 */
	public function getConnection ()
	{
		return $this->_connection;
	}

	/**
	 * @desc Получить текущие настройки адаптера
	 * @return array
	 */
	public function getConnectionOptions ()
	{
		return $this->_connectionOptions;
	}

	/**
	 * @desc Получить настройки по умолчанию
	 * @return Query_Option
	 */
	public function getDefaultOptions ()
	{
		if (!$this->_defaultOptions)
		{
			$this->_defaultOptions = new Query_Options ();
		}
		return $this->_defaultOptions;
	}

	/**
	 * @desc Получить методы операций по умолчани
	 * @return array
	 */
	public function getDefaultQueryMethod ()
	{
		return $this->_defaultQueryMethods;
	}

	/**
	 * @desc Получить код ошибки последнего запроса
	 * @return integer
	 */
	public function getErrorCode ()
	{
		return $this->_errno;
	}

	/**
	 * @desc Получить сообщение ошибки последнего запроса
	 * @return string
	 */
	public function getErrorMessage ()
	{
		return $this->_error;
	}

	/**
	 * @desc Получить количество найденных сущностей и игнорированием лимитов
	 * @return integer
	 */
	public function getFoundRowsCount ()
	{
		return $this->_foundRows;
	}

	/**
	 * @desc Получить id сущностей, созданной последним запросом
	 * @return mixed
	 */
	public function getLastInsertId ()
	{
		return $this->_insertId;
	}

	public function getQueryMethods ()
	{
		return $this->_queryMethods;
	}

	/**
	 * @desc Получить количество найденных сущностей
	 * @return integer
	 */
	public function getRowsCount ()
	{
		return $this->_numRows;
	}

	/**
	 * @desc Получить оттранслированный запрос
	 * @return mixed
	 */
	public function getTranslatedQuery ()
	{
		return $this->_query;
	}

	/**
	 * @desc Получить название транслятора
	 * @return string
	 */
	public function getTranslatorName ()
	{
		return $this->_translatorName;
	}

	/**
	 * @desc 
	 * @param resource $connection
	 */
	public function setConnection ($connection)
	{
		$this->_connection = $connection;
	}

	/**
	 * @desc Изменить значение настройки
	 * @param string $option
	 * @param mixed $value
	 */
	public function setOption ($option, $value)
	{
		$this->_connectionOptions [$option] = $value;
	}

	/**
	 * @desc Изменить оттранслированный запрос
	 * @param mixed $query
	 */
	public function setTranslatedQuery ($query)
	{
		$this->_query = $query;
	}

	/**
	 * @desc Получить значение настройки подключения
	 * @param string $name
	 */
	public function option ($name)
	{
		return isset ($this->_connectionOptions [$name])
			? $this->_connectionOptions [$name]
			: null;
	}
}