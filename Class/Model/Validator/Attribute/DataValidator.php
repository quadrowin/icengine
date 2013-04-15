<?php

/**
 * Валидировать модель посредствам Data_Validator
 * 
 * @author morph
 */
class Model_Validator_Attribute_DataValidator extends 
    Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $dataValidatorManager = $serviceLocator->getService(
            'dataValidatorManager'
        );
		$result = true;
		foreach ($this->value as $validator) {
			$validator = $dataValidatorManager->get($validator);
			$current = false;
			if ($validator) {
				$current = $validator->validate(
                    $this->model->sfield($this->field)
                );
			}
			$result &= $current;
            if (!$result) {
                break;
            }
		}
		return $result;
	}
}