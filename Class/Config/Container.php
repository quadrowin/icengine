<?php

class Config_Container
{
	/**
	 * 
	 * @var array
	 */
	private $_config;
	
	/**
	 * 
	 * @var string
	 */
	private $_name;
	
	/**
	 * 
	 * @var string
	 */
	private $_path;
	
	/**
	 * 
	 * @var string
	 */
	private $_type;
	
	public function __construct ($name, $type, $path)
	{
		$this->_name = $name;
		$this->_type = $type;
		$this->_path = $path;
		$this->load ();
	}
	
	/**
	 * @return Config_Abstract
	 */
	public function config ()
	{
		return $this->_config;
	}
	
	/**
	 * @return string
	 */
	public function getName ()
	{
		return $this->_name;
	}
	
	/**
	 * @return string
	 */
	public function getPath ()
	{
		return $this->_path;	
	}
	
	/**
	 * @return string
	 */
	public function getType ()
	{
		return $this->_type;
	}
	
	/**
	 * @return Config_Container
	 */
	public function load ()
	{
		$filename = rtrim ($this->_path, '/') . '/' . $this->_type . '/' . $this->_name . '.php';
		if (is_file ($filename))
		{
		    $config = null;
			include_once ($filename);
			if (isset ($config))
			{
				$this->_config = $config;
				unset ($config);
			}
		}
		return $this;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return Config_Container
	 */
	public function setName ($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	/**
	 * 
	 * @param string $path
	 * @return Config_Container
	 */
	public function setPath ($path)
	{
		$this->_path = $path;
		return $this;
	}
}