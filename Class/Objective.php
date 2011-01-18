<?php

class Objective 
{
		
	/**
	 * Данные объекта.
	 * @var array
	 */
	protected $_data = array ();
	
	public function __construct (array $data)
	{
		foreach ($data as $key => $value)
		{
			$this->$key = $value;
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
		
		$vars = get_class_vars (get_class ($this));
		foreach ($vars as $var)
		{
			$data [$var] = $this->$var;
		}
		
		return array_merge ($data, $this->_classVars ());
	}
	
	/**
	 * @return boolean
	 */
	public function __isset ($key)
	{
		return $this->exists ($key);
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
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
	    return isset ($this->_data [$key]) ? $this->_data [$key] : null;
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set ($key, $value)
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
	
	/**
	 * Значения полей объекта в виде массива.
	 * @return array
	 */
	protected function _classVars ()
	{
		$result = array ();
		$vars = get_class_vars (get_class ($this));
		foreach ($vars as $var)
		{
			if ($var [0] != '_')
			{
				$result [$var] = $this->$var;
			}
		}
		return $result;
	}
	
	/**
	 * Данные объекта как массив.
	 * Поля не будут переведены.
	 * @return array
	 */
	public function asArray ()
	{
		return array_merge ($this->_data, $this->_classVars ());
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
					$result = $result [$value];
				}
			}
			else
			{
				$result = $result [$path];
			}
		}
		return $result;
	}
	
}