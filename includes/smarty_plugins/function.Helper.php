<?php

/**
 * 
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_Helper (array $params, Smarty $smarty)
{
    $helper = $params ['call'];
    $class = 'View_Helper_' . $helper;
	Loader::load ($class);
	$helper = new $class ();
	return $helper->get ($params);
}