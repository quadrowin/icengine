<?php
/**
 * 
 * @desc Контроллер для работы с Css
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_View_Resource_Css extends Controller_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		$config = $this->config ();
		
		Loader::load ('View_Resource_Loader');
		
		if (isset ($config->dirs))
		{
			View_Resource_Loader::load (
				$config->base_url,
				$config->base_dir,
				$config->dirs
			);
		}
		else
		{
			foreach ($config->sources as $source)
			{
				View_Resource_Loader::load (
					$source ['base_url'],
					$source ['base_dir'],
					$source ['patterns']
				);
			}
		}
		
		$view = View_Render_Broker::getView ();
		
		$csses = $view->resources ()->getData (View_Resource_Manager::CSS);
		
		if ($config->packed_file)
		{
			$packer = $view->resources ()
				->packer (View_Resource_Manager::CSS);
					
			$packer->pack ($csses, $config->packed_file);
			
			$this->_output->send (array (
				'css'	=> $config->packed_url,
				'ts'	=> $packer->cacheTimestamp ()
			));
		}
		else
		{
			$this->_output->send ('csses', $csses);
		}
	}
	
}