<?php
/**
 * 
 * @desc Упаковщик Js ресурсов представления.
 * @author Юрий
 * @package IcEngine
 *
 */
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
	
	public function packOne (View_Resource $resource)
	{
		if (
			$this->config ()->item_prefix &&
			isset ($resource->filePath)
		)
		{
			$result = str_replace (
				'{$source}',
				$resource->filePath,
				$this->config ()->item_prefix
			);
		}
		else
		{
			$result = '';
		}
		
		if ($this->_currentResource->nopack)
		{
			echo 'nopack: ' . $resource->filePath;
			$result .= $resource->content () . "\n";
		}
	    else
	    {
			echo 'pack: ' . $resource->filePath;
			$packer = new JavaScriptPacker ($resource->content (), 0);
			$result .= $packer->pack ();
	    }
	    
		return $result . $this->config ()->item_postfix;
	}
	
}