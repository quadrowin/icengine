<?php

/**
 * Драйвер данных через провайдер.
 *
 * @author goorus, morph
 */
class Data_Driver_Provider extends Data_Driver_Abstract
{
	/**
	 * Используемый транслятор
	 *
     * @var string
	 */
	const TRANSLATOR = 'KeyValue';

    /**
     * Количество затронутых рядов
     *
     * @var integer
     */
    protected $affectedRows = 0;
    
    /**
     * Код ошибки
     *
     * @var integer
     */
    protected $errno = 0;

    /**
     * Сообщение об ошибке
     *
     * @var string
     */
    protected $error = '';

    /**
     * Количество найденных рядов
     *
     * @var integer
     */
    protected $foundRows = 0;

    /**
     * id последней созданной сущности
     *
     * @var mixed
     */
    protected $insertId = null;

    /**
     * Провайдер, через которого буду получаться данные
     *
     * @var Data_Provider_Abstract
     */
    protected $provider;

    /**
     * Текущий запрос
     *
     * @var mixed
     */
	protected $query;
    
    /**
     * Методы, через которые будут выполнены операции
     *
     * @var array
     */
    protected $queryMethods = array(
        Query::SELECT    => 'executeSelect',
        Query::DELETE    => 'executeDelete',
        Query::UPDATE    => 'executeUpdate',
        Query::INSERT    => 'executeInsert'
    );
    
    /**
     * Оттранслированный запрос
     *
     * @var array
     */
    protected $translated;

    /**
     * Запрос на удаление
     * 
     * @param Query_Abstract $query
	 * @param Query_Options $options
     */
	protected function executeDelete(Query_Abstract $query,
        Query_Options $options)
	{
		$this->affectedRows = $this->fullDeleteByPatterns(
			$this->translator()->extractTable($query),
			$this->translated
		);
		return true;
	}

	/**
	 * Запрос на вставку
     * 
     * @param Query_Abstract $query
	 * @param Query_Options $options
	 */
    protected function executeInsert(Query_Abstract $query,
        Query_Options $options)
    {
        foreach ($this->translated[0] as $key) {
        	$this->provider->set($key, $this->translated[1]);
        }
		$this->affectedRows = count($this->translated [0]);
		return true;
	}

	/**
	 * Запрос на выборку
     * 
     * @param Query_Abstract $query
	 * @param Query_Options $options
	 */
    protected function executeSelect(Query_Abstract $query,
        Query_Options $options)
    {
		$translator = $this->translator();
		$ids = array();
		$rows = array();
		// Выбираем ID всех записей, подходящих под условие
		foreach ($this->translated as $pattern) {
			if (strpos($pattern, '*') === false) {
                $keys = array($pattern);
            } else {
                $keys = $this->provider->keys($pattern);
            }
			foreach ($keys as $key) {
				$id = $translator->extractId($key);
				if (!isset($ids[$id])) {
					$ids[$id] = $id;
					$row = $this->provider->get($key);
					if ($row) {
						$rows [] = $row;
					}
				}
			}
		}
        return $rows;
    }

    /**
     * Запрос на обновление
     * 
     * @param Query_Abstract $query
	 * @param Query_Options $options
     */
    protected function executeUpdate(Query_Abstract $query, 
        Query_Options $options)
    {
    	// Удаление ненужных индексов
		$this->fullDeleteByPatterns(
			$this->translator()->extractTable($query),
			$this->translated[0]
		);
		// Установка новых значений
        if (!isset($this->translated[2])) {
            return false;
        }
		foreach ($this->translated[1] as $key) {
			$this->provider->set($key, $this->translated[2]);
		}
		return true;
	}

	/**
	 * Полный список ключей по маскам.
	 *
     * @param string $table
	 * @param array $patterns
	 * @return integer Количество удаленных первичных ключей.
	 */
	protected function fullDeleteByPatterns($table, array $patterns)
	{
		$translator = $this->translator();
		$ids = array();
		// Выбираем ID всех записей, подходящих под условие
		foreach ($patterns as $pattern) {
			$keys = $this->provider->keys($pattern);
			foreach ($keys as $key) {
				$ids[$key] = $translator->extractId($key);
			}
		}
		// Для каждого ID выбираем запись,
		// строим ключи согласно индексам и удаляем их.
		foreach ($ids as $id) {
			$key = $table . $translator->tableIndexDelim . 'k' .
                $translator->indexKeyDelim . $id;
            $row = $this->provider->get($key);
			$keys = $translator->compileKeys($table, $row);
			$this->provider->delete($keys);
		}
		return count($ids);
	}

    /**
     * @inheritdoc
     */
	public function execute(Query_Abstract $query, $options = null)
	{
		$this->query = $query;
		$this->translated = $query->translate(self::TRANSLATOR);
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
		$result = $this->{$m}($query, $options);
		if ($this->errno) {
			throw new Exception(
			    $this->error . "\n" . $this->query->translate('Mysql'),
			    $this->errno
			);
		}
		if (!$this->errno && is_null($result)) {
			$result = array();
		}
		return new Query_Result(array(
			'error'			=> $this->error,
			'errno'			=> $this->errno,
			'query'			=> $query,
		    'foundRows'		=> $this->foundRows,
			'result'		=> $result,
			'touchedRows'	=> $this->numRows + $this->affectedRows,
			'insertKey'		=> $this->insertId
		));
	}

	/**
     * Получить текущего провайдера
     *
	 * @return Data_Provider_Abstract
	 */
	public function getProvider()
	{
		return $this->provider;
	}

	/**
	 * @inheritdoc
	 */
	public function setOption($key, $value = null)
	{
		switch ($key) {
			case 'provider':
                $serviceLocator = IcEngine::serviceLocator();
                $dataProviderManager = $serviceLocator->getService(
                    'dataProviderManager'
                );
                $provider = $dataProviderManager->get($value);
				$this->setProvider($provider);
				return true;
		}
		return false;
	}

	/**
	 * Изменить текущего провайдера
     *
	 * @param Data_Provider_Abstract $provider
	 * @return Data_Driver_Provider
	 */
	public function setProvider(Data_Provider_Abstract $provider)
	{
		$this->provider = $provider;
		return $this;
	}

	/**
     * Получить трансплятор запросов
     *
	 * @return Query_Translator_KeyValue
	 */
	public function translator()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $translator = $serviceLocator->getService('queryTranslator');
		return $translator->byName(
			self::TRANSLATOR . '_' . $this->query->getName()
		);
	}
}