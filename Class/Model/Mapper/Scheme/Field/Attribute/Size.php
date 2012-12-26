<?php

/**
 * Атрибут поля size для схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Field_Attribute_Size extends 
    Model_Mapper_Scheme_Field_Attribute_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::filter
	 */
	public function filter($value)
	{
		return strlen($value, 0, $this->value);
	}

	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::validate
	 */
	public function validate($value)
	{
		return strlen($value) <= $this->value;
	}
}
