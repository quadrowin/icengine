<?php

/**
 * Хелпер для определения различий между текущей схемой модели и схемой
 * это модели в источнике данных
 * 
 * @author morph
 * @Service("helperModelMigrateDiff")
 */
class Helper_Model_Migrate_Diff extends Helper_Abstract
{
    /**
     * Найти различия текущей схемы и схемы источника
     * 
     * @param string $modelName
     */
    public function diff($modelName)
    {
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $dataSchemeDto = $this->getService('dto')->newInstance('Data_Scheme')
            ->setModelName($modelName);
        $dataScheme = new Data_Scheme($dataSchemeDto);
        $dataSource->getScheme($dataScheme);
        $currentScheme = $modelScheme->scheme($modelName);
        
    }
}