<?php

/**
 * Помощник модели
 *
 * @author morph, neon
 * @Service("helperModel")
 */
class Helper_Model 
{
    /**
     * Накинуть на модель after опшены
     *
     * @return Model
     */
    public function appendAfterOptions()
    {
        $args = func_get_args();
        $model = $args[0];
        $options = array_slice($args, 1);
        $locator = IcEngine::serviceLocator();
        $collectionManager = $locator->getService('collectionManager');
        $modelCollection = $collectionManager->create($model->modelName());
        $modelCollection->reset();
        $modelCollection->add($model);
        $optionManager = $locator->getService('collectionOptionManager');
        $optionManager->executeAfter($modelCollection, $options);
        return $modelCollection->first();
    }

    /**
     * Фильтрация значения
     * 
     * @param Model $model
     * @param string $field
     * @param mixed value
     * @return boolean
     */
    public function filterValue($model, $field, $value)
    {
        $annotations = $model->getAnnotations()['properties'];
        if (!isset($annotations[$field])) {
            return $value;
        }
        if (isset($annotations[$field]['Data\\Filter'])) {
            $serviceLocator = IcEngine::serviceLocator();
            $dataFilterManager = $serviceLocator->getService(
                'dataFilterManager'
            );
            $dataFilters = reset($annotations[$field]['Data\\Filter']);
            foreach ($dataFilters as $dataFilterName) {
                $dataFilter = $dataFilterManager->get($dataFilterName);
                if (!$dataFilter) {
                    throw new ErrorException('Incorrect data filter');
                }
                $value = $dataFilter->filter($value);
            }
        }
        return $value;
    }
    
    /**
     * Получить константу класса
     * 
     * @param string $const
     * @return string
     */
    public function getConst($const)
    {
        list($className, $constName) = explode('::', $const);
        $reflection = new \ReflectionClass($className);
        return $reflection->getConstant($constName);
    }

    /**
     * Получить public поля подели
     *
     * @param Model $model
     * @return array
     */
    public function getVars($model)
    {
        return get_object_vars($model);
    }
    
    /**
     * Сформировать путь до модели
     * 
     * @param string $modelName
     * @return string
     */
    public function makePath($modelName)
    {
        return str_replace('_', '/', $modelName) . '.php';
    }
    
    /**
     * Валидация поля
     * 
     * @param Model $model
     * @param string $field
     * @param mixed value
     * @return boolean
     */
    public function validateField($model, $field, $value)
    {
        $annotations = $model->getAnnotations()['properties'];
        if (!isset($annotations[$field])) {
            return true;
        }
        if (isset($annotations[$field]['Data\\Validator'])) {
            $dataValidatorName = 
            $serviceLocator = IcEngine::serviceLocator();
            $dataValidatorManager = $serviceLocator->getService(
                'dataValidatorManager'
            );
            $dataValidators = reset($annotations[$field]['Data\\Validator']);
            $isValid = true;
            foreach ($dataValidators as $dataValidatorName => $params) {
                if (is_numeric($dataValidatorName)) {
                    $dataValidatorName = $params;
                    $params = null;
                }
                $dataValidator = $dataValidatorManager->get($dataValidatorName);
                if (!$dataValidator) {
                    throw new ErrorException('Incorrect data validator');
                }
                $isValid = $isValid & $dataValidator->validate($value, $params);
                if (!$isValid) {
                    return false;
                }
            }
        }
        return true;
    }
}