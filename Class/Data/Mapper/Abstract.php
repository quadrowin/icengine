<?php

/**
 * Абстрактный мэппер данных
 * 
 * @author goorus, morph
 */
abstract class Data_Mapper_Abstract
{
	/**
	 * Опции по умолчанию
     * 
	 * @var Query_Options
	 */
	protected $defaultOptions;

	/**
	 * Выполнить запрос через мэппер данных
     * 
	 * @param Data_Source_Abstract $source
	 * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return Query_Result
	 */
	public function execute(Data_Source_Abstract $source,
		Query_Abstract $query, $options = null)
	{
		if (!($query instanceof Query_Abstract)) {
			return new Query_Result(null);
		}
		$start = microtime(true);
		$rows = $this->_execute($query, $options);
		$finish = microtime (true);
		$result = new Query_Result(array (
			'query'			=> $query,
			'startAt'		=> $start,
			'result'		=> $rows,
            'foundRows'     => count($rows),
			'touchedRows'	=> count($rows),
			'insertKey'		=> 0,
			'finishedAt'	=> $finish,
			'source'		=> $source
		));
		return $result;
	}

	/**
     * Получить опции по умолчанию
     * 
	 * @return Query_Options
	 */
	public function getDefaultOptions ()
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
	 * @return Data_Mapper_Abstract
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