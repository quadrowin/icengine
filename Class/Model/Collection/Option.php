<?php

class Model_Collection_Option
{
    
    /**
     * Название опции
     * @var string
     */
	private $_name;
	
	/**
	 * Параметры
	 * @var array
	 */
	protected $_params;
	
	public function __construct ($name, array $params = array ())
	{
		$this->_name = $name;
		$this->_params = $params;
	}
	
	public function getName ()
	{
		return $this->_name;
	}
	
	public function setName ($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getParams ()
	{
	    return $this->_params;
	}
	
	public function modelName ()
	{
		return substr (get_class ($this), 0, -18);
	}
	
}