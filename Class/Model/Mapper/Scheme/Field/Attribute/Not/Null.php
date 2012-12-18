<?php

/**
 * Атрибут поля not null для схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Field_Attribute_Not_Null extends 
    Model_Mapper_Scheme_Field_Attribute_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::filter
	 */
	public function filter($value)
	{
		return is_null($value) ? '' : $value;
	}

	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::validate
	 */
	public function validate($value)
	{
		return !$value ? !is_null($value) : true;
	}
}
