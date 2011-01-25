<?php

class View_Helper_Js extends View_Helper_Abstract
{

	/**
	 * Шаблон вставки
	 * @var string
	 */
    const TEMPLATE = 
    	"\t<script type=\"text/javascript\" src=\"{\$url}\"></script>\n";
    
	public function get (array $params)
	{
	    $config = (array) Config_Manager::load ('View_Resource', 'js')
	        ->__toArray ();
	    
	    Loader::load ('View_Resource_Loader');
	    View_Resource_Loader::load (
	        $config ['base_url'],
	        $config ['base_dir'],
	        $config ['dirs']
	    );
	    
		$jses = $this->_view->resources()->getData (
			View_Resource_Manager::JS
		);
		
		$result = '';
		
		if ($config ['packed_file'])
		{
			$packer = $this
				->_view
				->resources ()
				->packer (View_Resource_Manager::JS);
			
		    $packer->config = array_merge ($packer->config, $config);
		    
		    $packer->pack ($jses, $config ['packed_file']);
		    
            $result = 
                str_replace ('{$url}', $config ['packed_url'], self::TEMPLATE);
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