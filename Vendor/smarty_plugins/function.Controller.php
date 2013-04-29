<?php

/**
 * Плагин смарти для вызова контроллера.
 * 
 * @param array $params
 * @return string
 */
function smarty_function_Controller (array $params)
{
    $serviceLocator = IcEngine::serviceLocator();
    $controllerManager = $serviceLocator->getService('controllerManager');
	return $controllerManager->html($params['call'], $params);
}