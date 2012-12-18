<?php

/**
 * Атрибут поля unsigned для схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Field_Attribute_Unsigned extends 
Model_Mapper_Scheme_Field_Attribute_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Field_Attribute_Abstract::validate
	 */
	public function validate($value)
	{
		return $value >= 0;
	}
}
