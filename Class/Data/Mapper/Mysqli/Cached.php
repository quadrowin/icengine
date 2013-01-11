<?php

/**
 * Мэппер для работы с mysql, с кэшированием запросов.
 *
 * @author goorus, morph
 */
class Data_Mapper_Mysqli_Cached extends Data_Mapper_Mysqli
{
	/**
	 * Кэшер запросов.
	 *
     * @var Data_Provider_Abstract
	 */
	protected $cacher;

    /**
     * Кэши, уже полученные из провайдера
     *
     * @var array
     */
    protected static $caches = array();

    /**
     * Кэш запросов по тэгам
     *
     * @var array
     */
    protected static $tagsCaches = array();

    /**
     * Валидны ли тэги
     *
     * @var array
     */
    protected static $tagsValid = array();

	/**
	 * Получение хэша запроса
	 *
     * @return string
	 */
	protected function sqlHash($query)
	{
		return md5(json_encode($query->getParts()));
	}

	/**
	 * @inheritdoc
	 */
	protected function _executeChange(Query_Abstract $query,
        Query_Options $options)
	{
		if (!$this->linkIdentifier) {
			$this->connect();
		}
		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}
        $result = parent::_executeChange($query, $options);
        if (!$result) {
            return false;
        }
		if (Tracer::$enabled) {
			$endTime = microtime(true);
			$delta = $endTime - $startTime;
			if ($query instanceof Query_Delete) {
				Tracer::incDeleteQueryCount();
				Tracer::incDeleteQueryTime($delta);
			} else {
				Tracer::incUpdateQueryCount();
				Tracer::incUpdateQueryTime($delta);
			}
			Tracer::incDeltaQueryCount();
		}
		if ($this->affectedRows > 0) {
			$tags = $query->getTags();
			for ($i = 0, $count = sizeof($tags); $i < $count; ++$i) {
                $tag = $tags[$i];
				$this->cacher->tagDelete($tag);
                $this->tagDelete($tag);
			}
		}
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function _executeInsert(Query_Abstract $query,
        Query_Options $options)
	{
		if (!$this->linkIdentifier) {
			$this->connect();
		}
		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}
		$result = parent::_executeInsert($query, $options);
        if (!$result) {
            return false;
        }
		if (Tracer::$enabled) {
			$endTime = microtime(true);
			$delta = $endTime - $startTime;
			Tracer::incUpdateQueryCount();
			Tracer::incUpdateQueryTime($delta);
			Tracer::incDeltaQueryCount();
		}
		if ($this->affectedRows > 0) {
			$tags = $query->getTags();
			for ($i = 0, $count = sizeof($tags); $i < $count; $i++) {
				$tag = $tags[$i];
                $this->cacher->tagDelete($tag);
                $this->tagDelete($tag);
			}
		}
		return true;
	}

	/**
	 * @desc Выполняет запрос на получение данных.
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return null|array
	 */
	protected function _executeSelect(Query_Abstract $query,
        Query_Options $options)
	{
		if (Tracer::$enabled) {
			Tracer::incSelectQueryCount();
		}
		$key = $this->sqlHash($query);
		$expiration = $options->getExpiration();
        if (!isset(self::$caches[$key])) {
            $cache = $this->cacher->get($key);
        } else {
            $cache = self::$caches[$key];
        }
		$cacheValid = false;
		if ($cache) {
            $tagsValid = $this->isTagsValid($cache['t']);
            $expiresValid = $cache['a'] + $expiration > time() ||
                $expiration = 0;
			$cacheValid = $expiresValid && $tagsValid;
		}
		if ($cacheValid) {
            if (!isset(self::$caches[$key])) {
                self::$caches[$key] = $cache;
            }
			$this->numRows = count($cache['v']);
			$this->foundRows = $cache['f'];
			if (Tracer::$enabled) {
				Tracer::incCachedSelectQueryCount();
			}
			return $cache['v'];
		}
		if (!$this->linkIdentifier) {
			$this->connect();
		}
		if (Tracer::$enabled) {
			$startTime = microtime(true);
			Tracer::begin(__CLASS__, __METHOD__, __LINE__);
		}
        $this->sql = $query->translate('Mysql');
		$rows = parent::_executeSelect($query, $options);
		if (Tracer::$enabled) {
			$endTime = microtime(true);
			$delta = $endTime - $startTime;
			if ($delta >= Tracer::LOW_QUERY_TIME) {
				Tracer::addLowQuery($this->sql, $delta);
			} else {
				Tracer::incSelectQueryTime($delta);
			}
			Tracer::end(
                $this->sql,
                $this->numRows,
				memory_get_usage()
            );
			Tracer::incDeltaQueryCount();
		}
		$tags = $query->getTags();
        $providerTags = $this->cacher->getTags($tags);
        foreach ($tags as $tag) {
            self::$tagsValid[$tag] = true;
            self::$tagsCaches[$tag][] = $key;
        }
        $cache = array (
            'v' => $rows,
            'a' => time(),
            't' => $providerTags,
            'f'	=> $this->foundRows
        );
        self::$caches[$key] = $cache;
		$this->cacher->set($key, $cache);
		return $rows;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::execute()
	 */
	public function execute(Data_Source_Abstract $source, Query_Abstract $query,
        $options = null)
	{
		if (!($query instanceof Query_Abstract)) {
			return new Query_Result(null);
		}
		$start = microtime(true);
		$this->errno = 0;
		$this->error = '';
		$this->affectedRows = 0;
		$this->foundRows = 0;
		$this->numRows = 0;
		$this->insertId = null;
		if (!$options) {
			$options = $this->getDefaultOptions();
		}
		$m = $this->queryMethods[$query->type()];
        if ($m != '_executeSelect') {
            $this->sql = $query->translate('Mysql');
        }
		$result = $this->{$m}($query, $options);
		if ($this->errno) {
			throw new Exception(
				$this->error . "\n" . $this->sql,
				$this->errno
			);
		}
		if (!$this->errno && is_null($result)) {
			$result = array();
		}
		$finish = microtime (true);
		return new Query_Result(array(
			'error'			=> $this->error,
			'errno'			=> $this->errno,
			'query'			=> $query,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
			'foundRows'		=> $this->foundRows,
			'result'		=> $result,
			'touchedRows'	=> $this->numRows + $this->affectedRows,
			'insertKey'		=> $this->insertId,
			'source'		=> $source
		));
	}

	/**
     * Получить текущего кэшера
     *
	 * @return Data_Provider_Abstract
	 */
	public function getCacher()
	{
		return $this->cacher;
	}

    /**
     * Проверяет валидны ли тэги
     *
     * @param array $tags
     * @return boolean
     */
    protected function isTagsValid($tags)
    {
		$validTags = $this->cacher->checkTags($tags);
		if (!$validTags) {
			return false;
		}
        $isValid = true;
        foreach ($tags as $tag) {
            if (empty(self::$tagsValid[$tag])) {
                $isValid = false;
                break;
            }
        }
        if ($isValid) {
            return true;
        }
        if ($validTags) {
            foreach ($tags as $tag) {
                self::$tagsValid[$tag] = true;
            }
        }
        return $validTags;
    }

    /**
     * Удаляет внутренние сохраненные тэги и запросы
     *
     * @param string $tag
     */
    protected function tagDelete($tag)
    {
        if (!isset(self::$tagsValid[$tag])) {
            return;
        }
        unset(self::$tagsValid[$tag]);
        foreach (self::$tagsCaches as $key) {
            if (!isset(self::$caches[$key])) {
                continue;
            }
            unset(self::$caches[$key]);
        }
    }

	/**
	 * Изменить текущего кэшера
     *
	 * @param Data_Provider_Abstract $cacher
	 */
	public function setCacher(Data_Provider_Abstract $cacher)
	{
		$this->cacher = $cacher;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Mysqli::setOption()
	 */
	public function setOption($key, $value = null)
	{
		switch ($key) {
			case 'cache_provider':
                $serviceLocator = IcEngine::serviceLocator();
                $dataProviderManager = $serviceLocator->getService(
                    'dataProviderManager'
                );
                $provider = $dataProviderManager->get($value);
				$this->setCacher($provider);
				return;
			case 'expiration':
				$this->getDefaultOptions()->setExpiration($value);
				return;
		}
		return parent::setOption($key, $value);
	}
}