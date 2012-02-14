<?php

namespace Ice;

Loader::load ('Background_Agent_Abstract');

/**
 * @desc Агент для запуска заданий из очереди заданий
 * @author Илья Колесников
 * @package Ice
 * @copyright i-complex.ru
 */
class Background_Agent_Task extends Background_Agent_Abstract
{
	public function _finish ()
	{

	}

	public function _process ()
	{
		$queue = Model_Collection_Manager::getInstance ()
			->byQuery (
				'Task_Queue',
				Query::instance ()
					->where ('processed', 0)
			);

		foreach ($queue as $action)
		{
			$action->update (array (
				'processed'		=> 1,
				'finishedAt'	=> Helper_Date::toUnix ()
			));

			$controller = $action->Task->action;
			$action = 'index';

			if (strpos ($controller, '/') !== false)
			{
				list ($controller, $action) = explode ('/', $controller);
			}

			Controller_Manager::call ($controller, $action);
		}
	}

	public function _start ()
	{

	}
}