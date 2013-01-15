<?php

/**
 * Абстрактная модель индекса схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Index_Abstract
{
	/**
	 * Поля индекса
	 * 
     * @var array
	 */
	protected $fields;

	/**
	 * Получить имя фабрики
	 * 
     * @return string
	 */
	public function factoryName()
	{
		return 'Index';
	}

	/**
	 * Получить поля индекса
	 * 
     * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Получить имя индекса
	 * 
     * @return string
	 */
	public function getName()
	{
		return substr(get_class($this), strlen('Model_Mapper_Scheme_Index_'));
	}

	/**
	 * Изменить поля индекса
	 * 
     * @param array $fields
	 */
	public function setFields($fields)
	{
		$this->fields = $fields;
	}
}