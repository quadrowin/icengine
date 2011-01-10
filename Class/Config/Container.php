<?php

class Config_Container
{
	/**
	 * 
	 * @var Config_Array
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
	 * @return Config_Array
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
		$filename = 
		    rtrim ($this->_path, '/') . '/' . 
		    str_replace ('_', '/', $this->_type) . 
		    ($this->_name ? '/' . $this->_name : '') . 
		    '.php';
		if (is_file ($filename))
		{
    	    Loader::load ('Common_File');
    	    $ext = ucfirst (Common_File::extention ($filename));
    	    $class = 'Config_' . $ext;
    	    
    	    if (!Loader::load ($class) || !file_exists ($filename))
    	    {
    	        $this->_config = Config_Manager::emptyConfig ();
    	    }
    	    
    	    $this->_config = new $class ($filename);
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