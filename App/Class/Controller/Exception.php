<?php

namespace Ice;

/**
 *
 * @desc Исключения контроллера
 * @package Ice
 * @author Yury Shvedov
 * @tutorial
 * throw Controller_Exception::create('accessDenied');
 * throw Controller_Exception::create()
 *		->setCode(3)
 *		->setTemplate(__METHOD__ . '/error');
 *
 */
class Controller_Exception extends Exception
{

	/**
	 * @desc Задача контроллера
	 * @var Controller_Task
	 */
	protected $_task;

	/**
	 * @desc Шаблон
	 * @var string
	 */
	protected $_template;

	/**
	 * @desc Возвращает задание контроллера
	 * @return Controller_Task
	 */
	public function getTask ()
	{
		return $this->_task;
	}

	/**
	 * @desc Возвращает шабон
	 * @param boolean $auto [optional] Автогенерация пути до шаблона,
	 * если он не был явно задан.
	 * @return string
	 */
	public function getTemplate ($auto = true)
	{
		if ($auto && !$this->_template && $this->_task && $this->message)
		{
			return $this->_task->getTemplate () . '/' . $this->message;
		}
		return $this->_template;
	}

	/**
	 * @desc Задача, в которой произошло исключение
	 * @param Controller_Task $task
	 * @return Controller_Exception
	 */
	public function setTask (Controller_Task $task)
	{
		$this->_task = $task;
		return $this;
	}

	/**
	 * @desc Устанавлиает шаблон ошибки
	 * @param string $template
	 * @return Controller_Exception
	 */
	public function setTemplate ($template)
	{
		$this->_template = $template;
		return $this;
	}

}