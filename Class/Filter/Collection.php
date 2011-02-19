<?php
/**
 * 
 * @desc Коллекция фильтров
 * @author Илья
 * @package IcEngine
 *
 */
class Filter_Collection 
{
	protected $_filters;
	
	public function __construct ()
	{
		$this->_filters = array ();
	}
	
	public function append ($filter)
	{
		$this->_filters [] = $filter;
		return $this;
	}
	
	public function apply (&$data, $fields = null)
	{
		if (!is_null ($fields))
		{
			$fields = (array) $fields;
		}
		
		if (!is_array ($data))
		{
			for ($i = 0, $count = sizeof ($this->_filters); $i < $count; $i++)
			{
				$data = $this->_filters [$i]->filter ($data);
			}
		}
		else
		{
			foreach ($data as $key=>&$value)
			{
				if ($fields && !in_array ($key, $fields))
				{
					continue;
				}
				for ($i = 0, $count = sizeof ($this->_filters); $i < $count; $i++)
				{
					$value = $this->_filters [$i]->filter ($value);
				}
			}
		}
		
		return $this;
	}
}