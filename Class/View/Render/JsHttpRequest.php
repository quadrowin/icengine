<?php

class View_Render_JsHttpRequest extends View_Render_Abstract
{
	
	/**
	 * 
	 * @var JsHttpRequest
	 */
	protected $_request;
	
	protected function _afterConstruct ()
	{
		Loader::load ('JsHttpRequest', 'includes');
		$this->_request = new JsHttpRequest ('UTF-8');
	}
	
	public function fetch ($tpl)
	{
		$result = $this->_vars;
		$this->_vars = array ();
		return $result;
	}
	
	public function display ($tpl = null)
	{
		reset ($this->_vars);
		$GLOBALS ['_RESULT'] = current ($this->_vars);
		die ();
	}
	
	public function addHelper ($helper, $method)
	{
		
	}
	
}