<?php

class Objective 
{
		
	/**
	 * 
	 * @var array
	 */
	protected $_data;
	
	public function __construct (array $data)
	{
		$this->_data = array ();
		foreach ($data as $key => $value)
		{
			if (is_array ($value))
			{
				$this->_data [$key] = new self ($value);
			}
			else
			{
				$this->_data [$key] = $value;
			}
		}
	}
	
	/**
	 * @return array
	 */
	public function __toArray ()
	{
		$data = array ();
		foreach ($this->_data as $key => $value) 
		{
			if ($value instanceof Objective) 
			{
				$data [$key] = $value->__toArray ();
			} 
			else
			{
				$data [$key] = $value;
			}
		}
		return $data;
	}
	
	/**
	 * @return boolean
	 */
	public function __isset ($key)
	{
		return $this->exists ($key);
	}
	
	/**
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
	    return isset ($this->_data [$key]) ? $this->_data [$key] : null;
	}
	
	public function __clone ()
	{
		$data = array ();
		foreach ($this->_data as $key => $value) 
		{
			if ($value instanceof Objective) 
			{
				$data [$key] = clone $value;
			}
			else 
			{
				$data [$key] = $value;
			}
		}
		$this->_data = $data;
	}
	
	/**
	 * Данные объекта как массив
	 * @return array
	 */
	public function asArray ()
	{
		return $this->_data;
	}
	
	/**
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function exists ($key)
	{
		return isset ($this->_data[$key]);
	}
	
	/**
	 * @param string $path
	 * @return mixed
	 */
	public function get ($path = '')
	{
		$result = $this->__toArray ();
		if ($path)
		{
			if (strpos ($path, '.'))
			{
				$path = explode ('.', $path);
				foreach ($path as $value)
				{
					$result = $result[$value];
				}
			}
			else
			{
				$result = $result[$path];
			}
		}
		return $result;
	}
	
}