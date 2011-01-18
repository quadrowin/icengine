<?php

Loader::load ('View_Resource_Packer_Abstract');

class View_Resource_Packer_Css extends View_Resource_Packer_Abstract
{
    
    /**
     * Внешний путь до текущего файла ресурса
     * @var string
     */
    protected static $_currentFilePathUrl;
    
    /**
     * Текущий ресурс
     * @var array
     */
    protected static $_currentResource;
    
    /**
     * Импортируемые стили
     * @var array
     */
    protected static $_imports = array (); 
    
    public static function excludeImport (array $matches)
    {
        if (strncmp ($matches [1], '/', 1) == 0)
        {
            self::$_imports [] = $matches [0];
        }
        else
        {
            self::$_imports [] = 
            	'@import "' . self::$_currentFilePathUrl . $matches [1] . '";';
        }
        
        return '';
    }
    
    /**
     * 
     * @param string $str
     * @return string
     */
    public static function replaceUrl (array $matches)
    {
        if (strncmp ($matches [1], '/', 1) == 0)
        {
            return $matches [0];
        }
        
        return 'url("' . self::$_currentFilePathUrl . $matches [1] . '")';
    }
    
	/**
	 * 
	 * @param null|array <string> $resources
	 * @param string $result_style
	 * @return string|null
	 */
	public static function pack ($resources = null, $result_style = '')
	{
		if (is_null ($resources))
		{
			$resources = View_Render_Broker::getView ()
				->resources ()
					->getData (View_Resource_Manager::CSS);
		}
		
		$packages = array ();
		foreach ($resources as $resource)
		{
			self::$_currentResource = $resource;
		    self::$_currentFilePathUrl = dirname ($resource ['href']) . '/';
		    
			$packed = self::packOne (file_get_contents (
				rtrim (IcEngine::root (), '/') . $resource ['href']
			));
			
    		$packed = preg_replace_callback (
    			'/url\\([\'"]?(.*?)[\'"]?\\)/i',
    		    array (__CLASS__, 'replaceUrl'),
    		    $packed
    		);
    		
    		$packed = preg_replace_callback (
    		    '/@import\\s*[\'"]?(.*?)[\'"]?\\s*;/i',
    		    array (__CLASS__, 'excludeImport'),
    		    $packed
    		);
			
			$packages [] = "\n/* $resource */\n" . $packed;
		}
		
		$packed =
		    implode ("\n", self::$_imports) . "\n" . 
		    implode ("\n", $packages);
		
		if ($result_style)
		{
			file_put_contents ($result_style, $packed);
		}
		else
		{
			return $packed;
		}
	}
	
	/**
	 * 
	 * @param string $style
	 * @return string
	 */
	public static function packOne ($style)
	{
		$style = preg_replace ('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $style);
		$style = str_replace (array ("\r", "\t", '@CHARSET "UTF-8";'), '', $style);
		
		do {
    		$length = strlen ($style);
    		$style = str_replace ('  ', ' ', $style); 
		} while (strlen ($style) != $length);
		
		return $style;
	}
}