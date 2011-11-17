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

	public function getFields ()
	{
		return $this->_get ('Model_Mapper_Field_Abstract');
	}

	public function getKeys ()
	{
		return $this->_get ('Model_Mapper_Index_Abstract');
	}

	public function getSettings ()
	{
		return $this->_get ('Model_Mapper_Setting_Abstract');
	}

	public function _get ($class)
	{
		$result = array ();
		foreach ($this->_fields as $field)
		{
			if ($field instanceof $class)
			{
				$result [] = $field;
			}
		}
		return $result;
	}

	public function render ()
	{
		Loader::load ('Model_Mapper_Scheme_Render');
		$ds = Model_Scheme::dataSource ($this->_model);
		$adapter = $ds->getAdapter ();
		$render_name = $adapter->getTranslatorName ();
		return Model_Mapper_Scheme_Render::render ($render_name, $this);
	}
}