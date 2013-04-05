<?php

/**
 * Инпорт схемы модели из СУБД. Создание файлов модели и схемы
 * 
 * @author morph
 */
class Controller_Model_Import extends Controller_Abstract
{
    /**
     * Начать инпорт модели
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelImport")
     */
    public function run($name, $context)
    {
        $context->helperModelImport->inport($name);
    }
}