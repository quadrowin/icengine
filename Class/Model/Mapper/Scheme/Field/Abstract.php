<?php

/**
 * Абстрактное поле схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Field_Abstract
{
	/**
	 * Атрибуты поля
	 * 
     * @var Model_Mapper_Scheme_Field_Attribute_Set
	 */
	protected $attributes;

	/**
	 * Конструктор
	 */
	public function __construct ()
	{
		$this->attributes = new Model_Mapper_Scheme_Field_Attribute_Set;
	}

	/**
	 * Получить атрибуты поля
	 * 
     * @return Model_Mapper_Scheme_Field_Attribute_Set
	 */
	public function attributes()
	{
		return $this->attributes;
	}

	/**
	 * Получить имя фабрики
	 * 
     * @return string
	 */
	public function factoryName()
	{
		return 'Field';
	}

	/**
	 * Получить имя поля
	 * 
     * @return string
	 */
	public function getName()
	{
		return substr(get_class($this), strlen('Model_Mapper_Scheme_Field_'));
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