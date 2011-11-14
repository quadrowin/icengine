<?php

class Model_Mapper_Field_Attribute
{
	public static function factory ($name)
	{
		$class_name = 'Model_Mapper_Field_Attribute_' . $name;
		Loader::load ($class_name);
		return new $class_name;
	}
}