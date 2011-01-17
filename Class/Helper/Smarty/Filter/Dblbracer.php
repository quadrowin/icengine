<?php

class Helper_Smarty_Filter_Dblbracer
{
    
    const SMARTY_TAG = '{dblbracer}';
    const SMARTY_TAG_C = '{/dblbracer}';
    
    const TEMP_TAG = '<oPeNbRacEr>';
    const TEMP_TAG_C = '<ClosEBrAcER>';
    
    public static function register (Smarty $smarty)
    {
        $smarty->register_prefilter (
            array (__CLASS__, 'filter'));
    }
	
    public static function filter ($tpl_source, Smarty $smarty)
    {
        if (!$tpl_source)
        {
            return;
        }
        
        $p1 = strpos ($tpl_source, self::SMARTY_TAG);
        
        if (!$p1)
        {
            return $tpl_source;
        }
        
        $p2 = strpos ($tpl_source, self::SMARTY_TAG_C);
        
        if (!$p2)
        {
            return $tpl_source;
        }
        
        $result = substr ($tpl_source, 0, $p1);
        
        do
        {
            $content = substr (
                $tpl_source,
                $p1 + strlen (self::SMARTY_TAG),
                $p2 - $p1 - strlen (self::SMARTY_TAG)
            );
            
            $content = str_replace (
            	array ('{{', '}}'), 
            	array (self::TEMP_TAG, self::TEMP_TAG_C),
            	$content
            );
            
            $parts = explode ('{', $content);
            
            foreach ($parts as &$part)
            {
                $part = implode ('{rdelim}', explode ('}', $part));
            }
            
            $content = implode ('{ldelim}', $parts);
            
            $content = str_replace (
                array (self::TEMP_TAG, self::TEMP_TAG_C),
                array ('{', '}'), 
            	$content
            );
            
            $result .= $content;
            
            $last_p2 = $p2;
            
            $p1 = strpos ($tpl_source, self::SMARTY_TAG, $p2 + 3);
            $p2 = strpos ($tpl_source, self::SMARTY_TAG_C, $p1 + 3);
        } while ($p1 && $p2);
        
        $result .= substr (
            $tpl_source, 
            $last_p2 + strlen (self::SMARTY_TAG_C));
        
        return $result;
    }
    
}