<?php

namespace Ice;

/**
 *
 * @author Yury Shvedov
 *
 */
class Worker_Manager
{

	/**
	 *
	 * @var Worker_Manager
	 */
	protected static $_instance;

	/**
	 *
	 * @var array of Worker_Abstract
	 */
	protected $_items;

	/**
	 * @desc Выполнение текущего задания
	 * @param Task_Collection $tasks
	 */
	protected function _let (Task_Collection $tasks)
	{
		$task = $tasks->current ();

		// на вход результат предыдущего
		$last_response = $tasks->getResponse ();

		if ($last_response)
		{
			$task->getRequest ()
				->setExtra ($last_response->getExtra ())
				->setInput ($last_response->getOutput ());
				
			$task->getResponse ()
				->setHeader ($last_response->getHeader ());
		}

		$worker = $this->get ($task->getWorker ());
		$worker->let ($task, $tasks);

		$tasks->setResponse ($task->getResponse ());
	}

	/**
	 *
	 * @param string $name
	 * @return Worker_Abstract
	 */
	public function get ($name)
	{
		if (!isset ($this->_items [$name]))
		{
			$class = Manager_Abstract::completeClassName ($name, 'Worker');
			Loader::multiLoad ('Worker_Abstract', $class);
			$this->_items [$name] = new $class;
		}
		return $this->_items [$name];
	}

	/**
	 * @desc Возвращает экземпляр класса
	 * @return Worker_Manager
	 */
	public static function getInstance ()
	{
		if (!self::$_instance)
		{
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * @desc Выполнение всех заданий
	 * @param Task_Collection $tasks
	 */
	public function letAll (Task_Collection $tasks)
	{
		if ($tasks->rewind ())
		{
			do
			{
				$this->_let ($tasks);
			} while ($tasks->next ());
		}
	}

}
