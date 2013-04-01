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
     * @inheritdoc
     */
    protected $config = array(
        'category' => 'modelDiff'
    );
    
    /**
     * Создание миграции
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelMigrateDiff", "helperMigration")
     */
    public function index($name, $context)
    {
        $migrations = $context->helperModelMigrateDiff->diff($name);
        $content = $this->getService('helperCodeGenerator')->fromTemplate(
            'diffMigration', array(
                'migrations' => $migrations,
                'modelName'  => $name
            )
        );
        $migrationName = $context->helperMigration->getName($name);
        $category = $this->config()->category;
        $context->helperMigration->create($migrationName, $category, array(
            'content'   => $content,
            'modelName' => $name
        ));
    }
}