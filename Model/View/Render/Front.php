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
		$this->fetch ($tpl);
	}
	
	public function fetch($tpl)
	{
		throw new Exception ('o.O');
	}
	 
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function render (Controller_Task $task)
	{
		$transaction = $task->getTransaction ();
		$tasks = $transaction->receive ('tasks');
		
		foreach ($tasks as $t)
		{
			$render = $t->getViewRender ();
			$result = $render->render ($t);
			$this->assign ($t->getAssignVar (), $result);
		}
		
		$render = $tasks [0]->getViewRender ();

		$render->assign ($this->_vars);
		$render->display ();
	}
	
}