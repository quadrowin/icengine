<?php

function smarty_modifier_plural ($string, $forms)
{
	Ice\Loader::load ('View_Helper_Manager');
	return Ice\View_Helper_Manager::get (
		'Plural',
		array (
			'value'	=> $string,
			'forms'	=> $forms
		)
	);
}