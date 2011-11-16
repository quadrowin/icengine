<?php

class Model_Mapper_Field_Attribute_Abstract
{
	protected $_owner;

	protected $_value;

	public function getValue ()
	{
		return $this->_value;
	}

	public function setValue ($value)
	{
		$this->_value = $value;
	}

	public function getOwner ()
	{
		return $this->_owner;
	}

	public function setOwner (Model_Mapper_Field_Abstract $owner)
	{
		$this->_owner = $owner;
	}

	public function getName ()
	{
		return substr (get_class ($this), 29);
	}
}