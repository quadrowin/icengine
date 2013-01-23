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
     * Получить public поля подели
     *
     * @param Model $model
     * @return array
     */
    public function getVars($model)
    {
        return get_object_vars($model);
    }
}