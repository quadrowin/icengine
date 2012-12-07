<?php

/**
 *
 * @param type $string
 * @param type $forms
 * @return type
 */
function smarty_modifier_plural($string, $forms)
{
	$locator = IcEngine::serviceLocator();
	$viewHelperManager = $locator->getService('viewHelperManager');
	return $viewHelperManager->get(
		'Plural',
		array(
			'value'	=> $string,
			'forms'	=> $forms
		)
	);
}