<?php

namespace Ice;

/**
 *
 */
class Task_Collection implements \IteratorAggregate, \ArrayAccess
{

	/**
	 *
	 * @var Task_Response
	 */
	protected $_response;

	/**
	 *
	 * @var array of Task
	 */
	protected $_queue;

	/**
	 *
	 * @param Task $task
	 * @return $this
	 */
	public function add (Task $task)
	{
		$this->_queue [] = $task;
		return $this;
	}

	/**
	 * @desc Вернуть первый элемент коллекции, удалив его из коллекции.
	 * @return Task
	 */
	public function current ()
	{
		return current ($this->_queue);
	}

	/**
	 * @return ArrayIterator
	 */
	public function getIterator ()
	{
		return new ArrayIterator ($this);
	}

	/**
	 * @desc Результат выполнения последнего задания
	 * @return Task_Response
	 */
	public function getResponse ()
	{
		return $this->_response;
	}

	public function offsetSet ($offset, $value)
	{
		if (null === $offset)
		{
			$this->_queue [] = $value;
		}
		else
		{
			$this->_queue [$offset] = $value;
		}
	}

	public function offsetExists ($offset)
	{
		return isset ($this->_queue [$offset]);
	}

	public function offsetUnset ($offset)
	{
		unset ($this->_queue [$offset]);
	}

	public function offsetGet($offset)
	{
		return isset ($this->_queue [$offset])
			? $this->_queue [$offset]
			: null;
	}

	/**
	 *
	 * @return Task
	 */
	public function rewind ()
	{
		return reset ($this->_queue);
	}

	public function setResponse ($response)
	{
		$this->_response = $response;
		return $this;
	}

	/**
	 *
	 * @return Task
	 */
	public function next ()
	{
		return next ($this->_queue);
	}


}
