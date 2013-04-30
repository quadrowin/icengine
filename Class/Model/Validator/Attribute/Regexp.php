<?php

/**
 * Проверка на соответствие регулярному выражению
 * 
 * @author morph
 */
class Model_Validator_Attribute_Regexp extends Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
		return preg_match(
            '#' . $this->value . '#', $this->model->sfield($this->field)
        );
	}
}