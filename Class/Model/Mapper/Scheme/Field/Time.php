<?php

/**
 * Тип поля time схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Field_Time extends Model_Mapper_Scheme_Field_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Field_Abstract::validate
	 */
	public function validate($value)
	{
		return is_string($value);
	}
}