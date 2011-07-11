<?php
/**
 * 
 * @desc Контроллер для подключения js скриптов
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_View_Resource_Js extends Controller_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		$config = $this->config ();
		
		Loader::load ('View_Resource_Manager');
		
		foreach ($config->sources as $source)
		{
			View_Resource_Manager::load (
				$source ['base_url'],
				$source ['base_dir'],
				$source ['patterns'],
				View_Resource_Manager::JS
			);
		}
		
		$jses = View_Resource_Manager::getData (View_Resource_Manager::JS);
		
		if ($config->packed_file)
		{
			$packer = View_Resource_Manager::packer (
				View_Resource_Manager::JS
			);
			
			$packer->pack ($jses, $config->packed_file);
			
			$this->_output->send (array (
				'url'	=> $config->packed_url,
				'ts'	=> $packer->cacheTimestamp ()
			));
		}
		else
		{
			$this->_output->send ('jses', $jses);
		}
	}
	
}