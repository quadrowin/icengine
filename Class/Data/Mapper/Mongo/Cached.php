<?php

/**
 * Мэппер для работы с Mongodb, с кэшированием запросов
 *
 * @author goorus, morph
 */
class Data_Mapper_Mongo_Cached extends Data_Mapper_Mongo
{
	/**
	 * Кэшер запросов
     *
	 * @var Data_Provider_Abstract
	 */
	protected $cacher;

	/**
	 * Получение хэша запроса
     *
	 * @return string
	 */
	protected function queryHash()
	{
		return md5(json_encode($this->query));
	}

	/**
	 * @inheritdoc
	 */
	public function _executeDelete(Query_Abstract $query, Query_Options $options)
	{
        $this->collection = $this->connect()->selectCollection(
			$this->connectionOptions['database'],
			$this->query['collection']
		);
		parent::_executeDelete($query, $options);
		$tags = $query->getTags();
		for ($i = 0, $count = sizeof($tags); $i < $count; ++$i) {
			$this->cacher->tagDelete($tags[$i]);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function _executeInsert(Query_Abstract $query, Query_Options $options)
	{
        $this->collection = $this->connect()->selectCollection(
			$this->connectionOptions['database'],
			$this->query['collection']
		);
		parent::_executeInsert($query, $options);
		$tags = $query->getTags();
		for ($i = 0, $count = sizeof($tags); $i < $count; ++$i) {
			$this->cacher->tagDelete($tags [$i]);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function _executeSelect(Query_Abstract $query, Query_Options $options)
	{
		$key = $this->queryHash();
		$expiration = $options->getExpiration();
		$cache = $this->cacher->get($key);
		$useCache = false;
		if ($cache) {
            if ($cache['a'] + $expiration > time() || $expiration == 0) {
                if ($this->cacher->checkTags($cache['t'])) {
                    $useCache = true;
                }
            }
		}
		if ($useCache) {
			$this->foundRows = $cache['f'];
			$this->result = $cache['v'];
            return;
		}
        $this->collection = $this->connect()->selectCollection(
			$this->connectionOptions['database'],
			$this->query['collection']
		);
		parent::_executeSelect($query, $options);
		$tags = $query->getTags();
		$this->cacher->set(
			$key,
			array(
				'v' => $this->result,
				'a' => time(),
				't' => $this->cacher->getTags($tags),
				'f'	=> $this->foundRows
			)
		);
	}

    /**
     * @inheritdoc
     */
    public function _executeShow(Query_Abstract $query, Query_Options $options) {
        $this->connect();
        parent::_executeShow($query, $options);
    }

	/**
	 * @inheritdoc
	 */
	public function _executeUpdate(Query_Abstract $query, Query_Options $options)
	{
        $this->collection = $this->connect()->selectCollection(
			$this->connectionOptions['database'],
			$this->query['collection']
		);
		parent::_executeUpdate($query, $options);
		$tags = $query->getTags();
		for ($i = 0, $count = sizeof($tags); $i < $count; ++$i) {
			$this->cacher->tagDelete($tags[$i]);
		}
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
		$start = microtime (true);
		$this->query = $query->translate('Mongo');
		$this->result = array();
		$this->touchedRows = 0;
		$this->foundRows = 0;
		$this->insertId = null;
		if (!$options) {
			$options = $this->getDefaultOptions();
		}
		$m = $this->queryMethods[$query->type()];
		$this->{$m}($query, $options);
		$finish = microtime (true);
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
     * Получить кэшер запросов
     *
	 * @return Data_Provider_Abstract
	 */
	public function getCacher()
	{
		return $this->cacher;
	}

	/**
	 * Изменить кэшер запросов
     *
	 * @param Data_Provider_Abstract $cacher
	 */
	public function setCacher(Data_Provider_Abstract $cacher)
	{
		$this->cacher = $cacher;
	}

	/**
	 * @inheritdoc
	 */
	public function setOption($key, $value = null)
	{
		switch ($key) {
			case 'cache_provider':
                $serviceLocator = IcEngine::serviceLocator();
                $dataProviderManager = $serviceLocator->getService(
                    'dataProviderManager'
                );
				$this->setCacher($dataProviderManager->get($value));
				return;
			case 'expiration':
				$this->getDefaultOptions()->setExpiration($value);
				return;
		}
		return parent::setOption($key, $value);
	}
}