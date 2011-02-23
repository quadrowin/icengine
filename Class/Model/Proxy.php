<?php

class Model_Proxy extends Model
{
	
	/**
	 * @desc Представляемая модель.
	 * @var string
	 */
	protected $_modelName;
	
	/**
	 * 
	 * @param string $modelName
	 * @param array $fields
	 * @param boolean $autojoin
	 */
	public function __construct ($modelName, array $fields = array (), 
	    $autojoin = true)
	{
		$this->_modelName = $modelName;
		parent::__construct ($fields, $autojoin);
	}
	
	public function load ($key = null)
	{
	    return $this;
	}
	
	public function modelName ()
	{
		return $this->_modelName;
	}
	
	public function save ($hard_insert = false)
	{
	    return $this;
	}
	
}