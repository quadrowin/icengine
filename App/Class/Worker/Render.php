<?php

namespace Ice;

/**
 *
 * @desc
 * @author Yury Shvedov
 *
 */
class Worker_Render extends Worker_Abstract
{

	/**
	 *
	 * @return View_Render_Manager
	 */
	protected function _getViewRenderManager ()
	{
		return Core::di ()->getInstance ('Ice\\View_Render_Manager', $this);
	}

	public function let (Task $task)
	{
		$render = $task->getRequest ()->getExtra ('render');
		if (!is_object ($render))
		{
			$render = $this->_getViewRenderManager ()->byName ($render);
		}
		//var_dump ($task->getRequest ()->getInput ()->receiveAll ());
		$render->render ($task);
	}

}
