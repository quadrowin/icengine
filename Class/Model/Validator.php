<?php

/**
 * Валидатор модели
 *
 * @author morph
 */
class Model_Validator
{
    /**
     * Валидация модели
     *
     * @param Model $model
     * @param array $scheme Схема валидации
     * @param array|Data_Transport $input Входные параметры для валидации
     * @return boolean|array
     */
	public static function validate ($model, $scheme, $input)
	{
		$validate = array ();
		$error = false;

		foreach ($scheme as $field=>$attributes)
		{
			$current = array (
				'valid'		=> true,
				'errors'	=> array ()
			);

			foreach ($attributes as $attribute => $value)
			{
				if (is_numeric ($attribute))
				{
					$attribute = $value;
					$value = true;
				}

				$result = Model_Validator_Attribute::validate (
					$attribute, $model, $field, $value, $input
				);

				$current ['valid'] &= $result;
				if (!$result)
				{
					$error = true;
					$current ['errors'][$attribute] = 1;
				}
			}

			$validate [$field] = $current;
		}

		if (!$error)
		{
			return true;
		}
		return $validate;
	}
}