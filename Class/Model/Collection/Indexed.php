<?php

class Model_Collection_Indexed extends Model_Collection
{
    
    /**
     * Получение объектов коллекции по набору полей.
     * Для корректной работы, необходимо существование схемы модели.
     * 
     * @param array $conditions
     * 		Индексируемые поля для выбора
     */
    public function selectBy (array $conditions)
    {
        IcEngine::$modelManager->selectBy ($this->modelName (), $conditions);
    }
    
}