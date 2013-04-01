<?php

/**
 * Хелпер для пометки миграций в случае если необходимо, чтобы миграция
 * выполнилась только один раз для конкретного источника данных
 * 
 * @author morph
 * @Service("helperMigrationMark")
 */
class Helper_Migration_Mark extends Helper_Abstract
{
    /**
     * Получить конфигурацию для помеченных миграций
     */
    protected function getConfig()
    {
        $config = $this->getService('configManager')->get('Migration_Mark');
        if (!$config) {
            $this->rewriteConfig(array());
            return array();
        }
        return $config->__toArray();
    }
    
    /**
     * Помечена ли миграция
     * 
     * @param string $migrationName
     * @return boolean
     */
    public function isMarked($migrationName)
    {
        $config = $this->getConfig();
        return isset($config[$migrationName]);
    }
    
    /**
     * Пометить миграцию
     * 
     * @param string $migrationName
     */
    public function mark($migrationName)
    {
        $config = $this->getConfig();
        $config[$migrationName] = true;
        $this->rewriteConfig($config);
    }
    
    /**
     * Перезаписать конфигурацию пометок
     * 
     * @param array $config
     */
    protected function rewriteConfig($config)
    {
        $filename = IcEngine::root() . 'Ice/Config/Migration/Mark.php';
        $output = $this->getService('helperCodeGenerator')->fromTemplate(
            'migrationMark', array('config' => $config)
        );
        file_put_contents($filename, $output);
    }
}