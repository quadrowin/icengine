<?php

namespace Ice;

/**
 *
 * @desc Абстрактный класс опции коллекции.
 * @author Юрий Шведов, Илья Колесников
 * @package Ice
 *
 */
abstract class Model_Collection_Option_Abstract
{

	/**
	 * @desc Название опции
	 * @var string
	 */
	private $_name;

	/**
	 * @desc Создает и возвращает опцию
	 */
	public function __construct ()
	{
		$class = get_class ($this);
		$delim = '_Collection_Option_';
		$pos = strrpos ($class, $delim);

		$this->_name = substr (
			$class,
			$pos + strlen ($delim)
		);
	}

	/**
	 * @desc Вызывается после выполения запроса.
	 * @param Model_Collection $collection
	 * @param Query $query
	 * @param array $params
	 */
	public function after (Model_Collection $collection,
		Query $query, array $params)
	{

	}

	/**
	 * @desc Вызывается перед выполнением запроса.
	 * Переменная <i>$query</i> отличается от запроса, возвращаемого методом
	 * <i>$colleciton->query()</i>. По умолчанию эта переменная - клон
	 * изначального запроса коллекции, на который наложены опции.
	 * @param Model_Collection $collection
	 * @param Query $query
	 * @param array $params
	 */
	public function before (Model_Collection $collection,
		Query $query, array $params)
	{

	}

	/**
	 * @desc Возвращает название опции
	 * @return string
	 */
	public function getName ()
	{
		return $this->_name;
	}



}