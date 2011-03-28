<?php

class View_Render_Post extends View_Render_Abstract
{
	
	public function fetch ($tpl)
	{
		$result = $this->_vars;
		$this->_vars = array();
		return $result;
	}
	
	public function display ($tpl = null)
	{
		$redirect = isset ($this->_vars ['redirect']) ? $this->_vars ['redirect'] : '/';
		Loader::load ('Header');
		Header::redirect ($redirect);
		die();
	}
	
	public function addHelper ($helper, $method)
	{
		
	}
	
}