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
		
		$jses = $this->_view->resources()->getData (
			View_Resource_Manager::JS
		);
		
		$result = '';
		
		if ($config->packed_file)
		{
			$packer = $this
				->_view
				->resources ()
				->packer (View_Resource_Manager::JS);
			
			$packer->pack ($jses, $config->packed_file);
			
			$result = 
				str_replace ('{$url}', $config->packed_url, self::TEMPLATE);
		}
		else
		{
			foreach ($jses as $js)
			{
				$result .=
					str_replace ('{$url}', $js ['href'], self::TEMPLATE);
			}
		}
		
		return $result;
	}
	
}