<?php

class Model_Component extends Model
{
    
    /**
     * Переподключение компонента к другой модели
     * 
     * @param Model $model
     * 		Модель, к которой будет подключен компонент
     * @return Model_Component
     * 		Этот компонент
     */
    public function rejoin (Model $model)
    {
        $this->update (array (
            'table'	=> $model->table (),
            'rowId'	=> $model->key ()
        ));
        return $this;
    }
    
}