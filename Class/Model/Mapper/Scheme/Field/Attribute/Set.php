<?php

/**
 * Сет атрибутов поля схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Field_Attribute_Set
{
	/**
	 * Добавленные атрибуты
	 * 
     * @var array
	 */
	protected $data = array();

	/**
	 * Добавить атрибут в сет
	 * 
     * @param Model_Mapper_Sceme_Field_Attribute_Abstract $attribute
	 */
	public function add($attribute)
	{
		$this->data[] = $attribute;
	}

	/**
	 * Получить все атрибуты
	 * 
     * @return array
	 */
	public function all()
	{
		return $this->data;
	}

	/**
	 * Получить атрибут по имени
	 * 
     * @param string $name
	 * @return Model_Scheme_Field_Atribute_Abstract
	 */
	public function byName($name)
	{
		foreach ($this->data as $attribute) {
			if ($attribute->getName() == $name) {
				return $attribute;
			}
		}
	}
}