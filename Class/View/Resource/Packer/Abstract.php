<?php

abstract class View_Resource_Packer_Abstract
{
	
	const REFRESH_TIME = 300;
	
	/**
	 * Текущий ресурс
	 * @var View_Resource
	 */
	protected $_currentResource;
	
	/**
	 * Настройки
	 * @var array
	 */
	public $config = array (
		/**
		 * Префикс файла с упакованными ресурсами.
		 * @var string
		 */
		'pack_file_prefix'	=> "/* Packed by IcEngine {\$time} */\n",
		
		/**
		 * Префикс каждого скрипта
		 * @var string
		 */
		'pack_item_prefix' 	=> "/* {\$source} */\n",
	
		/**
		 * Постфикс каждого скрипта
		 * @var string
		 */
		'pack_item_postfix'	=> "\n\n",
	
		/**
		 * Файл для хранения состояния
		 * @var string
		 */
		'pack_state_file'	=> ''
	);
	
	public function _compileFilePrefix ()
	{
		return str_replace (
			'{$time}',
			date ('Y-m-d H:i:s'),
			$this->config ['pack_file_prefix']
		);
	}
	
	/**
	 * Проверяет существование валидного кэша для ресурсов.
	 * @param array $resources
	 * @param string $result_file
	 * @return boolean
	 */
	public function cacheValid (array $resources, $result_file)
	{
		if (
			!$result_file || 
			!file_exists ($result_file) || 
			!$this->config ['pack_state_file'] ||
			!file_exists ($this->config ['pack_state_file'])
		)
		{
			return false;
		}
		
		$state = file_get_contents ($this->config ['pack_state_file']);
		$state = json_decode ($state, true);
		
		if (!$state)
		{
			return false;
		}
		
		if (
			!isset ($state ['result_file']) ||
			!isset ($state ['result_time']) ||
			!isset ($state ['resources']) ||
			$state ['result_file'] != $result_file ||
			!is_array ($state ['resources']) ||
			count ($state ['resources']) != count ($resources)
		)
		{
			return false;
		}
		
		$delta_time = time () - $state ['result_time'];
		if ($delta_time > self::REFRESH_TIME)
		{
			$delta_time -= self::REFRESH_TIME;
			
			if (
				$delta_time > self::REFRESH_TIME ||
				rand (0, $delta_time) == 0
			)
			{
				return false;
			}
		} 
		
		foreach ($state ['resources'] as $i => $res)
		{
			if (
				$res ['filemtime'] != $resources [$i]->filemtime () ||
				$res ['file_path'] != $resources [$i]->filePath () 
			)
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Объединение результатов упаковщика.
	 * @param array $packages
	 * @return string
	 */
	public function compile (array $packages)
	{
		return
			$this->_compileFilePrefix () . 
			implode ("\n", $packages);
	}
	
	/**
	 * Пакование ресурсов в строку или указанный файл.
	 * 
	 * @param array <string> $resources
	 * 		Ресурсы
	 * @param string $result_file [optional]
	 * 		Файл для сохранения результата.
	 * @return mixed|string
	 * 		
	 */
	public function pack (array $resources, $result_file = '')
	{
		$packages = array ();
		
		if ($this->cacheValid ($resources, $result_file))
		{
			return true;
		}
		
		foreach ($resources as $resource)
		{
			if (!$resource->filePath)
			{
				$resource->filePath = 
					rtrim (IcEngine::root (), '/') . $resource->href;
				$resource->filemtime = filemtime ($resource->filePath);
			}
			
			$this->_currentResource = $resource;
			
			$packages [] = $this->packOne ($resource);
		}
		
		$packages = $this->compile ($packages);
		
		if ($result_file)
		{
			$this->saveValidState ($resources, $result_file);
			return file_put_contents ($result_file, $packages);
		}

		return $packages;
	}
	
	/**
	 * Паковка одного ресурса
	 * @param View_Resource $resource
	 * 		Ресурс.
	 * @return string
	 * 		Запакованная строка, содержащая ресурс.
	 */
	abstract public function packOne (View_Resource $resource);
	
	/**
	 * 
	 * @param array $resources
	 * @param string $result_file
	 */
	public function saveValidState (array $resources, $result_file)
	{
		if (
			!$result_file ||  
			!$this->config ['pack_state_file']
		)
		{
			return false;
		}
		
		$state = array (
			'result_file'	=> $result_file,
			'result_time'	=> time (),
			'resources'		=> array ()
		);
		
		foreach ($resources as $resource)
		{
			$state ['resources'][] = array (
				'file_path'	=> $resource->filePath (),
				'filemtime'	=> $resource->filemtime ()
			);
		}
		
		$state = json_encode ($state);
		file_put_contents ($this->config ['pack_state_file'], $state);
	}
	
}