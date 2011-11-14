<?php

class Model_Mapper_Scheme
{
	protected $_fields = array ();

	protected $_loaded = false;

	protected $_model;

	public function getModel ()
	{
		return $this->_model;
	}

	public function setModel ($model)
	{
		$this->_model = $model;
	}

	public function load ()
	{
		$this->_loaded = true;
		$this->_model->__scheme ();
	}

	public function loaded ()
	{
		return $this->_loaded;
	}

	public function update ()
	{
		foreach ($this->_fields as $name => $field)
		{
			$field->setValue ($this->_model->sfield ($name));
		}
	}

	public function __get ($name)
	{
		return $this->_fields [$name];
	}

	public function __set ($name, $value)
	{
		$value->setField ($name);
		$value->setModel ($this->_model);
		$value->setScheme ($this);

		$this->_fields [$name] = $value;
	}
}