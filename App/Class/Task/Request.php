<?php

namespace Ice;

/**
 *
 * @desc
 * @author Yury Shvedov
 *
 */
class Task_Request extends Request
{

	protected $_extra = array ();

	protected $_input;

	/**
	 *
	 * @param string $key [optional]
	 * @return mixed
	 */
	public function getExtra ($key = null)
	{
		return (null === $key)
			? $this->_extra
			: (isset ($this->_extra [$key]) ? $this->_extra [$key] : null);
	}

	/**
	 *
	 * @return Data_Transport
	 */
	public function getInput ()
	{
		return $this->_input;
	}

	/**
	 *
	 * @return $this
	 */
	public function setExtra (array $data)
	{
		$this->_extra = array_merge ($this->_extra, $data);
		return $this;
	}

	/**
	 *
	 * @param Data_Transport $input
	 */
	public function setInput ($input)
	{
		$this->_input = $input;
	}

}
