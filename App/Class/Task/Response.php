<?php

namespace Ice;

/**
 *
 * @desc
 * @author Yury Shvedov
 *
 */
class Task_Response
{

	protected $_extra = array ();

	protected $_header;

	/**
	 *
	 * @var Data_Transport
	 */
	protected $_output;


	public function __construct ()
	{
		Loader::multiLoad ('Data_Transport', 'Data_Provider_Buffer');
		$this->_output = new Data_Transport;
		$this->_output->appendProvider (new Data_Provider_Buffer);
	}

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
	 * @return Http_Header
	 */
	public function getHeader ()
	{
		return $this->_header;
	}

	/**
	 *
	 * @return Data_Transport
	 */
	public function getOutput ()
	{
		return $this->_output;
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
	 * @desc Устанавливает транспорт
	 * @param Data_Transport $output
	 * @return $this
	 */
	public function setOutput ($output)
	{
		$this->_output = $output;
		return $this;
	}

}
