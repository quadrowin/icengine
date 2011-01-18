<?php

abstract class View_Resource_Packer_Abstract
{
	
	/**
	 * Текущий ресурс
	 * @var array
	 */
	protected $_currentResource;
	
	/**
	 * Настройки
	 * @var array
	 */
	public $config = array (
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
	
	/**
	 * Пакование ресурсов в строку или указанный файл.
	 * 
	 * @param array <string> $resources
	 * 		Ресурсы
	 * @param string $result_file [optional]
	 * 		Файл для сохранения результата.
	 * @return null|string
	 * 		
	 */
	public function pack (array $resources, $result_file = '')
	{
		$packages = array ();
		
		foreach ($resources as $resource)
		{
			if (!isset ($resource ['source']))
			{
				$resource ['source'] = 
					rtrim (IcEngine::root (), '/') . $resource ['href'];
			}
			
			$this->_currentResource = $resource;
			
			$packages [] = $this->packOne ($resource);
		}
		
		$packages = implode ("\n", $packages);
		
		if ($result_file)
		{
			return file_put_contents ($result_file, $packages);
		}

		return $packages;
	}
	
	/**
	 * Паковка одного ресурса
	 * @param array $resource
	 * 		Ресурс.
	 * @return string
	 * 		Запакованная строка, содержащая ресурс.
	 */
	abstract public function packOne ($resource);
	
}