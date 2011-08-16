<?php

/**
 * 
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_Paginator (array $params, Smarty $smarty)
{
	Loader::load ('View_Helper_Paginator');
	$helper = new View_Helper_Paginator ();
	return $helper->get ($params);
}