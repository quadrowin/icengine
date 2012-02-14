<?php

namespace Ice;

/**
 *
 * @desc
 * @author Yury Shvedov
 *
 */
class Task_Controller_Action extends Controller_Task
{

	/**
	 * @desc
	 * @var Task
	 */
	protected $_task;

	/**
	 * @desc
	 * @param Task $task
	 */
	public function __construct (Task $task)
	{
		$this->_task = $task;
	}

	/**
	 * @desc Получить транспорт входных данных
	 * @return Data_Transport
	 */
	public function getInput ()
	{
		return $this->_task->getRequest ()->getInput ();
	}

	/**
	 * @desc Получить рендер
	 * @return View_Render_Abstract
	 */
	public function getViewRender ()
	{
		$name = $this->_task->getResponse ()->getExtra ('render');
		return $this->getViewRenderManager ()->byName ($name);
	}

	/**
	 *
	 * @return View_Render_Manager
	 */
	public function getViewRenderManager ()
	{
		return Core::di ()->getInstance ('Ice\\View_Render_Manager', $this);
	}

}
