<?php

namespace Ice;

/**
 *
 * @author Yury Shvedov
 *
 */
class Task
{

	/**
	 * @desc Исчключение
	 * @var \Exception
	 */
	protected $_exception;

	/**
	 * @desc Запрос
	 * @var Task_Request
	 */
	protected $_request;

	/**
	 * @desc Ответ
	 * @var Task_Response
	 */
	protected $_response;

	/**
	 * @desc Исполнитель
	 * @var string
	 */
	protected $_worker;

	/**
	 * @desc
	 * @param string $worker
	 * @param array $extra
	 */
	public function __construct ($worker, array $extra = array ())
	{
		$this->_worker = $worker;

		Loader::multiLoad ('Task_Request', 'Task_Response');

		$this->_request = new Task_Request;
		$this->_request->setExtra ($extra);

		$this->_response = new Task_Response;
	}

	/**
	 * @desc Возвращает возникшее исключение
	 * @return \Exception
	 */
	public function getException ()
	{
		return $this->_exception;
	}

	/**
	 * @desc Возвращает запрос
	 * @return Task_Request
	 */
	public function getRequest ()
	{
		return $this->_request;
	}

	/**
	 * @desc Результат выполнения
	 * @return Task_Response
	 */
	public function getResponse ()
	{
		return $this->_response;
	}

	/**
	 * @desc Исполнитель
	 * @return string
	 */
	public function getWorker ()
	{
		return $this->_worker;
	}

	/**
	 * @desc Исключение
	 * @param \Exception $exception
	 * @return $this
	 */
	public function setException ($exception)
	{
		$this->_exception = $exception;
		return $this;
	}

}
