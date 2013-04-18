<?php

/**
 * Абстрактный валидатор модели
 * 
 * @author morph
 */
class Model_Validator_Abstract
{
    /**
     * Схема валидации
     * 
     * @var array
     */
	protected $scheme = array ();

    /**
     * Валидация модели
     * 
     * @param Model $model
     * @param Data_Transport|array $input
     * @return array|boolean
     */
	public function validate($model, $input)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelValidator = $serviceLocator->getService('modelValidator');
		return $modelValidator->validate($model, $this->scheme, $input);
	}
}