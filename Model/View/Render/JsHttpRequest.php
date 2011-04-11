<?php
/**
 * 
 * @desc Рендер данных для библиотеки JsHttpRequest
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class View_Render_JsHttpRequest extends View_Render_Abstract
{
	
	/**
	 * @desc Экземпляр бекэнда.
	 * @var JsHttpRequest
	 */
	protected $_request;
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::_afterConstruct()
	 */
	protected function _afterConstruct ()
	{
		Loader::load ('JsHttpRequest', 'includes');
		$this->_request = new JsHttpRequest ('UTF-8');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function fetch ($tpl)
	{
		$result = $this->_vars;
		$this->_vars = array ();
		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display ($tpl = null)
	{
		$GLOBALS ['_RESULT'] = reset ($this->_vars);
		die ();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addHelper()
	 */
	public function addHelper ($helper, $method)
	{
		
	}
	
}