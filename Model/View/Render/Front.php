<?php
/**
 * 
 * @desc Редер фронт контроллера
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class View_Render_Front extends View_Render_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addHelper()
	 */
	public function addHelper ($helper, $method)
	{
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display ($tpl = null)
	{
		return $this->fetch ($tpl);
	}
	 
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function fetch ($tpl)
	{
		$render = $this->_vars ['render'];
		$tasks = $this->_vars ['tasks'];
		
		return $render->render ($tasks);
	}
	
}