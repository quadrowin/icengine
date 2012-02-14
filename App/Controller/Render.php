<?php

namespace Ice;

/**
 *
 * @desc Контролер для рендеринга заданий
 * @author Юрий Шведов, Илья Колесников
 * @package Ice
 *
 */
class Controller_Render extends Controller_Abstract
{

	/**
	 * @desc Рендерим
	 * @param Task $task
	 */
	public function index (Task $task)
	{
		$render = $task->getRequest ()->getExtra ('render');
		View_Render_Manager::byName ($render)->render ($task);
	}

}