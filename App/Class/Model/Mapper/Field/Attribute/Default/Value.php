<?php

namespace Ice;

Loader::load ('Model_Mapper_Field_Attribute_Abstract');

class Model_Mapper_Field_Attribute_Default_Value extends
	Model_Mapper_Field_Attribute_Abstract
{
	public function filter ()
	{
		if (!$this->_owner->getValue ())
		{
			$this->_owner->setValue ($this->_value);
		}
	}
}