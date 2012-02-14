<?php

namespace Ice;

class Model_Mapper_Field_Abstract
{
	protected $_attributes;

	protected $_field;

	protected $_model;

	protected $_scheme;

	protected $_value;

	public function addAttribute (Model_Mapper_Field_Attribute_Abstract $attribute)
	{
		$attribute->setOwner ($this);
		$this->_attributes [$attribute->getName ()] = $attribute;
	}

	public function getAttributes ()
	{
		return $this->_attributes;
	}

	public function filter ()
	{
		return $this->_value;
	}

	public function validate ()
	{
		return true;
	}

	public function getValue ()
	{
		$this->_value = $this->_model->field ($this->_field);
		return $this->_value;
	}

	public function setValue ($value)
	{

		$this->_value = $value;
	}

	public function getName ()
	{
		return substr (get_class ($this), 19);
	}

	public function getField ()
	{
		return $this->_field;
	}

	public function setField ($field)
	{
		$this->_field = $field;
	}

	public function getModel ()
	{
		return $this->_model;
	}

	public function setModel ($model)
	{
		$this->_model = $model;
	}

	public function getScheme ()
	{
		return $this->_scheme;
	}

	public function setScheme ($scheme)
	{
		$this->_scheme = $scheme;
	}
}