<?php

Loader::load ('View_Resource_Packer_Abstract');

class View_Resource_Packer_Css extends View_Resource_Packer_Abstract
{
    
    /**
     * Импортируемые стили.
     * @var array
     */
    protected $_imports = array (); 
    
    /**
     * Callback для preg_replace вырезания @import.
     * @param array $matches
     * @return string
     */
    public function _excludeImport (array $matches)
    {
        if (strncmp ($matches [1], '/', 1) == 0)
        {
            $this->_imports [] = $matches [0];
        }
        else
        {
            $this->_imports [] = 
            	'@import "' . $this->_currentResource->urlPath . $matches [1] . '";';
        }
        
        return '';
    }
    
    /**
     * Callback для preg_replace замены путей к изображениям.
     * @param array $matches
     * @return string
     */
    public function _replaceUrl (array $matches)
    {
        if (strncmp ($matches [1], '/', 1) == 0)
        {
            return $matches [0];
        }
        
        return 'url("' . $this->_currentResource->urlPath . $matches [1] . '")';
    }
    
	public function compile (array $packages)
	{
		return
			$this->_compileFilePrefix () .
		    implode ("\n", $this->_imports) . "\n" . 
		    implode ("\n", $packages);
	}
	
	/**
	 * 
	 * @param string $style
	 * @return string
	 */
	public function packOne (View_Resource $resource)
	{
		$resource->urlPath = dirname ($resource->href) . '/';
		
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
		
		$result .= preg_replace_callback (
			'/url\\([\'"]?(.*?)[\'"]?\\)/i',
		    array ($this, '_replaceUrl'),
		    $resource->content ()
		);
    		
		$result .= preg_replace_callback (
		    '/@import\\s*[\'"]?(.*?)[\'"]?\\s*;/i',
			array ($this, '_excludeImport'),
			$result
		);
		
		$result = preg_replace ('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $result);
		$result = str_replace (array ("\r", "\t", '@CHARSET "UTF-8";'), '', $result);
		
		do {
    		$length = strlen ($result);
    		$result = str_replace ('  ', ' ', $result); 
		} while (strlen ($result) != $length);
		
		return $result . $this->config ['pack_item_postfix'];
	}
}