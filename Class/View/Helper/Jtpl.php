<?php

class View_Helper_Jtpl extends View_Helper_Abstract
{

	/**
	 * Шаблон вставки
	 * @var string
	 */
	const TEMPLATE = 
		"\t<script type=\"text/javascript\" src=\"{\$url}\"></script>\n";
	
	public function get (array $params)
	{
		$config = Config_Manager::get ('View_Resource', 'jtpl');
		
		Loader::load ('View_Resource_Loader');
		
		$sources = $config->sources;
		
		foreach ($config->sources as $source)
		{
			View_Resource_Loader::load (
				$source ['base_url'],
				$source ['base_dir'],
				$source ['patterns'],
				View_Resource_Manager::JTPL
			);
		}
		
		$tpls = $this->_view->resources ()->getData (
			View_Resource_Manager::JTPL
		);
		
		$result = '';
		
		$packer = $this
			->_view
			->resources ()
			->packer (View_Resource_Manager::JTPL);
		
		$packer->config = $config->mergeConfig ($packer->config);
		
		$packer->pack ($tpls, $config ['packed_file']);
		
		$result = 
			str_replace ('{$url}', $config ['packed_url'], self::TEMPLATE);
		
		return $result;
	}
	
}