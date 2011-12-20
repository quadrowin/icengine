<?php

namespace Ice;

/**
 *
 * @desc Базовый класс исключений
 * @author Yury Shvedov
 * @package Ice
 * @tutorial
 * throw Exception::create('accessDenied');
 *
 */
class Exception extends \Exception {

	/**
	 * @desc Информация для отладки
	 * @var array
	 */
	protected $_data;

	/**
	 * @desc Создает и возвращает экземпляр
	 * @param string $message [optional] Сообщение
	 * @param string $code [optional] Код исключения
	 * @return $this
	 */
	public static function create ($message = null, $code = null)
	{
		$class = get_called_class ();
		return new $class ($message, $code);
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

}
