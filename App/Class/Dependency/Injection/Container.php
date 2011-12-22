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
	 * @desc Экземпляры для классов
	 * @var array of array of object
	 */
	protected $_instances = array ();

	/**
	 * @desc Алиасы классов
	 * @var array of string
	 */
	protected $_classes = array ();

	/**
	 * @desc Идентификатор экземпляра класса для объекта
	 * @param string $class
	 * @param object $context
	 * @return string
	 */
	protected function _getObjectMark ($class, $context)
	{
		return 'O' . spl_object_hash ($context) . '-' . $class;
	}

	/**
	 * @desc Идентификатор экземпляра класса для класса
	 * @param string $class
	 * @param string $context
	 * @return string
	 */
	protected function _getClassMark ($class, $context)
	{
		return 'C' . $context . '-' . $class;
	}

	/**
	 * @desc Идентификатор экземпляра класса по умолчанию
	 * @param string $class
	 * @return string
	 */
	protected function _getPublicMark ($class)
	{
		return 'P' . $class;
	}

	/**
	 * @desc Удаление последнего экземпляра из стека
	 * @param string $mark
	 */
	protected function _popInstance ($mark)
	{
		array_pop ($this->_instances [$mark]);
		if (!$this->_instances [$mark])
		{
			unset ($this->_instances [$mark]);
		}
	}

	/**
	 * @desc Помещение в стек экземпляра
	 * @param string $mark
	 * @param object $instance
	 */
	protected function _pushInstance ($mark, $instance)
	{
		$this->_instances [$mark][] = $instance;
	}

	/**
	 * @desc Возвращает экземпляр, установленный для класса
	 * @param string $class Класс результата
	 * @param string $context Запрашивающий класс
	 * @return object экземпляр $class
	 */
	public function getClassInstance ($class, $context)
	{
		$mark = $this->_getClassMark ($class, $context);

		if (!isset ($this->_instances [$mark]))
		{
			$instance = $this->getPublicInstance ($class);
			$this->_pushInstance ($mark, $instance);
			return $instance;
		}

		return end ($this->_instances [$mark]);
	}

	/**
	 * @desc Возвращает экземпляр класса в соответсвии с контекстом
	 * @param string $class Название класса
	 * @param mixed $context Контекст. Название запрашиваюего класса или
	 * объект, которому необходим экземпляр $class.
	 * @return object Экземпляр, соответсвующий контексту.
	 */
	public function getInstance ($class, $context = null)
	{
		if ($context)
		{
			return is_object ($context)
				? $this->getObjectInstance ($class, $context)
				: $this->getClassInstance ($class, $context);
		}

		return $this->getPublicInstance ($class);
	}

	/**
	 * @desc Возвращает экземпляр по умолчанию
	 * @param string $class Название класса
	 * @param object $context Запрашивающий объект
	 * @return object Экземпляр класса
	 */
	public function getObjectInstance ($class, $context)
	{
		$mark = $this->_getObjectMark ($class, $context);

		if (!isset ($this->_instances [$mark]))
		{
			$instance = $this->getClassInstance ($class, get_class ($context));
			$this->_pushInstance ($mark, $instance);
			return $instance;
		}

		return end ($this->_instances [$mark]);
	}

	/**
	 * @desc Возвращает экземпляр по умолчанию
	 * @param string $class Название класса
	 * @return object Экземпляр класса
	 */
	public function getPublicInstance ($class)
	{
		$mark = $this->_getPublicMark ($class);

		if (!isset ($this->_instances [$mark]))
		{
			$instance = $this->getNewInstance ($class);
			$this->_pushInstance ($mark, $instance);
			return $instance;
		}

		return end ($this->_instances [$mark]);
	}

	/**
	 * @desc Создает и возвращает новый экземпляр класса
	 * @param string $class
	 * @param string|object $context Класс или объект, запрашивающий экземпляр
	 * @return object
	 */
	public function getNewInstance ($class, $context = null)
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
	 * @desc
	 * @param string $class
	 * @param object $object
	 * @param string $context
	 * @return $this
	 */
	public function pushClassInstance ($class, $object, $context)
	{
		$mark = $this->_getClassMark ($class, $context);
		$this->_pushInstance ($mark, $object);
		return $this;
	}

	/**
	 * @desc Устанавлиает экземпляр
	 * @param string $class
	 * @param mixed $object
	 * @return $this
	 */
	public function pushInstance ($class, $object, $context = null)
	{
		if ($context)
		{
			return is_object ($context)
				? $this->pushObjectInstance ($class, $object, $context)
				: $this->pushClassInstance ($class, $object, $context);
		}

		return $this->pushPublicInstance ($class, $object);
	}

	/**
	 * @desc
	 * @param string $class
	 * @param object $object
	 * @param object $context
	 * @return $this
	 */
	public function pushObjectInstance ($class, $object, $context)
	{
		$mark = $this->_getObjectMark ($class, $context);
		$this->_pushInstance ($mark, $object);
		return $this;
	}

	/**
	 * @desc
	 * @param string $class
	 * @param object $object
	 * @return $this
	 */
	public function pushPublicInstance ($class, $object)
	{
		$mark = $this->_getPublicMark ($class);
		$this->_pushInstance ($mark, $object);
		return $this;
	}

	/**
	 *
	 * @param string $class
	 * @param string $context
	 * @return $this
	 */
	public function popClassInstance ($class, $context)
	{
		$mark = $this->_getClassMark ($class, $context);
		$this->_popInstance ($mark);
		return $this;
	}

	/**
	 * @desc
	 * @param string $class
	 * @param mixed $context
	 * @return $this
	 */
	public function popInstance ($class, $context = null)
	{
		if ($context)
		{
			return is_object ($context)
				? $this->popObjectInstance ($class, $object, $context)
				: $this->popClassInstance ($class, $object, $context);
		}

		return $this->popPublicInstance ($class);
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
