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
	 * @param Controller_Task $task
	 */
	public function index (Controller_Task $task)
	{
		$task->getViewRender ()->render ($task);
	}

}