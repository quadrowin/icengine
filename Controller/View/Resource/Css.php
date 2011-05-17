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
		
		$csses = View_Render_Broker::getView ()->resources ()->getData (
			View_Resource_Manager::CSS
		);
			
		$result = array ();
		
		if ($config->packed_file)
		{
			$packer = $this
				->_view
				->resources ()
				->packer (View_Resource_Manager::CSS);
					
			$packer->pack ($csses, $config->packed_file);
			
			$this->_output->send ('css', $config->packed_url);
		}
		else
		{
			$this->_output->send ('csses', $csses);
		}
	}
	
}