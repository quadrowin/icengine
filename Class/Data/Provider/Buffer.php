<?php

if (!class_exists ('Data_Provider_Abstract'))
{
	include dirname (__FILE__) . '/Abstract.php';
}

class Data_Provider_Buffer extends Data_Provider_Abstract
{
	private $_buffer;
	
	public function __construct ()
	{
		$this->_buffer = array ();
	}
		
	/**
	 * 
	 * @param string $key
	 * @param boolean $plain
	 * @return string
	 */
	public function get ($key, $plain = false)
	{
		return isset ($this->_buffer [$key]) ? $this->_buffer [$key] : null;
	}
	
	/**
	 * @return array
	 */
	public function getAll ()
	{
		return $this->_buffer;
	}
	
	/**
	 * 
	 * @param integer $delay
	 */
	public function flush ($delay = 0)
	{
		$this->_buffer = array ();
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param integer $expiration
	 * @param array $tags
	 */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		$this->_buffer [$key] = $value;
	}
}