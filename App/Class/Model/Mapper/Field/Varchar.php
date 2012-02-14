<?php

namespace Ice;

Loader::load ('Model_Mapper_Field_Abstract');

class Model_Mapper_Field_Varchar extends Model_Mapper_Field_Abstract
{
	public function validate ()
	{
		return is_string ($this->_value);
	}
}