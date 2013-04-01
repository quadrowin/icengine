<?php

/**
 * Абстрактный драйвер источника данных
 * 
 * @author goorus, morph
 */
abstract class Data_Driver_Abstract
{
	/**
	 * Опции по умолчанию
     * 
	 * @var Query_Options
	 */
	protected $defaultOptions;

	/**
	 * Выполнить запрос через драйвер данных
     * 
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return Query_Result
	 */
	public function execute(Query_Abstract $query, $options = null)
	{
		if (!($query instanceof Query_Abstract)) {
			return new Query_Result(null);
		}
		$start = microtime(true);
        if (!$options) {
            $options = new Query_Options();
        }
		$rows = $this->executeCommand($query, $options);
		$finish = microtime (true);
		$result = new Query_Result(array (
			'query'			=> $query,
			'startAt'		=> $start,
			'result'		=> $rows,
            'foundRows'     => count($rows),
			'touchedRows'	=> count($rows),
			'insertKey'		=> 0,
			'finishedAt'	=> $finish
		));
		return $result;
	}
    
    /**
     * Выполняет базовый запрос к драйверу
     * 
     * @param Query_Abstract $query
     * @param Query_Options $options
     * @return array
     */
    protected function executeCommand(Query_Abstract $query, 
        Query_Options $options)
    {
        return array();
    }

	/**
     * Получить опции по умолчанию
     * 
	 * @return Query_Options
	 */
	public function getDefaultOptions()
	{
		if (!$this->defaultOptions) {
			$this->defaultOptions = new Query_Options();
		}
		return $this->defaultOptions;
    }
    
	/**
	 * Изменить опции по умолчанию
     * 
	 * @param Query_Options $options
	 * @return Data_Driver_Abstract
	 */
	public function setDefaultOptions(Query_Options $options)
	{
		$this->defaultOptions = $options;
		return $this;
	}

	/**
	 * Установка параметров
	 * 
     * @param string|Objective $key Параметр.
	 * @param string $value [optional] Значение.
	 * @return boolean true, если удачно, иначе - false.
	 */
	public function setOption($key, $value = null)
	{
		return false;
    }
}