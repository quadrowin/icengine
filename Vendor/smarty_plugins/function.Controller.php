<?php
/**
 * @desc Плагин смарти для вызова контроллера.
 * @param array $params
 * @return string
 */
function smarty_function_Controller (array $params)
{
	$manager = Ice\Core::di ()->getInstance ('Ice\\Controller_Manager');
	return $manager->html ($params ['call'], $params);
}