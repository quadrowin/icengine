<?php
/**
 * @desc Плагин смарти для вызова контроллера.
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_Controller (array $params, Smarty $smarty)
{
	Loader::load ('View_Helper_Widget');
	$helper = new View_Helper_Widget ();
	$params ['call'] = 'Controller';
	return $helper->get ($params);
}