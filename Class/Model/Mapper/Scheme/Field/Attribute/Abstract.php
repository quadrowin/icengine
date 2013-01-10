<?php

/**
 * Абстрактный атрибут поля схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */

class Model_Mapper_Scheme_Field_Attribute_Abstract
{
	/**
	 * Значение атрибута поля
	 * 
     * @var string
	 */
	protected $value;

	/**
	 * Получить имя фабрики
	 * 
     * @return string
	 */
	public function factoryName()
	{
		return 'Field_Attribute';
	}

	/**
	 * Получить имя атрибута
	 * 
     * @return string
	 */
	public function getName()
	{
		return substr(
            get_class($this), strlen('Model_Mapper_Scheme_Field_Attribute_')
        );
	}

	/**
	 * Получить значение атрибута поля
	 * 
     * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Корректировать значение поля
	 * 
     * @param mixed $value
	 * @return mixed
	 */
	public function filter($value)
	{
		return $value;
	}

	/**
	 * Установить значение атрибута поля
	 * 
     * @param string $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Проверить значение поля на корректность
	 * 
     * @param mixed $value
	 * @return boolean
	 */
	public function validate($value)
	{
		return true;
	}
}