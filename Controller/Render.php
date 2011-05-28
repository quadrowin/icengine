<?php

/**
 * @desc Контролер для рендеринга заданий
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 */
class Controller_Render extends Controller_Abstract
{
	/**
	 * @desc Рендерим
	 */
	public function index ()
	{
		list (
			$task,
			$render
		) = $this->_input->receive (
			'task',
			'render'
		);
		
		$render->assign (array (
			'task'		=> $task,
			'render'	=> $task->getViewRender ()
		));
	}
}