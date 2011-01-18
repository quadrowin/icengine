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
			$packages [] = self::packOne (file_get_contents (
			    rtrim (IcEngine::root (), '/') . $resource
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
	    return $script . "\n";
	    
		$packer = new JavaScriptPacker (
			$script, 0
		);
		return $packer->pack ();
	}
}