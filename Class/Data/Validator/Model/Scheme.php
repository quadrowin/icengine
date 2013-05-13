<?php

/**
 * Проверяет существует ли указанное поле в схеме модели
 * 
 * @author morph
 */
class Data_Validator_Model_Scheme extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($data, $value = null)
    {
        $scheme = IcEngine::serviceLocator()->getService('modelScheme')->scheme(
            reset($this->params)
        );
        if ($data && !isset($scheme->fields[$data])) {
            return false;
        }
        return true;
    }
}