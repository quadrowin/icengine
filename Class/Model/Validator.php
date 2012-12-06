<?php

/**
 * Валидатор модели
 *
 * @author morph
 * @Service("modelValidator")
 */
class Model_Validator extends Manager_Abstract
{
    /**
     * Валидация модели
     *
     * @param Model $model
     * @param array $scheme Схема валидации
     * @param array|Data_Transport $input Входные параметры для валидации
     * @return boolean|array
     */
	public function validate($model, $scheme, $input)
	{
		$validate = array();
		$error = false;
        $validatorAttribute = $this->getService('modelValidatorAttribute');
		foreach ($scheme as $field => $attributes) {
			$current = array(
				'valid'		=> true,
				'errors'	=> array()
			);
			foreach ($attributes as $attribute => $value) {
				if (is_numeric($attribute)) {
					$attribute = $value;
					$value = true;
				}
				$result = $validatorAttribute->validate(
					$attribute, $model, $field, $value, $input
				);
				$current['valid'] &= $result;
				if (!$result) {
					$error = true;
					$current['errors'][$attribute] = 1;
				}
			}
			$validate[$field] = $current;
		}
		if (!$error) {
			return true;
		}
		return $validate;
	}
}