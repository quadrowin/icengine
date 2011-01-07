<?php

class View_Render_Ajax extends View_Render_Abstract
{
	
	public function __construct (array $config = array())
	{
		parent::__construct ($config);
	}
	
	public function fetch ($tpl)
	{
		$result = $this->_vars;
		$this->_vars = array();
		return $result;
	}
	
	public function display ($tpl = null)
	{
		reset($this->_vars);
		echo json_encode(current($this->_vars));
	}
	
	public function addHelper ($helper, $method)
	{
		
	}
	
}