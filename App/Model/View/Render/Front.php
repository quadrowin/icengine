<?php

namespace Ice;

/**
 *
 * @desc Редер фронт контроллера
 * @author Юрий Шведов, Илья Колесников
 * @package Ice
 *
 */
class View_Render_Front extends View_Render_Abstract
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		// Render for layout
		'layout_render'	=> 'Smarty'
	);

	/**
	 * @return View_Render_Manager
	 */
	protected function _getViewRenderManager ()
	{
		return Core::di ()->getInstance ('Ice\\View_Render_Manager', $this);
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display ($tpl)
	{
		$this->fetch ($tpl);
	}

	public function fetch ($tpl)
	{
		throw new Exception ('хД');
	}

	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function render (Task $task)
	{
		$input = $task->getRequest ()->getInput ();
		$this->assign ($input->receiveAll ());
		$tasks = $input->receive ('tasks');
//		var_dump ($tasks->getResponse ());
//		var_dump ($input);
//		var_dump ($input->receiveAll ());
//		die ();

//		$tasks->last ();
//		foreach ($tasks as $t)
//		{
//			$render = $t->getResponse ()->getExtra ('render');
//			$render = View_Render_Manager::byName ($render);
//			$result = $render->render ($t);
//			$this->assign ($t->getAssignVar (), $result);
//		}

		$config = $this->config ();
		$render = $this->_getViewRenderManager ()->byName (
			$config ['layout_render']
		);

		$render->assign ($tasks->getResponse ()->getOutput ()->receiveAll ());

		$render->display ($task->getRequest ()->getExtra ('template'));
	}

}