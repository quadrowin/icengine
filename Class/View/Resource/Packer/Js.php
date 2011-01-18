<?php

Loader::load ('View_Resource_Packer_Abstract');

class View_Resource_Packer_Js extends View_Resource_Packer_Abstract
{
	/**
	 * @desc Класс Packer'a
	 * @var string
	 */
	const PACKER = 'class.JavaScriptPacker.php';
	
	public function __construct ()
	{
		Loader::requireOnce (self::PACKER, 'includes');
	}
	
	public static function packOne ($resource)
	{
		if (
			$this->config ['pack_item_prefix'] &&
			isset ($resource ['options']['source'])
		)
		{
			$result = str_replace (
				'{$source}',
				$resource ['source'],
				$this->config ['pack_item_prefix']
			);
		}
		
		if (
			isset (self::$_currentResource ['options']['nopack']) &&
			self::$_currentResource ['options']['nopack']
		)
		{
			$result .= $resource ['href'] . "\n";
		}
	    else
	    {
			$packer = new JavaScriptPacker ($resource ['href'], 0);
			$result .= $packer->pack ();
	    }
	    
		return $result . $this->config ['pack_item_postfix'];
	}
}