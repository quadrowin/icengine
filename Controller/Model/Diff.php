<?php

/**
 * Создание миграции на основании разницы в текущей схеме модели и
 * схеме этой модели в источнике данных
 * 
 * @author morph
 */
class Controller_Model_Diff extends Controller_Abstract
{
    /**
     * Создание миграции
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelMigrateDiff")
     */
    public function index($name, $context)
    {
        $migrations = $context->helperModelMigrateDiff->diff($name);
        print_r($migrations);
    }
}