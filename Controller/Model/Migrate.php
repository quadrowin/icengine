<?php

/**
 * Контроллер для миграции моделей на основе автоматических миграций, созданных
 * Model_Diff
 * 
 * @author morph
 */
class Controller_Model_Migrate extends Controller_Abstract
{
    /**
     * Выполнить миграцию для всех моделей
     * 
     * @Context("controllerManager", "helperArray", "helperMigrationQueue")
     * @Template(null)
     * @Validator("User_Cli")
     * @ConfigMerge("Controller_Model_Diff")
     */
    public function all($context, $mark = false)
    {
        $config = $this->config();
        $queue = $context->helperMigrationQueue->getQueue($config->category);
        $modelNames = array_unique(
            $context->helperArray->column($queue, 'modelName')
        );
        $input = array('mark' => $mark);
        foreach ($modelNames as $modelName) {
            $input['name'] = $modelName;
            $context->controllerManager->call('Model_Migrate', 'index', $input);
        }
    }
    
    /**
     * Запуск автоматических миграций для заданной модели
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("migrationManager", "helperMigrationQueue", "configManager")
     * @Context("controllerManager", "helperMigrationMark")
     * @ConfigMerge("Controller_Model_Diff")
     */
    public function index($name, $context, $mark = false)
    {
        $config = $this->config();
        $queue = $context->helperMigrationQueue->getQueue($config->category);
        foreach ($queue as $queueData) {
            $migration = $context->migrationManager->get($queueData['name']);
            if ($queueData['modelName'] != $name || $queueData['isFinished']) {
                continue;
            }
            if ($queueData['isMarked']) {
                $migration->log('up');
                continue;
            }
            $migration->up();
            $migration->log('up');
            if ($mark) {
                $context->helperMigrationMark->mark($queueData['name']);
            }
            $input = array('name' => $name);
            $context->controllerManager->call('Model_Rebuild', 'index', $input);
        }
    }
}