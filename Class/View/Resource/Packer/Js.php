<?php
/**
 * 
 * @desc Упаковщик Js ресурсов представления.
 * @author Юрий
 * @package IcEnginen
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
			$result = strtr (
				$this->config ()->item_prefix,
				array (
					'{$source}' => $resource->filePath,
					'{$src}'	=> $resource->localPath
				)
			);
		}
		else
		{
			$result = '';
		}
		
		if ($this->_currentResource->nopack)
		{
			$result .= $resource->content ();
		}
		else
		{
//			$result .= 
//				preg_replace (
//					'#\n\s*/\*.*\*/#Us', 
//					"\n",
//					$resource->content ()
//				);
			//$packer = new JavaScriptPacker ($resource->content (), 0);
			//$result .= $packer->pack ();
			
//			ob_start ();
//			print_r (debug_backtrace ());
//			$trace = ob_get_contents ();
//			ob_end_clean ();

/*			file_put_contents (
				rtrim ($_SERVER ['DOCUMENT_ROOT'], '/').'/cache/last',
				
				$trace . PHP_EOL,
				//$resource->localPath . ' ' . Helper_Date::toUnix () . ' ' . microtime () . ' ' . $_SERVER ['REMOTE_ADDR'].PHP_EOL,
				FILE_APPEND
			);
*/
			$result .= $resource->content ();
		}
		
		return $result . $this->config ()->item_postfix;
	}
	
}
