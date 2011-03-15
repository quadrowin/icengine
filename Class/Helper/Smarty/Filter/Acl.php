<?php
/**
 * 
 * @desc Помощник для показа дополнительных секций администратора.
 * @author Юрий
 * @package IcEngine
 *
 */
class Helper_Smarty_Filter_Acl
{
	
	const SMARTY_TAG = 'acl ';
	const SMARTY_TAG_C = '{/acl}';
	
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
		$smarty->register_prefilter (array (__CLASS__, 'filter'));
	}
	
	/**
	 * @desc Фильтрация.
	 * @param string $tpl_source
	 * @param Smarty $smarty
	 * @return string
	 */
	public static function filter ($tpl_source, Smarty $smarty)
	{
		if (!$tpl_source)
		{
			return;
		}
		
		return self::pregReplace ($tpl_source);
	}
	
	/**
	 * 
	 */
	public static function pregReplace ($input)
	{
		$regex = '#\{acl}((?:[^[]|\{(?!/?acl})|(?R))+)\{/acl}#';
		
	    if (is_array ($input))
	    {
	        $input = '11' . print_r ($input) . '22';
	    }
	
	    return preg_replace_callback ($regex, __METHOD__, $input);
	}
	
}

$tests = array (
	'{acl}{/acl}',
	'{acl}foo{/acl}',
	'{acl role="bar"}{/acl}',
	'{acl role="bar"}foo{/acl}',
	'{acl role="bar"}foo{acl role="admin"}biz{/acl}{/acl}'
);

foreach ($tests as $test)
{
	echo 
		$test . '<br />' . 
		Helper_Smarty_Filter_Acl::pregReplace ($test) . '<br /><br />';
}