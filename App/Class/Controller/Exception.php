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
class Controller_Exception extends \Exception
{

	/**
	 * @desc Информация для отладки
	 * @var array
	 */
	protected $_data;

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
	 * @desc Создает и возвращает экземпляр
	 * @param string $message [optional] Сообщение
	 * @param string $code [optional] Код исключения
	 * @return self
	 */
	public static function create ($message = null, $code = null)
	{
		return new self ($message, $code);
	}

	/**
	 * @desc Возвращает информацию для отладки
	 * @param string $key
	 * @return mixed
	 */
	public function getData ($key)
	{
		return isset ($this->_data [$key]) ? $this->_data [$key] : null;
	}

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
	 * @desc Устанавлиает код ошибки
	 * @param integer $code
	 * @return Controller_Exception
	 */
	public function setCode ($code)
	{
		$this->code = $code;
		return $this;
	}

	/**
	 * @desc Сохраняет информацию для отладки
	 * @param array $data
	 * @return Controller_Exception
	 */
	public function setData (array $data)
	{
		$this->_data = $data + $this->_data;
		return $this;
	}

	/**
	 * @desc Устанавливает сообщение об ошибке
	 * @param string $message
	 * @return Controller_Exception
	 */
	public function setMessage ($message)
	{
		$this->message = $message;
		return $this;
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