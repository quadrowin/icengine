<?php

/**
 * Мэппер для работы с MongoDB
 *
 * @author goorus, morph
 */
class Data_Mapper_Mongo extends Data_Mapper_Abstract
{
	/**
	 * Соединение с монго
     *
	 * @var Mongo
	 */
	protected $connection;

	/**
	 * Текущая коллекция
     *
	 * @var MongoCollection
	 */
	protected $collection;

	/**
	 * Параметры соединения
     *
	 * @var array
	 */
	public $connectionOptions = array(
		'host'		=> 'localhost',
		'username'	=> '',
		'password'	=> '',
		'database'	=> 'ab',
		'charset'	=> 'utf8',
		'options'	=> array()
	);

	/**
	 * Последний оттранслированный запрос
     *
	 * @var array
	 */
	protected $query;

    /**
     * Результат выполнения последнего запроса
     *
     * @var Query_Result
     */
	protected $result = null;

    /**
     * Количество затронутых документов
     *
     * @var integer
     */
	protected $touchedRows = 0;

    /**
     * Количество документов в полученной коллекции
     *
     * @var integer
     */
	protected $foundRows = 0;

    /**
     * Id последнего созданного документа
     *
     * @var integer
     */
	protected $insertId = null;

	/**
	 * ОбMongoработчики по видам запросов
     *
	 * @var array
	 */
	protected $queryMethods = array (
		Query::SELECT	=> '_executeSelect',
		Query::SHOW		=> '_executeShow',
		Query::DELETE	=> '_executeDelete',
		Query::UPDATE	=> '_executeUpdate',
		Query::INSERT	=> '_executeInsert'
	);

	/**
	 * Запрос на удаление
	 *
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 */
	public function _executeDelete(Query_Abstract $query,
		Query_Options $options)
	{
		$this->query['criteria']['_id'] = $this->normalizeId(
			$this->query['criteria']['_id']
		);
		$this->collection->remove(
            $this->query['criteria'], $this->query['options']
		);
		$this->touchedRows = 1;
	}

	/**
	 * Запрос на вставку
     * 
     * @param Query_Abstract $query
	 * @param Query_Options $options
	 */
	public function _executeInsert(Query_Abstract $query,
		Query_Options $options)
	{
		if (isset($this->query['a']['_id'])) {
			$this->insertId = $this->query['a']['_id'];
			$this->collection->update(
				array(
					'_id'		=> $this->insertId
				),
				$this->query['a'],
				array(
					'upsert'	=> true
				)
			);
		} else {
			$this->collection->insert($this->query['a']);
			$this->insertId = $this->query['a']['_id'];
		}
		$this->touchedRows = 1;
	}

	/**
	 * Запрос на выборку
     * 
     * @param Query_Abstract $query
	 * @param Query_Options $options
	 */
	public function _executeSelect(Query_Abstract $query,
		Query_Options $options)
	{
		if ($this->query['find_one']) {
			$row = $this->collection->findOne($this->query['query']);
			$this->result = array();
			if ($row) {
				$this->result[] = $row;
			}
		} else {
			$r = $this->collection->find($this->query['query']);
			if ($this->query[Query::CALC_FOUND_ROWS]) {
				$this->foundRows = $r->count();
			}
			if ($this->query['sort']) {
				$r->sort($this->query['sort']);
			}
			if ($this->query['skip']) {
				$r->skip($this->query['skip']);
			}
			if ($this->query['limit']) {
				$r->limit($this->query['limit']);
			}
			$this->touchedRows = $r->count(true);
			$this->result = array();
			foreach ($r as $tr) {
				$this->result[] = $tr;
			}
		}
	}

	/**
	 * Служебный тип запроса
     *
	 * @param Query $query
	 * @param Query_Options $options
	 */
	public function _executeShow(Query_Abstract $query, Query_Options $options) 
    {
		$show = strtoupper($this->query['show']);
		if ($show == 'DELETE_INDEXES') {
			$this->result = array($this->collection->deleteIndexes());
		} elseif ($show == 'ENSURE_INDEXES') {
			// Создание индексов
            $locator = IcEngine::serviceLocator();
            $scheme = $locator->getService('modelScheme');
			$result = $scheme->scheme($this->query['model']);
			$this->result = $result['indexes'];
			foreach ($this->result as $key) {
				$options = array();
                $temp = $key[1];
				if ($key[0] == 'Primary') {
					$options['unique'] = true;
				}
				$keys = array();
				foreach ($temp as $index) {
					$keys[$index] = 1;
				}
				$this->collection->ensureIndex($keys, $options);
			}
		}
	}

	/**
	 * Запрос на обновление
	 *
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return void
	 */
	public function _executeUpdate(Query_Abstract $query, 
        Query_Options $options)
	{
		$this->query['criteria']['_id'] = $this->normalizeId(
			$this->query['criteria']['_id']
		);
		$this->collection->update(
            $this->query['criteria'],
            $this->query['newobj'],
            $this->query['options']
		);
		$this->touchedRows = 1;
	}

	/**
	 * Подключение к БД
     *
	 * @param Objective|array $config [optional]
	 * @return Mongo
	 */
	public function connect($config = null)
	{
		if ($this->connection) {
			return $this->connection;
		}
		if ($config) {
			$this->setOption($config);
		}
		$url = 'mongodb://';
		if ($this->connectionOptions['username'] &&
			$this->connectionOptions['password']) {
			$url .=
				$this->connectionOptions['username'] . ':' .
				$this->connectionOptions['password'] . '@';
		}
		$url .= $this->connectionOptions['host'];
		$options = array('connect'	=> true);
		if (isset($this->connectionOptions['options']['replicaSet'])) {
			$options['replicaSet'] =
                $this->connectionOptions['options']['replicaSet'];
		}
		$this->connection = new Mongo($url, $options);
		$this->connection->selectDB($this->connectionOptions['database']);
		return $this->connection;
	}

    /**
     * @inheritdoc
     */
	public function execute(Data_Source_Abstract $source, Query_Abstract $query,
		$options = null)
	{
		if (!($query instanceof Query_Abstract)) {
			return new Query_Result(null);
		}
		$start = microtime(true);
		$this->query = $query->translate('Mongo');
		$this->collection = $this->connect()->selectCollection(
			$this->connectionOptions['database'],
			$this->query['collection']
		);
		$this->result = array();
		$this->touchedRows = 0;
		$this->foundRows = 0;
		$this->insertId = null;
		if (!$options) {
			$options = $this->getDefaultOptions();
		}
		$m = $this->queryMethods[$query->type()];
		$this->{$m}($query, $options);
		$finish = microtime(true);
		return new Query_Result(array(
			'error'			=> '',
			'errno'			=> 0,
			'query'			=> $query,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
			'foundRows'		=> $this->foundRows,
			'result'		=> $this->result,
			'touchedRows'	=> $this->touchedRows,
			'insertKey'		=> $this->insertId,
			'currency'		=> 1,
			'source'		=> $source
		));
	}

	/**
	 * Возвращает ресурс соединения с mongo
     *
	 * @return resource
	 */
	public function linkIdentifier()
	{
		$this->connect();
		return $this->connection;
	}

	/**
	 * Преобразует входящий ключ
	 *
	 * @param mixed $value
	 * @return MongoId
	 */
	public function normalizeId($data)
	{
		if (is_array($data) && isset($data['$id'])) {
			$id = (string) $data['$id'];
			return new MongoId($id);
		}
		return $data;
	}

	/**
	 * @inheritdoc
	 */
	public function setOption($key, $value = null)
	{
		if (is_array($key) || !is_scalar($key)) {
			foreach ($key as $k => $v) {
				$this->setOption($k, $v);
			}
			return;
		}
		$locator = IcEngine::serviceLocator();
		$cryptManager = $locator->getService('cryptManager');
		if (isset($this->connectionOptions[$key])) {
			$this->connectionOptions[$key] = $cryptManager->autoDecode($value);
			return;
		}
		return parent::setOption($key, $value);
	}
}