<?php

class View_Helper_Css extends View_Helper_Abstract
{

	/**
	 * Шаблон вставки стиля.
	 * @var string
	 */
	const TEMPLATE = 
		"\t<link href=\"{\$url}\" rel=\"stylesheet\" type=\"text/css\" />\n";
	
	public function get (array $params)
	{
		$config = (array) Config_Manager::load ('View_Resource', 'css')
			->__toArray ();
		
		Loader::load ('View_Resource_Loader');
		
		if (isset ($config ['dirs']))
		{
			View_Resource_Loader::load (
				$config ['base_url'],
				$config ['base_dir'],
				$config ['dirs']
			);
		}
		else
		{
			foreach ($config ['sources'] as $source)
			{
				View_Resource_Loader::load (
					$source ['base_url'],
					$source ['base_dir'],
					$source ['patterns']
				);
			}
		}
		
		$csses = $this->_view->resources ()->getData (
			View_Resource_Manager::CSS
		);
			
		$result = '';
		
		if ($config ['packed_file'])
		{
			$packer = $this
				->_view
				->resources ()
				->packer (View_Resource_Manager::CSS);
				
			$packer->config = array_merge ($packer->config, $config);
				
			$packer->pack ($csses, $config ['packed_file']);
				
			$result = 
				str_replace ('{$url}', $config ['packed_url'], self::TEMPLATE);
		}
		else
		{
			foreach ($csses as $css)
			{
				$result .=
					str_replace('{$url}', $css ['href'], self::TEMPLATE);
			}
		}
		
		return $result;
	}
	
}