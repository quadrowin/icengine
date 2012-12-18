<?php

/**
 * Абстрактный метод связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Method_Abstract
{
	/**
	 * Параметры методв
	 * 
     * @var array
	 */
	protected $params;

	/**
	 * Выполнить метод
     * 
     * @return Model_Mapper_Method_Abstract
	 */
	public function execute()
	{
		return $this;
	}

	/**
	 * Получить имя метода
	 * 
     * @return string
	 */
	public function getName()
	{
		return substr(get_class($this), 'Model_Mapper_Method_');
	}

	/**
	 * Задать параметры метода
	 * 
     * @param array $params
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}
}