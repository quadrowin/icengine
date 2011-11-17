<?php

class Model_Mapper_Field
{
	public static function factory ($name)
	{
		$class_name = 'Model_Mapper_Field_' . $name;
		Loader::load ($class_name);
		return new $class_name;
	}
}