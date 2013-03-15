<?php

/**
 * Помощник синхронизирующихся моделей
 * 
 * @author morph
 * @Service("helperModelSync")
 */
class Helper_Model_Sync extends Helper_Abstract
{
    /**
     * Пересобрать записи модели
     * 
     * @param string $modelName
     */
    public function resync($modelName)
    {
        $filename = IcEngine::root() . 'Ice/Model/' . 
            str_replace('_', '/', $modelName) . '.php';
        if (!is_file($filename)) {
            return;
        }
        $content = file_get_contents($filename);
        $preStartPos = strpos($content, 'public static $rows');
        if ($preStartPos === false) {
            return;
        }
        $startPos = strpos($content, '(', $preStartPos);
        $endPos = strpos($content, ';', $startPos);
        $contentFirstPart = substr($content, 0, $startPos);
        $contentLastPast = substr($content, $endPos + 1);
        $query = $this->getService('query')
            ->select('*')
            ->from($modelName);
        $filters = $modelName::$filters;
        if ($filters) {
            foreach ($filters as $field => $value) {
                $query->where($field, $value);
            }
        }
        $dataSourceManager = $this->getService('dataSourceManager');
        $syncSource = $dataSourceManager->get('Sync');
        $dataSourceManager->setDataMapper($syncSource);
        $dynamicMapper = $syncSource->getDataMapper()->getDynamicMapper();
        $options = new Query_Options();
        $table = $dynamicMapper->execute($syncSource, $query, $options)
            ->asTable();
        $output = $this->getService('helperCodeGenerator')->fromTemplate(
            'modelSync', array('data' => $table)
        );
        $parts = explode(PHP_EOL, $output);
        foreach ($parts as $i => &$part) {
            if (!trim($part, " \t")) {
                unset($parts[$i]);
                continue;
            }
            if (strpos($part, 'array') !== false || 
                strpos($part, ')') !== false) {
                $part = "\t" . $part;
            }
        }
        $resultPart = implode(PHP_EOL, $parts);
        $resultOutput = $contentFirstPart . $resultPart . $contentLastPast;
        file_put_contents($filename, $resultOutput);
    }
}