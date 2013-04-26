<?php

/**
 * Драйвер для источника данных синхронизирующихся моделей
 *
 * @author morph
 */
class Data_Driver_Sync extends Data_Driver_Abstract
{
    /**
     * Драйвер источника данных СУДБ
     * 
     * @var Data_Driver_Abstract
     */
    protected $dynamicDriver;
    
	/**
	 * Обработчики по видам запросов.
	 *
     * @var array
	 */
	protected $queryMethods = array(
		Query::SELECT	=> 'executeStatic',
		Query::DELETE	=> 'executeDynamic',
		Query::UPDATE	=> 'executeDynamic',
		Query::INSERT	=> 'executeDynamic'
	);
    
    /**
     * Драйвер источника данных справочника
     * 
     * @var Data_Driver_Abstract
     */
    protected $staticDriver;

	/**
	 * Запрос на изменение данных (Insert, Update или Delete).
	 *
     * @param Query_Abstract $query Запрос
	 * @param Query_Options $options Параметры запроса.
	 * @return Query_Result
	 */
	protected function executeDynamic(Query_Abstract $query, 
        Query_Options $options)
	{
        $modelName = $this->getModelName($query);
        $result = $this->dynamicDriver->execute($query, $options);
        $this->getService('helperModelSync')->resync($modelName);
        return $result;
	}

	/**
	 * Запрос на выборку
	 *
     * @param Query_Abstract $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return Query_Result
	 */
	protected function executeStatic(Query_Abstract $query, 
        Query_Options $options)
	{
		$modelName = $this->getModelName($query);
        $rows = $modelName::$rows;
        if (!$rows) {
            $this->getService('helperModelSync')->resync($modelName);
        }
        $result = null;
        $ds = $this->getService('modelScheme')->dataSource($modelName);
        $filters = $modelName::$filters;
        $priorityFields = $modelName::$priorityFields;
        $criterias = $this->getCriterias($query);
        ksort($criterias);
        ksort($filters);
        $criteriasNames = array_keys($criterias); 
        $filtersNames = array_keys($filters);
        if (!$filters && !$priorityFields) {
            $result = $this->staticDriver->execute($query, $options);
        } elseif (!array_diff($criteriasNames, $filtersNames) &&
            !array_diff($filters, $criterias)) {
            $result = $this->staticDriver->execute($query, $options);
        } elseif (!array_diff($criteriasNames, $priorityFields)) {
            $result = $this->staticDriver->execute($query, $options);
            if (!$result->touchedRows()) {
                $result = $this->dynamicDriver->execute($query, $options);
            }
        } else {
            $result = $this->dynamicDriver->execute($query, $options);
        }
        return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function execute(Query_Abstract $query, $options = null)
	{
		$m = $this->queryMethods[$query->type()];
		$result = $this->{$m}($query, $options);
		return new Query_Result(array(
            'error'			=> '',
			'errno'			=> 0,
			'query'			=> $query,
			'startAt'		=> 0,
			'finishedAt'	=> 0,
			'foundRows'		=> $result->foundRows(),
			'result'		=> $result->asTable(),
			'touchedRows'	=> $result->touchedRows(),
			'insertKey'		=> $result->insertId()
        ));
	}

    /**
     * Получить критерии запроса 
     * 
     * @param Query_Abstract $query
     * @return array
     */
    public function getCriterias($query)
    {
        $criteria = array();
        $where = $query->getPart(Query::WHERE);
        if ($where) {
            foreach ($where as $part) {
                $where = $part[Query::WHERE];
                if (strpos($where, '.') !== false) {
                    list(,$quotedfield) = explode('.', $where);
                    $field = trim($quotedfield, '`');
                } else {
                    $field = trim($where, '`');
                }
                $criteria[$field] = $part[Query::VALUE];
            }
        }
        return $criteria;
    }
    
    /**
     * Получить драйвер для запросов к СУБД
     * 
     * @return Data_Driver_Abstract
     */
    public function getDynamicDriver()
    {
        return $this->dynamicDriver;
    }
    
    /**
     * Получить имя модели по запросу
     * 
     * @param Query_Abstract $query
     * @return string
     */
    protected function getModelName($query)
    {
        $from = $query->part(Query::FROM);
        if (!$from) {
            return false;
        }
        $modelName = reset($from)[Query::TABLE];
        return $modelName;
    }
    
    /**
     * Получить сервис по имени
     * 
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
    
    /**
     * Получить драйвер для запросов к справочнику
     * 
     * @return Data_Driver_Abstract
     */
    public function getStaticDriver()
    {
        return $this->staticDriver;
    }
    
	/**
	 * @inheritdoc
	 */
	public function setOption($key, $value = null)
	{
        $dataDriverManager = $this->getService('dataDriverManager');
		switch ($key) {
			case 'dynamicDriver':
                $dds = $this->getService('dds');
                $sourceConfig = $dds->getDataSource()->getConfig();
                $config = isset($sourceConfig['options'])
                    ? $sourceConfig['options'] : array();
                $this->dynamicDriver = $dataDriverManager->get($value, $config);
				return;
			case 'staticDriver':
				$this->staticDriver = $dataDriverManager->get($value);
				return;
		}
		return parent::setOption($key, $value);
	}
}