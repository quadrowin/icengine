<?php

/**
 * Источник данных для работы с моделями типа "Defined"
 * 
 * @author morph
 */
class Data_Source_Defined extends Data_Source_Abstract
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->setDataMapper(new Data_Mapper_Defined());
    }
}