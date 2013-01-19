<?php

/**
 * Выводит слово с нужным окончанием
 *
 * @param int $number
 * @param array $forms
 * @return string
 */
function smarty_modifier_plural($number, $forms)
{
	$locator = IcEngine::serviceLocator();
	$viewHelperManager = $locator->getService('viewHelperManager');
	return $viewHelperManager->get(
		'Plural',
		array(
			'value'	=> $number,
			'forms'	=> $forms
		)
	);
}