<?php

Loader::load ('Model_Mapper_Scheme_Field_Abstract');

/**
 * @desc Тип поля longtext схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Longtext extends Model_Mapper_Scheme_Field_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Field_Abstract::validate
	 */
	public function validate ($value)
	{
		return is_string ($value);
	}
}