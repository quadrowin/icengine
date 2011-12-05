<?php

namespace Ice;

Loader::load ('Model_Mapper_Field_Attribute_Abstract');

class Model_Mapper_Field_Attribute_Encoding extends Model_Mapper_Field_Attribute_Abstract
{
	public function filter ()
	{
		$encoding = DDS::getDataSource ()->getAdapter ()->option ('encoding');
		return $encoding != $this->_value
			? $this->_owner->setValue (
				iconv ($encoding, $this->_value, $this->_owner->getValue ()))
			: $this->_owner->getValue ();
	}

}