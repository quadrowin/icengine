<?php

Loader::load ('View_Resource_Packer_Abstract');
/**
 * 
 * Упаковщик Jtpl ресурсов представления.
 * @author Юрий
 *
 */
class View_Resource_Packer_Jtpl extends View_Resource_Packer_Abstract
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
			$this->config ['pack_item_prefix'] &&
			isset ($resource->filePath)
		)
		{
			$result = str_replace (
				'{$source}',
				$resource->filePath,
				$this->config ['pack_item_prefix']
			);
		}
		else
		{
			$result = '';
		}
		
		$content = $resource->content ();
		$content = str_replace (
			array ('\\',	'"',	"\r\n",			"\n",			"\r"),
			array ('\\\\',	'\\"',	'"+"\\r\\n"+"',	'"+"\\n"+"',	'"+"\\r"+"'),
			$content
		);
		
		$content = 
			'View_Render.templates[\'' . $resource->localPath . '\']="' . $content . '";';
		
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
	    
		return $result . $this->config ['pack_item_postfix'];
	}
	
}