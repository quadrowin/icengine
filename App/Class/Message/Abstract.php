<?php
/**
 * 
 * Базовый класс для сообщений
 * @author Юрий
 * @package IcEngine
 *
 */
class Message_Abstract
{
	
	/**
	 * Дополнительные параметры сообщений
	 * @var array
	 */
	protected $_data = array ();
	
	/**
	 * Тип сообщения
	 * @var string
	 */
	protected $_type;
	
	/**
	 * 
	 * @param array $data
	 * 		Дополнитльные параметры
	 * @param string $type
	 * 		Тип сообщений
	 */
	public function __construct (array $data = array (), $type = null)
	{
		$this->_data = $data;
		
		if (!$type)
		{
		    $this->_type = substr (get_class ($this), strlen ('Message_'));
		}
	}
	
	/**
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
		return $this->_data [$key];
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set ($key, $value)
	{
		$this->_data [$key] = $value;
	}
	
	/**
	 * Вызвать обработчик
	 * @param callback $callback
	 */
	public function notify ($callback)
	{
		if (
			is_array ($callback) && 
			isset ($callback [0], $callback [1]) &&
			is_string ($callback [0])
		)
		{
			// Подгружаем класс для вызова статического метода.
			Loader::load ($callback [0]);
		}
		return call_user_func ($callback, $this);
	}
	
	/**
	 * Тип сообщения
	 * @return string
	 */
	public function type ()
	{
		return $this->_type;
	}
	
}