<?php

namespace Ice;

Loader::load ('Model_Mapper_Field_Attribute_Abstract');

class Model_Mapper_Field_Attribute_Max_Length extends
	Model_Mapper_Field_Attribute_Abstract
{
	public function filter ()
	{
		return substr ($this->_value, 0, $value);
	}

	public function validate ()
	{
		return (bool) ((strlen ($this->_value) <= $value) !== false);
	}
}