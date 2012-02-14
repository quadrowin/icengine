<?php

namespace Ice;

/**
 *
 * @desc Источник данных для Mock объекта
 * @author Yury Shvedov
 *
 */
class Mock_Provider
{

	/**
	 * @desc Поля
	 * @var array of mixed
	 */
	protected $_fields = array ();

	/**
	 * @desc Зарегистированные методы
	 * @var array of callback
	 */
	protected $_methodsCallback = array ();

	/**
	 * @desc Результаты методов
	 * @var array of mixed
	 */
	protected $_methodsReturn = array ();

	/**
	 * @desc
	 * @param object $mock
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function callMethod ($mock, $method, $args)
	{
		if (array_key_exists ($method, $this->_methodsReturn))
		{
			return $this->_methodsReturn [$method];
		}

		if (isset ($this->_methodsCallback [$method]))
		{
			return call_user_func_array (
				$this->_methodsCallback [$method],
				$params
			);
		}
	}

	/**
	 * @desc for __get magic method
	 * @param string $key
	 * @return mixed
	 */
	public function getField ($key)
	{
		return $this->_fields [$key];
	}

	/**
	 * @desc Возвращает новый экземпляр
	 * @return self
	 */
	public static function newInstance ()
	{
		return new self;
	}

	/**
	 * @desc Установка значения поля
	 * @param string $field Название поля
	 * @param mixed $value Значение
	 * @return $this
	 */
	public function registerField ($field, $value)
	{
		$this->_fields [$field] = $value;
		return $this;
	}

	/**
	 * @desc Регистрирует метод для заменителя
	 * @param string $method Название метода
	 * @param callback $method Колбэк
	 * @return $this
	 */
	public function registerMethodCallback ($method, $callback)
	{
		$this->_methodsCallback [$method] = $callback;
		return $this;
	}

	/**
	 * @desc Метод всегда будет возвращать одно значение
	 * @param string $method Название метода
	 * @param mixed $return Результат
	 * @return $this
	 */
	public function registerMethodReturn ($method, $return)
	{
		$this->_methodsReturn [$method] = $return;
		return $this;
	}

	/**
	 * @desc for __set magic method
	 * @param string $key
	 * @param mixed $value
	 */
	public function setField ($key, $value)
	{
		$this->_fields [$key] = $value;
	}

}
