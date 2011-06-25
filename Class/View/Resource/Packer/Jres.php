<?php
/**
 * 
 * @desc Упаковщик Jres ресурсов представления.
 * @author Юрий
 * @package IcEngine
 *
 */
Loader::load ('View_Resource_Packer_Abstract');

class View_Resource_Packer_Jres extends View_Resource_Packer_Abstract
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
		
		$content = $resource->content ();
		
		$content = 
			'Resource_Manager.set (\'' . $resource->localPath . '\', new Resource ' . $content . ');';
		
		if (
			isset ($this->_currentResource->nopack) &&
			$this->_currentResource->nopack
		)
		{
			$result .= $content . "\n";
		}
	    else
	    {
			$packer = new JavaScriptPacker ($content, 0);
			$result .= $packer->pack ();
	    }
	    
		return $result . $this->config ()->item_postfix;
	}
	
}