<?php
/**
 * 
 * @desc Помощник для подмены открывающих скобок на двойные.
 * Вместо "{" и "}" следует использовать "{{" и "}}".
 * @author Юрий
 * @package IcEngine
 *
 */
class Helper_Smarty_Filter_Dblbracer
{
	
	const SMARTY_TAG = '{dblbracer}';
	const SMARTY_TAG_C = '{/dblbracer}';
	
	const TEMP_TAG = '<oPeNbRacEr>';
	const TEMP_TAG_C = '<ClosEBrAcER>';
	
	protected static $_tags = array (
		0	=> self::SMARTY_TAG,
		1	=> self::SMARTY_TAG_C
	);
	
	/**
	 * 
	 * @param string $str
	 */
	public static function _replaceRdelim ($str)
	{
		return str_replace ('}', '{rdelim}', $str);
	}
	
	/**
	 * 
	 * @param Smarty $smarty
	 */
	public static function register (Smarty $smarty)
	{
		$smarty->register_prefilter (
			array (__CLASS__, 'filter'));
	}
	
	/**
	 * 
	 * @param unknown_type $tpl_source
	 * @param Smarty $smarty
	 * @return string
	 */
	public static function filter ($tpl_source, Smarty $smarty)
	{
		if (!$tpl_source)
		{
			return;
		}
		
		$last_pos = 0;
		$t = 0;
		$result = '';
		
		while (true)
		{
			$tag = self::$_tags [$t];
			
			$p = strpos ($tpl_source, $tag, $last_pos);
			
			if (!$p)
			{
				return $result . substr ($tpl_source, $last_pos);
			}
			
			$chunk = substr ($tpl_source, $last_pos, $p - $last_pos);
			
			if (1 == $t)
			{
				// Заменяемая часть
				$chunk = str_replace (
					array ('{{', '}}'), 
					array (self::TEMP_TAG, self::TEMP_TAG_C),
					$chunk
				);
				
				
				$parts = array_map (
					array (__CLASS__, '_replaceRdelim'),
					explode ('{', $chunk)
				);
			
				$chunk = str_replace (
					array (self::TEMP_TAG, self::TEMP_TAG_C),
					array ('{', '}'),
					implode ('{ldelim}', $parts)
				);
			}
			
			$result .= $chunk;
			
			$last_pos = $p + strlen ($tag);
			$t = 1 - $t;
		}
	}
	
}