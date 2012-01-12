<?php
/**
 *
 * @desc Класс для формирования HTTP заголовка
 * @author Yury Shvedov
 *
 */
class Http_Header
{

	/**
	 * @desc Буффер хедера
	 * @var array of string
	 */
	protected $_buffer = array ();

	/**
	 * @desc Добавление заголовка
	 * @see header()
	 * @param string $string
	 * @param boolean $replace [optional]
	 * @param integer $http_response_code [optional]
	 * @return $this
	 */
	public function add ($string, $replace = null, $http_response_code = null)
	{
		$this->_buffer [] = func_get_args ();
		return $this;
	}

	/**
	 * @desc Очистка буффера
	 * @return $this
	 */
	public function clear ()
	{
		$this->_buffer = array ();
		return $this;
	}

	/**
	 * @desc Возвращает буффер
	 * @return array of string
	 */
	public function getBuffer ()
	{
		return $this->_buffer;
	}

	/**
	 * @desc Отправляет заголовок
	 * @return $this
	 */
	public function send ()
	{
		foreach ($this->_buffer as $args)
		{
			call_user_func_array ('header', $args);
		}
		return $this;
	}

}
