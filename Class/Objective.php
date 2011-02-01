<?php

class Objective implements ArrayAccess, IteratorAggregate, Countable
{
		
	/**
	 * Данные объекта.
	 * @var array
	 */
	protected $_data = array ();
	
	/**
	 * 
	 * @param array $data
	 */
	public function __construct (array $data = array ())
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
		
		return $data + $this->_classVars ();
	}
	
	/**
	 * @return boolean
	 */
	public function __isset ($key)
	{
		return isset ($this->_data [$key]);
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
		$vars = get_class_vars (get_class ($this));
		foreach ($vars as $key => $value)
		{
			if ($key [0] == '_')
			{
				unset ($vars [$key]);
			}
		}
		return $vars;
	}
	
	/**
	 * Данные объекта как массив.
	 * Поля не будут переведены.
	 * @return array
	 */
	public function asArray ()
	{
		return $this->_data + $this->_classVars ();
	}
	
	public function count ()
	{
		return count ($this->asArray ());
	}
	
	/**
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function exists ($key)
	{
		return isset ($this->_data [$key]);
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
	
	public function getIterator ()
	{
        return new ArrayIterator ($this->asArray ());
    }
	
    public function offsetSet ($offset, $value)
    {
        if (is_null ($offset))
        {
            $this->_data [] = $value;
        }
        else
        {
            $this->$offset = $value;
        }
    }
    
    public function offsetExists ($offset)
    {
        return isset ($this->$offset);
    }
    
    public function offsetUnset ($offset)
    {
    	$this->$offset = null;
    	if (array_key_exists ($offset, $this->_data))
    	{
        	unset ($this->_data [$offset]);
    	}
    }
    
    public function offsetGet ($offset)
    {
        return $this->$offset;
    }
	
}