<?php

namespace Ice;

/**
 *
 * @desc Заместитель классов
 * @author Ilya Kolesnikov, Yury Shvedov
 *
 */
class Mock
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
	 * @desc Название класса, который будет замещаться
	 * @var string
	 */
	protected $_className;

	/**
	 * (non-PHPDoc)
	 * @param string $class_name
	 */
	public function __construct ($class_name)
	{
		$this->_className = $class_name;
	}

	/**
	 * (non-PHPDoc)
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
		return $this->fields [$key];
	}

	/**
	 * (non-PHPDoc)
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set ($key, $value)
	{
		$this->fields [$key] = $value;
	}

	/**
	 * (non-PHPDoc)
	 */
	private function __clone ()
	{

	}

	/**
	 * (non-PHPDoc)
	 * @param string $method
	 * @param array <mixed> $params
	 * @return mixed
	 */
	public function __call ($method, $params)
	{
		if (array_key_exists ($method, $this->_methodsReturn))
		{
			return $this->_methodsReturn;
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
	 * @desc Установка значения поля
	 * @param string $field_name
	 * @param mixed $value
	 * @return $this
	 */
	public function registerField ($field_name, $value)
	{
		$this->fields [$field_name] = $value;
		return $this;
	}

	/**
	 * @desc Регистрирует метод для заменителя
	 * @param string $method_name Название метода
	 * @param callback $method Колбэк
	 * @return $this
	 */
	public function registerMethodCallback ($method_name, $callback)
	{
		$this->_methodsCallback [$method_name] = $method;
		return $this;
	}

	/**
	 * @desc Метод всегда будет возвращать одно значение
	 * @param string $method_name Название метода
	 * @param mixed $return Результат
	 * @return $this
	 */
	public function registerMethodReturn ($method_name, $return)
	{
		$this->_methodsReturn [$method_name] = $return;
		return $this;
	}

}