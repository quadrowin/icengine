<?php

Loader::load ('View_Resource_Packer_Abstract');

class View_Resource_Packer_Js extends View_Resource_Packer_Abstract
{
	/**
	 * @desc Класс Packer'a
	 * @var string
	 */
	const PACKER = 'class.JavaScriptPacker.php';
	
	/**
	 * Текущий ресурс
	 * @var array
	 */
	protected static $_currentResource;
	
	/**
	 * Настройки
	 * @var array
	 */
	public static $config = array (
		/**
		 * Префикс каждого скрипта
		 * @var string
		 */
		'pack_item_prefix' 	=> "/* {\$source} */\n",
	
		/**
		 * Постфикс каждого скрипта
		 * @var string
		 */
		'pack_item_postfix'	=> "\n\n"
	);
	
	/**
	 * 
	 * @param null|array <string> $resources
	 * @param string $result_style
	 * @return string|null
	 */
	public static function pack ($resources = null, $result_script = '')
	{
		if (is_null ($resources))
		{
			$resources = View_Render_Broker::getView()
				->resources ()
					->getData (View_Resource_Manager::JS);
		}
		
		Loader::requireOnce (
			 self::PACKER, 
			'includes'
		);

		$packages = array ();
		foreach ($resources as $resource)
		{
			self::$_currentResource = $resource;
			$packages [] = self::packOne (file_get_contents (
			    rtrim (IcEngine::root (), '/') . $resource ['href']
			));
		}
		
		$packed = implode ("\n", $packages);
		
		if ($result_script)
		{
			file_put_contents ($result_script, $packed);
		}
		else
		{
			return $packed;
		}
	}
	
	/**
	 * 
	 * @param string $script
	 * @return string
	 */
	public static function packOne ($script)
	{
		if (
			self::$config ['pack_item_prefix'] &&
			isset (self::$_currentResource ['options']['source'])
		)
		{
			$result = str_replace (
				'{$source}',
				self::$_currentResource ['options']['source'],
				self::$config ['pack_item_prefix']
			);
		}
		
		if (
			isset (self::$_currentResource ['options']['nopack']) &&
			self::$_currentResource ['options']['nopack']
		)
		{
			$result .= $script . "\n";
		}
	    else
	    {
			$packer = new JavaScriptPacker ($script, 0);
			$result .= $packer->pack ();
	    }
	    
		return $result . self::$config ['pack_item_postfix'];
	}
}