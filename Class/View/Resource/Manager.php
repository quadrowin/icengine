<?php

class View_Resource_Manager
{
	
	/**
	 * Тип ресурса - CSS
	 * @var string
	 */
	const CSS = 'css';
	
	/**
	 * Тип ресурса - JS
	 * @var string
	 */
	const JS = 'js';
	
	/**
	 * Ресурсы
	 * 
	 * @var array
	 */
	protected $_resources = array ();
	
	/**
	 * Добавление ресурса
	 * 
	 * @param string|array $data
	 * 		Тип ресурса или массив пар (тип => ссылка)
	 * @param string $type [optional]
	 * 		Ссылка 
	 */
	public function add ($data, $type = null)
	{
		if (is_array ($data))
		{
			foreach ($data as $d)
			{
				$this->add ($d);
			}
		}
		else
		{
			if (is_null ($type))
			{
				$type = strtolower (substr (strrchr ($data, '.'), 1));
			}
			
			if (!isset ($this->_resources [$type]))
			{
				$this->_resources [$type] = array ();
			}
			
			$this->_resources [$type][] = $data;
		}
	}
	
	/**
	 * Возвращает связанные данные по ресурсам
	 * 
	 * @param string $type
	 * 		Тип
	 * @return array
	 * 		Ресурсы
	 */
	public function getData ($type)
	{
		if (isset ($this->_resources [$type]))
		{
			return array_unique ($this->_resources [$type]);
		}
		
		return array ();
	}
	
}