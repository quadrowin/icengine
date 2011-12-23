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
	protected $_class;

	/**
	 * @desc Создает и возвращает экземпляр
	 * @param string $class Название замещаемого класса
	 */
	public function __construct ($class = null)
	{
		$this->_className = $class;
	}

	/**
	 * (non-PHPDoc)
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
		return $this->_fields [$key];
	}

	/**
	 * (non-PHPDoc)
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set ($key, $value)
	{
		$this->_fields [$key] = $value;
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

}