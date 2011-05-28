<?php
/**
 * @desc Плагин смарти для вызова контроллера.
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_Controller (array $params, Smarty $smarty)
{
	Loader::load ('Controller_Manager');
	return Controller_Manager::html ($params ['call'], $params);
}