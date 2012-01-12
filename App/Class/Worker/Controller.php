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
	 * @param Task $task
	 * @param Task_Collection $tasks
	 */
	public function let (Task $task, Task_Collection $tasks = null)
	{
		$this->getControllerManager ()->call ($task);
	}

	/**
	 * @desc
	 * @return Controller_Manager
	 */
	public function getControllerManager ()
	{
		return Core::di ()->getInstance ('Ice\\Controller_Manager', $this);
	}

}

