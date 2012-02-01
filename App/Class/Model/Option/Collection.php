<?php

namespace Ice;

/**
 *
 * @desc Коллекция опций модели.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Model_Option_Collection
{

	/**
	 * @desc Метод опции, вызываемый после выполнения запроса
	 */
	const METHOD_AFTER = 'after';

	/**
	 * @desc Метод опции, вызываемый перед выполнением запроса
	 */
	const METHOD_BEFORE = 'before';

    /**
     * @desc Коллекция, к которой привязаны опции.
     * Необходима для определения названий классов опций.
     * @var Model_Collection
     */
	protected $_collection;

	/**
	 * @desc Опции
	 * @var array <Model_Option>
	 */
	protected $_items = array ();

	/**
	 * @desc Создает и возвращает коллекцию опций.
	 * @param Model_Collection $collection
	 */
	public function __construct ($collection)
	{
		Loader::load ('Model_Option');
		$this->_collection = $collection;
	}

	/**
	 * @desc Вызвать метод для всех опций
	 * @param Query $query Запрос
	 * @param string $method Название метода
	 */
	protected function _execute ($query, $method)
	{
		foreach ($this->_items as $option)
		{
			/* @var $option Model_Option */
			if (method_exists ($option, $method))
			{
				$option->query = $query;
				$reflection = new \ReflectionMethod ($option, $method);
				$params = $reflection->getParameters ();
				foreach ($params as &$param) {
					$name = $param->name;
					$param = isset ($option->params [$name])
						? $option->params [$name]
						: null;
				}
				call_user_func_array (array ($option, $method), $params);
			}
		}
	}

	/**
	 * @desc Добавление опции
	 * @param mixed $item
	 * @return Model_Option
	 */
	public function add ($item)
	{
		// Случай, если передано только название
		if (is_string ($item))
		{
			$item = array ('name' => $item);
		}

		if (is_array ($item))
	    {
			$class = Model_Option::getClassName (
				isset ($item [0]) ? $item [0] : $item ['name'],
				$this->_collection
			);
			// Неизвестно, старая это или новая опция.
			if (Loader::tryLoad ($class))
			{
				// Это новая опция
				$item = new $class (
					$this->_collection,
					$item
				);
			}
			else
			{
				$item = Model_Option::create (
					'::Old',
					$this->_collection,
					$item
				);
			}

	    }

	    if ($item instanceof Model_Collection_Option_Abstract)
	    {
			Loader::load ('Model_Option_Old');
	        $item = new Model_Option_Old (
	        	$this->_collection,
	        	array (
					'option'	=> $item
				)
	        );
	    }

		if (!($item instanceof Model_Option))
		{
			throw new Zend_Exception ('Unsupported type: ' . gettype ($item));
		}

	    return $this->_items [] = $item;
	}

	/**
	 *
	 * @param Query $query
	 */
	public function executeAfter (Query $query)
	{
		$this->_execute ($query, self::METHOD_AFTER);
	}

	/**
	 *
	 * @param Query $query
	 */
	public function executeBefore (Query $query)
	{
		$this->_execute ($query, self::METHOD_BEFORE);
	}

	/**
	 * @return Model_Collection
	 */
	public function getCollection ()
	{
		return $this->_collection;
	}

	/**
	 * @return array
	 */
	public function getItems ()
	{
		return $this->_items;
	}

	/**
	 * @desc
	 * @param mixed $options
	 */
	public function setItems ($options)
	{
		$this->_items = array ();
		$options = (array) $options;
		for ($i = 0, $count = count ($options); $i < $count; ++$i)
		{
		    $this->add ($options [$i]);
		}
	}
}