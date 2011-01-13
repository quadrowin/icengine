<?php

Loader::load ('View_Resource_Packer_Abstract');

class View_Resource_Packer_Css extends View_Resource_Packer_Abstract
{
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
			$resources = View_Render_Broker::getView()
				->resources ()
					->getData (View_Resource_Manager::CSS);
		}
		
		$packages = array ();
		for ($i = 0, $icount = sizeof ($resources); $i < $icount; $i++)
		{
			$packages [] = self::packOne (file_get_contents ($resources [$i]));
		}
		
		$packed = join ('', $packages);
		
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
	    $style = str_replace (array ("\r\n", "\r", "\n", "\t"), '', $style);
	    while ($style != str_replace ('  ', ' ', $style));	
	    return $style;
	}
}