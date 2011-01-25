<?php

class Message_Abstract
{
	
	/**
	 * 
	 * @var array
	 */
	protected $_data = array ();
	
	/**
	 * 
	 * @var string
	 */
	protected $_type;
	
	public function __construct (array $data = array (), $type = null)
	{
		$this->_data = $data;
		
		if (!$type)
		{
		    $this->_type = substr (get_class ($this), strlen ('Message_'));
		}
	}
	
	/**
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
		return $this->_data [$key];
	}
	
	public function __set ($key, $value)
	{
		$this->_data [$key] = $value;
	}
	
	/**
	 * 
	 * @param callback $callback
	 */
	public function notify ($callback)
	{
	    return call_user_func ($callback, $this);
	}
	
	/**
	 * 
	 * @return string
	 */
	public function type ()
	{
		return $this->_type;
	}
	
}