<?php

/**
 * Валидация контроллера на предмет существования указанной модели с первичным
 * ключом, полученным из указанной переменной входящего транспорта
 * 
 * @author morph
 */
class Controller_Validator_Model_Exists extends Controller_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($params)
    {
        $params = array_values($params);
        $model = $this->getService('modelManager')->byKey(
            $params[0], $params[1]
        );
        if (!$model) {
            return $this->notFound();
        }
    }
}