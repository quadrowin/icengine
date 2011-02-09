<?php

class Resource_Manager
{

	/**
	 * 
	 * @var array
	 */
	protected $_resources = array ();
	
	/**
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function get ($type, $name)
	{
		return isset ($this->_resources [$type] [$name]) ? $this->_resources [$type] [$name] : null;
	}
	
	/**
	 * 
	 * @param string $name
	 * @param mixed $resource
	 */
	public function set ($type, $name, $resource)
	{		
		$this->_resources [$type] [$name] = $resource;
	}
	
	public function byType ($type)
	{
		return isset ($this->_resources [$type]) ? $this->_resources [$type] : null;
	}
	
}