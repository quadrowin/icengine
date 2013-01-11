<?php

/**
 * Тип поля binary схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Field_Binary extends 
    Model_Mapper_Scheme_Field_Abstract
{
	/**
     * @inheridoc
	 * @see Model_Mapper_Scheme_Field_Abstract::validate
	 */
	public function validate($value)
	{
		return is_string($value);
	}
}