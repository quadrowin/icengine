<?php

/**
 * Управление схемой модели (Model_Mapper)
 * 
 * @author morph
 */
class Controller_Model_Scheme extends Controller_Abstract
{
    /**
     * Пересинхронизировать схему с аннотаций
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelMigrateSync")
     */
    public function resync($name, $context)
    {
        $context->helperModelMigrateSync->resync($name);
    }
}