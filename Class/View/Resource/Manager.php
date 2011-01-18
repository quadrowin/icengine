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
	 * @var array <View_Resource_Item>
	 */
	protected $_resources = array ();
	
	/**
	 * Добавление ресурса
	 * 
	 * @param string|array $data
	 * 		Ссылка на ресурс или массив пар (тип => ссылка)
	 * @param string $type [optional]
	 * 		Тип ресурса
	 * @param array $flags
	 * 		Параметры
	 */
	public function add ($data, $type = null, array $options = array ())
	{
		if (is_array ($data))
		{
			foreach ($data as $d)
			{
				$this->add ($d, $type, $options);
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
			else 
			{
				foreach ($this->_resources [$type] as &$exists)
				{
					if ($exists->href == $data)
					{
						$exists ['options'] = array_merge (
							$exists ['options'],
							$options
						);
						return;
					}
				}
			}
			
			
			$this->_resources [$type][] = array (
				'href'		=> $data,
				'options'	=> $options
			);
		}
	}
	
	/**
	 * @desc
	 * 		Возвращает связанные данные по ресурсам.
	 * 
	 * @param string $type
	 * 		Тип
	 * @return array
	 * 		Ресурсы
	 */
	public function getData ($type)
	{
		if (!isset ($this->_resources [$type]))
		{
			return array ();
		}
		
		return $this->_resources [$type];
	}
	
}