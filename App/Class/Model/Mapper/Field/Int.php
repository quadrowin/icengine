<?php

Loader::load ('Model_Mapper_Field_Abstract');

class Model_Mapper_Field_Int extends Model_Mapper_Field_Abstract
{
	public function filter ()
	{
		return (int) $this->_value;
	}

	public function validate ()
	{
		return is_numeric ($this->_value);
	}
}