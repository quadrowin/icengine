<?php

/**
 * @desc ORM
 * @author Илья Колесников
 */
class Model_Mapper
{
	public static function field ($field_name, $field_attributes)
	{
		$field = Model_Mapper_Field::factory ($field_name);

		foreach ($field_attributes as $name => $value)
		{
			$attribute = Model_Mapper_Field_Attribute::factory ($name);
			$attribute->setValue ($value);
			$field->addAttribute ($attribute);
		}
		return $field;
	}
}