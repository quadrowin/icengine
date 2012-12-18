<?php

/**
 * Тип поля datetime схемы связей модели
 * 
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Datetime extends 
    Model_Mapper_Scheme_Field_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Field_Abstract::validate
	 */
	public function validate($value)
	{
		return (bool) strtotime($value);
	}
}