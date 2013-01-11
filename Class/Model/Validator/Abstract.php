<?php

class Model_Validator_Abstract
{
	protected static $_scheme = array (

	);

	public static function validate ($model, $input)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelValidator = $serviceLocator->getService('modelValidator');
		return $modelValidator->validate($model, static::$_scheme, $input);
	}
}