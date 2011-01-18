<?php

class View_Helper_Js extends View_Helper_Abstract
{

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
	    
		$jses = $this->_view->resources()->getData (View_Resource_Manager::JS);
		
		$result = '';
		
		if ($config ['packed_file'])
		{
		    Loader::load ('View_Resource_Packer_Js');
            View_Resource_Packer_Js::pack (
                $jses, $config ['packed_file']
            );
            $result = 
                str_replace ('{$url}', $config ['packed_url'], self::TEMPLATE);
		}
		else
		{
    		foreach ($jses as $js)
    		{
    			$result .=
    			    str_replace ('{$url}', $js, self::TEMPLATE);
    		}
		}
		
		return $result;
	}
	
}