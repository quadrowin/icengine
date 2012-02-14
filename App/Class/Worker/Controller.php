<?php

namespace Ice;

/**
 *
 * @desc
 * @author Yury Shvedov
 *
 */
class Worker_Controller extends Worker_Abstract
{

	/**
	 * @desc
	 * @return Controller_Manager
	 */
	protected function _getControllerManager ()
	{
		return Core::di ()->getInstance ('Ice\\Controller_Manager', $this);
	}

	/**
	 * @desc
	 * @param Task $task
	 * @param Task_Collection $tasks
	 */
	public function let (Task $task)
	{
		$this->_getControllerManager ()->call ($task);
	}

}

