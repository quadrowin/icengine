<?php

namespace Ice;

/**
 *
 * @desc Dependecy Injection
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Dependency_Injection_Container
{

	/**
	 * @desc Алиасы классов
	 * @var array of string
	 */
	protected $_classes = array ();

	/**
	 * @desc Инициализированные экземпляры
	 * @var array of array of object
	 */
	protected $_instances = array ();

	/**
	 *
	 * @param string $class
	 * @param boolean $autocreate [optional] Автосоздание
	 * @return object
	 */
	public function getInstance ($class, $autocreate = true)
	{
		if (
			!isset ($this->_instances [$class]) ||
			!$this->_instances [$class]
		)
		{
			if (!$autocreate)
			{
				return null;
			}

			$this->pushInstance (
				$class,
				$this->getNewInstance ($class)
			);
		}

		return end ($this->_instances [$class]);
	}

	/**
	 * @desc Создает и возвращает новый экземпляр класса
	 * @param string $class
	 * @return object
	 */
	public function getNewInstance ($class)
	{
		$class = self::getRealClass ($class);
		$class = Loader::load ($class);
		$reflection = new \ReflectionClass ($class);
		$args = func_get_args ();
		array_shift ($args);
		return $reflection->newInstanceArgs ($args);
	}

	/**
	 * @desc Возвращает реальный лкасс
	 * @param string $class
	 * @return string
	 */
	public function getRealClass ($class)
	{
		while (isset ($this->_classes [$class]))
		{
			$class = $this->_classes [$class];
		}

		return $class;
	}

	/**
	 *
	 * @param string $class
	 * @return boolean
	 */
	public function hasInstance ($class)
	{
		return
			isset ($this->_instances [$class]) &&
			$this->_instances [$class];
	}

	/**
	 * @desc Устанавлиает экземпляр
	 * @param string $class
	 * @param mixed $object
	 * @return $this
	 */
	public function pushInstance ($class, $object)
	{
		$this->_instances [$class][] = $object;
		return $this;
	}

	/**
	 *
	 * @param type $class
	 * @return Di_Container
	 */
	public function popInstance ($class)
	{
		array_pop ($this->_instances [$class]);
		return $this;
	}

	/**
	 * @desc Подмена класса
	 * @param string $class Исходный класс
	 * @param string $alias Класс реализации
	 * @return $this
	 */
	public function setRealClass ($source, $real)
	{
		$this->_classes [$source] = $real;
		return $this;
	}

}
