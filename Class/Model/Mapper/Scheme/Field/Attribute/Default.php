<?php

/**
 * Атрибут поля default для схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Field_Attribute_Default extends 
    Model_Mapper_Scheme_Field_Attribute_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::filter
	 */
	public function filter($value)
	{
		return !$value ? $this->value : $value;
	}
}
